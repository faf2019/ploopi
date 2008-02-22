<?php
/*
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

ob_start();
session_start();
unset($_SESSION);

///////////////////////////////////////////////////////////////////////////
// START TIMER
///////////////////////////////////////////////////////////////////////////

include_once './include/classes/class_timer.php' ;
$ploopi_timer = new timer();
$ploopi_timer->start();

///////////////////////////////////////////////////////////////////////////
// LOAD PLOOPI CONFIG
///////////////////////////////////////////////////////////////////////////

if (!file_exists('./config/config.php'))
{
    include_once './config/install.php';
    ploopi_die();
}
include_once './config/config.php'; // load config (mysql, path, etc.)

///////////////////////////////////////////////////////////////////////////
// INITIALIZE ERROR HANDLER
///////////////////////////////////////////////////////////////////////////

include_once './include/errors.php';

///////////////////////////////////////////////////////////////////////////
// REGISTER GLOBALS..... :(     TO DELETE !!!!!!!!!
///////////////////////////////////////////////////////////////////////////
include_once './include/import_gpr.php';

// set default header
include_once './include/header.php';

///////////////////////////////////////////////////////////////////////////
// LOAD GLOBALS, VARS & FUNCTIONS
///////////////////////////////////////////////////////////////////////////
include_once './include/global_constants.php';
include_once './include/functions/system.php';
include_once './include/functions/session.php';

///////////////////////////////////////////////////////////////////////////
// INCLUDES MAIN CLASSES
///////////////////////////////////////////////////////////////////////////

include_once './include/classes/class_data_object.php';

if (file_exists('./db/class_db_'._PLOOPI_SQL_LAYER.'.php')) include_once './db/class_db_'._PLOOPI_SQL_LAYER.'.php';

///////////////////////////////////////////////////////////////////////////
// GLOBALS
///////////////////////////////////////////////////////////////////////////

global $ploopi_initsession;
global $db;

///////////////////////////////////////////////////////////////////////////
// INIT VARIABLES
///////////////////////////////////////////////////////////////////////////

$db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
if(!$db->connection_id) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);

$ploopi_initsession = false;

if (empty($_SESSION) || ($_SESSION['ploopi']['host'] != $_SERVER['HTTP_HOST']))  {ploopi_session_reset();$ploopi_initsession = true;}
ploopi_session_update();

if ($ploopi_initsession)
{
    ///////////////////////////////////////////////////////////////////////////
    // GET WORKSPACES (FOR THIS DOMAIN)
    // on en profite pour appliquer l'héritage implicite des domaines pour les sous-espaces de travail
    ///////////////////////////////////////////////////////////////////////////

    $select = "SELECT * FROM ploopi_workspace where system = 0 order by depth";
    $db->query($select);

    $workspaces = array();

    while ($fields = $db->fetchrow())
    {
        $web_domain_array = split("\r\n",$fields['web_domainlist']);

        $workspaces[$fields['id']] = $fields;
        $workspaces[$fields['id']]['parents_array'] = split(';',$workspaces[$fields['id']]['parents']);
        $workspaces[$fields['id']]['web_domain_array'] = $web_domain_array;

        if (trim($workspaces[$fields['id']]['web_domainlist']) == '')
        {
            $p_array = $workspaces[$fields['id']]['parents_array'];
            for ($i=sizeof($p_array)-1;$i>=0;$i--)
            {
                if (isset($workspaces[$p_array[$i]]) && trim($workspaces[$p_array[$i]]['web_domainlist']) != '')
                {
                    $workspaces[$fields['id']]['web_domainlist'] = $workspaces[$p_array[$i]]['web_domainlist'];
                    $workspaces[$fields['id']]['web_domain_array'] = $workspaces[$p_array[$i]]['web_domain_array'];
                    break;
                }
            }
        }
    }

    $_SESSION['ploopi']['allworkspaces'] = implode(',', array_keys($workspaces));

    $host_array = array($_SESSION['ploopi']['host'], '*');
    $_SESSION['ploopi']['hosts'] = array('web' => array(), 'admin' => array());



    // on garde les id de groupes autorisés en fonction du domaine courant
    foreach($workspaces as $gid => $grp)
    {
        foreach($grp['web_domain_array'] as $domain)
        {
            if ($workspaces[$gid]['web'] && sizeof(array_intersect($workspaces[$gid]['web_domain_array'], $host_array)) && !in_array($gid, $_SESSION['ploopi']['hosts']['web'])) $_SESSION['ploopi']['hosts']['web'][] = $gid;
            //if ($workspaces[$gid]['web'] && !in_array($gid, $_SESSION['ploopi']['hosts']['web'])) $_SESSION['ploopi']['hosts']['web'][] = $gid;
        }
    }

    $_SESSION['ploopi']['workspaces'] = $workspaces;
}

if ($_SESSION['ploopi']['mode'] != 'web')
{
    $_SESSION['ploopi']['workspaceid'] = $_SESSION['ploopi']['hosts']['web'][0];

    include_once './modules/system/class_workspace.php';
    foreach($_SESSION['ploopi']['hosts']['web'] as $wid)
    {
        $workspace = new workspace();
        $workspace->open($wid);

        $_SESSION['ploopi']['workspaces'][$wid] = array_merge($_SESSION['ploopi']['workspaces'][$wid], $workspace->fields);
        //$_SESSION['ploopi']['workspaces'][$gid] = $group->fields;
        $_SESSION['ploopi']['workspaces'][$wid]['children']  = $workspace->getworkspacechildrenlite('web');

        if ($_SESSION['ploopi']['workspaces'][$wid]['parents'] != '')
        {
            $select = "SELECT * from ploopi_workspace WHERE id in (".str_replace(';',',',$_SESSION['ploopi']['workspaces'][$wid]['parents']).") AND web = 1";
            $db->query($select);

            $_SESSION['ploopi']['workspaces'][$wid]['parents'] = array();
            while ($row = $db->fetchrow())
            {
                $dom_array = split("\r\n", $row['web_domainlist']);
                foreach($dom_array as $dom)
                {
                    if ($_SERVER['HTTP_HOST'] == $dom) $_SESSION['ploopi']['workspaces'][$wid]['parents'][] = $row['id'];
                }
            }
        }

        $_SESSION['ploopi']['workspaces'][$wid]['brothers']  = $workspace->getworkspacebrotherslite('web',$_SERVER['HTTP_HOST']);
        $_SESSION['ploopi']['workspaces'][$wid]['list_parents'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['parents']);
        $_SESSION['ploopi']['workspaces'][$wid]['list_children'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['children']);
        $_SESSION['ploopi']['workspaces'][$wid]['list_brothers'] = implode(',',$_SESSION['ploopi']['workspaces'][$wid]['brothers']);
        $_SESSION['ploopi']['workspaces'][$wid]['modules'] = $workspace->getmodules(true);
    }

    if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['webeditmoduleid']))
    {
        $_SESSION['ploopi']['webeditmoduleid'] = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['webeditmoduleid'];
        // TEST A VALIDER
        $_SESSION['ploopi']['moduleid'] = $_SESSION['ploopi']['webeditmoduleid'];
    }

    $_SESSION['ploopi']['mode'] = 'web';


    include './include/load_param.php';
    ploopi_loadparams();
}
?>