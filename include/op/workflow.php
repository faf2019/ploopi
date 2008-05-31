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
 * Opérations sur le workflow
 *
 * @package ploopi
 * @subpackage workflow
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

switch($ploopi_op)
{
    case 'workflow_select_user':
        if (isset($_GET['user_id'])) $_SESSION['ploopi']['workflow']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
        if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['workflow']['users_selected'][$_GET['remove_user_id']]);


        foreach($_SESSION['ploopi']['workflow']['users_selected'] as $user_id)
        {
            include_once './include/classes/user.php';

            $user = new user();
            if ($user->open($user_id))
            {
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="ploopi_workflow_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=workflow_select_user&remove_user_id=<? echo $user->fields['id']; ?>','','div_workflow_users_selected');">
                        <img src="./img/icon_delete.gif">
                        <span><? echo "{$user->fields['lastname']} {$user->fields['firstname']} (Cliquez pour supprimer)"; ?></span>
                    </a>
                </p>
                <?
            }
        }
        ploopi_die();
    break;

    case 'workflow_search_users':
        $listgroup = array();

        include_once './include/classes/group.php';
        include_once './include/classes/workspace.php';

        $group = new group();
        $workspace = new workspace();

        $list = array();
        $list['workspaces'] = array();
        $list['groups'] = array();
        $list['users'] = array();

        $id_action = (!empty($_GET['id_action']) && is_numeric($_GET['id_action'])) ? $_GET['id_action'] : -1;

        // construction de la liste des groupes de travail et des groupes d'utilisateurs rattachés (pour l'utilisateur courant)
        foreach (split(',',ploopi_viewworkspaces_inv()) as $grpid) // pour chaque groupe de travail
        {
            if (isset($_SESSION['ploopi']['workspaces'][$grpid]))
            {
                $grp = $_SESSION['ploopi']['workspaces'][$grpid];

                if (isset($grp['adminlevel']) && $grp['backoffice'])
                {
                    $list['workspaces'][$grp['id']]['label'] = $grp['label'];
                    $list['workspaces'][$grp['id']]['groups'] = array();
                    $list['workspaces'][$grp['id']]['users'] = array();
                    $workspace->fields['id'] = $grp['id'];
                    foreach ($workspace->getgroups() as $orgrp)
                    {
                        $list['workspaces'][$grp['id']]['groups'][] = $orgrp['id'];
                        $list['groups'][$orgrp['id']]['label'] = $orgrp['label'];
                        $list['groups'][$orgrp['id']]['users'] = array();
                    }
                }
            }
        }

        $cleanedfilter = $db->addslashes($_GET['ploopi_workflow_userfilter']);
        $userfilter = "(u.login LIKE '%{$cleanedfilter}%' OR u.firstname LIKE '%{$cleanedfilter}%' OR u.lastname LIKE '%{$cleanedfilter}%')";

        // recherche des utilisateurs "admininstrateur d'espace" ou disposant d'une action particuliere dans le module
        $option_u = ($id_action != -1) ?
                    "
                    LEFT JOIN   ploopi_workspace_user_role wur
                    ON          wur.id_user = u.id
                    AND         wur.id_workspace = wu.id_workspace

                    LEFT JOIN   ploopi_role_action rau
                    ON          rau.id_role = wur.id_role

                    WHERE       wu.adminlevel = "._PLOOPI_ID_LEVEL_SYSTEMADMIN." OR (rau.id_action = {$id_action} AND rau.id_module_type = {$_SESSION['ploopi']['moduletypeid']})
                    AND         {$userfilter}
                    " : "WHERE {$userfilter}";

        $query_u =  "
                    SELECT      distinct(u.id), u.login, u.firstname, u.lastname, wu.id_workspace
                    FROM        ploopi_user u

                    INNER JOIN  ploopi_workspace_user wu
                    ON          wu.id_user = u.id
                    AND         wu.id_workspace IN (".implode(',',array_keys($list['workspaces'])).")

                    INNER JOIN  ploopi_module_workspace mw
                    ON          mw.id_workspace = wu.id_workspace
                    AND         mw.id_module = {$_SESSION['ploopi']['moduleid']}
                    {$option_u}
                    
                    ORDER BY u.lastname, u.firstname
                    ";

        // recherche des utilisateurs de groupes "admininstrateur d'espace" ou disposant d'une action particuliere dans le module
        $option_g = ($id_action != -1) ?
                    "
                    LEFT JOIN   ploopi_workspace_group_role wgr
                    ON          wgr.id_group = gu.id_group
                    AND         wgr.id_workspace = wg.id_workspace

                    LEFT JOIN   ploopi_role_action rag
                    ON          rag.id_role = wgr.id_role

                    WHERE       wg.adminlevel = "._PLOOPI_ID_LEVEL_SYSTEMADMIN." OR (rag.id_action = {$id_action} AND rag.id_module_type = {$_SESSION['ploopi']['moduletypeid']})
                    AND         {$userfilter}
                    " : "WHERE {$userfilter}";

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
                    {$option_g}
                    
                    ORDER BY u.lastname, u.firstname
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
            <div class="ploopi_workflow_select_empty">
                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png"><span>aucun validateur trouv&eacute;</span></p>
            </div>
            <?
        }
        else
        {
            ?>
            <div style="height:200px;overflow:auto;border-bottom:1px solid #c0c0c0;">
                <div style="overflow:hidden">
                <?
                // pour chaque espace de travail
                foreach($list['workspaces'] as $id_workspace => $workspace)
                {
                    if (!(empty($workspace['users']) && empty($workspace['groups'])))
                    {
                        ?>
                        <div class="ploopi_workflow_select_workgroup">
                            <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><? echo $workspace['label']; ?></span></p>
                        </div>
                        <?
                        if (!empty($workspace['users']))
                        {
                            foreach($workspace['users'] as $id_user)
                            {
                                $user = &$list['users'][$id_user];
                                ?>
                                <a class="ploopi_workflow_select_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=workflow_select_user&user_id=<? echo $id_user; ?>','','div_workflow_users_selected');">
                                    <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                                </a>
                                <?
                            }
                        }

                        if (!empty($workspace['groups']))
                        {
                            foreach($workspace['groups'] as $id_grp)
                            {
                                $group = &$list['groups'][$id_grp];
                                ?>
                                <div class="ploopi_workflow_select_usergroup">
                                    <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><? echo $list['groups'][$id_grp]['label'];  ?></span></p>
                                </div>
                                <?
                                foreach($list['groups'][$id_grp]['users'] as $id_user)
                                {
                                    $user = &$list['users'][$id_user];
                                    ?>
                                    <a class="ploopi_workflow_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=workflow_select_user&user_id=<? echo $id_user; ?>','','div_workflow_users_selected');">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                                    </a>
                                    <?
                                }
                            }
                        }
                    }
                }
                ?>
                </div>
            </div>
            <div class="ploopi_workflow_select_legend">
                <p class="ploopi_va">
                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span>Espace de Travail</span>
                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
                </p>
            </div>
            <?
        }

        ploopi_die();
    break;
}
?>