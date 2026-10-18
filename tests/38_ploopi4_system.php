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
 * Suite 38 — gabarit ploopi4 sur une instance : shell, écran refondu des espaces de
 * travail, préférences d'affichage, fiche en tiroir, actions groupées, export d'une
 * sélection ; et l'écran historique sous ploopi3.
 *
 * L'espace de l'administrateur (2) passe en ploopi4 le temps de la suite, puis
 * reprend son habillage.
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

define('P4_TEST_MDP', 'Ploopi4!Test2026');
define('P4_TEST_PREFIXE', 'p4_test_');

$arrTemplate = db()->query("SELECT template FROM ploopi_workspace WHERE id = 2")->fetch_assoc();
$strTemplateOrigine = $arrTemplate ? $arrTemplate['template'] : '';

function p4_nettoyer()
{
    global $strTemplateOrigine;

    db()->query("DELETE FROM ploopi_group_user WHERE id_user IN (SELECT id FROM ploopi_user WHERE login LIKE '".P4_TEST_PREFIXE."%')");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_user IN (SELECT id FROM ploopi_user WHERE login LIKE '".P4_TEST_PREFIXE."%')");
    db()->query("DELETE FROM ploopi_user WHERE login LIKE '".P4_TEST_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_group WHERE label LIKE '".P4_TEST_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_workspace IN (SELECT id FROM ploopi_workspace WHERE label LIKE '".P4_TEST_PREFIXE."%')");
    db()->query("DELETE FROM ploopi_workspace WHERE label LIKE '".P4_TEST_PREFIXE."%'");
    db()->query("DELETE FROM ploopi_param_user WHERE name IN ('system_theme', 'system_density') AND id_user = (SELECT id FROM ploopi_user WHERE login = '".db()->real_escape_string(cfg('admin_login'))."')");
    if ($strTemplateOrigine !== null) db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strTemplateOrigine)."' WHERE id = 2");
}

register_shutdown_function('p4_nettoyer');
p4_nettoyer();

/**
 * Crée un utilisateur, rattaché à un espace (niveau donné) ou à un groupe
 *
 * @return int identifiant
 */
function p4_utilisateur($strLogin, $intWorkspace = 0, $intAdminlevel = _PLOOPI_ID_LEVEL_USER, $intGroupe = 0)
{
    $strLogin = P4_TEST_PREFIXE.$strLogin;
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Test', 'Ploopi4', '%s@example.test', '%s', 0)",
        db()->real_escape_string($strLogin),
        db()->real_escape_string(ploopi\user::generate_hash(P4_TEST_MDP, $strLogin)),
        db()->real_escape_string($strLogin),
        date('YmdHis')
    ));
    $intUser = db()->insert_id;
    if ($intWorkspace) db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, {$intWorkspace}, 1, {$intAdminlevel})");
    if ($intGroupe) db()->query("INSERT INTO ploopi_group_user (id_user, id_group) VALUES ({$intUser}, {$intGroupe})");
    return $intUser;
}

/**
 * Ouvre une session backoffice et renvoie l'environnement (jeton compris), établi
 * dans l'espace 2 sur l'administration du module system
 */
function p4_connecter($strLogin, $strMotDePasse)
{
    H::reset();
    $arrRep = H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode($strMotDePasse));
    if ($arrRep['code'] !== 302) return '';
    p4_suivre($arrRep);
    // La session s'installe en deux chargements (redirection interne, puis menu et module)
    for ($intEssai = 0; $intEssai < 4; $intEssai++)
    {
        $strEnv = H::env();
        $arrEnv = explode('/', $strEnv);
        if (sizeof($arrEnv) === 5 && $arrEnv[0] !== '' && $arrEnv[2] !== '') return $strEnv;
    }
    return '';
}

/** Suit une redirection interne comme un navigateur */
function p4_suivre($arrRep)
{
    if ($arrRep['code'] === 302 && preg_match('/Location: (\S+)/i', $arrRep['headers'], $arrM) && parse_url($arrM[1], PHP_URL_HOST) === parse_url(cfg('base_url'), PHP_URL_HOST))
    {
        $arrLoc = parse_url($arrM[1]);
        return H::req($arrLoc['path'].(empty($arrLoc['query']) ? '' : '?'.$arrLoc['query']));
    }
    return $arrRep;
}

/** Environnement de la page courante, réglé sur l'administration (espace 2, module system) */
function p4_env()
{
    $arrEnv = explode('/', H::env());
    if (sizeof($arrEnv) !== 5) return '';
    $arrEnv[0] = _PLOOPI_MENU_WORKSPACES;
    $arrEnv[1] = 2;
    $arrEnv[2] = _PLOOPI_MODULE_SYSTEM;
    $arrEnv[3] = 'admin';
    return implode('/', $arrEnv);
}

/** Page d'administration du module system (redirection interne suivie) */
function p4_page($strQuery, $mixPost = null)
{
    $arrRep = H::req('/admin.php?'.$strQuery.'&ploopi_env='.urlencode(p4_env()), $mixPost);
    return p4_suivre($arrRep);
}

/** Opération du socle ou du module system */
function p4_op($strOp, $strQuery = '', $mixPost = null, $strScript = 'index-quick.php')
{
    return H::req("/{$strScript}?ploopi_op={$strOp}&ploopi_env=".urlencode(p4_env()).$strQuery, $mixPost);
}

/** Données de la liste des utilisateurs (JSON transmis au script de l'écran) */
function p4_liste($strBody)
{
    if (!preg_match('@<script type="application/json" data-p4-users-data>(.*?)</script>@s', $strBody, $arrM)) return null;
    return json_decode($arrM[1], true);
}

T::section('Mise en place');

db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");

// Espace B, hors de portée du gestionnaire de l'espace 2
db()->query("INSERT INTO ploopi_workspace (label, code, system, protected, parents, iprules, macrules, template, depth, mustdefinerule, backoffice, frontoffice, backoffice_domainlist, title, meta_description, meta_keywords, meta_author, meta_copyright, meta_robots, frontoffice_domainlist, priority, mail_model)
             VALUES ('".P4_TEST_PREFIXE."espace_b', '', 0, 0, '0;1', '', '', '', 2, 0, 1, 0, '', '', '', '', '', '', 'index, follow, all', '', 1, '')");
$intEspaceB = db()->insert_id;

$intGest = p4_utilisateur('gest', 2, _PLOOPI_ID_LEVEL_GROUPMANAGER);
$intUtilA = p4_utilisateur('util_a', 2, _PLOOPI_ID_LEVEL_USER);
$intAdminA = p4_utilisateur('admin_a', 2, _PLOOPI_ID_LEVEL_GROUPADMIN);
$intCibleB = p4_utilisateur('cible_b', $intEspaceB, _PLOOPI_ID_LEVEL_USER);

// Groupe sous le groupe principal, avec deux utilisateurs à détacher ensemble
db()->query("INSERT INTO ploopi_group (id_group, label, system, protected, parents, depth, id_workspace, shared) VALUES (3, '".P4_TEST_PREFIXE."groupe', 0, 0, '0;1;3', 3, 0, 0)");
$intGroupe = db()->insert_id;
$intMembre1 = p4_utilisateur('membre_1', 0, 0, $intGroupe);
$intMembre2 = p4_utilisateur('membre_2', 0, 0, $intGroupe);

// Groupe créé dans l'espace 2 (à la portée de son gestionnaire) : un membre simple et un
// administrateur d'espace
db()->query("INSERT INTO ploopi_group (id_group, label, system, protected, parents, depth, id_workspace, shared) VALUES (3, '".P4_TEST_PREFIXE."groupe_g', 0, 0, '0;1;3', 3, 2, 0)");
$intGroupeG = db()->insert_id;
$intMembreG = p4_utilisateur('membre_g', 0, 0, $intGroupeG);
$intAdminG = p4_utilisateur('admin_g', 2, _PLOOPI_ID_LEVEL_GROUPADMIN, $intGroupeG);

$strEnvAdmin = p4_connecter(cfg('admin_login'), cfg('admin_password'));
T::vrai('session administrateur établie', $strEnvAdmin !== '');

/**
 * Shell et écran refondu : l'espace 2 est en ploopi4
 */
T::section('Shell ploopi4 et écran des espaces de travail');

$arrRep = p4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
$strBody = $arrRep['body'];
T::eq('page servie', 200, $arrRep['code']);
T::contient('shell ploopi4 (#background.p4-app)', 'id="background" class="p4-app"', $strBody);
T::contient('contrat : #pagecontent.hook', 'id="pagecontent" class="hook"', $strBody);
T::contient('menu de system : section Organisation', 'Organisation', $strBody);
T::contient('menu de system : icône de l\'entrée Espaces de travail', '#i-sitemap', $strBody);
T::contient('fil d\'Ariane : page courante', 'aria-current="page"', $strBody);
T::contient('arborescence filtrable', 'data-p4-tree-filter', $strBody);
T::contient('en-tête de l\'espace choisi', 'class="p4-entity-head p4-ui"', $strBody);
T::contientpas('pas de barre d\'outils historique', 'class="toolbar"', $strBody);

$arrListe = p4_liste($strBody);
T::vrai('liste des utilisateurs transmise au script (JSON)', is_array($arrListe) && isset($arrListe['users']));
$intAttendus = (int) db()->query("SELECT COUNT(*) c FROM ploopi_workspace_user WHERE id_workspace = 2")->fetch_assoc()['c'];
T::eq('liste : les utilisateurs rattachés à l\'espace', $intAttendus, is_array($arrListe) ? sizeof($arrListe['users']) : -1);

$arrLigne = array();
if (is_array($arrListe)) foreach($arrListe['users'] as $arrU) if ($arrU['id'] == $intUtilA) $arrLigne = $arrU;
T::eq('ligne : identifiant de l\'utilisateur', P4_TEST_PREFIXE.'util_a', isset($arrLigne['login']) ? $arrLigne['login'] : '');
T::vrai('ligne : actions chiffrées (crypt::urlencode)', isset($arrLigne['edit']) && strpos($arrLigne['edit'], 'ploopi_url=') !== false);

/**
 * Préférences d'affichage : paramètres utilisateur du module system
 */
T::section('Préférences d\'affichage (system_display_prefs_save)');

$funcPref = function($strNom) {
    $arrL = db()->query("SELECT value FROM ploopi_param_user pu INNER JOIN ploopi_user u ON u.id = pu.id_user WHERE u.login = '".db()->real_escape_string(cfg('admin_login'))."' AND pu.name = '{$strNom}' AND pu.id_module = "._PLOOPI_MODULE_SYSTEM)->fetch_assoc();
    return $arrL ? $arrL['value'] : null;
};

p4_op('system_display_prefs_save', '', 'system_theme=dark&system_density=compact');
T::eq('thème enregistré', 'dark', $funcPref('system_theme'));
T::eq('densité enregistrée', 'compact', $funcPref('system_density'));

p4_op('system_display_prefs_save', '', 'system_theme='.urlencode('rose" onload="x'));
T::eq('valeur hors liste refusée', 'dark', $funcPref('system_theme'));

H::req('/index-quick.php?ploopi_op=system_display_prefs_save', 'system_theme=light');
T::eq('sans jeton : rien n\'est enregistré', 'dark', $funcPref('system_theme'));

$arrRep = p4_page('system_level=work&workspaceid=2');
T::contient('appliqué à la page (data-theme)', 'data-theme="dark" data-density="compact"', $arrRep['body']);

/**
 * Fiche en tiroir : seulement pour un utilisateur administrable
 */
T::section('Fiche d\'un utilisateur (system_user_card)');

$arrRep = p4_op('system_user_card', "&system_user_id={$intCibleB}");
T::contient('administrateur : fiche d\'un utilisateur de l\'espace B', P4_TEST_PREFIXE.'cible_b', $arrRep['body']);

$strEnvGest = p4_connecter(P4_TEST_PREFIXE.'gest', P4_TEST_MDP);
T::vrai('session du gestionnaire de l\'espace 2 établie', $strEnvGest !== '');

$arrRep = p4_op('system_user_card', "&system_user_id={$intCibleB}");
T::contientpas('gestionnaire : pas de fiche hors de son espace', P4_TEST_PREFIXE.'cible_b', $arrRep['body']);

$arrRep = p4_op('system_user_card', "&system_user_id={$intUtilA}&system_context=workspace&system_context_id=2", null, 'admin-light.php');
T::contient('témoin : le gestionnaire voit un utilisateur de son espace', P4_TEST_PREFIXE.'util_a', $arrRep['body']);
T::contient('témoin : niveau dans l\'espace du contexte', 'Utilisateur', $arrRep['body']);

$arrRep = p4_page("system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=modify_user&user_id={$intCibleB}");
T::contientpas('gestionnaire : pas de formulaire pour un utilisateur hors de son espace', P4_TEST_PREFIXE.'cible_b', $arrRep['body']);

/**
 * Détachement groupé : mêmes règles que le détachement unitaire
 */
T::section('Détachement groupé (detach_users)');

p4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
p4_page('wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_users', "user_id[]={$intUtilA}&user_id[]={$intAdminA}");
$funcDansEspace = function($intUser) { return (int) db()->query("SELECT COUNT(*) c FROM ploopi_workspace_user WHERE id_workspace = 2 AND id_user = {$intUser}")->fetch_assoc()['c']; };
T::eq('gestionnaire : l\'utilisateur simple est détaché', 0, $funcDansEspace($intUtilA));
T::eq('gestionnaire : l\'administrateur d\'espace (niveau supérieur) reste', 1, $funcDansEspace($intAdminA));

p4_connecter(cfg('admin_login'), cfg('admin_password'));
p4_page("system_level=work&groupid={$intGroupe}&wspToolbarItem=tabUsers&usrTabItem=tabUserList");
p4_page('wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_users', "user_id[]={$intMembre1}&user_id[]={$intMembre2}&user_id[]=abc");
T::eq('administrateur : les deux membres du groupe sont détachés', 0, (int) db()->query("SELECT COUNT(*) c FROM ploopi_group_user WHERE id_group = {$intGroupe}")->fetch_assoc()['c']);

/**
 * Détacher d'un groupe : un rattachement à un groupe ne porte pas de niveau ; le niveau
 * pris en compte est le plus élevé de l'utilisateur dans les espaces (direct ou par un
 * groupe). Un gestionnaire ne détache pas un utilisateur de niveau supérieur au sien,
 * ni seul ni en lot ; le détachement unitaire d'un espace applique enfin la même règle
 * que le détachement en lot.
 */
T::section('Détacher un utilisateur de niveau supérieur');

$funcDansGroupe = function($intUser) use ($intGroupeG) { return (int) db()->query("SELECT COUNT(*) c FROM ploopi_group_user WHERE id_group = {$intGroupeG} AND id_user = {$intUser}")->fetch_assoc()['c']; };
$funcLigne = function($strBody, $intUser) { $arrL = p4_liste($strBody); if (is_array($arrL)) foreach($arrL['users'] as $arrU) if ($arrU['id'] == $intUser) return $arrU; return array(); };

p4_connecter(P4_TEST_PREFIXE.'gest', P4_TEST_MDP);
$arrRep = p4_page("system_level=work&groupid={$intGroupeG}&wspToolbarItem=tabUsers&usrTabItem=tabUserList");
$arrLigne = $funcLigne($arrRep['body'], $intAdminG);
T::vrai('gestionnaire, liste du groupe : l\'administrateur y figure', !empty($arrLigne));
T::eq('gestionnaire, liste du groupe : pas d\'action « détacher » pour l\'administrateur', '', isset($arrLigne['detach']) ? $arrLigne['detach'] : 'absente');
$arrLigne = $funcLigne($arrRep['body'], $intMembreG);
T::vrai('témoin : action « détacher » pour le membre simple', !empty($arrLigne['detach']));

p4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_user&user_id={$intAdminG}");
T::eq('gestionnaire : l\'administrateur n\'est pas détaché du groupe (unitaire)', 1, $funcDansGroupe($intAdminG));
p4_page("system_level=work&groupid={$intGroupeG}&wspToolbarItem=tabUsers&usrTabItem=tabUserList");
p4_page('wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_users', "user_id[]={$intMembreG}&user_id[]={$intAdminG}");
T::eq('gestionnaire : l\'administrateur n\'est pas détaché du groupe (en lot)', 1, $funcDansGroupe($intAdminG));
T::eq('témoin : le membre simple est détaché', 0, $funcDansGroupe($intMembreG));

p4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
p4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_user&user_id={$intAdminA}");
T::eq('gestionnaire : l\'administrateur d\'espace n\'est pas détaché de l\'espace (unitaire)', 1, $funcDansEspace($intAdminA));

p4_connecter(cfg('admin_login'), cfg('admin_password'));
p4_page("system_level=work&groupid={$intGroupeG}&wspToolbarItem=tabUsers&usrTabItem=tabUserList");
p4_page("wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_user&user_id={$intAdminG}");
T::eq('administrateur système : détache l\'administrateur du groupe', 0, $funcDansGroupe($intAdminG));

/**
 * Export restreint à une sélection
 */
T::section('Export d\'une sélection (system_directory_ids)');

p4_page('system_level=work&workspaceid=2&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
$arrRep = p4_op('system_directory_export', "&system_directory_typedoc=csv&system_directory_ids={$intAdminA}", null, 'admin-light.php');
$arrLignes = array_values(array_filter(explode("\n", trim($arrRep['body']))));
T::eq('une ligne de données, plus l\'en-tête', 2, sizeof($arrLignes));
T::contient('la ligne est celle de l\'utilisateur choisi', P4_TEST_PREFIXE.'admin_a', $arrRep['body']);

$arrRep = p4_op('system_directory_export', '&system_directory_typedoc=csv&system_directory_ids='.urlencode('0) OR (1=1'), null, 'admin-light.php');
T::vrai('identifiants non numériques : aucune ligne', sizeof(array_filter(explode("\n", trim($arrRep['body'])))) <= 1);

/**
 * Non-régression : un espace en ploopi3 garde l'écran historique
 */
T::section('Écran historique sous ploopi3');

db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
$arrRep = p4_page('system_level=work&workspaceid=2');
T::contientpas('pas de shell ploopi4', 'class="p4-app"', $arrRep['body']);
T::contientpas('pas de liste refondue', 'data-p4-users', $arrRep['body']);
T::contient('arborescence historique', 'system_tree', $arrRep['body']);
T::contient('barre d\'outils historique', 'class="toolbar"', $arrRep['body']);

p4_nettoyer();

exit(T::bilan('38_ploopi4_system'));
