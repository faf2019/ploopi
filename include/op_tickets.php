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

switch($ploopi_op)
{
    case 'tickets_getnum':
        include_once './include/functions/tickets.php';
        list($newtickets, $lastticket) = ploopi_tickets_getnew();
        echo "{$newtickets},{$lastticket}";
        ploopi_die();
    break;
    
    case 'tickets_alert':
        ob_start();
        ?>
        <p class="ploopi_va" style="padding:4px;">
            <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/newmail.png">
            <a href="<? echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets"); ?>"><b>Vous avez reçu un nouveau ticket !</b></a>
        </p>
        <?
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup('Nouveau Ticket !', $content, 'popup_tickets_new_alert');
        ploopi_die();
    break;
        
    case 'tickets_new':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();
        
        ob_start();
        ?>
        <script type="text/javascript">
            ploopi_tickets_validate = function (form)
            {
                return (ploopi_validatefield('Titre',form.ticket_title,'string'));
            };
        </script>
        
        <div id="tickets_new">
            <form method="post" action="admin.php" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);};">
            <input type="hidden" name="ploopi_op" value="tickets_send">
            <input type="hidden" name="ploopi_tickets_reload" value="<? if (!empty($_GET['ploopi_tickets_reload'])) echo $_GET['ploopi_tickets_reload']; ?>">
            <?
            if (isset($_GET['ploopi_tickets_id_object']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_object" value="<? echo htmlentities($_GET['ploopi_tickets_id_object']); ?>">
                <?
            }
            if (isset($_GET['ploopi_tickets_id_record']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_record" value="<? echo htmlentities($_GET['ploopi_tickets_id_record']); ?>">
                <?
            }
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_object_label" value="<? echo htmlentities($_GET['ploopi_tickets_object_label']); ?>">
                <?
            }
            ?>
            <div style="font-weight:bold;"><? echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
            <div><input type="text" name="ticket_title" class="text" value="" style="width:98%"></div>
            <div style="font-weight:bold;"><? echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
            <div>
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
            </div>
            
            <?
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <div><b><? echo _PLOOPI_LABEL_TICKET_LINKEDOBJECT; ?></b>: <? echo htmlentities($_GET['ploopi_tickets_object_label']); ?></div>
                <?
            }
            ?>
            <p class="ploopi_va" style="padding:4px 0; cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'ticket_needed_validation');">
                <input type="checkbox" name="ticket_needed_validation" id="ticket_needed_validation" style="cursor:pointer;" value="1"><span><? echo _PLOOPI_LABEL_TICKET_VALIDATIONREQUIRED; ?></span>
            </p>

            <div style="padding:8px 4px;margin:4px 0;background-color:#f0f0f0;border:1px solid #c0c0c0;">
                <? 
                ploopi_tickets_selectusers((empty($_GET['ploopi_tickets_id_user'])) ? null : $_GET['ploopi_tickets_id_user']);
                ?>
            </div>
            <div style="text-align:right;">
                    <input type="submit" class="flatbutton" value="Envoyer" style="font-weight:bold;">
                    <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
            </div>
            </form>
            <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
        </div>
        <?
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup(_PLOOPI_LABEL_NEWTICKET, $content, 'system_popupticket');
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
                include_once './modules/system/class_user.php';
    
                $objUser = new user();
                $strUserName = ($objUser->open($ticket->fields['id_user'])) ? "{$objUser->fields['lastname']} {$objUser->fields['firstname']}" : _PLOOPI_LABEL_TICKET_UNKNOWN_USER;
                
                $ticket->fields['title'] = "RE: {$ticket->fields['title']}";
                $nextop = 'tickets_send';
                $button_value = 'Envoyer';
                $popup_title = _PLOOPI_LABEL_TICKET_RESPONSE;
                
                if (isset($_GET['quoted'])) $ticket->fields['message'] = '<div class="system_tickets_quoted_user">Ticket de <b>'.$strUserName.'</b> :</div><div class="system_tickets_quoted_message">'.$ticket->fields['message'].'</div>';
                else $ticket->fields['message'] = '';
            }
            else
            {
                $nextop = 'tickets_modify_next';
                $button_value = 'Modifier';
                $popup_title = _PLOOPI_LABEL_TICKET_MODIFICATION;
            }

            ?>
            <script type="text/javascript">
                ploopi_tickets_validate = function (form)
                {
                    return (ploopi_validatefield('Titre',form.ticket_title,'string'));
                };
            </script>
            <div id="tickets_new">
                <form method="post" action="admin.php" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);};">
                <input type="hidden" name="ploopi_op" value="<? echo $nextop; ?>">
                <input type="hidden" name="ticket_id" value="<? echo $_GET['ticket_id']; ?>">
                <input type="hidden" name="ploopi_tickets_reload" value="1">
                <?
                if ($ploopi_op == 'tickets_replyto')
                {
                    ?>
                    <div><span><? echo _PLOOPI_LABEL_TICKET_RECIPIENT; ?> : </span><span style="font-weight:bold;"><? echo $strUserName; ?></span></div>
                    <?
                }
                ?>

                <div style="font-weight:bold;"><? echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
                <div><input type="text" name="ticket_title" class="text" value="<? echo htmlentities($ticket->fields['title']); ?>" style="width:98%"></div>
                <div style="font-weight:bold;"><? echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
                <div>
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
                </div>
                <div style="text-align:right;">
                        <input type="submit" class="flatbutton" value="<? echo $button_value; ?>" style="font-weight:bold;">
                        <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
                </div>
                </form>
                <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
            </div>
            <?
        }
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup($popup_title, $content, 'system_popupticket');
        ploopi_die();
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
                if (!empty($_POST['ploopi_tickets_id_object']) && !empty($_POST['ploopi_tickets_id_record']) && !empty($_POST['ploopi_tickets_object_label']))
                {
                    ploopi_tickets_send($_POST['ticket_title'], $_POST['fck_ticket_message'], isset($_POST['ticket_needed_validation']) ? $_POST['ticket_needed_validation'] : 0, isset($_POST['ticket_delivery_notification']) ? $_POST['ticket_delivery_notification'] : 0, $_POST['ploopi_tickets_id_object'], $_POST['ploopi_tickets_id_record'], $_POST['ploopi_tickets_object_label']);
                }
                else
                {
                    ploopi_tickets_send($_POST['ticket_title'], $_POST['fck_ticket_message'], isset($_POST['ticket_needed_validation']) ? $_POST['ticket_needed_validation'] : 0, isset($_POST['ticket_delivery_notification']) ? $_POST['ticket_delivery_notification'] : 0);
                }
            }
        }

        if (!empty($_POST['ploopi_tickets_reload']) && $_POST['ploopi_tickets_reload'])
        {
            ?>
            <script type="text/javascript">
                window.parent.location.href = '<? echo ploopi_urlencode('admin.php?ploopi_mainmenu='._PLOOPI_MENU_MYWORKSPACE.'&op=tickets'); ?>';
            </script>
            <?
        }
        else
        {
            ?>
            <script type="text/javascript">
                window.parent.ploopi_hidepopup('system_popupticket');
            </script>
            <?
        }
        
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



    case 'tickets_select_user':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (isset($_GET['user_id'])) $_SESSION['ploopi']['tickets']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
        if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['tickets']['users_selected'][$_GET['remove_user_id']]);

        ploopi_tickets_displayusers();
        

        ploopi_die();
    break;

        
    case 'tickets_search_users':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();
        ?>
        <div style="height:150px;overflow-y:auto;overflow-x:hidden;border:1px solid #c0c0c0;">
        <?
            $listgroup = array();
            include_once './modules/system/class_group.php';
            include_once './modules/system/class_workspace.php';
            $group = new group();
            $workspace = new workspace();
            $list = array();
            $list['work'] = array();
            $list['org'] = array();
            
            $filtered_search_field = $db->addslashes($_GET['ploopi_ticket_userfilter']);
            $search_pattern = "AND (u.login LIKE '%{$filtered_search_field}%' OR u.lastname LIKE '%{$filtered_search_field}%' OR u.firstname LIKE '%{$filtered_search_field}%') ";

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
            $query_workspaces = "
                                SELECT      u.*,
                                            wu.id_workspace
    
    
                                FROM        ploopi_user u,
                                            ploopi_workspace w,
                                            ploopi_workspace_user wu
    
                                WHERE       u.id = wu.id_user
                                AND         w.id = wu.id_workspace
                                AND         wu.id_workspace IN (".implode(',',array_keys($list['work'])).")
                                {$search_pattern}
                                ORDER BY    u.lastname, u.firstname, u.login
                                ";


            $db->query($query_workspaces);


            // affectation des utilisateurs à leurs groupes de rattachement
            while ($fields = $db->fetchrow())
            {
                $list['work'][$fields['id_workspace']]['users'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
            }

    
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
                            <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                        </a>
                        <?
                    }
                }
    
                $query_groups = "
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
                                {$search_pattern}
                                ORDER BY    u.lastname, u.firstname, u.login
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
                                <p class="ploopi_va"><img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><? echo "{$user['lastname']} {$user['firstname']}"; ?></span></p>
                            </a>
                            <?
                        }
                    }
                }
            }
            ?>
        </div>
        <div class="system_tickets_select_legend">
            <p class="ploopi_va">
                <span style="font-weight:bold;">Légende:</span>
                <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span>Espace de Travail</span>
                <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                <img src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
            </p>
        </div>
        <?
        ploopi_die();
    break;
    
}
?>