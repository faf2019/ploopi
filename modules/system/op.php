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
                        $roleid = $_GET['system_roleusers_roleid'];
                        include './modules/system/admin_index_roles_assignment_list.php';
                        ploopi_die();
                    break;
                                            
                    // suppression de l'affectation d'un rôle à un utilisateur
                    case 'system_roleusers_delete_user':
                        if (empty($_GET['system_roleusers_userid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_user_role.php';

                        $wur = new workspace_user_role();

                        if ($wur->open($_GET['system_roleusers_userid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wur->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
                    break;

                    // suppression de l'affectation d'un rôle à un groupe
                    case 'system_roleusers_delete_group':
                        if (empty($_GET['system_roleusers_groupid']) || empty($_GET['system_roleusers_roleid']) || empty($_SESSION['system']['workspaceid'])) ploopi_die();

                        include_once './modules/system/class_workspace_group_role.php';

                        $wgr = new workspace_group_role();

                        if ($wgr->open($_GET['system_roleusers_groupid'], $_SESSION['system']['workspaceid'], $_GET['system_roleusers_roleid'])) $wgr->delete();

                        ploopi_redirect("admin-light.php?ploopi_op=system_roleusers&system_roleusers_roleid={$_GET['system_roleusers_roleid']}");
                        //ploopi_redirect("admin.php?op=assign_role&roleid={$_GET['system_roleusers_roleid']}");
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
                            <p class="ploopi_va" style="padding:4px;font-weight:bold;border-bottom:1px solid #c0c0c0;">
                                <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_noway.png">
                                <span>aucun utilisateur/groupe trouv&eacute;</span>
                            </p>
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
                                    <a class="system_roleusers_select" title="Sélectionner ce groupe et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $group['id']; ?>, 'group');">
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
                                    <a class="system_roleusers_select" title="Sélectionner cet utilisateur et lui attribuer ce rôle" href="javascript:void(0);" onclick="javascript:system_roleusers_select(<? echo $_GET['system_roleusers_roleid']; ?>, <? echo $user['id']; ?>, 'user');">
                                        <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['firstname']} {$user['lastname']} ({$user['login']})"; ?></span></p>
                                    </a>
                                    <?
                                }
                                ?>
                                </div>
                            </div>
                            <div class="system_roleusers_legend">
                                <p class="ploopi_va" style="float:right;">
                                    <span style="font-weight:bold;">Légende:</span>
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                                    <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
                                </p>
                                <p class="ploopi_va" style="float:left;">
                                    <span>Cliquez sur un utilisateur ou un groupe pour l'ajouter</span>
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
