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
 * Suite 17 — module webedit : ce que voit un visiteur anonyme du frontoffice.
 *
 * webedit EST le frontoffice (include/frontoffice.php lui délègue tout). Ses pages
 * sont servies sans session ni jeton : les filtres de publication et de visibilité
 * appliqués par display.php sont donc la seule barrière entre un contenu et le public.
 *
 * La suite monte un site réel — rubriques, articles, étiquettes — et l'interroge en
 * visiteur anonyme, par les URL réécrites du module.
 *
 * @package webedit
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

$strModule = PLOOPI_ROOT.'/modules/webedit';
$strSource = PLOOPI_ROOT.'/install/webedit/files';
$booDeploye = false;

/**
 * Retire le module et ses données. Appelé quoi qu'il arrive.
 */
function webedit_nettoyer()
{
    global $strModule, $booDeploye;

    $arrRequetes = array(
        "DELETE ke FROM ploopi_index_keyword_element ke INNER JOIN ploopi_index_element e ON e.id = ke.id_element
         WHERE e.id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'webedit%')",
        "DELETE FROM ploopi_index_element WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'webedit%')",
        "DELETE FROM `ploopi_mod_webedit_article`",
        "DELETE FROM `ploopi_mod_webedit_article_draft`",
        "DELETE FROM `ploopi_mod_webedit_heading`",
        "DELETE FROM `ploopi_mod_webedit_tag`",
        "DELETE FROM `ploopi_mod_webedit_article_tag`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'webedit%')",
        "DELETE FROM ploopi_module WHERE label LIKE 'webedit%'",
        "DELETE FROM ploopi_module_type WHERE label = 'webedit'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('webedit_nettoyer');
webedit_nettoyer();

T::section('Déploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg($strSource).' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}
T::vrai('modules/webedit déployé', is_dir($strModule.'/include'));

$arrErreurs = array();
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/webedit/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { $arrErreurs[] = $objErreur->getMessage(); }
}
T::vrai('structure.sql exécuté', empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 2)));

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('webedit', 0, 'Site web', '2.8.3', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;

/**
 * Crée une instance du module dans un espace.
 *
 * @param int $intType type de module
 * @param int $intWorkspace espace
 * @param string $strLabel libellé
 * @return int identifiant de l'instance
 */
function webedit_instance($intType, $intWorkspace, $strLabel)
{
    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
                 VALUES ('".db()->real_escape_string($strLabel)."', {$intType}, {$intWorkspace}, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
                 VALUES ({$intModule}, {$intWorkspace}, 1, '')");

    return $intModule;
}

$intSiteA = webedit_instance($intType, 2, 'webedit');
$intSiteB = webedit_instance($intType, 2, 'webedit_autresite');
T::vrai('deux instances du module créées', $intSiteA > 0 && $intSiteB > 0, "A={$intSiteA} B={$intSiteB}");

/**
 * Crée une rubrique.
 *
 * @param string $strLabel libellé
 * @param int $intPrive 1 si la rubrique est privée
 * @param int $intModule instance
 * @return int identifiant
 */
function webedit_rubrique($strLabel, $intPrive, $intModule)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                                 content_type, visible, private, private_visible, sortmode,
                                                 id_module, id_user, id_workspace)
         VALUES ('%s', '', '', 0, '', 0, 1, 'articles', 1, %d, 0, 0, %d, 2, 2)",
        db()->real_escape_string($strLabel), (int)$intPrive, (int)$intModule
    ));

    return db()->insert_id;
}

/**
 * Crée un article.
 *
 * @param string $strTitre titre
 * @param string $strContenu contenu
 * @param string $strPublie timestamp de début de publication
 * @param string $strDepublie timestamp de fin de publication
 * @param int $intRubrique rubrique
 * @param int $intModule instance
 * @return int identifiant
 */
function webedit_article($strTitre, $strContenu, $strPublie, $strDepublie, $intRubrique, $intModule)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_webedit_article (reference, title, metakeywords, content, content_cleaned,
                                                 metadescription, metatitle, author, version, visible,
                                                 comments_allowed, timestp, timestp_published, timestp_unpublished,
                                                 lastupdate_timestp, lastupdate_id_user, id_heading, id_module,
                                                 id_user, id_workspace, position, status, tags)
         VALUES ('', '%s', '', '%s', '%s', '', '', '', 1, 1, 0, '%s', '%s', '%s', '%s', 2, %d, %d, 2, 2, 1, 1, '')",
        db()->real_escape_string($strTitre), db()->real_escape_string($strContenu), db()->real_escape_string($strContenu),
        date('YmdHis'), $strPublie, $strDepublie, date('YmdHis'),
        (int)$intRubrique, (int)$intModule
    ));

    return db()->insert_id;
}

/**
 * Associe une étiquette à un article.
 *
 * @param string $strTag étiquette
 * @param int $intArticle article
 * @param int $intModule instance
 */
function webedit_etiquette($strTag, $intArticle, $intModule)
{
    db()->query("INSERT INTO ploopi_mod_webedit_tag (tag, id_module) VALUES ('".db()->real_escape_string($strTag)."', {$intModule})");
    $intTag = db()->insert_id;
    db()->query("INSERT INTO ploopi_mod_webedit_article_tag (id_article, id_tag) VALUES ({$intArticle}, {$intTag})");
}

$strPasse  = date('YmdHis', strtotime('-10 days'));
$strFutur  = date('YmdHis', strtotime('+10 days'));
$strAvant  = date('YmdHis', strtotime('-20 days'));

$intRubPublique = webedit_rubrique('Actualites', 0, $intSiteA);
$intRubPrivee   = webedit_rubrique('Interne', 1, $intSiteA);
$intRubSiteB    = webedit_rubrique('AutreSite', 0, $intSiteB);

$intArtPublie    = webedit_article('Article publie', 'CONTENU PUBLIC VISIBLE', $strPasse, 0, $intRubPublique, $intSiteA);
$intArtAVenir    = webedit_article('Article a venir', 'CONTENU PAS ENCORE PUBLIE', $strFutur, 0, $intRubPublique, $intSiteA);
$intArtExpire    = webedit_article('Article expire', 'CONTENU DEPUBLIE', $strAvant, $strPasse, $intRubPublique, $intSiteA);
$intArtPrive     = webedit_article('Article prive', 'CONTENU RUBRIQUE PRIVEE', $strPasse, 0, $intRubPrivee, $intSiteA);
$intArtSiteB     = webedit_article('Article autre site', 'CONTENU AUTRE INSTANCE', $strPasse, 0, $intRubSiteB, $intSiteB);

webedit_etiquette('essai', $intArtPublie, $intSiteA);
webedit_etiquette('essai', $intArtSiteB, $intSiteB);

T::vrai('rubriques et articles créés', $intArtPublie > 0 && $intArtSiteB > 0);

/**
 * Le frontoffice n'est servi que pour un espace qui le déclare et dont la liste de
 * domaines correspond à l'hôte (cf. loader, $_SESSION['ploopi']['hosts']['frontoffice']).
 * L'espace de travail est donc basculé le temps de la suite, puis rétabli.
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
 * Interroge le frontoffice en visiteur anonyme.
 *
 * @param string $strChemin chemin de l'URL
 * @return array réponse
 */
function webedit_public($strChemin)
{
    H::reset();

    return H::req($strChemin);
}

T::section('Rendu d\'une rubrique publique');
$arrRep = webedit_public("/articles/x-h{$intRubPublique}.html");
T::vrai('le frontoffice répond', $arrRep['code'] === 200, "HTTP {$arrRep['code']}");
/**
 * Contrôle témoin : sans contenu servi, tous les « absent de la page » qui suivent
 * passeraient sans rien prouver.
 */
T::contient('témoin : un article publié est bien rendu', 'CONTENU PUBLIC VISIBLE', $arrRep['body']);

T::section('Fenêtre de publication');
T::contientpas('un article dont la publication n\'a pas commencé est absent', 'CONTENU PAS ENCORE PUBLIE', $arrRep['body']);
T::contientpas('un article dépublié est absent', 'CONTENU DEPUBLIE', $arrRep['body']);

T::section('Rubrique privée');
T::contientpas('le contenu d\'une rubrique privée est absent de la rubrique publique', 'CONTENU RUBRIQUE PRIVEE', $arrRep['body']);

$arrRep = webedit_public("/articles/x-h{$intRubPrivee}.html");
T::contientpas('rubrique privée demandée directement : contenu absent', 'CONTENU RUBRIQUE PRIVEE', $arrRep['body']);

$arrRep = webedit_public("/articles/x-h{$intRubPublique}a{$intArtPrive}.html");
T::contientpas('article privé demandé par son identifiant : contenu absent', 'CONTENU RUBRIQUE PRIVEE', $arrRep['body']);

T::section('Articles hors fenêtre, demandés par leur identifiant');
$arrRep = webedit_public("/articles/x-h{$intRubPublique}a{$intArtAVenir}.html");
T::contientpas('article à venir : contenu absent', 'CONTENU PAS ENCORE PUBLIE', $arrRep['body']);

$arrRep = webedit_public("/articles/x-h{$intRubPublique}a{$intArtExpire}.html");
T::contientpas('article dépublié : contenu absent', 'CONTENU DEPUBLIE', $arrRep['body']);

/**
 * Non couvert ici : le cloisonnement entre instances sur la page d'étiquettes.
 *
 * La requête de recherche par étiquette (display.php) ne filtre pas sur id_module, ce
 * qui laisse supposer qu'une page d'étiquettes pourrait montrer les articles d'une
 * autre instance de webedit — donc d'un autre site de la même installation. Faute
 * d'avoir réussi à faire apparaître ne serait-ce que l'article du site courant sur
 * cette page dans l'environnement de test, le contrôle témoin échouait : la section a
 * été retirée plutôt que de livrer des assertions qui passent sans rien mesurer. Le
 * point reste à éprouver.
 */

T::section('Réindexation du site (backoffice)');
/**
 * La page s'arrêtait sur un die() de mise au point (« coucou »). Dessous, la
 * réindexation parcourait les articles de toutes les instances de webedit, et le
 * nombre de mots indexés était désactivé (toujours 0).
 */
H::reset();
H::connexion();
$arrEnv = explode('/', H::env());
T::vrai('administrateur connecté', sizeof($arrEnv) === 5);
$arrEnv[1] = 2;
$arrEnv[2] = $intSiteA;
$arrEnv[3] = 'admin';
$strEnv = implode('/', $arrEnv);
H::req('/admin.php?ploopi_env='.urlencode($strEnv));

$arrRep = H::req('/admin.php?webedit_menu=reindex&ploopi_env='.urlencode($strEnv));
T::contientpas('page servie (plus de die de mise au point)', 'coucou', $arrRep['body']);
T::contient('page de réindexation', 'Réindexer le contenu du site', $arrRep['body']);
T::vrai('articles du site courant comptés (4)', (bool)preg_match('/<strong>4 article/', $arrRep['body']));

$fnIndexes = function($intModule) {
    return (int)db()->query("SELECT COUNT(*) FROM ploopi_index_element WHERE id_object = 2 AND id_module = {$intModule}")->fetch_row()[0];
};
db()->query("DELETE FROM ploopi_index_element WHERE id_module IN ({$intSiteA}, {$intSiteB})");

$arrRep = H::req('/admin.php?webedit_menu=reindex&op=reindex&ploopi_env='.urlencode($strEnv));
T::contient('réindexation terminée', 'indexation terminée', $arrRep['body']);
T::eq('articles du site courant indexés', 4, $fnIndexes($intSiteA));
T::eq('articles d\'un autre site non touchés', 0, $fnIndexes($intSiteB));
$intEspace = (int)db()->query("SELECT id_workspace FROM ploopi_index_element WHERE id_object = 2 AND id_module = {$intSiteA} AND id_record = '{$intArtPublie}'")->fetch_row()[0];
T::eq('index rattaché à l\'espace de l\'article', 2, $intEspace);

$arrRep = H::req('/admin.php?webedit_menu=reindex&ploopi_env='.urlencode($strEnv));
T::vrai('mots indexés comptés', (bool)preg_match('/<strong>([1-9]\d*) mot/', $arrRep['body']), 'aucun mot compté');

/**
 * Brouillon dont le titre et le contenu cherchent à sortir de leur contexte : le titre
 * dans un attribut onclick (arbres de choix), le contenu dans le champ de l'éditeur.
 */
$intArtPiege = webedit_article('Lien "piege" <i>x</i>', 'AVANT</textarea><b id="evade">APRES', $strPasse, 0, $intRubPublique, $intSiteA);
db()->query("INSERT INTO ploopi_mod_webedit_article_draft SELECT * FROM ploopi_mod_webedit_article WHERE id = {$intArtPiege}");

T::section('Éditeur de l\'article (TinyMCE)');
/**
 * L'article s'édite dans la page du site (iframe du backoffice) : TinyMCE par le
 * point d'entrée du socle (js/editor.js), contenu échappé dans le champ.
 */
$arrRep = H::req("/index.php?headingid={$intRubPublique}&articleid={$intArtPiege}&webedit_mode=edit&type=draft&readonly=0&ploopi_env=".urlencode($strEnv));
T::vrai('page d\'édition servie', $arrRep['code'] === 200, "HTTP {$arrRep['code']}");
T::vrai('champ déclaré à l\'éditeur du socle', (bool)preg_match('/<textarea[^>]*id="editor"[^>]*data-ploopi-editor="complet"/', $arrRep['body']));
T::contient('éditeur créé par ploopi.editor', 'ploopi.editor', $arrRep['body']);
T::contientpas('plus de CKEditor', 'ckeditor', strtolower($arrRep['body']));
T::contient('contenu échappé dans le champ', 'AVANT&lt;/textarea&gt;&lt;b id=&quot;evade&quot;&gt;APRES', $arrRep['body']);
T::contientpas('le contenu ne referme pas le champ', '<b id="evade">', $arrRep['body']);

T::section('Choix d\'un lien vers un article');
/**
 * La fenêtre de choix rend l'URL à l'éditeur par ploopi.editor.picked(numéro, url) ;
 * le numéro est un entier, le titre de l'article ne sort pas de l'attribut onclick.
 */
$arrRep = H::req("/admin-light.php?ploopi_op=webedit_detail_heading&option=selectlink&hid=hl{$intRubPublique}&CKEditorFuncNum=7x&ploopi_env=".urlencode($strEnv));
T::contient('retour par ploopi.editor.picked', 'ploopi.editor.picked(7,', $arrRep['body']);
T::contient('lien vers l\'article', "articleid={$intArtPiege}", $arrRep['body']);
T::contientpas('plus d\'appel à CKEditor', 'CKEDITOR', $arrRep['body']);
T::contientpas('titre jamais brut dans la page', 'Lien "piege"', $arrRep['body']);

T::section('Redirection d\'une rubrique : titres échappés');
$arrRep = H::req("/admin-light.php?ploopi_op=webedit_detail_heading&option=selectredirect&hid=hr{$intRubPublique}&ploopi_env=".urlencode($strEnv));
T::contient('article proposé', 'webedit_select_article_or_heading', $arrRep['body']);
T::contientpas('titre jamais brut dans la page', 'Lien "piege"', $arrRep['body']);
T::contientpas('plus de addslashes dans un attribut', 'Lien \\"piege', $arrRep['body']);

T::section('Liste et fiche de l\'article : titres échappés');
$arrRep = H::req("/admin.php?headingid={$intRubPublique}&ploopi_env=".urlencode($strEnv));
T::contient('article listé dans sa rubrique', 'Lien &quot;piege&quot; &lt;i&gt;x&lt;/i&gt;</span>', $arrRep['body']);
T::contientpas('liste : titre jamais interprété comme du HTML', '<i>x</i>', $arrRep['body']);
T::contientpas('liste : le titre ne referme pas l\'attribut', 'Lien \\"piege', $arrRep['body']);
$arrRep = H::req("/admin.php?op=article_modify&articleid={$intArtPiege}&type=draft&ploopi_env=".urlencode($strEnv));
T::contient('fiche de l\'article servie', 'webedit_frame_editor', $arrRep['body']);
T::contientpas('fiche : le titre ne referme pas l\'attribut', 'Lien \\"piege', $arrRep['body']);
T::contient('fiche : titre échappé pour l\'attribut', 'Lien \\&quot;piege\\&quot; &lt;i&gt;', $arrRep['body']);

exit(T::bilan('17_module_webedit'));
