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
 * Charge les constantes génériques, les fonctions et classes principales.
 * Connecte l'utilisateur, initialise la session, charge les paramètres.
 * 
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Chargement de la partie commune de chargement de l'environnement
 */

include './include/start/common.php';

/**
 * Chargement constantes, fonctions, var globales, etc.
 */

include_once './include/start/global.php';

/**
 * Chargement des classes principales (dans la version light, pas grand chose)
 */

include_once './include/classes/user.php';
include_once './include/classes/group.php';
include_once './include/classes/workspace.php';
include_once './include/classes/log.php' ;
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
            $_uri = (empty($_SERVER['QUERY_STRING'])) ? '' : "{$scriptenv}?{$_SERVER['QUERY_STRING']}";
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

        if ($fields['date_expire'] != '' && $fields['date_expire'] != '00000000000000')
        {
            if ($fields['date_expire'] <= ploopi_createtimestamp())
            {
                ploopi_create_user_action_log(_SYSTEM_ACTION_LOGIN_ERR, $ploopi_login,_PLOOPI_MODULE_SYSTEM,_PLOOPI_MODULE_SYSTEM);
                session_destroy();
                ploopi_redirect("{$scriptenv}?ploopi_errorcode="._PLOOPI_ERROR_LOGINEXPIRE);
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
        session_destroy();
        ploopi_redirect("{$scriptenv}?ploopi_errorcode="._PLOOPI_ERROR_LOGINERROR);
    }
}

$ploopi_initsession |= isset($_GET['reloadsession']);


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
        $user->open($_SESSION['ploopi']['userid']);
        $_SESSION['ploopi']['user'] = $user->fields;

        if ($_SESSION['ploopi']['user']['servertimezone']) $_SESSION['ploopi']['user']['timezone'] = $_SESSION['ploopi']['timezone'];
        
        $_SESSION['ploopi']['actions'] = array();
        $user->getactions($_SESSION['ploopi']['actions']);

        // get all workspaces of current user
        $user_workspaces = $user->getworkspaces();
        
        $workspace = new workspace();
        $workspace->fields['id'] = _PLOOPI_SYSTEMGROUP;
        $_SESSION['ploopi']['system_modules'] = $workspace->getmodules(TRUE);

        $workspace_allowed = 0;

        foreach ($user_workspaces as $wid => $fields)
        {
            if (in_array($wid,$_SESSION['ploopi']['hosts']['admin']) || $fields['adminlevel'] == _PLOOPI_ID_LEVEL_SYSTEMADMIN)
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
                            if ($grp->open($idg)) $grp->getactions($_SESSION['ploopi']['actions']);
                        }
                    }

                    $workspace_ok = true;

                    if ($workspace->fields['mustdefinerule']) $workspace_ok = (isset($_SESSION['ploopi']['actions'][$wid])  || ($gu_exists && $group_user->fields['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN));

                    if ($workspace_ok)
                    {
                        //$_SESSION['ploopi']['workspaces'][$gid] = $fields;
                        $_SESSION['ploopi']['workspaces'][$wid] = $workspaces[$wid];
                        $_SESSION['ploopi']['workspaces'][$wid]['adminlevel']  = $adminlevel;
                        $_SESSION['ploopi']['workspaces'][$wid]['children']  = $workspace->getworkspacechildrenlite();
                        $_SESSION['ploopi']['workspaces'][$wid]['parents'] = explode(';',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
                        $_SESSION['ploopi']['workspaces'][$wid]['brothers']  = $workspace->getworkspacebrotherslite();
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
            ploopi_redirect("{$scriptenv}?ploopi_errorcode="._PLOOPI_ERROR_NOWORKSPACEDEFINED);
        }

        // sorting workspaces by depth
        uksort ($_SESSION['ploopi']['workspaces'], 'ploopi_workspace_sort');

        // create a list with allowed workspaces only
        $_SESSION['ploopi']['workspaces_allowed'] = array();
        foreach($_SESSION['ploopi']['workspaces'] as $workspace)
        {
            if (!empty($workspace['adminlevel'])) $_SESSION['ploopi']['workspaces_allowed'][] = $workspace['id'];
        }

        if (!isset($_GET['reloadsession'])) $ploopi_mainmenu = _PLOOPI_MENU_WORKSPACES;
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
        $_SESSION['ploopi']['mode'] = 'admin';
    break;

    case 'index.php':
        if ((!empty($_GET['webedit_mode'])) && $_SESSION['ploopi']['connected'])
        {
            // cas spécial du mode de rendu public du module Webedit (on utilise le rendu frontoffice sans activer tout le processus)
            $newmode = 'web';
        }
        else
        {
            $newmode = (_PLOOPI_FRONTOFFICE && is_dir('./modules/webedit/') && isset($_SESSION['ploopi']['hosts']['web'][0])) ? 'web' : 'admin';

            if ($_SESSION['ploopi']['mode'] != $newmode && $newmode == 'web')
            {
                if (!isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['web'][0]]['webeditmoduleid']))
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
                                and         ploopi_module_workspace.id_workspace = {$_SESSION['ploopi']['hosts']['web'][0]}
                                ");
            
                    if ($fields = $db->fetchrow()) $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['web'][0]]['webeditmoduleid'] = $fields['id_module'];
                    else $newmode = 'admin';
                }
                
                if ($newmode == 'web')
                {
                    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['hosts']['web'][0];
                    $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['webeditmoduleid'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['webeditmoduleid'];
                }
            }
        }

        $_SESSION['ploopi']['mode'] = $newmode;

    break;

}

///////////////////////////////////////////////////////////////////////////
// ADMIN SWITCHES
///////////////////////////////////////////////////////////////////////////

if ($_SESSION['ploopi']['mode'] == 'admin')
{
    if ($_SESSION['ploopi']['connected'])
    {
        if (isset($_REQUEST['ploopi_mainmenu']))        $ploopi_mainmenu = $_REQUEST['ploopi_mainmenu'];
        if (isset($_REQUEST['ploopi_workspaceid']))     $ploopi_workspaceid = $_REQUEST['ploopi_workspaceid'];
        if (isset($_REQUEST['ploopi_webworkspaceid'])) $ploopi_webworkspaceid = $_REQUEST['ploopi_webworkspaceid'];
        if (isset($_REQUEST['ploopi_moduleid']))        $ploopi_moduleid = $_REQUEST['ploopi_moduleid'];
        if (isset($_REQUEST['ploopi_action']))      $ploopi_action = $_REQUEST['ploopi_action'];
        if (isset($_REQUEST['ploopi_moduletabid']))     $ploopi_moduletabid = $_REQUEST['ploopi_moduletabid'];
        if (isset($_REQUEST['ploopi_moduleicon']))  $ploopi_moduletabid = $_REQUEST['ploopi_moduleicon'];


        ///////////////////////////////////////////////////////////////////////////
        // SWITCH MAIN MENU (Workspaces, Profile, etc.)
        ///////////////////////////////////////////////////////////////////////////

        if (isset($ploopi_mainmenu)) // new main menu selected
        {
            $_SESSION['ploopi']['mainmenu'] = $ploopi_mainmenu;

            $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['workspaces_allowed'][0];

            if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ploopi_loadparams();

            $_SESSION['ploopi']['moduleid'] = '';
            $_SESSION['ploopi']['action'] = 'public';
            $_SESSION['ploopi']['moduletabid'] = '';
            $_SESSION['ploopi']['moduleicon'] = '';
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

        if (isset($ploopi_workspaceid) && isset($_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['adminlevel']) && $_SESSION['ploopi']['workspaces'][$ploopi_workspaceid]['admin']) // new group selected
        {
            $_SESSION['ploopi']['mainmenu'] = _PLOOPI_MENU_WORKSPACES;
            $_SESSION['ploopi']['workspaceid'] = $ploopi_workspaceid;
            $_SESSION['ploopi']['moduleid'] = '';
            $_SESSION['ploopi']['action'] = 'public';
            $_SESSION['ploopi']['moduletabid'] = '';
            $_SESSION['ploopi']['moduleicon'] = '';
            $_SESSION['ploopi']['moduletype'] = '';
            $_SESSION['ploopi']['moduletypeid'] = '';
            $_SESSION['ploopi']['modulelabel'] = '';

            // load params
            ploopi_loadparams();
        }

        if (isset($ploopi_webworkspaceid)) // new webgroup selected
        {
            $_SESSION['ploopi']['webworkspaceid'] = $ploopi_webworkspaceid;
        }

        ///////////////////////////////////////////////////////////////////////////
        // LOOK FOR AUTOCONNECT MODULE
        ///////////////////////////////////////////////////////////////////////////

        if (!isset($ploopi_moduleid) && $_SESSION['ploopi']['moduleid'] == '' && !empty($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
        {
            $autoconnect_modules = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'];
            $autoconnect_workspaceid = $_SESSION['ploopi']['workspaceid'];

            foreach($autoconnect_modules as $id => $autoconnect_module_id)
            {
                if ($_SESSION['ploopi']['modules'][$autoconnect_module_id]['active'] && $_SESSION['ploopi']['modules'][$autoconnect_module_id]['autoconnect'])
                {
                    if (($_SESSION['ploopi']['connected'] || (!$_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['modules'][$autoconnect_module_id]['public'])) && !isset($ploopi_moduleid) && $_SESSION['ploopi']['moduleid'] == '')
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

        if (isset($ploopi_moduleid) && $ploopi_moduleid != $_SESSION['ploopi']['moduleid']) // new module selected
        {
            $_SESSION['ploopi']['moduleid'] = $ploopi_moduleid;
            $_SESSION['ploopi']['moduletabid']  = '';
            $_SESSION['ploopi']['moduleicon']   = '';

            $_SESSION['ploopi']['module_inter_id'] = '';

            /**
            * New module selected
            * => Load module informations
            */

            $select =
            "SELECT ploopi_module.id, ploopi_module.id_module_type, ploopi_module.label, ploopi_module_type.label AS module_type
            FROM ploopi_module, ploopi_module_type
            WHERE ploopi_module.id_module_type = ploopi_module_type.id
            AND ploopi_module.id = ".$_SESSION['ploopi']['moduleid'];

            $answer = $db->query($select);
            if ($fields = $db->fetchrow($answer))
            {
                /* IMPORTANT */
                /* USE IT TO KNOW INFORMATION ABOUT CURRENT SELECTED MODULE */
                $_SESSION['ploopi']['moduletype'] = $fields['module_type'];
                $_SESSION['ploopi']['moduletypeid'] = $fields['id_module_type'];
                $_SESSION['ploopi']['modulelabel'] = $fields['label'];
            }
        }

        // new action selected
        if (isset($ploopi_action)) $_SESSION['ploopi']['action'] = $ploopi_action;

        if (isset($ploopi_moduletabid)) // new moduletab selected
        {
            $_SESSION['ploopi']['moduletabid'] = $ploopi_moduletabid;
            $_SESSION['ploopi']['moduleicon'] = '';
        }

        if (isset($ploopi_moduleicon)) // new moduleicon selected
        {
            $_SESSION['ploopi']['moduleicon'] = $ploopi_moduleicon;
        }
    }

    if (empty($_SESSION['ploopi']['workspaceid'])) $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['hosts']['admin'][0];

    ///////////////////////////////////////////////////////////////////////////
    // CHOOSE TEMPLATE
    ///////////////////////////////////////////////////////////////////////////

    //if (empty($_SESSION['ploopi']['defaultskin']) && isset($_SESSION['ploopi']['hosts']['admin'][0]))
    if (isset($_SESSION['ploopi']['hosts']['admin'][0]))
    {
        if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['admin'][0]]))
        {
            $_SESSION['ploopi']['defaultskin'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['hosts']['admin'][0]]['admin_template'];
        }
    }

    if ($_SESSION['ploopi']['workspaceid'] != '') $_SESSION['ploopi']['template_name'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['admin_template'];
    elseif ($_SESSION['ploopi']['defaultskin'] != '') $_SESSION['ploopi']['template_name'] = $_SESSION['ploopi']['defaultskin'];

    if (empty($_SESSION['ploopi']['template_name']) || !file_exists("./templates/backoffice/{$_SESSION['ploopi']['template_name']}")) $_SESSION['ploopi']['template_name'] = _PLOOPI_DEFAULT_TEMPLATE;

    $_SESSION['ploopi']['template_path'] = "./templates/backoffice/{$_SESSION['ploopi']['template_name']}";

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
include_once './include/classes/param.php';
$param_type = new param_type();
//if (!isset($_SESSION['ploopi']['languages'])) $_SESSION['ploopi']['languages'] = $param_type->getallchoices(_PLOOPI_PARAMTYPE_LANGUAGE);


// View modes for modules
$ploopi_viewmodes = array(  _PLOOPI_VIEWMODE_UNDEFINED  => _PLOOPI_LABEL_VIEWMODE_UNDEFINED,
                            _PLOOPI_VIEWMODE_PRIVATE        => _PLOOPI_LABEL_VIEWMODE_PRIVATE,
                            _PLOOPI_VIEWMODE_DESC       => _PLOOPI_LABEL_VIEWMODE_DESC,
                            _PLOOPI_VIEWMODE_ASC            => _PLOOPI_LABEL_VIEWMODE_ASC,
                            _PLOOPI_VIEWMODE_GLOBAL     => _PLOOPI_LABEL_VIEWMODE_GLOBAL
                        );

$ploopi_system_levels = array(  _PLOOPI_ID_LEVEL_USER       => _PLOOPI_LEVEL_USER,
                                _PLOOPI_ID_LEVEL_GROUPMANAGER => _PLOOPI_LEVEL_GROUPMANAGER,
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

/*
    if (!$ploopi_errornum && ($_SESSION['ploopi']['moduleid']!= '' && !$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['public']))
    {
        $ploopi_errornum = 4;
    }
*/

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
    echo "<html><body><div style=\"text-align:center;\"><br /><br /><h1>Erreur de sécurité</h1>reconnectez vous ou fermez votre navigateur ou contactez l'administrateur système<br /><br /><b>erreur : $ploopi_errornum</b><br /><br /><a href=\"$scriptenv\">continuer</a></div></body></html>";
    ploopi_die();
}

$_SESSION['ploopi']['uri'] = (empty($_SERVER['QUERY_STRING'])) ? '' : "{$scriptenv}?{$_SERVER['QUERY_STRING']}";

?>
