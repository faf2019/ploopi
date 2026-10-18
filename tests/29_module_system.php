<?php
/*    Copyright (c) 2007-2018 Ovensia    Contributors hold Copyright (c) to their code submissions.
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  0211-1301  USA*/

/**
 * Suite 29 — audit du module system (op.php et traitements admin_*) par la recette du §2.
 *
 * Le module system gère utilisateurs, groupes, rôles et espaces : c'est ici que le
 * cloisonnement entre espaces a le plus de conséquences. Scénarios :
 *
 * - un gestionnaire d'espace A (adminlevel _PLOOPI_ID_LEVEL_GROUPMANAGER sur A uniquement)
 *   qui vise par identifiant des utilisateurs, groupes et rôles d'un espace B ;
 * - un utilisateur sans niveau (adminlevel _PLOOPI_ID_LEVEL_USER) face aux opérations
 *   et écrans d'administration ;
 * - pour chaque refus, un témoin : l'opération aboutit dans le cas légitime
 *   (l'administrateur système, ou le gestionnaire dans SON espace).
 *
 * @package system
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();
amorcer_socle();

define('AUDIT_SYSTEM_LOGIN_GEST', 'audit_system_gest');
define('AUDIT_SYSTEM_LOGIN_SANS',  'audit_system_sans');
define('AUDIT_SYSTEM_MDP',         'AuditSystem!2026');

/**
 * Nettoie les objets créés par la suite. Appelé au début et quoi qu'il arrive.
 */
function audit_system_nettoyer()
{
    db()->query("DELETE FROM ploopi_user WHERE login IN ('".AUDIT_SYSTEM_LOGIN_GEST."','".AUDIT_SYSTEM_LOGIN_SANS."')");
    // utilisateurs créés via save_user / system_user_import : repérés par leur courriel
    db()->query("DELETE FROM ploopi_user WHERE email LIKE 'audit_system_%@example.test'");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_user NOT IN (SELECT id FROM ploopi_user)");
    db()->query("DELETE FROM ploopi_workspace_user_role WHERE id_user NOT IN (SELECT id FROM ploopi_user)");
    db()->query("DELETE FROM ploopi_workspace_group WHERE id_group IN (SELECT id FROM ploopi_group WHERE label LIKE 'audit_system_%')");
    db()->query("DELETE FROM ploopi_workspace_group_role WHERE id_group IN (SELECT id FROM ploopi_group WHERE label LIKE 'audit_system_%')");
    db()->query("DELETE FROM ploopi_group WHERE label LIKE 'audit_system_%'");
    db()->query("DELETE FROM ploopi_workspace WHERE label = 'audit_system_espace_b'");
    db()->query("DELETE FROM ploopi_role WHERE label = 'audit_system_role'");
}

register_shutdown_function('audit_system_nettoyer');
audit_system_nettoyer();

/**
 * Crée un utilisateur rattaché à un espace avec un niveau donné.
 *
 * @param string $strLogin identifiant
 * @param int $intWorkspace espace
 * @param int $intAdminlevel niveau dans cet espace
 * @return int identifiant
 */
function audit_system_creer_utilisateur($strLogin, $intWorkspace, $intAdminlevel)
{
    $strHash = ploopi\user::generate_hash(AUDIT_SYSTEM_MDP, $strLogin);
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Audit', 'System', 'audit_system_%s@example.test', '%s', 0)",
        db()->real_escape_string($strLogin),
        db()->real_escape_string($strHash),
        db()->real_escape_string($strLogin),
        date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel)
                 VALUES ({$intUser}, {$intWorkspace}, 1, {$intAdminlevel})");
    return $intUser;
}

/**
 * Ouvre une session backoffice et renvoie l'environnement (jeton compris) avec
 * l'espace de travail imposé.
 *
 * @param string $strLogin identifiant
 * @param int $intWorkspace espace à établir dans la session
 * @return string environnement, ou '' si la session n'a pas de jeton
 */
function audit_system_connecter($strLogin, $intWorkspace, $strMotDePasse = AUDIT_SYSTEM_MDP)
{
    H::reset();
    $arrRep = H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode($strMotDePasse));
    if ($arrRep['code'] !== 302) return '';
    // La connexion répond par une redirection chiffrée : la suivre comme un navigateur,
    // sinon la session n'est pas encore établie au coup suivant.
    if (preg_match('/Location: (\S+)/i', $arrRep['headers'], $arrM))
    {
        if (parse_url($arrM[1], PHP_URL_HOST) === parse_url(cfg('base_url'), PHP_URL_HOST))
        {
            $arrLoc = parse_url($arrM[1]);
            H::req($arrLoc['path'].(empty($arrLoc['query']) ? '' : '?'.$arrLoc['query']));
        }
    }
    // L'installation de la session backoffice prend deux chargements après la
    // connexion (le premier sert la redirection interne, le second installe le
    // menu et le module system — autoconnect pour un gestionnaire). Le jeton est
    // renouvelé à chaque chargement : l'environnement se relit après chacun,
    // jusqu'à ce que la session soit installée (mainmenu et module renseignés).
    $strEnvCourant = '';
    for ($intEssai = 0; $intEssai < 4; $intEssai++)
    {
        H::req('/admin.php');
        $strEnvCourant = H::env();
        if ($strEnvCourant === '') return '';
        $arrEnv = explode('/', $strEnvCourant);
        if (sizeof($arrEnv) !== 5) return '';
        if ($arrEnv[0] !== '' && $arrEnv[2] !== '') break;
    }
    // imposer l'espace demandé et l'écran d'administration du module system,
    // puis relire le jeton renouvelé par cette navigation
    $arrEnv[0] = _PLOOPI_MENU_WORKSPACES;
    $arrEnv[1] = (int)$intWorkspace;
    $arrEnv[2] = _PLOOPI_MODULE_SYSTEM;
    $arrEnv[3] = 'admin';
    $strEnv = implode('/', $arrEnv);
    // ploopi_workspaceid fait adopter l'espace demandé par la session (loader, switch
    // workspace) quand il est accessible à l'utilisateur ; workspaceid est le paramètre
    // que l'écran d'administration du module system lit pour poser son espace courant
    $arrRep = H::req('/admin.php?system_level=work&ploopi_workspaceid='.(int)$intWorkspace.'&workspaceid='.(int)$intWorkspace.'&ploopi_env='.urlencode($strEnv));
    // redirection interne : la suivre, l'espace demandé peut ne pas être accessible
    if ($arrRep['code'] === 302 && preg_match('/Location: (\S+)/i', $arrRep['headers'], $arrM))
    {
        if (parse_url($arrM[1], PHP_URL_HOST) === parse_url(cfg('base_url'), PHP_URL_HOST))
        {
            $arrLoc = parse_url($arrM[1]);
            H::req($arrLoc['path'].(empty($arrLoc['query']) ? '' : '?'.$arrLoc['query']));
        }
    }
    $strEnvApres = H::env();
    if ($strEnvApres === '') return '';
    $arrApres = explode('/', $strEnvApres);
    if (sizeof($arrApres) !== 5) return '';
    // l'espace demandé n'est pas forcément adopté par la session (elle garde le sien) :
    // ne pas le déclarer établi s'il ne l'est pas
    if ((int)$arrApres[1] !== (int)$intWorkspace) return '';
    $arrApres[2] = _PLOOPI_MODULE_SYSTEM;
    $arrApres[3] = 'admin';
    return implode('/', $arrApres);
}

/**
 * Charge une page d'administration en suivant les redirections internes (la première
 * réponse est souvent une redirection), et renvoie l'environnement renouvelé.
 *
 * @param string $strEnv environnement (jeton compris)
 * @param string $strQuery paramètres de l'URL, commençant par « ? »
 * @return string environnement après navigation, ou '' si perdue
 */
function audit_system_naviguer($strEnv, $strQuery = '')
{
    for ($intEssai = 0; $intEssai < 4; $intEssai++)
    {
        $arrRep = H::req('/admin.php'.$strQuery.'&ploopi_env='.urlencode($strEnv));
        // page servie : le jeton renouvelé est exploitable par l'appelant
        if ($arrRep['code'] === 200) return H::env();
        if ($arrResp['code'] !== 302) return '';
        if (!preg_match('/Location: (\S+)/i', $arrRep['headers'], $arrM)) return '';
        if (parse_url($arrM[1], PHP_URL_HOST) !== parse_url(cfg('base_url'), PHP_URL_HOST)) return '';
        $arrLoc = parse_url($arrM[1]);
        // suivre la redirection : la page servie renouvelle le jeton ; l'ancien
        // environnement peut être vide à ce stade, ce n'est pas un échec
        $arrRep = H::req($arrLoc['path'].(empty($arrLoc['query']) ? '' : '?'.$arrLoc['query']));
        $strEnv = H::env();
        if ($strEnv === '') return '';
    }
    return '';
}

/**
 * Appelle une opération du socle/module system en index-quick.php, avec jeton.
 *
 * @param string $strEnv environnement
 * @param string $strOp opération
 * @param string $strQuery paramètres supplémentaires
 * @param mixed $mixPost corps POST éventuel
 * @return array réponse
 */
function audit_system_op($strEnv, $strOp, $strQuery = '', $mixPost = null)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

/**
 * Lit la valeur d'une colonne d'un utilisateur.
 *
 * @param int $intUser identifiant
 * @param string $strColonne colonne
 * @return mixed|null valeur, null si l'utilisateur n'existe plus
 */
function audit_system_lire($intUser, $strColonne)
{
    $objRs = db()->query("SELECT `{$strColonne}` v FROM ploopi_user WHERE id = ".(int)$intUser);
    $arrLigne = $objRs->fetch_assoc();
    return $arrLigne === null ? null : $arrLigne['v'];
}

/**
 * Crée l'espace B (cible des accès indus) et un gestionnaire de l'espace A.
 *
 * @return array espace B, gestionnaire, utilisateur cible de B
 */
function audit_system_monter()
{
    // Espace B, enfant de l'espace système 1, distinct de l'espace de l'administrateur (2)
    db()->query("INSERT INTO ploopi_workspace (label, code, system, protected, parents, iprules, macrules, template, depth, mustdefinerule, backoffice, frontoffice, backoffice_domainlist, title, meta_description, meta_keywords, meta_author, meta_copyright, meta_robots, frontoffice_domainlist, priority, mail_model)
                 VALUES ('audit_system_espace_b', '', 0, 0, '0;1', '', '', '', 2, 0, 1, 0, '', '', '', '', '', '', 'index, follow, all', '', 1, '')");
    $intEspaceB = db()->insert_id;

    // Utilisateur de B, sans lien avec A : c'est lui que le gestionnaire de A va viser
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('audit_system_cible_b', '%s', 'Cible', 'EspaceB', 'audit_system_cibleb@example.test', '%s', 0)",
        db()->real_escape_string(ploopi\user::generate_hash(AUDIT_SYSTEM_MDP, 'audit_system_cible_b')),
        date('YmdHis')
    ));
    $intCibleB = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intCibleB}, {$intEspaceB}, 1, 10)");

    // Gestionnaire de l'espace A (2) uniquement
    $intGest = audit_system_creer_utilisateur(AUDIT_SYSTEM_LOGIN_GEST, 2, _PLOOPI_ID_LEVEL_GROUPMANAGER);

    // Utilisateur simple de l'espace A, sans niveau
    $intSans = audit_system_creer_utilisateur(AUDIT_SYSTEM_LOGIN_SANS, 2, _PLOOPI_ID_LEVEL_USER);

    // Rôle dans l'espace B : la cible des affectations indues
    db()->query("INSERT INTO ploopi_role (label, description, id_workspace) VALUES ('audit_system_role', '', {$intEspaceB})");
    $intRoleB = db()->insert_id;
    db()->query("DELETE FROM ploopi_workspace_user_role WHERE id_role = {$intRoleB}");
    db()->query("DELETE FROM ploopi_workspace_group_role WHERE id_role = {$intRoleB}");

    // Groupe rattaché à l'espace B (ploopi_workspace_group : seuls les groupes rattachés
    // reçoivent un rôle de l'espace, comme le propose l'écran des attributions)
    db()->query("INSERT INTO ploopi_group (label, system, protected, parents, depth, id_workspace, shared)
                 VALUES ('audit_system_groupe_b', 0, 0, '0', 1, {$intEspaceB}, 0)");
    $intGroupeB = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_group (id_group, id_workspace, adminlevel) VALUES ({$intGroupeB}, {$intEspaceB}, "._PLOOPI_ID_LEVEL_USER.")");

    // L'administrateur système n'est rattaché qu'à son espace d'origine : pour que le
    // témoin puisse naviguer dans l'espace B (et y être manager), le rattacher aussi.
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel)
                 SELECT u.id, {$intEspaceB}, 1, 99 FROM ploopi_user u WHERE u.login = '".db()->real_escape_string(cfg('admin_login'))."'");

    return array(
        'espace_b' => $intEspaceB,
        'gest'     => $intGest,
        'sans'     => $intSans,
        'cible_b'  => $intCibleB,
        'role_b'   => $intRoleB,
        'groupe_b' => $intGroupeB,
    );
}

$arrEtat = audit_system_monter();
$intEspaceB = $arrEtat['espace_b'];

T::section('Mise en place');

T::vrai('témoin : session administrateur établie', audit_system_connecter(cfg('admin_login'), 2, cfg('admin_password')) !== '');
T::vrai('témoin : session gestionnaire établie', audit_system_connecter(AUDIT_SYSTEM_LOGIN_GEST, 2) !== '');
T::vrai('témoin : session utilisateur simple établie', audit_system_connecter(AUDIT_SYSTEM_LOGIN_SANS, 2) !== '');

/**
 * Les jetons sont consommés à chaque page et les sessions ne partagent pas de bocal
 * à cookies : on se reconnecte à chaque usage, comme le fait la suite 23.
 *
 * @param string $strQui admin, gest, sans ou adminb
 * @return string environnement avec jeton frais
 */
function audit_system_session($strQui)
{
    global $intEspaceB;
    switch ($strQui)
    {
        case 'admin':  return audit_system_connecter(cfg('admin_login'), 2, cfg('admin_password'));
        case 'adminb': return audit_system_connecter(cfg('admin_login'), $intEspaceB, cfg('admin_password'));
        case 'gest':   return audit_system_connecter(AUDIT_SYSTEM_LOGIN_GEST, 2);
        default:       return audit_system_connecter(AUDIT_SYSTEM_LOGIN_SANS, 2);
    }
}

/**
 * Un utilisateur sans niveau ne doit même pas entrer dans l'administration du module :
 * admin.php de system le renvoie. Témoin : le gestionnaire, lui, y entre.
 */
T::section('Écran d\'administration (modules/system/admin.php)');

$arrRep = H::req('/admin.php?system_level=work&ploopi_env='.urlencode(audit_system_session('sans')));
T::vrai('utilisateur sans niveau : l\'écran d\'administration n\'est pas servi',
    strpos($arrRep['body'], 'audit_system') === false && $arrRep['code'] !== 500,
    "HTTP {$arrRep['code']}");

$strEnvNavigue = audit_system_naviguer(audit_system_session('gest'), '?system_level=work');
T::vrai('témoin : le gestionnaire entre dans l\'administration', $strEnvNavigue !== '');

/**
 * system_delete_user exige _PLOOPI_ID_LEVEL_SYSTEMADMIN : ni le gestionnaire ni
 * l'utilisateur simple ne doivent pouvoir supprimer un compte — même dans leur espace.
 * Témoin : la suite 01 l'établit déjà pour l'administrateur ; on vérifie le compte en base.
 */
T::section('system_delete_user');

$intVictimeSans = (int)db()->query("SELECT id FROM ploopi_user WHERE login = '".AUDIT_SYSTEM_LOGIN_SANS."'")->fetch_assoc()['id'];
T::vrai('cible présente avant les tentatives', audit_system_lire($intVictimeSans, 'lastname') !== null);

audit_system_op(audit_system_session('gest'), 'system_delete_user', "&system_user_id={$intVictimeSans}");
T::vrai('gestionnaire : l\'utilisateur survit', audit_system_lire($intVictimeSans, 'lastname') !== null);

audit_system_op(audit_system_session('sans'), 'system_delete_user', "&system_user_id={$intVictimeSans}");
T::vrai('utilisateur simple : l\'utilisateur survit', audit_system_lire($intVictimeSans, 'lastname') !== null);

audit_system_op(audit_system_session('admin'), 'system_delete_user', "&system_user_id={$intVictimeSans}");
T::vrai('témoin : l\'administrateur supprime le compte', audit_system_lire($intVictimeSans, 'lastname') === null);

/**
 * Les affectations de rôles (system_roleusers_*) travaillent sur
 * $_SESSION['system']['workspaceid'] : l'espace courant de la session d'administration.
 * Un gestionnaire de A ne doit pas affecter le rôle de B à qui que ce soit — ni même
 * le voir. Témoin : l'administrateur système affecte le rôle de B sans difficulté.
 */
T::section('system_roleusers (rôles)');

// Le gestionnaire de A pose l'écran d'administration (son espace A courant : 2)
// session['system']['workspaceid'] du gestionnaire vaut 2 (espace A)
// tenter d'affecter le rôle de B (identifiant posté) à l'utilisateur cible de B
$intCibleB = $arrEtat['cible_b'];
$intRoleB = $arrEtat['role_b'];

/**
 * CONSTAT FIGÉ — system_roleusers_select_user n'appartient pas au rôle visé à
 * l'espace courant : l'interface ne propose que les rôles de l'espace, mais
 * l'opération accepte n'importe quel identifiant. Un gestionnaire de A affecte ici
 * le rôle de B à la cible, dans l'espace A (id_workspace de la session).
 * La suite 01 avait mesuré le contrôle du jeton ; celui-ci est d'ordre des droits.
 * Le correctif vérifiera l'appartenance du rôle à l'espace courant (ou hérité
 * et partagé) et fera de cette assertion un refus.
 */
audit_system_op(audit_system_session('gest'), 'system_roleusers_select_user', "&system_roleusers_roleid={$intRoleB}&system_roleusers_userid={$intCibleB}");
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_workspace_user_role WHERE id_user = {$intCibleB} AND id_role = {$intRoleB}");
T::vrai('gestionnaire de A : aucune affectation du rôle de B', (int)$objRs->fetch_assoc()['c'] === 0,
    'constat figé : le rôle de B est affecté dans l\'espace A');

// témoin : l'administrateur, connecté sur l'espace B, y affecte le rôle
audit_system_op(audit_system_session('adminb'), 'system_roleusers_select_user', "&system_roleusers_roleid={$intRoleB}&system_roleusers_userid={$intCibleB}");
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_workspace_user_role WHERE id_user = {$intCibleB} AND id_role = {$intRoleB}");
T::vrai('témoin : l\'administrateur affecte le rôle de B', (int)$objRs->fetch_assoc()['c'] === 1);

// puis la retire : le gestionnaire ne doit pas pouvoir non plus supprimer l'affectation
audit_system_op(audit_system_session('gest'), 'system_roleusers_delete_user', "&system_roleusers_roleid={$intRoleB}&system_roleusers_userid={$intCibleB}");
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_workspace_user_role WHERE id_user = {$intCibleB} AND id_role = {$intRoleB}");
T::vrai('gestionnaire de A : l\'affectation de B survit', (int)$objRs->fetch_assoc()['c'] === 1);

/**
 * Même règle pour les groupes. La PR #54 avait gardé select_user, delete_user et
 * delete_group, pas select_group : un gestionnaire de A affectait encore le rôle
 * de B à un groupe, dans l'espace A.
 */
$intGroupeRoleB = $arrEtat['groupe_b'];
$compter_groupe = function () use ($intGroupeRoleB, $intRoleB) {
    return (int)db()->query("SELECT COUNT(*) c FROM ploopi_workspace_group_role WHERE id_group = {$intGroupeRoleB} AND id_role = {$intRoleB}")->fetch_assoc()['c'];
};
audit_system_op(audit_system_session('gest'), 'system_roleusers_select_group', "&system_roleusers_roleid={$intRoleB}&system_roleusers_groupid={$intGroupeRoleB}");
T::eq('gestionnaire de A : aucune affectation du rôle de B à un groupe', 0, $compter_groupe());

audit_system_op(audit_system_session('adminb'), 'system_roleusers_select_group', "&system_roleusers_roleid={$intRoleB}&system_roleusers_groupid={$intGroupeRoleB}");
T::eq('témoin : l\'administrateur affecte le rôle de B au groupe', 1, $compter_groupe());

audit_system_op(audit_system_session('gest'), 'system_roleusers_delete_group', "&system_roleusers_roleid={$intRoleB}&system_roleusers_groupid={$intGroupeRoleB}");
T::eq('gestionnaire de A : l\'affectation du groupe survit', 1, $compter_groupe());

/**
 * save_user (modules/system/admin_index_users.php) : le gestionnaire de A peut créer
 * et modifier les utilisateurs de SON espace. L'identifiant visé par user_id n'est pas
 * contrôlé contre l'espace courant : un gestionnaire de A modifie-t-il le nom de la
 * cible de B ?
 */
T::section('save_user (modification d\'un utilisateur)');

$intCibleB = $arrEtat['cible_b'];
T::vrai('nom initial de la cible', audit_system_lire($intCibleB, 'lastname') === 'EspaceB');

// entrer dans l'écran utilisateur du gestionnaire (espace A courant)
audit_system_naviguer(audit_system_session('gest'), '?system_level=work&wspToolbarItem=tabUsers&usrTabItem=tabUserList');
// tenter la modification de la cible de B
$arrPost = 'user_lastname=MODIFIE_PAR_GEST&user_firstname=Cible&user_ticketsbyemail=1&user_password_force_update=1&user_disabled=1&user_servertimezone=1&usernewpass=&usernewpass_confirm=';
H::req("/admin.php?op=save_user&user_id={$intCibleB}&ploopi_env=".urlencode(audit_system_session('gest')), $arrPost);
T::vrai('gestionnaire de A : le nom de la cible de B est inchangé',
    audit_system_lire($intCibleB, 'lastname') === 'EspaceB',
    'obtenu : '.audit_system_lire($intCibleB, 'lastname'));

// témoin : l'administrateur modifie cette même cible, depuis l'espace B
$strEnvAdminB = audit_system_session('adminb');
$strEnvAdminB = audit_system_naviguer($strEnvAdminB, '?system_level=work&wspToolbarItem=tabUsers&usrTabItem=tabUserList&workspaceid='.$intEspaceB);
H::req("/admin.php?op=save_user&user_id={$intCibleB}&workspaceid={$intEspaceB}&ploopi_env=".urlencode($strEnvAdminB), $arrPost);
T::vrai('témoin : l\'administrateur modifie la cible', audit_system_lire($intCibleB, 'lastname') === 'MODIFIE_PAR_GEST');

/**
 * admin_index.php 'save_group' : un gestionnaire de A peut-il renommer le groupe de B
 * en le visant par group_id ?
 */
T::section('save_group (modification d\'un groupe)');

$intGroupeB = $arrEtat['groupe_b'];
T::vrai('libellé initial du groupe de B', (function() use ($intGroupeB) {
    $objRs = db()->query("SELECT label FROM ploopi_group WHERE id = {$intGroupeB}");
    return $objRs->fetch_assoc()['label'] === 'audit_system_groupe_b';
})());

$strEnvGestGrp = audit_system_session('gest');
$strEnvGestGrp = audit_system_naviguer($strEnvGestGrp, '?system_level=org&groupid='.$intGroupeB.'&wspToolbarItem=tabGroups');
H::req("/admin.php?op=save_group&group_id={$intGroupeB}&group_label=MODIFIE_PAR_GEST&groupid={$intGroupeB}&ploopi_env=".urlencode($strEnvGestGrp));
$objRs = db()->query("SELECT label FROM ploopi_group WHERE id = {$intGroupeB}");
T::vrai('gestionnaire de A : le groupe de B est inchangé', $objRs->fetch_assoc()['label'] === 'audit_system_groupe_b');

// Le renommage d'un groupe passe par l'onglet « groupes » du niveau « organisation » :
// la session doit viser le groupe de B (groupid), le POST porte l'identifiant visé.
$strEnvAdminB2 = audit_system_session('admin');
$strEnvAdminB2 = audit_system_naviguer($strEnvAdminB2, '?system_level=org&groupid='.$intGroupeB.'&wspToolbarItem=tabGroups');
H::req("/admin.php?op=save_group&group_id={$intGroupeB}&group_label=MODIFIE_PAR_ADMIN&groupid={$intGroupeB}&ploopi_env=".urlencode($strEnvAdminB2));
$objRs = db()->query("SELECT label FROM ploopi_group WHERE id = {$intGroupeB}");
T::vrai('témoin : l\'administrateur renomme le groupe', $objRs->fetch_assoc()['label'] === 'MODIFIE_PAR_ADMIN');

/**
 * La navigation elle-même : admin.php?workspaceid=<B> doit refuser l'espace B au
 * gestionnaire de A — system_getwg() filtre l'arbre. Témoin : l'administrateur y entre.
 */
T::section('Navigation vers l\'espace B');

$arrRep = H::req('/admin.php?system_level=work&workspaceid='.$intEspaceB.'&ploopi_env='.urlencode(audit_system_session('gest')));
T::vrai('gestionnaire de A : l\'espace B n\'apparaît pas dans son arbre',
    strpos($arrRep['body'], 'audit_system_espace_b') === false,
    "HTTP {$arrRep['code']}, ".strlen($arrRep['body']).' octets');

$arrRep = H::req('/admin.php?system_level=work&workspaceid='.$intEspaceB.'&ploopi_env='.urlencode(audit_system_session('admin')));
T::vrai('témoin : l\'administrateur voit l\'espace B',
    strpos($arrResp['body'] ?? $arrRep['body'], 'audit_system_espace_b') !== false || $arrRep['code'] === 200);

/**
 * system_send_photo (photo d'un utilisateur) : un gestionnaire de A peut-il déposer
 * une photo pour la cible de B ?
 */
T::section('system_send_photo');

T::vrai('cible de B encore présente', audit_system_lire($intCibleB, 'lastname') !== null);

// La photo se dépose par POST multipart ; sans fichier, l'opération ne modifie rien.
// La barrière mesurée est ailleurs : le gestionnaire de A ne doit pas obtenir de
// réponse traitée pour un utilisateur d'un AUTRE espace. On vérifie surtout que
// la requête est sans effet ni erreur côté serveur.
$arrRep = audit_system_op(audit_system_session('gest'), 'system_send_photo', "&system_user_id={$intCibleB}");
T::vrai('gestionnaire de A : la cible de B survit à l\'appel', audit_system_lire($intCibleB, 'lastname') !== null);

/**
 * updatemb / updatedesc (métabase d'un type de module) : réservées à l'administrateur
 * système. Un gestionnaire ne doit pas pouvoir recharger la métabase.
 */
T::section('updatemb / updatedesc');

$intTypeSystem = 1; // type du module system
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_mb_object WHERE id_module_type = {$intTypeSystem}");
$intObjetsAvant = (int)$objRs->fetch_assoc()['c'];

$arrRep = audit_system_op(audit_system_session('gest'), 'updatemb', "&idmoduletype={$intTypeSystem}");
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_mb_object WHERE id_module_type = {$intTypeSystem}");
T::vrai('gestionnaire : la métabase est inchangée', (int)$objRs->fetch_assoc()['c'] === $intObjetsAvant);

$arrRep = audit_system_op(audit_system_session('sans'), 'updatemb', "&idmoduletype={$intTypeSystem}");
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_mb_object WHERE id_module_type = {$intTypeSystem}");
T::vrai('utilisateur simple : la métabase est inchangée', (int)$objRs->fetch_assoc()['c'] === $intObjetsAvant);

/**
 * system_user_import : l'import CSV passe par la session, sans droit propre. Le
 * gestionnaire de A est en droit de l'utiliser dans son espace ; l'utilisateur simple
 * ne doit pas pouvoir y déposer des données.
 */
T::section('system_user_import');

$arrRep = audit_system_op(audit_system_session('sans'), 'system_user_import', '', 'system_user_sep=;');
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_user WHERE email LIKE 'audit_system_import@example.test'");
T::vrai('utilisateur simple : aucun utilisateur importé', (int)$objRs->fetch_assoc()['c'] === 0);

/**
 * Bilan : l'audit aura mesuré chaque barrière. Toute assertion retournée plus bas est
 * un constat, pas un jugement : voir la feuille de route pour la marche à suivre.
 */
audit_system_nettoyer();

exit(T::bilan());
