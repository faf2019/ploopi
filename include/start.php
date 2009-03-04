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
 * Charge les constantes g�n�riques, les fonctions et classes principales.
 * Connecte l'utilisateur, initialise la session, charge les param�tres.
 *
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Chargement de la partie commune de chargement de l'environnement
 */

include './include/start/common.php';

/**
 * Chargement fonctions g�n�riques
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
        // parse previous uri to detect "ploopi_mainmenu" param
        // if "ploopi_mainmenu" param then redirect after connect

        // parse previous uri to detect "ploopi_mainmenu" param
        // if "ploopi_mainmenu" param then redirect after connect

        if (!empty($_SESSION['ploopi']['uri']))
        {
            $_uri = (empty($_SERVER['QUERY_STRING'])) ? '' : "admin.php?{$_SERVER['QUERY_STRING']}";
            $_purl = parse_url($_SESSION['ploopi']['uri']);
            $_params = array();

            foreach(explode('&',$_purl['query']) as $param)
            {
                if (strstr($param, '=')) list($key, $value) = explode('=',$param);
                else {$key = $param; $value = '';}

                $_REQUEST[$key] = $_GET[$key] = ploopi_filtervar($value);

                if ($key == 'ploopi_url')
                {
                    require_once './include/classes/cipher.php';
                    $cipher = new ploopi_cipher();
                    $ploopi_url = $cipher->decrypt($_GET['ploopi_url']);

                    foreach(explode('&',$ploopi_url) as $param)
                    {
                        if (strstr($param, '=')) list($key, $value) = explode('=',$param);
                        else {$key = $param; $value = '';}

                        $_REQUEST[$key] = $_GET[$key] = ploopi_filtervar($value);
                    }
                }
            }

            $login_redirect = (!empty($_SESSION['ploopi']['uri']) && empty($uri) && !empty($_params['ploopi_mainmenu'])) ? $_SESSION['ploopi']['uri'] : '';
            unset($_uri);
            unset($_purl);
            unset($_params);
        }

        ploopi_session_reset();
        $ploopi_initsession = true;

        $fields = $db->fetchrow();

        if (!empty($fields['date_expire']))
        {
            if ($fields['date_expire'] <= ploopi_createtimestamp())
            {
                ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                ploopi_logout(_PLOOPI_ERROR_LOGINEXPIRE);
            }
        }
        $_SESSION['ploopi']['connected']    = 1;
        $_SESSION['ploopi']['login']        = $fields['login'];
        $_SESSION['ploopi']['userid']   = $fields['id'];

        $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;

        ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_OK, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
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
 * Permet de g�rer le cas ou la session est partiellement charg�e (on passe d'abord par index-quick.php...)
 */
$ploopi_initsession |= empty($_SESSION['ploopi']['mode']);

if ($ploopi_initsession)
{
    include './include/start/initsession.php';

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

        $workspace_allowed = 0;

        foreach ($user_workspaces as $wid => $fields)
        {
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
                        $_SESSION['ploopi']['workspaces'][$wid]['children']  = $workspace->getchildren();
                        $_SESSION['ploopi']['workspaces'][$wid]['parents'] = explode(';',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
                        $_SESSION['ploopi']['workspaces'][$wid]['brothers']  = $workspace->getbrothers();
                        $_SESSION['ploopi']['workspaces'][$wid]['list_parents'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
                        $_SESSION['ploopi']['workspaces'][$wid]['list_children'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['children']);
                        $_SESSION['ploopi']['workspaces'][$wid]['list_brothers'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['brothers']);
                        $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);

                        $workspace_allowed++;
                    }
                }
            }
        }

        if (!$workspace_allowed)
        {
            session_destroy();
            ploopi_redirect("admin.php?ploopi_errorcode="._PLOOPI_ERROR_NOWORKSPACEDEFINED);
        }

        // sorting workspaces by depth
        uksort ($_SESSION['ploopi']['workspaces'], create_function('$a,$b', 'return (intval($_SESSION[\'ploopi\'][\'workspaces\'][$b][\'depth\'])<intval($_SESSION[\'ploopi\'][\'workspaces\'][$a][\'depth\']));'));

        // create a list with allowed workspaces only
        $_SESSION['ploopi']['workspaces_allowed'] = array();
        foreach($_SESSION['ploopi']['workspaces'] as $workspace)
        {
            if (!empty($workspace['adminlevel'])) $_SESSION['ploopi']['workspaces_allowed'][] = $workspace['id'];
        }

        if (!isset($_REQUEST['reloadsession'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;
    }
}

if (!$_SESSION['ploopi']['paramloaded']) include './include/start/load_param.php';

if (!empty($login_redirect)) ploopi_redirect($login_redirect, false);

/**
 * Switch entre backoffice et frontoffice en fonction du nom du script appelant (admin.php/index.php)
 */

switch($_SESSION['ploopi']['scriptname'])
{
    case 'admin.php':
    case 'admin-light.php':
        $_SESSION['ploopi']['mode'] = 'backoffice';
    break;

    case 'index.php':
        if ((!empty($_GET['webedit_mode'])) && $_SESSION['ploopi']['connected'])
        {
            // cas sp�cial du mode de rendu public du module Webedit (on utilise le rendu frontoffice sans activer tout le processus)
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

        ///////////////////////////////////////////////////////////////////////////
        // SWITCH MAIN MENU (Workspaces, Profile, etc.)
        ///////////////////////////////////////////////////////////////////////////
        if (isset($ploopi_mainmenu) && $ploopi_mainmenu != $_SESSION['ploopi']['mainmenu']) // new main menu selected
        {
            $_SESSION['ploopi']['mainmenu'] = $ploopi_mainmenu;

            echo $_SESSION['ploopi']['backoffice']['workspaceid'] = $_SESSION['ploopi']['workspaces_allowed'][0];

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
        
        ///////////////////////////////////////////////////////////////////////////
        // SWITCH WORKSPACE
        ///////////////////////////////////////////////////////////////////////////
        
        // Traitement d'un car particulier li� au d�tachement d'un utilisateur � l'espace qu'il consulte 
        if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]))
        {
            $ploopi_workspaceid = $_SESSION['ploopi']['hosts']['backoffice'][0];
        }
        
        if (isset($ploopi_workspaceid) && $_SESSION['ploopi']['backoffice']['workspaceid'] != $ploopi_workspaceid && isset($_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['backoffice']) // new group selected
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

        if (!isset($ploopi_moduleid) && $_SESSION['ploopi']['backoffice']['moduleid'] == '' && !empty($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]['modules']))
        {
            $autoconnect_modules = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['backoffice']['workspaceid']]['modules'];
            $autoconnect_workspaceid = $_SESSION['ploopi']['backoffice']['workspaceid'];

            foreach($autoconnect_modules as $id => $autoconnect_module_id)
            {
                if ($_SESSION['ploopi']['modules'][$autoconnect_module_id]['active'] && $_SESSION['ploopi']['modules'][$autoconnect_module_id]['autoconnect'])
                {
                    if (($_SESSION['ploopi']['connected'] || (!$_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['modules'][$autoconnect_module_id]['public'])) && !isset($ploopi_moduleid) && $_SESSION['ploopi']['backoffice']['moduleid'] == '')
                    {
                        $ploopi_moduleid = $autoconnect_module_id;
                        $ploopi_action = 'public';
                    }
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
        _PLOOPI_VIEWMODE_GLOBAL     => _PLOOPI_LABEL_VIEWMODE_GLOBAL
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

if (session_id()!='')
{
    if (_PLOOPI_SESSIONTIME <= 86400) $timestplimit = ploopi_createtimestamp() - _PLOOPI_SESSIONTIME;
    else $timestplimit = ploopi_createtimestamp() - 86400;
    $db->query("DELETE FROM ploopi_connecteduser WHERE timestp < {$timestplimit}");
    $connecteduser = new connecteduser();
    $connecteduser->open(session_id());
    $connecteduser->fields['sid'] = session_id();
    $connecteduser->fields['ip'] = implode(',', $_SESSION['ploopi']['remote_ip']);
    $connecteduser->fields['domain'] = (empty($_SESSION['ploopi']['host'])) ? '' : $_SESSION['ploopi']['host'];
    $connecteduser->fields['timestp'] = ploopi_createtimestamp();
    $connecteduser->fields['user_id'] = $_SESSION['ploopi']['userid'];
    $connecteduser->fields['workspace_id'] = $_SESSION['ploopi']['workspaceid'];
    $connecteduser->fields['module_id'] = $_SESSION['ploopi']['moduleid'];
    $connecteduser->fields['timestp'] = ploopi_createtimestamp();
    $connecteduser->save();
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

if (!$_SESSION['ploopi']['connected'])
{
    // can't be admin and not connected
    if ($_SESSION['ploopi']['action'] == 'admin')
    {
        $_SESSION['ploopi']['action'] = 'public';
        $ploopi_errornum = 1;
    }

    // can't call site/meta/system modules and being not connected
    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM))
    {
        $ploopi_errornum = 2;
    }

    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])))
    {
        $ploopi_errornum = 3;
    }

    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['active']))
    {
        $ploopi_errornum = 5;
    }
}
else
{
    // test moduleid
    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])))
    {
        $ploopi_errornum = 3;
    }

    // test if module is active
    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['active']))
    {
        $ploopi_errornum = 5;
    }

    // test workspaceid
    if (!$ploopi_errornum && ($_SESSION['ploopi']['workspaceid']!= '' && !isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']])))
    {
        $ploopi_errornum = 6;
    }
}

if ($ploopi_errornum)
{
    session_destroy();
    echo "<html><body><div style=\"text-align:center;\"><br /><br /><h1>Erreur de s�curit�</h1>reconnectez vous ou fermez votre navigateur ou contactez l'administrateur syst�me<br /><br /><b>erreur : $ploopi_errornum</b><br /><br /><a href=\"admin.php\">continuer</a></div></body></html>";
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
