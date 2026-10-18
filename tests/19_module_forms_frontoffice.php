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
 * Suite 19 — module forms en frontoffice : un visiteur peut-il soumettre un formulaire ?
 *
 * Un formulaire « cms » s'insère dans un article webedit comme objet WCE. Le visiteur,
 * anonyme, le remplit et le poste vers index.php?ploopi_op=forms_reply_save. Or les
 * opérations de module exigent un jeton anti-CSRF, que seul le backoffice émet, sauf si
 * le module les déclare dans include/publicops.php.
 *
 * La suite monte un site réel, rend l'article en visiteur anonyme, relève l'URL d'action
 * du formulaire telle que le socle la produit, y poste une réponse, et mesure la table
 * de données.
 *
 * @package forms
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

define('FOFORMS_VALEUR', 'REPONSE VISITEUR ANONYME');

$arrDeploye = array();

/**
 * Retire les deux modules et leurs données. Appelé quoi qu'il arrive.
 */
function foforms_nettoyer()
{
    global $arrDeploye;

    $objRs = db()->query("SHOW TABLES LIKE 'form\\_%'");
    while ($objRs && $arrLigne = $objRs->fetch_row()) db()->query("DROP TABLE IF EXISTS `{$arrLigne[0]}`");

    $arrRequetes = array(
        "DELETE FROM `ploopi_mod_forms_form`",
        "DELETE FROM `ploopi_mod_forms_field`",
        "DELETE FROM `ploopi_mod_webedit_article`",
        "DELETE FROM `ploopi_mod_webedit_heading`",
        "DELETE FROM ploopi_mb_wce_object WHERE id_module_type IN (SELECT id FROM ploopi_module_type WHERE label = 'forms')",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label IN ('forms', 'webedit'))",
        "DELETE FROM ploopi_module WHERE label IN ('forms', 'webedit')",
        "DELETE FROM ploopi_module_type WHERE label IN ('forms', 'webedit')"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    foreach ($arrDeploye as $strDossier) if (is_dir($strDossier)) exec('rm -rf '.escapeshellarg($strDossier));
}

register_shutdown_function('foforms_nettoyer');
foforms_nettoyer();

T::section('Déploiement des modules');

/**
 * Déploie un module depuis install/, exécute sa structure et enregistre une instance
 * dans l'espace 2.
 *
 * @param string $strModule nom du module
 * @param string $strVersion version déclarée
 * @return array [type de module, instance]
 */
function foforms_deployer($strModule, $strVersion)
{
    global $arrDeploye;

    $strCible = PLOOPI_ROOT."/modules/{$strModule}";
    if (!is_dir($strCible))
    {
        exec('cp -r '.escapeshellarg(PLOOPI_ROOT."/install/{$strModule}/files").' '.escapeshellarg($strCible), $arrSortie, $intRc);
        if ($intRc === 0) $arrDeploye[] = $strCible;
    }

    foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT."/install/{$strModule}/structure.sql")) as $strRequete)
    {
        if (trim($strRequete) === '') continue;
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
                 VALUES ('{$strModule}', 0, '{$strModule}', '{$strVersion}', 'Ovensia', '".date('YmdHis')."')");
    $intType = db()->insert_id;
    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
                 VALUES ('{$strModule}', {$intType}, 2, 1, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
                 VALUES ({$intModule}, 2, 1, '')");

    return array($intType, $intModule);
}

list($intTypeWebedit, $intWebedit) = foforms_deployer('webedit', '2.8.3');
list($intTypeForms, $intForms) = foforms_deployer('forms', '3.8.1');
T::vrai('webedit et forms déployés', is_dir(PLOOPI_ROOT.'/modules/webedit/include') && is_dir(PLOOPI_ROOT.'/modules/forms/classes'));
T::vrai('instances enregistrées', $intWebedit > 0 && $intForms > 0, "webedit={$intWebedit} forms={$intForms}");

// L'objet WCE déclaré par install/forms/mb.xml
db()->query("INSERT INTO ploopi_mb_wce_object (id, label, id_module_type, script, select_id, select_label, select_table)
             VALUES (1, 'Affichage Formulaire', {$intTypeForms}, '?op=\\'display\\'', 'id', 'label', 'ploopi_mod_forms_form')");

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
 * Crée un formulaire, son unique champ texte et sa table de données.
 *
 * @param string $strLabel libellé
 * @param string $strType typeform (cms : public, app : backoffice seulement)
 * @param int $intModule instance
 * @return array [identifiant du formulaire, identifiant du champ]
 */
function foforms_formulaire($strLabel, $strType, $intModule)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_forms_form (label, description, email_from, email, typeform, option_modify, option_view,
                                            nbline, autobackup, autobackup_date, cms_response, id_user, id_workspace, id_module)
         VALUES ('%s', '', '', '', '%s', 'nobody', 'global', 25, 0, 0, 'MERCI POUR VOTRE REPONSE', 2, 2, %d)",
        db()->real_escape_string($strLabel), db()->real_escape_string($strType), (int)$intModule
    ));
    $intForm = db()->insert_id;

    db()->query("INSERT INTO ploopi_mod_forms_field (id_form, name, fieldname, type, format, `values`, formula, description,
                                                     xhtmlcontent, xhtmlcontent_cleaned, position, style_field, style_form,
                                                     defaultvalue, option_formview, option_arrayview, option_exportview)
                 VALUES ({$intForm}, 'Votre message', 'champ_1', 'text', 'string', '', '', '', '', '', 1, '', '', '', 1, 1, 1)");
    $intField = db()->insert_id;

    db()->query("CREATE TABLE `form_{$intModule}_{$intForm}` (
                    `#id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `date_validation` BIGINT(14) DEFAULT 0, `ip` VARCHAR(16) DEFAULT '',
                    `user_id` INT(10) UNSIGNED DEFAULT 0, `user_login` VARCHAR(255) DEFAULT '',
                    `user_firstname` VARCHAR(255) DEFAULT '', `user_lastname` VARCHAR(255) DEFAULT '',
                    `workspace_id` INT(10) UNSIGNED DEFAULT 0, `workspace_label` VARCHAR(255) DEFAULT '',
                    `workspace_code` VARCHAR(255) DEFAULT '', `champ_1` VARCHAR(255) DEFAULT NULL
                 ) ENGINE=MyISAM");

    return array($intForm, $intField);
}

/**
 * @param int $intForm formulaire
 * @param int $intModule instance
 * @return array réponses enregistrées
 */
function foforms_reponses($intForm, $intModule)
{
    $arrReponses = array();
    $objRs = db()->query("SELECT * FROM `form_{$intModule}_{$intForm}`");
    while ($objRs && $arrLigne = $objRs->fetch_assoc()) $arrReponses[] = $arrLigne;

    return $arrReponses;
}

list($intFormCms, $intFieldCms) = foforms_formulaire('Contact public', 'cms', $intForms);
list($intFormApp, $intFieldApp) = foforms_formulaire('Formulaire interne', 'app', $intForms);

$strPasse = date('YmdHis', strtotime('-10 days'));
db()->query("INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                                     content_type, visible, private, private_visible, sortmode,
                                                     id_module, id_user, id_workspace)
             VALUES ('Contact', '', '', 0, '', 0, 1, 'articles', 1, 0, 0, 0, {$intWebedit}, 2, 2)");
$intRubrique = db()->insert_id;

$strContenu = "<p>FORMULAIRE CI-DESSOUS</p>[[1,{$intForms},{$intFormCms}]]";
db()->query(sprintf(
    "INSERT INTO ploopi_mod_webedit_article (reference, title, metakeywords, content, content_cleaned,
                                             metadescription, metatitle, author, version, visible,
                                             comments_allowed, timestp, timestp_published, timestp_unpublished,
                                             lastupdate_timestp, lastupdate_id_user, id_heading, id_module,
                                             id_user, id_workspace, position, status, tags)
     VALUES ('', 'Contact', '', '%s', '%s', '', '', '', 1, 1, 0, '%s', '%s', 0, '%s', 2, %d, %d, 2, 2, 1, 1, '')",
    db()->real_escape_string($strContenu), db()->real_escape_string($strContenu),
    date('YmdHis'), $strPasse, date('YmdHis'), $intRubrique, $intWebedit
));
$intArticle = db()->insert_id;
T::vrai('formulaires, rubrique et article créés', $intFormCms > 0 && $intFormApp > 0 && $intArticle > 0);

T::section('Rendu de l\'article en visiteur anonyme');
H::reset();
$arrRep = H::req("/articles/contact-h{$intRubrique}a{$intArticle}.html");
T::vrai('l\'article est servi', $arrRep['code'] === 200, "HTTP {$arrRep['code']}");
T::contient('témoin : le contenu de l\'article est rendu', 'FORMULAIRE CI-DESSOUS', $arrRep['body']);

// Le formulaire du module (id forms_form_<id>), pas un autre formulaire de la page (recherche du gabarit…)
preg_match('/<form id="forms_form_'.$intFormCms.'"[^>]*\saction="([^"]*)"/i', $arrRep['body'], $arrMatches);
$strAction = isset($arrMatches[1]) ? html_entity_decode($arrMatches[1]) : '';
T::vrai('le formulaire est rendu dans l\'article', $strAction !== '', 'action='.$strAction);

T::section('Soumission anonyme d\'un formulaire public');
if ($strAction !== '')
{
    $strUrl = strpos($strAction, 'http') === 0 ? $strAction : '/'.ltrim(preg_replace('#^\./#', '', $strAction), '/');
    H::req($strUrl, "field_{$intFieldCms}=".urlencode(FOFORMS_VALEUR));
}
$arrReponses = foforms_reponses($intFormCms, $intForms);
T::eq('la réponse du visiteur est enregistrée', FOFORMS_VALEUR, $arrReponses[0]['champ_1'] ?? null);

T::section('Impression d\'un formulaire public par un visiteur');
/**
 * Le bouton « Imprimer » d'un formulaire public appelle forms_print, que le
 * répartiteur ignorait pour un visiteur faute de déclaration dans publicops.php.
 * Hors backoffice, l'opération n'imprime qu'un formulaire cms, saisie en cours seule.
 */
H::reset();
$arrRep = H::req("/index-light.php?ploopi_op=forms_print&forms_id={$intFormCms}&record_id=1");
T::contient('formulaire public : la page d\'impression est servie', 'window.print()', $arrRep['body']);
$arrRep = H::req("/index-light.php?ploopi_op=forms_print&forms_id={$intFormApp}");
T::contientpas('formulaire interne : rien n\'est imprimé', 'window.print()', $arrRep['body']);

T::section('Formulaire interne posté anonymement');
/**
 * Par index-quick.php, la session anonyme n'a pas encore de mode : ni backoffice ni
 * frontoffice. Le contrôle « formulaire public » doit valoir hors backoffice, pas
 * seulement en frontoffice. Témoin : le formulaire public, par le même chemin, passe.
 */
db()->query("TRUNCATE TABLE `form_{$intForms}_{$intFormCms}`");
H::reset();
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormCms}", "field_{$intFieldCms}=".urlencode('PAR INDEX-QUICK'));
T::eq('témoin : le formulaire public est accepté par ce chemin', 1, sizeof(foforms_reponses($intFormCms, $intForms)));

H::reset();
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}", "field_{$intFieldApp}=".urlencode('INTRUSION ANONYME'));
T::eq('un formulaire interne refuse la réponse anonyme', 0, sizeof(foforms_reponses($intFormApp, $intForms)));

H::reset();
H::req('/index.php?ploopi_op=forms_reply_save&forms_id='.$intFormApp, "field_{$intFieldApp}=".urlencode('INTRUSION ANONYME'));
T::eq('y compris par le frontoffice', 0, sizeof(foforms_reponses($intFormApp, $intForms)));

T::section('Saisie en backoffice : le jeton reste exigé');
/**
 * Déclarer forms_reply_save publique la dispense du jeton dans le répartiteur. En
 * backoffice, où elle écrit avec les droits de la session, elle doit donc l'exiger
 * elle-même : sinon un site tiers la déclenche avec la session d'un administrateur.
 */
H::reset();
H::connexion();
$arrEnv = explode('/', H::env());
$arrEnv[1] = 2;
$arrEnv[2] = $intForms;
$arrEnv[3] = 'public';
$strEnv = implode('/', $arrEnv);
H::req('/admin.php?ploopi_env='.urlencode($strEnv));

H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}&ploopi_env=".urlencode($strEnv), "field_{$intFieldApp}=".urlencode('AVEC JETON'));
T::eq('témoin : avec le jeton, l\'administrateur saisit une réponse', 1, sizeof(foforms_reponses($intFormApp, $intForms)));

H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}", "field_{$intFieldApp}=".urlencode('SANS JETON'));
T::eq('sans le jeton, aucune réponse ajoutée', 1, sizeof(foforms_reponses($intFormApp, $intForms)));

T::section('Notification après une saisie par index-quick.php');
/**
 * Un formulaire partagé avec un utilisateur lui notifie chaque réponse (ticket).
 * ticket::send() instancie le moteur de gabarits, que seuls backoffice.php et
 * light.php chargent : par index-quick.php, la réponse était enregistrée puis
 * l'opération s'arrêtait sur « Class Template not found », sans notification.
 */
db()->query("INSERT INTO ploopi_share (id, id_module, id_record, id_object, type_share, id_share, id_module_type)
             SELECT IFNULL(MAX(id), 0) + 1, {$intForms}, {$intFormApp}, 1, 'user', 2, 0 FROM ploopi_share");
$compter_tickets = function () { return (int)db()->query("SELECT COUNT(*) c FROM ploopi_ticket WHERE title = 'Formulaire interne'")->fetch_assoc()['c']; };
$intAvant = $compter_tickets();
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}&ploopi_env=".urlencode($strEnv), "field_{$intFieldApp}=".urlencode('AVEC NOTIFICATION'));
T::eq('la réponse saisie par index-quick.php est notifiée', $intAvant + 1, $compter_tickets());
db()->query("DELETE FROM ploopi_share WHERE id_module = {$intForms} AND id_record = {$intFormApp}");
db()->query("DELETE FROM ploopi_ticket WHERE title = 'Formulaire interne'");
db()->query("TRUNCATE TABLE `form_{$intForms}_{$intFormApp}`");
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}&ploopi_env=".urlencode($strEnv), "field_{$intFieldApp}=".urlencode('AVEC JETON'));

T::section('Règles d\'affichage appliquées à l\'enregistrement');
/**
 * La date de publication et l'option « une seule réponse » n'étaient vérifiées qu'à
 * l'affichage du formulaire : une réponse postée directement les ignorait.
 */
db()->query("UPDATE ploopi_mod_forms_form SET option_onlyone = 1 WHERE id = {$intFormApp}");
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}&ploopi_env=".urlencode($strEnv), "field_{$intFieldApp}=".urlencode('SECONDE REPONSE'));
T::eq('une seule réponse : la seconde est refusée', 1, sizeof(foforms_reponses($intFormApp, $intForms)));
db()->query("UPDATE ploopi_mod_forms_form SET option_onlyone = 0 WHERE id = {$intFormApp}");
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormApp}&ploopi_env=".urlencode($strEnv), "field_{$intFieldApp}=".urlencode('SECONDE REPONSE'));
T::eq('témoin : sans l\'option, la seconde est acceptée', 2, sizeof(foforms_reponses($intFormApp, $intForms)));

db()->query("TRUNCATE TABLE `form_{$intForms}_{$intFormCms}`");
db()->query("UPDATE ploopi_mod_forms_form SET pubdate_end = '".date('YmdHis', strtotime('-1 day'))."' WHERE id = {$intFormCms}");
H::reset();
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormCms}", "field_{$intFieldCms}=".urlencode('APRES PUBLICATION'));
T::eq('formulaire dépublié : réponse anonyme refusée', 0, sizeof(foforms_reponses($intFormCms, $intForms)));
db()->query("UPDATE ploopi_mod_forms_form SET pubdate_end = 0 WHERE id = {$intFormCms}");
H::req("/index-quick.php?ploopi_op=forms_reply_save&forms_id={$intFormCms}", "field_{$intFieldCms}=".urlencode('PENDANT PUBLICATION'));
T::eq('témoin : formulaire publié, réponse acceptée', 1, sizeof(foforms_reponses($intFormCms, $intForms)));

exit(T::bilan('19_module_forms_frontoffice'));
