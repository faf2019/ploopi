<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Gestion de l'interface générale et des accès aux différentes interfaces d'administration
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Si l'utilisateur n'est pas gestionnaire, il n'a rien à faire ici => []
 */
if (!ploopi\acl::ismanager()) ploopi\output::redirect('admin.php?ploopi_logout');
else
{
    /**
     * Initialisation du module
     */

    ploopi\module::init('system');

    if (!empty($_REQUEST['system_level'])) $_SESSION['system']['level'] = $_REQUEST['system_level'];
    if (empty($_SESSION['system']['level'])) $_SESSION['system']['level'] = _SYSTEM_WORKSPACES;

    $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

    switch($_SESSION['system']['level'])
    {
        /**
         * Historiquement les 2 cas étaient séparés (groupes d'utilisateurs / espaces de travail)
         * Ils ne le sont plus.
         */

        case _SYSTEM_GROUPS:
        case _SYSTEM_WORKSPACES:

            global $workspaces;
            global $groups;
            global $workspaceid;
            global $groupid;

            list($workspaces, $groups) = system_getwg();

            // init session
            if (empty($_SESSION['system']['workspaceid'])) $_SESSION['system']['workspaceid'] = 0;
            if (empty($_SESSION['system']['groupid'])) $_SESSION['system']['groupid'] = 0;

            // lecture params
            if (!empty($_REQUEST['groupid']))
            {
                $workspaceid = 0;
                $groupid = $_REQUEST['groupid'];
            }

            if (!empty($_REQUEST['workspaceid']))
            {
                $groupid = 0;
                $workspaceid = $_REQUEST['workspaceid'];
            }

            if (empty($groupid) && empty($workspaceid))
            {
                // lecture session
                $groupid = $_SESSION['system']['groupid'];
                $workspaceid = $_SESSION['system']['workspaceid'];
            }

            // toujours rien de sélectionné => recherche workspaceid par défaut
            if (empty($groupid) && empty($workspaceid))
            {
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN) $workspaceid = $_SESSION['ploopi']['workspaceid'];
                else $workspaceid = $workspaces['tree'][1][0];
            }

            // test si workspaceid valide
            if (!empty($workspaceid) && ($workspaceid == 1 || !isset($workspaces['list'][$workspaceid]))) // workspace non autorisé !!!
            {
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN) $workspaceid = $_SESSION['ploopi']['workspaceid'];
                else $workspaceid = $workspaces['tree'][1][0];
            }

            // test si groupid valide
            if (!empty($groupid) && ($groupid == 1 || !isset($groups['list'][$groupid]))) // groupe non autorisé !!!
            {
                $groupid = 0;

                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN) $workspaceid = $_SESSION['ploopi']['workspaceid'];
                else $workspaceid = $workspaces['tree'][1][0];
            }

            // sauvegarde session_cache_expire
            $_SESSION['system']['workspaceid'] = $workspaceid;
            $_SESSION['system']['groupid'] = $groupid;

            if (!empty($workspaceid)) $_SESSION['system']['level'] = _SYSTEM_WORKSPACES;
            if (!empty($groupid)) $_SESSION['system']['level'] = _SYSTEM_GROUPS;

            if ($op == 'xml_detail_group')
            {
                ob_end_clean();
                if (!empty($_GET['typetree']) && !empty($_GET['gid']))
                {
                    switch($_GET['typetree'])
                    {
                        case 'workspaces':
                            echo system_build_tree('workspaces', $_GET['gid'], 0);
                        break;

                        case 'groups':
                            echo system_build_tree('groups', 0, $_GET['gid']);
                        break;
                    }
                }
                ploopi\system::kill();
            }

            echo ploopi\skin::get()->create_pagetitle(_SYSTEM_PAGE_TITLE);
            echo ploopi\skin::get()->open_simplebloc(_PLOOPI_ADMIN_WORKSPACES);
            ?>
            <div style="overflow:auto;">
                <div class="system_tree">
                    <div class="system_tree_padding">
                        <?php

                        if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN) echo system_build_tree('workspaces');
                        else echo system_build_tree('workspaces', $workspaces['list'][$_SESSION['ploopi']['workspaceid']]['id_workspace']);

                        //if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN) echo system_build_tree('groups');
                        ?>
                    </div>
                </div>
                <div class="system_main">
                    <?php include_once './modules/system/admin_index.php'; ?>
                </div>
            </div>
            <?php
            echo ploopi\skin::get()->close_simplebloc();
        break;

        /**
         * Point d'entrée vers l'interface d'administration "système"
         */
        case 'system':
            if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
            {
                echo ploopi\skin::get()->create_pagetitle(_SYSTEM_PAGE_TITLE);
                echo ploopi\skin::get()->open_simplebloc(_PLOOPI_ADMIN_SYSTEM);
                ?>
                <div class="system_main">
                <?php include_once './modules/system/admin_system.php'; ?>
                </div>
                <?php
                echo ploopi\skin::get()->close_simplebloc();
            }
            else ploopi\output::redirect("admin.php?system_level="._SYSTEM_WORKSPACES);
        break;
    }

}
?>
