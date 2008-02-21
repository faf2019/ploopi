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

include_once './include/functions/rights.php';

if (isset($_REQUEST['ploopi_op'])) $ploopi_op = $_REQUEST['ploopi_op'];

if (isset($ploopi_op))
{
    switch($ploopi_op)
    {
        case 'ploopi_switchdisplay':
            if (!empty($_GET['id'])) $_SESSION['ploopi']['switchdisplay'][$_GET['id']] = $_GET['display'];
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            ploopi_die();
        break;

        case 'ploopi_checkpasswordvalidity':
            if (!$_SESSION['ploopi']['connected'] || !isset($_GET['password'])) ploopi_die();
            if (_PLOOPI_USE_COMPLEXE_PASSWORD) echo ploopi_checkpasswordvalidity($_GET['password']);
            else echo true;
            ploopi_die();
        break;

        case 'ploopi_skin_array_refresh':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            $skin->display_array_refresh($_GET['array_id'], $_GET['array_orderby']);
            ploopi_die();
        break;

        case 'workflow_select_user':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            if (isset($_GET['user_id'])) $_SESSION['ploopi']['workflow']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
            if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['workflow']['users_selected'][$_GET['remove_user_id']]);


            foreach($_SESSION['ploopi']['workflow']['users_selected'] as $user_id)
            {
                include_once('./modules/system/class_user.php');

                $user = new user();
                $user->open($user_id);
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="ploopi_workflow_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=workflow_select_user&remove_user_id=<? echo $user->fields['id']; ?>','','div_workflow_users_selected');">
                        <img src="./img/icon_delete.gif">
                        <span><? echo "{$user->fields['firstname']} {$user->fields['lastname']} ({$user->fields['login']})"; ?></span>
                    </a>
                </p>
                <?
            }
            ploopi_die();
        break;

        case 'workflow_search_users':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $listgroup = array();
            include_once './modules/system/class_group.php';
            include_once './modules/system/class_workspace.php';
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

                    if (isset($grp['adminlevel']) && $grp['admin'])
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

            $userfilter = "u.login LIKE '%".$db->addslashes($_GET['ploopi_workflow_userfilter'])."%'";

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
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
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
                                            <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
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

        case 'shares_select_user':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (isset($_GET['user_id'])) $_SESSION['ploopi']['shares']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
            if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['shares']['users_selected'][$_GET['remove_user_id']]);


            foreach($_SESSION['ploopi']['shares']['users_selected'] as $user_id)
            {
                include_once('./modules/system/class_user.php');

                $user = new user();
                $user->open($user_id);

                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="ploopi_shares_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=shares_select_user&remove_user_id=<? echo $user->fields['id']; ?>','','div_shares_users_selected');">
                        <img src="./img/icon_delete.gif">
                        <span><? echo "{$user->fields['firstname']} {$user->fields['lastname']} ({$user->fields['login']})"; ?></span>
                    </a>
                </p>
                <?
            }
            ploopi_die();
        break;

        case 'shares_search_users':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            //$listgroup = array();
            include_once './modules/system/class_group.php';
            include_once './modules/system/class_workspace.php';
            $group = new group();
            $workspace = new workspace();

            $list = array();
            $list['workspaces'] = array();
            $list['groups'] = array();
            $list['users'] = array();

            if (isset($id_action) && !is_numeric($id_action)) $id_action = -1;

            // construction de la liste des groupes de travail et des groupes d'utilisateurs rattachés (pour l'utilisateur courant)
            foreach (split(',',ploopi_viewworkspaces_inv()) as $grpid) // pour chaque groupe de travail
            {
                if (isset($_SESSION['ploopi']['workspaces'][$grpid]))
                {
                    $grp = $_SESSION['ploopi']['workspaces'][$grpid];

                    if (isset($grp['adminlevel']) && $grp['admin'])
                    {
                        $list['workspaces'][$grp['id']]['label'] = $grp['label'];
                        $list['workspaces'][$grp['id']]['groups'] = array();
                        $list['workspaces'][$grp['id']]['users'] = array();
                        $workspace->fields['id'] = $grp['id'];
                        foreach ($workspace->getgroups() as $orgrp)
                        {
                            $list['workspaces'][$grp['id']]['groups'][] = $orgrp['id'];
                            $list['groups'][$orgrp['id']]['label'] = $orgrp['label'];
                        }
                    }
                }
            }

            $userfilter = "u.login LIKE '%".$db->addslashes($_GET['ploopi_shares_userfilter'])."%'";

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
                <div class="ploopi_shares_select_empty">
                    <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png"><span>aucun utilisateur trouv&eacute;</span></p>
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
                            <div class="ploopi_shares_select_workgroup">
                                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><? echo $workspace['label']; ?></span></p>
                            </div>
                            <?
                            if (!empty($workspace['users']))
                            {
                                foreach($workspace['users'] as $id_user)
                                {
                                    $user = &$list['users'][$id_user];
                                    ?>
                                    <a class="ploopi_shares_select_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=shares_select_user&user_id=<? echo $id_user; ?>','','div_shares_users_selected');">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
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
                                    <div class="ploopi_shares_select_usergroup">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><? echo $list['groups'][$id_grp]['label'];  ?></span></p>
                                    </div>
                                    <?
                                    foreach($list['groups'][$id_grp]['users'] as $id_user)
                                    {
                                        $user = &$list['users'][$id_user];
                                        ?>
                                        <a class="ploopi_shares_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=shares_select_user&user_id=<? echo $id_user; ?>','','div_shares_users_selected');">
                                            <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
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
                <div class="ploopi_shares_select_legend">
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

        case 'annotation':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (empty($_GET['id_annotation'])) ploopi_die();

            $id_object = $_SESSION['annotations'][$_GET['id_annotation']]['id_object'];
            $id_record = $_SESSION['annotations'][$_GET['id_annotation']]['id_record'];

            $select =   "
                        SELECT      count(*) as c
                        FROM        ploopi_annotation a
                        WHERE       a.id_record = '".$db->addslashes($id_record)."'
                        AND         a.id_object = {$id_object}
                        AND         a.id_module = {$_SESSION['ploopi']['moduleid']}
                        AND         (a.private = 0
                        OR          (a.private = 1 AND a.id_user = {$_SESSION['ploopi']['userid']}))
                        ";
            $rs_anno = $db->query($select);

            if ($fields = $db->fetchrow($rs_anno)) $nbanno = $fields['c'];
            else $nbanno = 0;

            $annotation_show = (isset($_SESSION['ploopi']['annotations']['show'][$_GET['id_annotation']]));

            ?>
            <a name="annotation_<? echo $_GET['id_annotation']; ?>" style="display:none;"></a>
            <div style="overflow:hidden;">
                <a id="annotations_count_<? echo $_GET['id_annotation']; ?>" class="ploopi_annotation_viewlist" href="#annotation_<? echo $_GET['id_annotation']; ?>" onclick="javascript:ploopi_getelem('annotations_list_<? echo $_GET['id_annotation']; ?>').style.display=(ploopi_getelem('annotations_list_<? echo $_GET['id_annotation']; ?>').style.display=='block') ? 'none' : 'block'; ploopi_xmlhttprequest('index-light.php','ploopi_op=annotation_show&object_id=<? echo $_GET['id_annotation']; ?>');return false;"><img border="0" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/annotation.png"><span><? echo $nbanno; ?> annotation<? echo ($nbanno>1) ? 's' : ''; ?></span></a>

                <div style="display:<? echo ($annotation_show) ? 'block' : 'none'; ?>;" id="annotations_list_<? echo $_GET['id_annotation']; ?>">

                <?

                $select =   "
                            SELECT      a.*,
                                        u.firstname,
                                        u.lastname,
                                        u.login,
                                        t.id as idtag,
                                        t.tag
                            FROM        ploopi_annotation a

                            INNER JOIN  ploopi_user u ON a.id_user = u.id

                            LEFT JOIN   ploopi_annotation_tag at ON a.id = at.id_annotation
                            LEFT JOIN   ploopi_tag t ON t.id = at.id_tag

                            WHERE       a.id_record = '".$db->addslashes($id_record)."'
                            AND         a.id_object = {$id_object}
                            AND         a.id_module = {$_SESSION['ploopi']['moduleid']}
                            AND         (a.private = 0
                            OR          (a.private = 1 AND a.id_user = {$_SESSION['ploopi']['userid']}))
                            ORDER BY    a.date_annotation DESC
                            ";

                $rs_anno = $db->query($select);

                $array_anno = array();
                while ($fields = $db->fetchrow($rs_anno))
                {
                    $array_anno[$fields['id']]['fields'] = $fields;
                    if (!is_null($fields['tag'])) $array_anno[$fields['id']]['tags'][$fields['idtag']] = $fields['tag'];
                }

                foreach($array_anno as $anno)
                {

                    $fields = $anno['fields'];

                    $ldate = ploopi_timestamp2local($fields['date_annotation']);
                    $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;

                    $private = '';
                    if ($fields['private']) $private = 'Privé';

                    ?>
                    <div class="ploopi_annotations_row_<? echo $numrow; ?>">
                        <div>
                            <div style="float:right;padding:2px 4px;">par <strong><? echo "{$fields['firstname']} {$fields['lastname']}"; ?></strong> le <? echo $ldate['date']; ?> à <? echo $ldate['time']; ?> <? echo $private; ?></div>
                            <div style="padding:2px 4px;"><strong><? echo htmlentities($fields['title']); ?></strong></div>
                        </div>
                        <div style="clear:both;padding:2px 4px;"><? echo ploopi_make_links(ploopi_nl2br(htmlentities($fields['content']))); ?></div>
                        <div style="clear:both;">
                            <?
                            if ($fields['id_user'] == $_SESSION['ploopi']['userid'])
                            {
                                ?>
                                <div style="float:right;padding:2px 4px;">
                                    <a href="javascript:ploopi_annotation_delete('<? echo $_GET['id_annotation']; ?>', '<? echo $fields['id']; ?>');">supprimer</a>
                                </div>
                                <?
                            }
                            ?>
                            <div style="padding:2px 4px;">
                            <?
                            if (isset($anno['tags']) && is_array($anno['tags']))
                            {
                                echo "<b>tags :</b>";
                                foreach($anno['tags'] as $idtag => $tag)
                                {
                                    ?>
                                    <a href="javascript:void(0);" onclick="javascript:ploopi_showpopup('','400',event,'click');ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=annotation_taghistory&id_tag=<? echo $idtag; ?>','','ploopi_popup');return false;"><? echo htmlentities($tag); ?></a>
                                    <?
                                }
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                    <?
                }

                if ($_SESSION['ploopi']['connected'])
                {
                    $id_module_type = (isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'] : 0;

                    $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;
                    ?>
                    <div class="ploopi_annotations_row_<? echo $numrow; ?>">
                        <form action="" method="post" id="form_annotation_<? echo $_GET['id_annotation']; ?>" target="form_annotation_target_<? echo $_GET['id_annotation']; ?>" onsubmit="return ploopi_annotation_validate(this);">
                        <input type="hidden" name="ploopi_op" value="annotation_save">
                        <input type="hidden" name="id_annotation" value="<? echo $_GET['id_annotation']; ?>">

                        <div class="ploopi_annotations_titleform">Ajout d'une Annotation <? echo (isset($ploopi_annotation_private)) ? 'privée' : ''; ?></div>
                        <div style="padding:2px 4px;"><input type="checkbox" name="ploopi_annotation_private" value="1">Privée (visible par vous uniquement)</div>
                        <div style="padding:2px 4px;">Titre:</div>
                        <div style="padding:2px 4px;"><input type="text" class="text" style="width:99%;" name="ploopi_annotation_title"></div>
                        <div style="padding:2px 4px;">Tags:</div>
                        <div style="padding:2px 4px;"><input type="text" class="text" style="width:99%;" name="ploopi_annotationtags" id="ploopi_annotationtags_<? echo $_GET['id_annotation']; ?>" autocomplete="off"></div>
                        <div style="padding:2px 4px;" id="tagsfound_<? echo $_GET['id_annotation']; ?>"></div>
                        <div style="padding:2px 4px;">Commentaire:</div>
                        <div style="padding:2px 4px;"><textarea class="text" style="width:99%;" rows="4" name="ploopi_annotation_content"></textarea></div>

                        <div style="padding:2px 4px;text-align:right;">
                            <input type="button" onclick="ploopi_getelem('form_annotation_<? echo $_GET['id_annotation']; ?>').ploopi_op.value=''; ploopi_getelem('form_annotation_<? echo $_GET['id_annotation']; ?>').submit()" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>">
                            <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
                        </div>
                        </form>
                    </div>
                    <?
                }
                ?>
                </div>
            </div>
            <iframe name="form_annotation_target_<? echo $_GET['id_annotation']; ?>" src="./img/blank.gif" style="width:0;height:0;display:none;"></iframe>

            <script type="text/javascript">
                ploopi_annotation_tag_init('<? echo $_GET['id_annotation']; ?>');
            </script>
            <?
            ploopi_die();
        break;

        case 'annotation_taghistory':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            ?>
            <div class="ploopi_annotation_popup">
            <?
            if (isset($_GET['id_tag']) && is_numeric($_GET['id_tag']))
            {
                include_once './include/global.php';
                include_once './modules/system/class_tag.php';

                $tag = new tag();
                $tag->open($_GET['id_tag']);

                ?>
                <div style="padding:4px;">Le tag <b><? echo $tag->fields['tag'] ; ?></b> a aussi été utilisé sur les annotations suivantes :</div>
                <div class="ploopi_annotation_popup_list">
                <?


                $select =   "
                            SELECT      a.*,
                                        o.script,
                                        o.label as object_name,
                                        m.label as module_name

                            FROM        ploopi_annotation a

                            INNER JOIN  ploopi_annotation_tag at
                            ON          at.id_annotation = a.id
                            AND         at.id_tag = {$_GET['id_tag']}

                            INNER JOIN  ploopi_module m
                            ON          a.id_module = m.id

                            LEFT JOIN   ploopi_mb_object o ON o.id = a.id_object AND o.id_module_type = m.id_module_type

                            ORDER BY    a.date_annotation DESC
                            ";

                $rs = $db->query($select);

                while ($fields = $db->fetchrow($rs))
                {
                    $ld = ploopi_timestamp2local($fields['date_annotation']);
                    ?>
                    <div class="ploopi_annotations_row_<? echo $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2; ?>" style="padding:4px;">
                        <div style="float:right;"><? echo "le {$ld['date']} à {$ld['time']}"; ?></div>
                        <div style="font-weight:bold;"><? echo "{$fields['title']}"; ?></div>
                        <div style="clear:both;padding-top:4px;"><? echo ploopi_make_links(ploopi_nl2br(htmlentities($fields['content']))); ?></div>
                        <?
                        if ($fields['id_record'] != '')
                        {
                            $object_script = str_replace(
                                                        array(
                                                            '<IDRECORD>',
                                                            '<IDMODULE>',
                                                            '<IDWORKSPACE>'
                                                        ),
                                                        array(
                                                            $fields['id_record'],
                                                            $fields['id_module'],
                                                            $fields['id_workspace']
                                                        ),
                                                        $fields['script']
                                            );
                            ?>
                            <div style="clear:both;padding-top:4px;text-align:right;"><a href="<? echo "admin.php?ploopi_mainmenu=1&{$object_script}"; ?>"><? echo "{$fields['module_name']} / {$fields['object_name']} / {$fields['object_label']}"; ?></a></div>
                            <?
                        }
                        ?>
                    </div>
                    <?
                }
                ?>
                </div>
                <?
            }
            else echo "erreur";
            ?>
            <!--a style="display:block;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a-->
            <div style="padding:4px;text-align:right"><a href="javascript:void(0);" onclick="javascript:ploopi_hidepopup();">Fermer</a></div>
            </div>
            <?
            ploopi_die();
        break;

        case 'annotation_searchtags':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (!empty($_GET['tag']))
            {
                $select =   "
                            SELECT  t.id,
                                    t.tag,
                                    count(*) as c
                            FROM    ploopi_tag t,
                                    ploopi_annotation_tag at
                            WHERE   t.tag LIKE '".$db->addslashes($_GET['tag'])."%'
                            AND     t.id_user = {$_SESSION['ploopi']['userid']}
                            AND     t.id = at.id_tag
                            GROUP BY t.id
                            ORDER BY c DESC
                            ";

                $rs = $db->query($select);
                $c=0;

                while ($fields = $db->fetchrow($rs))
                {
                    if ($c++) echo '|';
                    echo "{$fields['tag']};{$fields['c']}";
                }
            }
        break;

        case 'annotation_delete':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once './modules/system/class_annotation.php';
            $annotation = new annotation();

            if (!empty($_GET['ploopi_annotation_id']) && is_numeric($_GET['ploopi_annotation_id']) && $annotation->open($_GET['ploopi_annotation_id']) && $annotation->fields['id_user'] == $_SESSION['ploopi']['userid'])
            {
                $annotation->delete();
            }
        break;

        case 'annotation_save':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (!empty($_POST['id_annotation']))
            {
                include_once './modules/system/class_annotation.php';

                $annotation = new annotation();
                $annotation->setvalues($_POST,'ploopi_annotation_');

                $annotation->fields['id_object'] = $_SESSION['annotations'][$_POST['id_annotation']]['id_object'];
                $annotation->fields['id_record'] = $_SESSION['annotations'][$_POST['id_annotation']]['id_record'];
                $annotation->fields['object_label'] = $_SESSION['annotations'][$_POST['id_annotation']]['object_label'];

                if (isset($_POST['ploopi_annotationtags'])) $annotation->tags = $_POST['ploopi_annotationtags'];
                if (!isset($_POST['ploopi_annotation_private'])) $annotation->fields['private'] = 0;

                $annotation->fields['date_annotation'] = ploopi_createtimestamp();
                $annotation->setuwm();

                if (!empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']])) $annotation->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
                $annotation->save();
                ?>
                <script type="text/javascript">
                    window.parent.ploopi_annotation('<? echo $_POST['id_annotation']; ?>');
                </script>
            <?
            }
            ploopi_die();
            //ploopi_tickets_send($annotation->fields['id_object'], $annotation->fields['id_record'], $annotation->fields['object_label'], $annotation->fields['title'], $annotation->fields['content']);
        break;

        case 'annotation_show':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (isset($_GET['object_id']))
            {
                if (isset($_SESSION['ploopi']['annotations']['show'][$_GET['object_id']])) unset($_SESSION['ploopi']['annotations']['show'][$_GET['object_id']]);
                else $_SESSION['ploopi']['annotations']['show'][$_GET['object_id']] = 1;
            }
        break;


        // FAVORITES CASES
        case 'favorites_save':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once './modules/system/class_favorite.php';
            include_once './modules/system/class_favorite_heading.php';

            $favorite = new favorite();
            $favorite->setvalues($_GET,'favorite_');

            if (isset($fav_new_heading) && $fav_new_heading != '')
            {
                $favorite_heading = new favorite_heading();
                $favorite_heading->open($favorite_id_heading);
                $child = $favorite_heading->createchild($fav_new_heading);
                $favorite->fields['id_heading'] = $child->save();
            }
            $favorite->setugm();
            $favorite->save();
            //ploopi_print_r($favorite);


            ?>
            <table style="padding:2px">
            <tr>
                <td><img src="./img/loading.gif"></td>
                <td>Enregistrement en cours</td>
            </tr>
            </table>
            <?
            ploopi_die();
        break;

        case 'favorites_addto':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            ploopi_init_module('system');
            $headings = system_favorite_getheadings();
            if (!isset($label)) $label = '';
            ?>
            <form action="" onsubmit="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=favorites_save&favorite_id_object=<? echo $id_object; ?>&favorite_id_record=<? echo $id_record; ?>&favorite_label='+this.favorite_label.value+'&favorite_id_heading='+this.favorite_id_heading.value+'&fav_new_heading='+this.fav_new_heading.value,'','ploopi_popup');setTimeout('ploopi_hidepopup()', 1500);return(false);">
            <table style="padding:0px;padding-bottom:10px;width:100%;" cellspacing="0">
            <tr>
                <td style="padding:2px; font-weight:bold;">Ajout aux favoris</strong></td>
                <td style="padding:2px;text-align:right;"><a href="javascript:ploopi_hidepopup();">Fermer</a></td>
            </tr>
            </table>

            <table style="padding:0px" cellspacing="0">
            <tr>
                <td style="padding:2px;">Intitulé:</td>
            </tr>
            <tr>
                <td style="padding:2px;text-align:left;"><input name="favorite_label" type="text" style="width:195px" class="text" value="<? echo $label; ?>"></td>
            </tr>
            <tr>
                <td style="padding:2px;">Rubrique:</td>
            </tr>
            <tr>
                <td style="padding:2px;text-align:left;">
                <select name="favorite_id_heading" style="width:195px" class="select"><? echo system_favorite_build_select($headings); ?></select>
                </td>
            </tr>
            <tr>
                <td style="padding:2px;">Sous-Rubrique:</td>
            </tr>
            <tr>
                <td style="padding:2px;text-align:left;"><input name="fav_new_heading" type="text" style="width:195px" class="text"></td>
            </tr>
            </table>

            <table style="padding:0px;width:100%;" cellspacing="0">
            <tr>
                <td align="right">
                    <table style="padding:0px;padding-top:4px;" cellspacing="0">
                    <tr>
                        <td style="padding:2px;text-align:right"><input class="button" type="submit" value="<? echo _PLOOPI_SAVE; ?>"></td>
                        <td style="padding:2px;text-align:right"><input class="button" type="button" onclick="javascript:ploopi_hidepopup()" value="<? echo _PLOOPI_CANCEL; ?>"></td>
                    </tr>
                    </table>
                </td>
            </tr>
            </table>
            </form>
            <?
            ploopi_die();
        break;

        case 'colorpicker_open':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            ?>
            <div style="overflow:hidden;padding:2px;background-color:#ffffff;z-index:1;">
                <div style="margin-bottom:2px;overflow:hidden;">
                    <div style="float:left;position:relative;width:35px;height:200px;z-index:3;">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:5;" src="./img/colorpicker/h.png" id="colorpicker_h">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:10;" src="./img/colorpicker/position.png" id="colorpicker_position">
                    </div>
                    <div style="float:left;position:relative;width:200px;height:200px;margin-left:2px;z-index:3;">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:5;" src="./img/colorpicker/sv.png" id="colorpicker_sv">
                        <img style="display:block;position:absolute;cursor:pointer;z-index:10;" src="./img/colorpicker/crosshairs.png" id="colorpicker_crosshairs">
                    </div>
                </div>
                <div style="clear:both;width:237px;height:30px;z-index:5;" id="colorpicker_selectedcolor">
                <input type="button" class="button" style="margin:6px;float:right;" value="fermer" onclick="javascript:ploopi_getelem('<? echo $_GET['inputfield_id']; ?>').value = ploopi_getelem('colorpicker_inputcolor').value; ploopi_hidepopup();var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_GET['inputfield_id']; ?>').dispatchEvent(e);">
                <input type="text" class="text" id="colorpicker_inputcolor" style="margin:6px;width:60px;float:left;" value="<? echo $_GET['colorpicker_value']; ?>">
                </div>
            </div>
            <?
            ploopi_die();
        break;

        case 'calendar_open':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $month = date('n');
            $year = date('Y');

            if (isset($_GET['selected_date']))
            {
                $sel_day = $sel_month = $sel_year = 0;

                switch(_PLOOPI_DATEFORMAT)
                {
                    case _PLOOPI_DATEFORMAT_US:
                        if (ereg(_PLOOPI_DATEFORMAT_EREG_US, $_GET['selected_date'], $regs))
                        {
                            $sel_day = $regs[3];
                            $sel_month = $regs[2];
                            $sel_year = $regs[1];

                            $month = $sel_month;
                            $year = $sel_year;
                        }
                    break;

                    case _PLOOPI_DATEFORMAT_FR:
                        if (ereg(_PLOOPI_DATEFORMAT_EREG_FR, $_GET['selected_date'], $regs))
                        {
                            $sel_day = $regs[1];
                            $sel_month = $regs[2];
                            $sel_year = $regs[3];

                            $month = $sel_month;
                            $year = $sel_year;
                        }
                    break;
                }

                $_SESSION['calendar'] = array(
                                            'selected_month'    => $sel_month,
                                            'selected_day'      => $sel_day,
                                            'selected_year'     => $sel_year
                                        );
            }
            elseif (isset($_GET['calendar_month']) && isset($_GET['calendar_year']))
            {
                    $month = $_GET['calendar_month'];
                    $year = $_GET['calendar_year'];
            }

            settype($day,'integer');
            settype($month,'integer');
            settype($year,'integer');

            if (isset($_GET['inputfield_id'])) $_SESSION['calendar']['inputfield_id'] = $_GET['inputfield_id'];

            $selectedday = mktime(0,0,0,$_SESSION['calendar']['selected_month'], $_SESSION['calendar']['selected_day'], $_SESSION['calendar']['selected_year']);
            $today = mktime(0,0,0,date('n'),date('j'),date('Y'));

            $firstday = mktime(0,0,0,$month,1,$year);

            $weekday = date('w', $firstday);
            if ($weekday == 0) $weekday = 7;

            $prev_month = ($month-1)%12+(($month-1)%12 == 0)*12;
            $next_month = ($month+1)%12+(($month+1)%12 == 0)*12;

            $prev_year = $year - ($prev_month == 12);
            $next_year = $year + ($next_month == 1);
            ?>
            <div id="calendar">
                <div class="calendar_row">
                    <div class="calendar_arrow">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=calendar_open&calendar_month=<? echo $prev_month; ?>&calendar_year=<? echo $prev_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/prev.png"></a>
                    </div>
                    <div class="calendar_month">
                        <? echo "{$ploopi_agenda_months[$month]} $year"; ?>
                    </div>
                    <div class="calendar_arrow">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=calendar_open&calendar_month=<? echo $next_month; ?>&calendar_year=<? echo $next_year; ?>','','ploopi_popup_calendar');"><img style="border:0;" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/next.png"></a>
                    </div>
                </div>
                <div class="calendar_row">
                <?
                foreach($ploopi_agenda_days as $d)
                {
                    ?>
                    <div class="calendar_day"><? echo $d[0]; ?></div>
                    <?
                }
                ?>
                </div>
                <?
                if ($weekday > 1)
                {
                    ?>
                    <div class="calendar_row">
                    <?
                    for ($d = 1; $d < $weekday; $d++)
                    {
                        ?>
                        <div class="calendar_day"><div>&nbsp;</div></div>
                        <?
                    }
                }

                for ($d = 1; $d <= date('t', $firstday) ; $d++)
                {
                    if ($weekday == 8) $weekday = 1;

                    if ($weekday == 1)
                    {
                        ?>
                        <div class="calendar_row">
                        <?
                    }
                    $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", $year, $month, $d));
                    $class = '';
                    $currentday = mktime(0,0,0,$month, $d, $year);
                    if ($currentday == $selectedday) $class = 'class="calendar_day_selected"';
                    elseif ($currentday == $today) $class = 'class="calendar_day_today"';
                    ?>
                        <div class="calendar_day"><a <? echo $class; ?> href="javascript:void(0);" onclick="javascript:ploopi_getelem('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').dispatchEvent(e);"><? echo $d; ?></a></div>
                    <?

                    if ($weekday == 7) echo '</div>';
                    $weekday++;
                }

                if ($weekday <= 7)
                {
                    for ($d = $weekday; $d <= 7 ; $d++)
                    {
                        ?>
                        <div class="calendar_day"><div>&nbsp;</div></div>
                        <?
                    }

                    echo '</div>';
                }

                $localdate = ploopi_timestamp2local(sprintf("%04d%02d%02d000000", date('Y'), date('n'), date('j')));
                ?>
                <div class="calendar_row" style="height:1.2em;">
                    <a style="display:block;float:left;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_getelem('<? echo $_SESSION['calendar']['inputfield_id']; ?>').value='<? echo $localdate['date']; ?>';ploopi_hidepopup('ploopi_popup_calendar');var e = document.createEvent('HTMLEvents');e.initEvent('change', false, false);$('<? echo $_SESSION['calendar']['inputfield_id']; ?>').dispatchEvent(e);">Aujourd'hui</a>
                    <a style="display:block;float:right;line-height:1.2em;height:1.2em;" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup('ploopi_popup_calendar');">Fermer</a>
                </div>
            </div>
            <?
            ploopi_die();
        break;

          /***********************/
         /** DOCUMENTS_BROWSER **/
        /***********************/

        case 'documents_selectfile':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $_SESSION['documents']['id_object'] = $_GET['id_object'];
            $_SESSION['documents']['id_record'] = $_GET['id_record'];
            $_SESSION['documents']['id_user'] = $_SESSION['ploopi']['userid'];
            $_SESSION['documents']['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
            $_SESSION['documents']['id_module'] = $_SESSION['ploopi']['moduleid'];
            $_SESSION['documents']['documents_id'] = $_GET['documents_id'];
            $_SESSION['documents']['mode'] = 'selectfile';
            $_SESSION['documents']['destfield'] = $_GET['destfield'];

        case 'documents_browser':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once('./include/classes/class_documentsfolder.php');
            include_once('./include/classes/class_documentsfile.php');

            if (isset($_REQUEST['currentfolder'])) $currentfolder = $_REQUEST['currentfolder'];
            if (isset($_REQUEST['mode'])) $_SESSION['documents']['mode'] = $_REQUEST['mode'];

            if (empty($currentfolder)) // on va chercher la racine
            {
                $db->query("SELECT id FROM ploopi_documents_folder WHERE id_folder = 0 and id_object = '{$_SESSION['documents']['id_object']}' and id_record = '".$db->addslashes($_SESSION['documents']['id_record'])."'");

                if ($row = $db->fetchrow()) $currentfolder = $row['id'];
                else // racine inexistante, il faut la créer
                {
                    $documentsfolder = new documentsfolder();
                    $documentsfolder->fields['name'] = 'Racine';
                    $documentsfolder->fields['id_folder'] = 0;
                    $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
                    $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
                    $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
                    $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
                    $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
                    $currentfolder = $documentsfolder->save();
                }
            }

            ?>

            <div class="documents_browser">

                <div class="documents_path">
                    <?
                    // voir pour une optimisation de cette partie car on ouvre un docfolder sans doute pour rien
                    $documentsfolder = new documentsfolder();

                    if (!empty($currentfolder)) $documentsfolder->open($currentfolder);
                    ?>

                    <a title="Rechercher un Fichier" href="javascript:void(0);" style="float:right;"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_search.png"></a>
                    <?
                    if (empty($_SESSION['documents']['mode']))
                    {
                        if ($_SESSION['documents']['rights']['DOCUMENT_CREATE'])
                        {
                            ?><a title="Créer un nouveau fichier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfile('<? echo $currentfolder; ?>','',event);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfile.png"></a><?
                        }
                        if ($_SESSION['documents']['rights']['FOLDER_CREATE'])
                        {
                            ?>
                            <a title="Créer un nouveau Dossier" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_openfolder('<? echo $currentfolder; ?>','',event);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_newfolder.png"></a>
                            <?
                        }
                    }
                    ?>
                    <a title="Aller au Dossier Racine" href="javascript:void(0);" style="float:right;" onclick="javascript:ploopi_documents_browser('','<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>','',true);"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_home.png"></a>

                    <div>Emplacement :</div>
                    <?
                    if ($currentfolder != 0)
                    {
                        $documentsfolder = new documentsfolder();
                        $documentsfolder->open($currentfolder);

                        $db->query("SELECT id, name, id_folder FROM ploopi_documents_folder WHERE id in ({$documentsfolder->fields['parents']},{$currentfolder}) ORDER by id");

                        while ($row = $db->fetchrow())
                        {
                            // change root name
                            $foldername = (!$row['id_folder']) ? $_SESSION['documents']['root_name'] : $row['name'];
                            ?>
                            <a <? if ($currentfolder == $row['id']) echo 'class="doc_pathselected"'; ?> href="javascript:void(0);" onclick="javascript:ploopi_documents_browser('<? echo $row['id']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>','',true);">
                                <p class="ploopi_va">
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/documents/ico_folder.png" />
                                    <span><? echo $foldername; ?></span>
                                </p>
                            </a>
                            <?
                        }
                    }
                    ?>
                </div>
                <?

                // initialisation  du tri par défaut pour le browser courant
                if (empty($_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'])) $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] = 'nom';
                if (empty($_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'])) $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] = 'ASC';

                // doit-on inverser le sens du tri ? (si l'orderby demandé et le meme que celui stocké en session)
                $invertsort = (!empty($_GET['orderby']) && $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] == $_GET['orderby']);

                // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
                $orderby = (empty($_GET['orderby'])) ? $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] : $_GET['orderby'];

                // doit-on réinitialiser le sens du tri ?
                $resetsort = ($_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] != $orderby);

                $sort_option = '';

                if ($resetsort) $sort_option = 'ASC';
                else
                {
                    if ($invertsort)
                    {
                        if ($_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] == 'ASC') $sort_option = 'DESC';
                        else $sort_option = 'ASC';
                    }
                    else $sort_option = $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'];
                }

                $_SESSION['documents'][$_SESSION['documents']['documents_id']]['orderby'] = $orderby;
                $_SESSION['documents'][$_SESSION['documents']['documents_id']]['sort'] = $sort_option;

                $sort_img = '';
                if (!empty($sort_option)) $sort_img = ($sort_option == 'DESC') ? '<img src="./modules/agrid/img/arrow_down.png">' : '<img src="./modules/agrid/img/arrow_up.png">';

                $documents_columns = array();


                $sort_column = ($orderby == 'nom') ? $sort_img : '';
                $documents_columns['auto'][1] = array(  'label' => '<span>Nom&nbsp;</span>'.$sort_column,
                                                        'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'nom',true);",
                                                        'style' => ($orderby == 'nom') ? 'background-color:#e0e0e0;' : ''
                                                        );

                if (empty($_SESSION['documents']['fields']) || in_array('type', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'type') ? $sort_img : '';
                    $documents_columns['right'][3] = array( 'label' => '<span>Type&nbsp;</span>'.$sort_column,
                                                            'width' => '65',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'type',true);",
                                                            'style' => ($orderby == 'type') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('timestp_modify', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'date_modif') ? $sort_img : '';
                    $documents_columns['right'][4] = array( 'label' => '<span>Date Modif&nbsp;</span>'.$sort_column,
                                                            'width' => '130',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'date_modif',true);",
                                                            'style' => ($orderby == 'date_modif') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('timestp_file', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'date') ? $sort_img : '';
                    $documents_columns['right'][5] = array( 'label' => '<span>Date&nbsp;</span>'.$sort_column,
                                                            'width' => '80',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'date',true);",
                                                            'style' => ($orderby == 'date') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('ref', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'ref') ? $sort_img : '';
                    $documents_columns['right'][6] = array( 'label' => '<span>Ref&nbsp;</span>'.$sort_column,
                                                            'width' => '75',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'ref',true);",
                                                            'style' => ($orderby == 'ref') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('label', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'libelle') ? $sort_img : '';
                    $documents_columns['right'][7] = array( 'label' => '<span>Libellé&nbsp;</span>'.$sort_column,
                                                            'width' => '110',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'libelle',true);",
                                                            'style' => ($orderby == 'libelle') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['fields']) || in_array('size', $_SESSION['documents']['fields']))
                {
                    $sort_column = ($orderby == 'taille') ? $sort_img : '';
                    $documents_columns['right'][8] = array( 'label' => '<span>Taille&nbsp;</span>'.$sort_column,
                                                            'width' => '90',
                                                            'onclick' => "ploopi_documents_browser('{$currentfolder}', '{$_SESSION['documents']['documents_id']}', '{$_SESSION['documents']['mode']}', 'taille',true);",
                                                            'style' => ($orderby == 'taille') ? 'background-color:#e0e0e0;' : ''
                                                            );
                }

                if (empty($_SESSION['documents']['mode'])) $documents_columns['actions_right'][9] = array('label' => 'Actions', 'width' => '85');

                // DISPLAY FOLDERS
                $documents_folder_values = array();

                $orderby_option = '';

                if (!empty($orderby))
                {
                    switch($orderby)
                    {
                        case 'date_modify':
                            $orderby_option = 'f.timestp_modify';
                        break;

                        case 'taille':
                            $orderby_option = 'f.nbelements';
                        break;

                        case 'libelle':
                            $orderby_option = 'f.description';
                        break;

                        default:
                        case 'nom':
                            $orderby_option = 'f.name';
                        break;
                    }
                }

                $orderby_option = "ORDER BY {$orderby_option} {$sort_option}";

                $sql =  "
                        SELECT      f.*,
                                    u.login
                        FROM        ploopi_documents_folder f
                        LEFT JOIN   ploopi_user u
                        ON          f.id_user = u.id
                        WHERE       f.id_folder = {$currentfolder}

                        {$orderby_option}
                        ";

                $db->query($sql);

                $i = 0;
                while ($row = $db->fetchrow())
                {
                    $ldate = ploopi_timestamp2local($row['timestp_modify']);

                    $documents_folder_values[$i]['values'][1] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/ico_folder.png\" /><span>&nbsp;{$row['name']}</span>", 'style' => '');
                    $documents_folder_values[$i]['values'][3] = array('label' => 'Dossier', 'style' => '');
                    $documents_folder_values[$i]['values'][4] = array('label' => "{$ldate['date']} {$ldate['time']}", 'style' => '');
                    $documents_folder_values[$i]['values'][5] = array('label' => '&nbsp;', 'style' => '');
                    $documents_folder_values[$i]['values'][6] = array('label' => '&nbsp;', 'style' => '');
                    $documents_folder_values[$i]['values'][7] = array('label' => '&nbsp;', 'style' => '');
                    $documents_folder_values[$i]['values'][8] = array('label' => "{$row['nbelements']} element(s)", 'style' => '');

                    $actions = '';
                    if ($_SESSION['documents']['rights']['DOCUMENT_MODIFY']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le dossier et son contenu\')) ploopi_documents_deletefolder(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
                    if ($_SESSION['documents']['rights']['DOCUMENT_DELETE']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfolder(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

                    if ($actions == '') $actions = '&nbsp;';

                    if (empty($_SESSION['documents']['mode'])) $documents_folder_values[$i]['values'][9] = array('label' => $actions, 'style' => '');

                    $documents_folder_values[$i]['description'] = '';
                    $documents_folder_values[$i]['link'] = 'javascript:void(0);';
                    $documents_folder_values[$i]['option'] = 'onclick="javascript:ploopi_documents_browser(\''.$row['id'].'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$_SESSION['documents']['mode'].'\',\'\',true);"';
                    $documents_folder_values[$i]['style'] = '';

                    $i++;
                }

                // DISPLAY FILES
                $documents_file_values = array();

                $orderby_option = '';

                if (!empty($orderby))
                {
                    switch($orderby)
                    {
                        case 'date_modif':
                            $orderby_option = 'f.timestp_modify';
                        break;

                        case 'ref':
                            $orderby_option = 'f.ref';
                        break;

                        case 'nom':
                            $orderby_option = 'f.name';
                        break;

                        case 'libelle':
                            $orderby_option = 'f.label';
                        break;

                        case 'type':
                            $orderby_option = 'f.name';
                        break;

                        case 'date':
                            $orderby_option = 'f.timestp_file';
                        break;

                        case 'taille':
                            $orderby_option = 'f.size';
                        break;
                    }
                }

                $orderby_option = "ORDER BY {$orderby_option} {$sort_option}";

                $sql =  "
                        SELECT      f.*,
                                    u.login,
                                    e.filetype

                        FROM        ploopi_documents_file f

                        LEFT JOIN   ploopi_user u
                        ON          f.id_user = u.id

                        LEFT JOIN   ploopi_documents_ext e
                        ON          e.ext = f.extension

                        WHERE       f.id_folder = {$currentfolder}

                        {$orderby_option}
                        ";

                $db->query($sql);

                while ($row = $db->fetchrow())
                {
                    $ksize = sprintf("%.02f",$row['size']/1024);
                    $ldate = ploopi_timestamp2local($row['timestp_modify']);

                    $ldate_file = ($row['timestp_file'] != 0) ? ploopi_timestamp2local($row['timestp_file']) : array('date' => '');

                    $ico = (file_exists("{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/ico_{$row['filetype']}.png")) ? "ico_{$row['filetype']}.png" : 'ico_default.png';

                    $actions = '';

                    if ($_SESSION['documents']['rights']['FOLDER_MODIFY']) $actions .= '<a title="Supprimer" style="display:block;float:right;" href="javascript:if (confirm(\'Attention, cette action va supprimer définitivement le fichier\')) ploopi_documents_deletefile(\''.$currentfolder.'\',\''.$_SESSION['documents']['documents_id'].'\',\''.$row['id'].'\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_trash.png" /></a>';
                    if ($_SESSION['documents']['rights']['FOLDER_DELETE']) $actions .= '<a title="Modifier" style="display:block;float:right;" href="javascript:void(0);" onclick="javascript:ploopi_documents_openfile(\''.$currentfolder.'\',\''.$row['id'].'\',event);"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_modify.png" /></a>';

                    $documents_file_values[$i]['values'][1] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/documents/mimetypes/{$ico}\" /><span>&nbsp;{$row['name']}</span>", 'style' => '');
                    $documents_file_values[$i]['values'][3] = array('label' => 'Fichier', 'style' => '');
                    $documents_file_values[$i]['values'][4] = array('label' => "{$ldate['date']} {$ldate['time']}", 'style' => '');
                    $documents_file_values[$i]['values'][5] = array('label' => $ldate_file['date'], 'style' => '');
                    $documents_file_values[$i]['values'][6] = array('label' => $row['ref'], 'style' => '');
                    $documents_file_values[$i]['values'][7] = array('label' => $row['label'], 'style' => '');
                    $documents_file_values[$i]['values'][8] = array('label' => "{$ksize} ko", 'style' => '');
                    $documents_file_values[$i]['values'][9] = array('label' => $actions.'<a title="Télécharger" style="display:block;float:right;" href="'.ploopi_urlencode("{$scriptenv}?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download.png" /></a>
                                                                                        <a title="Télécharger (ZIP)" style="display:block;float:right;" href="'.ploopi_urlencode("{$scriptenv}?ploopi_op=documents_downloadfile_zip&documentsfile_id={$row['id']}").'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/documents/ico_download_zip.png" /></a>
                                                                                        ', 'style' => '');
                    $documents_file_values[$i]['description'] = '';
                    if ($_SESSION['documents']['mode'] == 'selectfile')
                    {
                        $documents_file_values[$i]['link'] = 'javascript:void(0);';
                        $documents_file_values[$i]['onclick'] = "javascript:ploopi_getelem('{$_SESSION['documents']['destfield']}').value='{$row['name']}';ploopi_getelem('{$_SESSION['documents']['destfield']}_id').value='{$row['id']}';ploopi_hidepopup();";
                    }
                    else $documents_file_values[$i]['link'] = ploopi_urlencode("admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$row['id']}&attachement=".$_SESSION['documents']['attachement']);


                    $documents_file_values[$i]['style'] = '';

                    $i++;
                }


                if ($sort_option == 'ASC') $skin->display_array($documents_columns, array_merge($documents_folder_values, $documents_file_values));
                else $skin->display_array($documents_columns, array_merge($documents_file_values, $documents_folder_values));
                ?>
            </div>
            <?
            ploopi_die();
        break;

        case 'documents_popup':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            //include_once './include/functions/documents.php';
            //ploopi_documents($_GET['id_object'], $_GET['id_record']);
            ?>
            <div id="ploopidocuments_<? echo ploopi_documents_getid($_GET['id_object'], $_GET['id_record']); ?>"></div>
            <?
            ploopi_die();
        break;

        case 'documents_downloadfile':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (!empty($_GET['documentsfile_id']))
            {
                include_once('./include/classes/class_documentsfile.php');

                $documentsfile = new documentsfile();
                $documentsfile->open($_GET['documentsfile_id']);

                $attachement = true;

                if (isset($_GET['attachement']) && ($_GET['attachement'] == 0 || $_GET['attachement'] == 'false')) $attachement = false;

                if (file_exists($documentsfile->getfilepath())) ploopi_downloadfile($documentsfile->getfilepath(),$documentsfile->fields['name'], false, $attachement);
            }
            ploopi_die();
        break;

        case 'documents_downloadfile_zip':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $zip_path = ploopi_documents_getpath()._PLOOPI_SEP.'zip';
            if (!is_dir($zip_path)) mkdir($zip_path);

            if (!empty($_GET['documentsfile_id']))
            {
                include_once './lib/pclzip-2-5/pclzip.lib.php';
                include_once('./include/classes/class_documentsfile.php');

                $documentsfile = new documentsfile();
                $documentsfile->open($_GET['documentsfile_id']);

                if (file_exists($documentsfile->getfilepath()) && is_writeable($zip_path))
                {
                    // create a temporary file with the real name
                    $tmpfilename = $zip_path._PLOOPI_SEP.$documentsfile->fields['name'];

                    copy($documentsfile->getfilepath(),$tmpfilename);

                    // create zip file
                    $zip_filename = "archive_{$_GET['documentsfile_id']}.zip";
                    echo $zip_filepath = $zip_path._PLOOPI_SEP.$zip_filename;
                    $zip = new PclZip($zip_filepath);
                    $zip->create($tmpfilename,PCLZIP_OPT_REMOVE_ALL_PATH);

                    // delete temporary file
                    unlink($tmpfilename);

                    // download zip file
                    ploopi_downloadfile($zip_filepath, $zip_filename, true);
                }
            }

            ploopi_die();
        break;

        case 'documents_savefolder':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once('./include/classes/class_documentsfolder.php');
            $documentsfolder = new documentsfolder();

            if (!empty($_POST['documentsfolder_id']))
            {
                $documentsfolder->open($_POST['documentsfolder_id']);
                $documentsfolder->setvalues($_POST,'documentsfolder_');
                $documentsfolder->save();
            }
            else // new folder
            {
                $documentsfolder->setvalues($_POST,'documentsfolder_');
                $documentsfolder->fields['id_folder'] = $_POST['currentfolder'];
                $documentsfolder->fields['id_object'] = $_SESSION['documents']['id_object'];
                $documentsfolder->fields['id_record'] = $_SESSION['documents']['id_record'];
                $documentsfolder->fields['id_module'] = $_SESSION['documents']['id_module'];
                $documentsfolder->fields['id_user'] = $_SESSION['documents']['id_user'];
                $documentsfolder->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
                $documentsfolder->save();
            }
            ?>
            <script type="text/javascript">
                window.parent.ploopi_documents_browser('<? echo $_POST['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>')
                window.parent.ploopi_hidepopup();
            </script>
            <?
            ploopi_die();
        break;

        case 'documents_openfolder':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once('./include/classes/class_documentsfolder.php');
            $documentsfolder = new documentsfolder();

            if (empty($_GET['documentsfolder_id']))
            {
                $documentsfolder->init_description();
                ?>
                <div class="documents_formtitle">Nouveau Dossier</div>
                <?
            }
            else
            {
                $documentsfolder->open($_GET['documentsfolder_id']);
                ?>
                <div class="documents_formtitle">Modification du Dossier</div>
                <?
            }
            ?>
            <form id="documents_folderform" action="admin-light.php" method="post" target="documents_folderform_iframe" enctype="multipart/form-data">
            <input type="hidden" name="ploopi_op" value="documents_savefolder">
            <input type="hidden" name="currentfolder" value="<? echo $_GET['currentfolder']; ?>">
            <?
            if (!empty($_GET['documentsfolder_id']))
            {
                ?>
                <input type="hidden" name="documentsfolder_id" value="<? echo $_GET['documentsfolder_id']; ?>">
                <?
            }
            ?>

            <div class="ploopi_form">
                <div class="documents_formcontent">
                    <p>
                        <label>Libellé:</label>
                        <input type="text" class="text" name="documentsfolder_name" value="<? echo htmlentities($documentsfolder->fields['name']); ?>">
                    </p>
                    <p>
                        <label>Commentaire:</label>
                        <textarea class="text" name="documentsfolder_description"><? echo htmlentities($documentsfolder->fields['description']); ?></textarea>
                    </p>
                </div>
                <div class="documents_formcontent" style="text-align:right;padding:4px;">
                    <input type="button" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup();">
                    <input type="submit" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_SAVE; ?>">
                    <!-- onclick="javascript:ploopi_hidepopup();ploopi_documents_browser('<? echo $_GET['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>')" -->
                </div>
            </div>
            </form>
            <iframe name="documents_folderform_iframe" src="./img/blank.gif" style="width:0;height:0;visibility:hidden;display:none;"></iframe>
            <?
            ploopi_die();
        break;

        case 'documents_savefile':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once('./include/classes/class_documentsfile.php');
            $documentsfile = new documentsfile();

            if (!empty($_POST['documentsfile_id'])) $documentsfile->open($_POST['documentsfile_id']);
            else
            {
                $documentsfile->fields['id_object'] = $_SESSION['documents']['id_object'];
                $documentsfile->fields['id_record'] = $_SESSION['documents']['id_record'];
                $documentsfile->fields['id_module'] = $_SESSION['documents']['id_module'];
                $documentsfile->fields['id_user'] = $_SESSION['documents']['id_user'];
                $documentsfile->fields['id_workspace'] = $_SESSION['documents']['id_workspace'];
            }

            $documentsfile->setvalues($_POST,'documentsfile_');
            $documentsfile->fields['timestp_file'] = ploopi_local2timestamp($documentsfile->fields['timestp_file']);
            $documentsfile->fields['id_folder'] = $_POST['currentfolder'];

            if (!empty($_FILES['documentsfile_file']['name']))
            {
                $documentsfile->fields['id_user_modify'] = $_SESSION['ploopi']['userid'];
                $documentsfile->tmpfile = $_FILES['documentsfile_file']['tmp_name'];
                $documentsfile->fields['name'] = $_FILES['documentsfile_file']['name'];
                $documentsfile->fields['size'] = $_FILES['documentsfile_file']['size'];
            }

            $error = $documentsfile->save();
            ?>
            <script type="text/javascript">
                window.parent.ploopi_documents_browser('<? echo $_POST['currentfolder']; ?>', '<? echo $_SESSION['documents']['documents_id']; ?>', '<? echo $_SESSION['documents']['mode']; ?>')
                window.parent.ploopi_hidepopup();
            </script>
            <?
            ploopi_die();
        break;

        case 'documents_openfile':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            include_once('./include/classes/class_documentsfile.php');
            $documentsfile = new documentsfile();

            if (empty($_GET['documentsfile_id']))
            {
                $documentsfile->init_description();
                ?>
                <div class="documents_formtitle">Nouveau Fichier</div>
                <?
            }
            else
            {
                $documentsfile->open($_GET['documentsfile_id']);
                ?>
                <div class="documents_formtitle">Modification du Fichier</div>
                <?

            }

            $ldate = ($documentsfile->fields['timestp_file']!=0 && $documentsfile->fields['timestp_file']!='') ? ploopi_timestamp2local($documentsfile->fields['timestp_file']) : array('date' => '');
            ?>
            <form id="documents_folderform" action="admin-light.php" method="post" target="documents_fileform_iframe" enctype="multipart/form-data" onsubmit="javascript:return ploopi_documents_validate(this)">
            <input type="hidden" name="ploopi_op" value="documents_savefile">
            <input type="hidden" name="currentfolder" value="<? echo $_GET['currentfolder']; ?>">
            <?
            if (!empty($_GET['documentsfile_id']))
            {
                ?>
                <input type="hidden" name="documentsfile_id" value="<? echo $_GET['documentsfile_id']; ?>">
                <?
            }
            ?>
            <div class="ploopi_form">
                <div class="documents_formcontent">
                    <?
                    if (empty($_GET['documentsfile_id']))
                    {
                        ?>
                        <p>
                            <label>Fichier:</label>
                            <input type="file" class="text" name="documentsfile_file" tabindex="1">
                        </p>
                        <?
                    }
                    else
                    {
                        ?>
                        <p>
                            <label>Nom du Fichier:</label>
                            <input type="input" class="text" name="documentsfile_name" value="<? echo htmlentities($documentsfile->fields['name']); ?>" tabindex="2">
                        </p>
                        <p>
                            <label>Nouveau Fichier:</label>
                            <input type="file" class="text" name="documentsfile_file" tabindex="2">
                        </p>
                        <?
                    }
                    ?>
                    <p>
                        <label>Libellé:</label>
                        <input class="text" name="documentsfile_label" value="<? echo htmlentities($documentsfile->fields['label']); ?>" tabindex="3">
                    </p>
                    <p>
                        <label>Référence:</label>
                        <input class="text" name="documentsfile_ref" value="<? echo htmlentities($documentsfile->fields['ref']); ?>" tabindex="4">
                    </p>
                    <p>
                        <label>Date:</label>
                        <input class="text" id="documentsfile_timestp_file" name="documentsfile_timestp_file" value="<? echo $ldate['date']; ?>" readonly style="width:75px;" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);" tabindex="5">
                        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('documentsfile_timestp_file', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                    </p>
                    <p>
                        <label>Mots Clés:</label>
                        <textarea class="text" name="documentsfile_description" tabindex="6"><? echo htmlentities($documentsfile->fields['description']); ?></textarea>
                    </p>
                </div>
                <div class="documents_formcontent" style="text-align:right;padding:4px;">
                    <input type="button" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup();">
                    <input type="submit" class="flatbutton" style="width:100px;" value="<? echo _PLOOPI_SAVE; ?>" tabindex="7">
                </div>
            </div>
            </form>
            <iframe name="documents_fileform_iframe" src="./img/blank.gif" style="width:0;height:0;visibility:hidden;display:none;"></iframe>
            <?
            ploopi_die();
        break;

        case 'documents_deletefile':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (!empty($_GET['documentsfile_id']))
            {
                include_once('./include/classes/class_documentsfile.php');

                $documentsfile = new documentsfile();
                $documentsfile->open($_GET['documentsfile_id']);

                $documentsfile->delete();
            }

            ploopi_redirect("{$scriptenv}?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}");
        break;

        case 'documents_deletefolder':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (!empty($_GET['documentsfolder_id']))
            {
                include_once('./include/classes/class_documentsfolder.php');

                $documentsfolder = new documentsfolder();
                $documentsfolder->open($_GET['documentsfolder_id']);

                $documentsfolder->delete();
            }
            ploopi_redirect("{$scriptenv}?ploopi_op=documents_browser&currentfolder={$_GET['currentfolder']}");
        break;


        default: // look for ploopi_op in modules

            if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
            {
                foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm)
                {
                    if (isset($_SESSION['ploopi']['modules'][$idm]))
                    {
                        if ($_SESSION['ploopi']['modules'][$idm]['active'])
                        {
                            $ploopi_mod_opfile = "./modules/{$_SESSION['ploopi']['modules'][$idm]['moduletype']}/op.php";
                            if (file_exists($ploopi_mod_opfile)) include_once $ploopi_mod_opfile;
                        }
                    }

                }
            }
            include_once "./modules/system/op.php";
        break;
    }

    //ploopi_die('fonction non définie');
}

?>
