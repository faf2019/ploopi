<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Suite 39 — écrans de system refondus pour ploopi4 (onglets d'un espace et d'un
 * groupe, niveau Système, Mon espace) et contrôles d'accès corrigés à cette occasion :
 * opérations sur les modules, paramètres et rôles d'un autre espace, rattachements,
 * niveau d'un groupe, suppression en masse depuis l'annuaire, champs protégés de la
 * fiche et du profil, messages d'autrui ; sauvegarde SQL réparée.
 *
 * L'espace 2 passe en ploopi4 le temps de la suite, puis reprend son habillage.
 * Données de test préfixées « p4ecr » (sans souligné : l'annuaire refuse « _ »).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();
amorcer_socle();

define('E4_MDP', 'Ploopi4!Ecrans2026');
define('E4_PREFIXE', 'p4ecr');

$arrTemplate = db()->query("SELECT template FROM ploopi_workspace WHERE id = 2")->fetch_assoc();
$strTemplateOrigine = $arrTemplate ? $arrTemplate['template'] : '';

function e4_nettoyer()
{
    global $strTemplateOrigine;

    $strUsers = "SELECT id FROM ploopi_user WHERE login LIKE '".E4_PREFIXE."%'";
    $strTickets = "SELECT id FROM ploopi_ticket WHERE title LIKE '".E4_PREFIXE."%'";
    $strModules = "SELECT id FROM ploopi_module WHERE label LIKE '".E4_PREFIXE."%'";
    $strRoles = "SELECT id FROM ploopi_role WHERE label LIKE '".E4_PREFIXE."%'";

    foreach(array('ploopi_ticket_dest', 'ploopi_ticket_status', 'ploopi_ticket_watch') as $strTable) db()->query("DELETE FROM {$strTable} WHERE id_ticket IN (SELECT id FROM ({$strTickets}) t)");
    $strAnnotations = "SELECT id FROM ploopi_annotation WHERE content LIKE '".E4_PREFIXE."%'";
    db()->query("DELETE FROM ploopi_annotation_tag WHERE id_annotation IN (SELECT id FROM ({$strAnnotations}) a)");
    db()->query("DELETE FROM ploopi_annotation WHERE content LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_tag WHERE tag LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_ticket WHERE title LIKE '".E4_PREFIXE."%'");
    $strIndex = "SELECT id FROM ploopi_index_element WHERE label LIKE '".E4_PREFIXE."%'";
    foreach(array('ploopi_index_keyword_element', 'ploopi_index_stem_element', 'ploopi_index_phonetic_element') as $strTable) db()->query("DELETE FROM {$strTable} WHERE id_element IN (SELECT id FROM ({$strIndex}) i)");
    db()->query("DELETE FROM ploopi_index_element WHERE label LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_workspace_user_role WHERE id_role IN (SELECT id FROM ({$strRoles}) r)");
    db()->query("DELETE FROM ploopi_role_action WHERE id_role IN (SELECT id FROM ({$strRoles}) r)");
    db()->query("DELETE FROM ploopi_role WHERE label LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_param_workspace WHERE id_module IN (SELECT id FROM ({$strModules}) m)");
    db()->query("DELETE FROM ploopi_param_user WHERE id_module IN (SELECT id FROM ({$strModules}) m)");
    db()->query("DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ({$strModules}) m)");
    db()->query("DELETE FROM ploopi_module WHERE label LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_group_user WHERE id_user IN (SELECT id FROM ({$strUsers}) u)");
    db()->query("DELETE FROM ploopi_group_user WHERE id_group IN (SELECT id FROM ploopi_group WHERE label LIKE '".E4_PREFIXE."%') OR id_user = 0");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_user IN (SELECT id FROM ({$strUsers}) u)");
    db()->query("DELETE FROM ploopi_user WHERE login LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_workspace_group WHERE id_group IN (SELECT id FROM ploopi_group WHERE label LIKE '".E4_PREFIXE."%')");
    db()->query("DELETE FROM ploopi_group WHERE label LIKE '".E4_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_workspace IN (SELECT id FROM ploopi_workspace WHERE label LIKE '".E4_PREFIXE."%')");
    db()->query("DELETE FROM ploopi_workspace WHERE label LIKE '".E4_PREFIXE."%'");
    if ($strTemplateOrigine !== null) db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strTemplateOrigine)."' WHERE id = 2");
}

register_shutdown_function('e4_nettoyer');
e4_nettoyer();

/**
 * Crée un utilisateur, rattaché à un espace (niveau donné) et/ou à un groupe
 *
 * @return int identifiant
 */
function e4_utilisateur($strNom, $intWorkspace = 0, $intAdminlevel = _PLOOPI_ID_LEVEL_USER, $intGroupe = 0)
{
    $strLogin = E4_PREFIXE.$strNom;
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Test', 'Ecrans', '%s@example.test', '%s', 0)",
        db()->real_escape_string($strLogin),
        db()->real_escape_string(ploopi\user::generate_hash(E4_MDP, $strLogin)),
        db()->real_escape_string($strLogin),
        date('YmdHis')
    ));
    $intUser = db()->insert_id;
    if ($intWorkspace) db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, {$intWorkspace}, 1, {$intAdminlevel})");
    if ($intGroupe) db()->query("INSERT INTO ploopi_group_user (id_user, id_group) VALUES ({$intUser}, {$intGroupe})");
    return $intUser;
}

/** Suit une redirection interne comme un navigateur */
function e4_suivre($arrRep)
{
    if ($arrRep['code'] === 302 && preg_match('/Location: (\S+)/i', $arrRep['headers'], $arrM) && parse_url($arrM[1], PHP_URL_HOST) === parse_url(cfg('base_url'), PHP_URL_HOST))
    {
        $arrLoc = parse_url($arrM[1]);
        return H::req($arrLoc['path'].(empty($arrLoc['query']) ? '' : '?'.$arrLoc['query']));
    }
    return $arrRep;
}

/** Ouvre une session backoffice ; renvoie vrai si l'environnement (jeton compris) est établi */
function e4_connecter($strLogin, $strMotDePasse)
{
    H::reset();
    $arrRep = H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode($strMotDePasse));
    if ($arrRep['code'] !== 302) return false;
    e4_suivre($arrRep);
    for ($intEssai = 0; $intEssai < 4; $intEssai++)
    {
        // Jeton établi (un utilisateur simple arrive sur Mon espace, sans module courant)
        $arrEnv = explode('/', H::env());
        if (sizeof($arrEnv) === 5 && $arrEnv[0] !== '' && $arrEnv[4] !== '') return true;
    }
    return false;
}

/**
 * Environnement de la page courante, réglé sur le module system (espace 2) : administration
 * (menu Espaces de travail) ou Mon espace (action public)
 */
function e4_env($booMonEspace = false)
{
    $arrEnv = explode('/', H::env());
    if (sizeof($arrEnv) !== 5) return '';
    $arrEnv[0] = $booMonEspace ? _PLOOPI_MENU_MYWORKSPACE : _PLOOPI_MENU_WORKSPACES;
    $arrEnv[1] = 2;
    $arrEnv[2] = _PLOOPI_MODULE_SYSTEM;
    $arrEnv[3] = $booMonEspace ? 'public' : 'admin';
    return implode('/', $arrEnv);
}

/** Page du module system (redirection interne suivie) */
function e4_page($strQuery, $mixPost = null, $booMonEspace = false)
{
    return e4_suivre(H::req('/admin.php?'.$strQuery.'&ploopi_env='.urlencode(e4_env($booMonEspace)), $mixPost));
}

/** Opération du module system */
function e4_op($strOp, $strQuery = '', $mixPost = null, $booMonEspace = false)
{
    return H::req("/admin-light.php?ploopi_op={$strOp}&ploopi_env=".urlencode(e4_env($booMonEspace)).$strQuery, $mixPost);
}

/** Valeur d'un champ (première ligne) */
function e4_val($strSql)
{
    $arrL = db()->query($strSql)->fetch_row();
    return $arrL ? $arrL[0] : null;
}

/** Écran complet : shell ploopi4 rendu jusqu'au bout et en-tête de l'écran */
function e4_ecran($strLabel, $arrRep, $strMarqueur = 'class="p4-page-head p4-ui"')
{
    $booComplet = $arrRep['code'] === 200 && strpos($arrRep['body'], 'id="pagecontent"') !== false && strpos($arrRep['body'], '</html>') !== false && stripos($arrRep['body'], 'Fatal error') === false;
    T::vrai("{$strLabel} : page complète", $booComplet, "HTTP {$arrRep['code']}");
    T::contient("{$strLabel} : écran refondu", $strMarqueur, $arrRep['body']);
}

T::section('Mise en place');

db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");

// Espace B, hors de portée du gestionnaire de l'espace 2
db()->query("INSERT INTO ploopi_workspace (label, code, system, protected, parents, iprules, macrules, template, depth, mustdefinerule, backoffice, frontoffice, backoffice_domainlist, title, meta_description, meta_keywords, meta_author, meta_copyright, meta_robots, frontoffice_domainlist, priority, mail_model)
             VALUES ('".E4_PREFIXE."espaceb', '', 0, 0, '0;1', '', '', '', 2, 0, 1, 0, '', '', '', '', '', '', 'index, follow, all', '', 1, '')");
$intEspaceB = db()->insert_id;

// Groupe B, rattaché au seul espace B
db()->query("INSERT INTO ploopi_group (id_group, label, system, protected, parents, depth, id_workspace, shared) VALUES (1, '".E4_PREFIXE."groupeb', 0, 0, '0;1', 2, 0, 0)");
$intGroupeB = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_group (id_group, id_workspace, adminlevel) VALUES ({$intGroupeB}, {$intEspaceB}, "._PLOOPI_ID_LEVEL_USER.")");

// Groupe rattaché à l'espace 2 au niveau utilisateur
db()->query("INSERT INTO ploopi_group (id_group, label, system, protected, parents, depth, id_workspace, shared) VALUES (1, '".E4_PREFIXE."groupe2', 0, 0, '0;1', 2, 0, 0)");
$intGroupe2 = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_group (id_group, id_workspace, adminlevel) VALUES ({$intGroupe2}, 2, "._PLOOPI_ID_LEVEL_USER.")");

$intGest = e4_utilisateur('gest', 2, _PLOOPI_ID_LEVEL_GROUPMANAGER);
$intUtil = e4_utilisateur('util', 2, _PLOOPI_ID_LEVEL_USER);
$intCibleB = e4_utilisateur('cibleb', $intEspaceB, _PLOOPI_ID_LEVEL_USER, $intGroupeB);
$intDel = e4_utilisateur('annuaire', 2, _PLOOPI_ID_LEVEL_USER);

// Instance de module de l'espace B (type system : seule la ligne compte)
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public, shared, herited, adminrestricted, viewmode, transverseview, autoconnect) VALUES ('".E4_PREFIXE."moduleb', 1, {$intEspaceB}, 1, 1, 0, 0, 0, 0, 0, 0, 0)");
$intModuleB = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModuleB}, {$intEspaceB}, 1, 'left')");

// Instance inactive (hors menus) rattachée à l'espace 2, pour y porter un rôle
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public, shared, herited, adminrestricted, viewmode, transverseview, autoconnect) VALUES ('".E4_PREFIXE."module2', 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
$intModule2 = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule2}, 2, 99, 'left')");

// Rôles : un de l'espace B, un de l'espace 2
db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description, def, shared) VALUES ({$intModuleB}, {$intEspaceB}, '".E4_PREFIXE."roleb', '', 0, 0)");
$intRoleB = db()->insert_id;
db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description, def, shared) VALUES ({$intModule2}, 2, '".E4_PREFIXE."role2', '', 0, 0)");
$intRole2 = db()->insert_id;

// Message de cibleb à l'administrateur, avec une réponse : util n'en est ni l'émetteur ni le destinataire
$intAdmin = (int) e4_val("SELECT id FROM ploopi_user WHERE login = '".db()->real_escape_string(cfg('admin_login'))."'");
db()->query("INSERT INTO ploopi_ticket (title, message, object_label, timestp, id_record, id_user, parent_id, root_id) VALUES ('".E4_PREFIXE." message', '<p>Contenu du message</p>', '', ".date('YmdHis').", '', {$intCibleB}, 0, 0)");
$intTicket = db()->insert_id;
db()->query("UPDATE ploopi_ticket SET root_id = {$intTicket} WHERE id = {$intTicket}");
db()->query("INSERT INTO ploopi_ticket_dest (id_user, id_ticket) VALUES ({$intAdmin}, {$intTicket})");
db()->query("INSERT INTO ploopi_ticket (title, message, object_label, timestp, id_record, id_user, parent_id, root_id) VALUES ('".E4_PREFIXE." reponse confidentielle', '<p>Reponse</p>', '', ".date('YmdHis').", '', {$intAdmin}, {$intTicket}, {$intTicket})");
db()->query("UPDATE ploopi_ticket SET count_replies = 1 WHERE id = {$intTicket}");

T::vrai('session administrateur établie', e4_connecter(cfg('admin_login'), cfg('admin_password')));

/**
 * Écrans refondus : un espace, un groupe, le niveau Système, Mon espace
 */
T::section('Écrans d\'un espace de travail (ploopi4)');

e4_ecran('onglet Espace', e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces'), 'class="p4-entity-head p4-ui"');
$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabModules');
e4_ecran('onglet Modules', $arrRep, 'p4-modules-table');
$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabParams');
e4_ecran('onglet Paramètres', $arrRep, 'class="p4-aside-layout');
e4_ecran('onglet Rôles', e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabRoles'), E4_PREFIXE.'role2');
e4_ecran('liste des groupes', e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabGroupList'), E4_PREFIXE.'groupe2');
e4_ecran('rattacher un utilisateur', e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserAttach'), 'data-p4-table');
e4_ecran('fiche d\'un utilisateur', e4_page("system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=modify_user&user_id={$intUtil}"), 'name="user_lastname"');
e4_ecran('onglet Groupe', e4_page('system_level=work&groupid=3&wspToolbarItem=tabGroups'), 'class="p4-entity-head p4-ui"');
e4_ecran('import d\'utilisateurs (groupe)', e4_page('system_level=work&groupid=3&wspToolbarItem=tabUsers&usrTabItem=tabUserImport'), 'name="system_user_file"');

T::section('Annotations (ploopi4)');
/**
 * Bloc d'annotations du socle (annotation::display) refondu sous ploopi4 : fil des
 * annotations (auteur, date, étiquettes, confidentialité), formulaire d'ajout, sans
 * gestionnaire en ligne. Les annotations privées d'autrui ne paraissent ni dans le bloc ni
 * dans la fenêtre d'une étiquette (cette dernière les montrait).
 */
// Objet « espace de travail » du module system (_SYSTEM_OBJECT_WORKSPACE = 1)
$fnAnnotation = function($intUser, $strContenu, $intPrive, $strTag = '') {
    db()->query("INSERT INTO ploopi_annotation (title, content, object_label, type_annotation, date_annotation, private, id_record, id_object, id_user, id_workspace, id_element, id_module, id_module_type)
                 VALUES ('', '".db()->real_escape_string($strContenu)."', 'Espace', '', '".date('YmdHis')."', {$intPrive}, '2', 1, {$intUser}, 2, '', "._PLOOPI_MODULE_SYSTEM.", 1)");
    $intId = db()->insert_id;
    if ($strTag != '')
    {
        $intTag = (int) e4_val("SELECT id FROM ploopi_tag WHERE tag = '".db()->real_escape_string($strTag)."'");
        if (!$intTag) { db()->query("INSERT INTO ploopi_tag (tag, tag_clean, id_user) VALUES ('".db()->real_escape_string($strTag)."', '".db()->real_escape_string($strTag)."', {$intUser})"); $intTag = db()->insert_id; }
        db()->query("INSERT INTO ploopi_annotation_tag (id_annotation, id_tag) VALUES ({$intId}, {$intTag})");
    }
    return $intId;
};
$intAnnoAdmin = $fnAnnotation($intAdmin, E4_PREFIXE.' note de l\'administrateur', 0, E4_PREFIXE.'tag');
$intAnnoAutre = $fnAnnotation($intUtil, E4_PREFIXE.' publique </div><script>alert(1)</script>', 0, E4_PREFIXE.'tag');
$intAnnoSecret = $fnAnnotation($intUtil, E4_PREFIXE.' SECRETPRIVE', 1, E4_PREFIXE.'tag');
$intTagE4 = (int) e4_val("SELECT id FROM ploopi_tag WHERE tag = '".E4_PREFIXE."tag'");

$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces');
preg_match('@<section class="p4-anno".*?</section>@s', $arrRep['body'], $arrM);
$strBloc = $arrM[0] ?? '';
T::vrai('bloc refondu présent', $strBloc !== '');
T::contient('compte des annotations visibles (2)', '<span class="p4-badge p4-badge-brand">2</span>', $strBloc);
T::contient('auteur et texte', E4_PREFIXE.' note de l&#039;administrateur', $strBloc);
T::contient('étiquette en pastille', '#'.E4_PREFIXE.'tag', $strBloc);
T::contientpas('texte d\'autrui échappé', '<script>alert(1)</script>', $strBloc);
T::contientpas('annotation privée d\'autrui absente', 'SECRETPRIVE', $strBloc);
T::eq('suppression proposée pour sa seule annotation', 1, substr_count($strBloc, 'data-p4-anno-delete='));
T::contient('texte requis, étiquettes facultatives', 'name="ploopi_annotation_content" placeholder="Votre remarque…" required', $strBloc);
T::vrai('aucun gestionnaire en ligne', !preg_match('/\son[a-z]+\s*=/i', $strBloc));
T::vrai('aucun script en ligne', !preg_match('/<script(?![^>]*\bsrc=)[^>]*>/i', $strBloc));

$arrRep = e4_op('annotation_taghistory', "&id_tag={$intTagE4}");
T::contient('fenêtre d\'une étiquette refondue', 'class="p4-ui p4-anno-history"', $arrRep['body']);
T::contient('elle liste les annotations publiques', E4_PREFIXE.' note de l&#039;administrateur', $arrRep['body']);
T::contientpas('ni l\'annotation privée d\'autrui', 'SECRETPRIVE', $arrRep['body']);
T::vrai('sans gestionnaire en ligne', !preg_match('/\son[a-z]+\s*=/i', $arrRep['body']));

T::section('Écrans du niveau Système (ploopi4)');

e4_ecran('Modules', e4_page('system_level=system&sysToolbarItem=install'), 'data-p4-table-filter');
e4_ecran('Paramètres', e4_page('system_level=system&sysToolbarItem=params'), 'class="p4-aside-layout');
e4_ecran('Utilisateurs (annuaire)', e4_page('system_level=system&sysToolbarItem=directory'), 'p4-dir-toolbar');
$arrRep = e4_page('system_level=system&sysToolbarItem=tools&op=list');
e4_ecran('Outils', $arrRep, 'class="p4-tiles p4-ui"');
foreach(array('connectedusers', 'actionhistory', 'diagnostic', 'serverload', 'stats') as $strOutil) e4_ecran("outil {$strOutil}", e4_page("system_level=system&sysToolbarItem=tools&op={$strOutil}"), 'class="p4-back"');

/**
 * § 3.19 : la configuration de PHP s'affichait dans un cadre au fond blanc (sortie brute
 * de phpinfo(), même en thème sombre). Rendue dans l'écran : tableau filtrable, valeurs
 * échappées, valeur principale seulement quand elle diffère.
 */
$arrRep = e4_page('system_level=system&sysToolbarItem=tools&op=phpinfo');
e4_ecran('outil phpinfo', $arrRep, 'class="p4-back"');
T::contientpas('plus de cadre au fond blanc', 'system_tools_phpinfo', $arrRep['body']);
T::vrai('tableau filtrable des directives', strpos($arrRep['body'], 'p4-phpinfo-table') !== false && strpos($arrRep['body'], 'data-p4-table-filter') !== false);
T::contient('directive memory_limit', '<code>memory_limit</code>', $arrRep['body']);
T::contient('version de PHP en tête', 'PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, $arrRep['body']);
T::vrai('aucune balise de phpinfo reprise telle quelle', strpos($arrRep['body'], 'class="e"') === false && strpos($arrRep['body'], 'class="v"') === false);

/**
 * § 3.19 : intitulés écrits en dur dans les outils historiques (« Tps de Réponse »,
 * « SQL (qy) », « nombre de coeurs »…), désormais constantes de langue (§ 12).
 */
$arrEnDur = array();
foreach(glob(PLOOPI_ROOT.'/modules/system/tools_*.php') as $strFichier) if (preg_match("/'label' => '[A-ZÉ]/u", file_get_contents($strFichier))) $arrEnDur[] = basename($strFichier);
T::vrai('outils : aucun en-tête de colonne écrit en dur', empty($arrEnDur), implode(', ', $arrEnDur));
$arrRep = e4_page('system_level=system&sysToolbarItem=tools&op=stats');
T::contient('outil stats : en-tête de colonne de langue', 'Temps de r&eacute;ponse (ms)', $arrRep['body']);
T::contient('outil stats : titre de langue', 'c&oelig;urs', $arrRep['body']);

T::section('Mon espace (ploopi4)');

e4_ecran('messages', e4_page('op=tickets', null, true));
T::contient('messages : le message reçu est listé', E4_PREFIXE.' message', e4_page('op=tickets', null, true)['body']);
e4_ecran('annotations', e4_page('op=annotation', null, true));
e4_ecran('profil', e4_page('op=profile', null, true), 'name="user_lastname"');

/**
 * § 3.19 : la photo se choisissait dans une fenêtre historique (bouton « Submit », son
 * libellé étant passé en name). Sous ploopi4 : choix de fichier dans le champ, envoyé
 * en arrière-plan (system_send_photo), aperçu de la photo temporaire (system_get_photo).
 */
$arrRep = e4_page('op=profile', null, true);
T::vrai('photo : choix de fichier dans le champ', (bool)preg_match('/<input[^>]*type="file"[^>]*accept="image\/\*"[^>]*data-p4-photo-file/', $arrRep['body']));
T::contientpas('photo : plus de fenêtre historique', 'data-p4-photo-choose', $arrRep['body']);
preg_match('/data-p4-photo-url="([^"]+)"/', $arrRep['body'], $arrM);
preg_match('/data-p4-photo-preview="([^"]+)"/', $arrRep['body'], $arrP);
$strImage = tempnam(sys_get_temp_dir(), 'p4photo').'.png';
$rscImage = imagecreatetruecolor(40, 60); imagepng($rscImage, $strImage); unset($rscImage);
if (isset($arrM[1], $arrP[1]))
{
    H::req('/'.html_entity_decode($arrM[1]), array('system_user_photo' => new CURLFile($strImage, 'image/png', 'photo.png')));
    $arrApercu = H::req('/'.html_entity_decode($arrP[1]));
    T::vrai('photo envoyée en arrière-plan puis lue en aperçu (PNG)', strpos($arrApercu['body'], "\x89PNG") === 0, substr($arrApercu['headers'], 0, 120));
}
else T::vrai('adresses d\'envoi et d\'aperçu dans le champ', false);
@unlink($strImage);
e4_ecran('droits', e4_page('op=actions', null, true));
e4_ecran('paramètres', e4_page('op=param', null, true));
e4_ecran('recherche', e4_page('op=search', 'system_search_keywords=ploopi', true), 'name="system_search_keywords"');

T::section('Profil : écran refondu plutôt que la fenêtre historique (ploopi4)');
/**
 * § 3.19 : « Mon profil » du menu utilisateur, et la demande de compléter un profil
 * incomplet à la connexion, ouvraient la fenêtre historique (system_update_profile).
 * Sous ploopi4, ils mènent à l'écran refondu Mon profil, qui l'annonce une fois.
 */
$arrRep = e4_page('op=tickets', null, true);
T::vrai('menu utilisateur : lien vers Mon profil', (bool)preg_match('/<a[^>]*class="p4-menu-item"[^>]*id="tpl_profile"[^>]*href="admin\.php\?ploopi_url=/', $arrRep['body']));
T::contientpas('plus de fenêtre historique dans le menu', 'data-p4-profile=', $arrRep['body']);
$intIncomplet = e4_utilisateur('incomplet', 2);
$strRequis = (string) e4_val("SELECT value FROM ploopi_param_default WHERE id_module = 1 AND name = 'system_user_required_fields'");
db()->query("UPDATE ploopi_param_default SET value = 'service' WHERE id_module = 1 AND name = 'system_user_required_fields'");
$strEdition = (string) e4_val("SELECT value FROM ploopi_param_default WHERE id_module = 1 AND name = 'system_profile_edit_allowed'");
db()->query("UPDATE ploopi_param_default SET value = '1' WHERE id_module = 1 AND name = 'system_profile_edit_allowed'");
H::reset();
/**
 * Pas de redirection HTTP : la première page après la connexion est servie entière (les
 * clients qui la lisent, comme H::env(), y trouvent l'environnement) ; le lien du menu
 * vers Mon profil porte data-p4-profile-required, que le shell suit aussitôt.
 */
H::req('/admin.php', 'ploopi_login='.E4_PREFIXE.'incomplet&ploopi_password='.urlencode(E4_MDP));
$arrRep = H::req('/admin.php');
T::contientpas('profil incomplet : pas de fenêtre historique', 'system_popup_update_profile', $arrRep['body']);
T::vrai('profil incomplet : première page servie, environnement compris', $arrRep['code'] === 200 && (bool)preg_match("/_PLOOPI_ENV *= *'[^']+'/", $arrRep['body']), "HTTP {$arrRep['code']}");
T::vrai('profil incomplet : le shell est invité à ouvrir Mon profil', (bool)preg_match('/<a[^>]*id="tpl_profile"[^>]*data-p4-profile-required="1"/', $arrRep['body']));
preg_match('/<a[^>]*id="tpl_profile"[^>]*href="([^"]+)"/', $arrRep['body'], $arrLien);
$arrRep = H::req('/'.html_entity_decode($arrLien[1] ?? ''));
for ($intSaut = 0; $intSaut < 4 && $arrRep['code'] === 302; $intSaut++) $arrRep = e4_suivre($arrRep);
T::contient('profil incomplet : écran Mon profil', 'name="user_lastname"', $arrRep['body']);
T::contient('profil incomplet : message', 'p4-profile-incomplete', $arrRep['body']);
T::contientpas('invitation faite une seule fois', 'data-p4-profile-required="1"', $arrRep['body']);
$arrRep = H::req('/admin.php?ploopi_env='.urlencode(H::env()));
T::contientpas('message affiché une seule fois', 'p4-profile-incomplete', $arrRep['body']);
db()->query("UPDATE ploopi_param_default SET value = '".db()->real_escape_string($strRequis)."' WHERE id_module = 1 AND name = 'system_user_required_fields'");
db()->query("UPDATE ploopi_param_default SET value = '".db()->real_escape_string($strEdition)."' WHERE id_module = 1 AND name = 'system_profile_edit_allowed'");
T::vrai('session administrateur rétablie', e4_connecter(cfg('admin_login'), cfg('admin_password')));

T::section('Mes annotations : modifier et supprimer (ploopi4)');
/**
 * § 3.19 : Mes annotations ne permettait ni de modifier ni de supprimer. Seul l'auteur le
 * peut ; la modification remplace les étiquettes (annotation::save n'en retirait aucune).
 */
$intAnnoModif = $fnAnnotation($intAdmin, E4_PREFIXE.' a modifier', 0, E4_PREFIXE.'avant');
$intAnnoSuppr = $fnAnnotation($intAdmin, E4_PREFIXE.' a supprimer', 0, E4_PREFIXE.'avant');
$funcTags = function($intAnno) { return (string) e4_val("SELECT GROUP_CONCAT(t.tag ORDER BY t.tag SEPARATOR ' ') FROM ploopi_annotation_tag at INNER JOIN ploopi_tag t ON t.id = at.id_tag WHERE at.id_annotation = {$intAnno}"); };

$arrRep = e4_page('op=annotation', null, true);
T::vrai('formulaire de modification de sa note, texte prérempli', (bool)preg_match('/<form[^>]*data-p4-note-form="'.$intAnnoModif.'".*?<textarea[^>]*name="annotation_content"[^>]*>'.E4_PREFIXE.' a modifier<\/textarea>/s', $arrRep['body']));
T::vrai('étiquettes préremplies', (bool)preg_match('/name="annotation_tags"[^>]*value="'.E4_PREFIXE.'avant"/', $arrRep['body']));
T::contient('suppression proposée, avec confirmation', 'data-p4-note-delete="'.$intAnnoSuppr.'"', $arrRep['body']);
T::vrai('écran sans gestionnaire en ligne', !preg_match('/\son[a-z]+\s*=/i', preg_replace('@^.*?<main[^>]*>|</main>.*$@s', '', $arrRep['body'])));

e4_page('op=annotation', 'annotation_action=save&annotation_id='.$intAnnoModif.'&annotation_content='.urlencode(E4_PREFIXE.' modifiée & "citée"').'&annotation_tags='.urlencode(E4_PREFIXE.'avant '.E4_PREFIXE.'neuve').'&annotation_private=1', true);
T::eq('texte modifié, tel quel (ni échappé, ni converti)', E4_PREFIXE.' modifiée & "citée"', e4_val("SELECT content FROM ploopi_annotation WHERE id = {$intAnnoModif}"));
T::eq('rendue privée', '1', e4_val("SELECT private FROM ploopi_annotation WHERE id = {$intAnnoModif}"));
T::eq('étiquette ajoutée', E4_PREFIXE.'avant '.E4_PREFIXE.'neuve', $funcTags($intAnnoModif));
e4_page('op=annotation', 'annotation_action=save&annotation_id='.$intAnnoModif.'&annotation_content='.urlencode(E4_PREFIXE.' modifiée').'&annotation_tags='.urlencode(E4_PREFIXE.'neuve'), true);
T::eq('étiquette retirée', E4_PREFIXE.'neuve', $funcTags($intAnnoModif));
T::eq('décochée : publique', '0', e4_val("SELECT private FROM ploopi_annotation WHERE id = {$intAnnoModif}"));
e4_page('op=annotation', 'annotation_action=save&annotation_id='.$intAnnoModif.'&annotation_content=++&annotation_tags=', true);
T::eq('texte vide refusé', E4_PREFIXE.' modifiée', e4_val("SELECT content FROM ploopi_annotation WHERE id = {$intAnnoModif}"));
e4_page('op=annotation', 'annotation_action=save&annotation_id='.$intAnnoAutre.'&annotation_content=pirate&annotation_tags=', true);
T::vrai('note d\'autrui : non modifiée', strpos((string) e4_val("SELECT content FROM ploopi_annotation WHERE id = {$intAnnoAutre}"), 'pirate') === false);
e4_page('op=annotation', 'annotation_action=delete&annotation_id='.$intAnnoAutre, true);
T::eq('note d\'autrui : non supprimée', '1', e4_val("SELECT COUNT(*) FROM ploopi_annotation WHERE id = {$intAnnoAutre}"));
$arrRep = e4_page('op=annotation', 'annotation_action=delete&annotation_id='.$intAnnoSuppr, true);
T::eq('sa note : supprimée', '0', e4_val("SELECT COUNT(*) FROM ploopi_annotation WHERE id = {$intAnnoSuppr}"));
T::eq('ses liens d\'étiquettes avec', '0', e4_val("SELECT COUNT(*) FROM ploopi_annotation_tag WHERE id_annotation = {$intAnnoSuppr}"));
e4_ecran('après suppression', $arrRep);

$arrRep = e4_op('tickets_open_responses', "&ticket_id={$intTicket}", null, true);
T::contient('réponses d\'un message : fil refondu (destinataire)', 'class="p4-thread"', $arrRep['body']);
T::contient('réponses d\'un message : la réponse est lue par le destinataire', E4_PREFIXE.' reponse confidentielle', $arrRep['body']);

/**
 * Contrôles d'accès de l'administration d'un espace
 */
T::section('Modules, paramètres et rôles d\'un autre espace');

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabModules');
e4_page("wspToolbarItem=tabModules&op=switch_active&moduleid={$intModuleB}");
T::eq('module de l\'espace B : pas d\'activation depuis l\'espace 2', '1', e4_val("SELECT active FROM ploopi_module WHERE id = {$intModuleB}"));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabParams');
e4_page('wspToolbarItem=tabParams&op=save', "idmodule={$intModuleB}&system_language=english");
T::eq('paramètres d\'un module de l\'espace B : rien n\'est enregistré pour l\'espace 2', '0', e4_val("SELECT COUNT(*) FROM ploopi_param_workspace WHERE id_module = {$intModuleB}"));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabRoles');
e4_page("wspToolbarItem=tabRoles&op=delete_role&roleid={$intRoleB}");
T::eq('rôle de l\'espace B : pas de suppression depuis l\'espace 2', '1', e4_val("SELECT COUNT(*) FROM ploopi_role WHERE id = {$intRoleB}"));

e4_page('wspToolbarItem=tabRoles&op=save_role', "roleid={$intRoleB}&role_label=".E4_PREFIXE."pirate&role_id_module={$intModule2}");
T::eq('rôle de l\'espace B : pas de modification depuis l\'espace 2', E4_PREFIXE.'roleb', e4_val("SELECT label FROM ploopi_role WHERE id = {$intRoleB}"));

e4_op('system_roleusers_select_user', "&system_roleusers_userid={$intCibleB}&system_roleusers_roleid={$intRole2}");
T::eq('rôle de l\'espace 2 : pas d\'affectation à un utilisateur hors de l\'espace', '0', e4_val("SELECT COUNT(*) FROM ploopi_workspace_user_role WHERE id_role = {$intRole2} AND id_user = {$intCibleB}"));

e4_op('system_roleusers_select_user', "&system_roleusers_userid={$intUtil}&system_roleusers_roleid={$intRole2}");
T::eq('témoin : affectation à un utilisateur de l\'espace', '1', e4_val("SELECT COUNT(*) FROM ploopi_workspace_user_role WHERE id_role = {$intRole2} AND id_user = {$intUtil}"));

T::section('Fiche d\'un utilisateur : champs protégés');

$strHash = e4_val("SELECT password FROM ploopi_user WHERE id = {$intUtil}");
e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
e4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=save_user&user_id={$intUtil}", 'user_lastname=Modifie&user_firstname=Test&user_email='.E4_PREFIXE.'util%40example.test&user_password='.urlencode('$2y$10$pirate').'&user_date_expire=&usernewpass=&usernewpass_confirm=');
T::eq('enregistrement de la fiche', 'Modifie', e4_val("SELECT lastname FROM ploopi_user WHERE id = {$intUtil}"));
T::eq('empreinte du mot de passe : pas reprise du formulaire', $strHash, e4_val("SELECT password FROM ploopi_user WHERE id = {$intUtil}"));
T::eq('date d\'expiration vide : 0 (mode SQL strict)', '0', e4_val("SELECT date_expire FROM ploopi_user WHERE id = {$intUtil}"));

/**
 * Création d'un utilisateur (op=save_user, admin_index_users.php) : le compte n'était créé
 * que sous le contrôle du JavaScript du formulaire. Côté serveur, des mots de passe
 * différents ou refusés laissaient créer le compte sans mot de passe, et un échec de
 * l'enregistrement (identifiant trop long pour la colonne, mode SQL strict) était
 * journalisé comme une création et rattachait au groupe un utilisateur sans identifiant.
 */
T::section('Création d\'un utilisateur : erreurs refusées');

// Groupe de l'installation (les groupes créés par la suite ne sont pas dans l'arborescence)
$intGroupeP = (int) e4_val("SELECT id FROM ploopi_group WHERE label = 'Groupe Principal'");
$funcCreer = function($strLogin, $strMdp, $strConfirmation) use ($intGroupeP) {
    e4_page("system_level=work&groupid={$intGroupeP}&wspToolbarItem=tabUsers&usrTabItem=tabUserAdd");
    return e4_page('wspToolbarItem=tabUsers&usrTabItem=tabUserAdd&op=save_user&confirm', 'user_login='.urlencode($strLogin).'&user_lastname=Creation&user_firstname='.urlencode($strLogin).'&user_email='.E4_PREFIXE.'creation%40example.test&user_date_expire=&usernewpass='.urlencode($strMdp).'&usernewpass_confirm='.urlencode($strConfirmation));
};
// _SYSTEM_ACTION_CREATEUSER (modules/system/include/global.php) : 19
$funcJournal = function() { return (int) e4_val("SELECT COUNT(*) FROM ploopi_user_action_log WHERE id_action = 19 AND id_module_type = 1"); };
// Première ligne de _SYSTEM_MSG_SAVEUSERERROR (modules/system/lang/french.php)
preg_match('/_SYSTEM_MSG_SAVEUSERERROR\', "([^\\\\]+)\\\\n/', file_get_contents(PLOOPI_ROOT.'/modules/system/lang/french.php'), $arrMsg);
$strMsgEchec = isset($arrMsg[1]) ? $arrMsg[1] : '(message introuvable)';
$intRattaches = (int) e4_val("SELECT COUNT(*) FROM ploopi_group_user WHERE id_group = {$intGroupeP}");

$intJournal = $funcJournal();
$arrRep = $funcCreer(E4_PREFIXE.'mdpdiff', 'Ploopi4!Ecrans2026', 'Autre!Mdp2026');
T::eq('mots de passe différents : pas de compte créé', '0', e4_val("SELECT COUNT(*) FROM ploopi_user WHERE login = '".E4_PREFIXE."mdpdiff'"));
T::eq('mots de passe différents : pas de création journalisée', $intJournal, $funcJournal());
T::contient('mots de passe différents : message d\'erreur, saisie conservée', E4_PREFIXE.'mdpdiff', $arrRep['body']);

$arrRep = $funcCreer(str_repeat('x', 40), 'Ploopi4!Ecrans2026', 'Ploopi4!Ecrans2026');
T::eq('échec de l\'enregistrement (identifiant trop long) : pas de création journalisée', $intJournal, $funcJournal());
T::eq('échec de l\'enregistrement : aucun rattachement au groupe', $intRattaches, (int) e4_val("SELECT COUNT(*) FROM ploopi_group_user WHERE id_group = {$intGroupeP}"));
T::contient('échec de l\'enregistrement : message d\'erreur', ploopi\str::htmlentities($strMsgEchec), $arrRep['body']);

$funcCreer(E4_PREFIXE.'creation', 'Ploopi4!Ecrans2026', 'Ploopi4!Ecrans2026');
$intCree = (int) e4_val("SELECT id FROM ploopi_user WHERE login = '".E4_PREFIXE."creation'");
T::vrai('témoin : création d\'un utilisateur valide', $intCree > 0);
T::eq('témoin : création journalisée', $intJournal + 1, $funcJournal());
T::eq('témoin : rattaché au groupe', '1', e4_val("SELECT COUNT(*) FROM ploopi_group_user WHERE id_group = {$intGroupeP} AND id_user = {$intCree}"));

/**
 * Gestionnaire de l'espace 2 : onglets réservés, rattachements, niveau d'un groupe, annuaire
 */
T::section('Gestionnaire d\'espace');

T::vrai('session du gestionnaire de l\'espace 2 établie', e4_connecter(E4_PREFIXE.'gest', E4_MDP));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers');
$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabParams');
T::contientpas('onglet Paramètres réservé : non servi au gestionnaire', 'class="p4-aside-layout', $arrRep['body']);

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserAttach');
e4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserAttach&op=attach_user&userid={$intCibleB}");
T::eq('rattachement d\'un utilisateur hors de portée : refusé', '0', e4_val("SELECT COUNT(*) FROM ploopi_workspace_user WHERE id_workspace = 2 AND id_user = {$intCibleB}"));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabGroupList');
e4_page("wspToolbarItem=tabUsers&usrTabItem=tabGroupList&op=save_group&orgid={$intGroupe2}", 'workspacegroup_adminlevel='._PLOOPI_ID_LEVEL_SYSTEMADMIN);
T::eq('niveau d\'un groupe : pas au-delà du sien', (string) _PLOOPI_ID_LEVEL_USER, e4_val("SELECT adminlevel FROM ploopi_workspace_group WHERE id_group = {$intGroupe2} AND id_workspace = 2"));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
$strFiche = 'user_lastname=Ecrans&user_firstname=Test&user_email='.E4_PREFIXE.'annuaire%40example.test&user_date_expire=&usernewpass=&usernewpass_confirm=';
e4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=save_user&user_id={$intDel}", $strFiche.'&userworkspace_adminlevel='._PLOOPI_ID_LEVEL_GROUPMANAGER);
T::eq('témoin : niveau d\'un utilisateur porté au sien', (string) _PLOOPI_ID_LEVEL_GROUPMANAGER, e4_val("SELECT adminlevel FROM ploopi_workspace_user WHERE id_workspace = 2 AND id_user = {$intDel}"));
e4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=save_user&user_id={$intDel}", $strFiche.'&userworkspace_adminlevel='._PLOOPI_ID_LEVEL_SYSTEMADMIN);
T::eq('niveau d\'un utilisateur : pas au-delà du sien (administrateur système refusé)', (string) _PLOOPI_ID_LEVEL_GROUPMANAGER, e4_val("SELECT adminlevel FROM ploopi_workspace_user WHERE id_workspace = 2 AND id_user = {$intDel}"));

e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabDirectory', 'ploopi_login='.E4_PREFIXE.'annuaire');
e4_page('wspToolbarItem=tabDirectory&delete=1', 'ploopi_login='.E4_PREFIXE.'annuaire');
T::eq('annuaire : pas de suppression en masse par un gestionnaire', '1', e4_val("SELECT COUNT(*) FROM ploopi_user WHERE id = {$intDel}"));

/**
 * Mon espace d'un utilisateur simple : profil et messages d'autrui
 */
T::section('Mon espace : profil et messages d\'autrui');

T::vrai('session de l\'utilisateur simple établie', e4_connecter(E4_PREFIXE.'util', E4_MDP));

e4_page('op=profile', null, true);
e4_page('op=save_user', 'user_lastname=Profil&user_firstname=Test&user_email='.E4_PREFIXE.'util%40example.test&user_login='.E4_PREFIXE.'pirate&user_disabled=1&user_date_expire=20200101000000', true);
T::eq('profil enregistré', 'Profil', e4_val("SELECT lastname FROM ploopi_user WHERE id = {$intUtil}"));
T::eq('profil : identifiant inchangé', E4_PREFIXE.'util', e4_val("SELECT login FROM ploopi_user WHERE id = {$intUtil}"));
T::eq('profil : compte toujours actif', '0', e4_val("SELECT disabled FROM ploopi_user WHERE id = {$intUtil}"));
T::eq('profil : date d\'expiration inchangée', '0', e4_val("SELECT date_expire FROM ploopi_user WHERE id = {$intUtil}"));

e4_page('op=param', null, true);
e4_page('op=paramsave', "idmodule={$intModuleB}&system_language=english", true);
T::eq('paramètres personnels : pas pour un module hors de ses espaces', '0', e4_val("SELECT COUNT(*) FROM ploopi_param_user WHERE id_module = {$intModuleB} AND id_user = {$intUtil}"));

e4_page('op=tickets', null, true);
$arrRep = e4_op('tickets_open_responses', "&ticket_id={$intTicket}", null, true);
T::contientpas('réponses d\'un message d\'autrui : refusées', E4_PREFIXE.' reponse', $arrRep['body']);
e4_op('tickets_validate', "&ticket_id={$intTicket}", null, true);
e4_op('tickets_open', "&ticket_id={$intTicket}", null, true);
T::eq('message d\'autrui : ni validé ni ouvert', '0', e4_val("SELECT COUNT(*) FROM ploopi_ticket_status WHERE id_ticket = {$intTicket} AND id_user = {$intUtil}"));

/**
 * Composition d'un message : fenêtre de ploopi4 (champs, destinataires en pastilles,
 * recherche à la frappe), sans script ni gestionnaire en ligne
 */
T::section('Messages d\'autrui : ni citation, ni réponse, ni modification');
/**
 * Toujours avec l'utilisateur simple, ni émetteur ni destinataire du message de cibleb :
 * « Citer » affichait le message, la réponse s'insérait dans le fil, et la modification
 * réécrivait titre et contenu, pour tout identifiant de message.
 */
$arrRep = e4_op('tickets_replyto', "&ticket_id={$intTicket}&quoted=1", null, true);
T::contientpas('citation d\'un message d\'autrui : contenu non affiché', 'Contenu du message', $arrRep['body']);
e4_op('tickets_send', '', 'ticket_id='.$intTicket.'&ticket_title='.urlencode(E4_PREFIXE.' intrus').'&fck_ticket_message='.urlencode('<p>Intrus</p>'), true);
T::eq('réponse dans le fil d\'autrui : refusée', '0', e4_val("SELECT COUNT(*) FROM ploopi_ticket WHERE title = '".E4_PREFIXE." intrus'"));
$arrRep = e4_op('tickets_modify', "&ticket_id={$intTicket}", null, true);
T::contientpas('modification d\'un message d\'autrui : formulaire refusé', 'Contenu du message', $arrRep['body']);
e4_op('tickets_modify_next', '', 'ticket_id='.$intTicket.'&ticket_title='.urlencode(E4_PREFIXE.' falsifie').'&fck_ticket_message='.urlencode('<p>Falsifie</p>'), true);
T::eq('modification d\'un message d\'autrui : refusée', E4_PREFIXE.' message', e4_val("SELECT title FROM ploopi_ticket WHERE id = {$intTicket}"));

T::section('Composition d\'un message (ploopi4)');

T::vrai('session administrateur (destinataire du message de cibleb)', e4_connecter(cfg('admin_login'), cfg('admin_password')));

// Le cadre de la fenêtre (skin::create_popup, commun à toutes les fenêtres) n'est pas
// éprouvé ici : seul le contenu de la fenêtre de composition l'est
$fnSansEnLigne = function($strLabel, $strHtml) {
    if (preg_match('/<div class="p4-ui p4-ticket-compose">.*?<\/iframe>/s', $strHtml, $arrM)) $strHtml = $arrM[0];
    T::vrai("{$strLabel} : aucun gestionnaire en ligne", !preg_match('/\son[a-z]+\s*=/i', $strHtml));
    T::vrai("{$strLabel} : aucun script en ligne", !preg_match('/<script(?![^>]*\bsrc=)[^>]*>/i', $strHtml));
};

$arrRep = e4_op('tickets_new', "&ploopi_tickets_id_user={$intGest}", null, true);
T::contient('fenêtre de composition refondue', 'class="p4-ui p4-ticket-compose"', $arrRep['body']);
T::vrai('titre en champ de ploopi4', (bool)preg_match('/<input[^>]*class="p4-input"[^>]*name="ticket_title"/', $arrRep['body']));
T::contient('recherche de destinataires à la frappe', 'data-p4-ticket-search', $arrRep['body']);
T::vrai('destinataire proposé en pastille', (bool)preg_match('/class="p4-ticket-chip"[^>]*>(?:(?!<\/span>).)*Ecrans Test/s', $arrRep['body']));
T::vrai('destinataire transmis au formulaire', (bool)preg_match('/name="system_ticket_ctrl_user_to" value="'.$intGest.'"/', $arrRep['body']));
T::vrai('message : champ déclaré à l\'éditeur du socle (TinyMCE)', (bool)preg_match('/<textarea[^>]*name="fck_ticket_message"[^>]*data-ploopi-editor="simple"/', $arrRep['body']));
$fnSansEnLigne('composition', $arrRep['body']);

$arrRep = e4_op('tickets_search_users', '', 'ploopi_ticket_userfilter='.E4_PREFIXE.'util&ploopi_ticket_typefilter=user', true);
T::vrai('recherche : utilisateur proposé', (bool)preg_match('/data-p4-ticket-add="user" data-p4-ticket-id="'.$intUtil.'"/', $arrRep['body']));
T::contientpas('recherche : utilisateur non concerné absent', 'data-p4-ticket-id="'.$intGest.'"', $arrRep['body']);
$fnSansEnLigne('recherche', $arrRep['body']);
$arrRep = e4_op('tickets_search_users', '', 'ploopi_ticket_userfilter=zzzinconnu&ploopi_ticket_typefilter=user', true);
T::contient('recherche sans résultat : message', 'Aucun destinataire', $arrRep['body']);

$arrRep = e4_op('tickets_select_user', "&user_id={$intUtil}", null, true);
T::vrai('ajout : deux destinataires', (bool)preg_match('/name="system_ticket_ctrl_user_to" value="'.$intGest.','.$intUtil.'"/', $arrRep['body']));
T::contient('ajout : pastille retirable', 'data-p4-ticket-remove="'.$intUtil.'"', $arrRep['body']);
$fnSansEnLigne('destinataires', $arrRep['body']);
$arrRep = e4_op('tickets_select_user', "&remove_user_id={$intGest}", null, true);
T::vrai('retrait : un destinataire', (bool)preg_match('/name="system_ticket_ctrl_user_to" value="'.$intUtil.'"/', $arrRep['body']));

e4_op('tickets_send', '', 'ticket_title='.urlencode(E4_PREFIXE.' compose').'&fck_ticket_message='.urlencode('<p>Bonjour</p>').'&system_ticket_ctrl_user_to='.$intUtil, true);
$intEnvoye = (int) e4_val("SELECT id FROM ploopi_ticket WHERE title = '".E4_PREFIXE." compose'");
T::vrai('message envoyé', $intEnvoye > 0);
T::eq('message adressé au destinataire choisi', (string)$intUtil, e4_val("SELECT GROUP_CONCAT(id_user) FROM ploopi_ticket_dest WHERE id_ticket = {$intEnvoye}"));

/**
 * Envoi et réponse échouaient en mode SQL strict (id_object = '' et id = '' dans des
 * colonnes entières) : la messagerie n'enregistrait aucun nouveau message ni aucune réponse.
 */
e4_op('tickets_send', '', 'ticket_id='.$intTicket.'&ticket_title='.urlencode(E4_PREFIXE.' re').'&fck_ticket_message='.urlencode('<p>Reponse envoyee</p>'), true);
T::eq('réponse enregistrée dans le fil', (string)$intTicket, e4_val("SELECT parent_id FROM ploopi_ticket WHERE title = '".E4_PREFIXE." re'"));

$arrRep = e4_op('tickets_replyto', "&ticket_id={$intTicket}&quoted=1", null, true);
T::contient('réponse : fenêtre refondue', 'class="p4-ui p4-ticket-compose"', $arrRep['body']);
T::contient('réponse : titre préfixé', 'value="RE: '.E4_PREFIXE.' message"', $arrRep['body']);
T::vrai('réponse : citation dans le champ de l\'éditeur, échappée', (bool)preg_match('/data-ploopi-editor="simple"[^>]*>[^<]*&lt;p&gt;Contenu du message&lt;\/p&gt;/', $arrRep['body']));
$fnSansEnLigne('réponse', $arrRep['body']);

T::section('Recherche intégrale : résultats à la frappe (ploopi4)');
/**
 * § 3.19 : la recherche du shell soumettait la recherche complète, sans résultat à la
 * frappe. Suggestions par system_search_suggest : mêmes règles que l'écran de recherche
 * (modules actifs de l'espace courant, enregistrements autorisés), huit au plus.
 */
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public, shared, herited, adminrestricted, viewmode, transverseview, autoconnect) VALUES ('".E4_PREFIXE."recherche', 1, 2, 1, 1, 0, 0, 0, 0, 0, 0, 0)");
$intModRech = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModRech}, 2, 98, 'left')");
foreach (array(
    array('2', E4_PREFIXE.' Pégase', 'Le projet Pégase démarre en janvier', 2, $intModRech),
    array('3', E4_PREFIXE.' <b>Pégase</b> balisé', 'Pégase encore', 2, $intModRech),
    array((string) $intEspaceB, E4_PREFIXE.' Pégase confidentiel', 'Pégase dans un autre espace', $intEspaceB, $intModuleB)
) as $arrIdx)
{
    ploopi\search_index::add(_PLOOPI_MODULE_SYSTEM, $arrIdx[0], $arrIdx[1], $arrIdx[2], '', true, 0, 0, $intAdmin, $arrIdx[3], $arrIdx[4]);
}

T::vrai('session administrateur (modules de l\'espace rechargés)', e4_connecter(cfg('admin_login'), cfg('admin_password')));
$arrRep = e4_page('op=tickets', null, true);
T::vrai('champ du shell déclaré en liste de suggestions', (bool)preg_match('/<input[^>]*id="p4-search-input"[^>]*role="combobox"/', $arrRep['body']));
// Adresse des suggestions, lue dans le shell (son jeton est celui de la session)
$funcAdresse = function() { preg_match('/data-p4-suggest="([^"]+)"/', e4_page('op=tickets', null, true)['body'], $arrM); return isset($arrM[1]) ? html_entity_decode($arrM[1]) : ''; };
$strSuggest = $funcAdresse();
T::vrai('adresse des suggestions chiffrée dans le formulaire', strpos($strSuggest, 'admin-light.php?ploopi_url=') === 0, $strSuggest);
$funcSuggest = function($strTerme) use (&$strSuggest) { return H::req('/'.$strSuggest, 'system_search_suggest='.urlencode($strTerme)); };

$arrRep = $funcSuggest('pegase');
T::contient('contenu de l\'espace trouvé (sans accent saisi)', E4_PREFIXE.' P&eacute;gase<', $arrRep['body']);
T::vrai('lien chiffré vers le contenu', (bool)preg_match('/<a[^>]*role="option"[^>]*href="admin\.php\?ploopi_url=/', $arrRep['body']));
T::contientpas('contenu d\'un autre espace absent', 'confidentiel', $arrRep['body']);
T::contientpas('libellé échappé', '<b>Pégase</b>', $arrRep['body']);
T::contient('accès à tous les résultats', 'data-p4-suggest-all', $arrRep['body']);
T::vrai('aucun gestionnaire ni script en ligne', !preg_match('/\son[a-z]+\s*=|<script/i', $arrRep['body']));
T::contient('début de mot : « pega » suggère « Pégase »', E4_PREFIXE.' P&eacute;gase<', $funcSuggest('pega')['body']);
T::contientpas('début de mot : rien d\'un autre espace', 'confidentiel', $funcSuggest('pega')['body']);
T::eq('moins de deux caractères : rien', '', trim($funcSuggest('p')['body']));
T::contient('aucun résultat : message', 'p4-suggest-empty', $funcSuggest('zzzinconnu')['body']);

$arrRep = e4_page('op=search', 'system_search_keywords=pegase', true);
T::contient('écran de recherche : même contenu trouvé', E4_PREFIXE.' P&eacute;gase', $arrRep['body']);
T::contientpas('écran de recherche : mots entiers, comme avant', E4_PREFIXE.' P&eacute;gase', e4_page('op=search', 'system_search_keywords=pega', true)['body']);
T::contientpas('écran de recherche : autre espace absent', 'confidentiel', $arrRep['body']);
db()->query("DELETE FROM ploopi_module_workspace WHERE id_module = {$intModRech}");
T::vrai('session sans module dans l\'espace', e4_connecter(cfg('admin_login'), cfg('admin_password')));
e4_ecran('écran de recherche sans aucun module (requête SQL vide)', e4_page('op=search', 'system_search_keywords=pegase', true), 'name="system_search_keywords"');
$strSuggest = $funcAdresse();
T::contient('suggestions sans aucun module : message', 'p4-suggest-empty', $funcSuggest('pegase')['body']);

T::section('Recherche dans les messages (ploopi4)');
/**
 * § 3.19 : la messagerie ne permettait pas de chercher. Titre, contenu et expéditeur,
 * parmi les seuls messages de l'utilisateur ; terme gardé en session comme le filtre
 * de l'annuaire, effacé par tickets_search_reset.
 */
db()->query("INSERT INTO ploopi_ticket (title, message, object_label, timestp, id_record, id_user, parent_id, root_id) VALUES ('".E4_PREFIXE." secret', '<p>Entre deux autres</p>', '', ".date('YmdHis').", '', {$intCibleB}, 0, 0)");
$intSecret = db()->insert_id;
db()->query("INSERT INTO ploopi_ticket_dest (id_user, id_ticket) VALUES ({$intGest}, {$intSecret})");
db()->query("INSERT INTO ploopi_ticket (title, message, object_label, timestp, id_record, id_user, parent_id, root_id) VALUES ('".E4_PREFIXE." accents', '<p>R&eacute;union d&rsquo;&eacute;t&eacute;</p>', '', ".date('YmdHis').", '', {$intCibleB}, 0, 0)");
db()->query("INSERT INTO ploopi_ticket_dest (id_user, id_ticket) VALUES ({$intAdmin}, ".db()->insert_id.")");

e4_page('op=tickets&filtertype=all', null, true);
$funcCherche = function($strTerme) { return e4_page('op=tickets', 'tickets_search='.urlencode($strTerme), true); };

$arrRep = $funcCherche('Contenu du message');
e4_ecran('recherche dans les messages', $arrRep);
T::vrai('champ de recherche, terme rappelé', (bool)preg_match('/<input[^>]*name="tickets_search"[^>]*value="Contenu du message"/', $arrRep['body']));
T::contient('par le contenu : message trouvé', E4_PREFIXE.' message<', $arrRep['body']);
T::contientpas('par le contenu : autre message écarté', E4_PREFIXE.' compose', $arrRep['body']);
T::contient('nombre de résultats annoncé', 'p4-tickets-found', $arrRep['body']);
$arrRep = $funcCherche('compose');
T::contient('par le titre : message trouvé', E4_PREFIXE.' compose', $arrRep['body']);
T::contientpas('par le titre : autre message écarté', E4_PREFIXE.' message<', $arrRep['body']);
$arrRep = $funcCherche(E4_PREFIXE.'cibleb');
T::contient('par l\'expéditeur : message trouvé', E4_PREFIXE.' message<', $arrRep['body']);
T::contientpas('par l\'expéditeur : message d\'un autre écarté', E4_PREFIXE.' compose', $arrRep['body']);
$arrRep = $funcCherche('réunion d’été');
T::contient('contenu enregistré en entités HTML : trouvé', E4_PREFIXE.' accents', $arrRep['body']);
$arrRep = $funcCherche('secret');
T::contientpas('messages d\'autrui jamais trouvés', E4_PREFIXE.' secret', $arrRep['body']);
T::contient('aucun résultat : état vide de la recherche', 'data-p4-tickets-nomatch', $arrRep['body']);
$arrRep = $funcCherche('%');
T::contientpas('« % » cherché littéralement (pas de joker)', E4_PREFIXE.' compose', $arrRep['body']);
e4_ecran('terme avec apostrophe et antislash', $funcCherche("l'été \\"));
$funcCherche('compose');
$arrRep = e4_page('op=tickets&page=1', null, true);
T::contientpas('terme gardé d\'une page à l\'autre', E4_PREFIXE.' message<', $arrRep['body']);
$arrRep = e4_page('op=tickets&tickets_search_reset=1', null, true);
T::vrai('effacer : tous les messages', strpos($arrRep['body'], E4_PREFIXE.' message<') !== false && strpos($arrRep['body'], E4_PREFIXE.' compose') !== false);

/**
 * Choix d'une date (ploopi.calendar, champs de date des modules) : sous ploopi4, flèches
 * du sprite avec un libellé, mois et année sur une ligne. L'identifiant du champ, recopié
 * dans le JavaScript de chaque jour, est refusé s'il n'est pas un identifiant.
 */
T::section('Choix d\'une date (ploopi4)');

$arrRep = e4_op('calendar_open', '&inputfield_id=p4_date_test&selected_date='.urlencode('15/10/2026'));
T::contient('fenêtre servie', 'id="calendar"', $arrRep['body']);
T::contient('jour choisi mis en évidence', 'class="calendar_day_selected"', $arrRep['body']);
T::contient('flèches : icônes du sprite', '#i-chevrons-left', $arrRep['body']);
T::contientpas('flèches : plus d\'images', 'calendar/prev.png', $arrRep['body']);
T::eq('flèches : quatre boutons libellés', 4, preg_match_all('/<a [^>]*class="calendar_nav"[^>]*aria-label="[^"]+"/', $arrRep['body']));
T::contient('mois et année sur une ligne', 'Octobre 2026', $arrRep['body']);
$arrRep = e4_op('calendar_open', '&inputfield_id='.urlencode("x');alert(1);//"));
T::contientpas('identifiant de champ non conforme refusé', 'alert(1)', $arrRep['body']);
T::contientpas('identifiant de champ non conforme : pas de calendrier', 'id="calendar"', $arrRep['body']);

/**
 * Sauvegarde SQL : archive complète, structure exacte, données non échappées en HTML
 */
T::section('Sauvegarde SQL (outil sqldump)');

T::vrai('session administrateur rétablie', e4_connecter(cfg('admin_login'), cfg('admin_password')));
e4_page('system_level=system&sysToolbarItem=tools&op=list');
$arrRep = H::req('/admin.php?system_level=system&sysToolbarItem=tools&op=sqldump&ploopi_env='.urlencode(e4_env()));
T::eq('archive envoyée (zip)', 'PK', substr($arrRep['body'], 0, 2));

$strSql = '';
if (substr($arrRep['body'], 0, 2) === 'PK' && class_exists('ZipArchive'))
{
    $strZip = tempnam(sys_get_temp_dir(), 'p4ecr');
    file_put_contents($strZip, $arrRep['body']);
    $objZip = new ZipArchive();
    if ($objZip->open($strZip) === true) { $strSql = (string) $objZip->getFromName('dump.sql'); $objZip->close(); }
    unlink($strZip);
}
T::contient('structure exacte (SHOW CREATE TABLE)', 'CREATE TABLE `ploopi_user`', $strSql);
T::contientpas('plus de table « Array »', 'CREATE TABLE Array', $strSql);
T::contient('données : apostrophes échappées pour SQL, pas en HTML', "Contenu du message", $strSql);
T::contientpas('données : pas d\'entité HTML', '&lt;p&gt;', $strSql);

/**
 * Mise à jour d'un module déjà à la version visée : rien n'est rejoué
 */
T::section('Mise à jour d\'un module déjà à jour');

$arrType = db()->query("SELECT id, label, version FROM ploopi_module_type WHERE id <> 1 ORDER BY id LIMIT 1")->fetch_assoc();
if (!$arrType) T::skip('mise à jour d\'un module', 'aucun module installé hors system');
else
{
    $strV = urlencode($arrType['version']);
    $arrRep = e4_page("system_level=system&sysToolbarItem=install&op=update&idmoduletype={$arrType['id']}&updatefrom={$strV}&updateto={$strV}");
    T::contient("{$arrType['label']} {$arrType['version']} : signalé déjà à jour", 'Module déjà mis à jour', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));
}

T::section('Déclinaison ploopi4.1');
/**
 * ploopi4.1 sert les fichiers de ploopi4 et ajoute sa palette : mêmes écrans refondus,
 * feuille de la palette chargée après celle du gabarit
 */
db()->query("UPDATE ploopi_workspace SET template = 'ploopi4.1' WHERE id = 2");
e4_connecter(cfg('admin_login'), cfg('admin_password'));
$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces');
e4_ecran('espace sous ploopi4.1', $arrRep, 'class="p4-entity-head p4-ui"');
T::contient('feuille de ploopi4', './templates/backoffice/ploopi4/css/styles.pack.css', $arrRep['body']);
T::vrai('palette de ploopi4.1 chargée après', (bool)preg_match('@ploopi4/css/styles\.pack\.css[^>]*>\s*<link[^>]*href="\./templates/backoffice/ploopi4\.1/css/palette\.css@', $arrRep['body']));
T::contient('icônes de ploopi4', './templates/backoffice/ploopi4/img/icons.svg', $arrRep['body']);
db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");
e4_connecter(cfg('admin_login'), cfg('admin_password'));
$arrRep = e4_page('system_level=work&workspaceid=2&wspToolbarItem=tabWorkspaces');
T::contientpas('retour à ploopi4 : plus de palette', 'palette.css', $arrRep['body']);

/**
 * Non-régression : sous ploopi3, écrans historiques
 */
T::section('Écrans historiques sous ploopi3');

db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
$arrRep = e4_page('op=tickets', null, true);
T::contientpas('messages : pas d\'écran refondu', 'class="p4-tickets"', $arrRep['body']);
T::contient('messages : écran historique', 'system_tickets', $arrRep['body']);
$arrRep = e4_page('system_level=system&sysToolbarItem=tools&op=list');
T::contientpas('outils : pas de pavés refondus', 'class="p4-tiles', $arrRep['body']);
$arrRep = e4_op('tickets_new', '', null, true);
T::contientpas('composition d\'un message : pas de fenêtre refondue', 'p4-ticket-compose', $arrRep['body']);
T::contient('composition d\'un message : recherche historique', 'id="ploopi_ticket_search_btn"', $arrRep['body']);
$arrRep = e4_op('annotation_taghistory', "&id_tag={$intTagE4}");
T::contient('étiquette : fenêtre historique', 'ploopi_annotation_popup', $arrRep['body']);
T::contientpas('étiquette : pas de fenêtre refondue', 'p4-anno-history', $arrRep['body']);
T::contientpas('étiquette : annotation privée d\'autrui absente (fuite corrigée)', 'SECRETPRIVE', $arrRep['body']);
$arrRep = e4_op('calendar_open', '&inputfield_id=p4_date_test&selected_date='.urlencode('15/10/2026'));
T::contient('choix d\'une date : flèches historiques', 'calendar/prev.png', $arrRep['body']);
T::contientpas('choix d\'une date : pas d\'icônes du sprite', '#i-chevrons-left', $arrRep['body']);

e4_nettoyer();

exit(T::bilan('39_ploopi4_ecrans'));
