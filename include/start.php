<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Script de chargement de l'environnement Ploopi.
 * Charge les fonctions et classes principales.
 * Connecte l'utilisateur, initialise la session, charge les paramètres.
 *
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Chargement fonctions génériques
 */

include_once './include/start/functions.php';

/**
 * Chargement des classes principales
 */

include_once './include/classes/user.php';
include_once './include/classes/group.php';
include_once './include/classes/workspace.php';
include_once './include/classes/param.php';

/**
 * Gestion de la connexion d'un utilisateur
 */

unset($ploopi_login);
unset($ploopi_password);

if (isset($_REQUEST['ploopi_login'])) $ploopi_login = $_REQUEST['ploopi_login'];
if (isset($_REQUEST['ploopi_password'])) $ploopi_password = $_REQUEST['ploopi_password'];

if ((!empty($ploopi_login) && !empty($ploopi_password)))
{
    $sql =  "
            SELECT      *
            FROM        ploopi_user
            WHERE       login = '".$db->addslashes($ploopi_login)."'
            AND         password = '".md5(_PLOOPI_SECRETKEY."/{$ploopi_login}/".md5($ploopi_password))."'
            ";

    $db->query($sql);

    if ($db->numrows() == 1) // 1 user found
    {
        // Gestion de la redirection après login (en fonction de l'url de provenance et du script d'authentification)
        $arrReferer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : array(); // Provenance
        $arrRequest = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : array();  // Demande d'authentification

        $strRefererHost = isset($arrReferer['host']) ? $arrReferer['host'] : '';
        $strRequestHost = $_SERVER['HTTP_HOST'];

        $strRefererScript = isset($arrReferer['path']) ? basename($arrReferer['path']) : '';
        $strRequestScript = isset($arrRequest['path']) ? basename($arrRequest['path']) : '';

        $strLoginRedirect = '';

        // Même domaine, même script, redirection acceptée
        if ($strRefererHost == $strRequestHost && ($strRefererScript == $strRequestScript || $strRequestScript != 'admin.php'))
        {
            $strLoginRedirect = $_SERVER['HTTP_REFERER'];
        }
        // on force la redirection sur le domaine+script courant
        else $strLoginRedirect = _PLOOPI_BASEPATH.'/'.$strRequestScript;

        $fields = $db->fetchrow();

        if (!empty($fields['date_expire']))
        {
            if ($fields['date_expire'] <= ploopi_createtimestamp())
            {
                ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                ploopi_logout(_PLOOPI_ERROR_LOGINEXPIRE);
            }
        }

        $_SESSION['ploopi']['login'] = $fields['login'];
        $_SESSION['ploopi']['password'] = $ploopi_password;
        $_SESSION['ploopi']['userid'] = $fields['id'];
        $_SESSION['ploopi']['user'] = $fields;
        ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_OK, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);

        ploopi_session_reset();
        $ploopi_initsession = true;

        $_SESSION['ploopi']['login'] = $fields['login'];
        $_SESSION['ploopi']['password'] = $ploopi_password;
        $_SESSION['ploopi']['userid'] = $fields['id'];
        $_SESSION['ploopi']['user'] = $fields;
    }
    else
    {
        ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
        ploopi_logout(_PLOOPI_ERROR_LOGINERROR);
    }
}

/**
 * Permet de forcer un rechargement de session
 */
$ploopi_initsession |= isset($_REQUEST['reloadsession']);

/**
 * Permet de gérer le cas ou la session est partiellement chargée (on passe d'abord par index-quick.php...)
 */
$ploopi_initsession |= empty($_SESSION['ploopi']['mode']);

if ($ploopi_initsession) include './include/start/initsession.php';

/**
 * Switch entre backoffice et frontoffice en fonction du nom du script appelant (admin.php/index.php) et de la config du portail
 */

switch($ploopi_access_script)
{
    case 'admin':
    case 'admin-light':
        $_SESSION['ploopi']['mode'] = 'backoffice';
    break;

    case 'index':
    case 'index-light':
        if ((!empty($_GET['webedit_mode'])) && isset($_SESSION['ploopi']['backoffice']['connected']) && $_SESSION['ploopi']['backoffice']['connected'] && isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['backoffice']['moduleid']]) && $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['backoffice']['moduleid']]['moduletype'] == 'webedit')
        {
            // cas spécial du mode de rendu public du module Webedit (on utilise le rendu frontoffice sans activer tout le processus)
            $newmode = 'frontoffice';
            $_SESSION['ploopi']['frontoffice']['workspaceid'] = $_SESSION['ploopi']['backoffice']['workspaceid'];
            $_SESSION['ploopi']['frontoffice']['moduleid'] = $_SESSION['ploopi']['backoffice']['moduleid'];
        }
        else
        {
            $newmode = (_PLOOPI_FRONTOFFICE && is_dir('./modules/webedit/') && isset($_SESSION['ploopi']['hosts']['frontoffice'][0])) ? 'frontoffice' : 'backoffice';

            if ($_SESSION['ploopi']['mode'] != $newmode && $newmode == 'frontoffice')
            {

                if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]]['webeditmoduleid']))
                {
                    // on cherche le module webedit
                    $db->query( "
                                select      ploopi_module_workspace.id_module

                                from        ploopi_module,
                                            ploopi_module_type,
                                            ploopi_module_workspace

                                where       ploopi_module.id_module_type = ploopi_module_type.id
                                and         (ploopi_module_type.label = 'webedit')
                                and         ploopi_module.id = ploopi_module_workspace.id_module
                                and         ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['hosts']['frontoffice'][0]}
                                ");

                    if ($fields = $db->fetchrow()) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['frontoffice'][0]]['webeditmoduleid'] = $fields['id_module'];
                    else $newmode = 'backoffice';
                }

                if ($newmode == 'frontoffice')
                {
                    $_SESSION['ploopi']['frontoffice']['workspaceid'] = $_SESSION['ploopi']['hosts']['frontoffice'][0];
                    $_SESSION['ploopi']['frontoffice']['moduleid'] = $_SESSION['ploopi']['webeditmoduleid'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['frontoffice']['workspaceid']]['webeditmoduleid'];
                }
            }
        }

        $_SESSION['ploopi']['mode'] = $newmode;

    break;

}

if ($ploopi_initsession)
{
    /**
     * Chargement du profil utilisateur
     */

    if ($_SESSION['ploopi']['userid'] != 0)
    {
        include './include/start/load_param.php';

        $user = new user();
        if (!$user->open($_SESSION['ploopi']['userid'])) ploopi_logout();

        $_SESSION['ploopi']['user'] = $user->fields;

        if ($_SESSION['ploopi']['user']['servertimezone']) $_SESSION['ploopi']['user']['timezone'] = $_SESSION['ploopi']['timezone'];

        $_SESSION['ploopi']['actions'] = array();
        $_SESSION['ploopi']['actions'] = $user->getactions($_SESSION['ploopi']['actions']);

        // get all workspaces of current user
        $user_workspaces = $user->getworkspaces();

        $workspace = new workspace();
        $workspace->fields['id'] = _PLOOPI_SYSTEMGROUP;
        $_SESSION['ploopi']['system_modules'] = $workspace->getmodules(true);

        $_SESSION['ploopi']['frontoffice']['connected'] = 0;
        $_SESSION['ploopi']['backoffice']['connected'] = 0;

        foreach ($user_workspaces as $wid => $fields)
        {
            if (in_array($wid,$_SESSION['ploopi']['hosts']['frontoffice']) || $fields['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN)
            {
                $_SESSION['ploopi']['frontoffice']['connected'] = 1;
            }

            if (in_array($wid,$_SESSION['ploopi']['hosts']['backoffice']) || $fields['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN)
            {
                $adminlevel = $fields['adminlevel'];

                $workspace = new workspace();
                $workspace->fields = $fields;

                $iprules = ploopi_getiprules($fields['iprules']);

                if (ploopi_isipvalid($iprules))
                {
                    if (!empty($fields['groups']))
                    {
                        foreach($fields['groups'] as $idg)
                        {
                            $grp = new group();
                            if ($grp->open($idg)) $_SESSION['ploopi']['actions'] = $grp->getactions($_SESSION['ploopi']['actions']);
                        }
                    }

                    $workspace_ok = true;

                    if ($workspace->fields['mustdefinerule']) $workspace_ok = (isset($_SESSION['ploopi']['actions'][$wid])  || ($gu_exists && $group_user->fields['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN));

                    if ($workspace_ok)
                    {
                        //$_SESSION['ploopi']['workspaces'][$gid] = $fields;
                        $_SESSION['ploopi']['workspaces'][$wid] = $workspaces[$wid];
                        $_SESSION['ploopi']['workspaces'][$wid]['adminlevel']  = $adminlevel;
                        $_SESSION['ploopi']['workspaces'][$wid]['backoffice']  = 1;
                        $_SESSION['ploopi']['workspaces'][$wid]['children']  = $workspace->getchildren();
                        $_SESSION['ploopi']['workspaces'][$wid]['parents'] = explode(';',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
                        $_SESSION['ploopi']['workspaces'][$wid]['brothers']  = $workspace->getbrothers();
                        $_SESSION['ploopi']['workspaces'][$wid]['list_parents'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
                        $_SESSION['ploopi']['workspaces'][$wid]['list_children'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['children']);
                        $_SESSION['ploopi']['workspaces'][$wid]['list_brothers'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['brothers']);
                        $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);

                        $_SESSION['ploopi']['backoffice']['connected'] = 1;
                    }
                }
            }
        }


        if (!$_SESSION['ploopi']['frontoffice']['connected'] && !$_SESSION['ploopi']['backoffice']['connected'] || (!$_SESSION['ploopi']['backoffice']['connected'] && $_SESSION['ploopi']['mode'] == 'backoffice'))
        {
            ploopi_logout(_PLOOPI_ERROR_NOWORKSPACEDEFINED);
        }

        // sorting workspaces by priority/label
        uksort ($_SESSION['ploopi']['workspaces'], create_function('$a,$b', 'return (sprintf("%03d_%s", intval($_SESSION[\'ploopi\'][\'workspaces\'][$b][\'priority\']), $_SESSION[\'ploopi\'][\'workspaces\'][$b][\'label\']) < sprintf("%03d_%s", intval($_SESSION[\'ploopi\'][\'workspaces\'][$a][\'priority\']), $_SESSION[\'ploopi\'][\'workspaces\'][$a][\'label\']));'));

        // create a list with allowed workspaces only
        $_SESSION['ploopi']['workspaces_allowed'] = array();
        foreach($_SESSION['ploopi']['workspaces'] as $workspace) if (!empty($workspace['adminlevel'])) $_SESSION['ploopi']['workspaces_allowed'][] = $workspace['id'];

        if (!isset($_REQUEST['reloadsession'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;
    }
}

if (!$_SESSION['ploopi']['paramloaded']) include './include/start/load_param.php';

if (!empty($strLoginRedirect)) ploopi_redirect($strLoginRedirect, false, false);
unset($strLoginRedirect);

// Indicateur global de connexion
$_SESSION['ploopi']['connected'] = isset($_SESSION['ploopi'][$_SESSION['ploopi']['mode']]['connected']) && $_SESSION['ploopi'][$_SESSION['ploopi']['mode']]['connected'];

///////////////////////////////////////////////////////////////////////////
// ADMIN SWITCHES
///////////////////////////////////////////////////////////////////////////

if ($_SESSION['ploopi']['mode'] == 'backoffice')
{
    if ($_SESSION['ploopi']['connected'])
    {
        if (isset($_REQUEST['ploopi_env']))
        {
            /**
             * ploopi_env contient ploopi_mainmenu (int), ploopi_workspaceid (int), ploopi_moduleid (int), ploopi_action (string)
             */
            $arrEnv = explode(',', $_REQUEST['ploopi_env']);

            if (isset($arrEnv[0]) && is_numeric($arrEnv[0]))
                $ploopi_mainmenu = $arrEnv[0];

            if (isset($arrEnv[1]) && is_numeric($arrEnv[1]))
                $ploopi_workspaceid = $arrEnv[1];

            if (isset($arrEnv[2]) && is_numeric($arrEnv[2]))
                $ploopi_moduleid = $arrEnv[2];

            if (isset($arrEnv[3]))
                $ploopi_action = $arrEnv[3];
        }

        if (isset($_REQUEST['ploopi_mainmenu']) && is_numeric($_REQUEST['ploopi_mainmenu']))
            $ploopi_mainmenu = $_REQUEST['ploopi_mainmenu'];

        if (isset($_REQUEST['ploopi_workspaceid']) && is_numeric($_REQUEST['ploopi_workspaceid']))
            $ploopi_workspaceid = $_REQUEST['ploopi_workspaceid'];

        if (isset($_REQUEST['ploopi_moduleid']) && is_numeric($_REQUEST['ploopi_moduleid']))
            $ploopi_moduleid = $_REQUEST['ploopi_moduleid'];

        if (isset($_REQUEST['ploopi_action']))
            $ploopi_action = $_REQUEST['ploopi_action'];

        // Cas particulier de la connexion ou du transfert front/back
        if (empty($ploopi_mainmenu) && empty($_SESSION['ploopi']['mainmenu'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;

        ///////////////////////////////////////////////////////////////////////////
        // SWITCH MAIN MENU (Workspaces, Profile, etc.)
        ///////////////////////////////////////////////////////////////////////////
        if (isset($ploopi_mainmenu) && $ploopi_mainmenu != $_SESSION['ploopi']['mainmenu']) // new main menu selected
        {
            $_SESSION['ploopi']['mainmenu'] = $ploopi_mainmenu;

            $_SESSION['ploopi']['backoffice']['workspaceid'] = $_SESSION['ploopi']['workspaces_allowed'][0];

            if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ploopi_loadparams();

            $_SESSION['ploopi']['backoffice']['moduleid'] = '';
            $_SESSION['ploopi']['action'] = 'public';
            $_SESSION['ploopi']['moduletype'] = '';
            $_SESSION['ploopi']['moduletypeid'] = '';
            $_SESSION['ploopi']['modulelabel'] = '';

            switch($_SESSION['ploopi']['mainmenu'])
            {
                case _PLOOPI_MENU_MYWORKSPACE:
                case _PLOOPI_MENU_SEARCH:
                //case _PLOOPI_MENU_ABOUT:
                    $ploopi_moduleid = _PLOOPI_MODULE_SYSTEM;
                    $ploopi_action = 'public';
                break;
            }
        }

        if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
        {

            ///////////////////////////////////////////////////////////////////////////
            // SWITCH WORKSPACE
            ///////////////////////////////////////////////////////////////////////////

            // Traitement d'un car particulier lié au détachement d'un utilisateur à l'espace qu'il consulte
            if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]))
            {
                $ploopi_workspaceid = $_SESSION['ploopi']['hosts']['backoffice'][0];
            }

            if (isset($_REQUEST['ploopi_switch_workspace']) || (isset($ploopi_workspaceid) && $_SESSION['ploopi']['backoffice']['workspaceid'] != $ploopi_workspaceid && isset($_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['backoffice'])) // new group selected
            {
                $_SESSION['ploopi']['mainmenu'] = _PLOOPI_MENU_WORKSPACES;
                $_SESSION['ploopi']['backoffice']['workspaceid'] = $ploopi_workspaceid;
                $_SESSION['ploopi']['backoffice']['moduleid'] = '';
                $_SESSION['ploopi']['action'] = 'public';
                $_SESSION['ploopi']['moduletype'] = '';
                $_SESSION['ploopi']['moduletypeid'] = '';
                $_SESSION['ploopi']['modulelabel'] = '';

                // load params
                ploopi_loadparams();
            }

            ///////////////////////////////////////////////////////////////////////////
            // LOOK FOR AUTOCONNECT MODULE
            ///////////////////////////////////////////////////////////////////////////

            if (!isset($ploopi_moduleid) && $_SESSION['ploopi']['backoffice']['moduleid'] == '')
            {
                $arrModules = &$_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]['modules'];
                $intAutoconnectModuleId = null;

                foreach($arrModules as $intModuleId)
                {
                    if (is_null($intAutoconnectModuleId) && $_SESSION['ploopi']['modules'][$intModuleId]['active'] && $_SESSION['ploopi']['modules'][$intModuleId]['autoconnect']) $intAutoconnectModuleId = $intModuleId;
                }

                if (is_null($intAutoconnectModuleId) && ploopi_ismanager()) $intAutoconnectModuleId = _PLOOPI_MODULE_SYSTEM;

                if (!is_null($intAutoconnectModuleId))
                {

                    $ploopi_moduleid = $intAutoconnectModuleId;

                    $ploopi_action = $intAutoconnectModuleId == _PLOOPI_MODULE_SYSTEM ? 'admin' : 'public';
                }
            }
        }

        ///////////////////////////////////////////////////////////////////////////
        // SWITCH MODULE
        ///////////////////////////////////////////////////////////////////////////

        if (isset($ploopi_moduleid) && $ploopi_moduleid != $_SESSION['ploopi']['backoffice']['moduleid']) // new module selected
        {
            $_SESSION['ploopi']['backoffice']['moduleid'] = $ploopi_moduleid;

            /**
            * New module selected
            * => Load module informations
            */

            $select =   "
                        SELECT  ploopi_module.id,
                                ploopi_module.id_module_type,
                                ploopi_module.label,
                                ploopi_module_type.label AS module_type

                        FROM    ploopi_module,
                                ploopi_module_type

                        WHERE   ploopi_module.id_module_type = ploopi_module_type.id
                        AND     ploopi_module.id = {$_SESSION['ploopi']['backoffice']['moduleid']}
                        ";

            $answer = $db->query($select);
            if ($fields = $db->fetchrow($answer))
            {
                $_SESSION['ploopi']['moduletype'] = $fields['module_type'];
                $_SESSION['ploopi']['moduletypeid'] = $fields['id_module_type'];
                $_SESSION['ploopi']['modulelabel'] = $fields['label'];
            }
        }

        // new action selected
        if (isset($ploopi_action)) $_SESSION['ploopi']['action'] = $ploopi_action;
    }

    if (empty($_SESSION['ploopi']['backoffice']['workspaceid'])) $_SESSION['ploopi']['backoffice']['workspaceid'] = $_SESSION['ploopi']['hosts']['backoffice'][0];

    ///////////////////////////////////////////////////////////////////////////
    // CHOOSE TEMPLATE
    ///////////////////////////////////////////////////////////////////////////

    $default_template = '';
    if (isset($_SESSION['ploopi']['hosts']['backoffice'][0]))
    {
        if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['backoffice'][0]]))
        {
            $default_template = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['backoffice'][0]]['template'];
        }
    }

    if ($_SESSION['ploopi']['backoffice']['workspaceid'] != '') $_SESSION['ploopi']['template_name'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]['template'];
    elseif (!empty($default_template)) $_SESSION['ploopi']['template_name'] = $default_template;

    if (empty($_SESSION['ploopi']['template_name']) || !file_exists("./templates/backoffice/{$_SESSION['ploopi']['template_name']}")) $_SESSION['ploopi']['template_name'] = _PLOOPI_DEFAULT_TEMPLATE;

    $_SESSION['ploopi']['template_path'] = "./templates/backoffice/{$_SESSION['ploopi']['template_name']}";

    $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['backoffice']['moduleid'];
    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['backoffice']['workspaceid'];
}
else
{
    $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['frontoffice']['moduleid'];
    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['frontoffice']['workspaceid'];
}

// shortcuts for admin & workspaceid
if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['adminlevel'])) $_SESSION['ploopi']['adminlevel'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['adminlevel'];
else $_SESSION['ploopi']['adminlevel'] = 0;

///////////////////////////////////////////////////////////////////////////
// LOAD LANGUAGE FILE
///////////////////////////////////////////////////////////////////////////

if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'] != 'french' && file_exists("./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php"))
{
    include_once "./lang/{$_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language']}.php";
}
else include_once "./lang/french.php"; // default language file (french)

// LANGUAGES LIST
/**
 include_once './include/classes/param.php';
 $param_type = new param_type();
 if (!isset($_SESSION['ploopi']['languages'])) $_SESSION['ploopi']['languages'] = $param_type->getallchoices(_PLOOPI_PARAMTYPE_LANGUAGE);
*/

// View modes for modules
$ploopi_viewmodes =
    array(
        _PLOOPI_VIEWMODE_UNDEFINED  => _PLOOPI_LABEL_VIEWMODE_UNDEFINED,
        _PLOOPI_VIEWMODE_PRIVATE    => _PLOOPI_LABEL_VIEWMODE_PRIVATE,
        _PLOOPI_VIEWMODE_DESC       => _PLOOPI_LABEL_VIEWMODE_DESC,
        _PLOOPI_VIEWMODE_ASC        => _PLOOPI_LABEL_VIEWMODE_ASC,
        _PLOOPI_VIEWMODE_GLOBAL     => _PLOOPI_LABEL_VIEWMODE_GLOBAL,
        _PLOOPI_VIEWMODE_ASCDESC   => _PLOOPI_LABEL_VIEWMODE_ASCDESC
    );

$ploopi_system_levels =
    array(
        _PLOOPI_ID_LEVEL_USER           => _PLOOPI_LEVEL_USER,
        _PLOOPI_ID_LEVEL_GROUPMANAGER   => _PLOOPI_LEVEL_GROUPMANAGER,
        _PLOOPI_ID_LEVEL_GROUPADMIN     => _PLOOPI_LEVEL_GROUPADMIN,
        _PLOOPI_ID_LEVEL_SYSTEMADMIN    => _PLOOPI_LEVEL_SYSTEMADMIN
    );

///////////////////////////////////////////////////////////////////////////
// UPDATE LIVE STATS
///////////////////////////////////////////////////////////////////////////
if (session_id() != '')
{
    $timestplimit = ploopi_timestamp_add(ploopi_createtimestamp(), 0, 0, -min( _PLOOPI_SESSIONTIME,  86400));
    $db->query("DELETE FROM ploopi_connecteduser WHERE timestp < {$timestplimit}");
    $objConnectedUser = new connecteduser();
    $objConnectedUser->open(session_id());
    $objConnectedUser->fields['sid'] = session_id();
    $objConnectedUser->fields['ip'] = implode(',', $_SESSION['ploopi']['remote_ip']);
    $objConnectedUser->fields['domain'] = (empty($_SESSION['ploopi']['host'])) ? '' : $_SESSION['ploopi']['host'];
    $objConnectedUser->fields['timestp'] = ploopi_createtimestamp();
    $objConnectedUser->fields['user_id'] = $_SESSION['ploopi']['userid'];
    $objConnectedUser->fields['workspace_id'] = $_SESSION['ploopi']['workspaceid'];
    $objConnectedUser->fields['module_id'] = $_SESSION['ploopi']['moduleid'];
    $objConnectedUser->fields['timestp'] = ploopi_createtimestamp();
    $objConnectedUser->save();
    $db->query("SELECT count(*) as c FROM ploopi_connecteduser WHERE user_id > 0");
    $row = $db->fetchrow();
    $_SESSION['ploopi']['connectedusers'] = $row['c'];

    $db->query("SELECT count(*) as c FROM ploopi_connecteduser WHERE user_id = 0");
    $row = $db->fetchrow();
    $_SESSION['ploopi']['anonymoususers'] = $row['c'];
}

///////////////////////////////////////////////////////////////////////////
// SOME SECURITY TESTS
///////////////////////////////////////////////////////////////////////////

$ploopi_errornum = 0;
if ($_SESSION['ploopi']['connected'])
{
    // teste moduleid
    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]))) $ploopi_errornum = 3;
    // test if module is active
    elseif (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['active'])) $ploopi_errornum = 5;

    // test workspaceid
    if (!$ploopi_errornum && ($_SESSION['ploopi']['workspaceid']!= '' && !isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]))) $ploopi_errornum = 6;
}

if ($ploopi_errornum)
{
    session_destroy();
    echo "<html><body><div style=\"text-align:center;\"><br /><br /><h1>Erreur de sécurité</h1>reconnectez vous ou fermez votre navigateur ou contactez l'administrateur système<br /><br /><b>erreur : $ploopi_errornum</b><br /><br /><a href=\"admin.php\">continuer</a></div></body></html>";
    ploopi_die();
}

$_SESSION['ploopi']['uri'] = (empty($_SERVER['QUERY_STRING'])) ? '' : "admin.php?{$_SERVER['QUERY_STRING']}";
$_SESSION['ploopi']['env'] =
    sprintf(
        "%s,%s,%s,%s",
        $_SESSION['ploopi']['mainmenu'],
        $_SESSION['ploopi']['workspaceid'],
        $_SESSION['ploopi']['moduleid'],
        $_SESSION['ploopi']['action']
    );
?>
