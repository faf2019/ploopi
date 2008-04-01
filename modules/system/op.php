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

if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM)
{
    switch($ploopi_op)
    {
        /*
         * TICKETS
         *
         * */

        case 'tickets_delete':
            include_once('./modules/system/class_ticket.php');

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $arrTickets[] = $_GET['ticket_id'];
            }
            elseif (isset($_POST['tickets_delete_id']) && is_array($_POST['tickets_delete_id']))
            {
                $arrTickets = $_POST['tickets_delete_id'];
            }

            foreach($arrTickets as $ticket_id)
            {
                $ticket = new ticket();
                if (is_numeric($ticket_id) && $ticket->open($ticket_id))
                {
                    if ($_SESSION['ploopi']['userid'] == $ticket->fields['id_user']) // utilisateur = emetteur
                    {
                        $ticket->fields['deleted'] = 1;
                        $ticket->save();
                    }

                    include_once('./modules/system/class_ticket_dest.php');
                    $ticket_dest = new ticket_dest();
                    if ($ticket_dest->open($_SESSION['ploopi']['userid'], $ticket_id))
                    {
                        $ticket_dest->fields['deleted'] = 1;
                        $ticket_dest->save();
                    }
                }
            }

            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;

        case 'tickets_send':
            include_once('./modules/system/class_ticket.php');
            $ticket = new ticket();
            if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']))
            {
                include_once('./modules/system/class_ticket_dest.php');

                $root_ticket = new ticket();
                if ($ticket->fields['root_id'] && $root_ticket->open($ticket->fields['root_id']))
                {
                    $root_ticket->fields['count_replies']++;
                    $root_ticket->fields['lastreply_timestp'] = ploopi_createtimestamp();
                    $root_ticket->save();
                }

                $response = new ticket();
                $response->fields = $ticket->fields;
                $response->fields['id'] = '';
                $response->fields['title'] = $_POST['ticket_title'];
                $response->fields['message'] = $_POST['fck_ticket_message'];
                $response->fields['id_user'] = $_SESSION['ploopi']['userid'];
                $response->fields['timestp'] = ploopi_createtimestamp();
                $response->fields['lastreply_timestp'] = $response->fields['timestp'];
                $response->fields['parent_id'] = $_POST['ticket_id'];
                $response->fields['root_id'] = $ticket->fields['root_id'];
                $id_resp = $response->save();

                $db->query("DELETE FROM ploopi_ticket_watch WHERE id_ticket = {$ticket->fields['root_id']} AND id_user <> {$_SESSION['ploopi']['userid']}");
            }
            else
            {
                if (!empty($_POST['ticket_title']) && isset($_POST['fck_ticket_message']))
                {
                    ploopi_tickets_send($_POST['ticket_title'], $_POST['fck_ticket_message'], isset($_POST['ticket_needed_validation']) ? $_POST['ticket_needed_validation'] : 0, isset($_POST['ticket_delivery_notification']) ? $_POST['ticket_delivery_notification'] : 0);
                }
            }

            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;

        case 'tickets_new':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            ob_start();
            ?>
            <div id="tickets_new">
                <form method="post" action="admin.php">
                <input type="hidden" name="ploopi_op" value="tickets_send">
                <table cellpadding="2" cellspacing="0" style="width:100%">
                <?
                if (isset($_GET['id_object']))
                {
                    ?>
                    <input type="hidden" name="id_object" value="<? echo htmlentities($_GET['id_object']); ?>">
                    <?
                }
                if (isset($_GET['id_record']))
                {
                    ?>
                    <input type="hidden" name="id_record" value="<? echo htmlentities($_GET['id_record']); ?>">
                    <?
                }
                if (isset($_GET['object_label']))
                {
                    ?>
                    <input type="hidden" name="object_label" value="<? echo htmlentities($_GET['object_label']); ?>">
                    <tr><td style="font-weight:bold;">Objet / Ref</td></tr>
                    <tr>
                        <td><? echo $_GET['object_label']; ?></td>
                    </tr>
                    <?
                }
                ?>
                <tr><td style="font-weight:bold;">Titre</td></tr>
                <tr>
                    <td><input type="text" name="ticket_title" class="text" value="" style="width:380px;"></td>
                </tr>
                <tr><td style="font-weight:bold;">Message</td></tr>
                <tr>
                    <td>
                    <?
                    include_once('./FCKeditor/fckeditor.php') ;

                    $oFCKeditor = new FCKeditor('fck_ticket_message') ;

                    $oFCKeditor->BasePath   = "./FCKeditor/";

                    // width & height
                    $oFCKeditor->Width='100%';
                    $oFCKeditor->Height='200';

                    $oFCKeditor->Config['CustomConfigurationsPath'] = "../../modules/system/fckeditor/fckconfig.js"  ;
                    $oFCKeditor->Config['EditorAreaCSS'] = "../../modules/system/fckeditor/fck_editorarea.css" ;
                    $oFCKeditor->Create('FCKeditor_1') ;
                    ?>
                    </td>
                </tr>
                <tr><td colspan="2" style="font-weight:bold;"><input type="checkbox" name="ticket_needed_validation" value="1">&nbsp;Validation requise</td></tr>

                <tr>
                    <td><? ploopi_tickets_selectusers(false,null,380); ?></td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <input type="submit" class="flatbutton" value="Envoyer" style="font-weight:bold;">
                        <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
                    </td>
                </tr>
                </table>
                </form>
            </div>
            <?
            $content = ob_get_contents();
            ob_end_clean();
            echo $skin->create_popup('Tickets', $content, 'system_popupticket');
            ploopi_die();
        break;

        case 'tickets_replyto':
        case 'tickets_modify':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();
            
            ob_start();
            include_once './modules/system/class_ticket.php';
            $ticket = new ticket();

            if (!empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id']))
            {
                if ($ploopi_op == 'tickets_replyto')
                {
                    $ticket->fields['title'] = "RE: {$ticket->fields['title']}";
                    $nextop = 'tickets_send';
                    $button_value = 'Envoyer';

                    if (isset($_GET['quoted'])) $ticket->fields['message'] = '<div class="system_tickets_quoted_user">Message de <b>'.$strUserName.'</b> :</div><div class="system_tickets_quoted_message">'.$ticket->fields['message'].'</div>';
                    else $ticket->fields['message'] = '';
                }
                else
                {
                    $nextop = 'tickets_modify_next';
                    $button_value = 'Modifier';
                }

                include_once './modules/system/class_user.php';

                $objUser = new user();
                $strUserName = ($objUser->open($ticket->fields['id_user'])) ? "{$objUser->fields['firstname']} {$objUser->fields['lastname']}" : 'Inconnu';



                ?>
                <div id="tickets_new">
                    <form method="post" action="admin.php">
                    <input type="hidden" name="ploopi_op" value="<? echo $nextop; ?>">
                    <input type="hidden" name="ticket_id" value="<? echo $_GET['ticket_id']; ?>">
                    <table cellpadding="2" cellspacing="0" style="width:100%">
                    <tr><td style="font-weight:bold;">Titre</td></tr>
                    <tr>
                        <td><input type="text" name="ticket_title" class="text" value="<? echo htmlentities($ticket->fields['title']); ?>" style="width:380px"></td>
                    </tr>
                    <tr><td style="font-weight:bold;">Message</td></tr>
                    <tr>
                        <td>
                        <?
                        include_once('./FCKeditor/fckeditor.php') ;

                        $oFCKeditor = new FCKeditor('fck_ticket_message') ;

                        $oFCKeditor->BasePath = "./FCKeditor/";

                        // default value
                        $oFCKeditor->Value = $ticket->fields['message'];

                        // width & height
                        $oFCKeditor->Width='100%';
                        $oFCKeditor->Height='200';

                        $oFCKeditor->Config['CustomConfigurationsPath'] = "../../modules/system/fckeditor/fckconfig.js"  ;
                        $oFCKeditor->Config['EditorAreaCSS'] = "../../modules/system/fckeditor/fck_editorarea.css" ;
                        $oFCKeditor->Create('FCKeditor_1') ;
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:right;">
                            <input type="submit" class="flatbutton" value="<? echo $button_value; ?>" style="font-weight:bold;">
                            <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
                        </td>
                    </tr>
                    </table>
                    </form>
                </div>
                <?
            }
            $content = ob_get_contents();
            ob_end_clean();
            echo $skin->create_popup('Tickets', $content, 'system_popupticket');
            ploopi_die();
        break;

        case 'tickets_modify_next':
            include_once('./modules/system/class_ticket.php');
            $ticket = new ticket();
            if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']))
            {
                $_POST['ticket_message'] = $_POST['fck_ticket_message'];
                unset($_POST['fck_ticket_message']);
                $ticket->fields['lastedit_timestp'] = ploopi_createtimestamp();
                $ticket->setvalues($_POST, 'ticket_');
                $ticket->save();
            }

            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;


        case 'tickets_search_users':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            $listgroup = array();
            include_once './modules/system/class_group.php';
            include_once './modules/system/class_workspace.php';
            $group = new group();
            $workspace = new workspace();
            $list = array();
            $list['work'] = array();
            $list['org'] = array();

            // construction de la liste des groupes de travail et des groupes d'utilisateurs rattachés (pour l'utilisateur courant)
            foreach ($_SESSION['ploopi']['workspaces'] as $grp) // pour chaque groupe de travail
            {
                if (isset($grp['adminlevel']) && $grp['admin'])
                {
                    $list['work'][$grp['id']]['label'] = $grp['label'];
                    $list['work'][$grp['id']]['org'] = array();
                    $list['work'][$grp['id']]['users'] = array();
                    $workspace->fields['id'] = $grp['id'];
                    foreach ($workspace->getgroups() as $orgrp)
                    {
                        $list['work'][$grp['id']]['org'][] = $orgrp['id'];
                        $list['org'][$orgrp['id']]['label'] = $orgrp['label'];
                    }
                }
            }

            // recherche des utilisateurs attachés aux espaces précédemment sélectionnés
            $query_workspaces =     "
                            SELECT      u.*,
                                        wu.id_workspace


                            FROM        ploopi_user u,
                                        ploopi_workspace w,
                                        ploopi_workspace_user wu

                            WHERE       u.id = wu.id_user
                            AND         w.id = wu.id_workspace
                            AND         wu.id_workspace IN (".implode(',',array_keys($list['work'])).")
                            AND         u.login LIKE '%".$db->addslashes($_GET['ploopi_ticket_userfilter'])."%'

                            ";


            $db->query($query_workspaces);

            if (!$db->numrows())
            {
                ?>
                <div class="system_tickets_select_empty">
                    <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png"><span>aucun espace de travail trouvé</span></p>
                </div>
                <?
            }
            else
            {
                // affectation des utilisateurs à leurs groupes de rattachement
                while ($fields = $db->fetchrow())
                {
                    $list['work'][$fields['id_workspace']]['users'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                }

                //ploopi_print_r($grouplist);

                // pour chaque espace de travail
            foreach($list['work'] as $id_workgrp => $workgrp)
            {

                    ?>
                    <div class="system_tickets_select_workgroup">
                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><? echo $workgrp['label']; ?></span></p>
                    </div>
                    <?
                    if (!empty($workgrp['users']))
                    {
                        foreach($workgrp['users'] as $id_user => $user)
                        {
                            ?>
                            <a class="system_tickets_select_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=tickets_select_user&user_id=<? echo $id_user; ?>','','div_ticket_users_selected');">
                                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
                            </a>
                            <?
                        }
                    }

                    $query_groups =     "

                            SELECT      u.*,
                                        wg.id_group


                            FROM        ploopi_user u,
                                        ploopi_group g,
                                        ploopi_group_user gu,
                                        ploopi_workspace w,
                                        ploopi_workspace_group wg

                            WHERE       u.id = gu.id_user
                            AND         w.id = ".$id_workgrp."
                            AND         wg.id_workspace = w.id
                            AND         g.id = wg.id_group
                            AND         g.id = gu.id_group
                            AND         gu.id_group = wg.id_group
                            AND         u.login LIKE '%".$db->addslashes($_GET['ploopi_ticket_userfilter'])."%'
                            ";


                    $db->query($query_groups);
                    $listgroup = array();
                    while ($fields = $db->fetchrow())
                    {
                        $listgroup[$fields['id_group']]['users'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                    }

                    foreach($listgroup as $id_orggrp => $group)
                    {
                        if (!empty($group['users']))
                        {
                            ?>
                            <div class="system_tickets_select_usergroup">
                                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><? echo $list['org'][$id_orggrp]['label']; ?></span></p>
                            </div>
                            <?
                            foreach($group['users'] as $id_user => $user)
                            {
                                ?>
                                <a class="system_tickets_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=tickets_select_user&user_id=<? echo $id_user; ?>','','div_ticket_users_selected');">
                                    <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
                                </a>
                                <?
                            }
                        }
                    }
                }

                ?>
                <div class="system_tickets_select_legend">
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

        case 'tickets_select_user':
            if (!$_SESSION['ploopi']['connected']) ploopi_die();

            if (isset($_GET['user_id'])) $_SESSION['ploopi']['tickets']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
            if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['tickets']['users_selected'][$_GET['remove_user_id']]);


            foreach($_SESSION['ploopi']['tickets']['users_selected'] as $user_id)
            {
                include_once('./modules/system/class_user.php');

                $user = new user();
                $user->open($user_id);

                $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="system_tickets_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=tickets_select_user&remove_user_id=<? echo $user->fields['id']; ?>','','div_ticket_users_selected');">
                        <img src="./img/icon_delete.gif">
                        <span><? echo "{$user->fields['firstname']} {$user->fields['lastname']} ({$user->fields['login']})"; ?></span>
                    </a>
                </p>
                <?
            }
            ploopi_die();
        break;


        case 'tickets_open':
            include_once('./modules/system/class_ticket.php');
            $ticket = new ticket();

            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id']))
            {
                include_once('./modules/system/class_ticket_watch.php');
                include_once('./modules/system/class_ticket_status.php');

                $ticket_status = new ticket_status();

                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_OPENED))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_OPENED;
                    $ticket_status->save();
                }

                $ticket_watch = new ticket_watch();
                $ticket_watch->open($_GET['ticket_id'], $_SESSION['ploopi']['userid']);
                $ticket_watch->fields['id_ticket'] = $_GET['ticket_id'];
                $ticket_watch->fields['id_user'] = $_SESSION['ploopi']['userid'];
                $ticket_watch->fields['notify'] = 0;
                $ticket_watch->save();

                $ticket->fields['count_read']++;
                $ticket->save();
            }
            ploopi_die();
        break;


        case 'tickets_open_responses':
            if (isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                $rootid = $db->addslashes($_GET['ticket_id']);

                $sql =  "
                        SELECT      t.id,
                                    t.title,
                                    t.message,
                                    t.timestp,
                                    t.lastedit_timestp,
                                    t.id_module,
                                    t.parent_id,
                                    t.root_id,
                                    t.id_user as sender_uid,
                                    ts.status,
                                    u.login,
                                    u.firstname,
                                    u.lastname

                        FROM        ploopi_ticket t

                        INNER JOIN  ploopi_user u
                        ON          t.id_user = u.id

                        LEFT JOIN   ploopi_ticket_status ts
                        ON          ts.id_ticket = t.id
                        AND         ts.id_user = {$_SESSION['ploopi']['userid']}

                        WHERE       t.root_id = {$rootid}
                        AND         t.id <> {$rootid}

                        ORDER BY    t.timestp DESC
                        ";

                $tickets = array();
                $parents = array();

                $rs = $db->query($sql);

                while ($fields = $db->fetchrow($rs))
                {
                    if (!isset($tickets[$fields['id']]))
                    {
                        $tickets[$fields['id']] = $fields;
                        $parents[$fields['parent_id']][] = $fields['id'];
                    }

                }

                if (!empty($tickets)) system_tickets_displayresponses($parents, $tickets, $_GET['ticket_id']);
            }
            ploopi_die();
        break;

        case 'tickets_validate':
            include_once('./modules/system/class_ticket_status.php');
            $ticket_status = new ticket_status();

            if (!empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']))
            {
                if (!$ticket_status->open($_GET['ticket_id'], $_SESSION['ploopi']['userid'], _PLOOPI_TICKETS_DONE))
                {
                    $ticket_status->fields['id_ticket'] = $_GET['ticket_id'];
                    $ticket_status->fields['id_user'] = $_SESSION['ploopi']['userid'];
                    $ticket_status->fields['status'] = _PLOOPI_TICKETS_DONE;
                    $ticket_status->save();
                }
            }
            ploopi_redirect("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets");
        break;



        /*
         * SEARCH
         *
         * */

        case 'system_search':
            include_once('./modules/system/public_search_result.php');
            ploopi_die();
        break;


        default:
            if (ploopi_ismanager())
            {
                switch($ploopi_op)
                {
                    case 'system_roleusers':
                        if (empty($_GET['system_roleusers_roleid'])) ploopi_die();

                        $sql =  "
                                SELECT      u.*
                                FROM        ploopi_workspace_user_role wur
                                INNER JOIN  ploopi_user u
                                ON          u.id = wur.id_user
                                WHERE       wur.id_role = {$_GET['system_roleusers_roleid']}
                                AND         wur.id_workspace = {$_SESSION['system']['workspaceid']}
                                ";

                        $db->query($sql);
                        $users = $db->getarray();

                        $sql =  "
                                SELECT      g.*
                                FROM        ploopi_workspace_group_role wgr
                                INNER JOIN  ploopi_group g
                                ON          g.id = wgr.id_group
                                WHERE       wgr.id_role = {$_GET['system_roleusers_roleid']}
                                AND         wgr.id_workspace = {$_SESSION['system']['workspaceid']}
                                ";

                        $db->query($sql);
                        $groups = $db->getarray();

                        if (empty($groups) && empty($users))
                        {
                            ?>
                            <div class="system_roleusers_title">Aucun utilisateur ou groupe affecté à ce rôle, utilisez la recherche pour en ajouter</div>
                            <?
                        }

                        if (!empty($groups))
                        {
                            ?>
                            <div class="system_roleusers_title">Groupes affectés à ce rôle:</div>
                            <?
                        }

                        foreach($groups as $group)
                        {
                            ?>
                            <a class="system_roleusers_select" href="javascript:void(0);" onclick="javascript:system_roleusers_delete(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $group['id']; ?>, 'group');">
                                <p class="ploopi_va">
                                    <img src="./img/icon_delete.gif">
                                    <span><? echo "{$group['label']}"; ?></span>
                                </p>
                            </a>
                            <?
                        }

                        if (!empty($users))
                        {
                            ?>
                            <div class="system_roleusers_title">Utilisateurs affectés à ce rôle:</div>
                            <?
                        }

                        foreach($users as $user)
                        {
                            ?>
                            <a class="system_roleusers_select" href="javascript:void(0);" onclick="javascript:system_roleusers_delete(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $user['id']; ?>, 'user');">
                                <p class="ploopi_va">
                                    <img src="./img/icon_delete.gif">
                                    <span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span>
                                </p>
                            </a>
                            <?
                        }

                        ploopi_die();
                    break;

                    // suppression de l'affectation d'un rôle à un utilisateur
                    case 'system_roleusers_delete_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_user_role.php';

                        $wur = new workspace_user_role();

                        if ($wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wur->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // suppression de l'affectation d'un rôle à un groupe
                    case 'system_roleusers_delete_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_group_role.php';

                        $wgr = new workspace_group_role();

                        if ($wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wgr->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un utilisateur
                    case 'system_roleusers_select_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_user_role.php';

                        $wur = new workspace_user_role();

                        if (!$wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wur->fields['id_user'] = $_GET['system_roleusers_userid'];
                            $wur->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wur->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wur->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // affectation d'un rôle à un groupe
                    case 'system_roleusers_select_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_group_role.php';

                        $wgr = new workspace_group_role();

                        if (!$wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid']))
                        {
                            $wgr->fields['id_group'] = $_GET['system_roleusers_groupid'];
                            $wgr->fields['id_workspace'] = $_SESSION['system']['workspaceid'];
                            $wgr->fields['id_role'] = $_GET['system_roleusers_roleid'];
                            $wgr->save();
                        }

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                    break;

   // résultat de la recherche utilisateurs / groupes
                    case 'system_roleusers_search':
                        if (!isset($_GET['system_roleusers_filter'])) ploopi_die();
                        
                        $cleanedfilter = $db->addslashes($_GET['system_roleusers_filter']);
                        $userfilter = "(u.login LIKE '%{$cleanedfilter}%' OR u.firstname LIKE '%{$cleanedfilter}%' OR u.lastname LIKE '%{$cleanedfilter}%')";
                        
                        $sql =  "
                                SELECT      u.id,
                                            u.lastname,
                                            u.firstname,
                                            u.login,
                                            u.service

                                FROM        ploopi_user u

                                INNER JOIN  ploopi_workspace_user wu
                                ON          wu.id_user = u.id
                                AND         wu.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$userfilter}

                                ORDER BY    u.lastname, u.firstname
                                ";

                        $db->query($sql);
                        $users = $db->getarray();

                        $groupfilter = "g.label LIKE '%{$cleanedfilter}%'";
                        
                        $sql =  "
                                SELECT      g.id,
                                            g.label,
                                            g.parents

                                FROM        ploopi_group g

                                INNER JOIN  ploopi_workspace_group wg
                                ON          wg.id_group = g.id
                                AND         wg.id_workspace = {$_SESSION['system']['workspaceid']}
                                WHERE       {$groupfilter}
                                
                                ORDER BY    g.label
                                ";

                        $db->query($sql);
                        $groups = $db->getarray();

                        if (empty($users) && empty($groups))
                        {
                            ?>
                            <div class="system_roleusers_select_empty">
                                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png"><span>aucun utilisateur/groupe trouv&eacute;</span></p>
                            </div>
                            <?
                        }
                        else
                        {
                            ?>
                            <div style="height:200px;overflow:auto;border-bottom:1px solid #c0c0c0;">
                                <div style="overflow:hidden">
                                <?
                                // pour chaque groupe
                                foreach($groups as $group)
                                {
                                    ?>
                                    <a class="system_roleusers_select" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $group['id']; ?>, 'group');">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><? echo "{$group['label']}"; ?></span></p>
                                    </a>
                                    <?
                                }
                                ?>
                                <?
                                // pour chaque utilisateur
                                foreach($users as $user)
                                {
                                    ?>
                                    <a class="system_roleusers_select" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $user['id']; ?>, 'user');">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
                                    </a>
                                    <?
                                }
                                ?>
                                </div>
                            </div>
                            <div class="system_roleusers_legend">
                                <p class="ploopi_va">
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
                                </p>
                            </div>
                            <?
                        }

                        ploopi_die();
                    break;
                    
                    case 'system_serverload':
                        include './modules/system/tools_serverload.php';
                        ploopi_die();
                    break;
                    
                }
            }
        break;
    }
}
?>
