<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2009 Ovensia
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
 * Op�rations sur les partages
 *
 * @package ploopi
 * @subpackage share
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

switch($ploopi_op)
{
    case 'share_select_user':
        if (empty($_GET['share_id'])) ploopi_die();

        if (!isset($_SESSION['ploopi']['share'][$_GET['share_id']])) $_SESSION['ploopi']['share'][$_GET['share_id']] = array('users_selected' => array(), 'groups_selected' => array());

        if (isset($_GET['user_id'])) $_SESSION['ploopi']['share'][$_GET['share_id']]['users_selected'][$_GET['user_id']] = $_GET['user_id'];
        if (isset($_GET['group_id'])) $_SESSION['ploopi']['share'][$_GET['share_id']]['groups_selected'][$_GET['group_id']] = $_GET['group_id'];

        if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['share'][$_GET['share_id']]['users_selected'][$_GET['remove_user_id']]); 
        if (isset($_GET['remove_group_id'])) unset($_SESSION['ploopi']['share'][$_GET['share_id']]['groups_selected'][$_GET['remove_group_id']]);

        foreach($_SESSION['ploopi']['share'][$_GET['share_id']]['groups_selected'] as $group_id)
        {
            include_once './include/classes/group.php';

            $group = new group();
            if ($group->open($group_id))
            {
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="ploopi_share_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=share_select_user&share_id=<? echo $_GET['share_id']; ?>&remove_group_id=<?php echo $group->fields['id']; ?>', 'div_share_users_selected_<? echo $_GET['share_id']; ?>');">
                        <img src="./img/icon_delete.gif">
                        <span>Groupe &laquo; </span><strong><?php echo $group->fields['label']; ?></strong><span></span> &raquo; (Cliquez pour supprimer)</span>
                    </a>
                </p>
                <?php
            }
        }

        foreach($_SESSION['ploopi']['share'][$_GET['share_id']]['users_selected'] as $user_id)
        {
            include_once './include/classes/user.php';

            $user = new user();
            if ($user->open($user_id))
            {
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="ploopi_share_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=share_select_user&share_id=<? echo $_GET['share_id']; ?>&remove_user_id=<?php echo $user->fields['id']; ?>', 'div_share_users_selected_<? echo $_GET['share_id']; ?>');">
                        <img src="./img/icon_delete.gif">
                        <strong><?php echo "{$user->fields['lastname']} {$user->fields['firstname']}"; ?></strong><span>&nbsp;(Cliquez pour supprimer)</span>
                    </a>
                </p>
                <?php
            }
        }

        ploopi_die();
    break;

    case 'share_search_users':
        if (empty($_GET['share_id'])) ploopi_die();
        
        include_once './include/classes/group.php';
        include_once './include/classes/workspace.php';

        $group = new group();
        $workspace = new workspace();

        $list = array();
        $list['workspaces'] = array();
        $list['groups'] = array();
        $list['users'] = array();

        if (isset($id_action) && !is_numeric($id_action)) $id_action = -1;

        // Recherche des espaces de travail qui supportent ce module, selon la vue inverse
        $rs = $db->query("
            SELECT  w.*
            FROM    ploopi_workspace w,
                    ploopi_module_workspace mw
            WHERE   w.id = mw.id_workspace
            AND     w.id IN (".ploopi_viewworkspaces_inv().")
            AND     w.backoffice = 1
            AND     mw.id_module = {$_SESSION['ploopi']['moduleid']}
            ORDER BY w.depth, w.label
        ");

        while ($row = $db->fetchrow($rs))
        {
            $list['workspaces'][$row['id']]['label'] = $row['label'];
            $list['workspaces'][$row['id']]['groups'] = array();
            $list['workspaces'][$row['id']]['users'] = array();
            $workspace->fields['id'] = $row['id'];
            foreach ($workspace->getgroups() as $grp)
            {
                $list['workspaces'][$row['id']]['groups'][] = $grp['id'];
                $list['groups'][$grp['id']]['label'] = $grp['label'];
            }
        }

        $cleanedfilter = $db->addslashes($_GET['ploopi_share_userfilter']);
        $userfilter = "(u.login LIKE '%{$cleanedfilter}%' OR u.firstname LIKE '%{$cleanedfilter}%' OR u.lastname LIKE '%{$cleanedfilter}%')";

        // recherche des utilisateurs "admininstrateur d'espace" ou disposant d'une action particuliere dans le module
        $query_u =  "
                    SELECT      distinct(u.id), u.login, u.firstname, u.lastname, wu.id_workspace
                    FROM        ploopi_user u

                    INNER JOIN  ploopi_workspace_user wu
                    ON          wu.id_user = u.id
                    AND         wu.id_workspace IN (".implode(',',array_keys($list['workspaces'])).")

                    INNER JOIN  ploopi_module_workspace mw
                    ON          mw.id_workspace = wu.id_workspace
                    AND         mw.id_module = {$_SESSION['ploopi']['moduleid']}
                    WHERE       {$userfilter}

                    ORDER BY    u.lastname, u.firstname
                    ";

        // recherche des utilisateurs de groupes "admininstrateur d'espace" ou disposant d'une action particuliere dans le module
        $query_g =  "
                    SELECT      distinct(u.id), u.login, u.firstname, u.lastname, wg.id_group, wg.id_workspace
                    FROM        ploopi_user u

                    INNER JOIN  ploopi_group_user gu
                    ON          gu.id_user = u.id

                    INNER JOIN  ploopi_workspace_group wg
                    ON          wg.id_group = gu.id_group
                    AND         wg.id_workspace IN (".implode(',',array_keys($list['workspaces'])).")

                    INNER JOIN  ploopi_module_workspace mw
                    ON          mw.id_workspace = wg.id_workspace
                    AND         mw.id_module = {$_SESSION['ploopi']['moduleid']}
                    WHERE       {$userfilter}

                    ORDER BY    u.lastname, u.firstname
                    ";

        $db->query($query_u);
        while ($fields = $db->fetchrow())
        {
            $list['users'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
            $list['workspaces'][$fields['id_workspace']]['users'][$fields['id']] = $fields['id'];
        }

        $db->query($query_g);
        while ($fields = $db->fetchrow())
        {
            $list['users'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
            $list['groups'][$fields['id_group']]['users'][$fields['id']] = $fields['id'];
        }

        if (!sizeof($list['users']))
        {
            ?>
            <div class="ploopi_share_select_empty">
                <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png"><span>aucun utilisateur trouv&eacute;</span></p>
            </div>
            <?php
        }
        else
        {
            ?>
            <div style="height:200px;overflow:auto;border-bottom:1px solid #c0c0c0;">
                <div style="overflow:hidden">
                <?php
                // pour chaque espace de travail
                foreach($list['workspaces'] as $id_workspace => $workspace)
                {
                    if (!(empty($workspace['users']) && empty($workspace['groups'])))
                    {
                        ?>
                        <div class="ploopi_share_select_workgroup">
                            <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><?php echo $workspace['label']; ?></span></p>
                        </div>
                        <?php
                        if (!empty($workspace['users']))
                        {
                            foreach($workspace['users'] as $id_user)
                            {
                                $user = &$list['users'][$id_user];
                                ?>
                                <a class="ploopi_share_select_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=share_select_user&share_id=<?php echo $_GET['share_id']; ?>&user_id=<?php echo $id_user; ?>', 'div_share_users_selected_<?php echo $_GET['share_id']; ?>');">
                                    <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                                </a>
                                <?php
                            }
                        }

                        if (!empty($workspace['groups']))
                        {
                            foreach($workspace['groups'] as $id_grp)
                            {
                                $group = &$list['groups'][$id_grp];
                                ?>
                                <a class="ploopi_share_select_usergroup" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=share_select_user&share_id=<?php echo $_GET['share_id']; ?>&group_id=<?php echo $id_grp; ?>', 'div_share_users_selected_<?php echo $_GET['share_id']; ?>');">
                                    <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><?php echo $list['groups'][$id_grp]['label'];  ?></span></p>
                                </a>
                                <?php
                                if (!empty($list['groups'][$id_grp]['users']))
                                {
                                    foreach($list['groups'][$id_grp]['users'] as $id_user)
                                    {
                                        $user = &$list['users'][$id_user];
                                        ?>
                                        <a class="ploopi_share_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=share_select_user&share_id=<?php echo $_GET['share_id']; ?>&user_id=<?php echo $id_user; ?>', 'div_share_users_selected_<?php echo $_GET['share_id']; ?>');">
                                            <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                                        </a>
                                        <?php
                                    }
                                }
                            }
                        }
                    }
                }
                ?>
                </div>
            </div>
            <div class="ploopi_share_select_legend">
                <p class="ploopi_va">
                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span>Espace de Travail</span>
                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
                </p>
            </div>
            <?php
        }

        ploopi_die();
    break;
}
