<?php
/*
    Copyright (c) 2007-2018 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Suite 21 — module webedit : les opérations que déclenche un visiteur anonyme.
 *
 * Les URL réécrites du frontoffice (sitemap.xml, flux RSS et Atom, lien de désabonnement)
 * et le formulaire d'abonnement aboutissent à des opérations ploopi_op du module. Or une
 * opération de module exige un jeton anti-CSRF, que seul le backoffice émet, sauf si le
 * module la déclare dans include/publicops.php.
 *
 * La suite monte un site réel et appelle chaque URL telle que le socle la rend à un
 * visiteur, puis mesure la réponse ou l'état en base. Elle vérifie aussi que ce qui est
 * servi publiquement ne révèle rien d'une rubrique privée.
 *
 * @package webedit
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

define('FOWEB_COURRIEL', 'visiteur.abonne@exemple.test');

$arrDeploye = array();

/**
 * Retire le module et ses données. Appelé quoi qu'il arrive.
 */
function foweb_nettoyer()
{
    global $arrDeploye;

    $arrRequetes = array(
        "DELETE FROM `ploopi_mod_webedit_heading_subscriber`",
        "DELETE FROM `ploopi_mod_webedit_article_tag`",
        "DELETE FROM `ploopi_mod_webedit_tag`",
        "DELETE FROM `ploopi_mod_webedit_article`",
        "DELETE FROM `ploopi_mod_webedit_heading`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label IN ('webedit', 'webedit_autresite'))",
        "DELETE FROM ploopi_module WHERE label IN ('webedit', 'webedit_autresite')",
        "DELETE FROM ploopi_module_type WHERE label = 'webedit'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    foreach ($arrDeploye as $strDossier) if (is_dir($strDossier)) exec('rm -rf '.escapeshellarg($strDossier));
}

register_shutdown_function('foweb_nettoyer');
foweb_nettoyer();

T::section('Déploiement du module');

$strCible = PLOOPI_ROOT.'/modules/webedit';
if (!is_dir($strCible))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/webedit/files').' '.escapeshellarg($strCible), $arrSortie, $intRc);
    if ($intRc === 0) $arrDeploye[] = $strCible;
}

foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/webedit/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('webedit', 0, 'webedit', '2.8.5', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
             VALUES ('webedit', {$intType}, 2, 1, 1, 1)");
$intWebedit = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intWebedit}, 2, 1, '')");
T::vrai('webedit déployé et instancié', is_dir($strCible.'/include') && $intWebedit > 0, "instance={$intWebedit}");

/**
 * Le frontoffice n'est servi que pour un espace qui le déclare (cf. suite 17).
 */
$arrEtatFo = db()->query('SELECT frontoffice, frontoffice_domainlist FROM ploopi_workspace WHERE id = 2')->fetch_assoc();
db()->query("UPDATE ploopi_workspace SET frontoffice = 1, frontoffice_domainlist = '*' WHERE id = 2");
register_shutdown_function(function() use ($arrEtatFo) {
    if (empty($arrEtatFo)) return;
    db()->query(sprintf(
        "UPDATE ploopi_workspace SET frontoffice = %d, frontoffice_domainlist = '%s' WHERE id = 2",
        (int)$arrEtatFo['frontoffice'], db()->real_escape_string($arrEtatFo['frontoffice_domainlist'])
    ));
});

/**
 * Crée une rubrique (flux et abonnements activés, valeurs par défaut du schéma).
 *
 * Le gabarit « corporate » rend le formulaire d'abonnement.
 *
 * @param string $strLabel libellé
 * @param int $intPrive 1 si la rubrique est privée
 * @param int $intPosition position
 * @return int identifiant
 */
function foweb_rubrique($strLabel, $intPrive, $intPosition)
{
    global $intWebedit;

    db()->query(sprintf(
        "INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                                 content_type, visible, private, private_visible, sortmode,
                                                 id_module, id_user, id_workspace)
         VALUES ('%s', '', 'corporate', 0, '', 0, %d, 'articles', 1, %d, 0, 0, %d, 2, 2)",
        db()->real_escape_string($strLabel), (int)$intPosition, (int)$intPrive, (int)$intWebedit
    ));

    return db()->insert_id;
}

/**
 * Crée un article publié depuis dix jours.
 *
 * @param string $strTitre titre (sert aussi de metatitle, donc d'URL réécrite)
 * @param int $intRubrique rubrique
 * @param int $intModule instance, celle du site par défaut
 * @return int identifiant
 */
function foweb_article($strTitre, $intRubrique, $intModule = null)
{
    global $intWebedit;

    if (is_null($intModule)) $intModule = $intWebedit;

    $strContenu = '<p>'.$strTitre.'</p>';
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_webedit_article (reference, title, metakeywords, content, content_cleaned,
                                                 metadescription, metatitle, author, version, visible,
                                                 comments_allowed, timestp, timestp_published, timestp_unpublished,
                                                 lastupdate_timestp, lastupdate_id_user, id_heading, id_module,
                                                 id_user, id_workspace, position, status, tags)
         VALUES ('', '%s', '', '%s', '%s', '', '%s', '', 1, 1, 0, '%s', '%s', 0, '%s', 2, %d, %d, 2, 2, 1, 1, '')",
        db()->real_escape_string($strTitre), db()->real_escape_string($strContenu), db()->real_escape_string($strContenu),
        db()->real_escape_string($strTitre), date('YmdHis'), date('YmdHis', strtotime('-10 days')), date('YmdHis'),
        (int)$intRubrique, (int)$intModule
    ));

    return db()->insert_id;
}

/**
 * Associe une étiquette à un article.
 *
 * @param string $strTag étiquette
 * @param int $intArticle article
 * @param int $intModule instance, celle du site par défaut
 */
function foweb_etiquette($strTag, $intArticle, $intModule = null)
{
    global $intWebedit;

    if (is_null($intModule)) $intModule = $intWebedit;

    db()->query("INSERT INTO ploopi_mod_webedit_tag (tag, id_module) VALUES ('".db()->real_escape_string($strTag)."', {$intModule})");
    $intTag = db()->insert_id;
    db()->query("INSERT INTO ploopi_mod_webedit_article_tag (id_article, id_tag) VALUES ({$intArticle}, {$intTag})");
}

$intRubPublique = foweb_rubrique('Actualites', 0, 1);
$intRubPrivee   = foweb_rubrique('Reservecachee', 1, 2);
$intArtPublic   = foweb_article('Articletemoinpublic', $intRubPublique);
$intArtPrive    = foweb_article('Articlesecretprive', $intRubPrivee);
$intArtAccent   = foweb_article('Rentrée à l\'école', $intRubPublique);
foweb_etiquette('etiquettepublique', $intArtPublic);
foweb_etiquette('etiquettesecrete', $intArtPrive);
T::vrai('rubriques et articles créés', $intArtPublic > 0 && $intArtPrive > 0);

T::section('sitemap.xml demandé par un visiteur');
H::reset();
$arrRep = H::req('/sitemap.xml');
T::eq('le plan du site est servi', 200, $arrRep['code']);
T::contient('c\'est un plan de site XML, pas la page d\'accueil', '<urlset', $arrRep['body']);

/**
 * La page d'accueil HTML cite elle aussi l'article public : on ne cherche les marqueurs
 * que dans les balises <loc> du plan.
 */
preg_match_all('#<loc>([^<]*)</loc>#', $arrRep['body'], $arrLocs);
$strPlan = strtolower(implode("\n", $arrLocs[1]));
T::contient('il référence l\'article public', 'articletemoinpublic', $strPlan);
T::contient('et l\'étiquette d\'un article public', 'etiquettepublique', $strPlan);

T::section('sitemap.xml : rien d\'une rubrique privée');
/**
 * Le plan du site est destiné aux moteurs de recherche, donc à des visiteurs anonymes.
 * Les témoins de la section précédente établissent que le plan est rendu et qu'il
 * contient les articles et étiquettes publics.
 */
T::contientpas('ni l\'article d\'une rubrique privée', 'articlesecretprive', $strPlan);
T::contientpas('ni la rubrique privée elle-même', 'reservecachee', $strPlan);
T::contientpas('ni les étiquettes de ses articles', 'etiquettesecrete', $strPlan);

T::section('Flux RSS et Atom demandés par un visiteur');
H::reset();
$arrRep = H::req('/web/rss/site.xml');
T::eq('le flux RSS du site est servi', 200, $arrRep['code']);
T::contient('c\'est un flux RSS, pas la page d\'accueil', '<rss', $arrRep['body']);
T::contient('il contient l\'article public', '<title>Articletemoinpublic</title>', $arrRep['body']);
T::contientpas('pas l\'article de la rubrique privée', 'Articlesecretprive', $arrRep['body']);

/**
 * Comparaison à la valeur d'origine, après décodage par un analyseur XML : l'échappement
 * de l'apostrophe (&#039; ou &apos;) est indifférent, un double encodage ne l'est pas.
 */
$arrTitres = array();
$objFlux = @simplexml_load_string($arrRep['body']);
if ($objFlux !== false) foreach ($objFlux->channel->item as $objItem) $arrTitres[] = (string)$objItem->title;
T::vrai('le flux est du XML bien formé', $objFlux !== false);
T::vrai('les titres accentués sont transmis tels quels', in_array('Rentrée à l\'école', $arrTitres, true), implode(' | ', $arrTitres));

H::reset();
$arrRep = H::req('/web/atom/site.xml');
T::contient('le flux Atom du site est servi', '<feed', $arrRep['body']);
T::contient('il contient l\'article public', 'Articletemoinpublic</title>', $arrRep['body']);

H::reset();
$arrRep = H::req("/web/rss/actualites-h{$intRubPublique}.xml");
T::contient('le flux RSS d\'une rubrique est servi', '<title>Articletemoinpublic</title>', $arrRep['body']);

H::reset();
$arrRep = H::req("/web/rss/reservecachee-h{$intRubPrivee}.xml");
T::contient('témoin : le flux d\'une rubrique privée est servi', '<rss', $arrRep['body']);
T::contientpas('mais ne livre pas ses articles', 'Articlesecretprive', $arrRep['body']);

T::section('Abonnement d\'un visiteur à une rubrique');
H::reset();
$arrRep = H::req("/articles/actualites-h{$intRubPublique}.html");
T::vrai('la rubrique est servie', $arrRep['code'] === 200, "HTTP {$arrRep['code']}");

/**
 * L'action du formulaire d'abonnement, telle que le socle la rend : celle du formulaire
 * qui porte le champ subscription_email.
 */
$strAction = '';
if (preg_match_all('/<form[^>]*\saction="([^"]*)"[^>]*>(.*?)<\/form>/is', $arrRep['body'], $arrForms, PREG_SET_ORDER))
{
    foreach ($arrForms as $arrForm) if (strpos($arrForm[2], 'subscription_email') !== false) $strAction = html_entity_decode($arrForm[1]);
}
T::vrai('le formulaire d\'abonnement est rendu', $strAction !== '', 'action='.$strAction);

/**
 * @return int nombre d'abonnements enregistrés pour le courriel de test
 */
function foweb_abonnements()
{
    $objRs = db()->query("SELECT COUNT(*) FROM ploopi_mod_webedit_heading_subscriber WHERE email = '".FOWEB_COURRIEL."'");

    return (int)$objRs->fetch_row()[0];
}

/**
 * Le dernier courriel envoyé à l'adresse de test, et le lien d'abonnement qu'il porte.
 *
 * @param string $strChemin 'subscribe' ou 'unsubscribe'
 * @return string chemin réécrit du lien (/subscribe/<jeton>/index.html), '' si absent
 */
function foweb_lien_courriel($strChemin)
{
    $arrCourriels = courriels();
    $strCourriel = (string)end($arrCourriels);
    if (strpos($strCourriel, FOWEB_COURRIEL) === false) return '';

    // Corps en quoted-printable possible : on le décode avant de chercher le lien
    $strCorps = quoted_printable_decode($strCourriel);

    return preg_match('#/('.$strChemin.'/[A-Za-z0-9_.-]+/index\.html)#', $strCorps, $arrMatches) ? '/'.$arrMatches[1] : '';
}

/**
 * Poste le formulaire d'abonnement, tel que la page le rend.
 */
function foweb_demander_abonnement()
{
    global $strAction, $intRubPublique;

    if ($strAction === '') return;

    $strUrl = strpos($strAction, 'http') === 0 ? $strAction : '/'.ltrim(preg_replace('#^\./#', '', $strAction), '/');
    H::req($strUrl, 'subscription_headingid='.$intRubPublique.'&subscription_email='.urlencode(FOWEB_COURRIEL));
}

/**
 * Double opt-in : le formulaire n'écrit rien, il envoie à l'adresse saisie un lien de
 * confirmation signé. Avant 2.8.7, il abonnait (ou désabonnait) aussitôt n'importe
 * quelle adresse.
 */
$booCourriels = cfg('courriels') != '';
courriels_vider();
foweb_demander_abonnement();
T::eq('la demande seule n\'abonne pas l\'adresse', 0, foweb_abonnements());

if (!$booCourriels) T::skip('courriel de confirmation', 'instance sans capture des courriels (tests/instance.sh)');
else
{
    $strLien = foweb_lien_courriel('subscribe');
    T::vrai('un courriel de confirmation part vers l\'adresse saisie, avec un lien signé', $strLien !== '', implode("\n----\n", courriels()));

    /**
     * Jeton retouché : un caractère de la signature changé.
     */
    $strFaux = preg_replace_callback('#\.([a-f0-9])#', function($m) { return '.'.($m[1] === 'a' ? 'b' : 'a'); }, $strLien, 1);
    H::reset();
    $arrRep = H::req($strFaux);
    T::eq('un lien retouché n\'abonne pas', 0, foweb_abonnements());
    T::contient('et le dit', 'pas valide', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));

    H::reset();
    $arrRep = H::req($strLien);
    T::eq('le lien du courriel abonne l\'adresse', 1, foweb_abonnements());
    T::contient('et le confirme', 'Votre abonnement a été validé', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));

    H::reset();
    H::req($strLien);
    T::eq('suivi deux fois, il n\'abonne qu\'une fois', 1, foweb_abonnements());

    /**
     * Une seconde demande pour une adresse abonnée ne la désabonne plus : elle lui
     * envoie un lien de désabonnement.
     */
    courriels_vider();
    foweb_demander_abonnement();
    T::eq('une nouvelle demande ne désabonne pas l\'adresse', 1, foweb_abonnements());
    $strLienDesabo = foweb_lien_courriel('unsubscribe');
    T::vrai('elle lui envoie un lien de désabonnement', $strLienDesabo !== '', implode("\n----\n", courriels()));

    H::reset();
    H::req($strLienDesabo);
    T::eq('ce lien désabonne l\'adresse', 0, foweb_abonnements());
}

T::section('Liens signés : échéance, instance, anciens liens');
/**
 * Les jetons sont fabriqués ici par les fonctions du module, avec la clé de
 * l'instance : la suite tourne sur la même configuration que le serveur.
 */
require_once PLOOPI_ROOT.'/modules/webedit/include/global.php';

db()->query("DELETE FROM ploopi_mod_webedit_heading_subscriber");
$strPerime = webedit_subscription_token('c', $intWebedit, $intRubPublique, FOWEB_COURRIEL, time() - 60);
H::reset();
H::req('/subscribe/'.$strPerime.'/index.html');
T::eq('un lien de confirmation périmé n\'abonne pas', 0, foweb_abonnements());

$strValide = webedit_subscription_token('c', $intWebedit, $intRubPublique, FOWEB_COURRIEL, time() + 3600);
H::reset();
H::req('/subscribe/'.$strValide.'/index.html');
T::eq('témoin : le même lien, non périmé, abonne', 1, foweb_abonnements());

/**
 * Un jeton de confirmation ne vaut pas désabonnement, et inversement.
 */
H::reset();
H::req('/unsubscribe/'.$strValide.'/index.html');
T::eq('un jeton de confirmation ne désabonne pas', 1, foweb_abonnements());

/**
 * Un lien de désabonnement d'un autre site (autre instance) laisse l'abonnement à
 * celui-ci.
 */
H::reset();
H::req('/unsubscribe/'.webedit_subscription_token('u', $intWebedit + 1000, 0, FOWEB_COURRIEL).'/index.html');
T::eq('le désabonnement d\'un autre site ne touche pas celui-ci', 1, foweb_abonnements());

/**
 * L'ancien lien md5(adresse) se calculait depuis l'adresse seule.
 */
H::reset();
$arrRep = H::req('/unsubscribe/'.md5(FOWEB_COURRIEL).'/index.html');
T::eq('l\'ancien lien md5(adresse) ne désabonne plus', 1, foweb_abonnements());
T::contient('il aboutit à un message explicite', 'pas valide', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));

H::reset();
H::req('/unsubscribe/'.webedit_subscription_token('u', $intWebedit, 0, FOWEB_COURRIEL).'/index.html');
T::eq('le lien de désabonnement signé retire l\'abonnement', 0, foweb_abonnements());

T::section('Caches publics : propres à chaque site');
/**
 * Avec _PLOOPI_USE_CACHE, le plan du site, les flux et le nuage sont servis depuis le
 * cache pendant cinq minutes. Un identifiant sans l'instance faisait servir à un site
 * le plan ou les flux d'un autre site de la même installation. Contrôle lexical : le
 * cache est désactivé dans l'instance de test. (Le cache d'article porte l'identifiant
 * d'article, propre à une instance.)
 */
$arrSansInstance = array();
foreach (array('backend.php', 'include/global.php') as $strFichier)
{
    preg_match_all('/new ploopi\\\\cache\((.*?), \d+\)/', file_get_contents(PLOOPI_ROOT.'/install/webedit/files/'.$strFichier), $arrMatches);
    foreach ($arrMatches[1] as $strId)
    {
        if (strpos($strId, 'webedit/article/') !== false) continue;
        if (strpos($strId, "['moduleid']") === false && strpos($strId, '$intModuleId') === false) $arrSansInstance[] = "{$strFichier} : {$strId}";
    }
}
T::eq('les identifiants de cache du plan, des flux et du nuage portent l\'instance', array(), $arrSansInstance);
T::contientpas('le nuage mis en cache ne dépend pas des droits de la session', 'allowedheading', file_get_contents(PLOOPI_ROOT.'/install/webedit/files/backend.php'));

T::section('Commentaires publics retirés');
/**
 * Ils dépendaient d'un captcha jamais livré : le formulaire était rendu, son envoi
 * échouait. Un article ouvert aux commentaires ne rend plus de formulaire.
 */
db()->query("UPDATE ploopi_mod_webedit_article SET comments_allowed = 1 WHERE id = {$intArtPublic}");
H::reset();
$arrRep = H::req("/articles/actualites-h{$intRubPublique}a{$intArtPublic}.html");
T::contient('témoin : l\'article est servi', 'Articletemoinpublic', $arrRep['body']);
T::contientpas('aucun formulaire de commentaire', 'name="comment_comment"', $arrRep['body']);
T::contientpas('ni de captcha', 'captcha_code', $arrRep['body']);
db()->query("UPDATE ploopi_mod_webedit_article SET comments_allowed = 0 WHERE id = {$intArtPublic}");

T::section('Nuage d\'étiquettes 3D : adresse du flux');
/**
 * Les gabarits n'embarquent plus le nuage 3D en Flash (lecteur abandonné par les
 * navigateurs) : le nuage HTML reste. Le flux XML demeure servi par webedit_backend ;
 * l'adresse qu'en donne display.php portait l'identifiant de l'espace à la place de
 * celui de l'instance.
 */
T::contientpas('le gabarit n\'embarque plus de nuage Flash', 'tagcloud.swf', $arrRep['body']);
T::vrai('l\'adresse du flux du nuage désigne l\'instance', (bool)preg_match("/'URL_XML_TAG3D'.*moduleid=\{\\\$_SESSION\['ploopi'\]\['moduleid'\]\}/", file_get_contents(PLOOPI_ROOT.'/install/webedit/files/display.php')));

T::section('Nuage d\'étiquettes 3D : identifiant d\'instance non typé');
/**
 * webedit_backend, publique, sert aussi le nuage d'étiquettes (backendtype=tagcloud3D),
 * dont la requête injectait ploopi_moduleid tel quel. Le témoin établit que le nuage est
 * servi ; la charge tente d'y faire remonter l'identifiant de connexion des comptes.
 */
H::reset();
$arrRep = H::req("/index.php?ploopi_op=webedit_backend&backendtype=tagcloud3D&ploopi_moduleid={$intWebedit}");
T::contient('témoin : le nuage d\'étiquettes est servi', 'etiquettepublique', $arrRep['body']);

/**
 * La requête se poursuit sur plusieurs lignes par des conditions sur a.timestp_* : la
 * charge les rend valides par une table dérivée plutôt que de les commenter.
 */
$strCharge = "0 UNION SELECT CONCAT('fuite', a.login), 0 FROM (SELECT login, 0 AS timestp_published, 0 AS timestp_unpublished FROM ploopi_user) a WHERE 1";
H::reset();
$arrRep = H::req('/index.php?ploopi_op=webedit_backend&backendtype=tagcloud3D&ploopi_moduleid='.rawurlencode($strCharge));
T::contientpas('aucune donnée de la base ne remonte par ploopi_moduleid', 'fuite', $arrRep['body']);

T::section('Page d\'étiquette : rien d\'un autre site');
/**
 * Une seconde instance de webedit, sans espace de rattachement : un autre site de la
 * même installation. Ses articles portent la même étiquette que l'article du site servi.
 * Sa rubrique est publique : seul le cloisonnement entre instances est en jeu.
 */
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
             VALUES ('webedit_autresite', {$intType}, 2, 1, 1, 1)");
$intAutreSite = db()->insert_id;
db()->query(sprintf(
    "INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                             content_type, visible, private, private_visible, sortmode,
                                             id_module, id_user, id_workspace)
     VALUES ('Autresite', '', 'corporate', 0, '', 0, 1, 'articles', 1, 0, 0, 0, %d, 2, 2)",
    $intAutreSite
));
$intRubAutreSite = db()->insert_id;
foweb_etiquette('etiquettepartagee', $intArtPublic);
foweb_etiquette('etiquettepartagee', foweb_article('Articleautresite', $intRubAutreSite, $intAutreSite), $intAutreSite);

H::reset();
$arrRep = H::req('/tags/etiquettepartagee.html');
T::contient('témoin : la page d\'étiquette liste l\'article du site', 'Articletemoinpublic', $arrRep['body']);
T::contientpas('pas celui de l\'autre site', 'Articleautresite', $arrRep['body']);

exit(T::bilan('21_module_webedit_frontoffice'));
