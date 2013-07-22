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
 * Opérations sur les tickets (messages)
 *
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
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
            <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/newmail.png">
            <a href="<?php echo ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets"); ?>"><b>Vous avez reçu un nouveau message !</b></a>
        </p>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup('Nouveau Message !', $content, 'popup_tickets_new_alert');
        ploopi_die();
    break;

    case 'tickets_new':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        ob_start();
        ?>
        <script type="text/javascript">
            ploopi_tickets_validate = function (form)
            {
            	if(ploopi_validatefield('Titre',form.ticket_title,'string'))
            	if(ploopi_ticket_validateTo('Destinataire',form.system_ticket_ctrl_user_to))
                    return true;
                return false;
            }
        </script>

        <div id="tickets_new">
            <form method="post" action="<?php echo ploopi_urlencode('admin.php'); ?>" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);">
            <input type="hidden" name="ploopi_op" value="tickets_send">
            <input type="hidden" name="ploopi_tickets_reload" value="<?php if (!empty($_GET['ploopi_tickets_reload'])) echo $_GET['ploopi_tickets_reload']; ?>">
            <?php
            if (isset($_GET['ploopi_tickets_id_object']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_object" value="<?php echo ploopi_htmlentities($_GET['ploopi_tickets_id_object']); ?>">
                <?php
            }
            if (isset($_GET['ploopi_tickets_id_record']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_record" value="<?php echo ploopi_htmlentities($_GET['ploopi_tickets_id_record']); ?>">
                <?php
            }
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_object_label" value="<?php echo ploopi_htmlentities($_GET['ploopi_tickets_object_label']); ?>">
                <?php
            }
            ?>
            <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
            <div><input type="text" name="ticket_title" class="text" value="" style="width:98%"></div>
            <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
            <div>
                <?php
                include_once './include/functions/fck.php';
                
                $arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/modules/system/fckeditor/fckconfig.js';
                $arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.'/modules/system/fckeditor/fck_editorarea.css';
                
                ploopi_fckeditor('fck_ticket_message', '', '100%', '200', $arrConfig);
                ?>
            </div>

            <?php
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <div><b><?php echo _PLOOPI_LABEL_TICKET_LINKEDOBJECT; ?></b>: <?php echo ploopi_htmlentities($_GET['ploopi_tickets_object_label']); ?></div>
                <?php
            }
            ?>
            <?php
            /*
            <p class="ploopi_va" style="padding:4px 0; cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'ticket_needed_validation');">
                <input type="checkbox" name="ticket_needed_validation" id="ticket_needed_validation" style="cursor:pointer;" value="1"><span><?php echo _PLOOPI_LABEL_TICKET_VALIDATIONREQUIRED; ?></span>
            </p>
            */
            ?>

            <div style="padding:8px 4px;margin:4px 0;background-color:#f0f0f0;border:1px solid #c0c0c0;">
                <?php
                ploopi_tickets_selectusers((empty($_GET['ploopi_tickets_id_user'])) ? null : $_GET['ploopi_tickets_id_user']);
                ?>
            </div>
            <div style="text-align:right;">
                    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
                    <input type="submit" class="flatbutton" value="Envoyer" style="font-weight:bold;">
            </div>
            </form>
            <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup(_PLOOPI_LABEL_NEWTICKET, $content, 'system_popupticket');
        ploopi_die();
    break;

    case 'tickets_replyto':
    case 'tickets_modify':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        ob_start();

        include_once './include/classes/ticket.php';
        $ticket = new ticket();

        if (!empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id']))
        {
            if ($ploopi_op == 'tickets_replyto')
            {
                include_once './include/classes/user.php';

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
                }
            </script>
            <div id="tickets_new">
                <form method="post" action="<?php echo ploopi_urlencode('admin.php'); ?>" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);">
                <input type="hidden" name="ploopi_op" value="<?php echo $nextop; ?>">
                <input type="hidden" name="ticket_id" value="<?php echo $_GET['ticket_id']; ?>">
                <input type="hidden" name="ploopi_tickets_reload" value="1">
                <?php
                if ($ploopi_op == 'tickets_replyto')
                {
                    ?>
                    <div><span><?php echo _PLOOPI_LABEL_TICKET_RECIPIENT; ?> : </span><span style="font-weight:bold;"><?php echo $strUserName; ?></span></div>
                    <?php
                }
                ?>

                <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
                <div><input type="text" name="ticket_title" class="text" value="<?php echo ploopi_htmlentities($ticket->fields['title']); ?>" style="width:98%"></div>
                <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
                <div>
                    <?php
                    include_once './include/functions/fck.php';

                    $arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/modules/system/fckeditor/fckconfig.js';
                    $arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.'/modules/system/fckeditor/fck_editorarea.css';
                    
                    ploopi_fckeditor('fck_ticket_message', $ticket->fields['message'], '100%', '200', $arrConfig);
                    ?>
                </div>
                <div style="text-align:right;">
                        <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi_hidepopup('system_popupticket');">
                        <input type="submit" class="flatbutton" value="<?php echo $button_value; ?>" style="font-weight:bold;">
                </div>
                </form>
                <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
            </div>
            <?php
        }
        $content = ob_get_contents();
        ob_end_clean();
        echo $skin->create_popup($popup_title, $content, 'system_popupticket');
        ploopi_die();
    break;

    case 'tickets_send':
        include_once('./include/classes/ticket.php');
        $ticket = new ticket();
        if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']))
        {
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
            $response->fields['message'] = ploopi_htmlpurifier($_POST['fck_ticket_message']);
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
                window.parent.location.href = '<?php echo ploopi_urlencode('admin.php?ploopi_mainmenu='._PLOOPI_MENU_MYWORKSPACE.'&op=tickets'); ?>';
            </script>
            <?php
        }
        else
        {
            ?>
            <script type="text/javascript">
                window.parent.ploopi_hidepopup('system_popupticket');
            </script>
            <?php
        }

        ploopi_die();
    break;

    case 'tickets_modify_next':
        include_once('./include/classes/ticket.php');
        $ticket = new ticket();
        if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']))
        {
            $_POST['ticket_message'] = $_POST['fck_ticket_message'];
            unset($_POST['fck_ticket_message']);
            $ticket->fields['lastedit_timestp'] = ploopi_createtimestamp();
            $ticket->setvalues($_POST, 'ticket_');
            $ticket->save();
        }
        ?>
        <script type="text/javascript">
            window.parent.location.href = '<?php echo ploopi_urlencode('admin.php?ploopi_mainmenu='._PLOOPI_MENU_MYWORKSPACE.'&op=tickets'); ?>';
        </script>
        <?
        ploopi_die();
    break;

    case 'tickets_select_user':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();

        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) $_SESSION['ploopi']['tickets']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
        if (isset($_GET['group_id']) && is_numeric($_GET['group_id'])) 
        {
            $objGroup = new group();
            $objGroup->fields['id'] = $_GET['group_id'];
            $arrUsers = $objGroup->getusers();
            
            foreach($objGroup->getusers() as $user_id => $arrUser) $_SESSION['ploopi']['tickets']['users_selected'][$user_id] = $user_id;
        }
        if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['tickets']['users_selected'][$_GET['remove_user_id']]);

        ploopi_tickets_displayusers();

        ploopi_die();
    break;

    case 'tickets_search_users':
        if (!$_SESSION['ploopi']['connected']) ploopi_die();
        if (!isset($_POST['ploopi_ticket_userfilter']) && !isset($_POST['ploopi_ticket_typefilter'])) ploopi_die();
        
        ?>
        <div style="height:150px;overflow-y:auto;overflow-x:hidden;border:1px solid #c0c0c0;">
        <?php
            $listgroup = array();
            include_once './include/classes/group.php';
            include_once './include/classes/workspace.php';

            $group = new group();
            $workspace = new workspace();

            // liste pour les espaces, groupes, utilisateurs
            $list = array();
            $list['wsp'] = array();
            $list['grp'] = array();
            $list['usr'] = array();
            
            $booEmptySearch = true;
            
            // Filtre utilisateur
            $filtered_search_field = $db->addslashes($_POST['ploopi_ticket_userfilter']);
            $search_pattern_user = ($_POST['ploopi_ticket_typefilter'] == 'user') ? "AND (u.login LIKE '%{$filtered_search_field}%' OR u.lastname LIKE '%{$filtered_search_field}%' OR u.firstname LIKE '%{$filtered_search_field}%') " : '';

            // construction de la liste des groupes de travail et des groupes d'utilisateurs rattachés (pour l'utilisateur courant)
            foreach ($_SESSION['ploopi']['workspaces'] as $wsp) // pour chaque groupe de travail
            {
                if (isset($wsp['adminlevel']) && $wsp['backoffice'])
                {
                    if ($_POST['ploopi_ticket_typefilter'] != 'workspace' || $_POST['ploopi_ticket_userfilter'] == '' || ($_POST['ploopi_ticket_typefilter'] == 'workspace' && stristr(ploopi_convertaccents($wsp['label']), ploopi_convertaccents($_POST['ploopi_ticket_userfilter'])) !== false))
                    {
                        $list['wsp'][$wsp['id']]['label'] = $wsp['label'];
                        $list['wsp'][$wsp['id']]['empty'] = true;
                        $list['wsp'][$wsp['id']]['groups'] = array();
                        $list['wsp'][$wsp['id']]['users'] = array();
                        $workspace->fields['id'] = $wsp['id'];
                        foreach ($workspace->getgroups(true) as $grp)
                        {
                            if ($_POST['ploopi_ticket_typefilter'] != 'group' || ploopi_convertaccents($_POST['ploopi_ticket_userfilter']) == '' || ($_POST['ploopi_ticket_typefilter'] == 'group' && stristr(ploopi_convertaccents($grp['label']), ploopi_convertaccents($_POST['ploopi_ticket_userfilter'])) !== false))
                            {
                                $list['wsp'][$wsp['id']]['groups'][$grp['id']] = $grp['id'];
                                if (!isset($list['grp'][$grp['id']])) $list['grp'][$grp['id']]['label'] = $grp['label'];
                            }
                        }
                    }
                }
            }

            
            if (!empty($list['wsp']))
            {
                if ($_POST['ploopi_ticket_typefilter'] != 'group')
                {
                    // recherche des utilisateurs attachés aux espaces précédemment sélectionnés
                    $db->query("
                        SELECT      u.*,
                                    wu.id_workspace
    
                        FROM        ploopi_user u,
                                    ploopi_workspace w,
                                    ploopi_workspace_user wu
    
                        WHERE       u.id = wu.id_user
                        AND         w.id = wu.id_workspace
                        AND         wu.id_workspace IN (".implode(',',array_keys($list['wsp'])).")
                        {$search_pattern_user}
                        ORDER BY    u.lastname, u.firstname, u.login
                    ");
        
                    // affectation des utilisateurs à leurs groupes de rattachement
                    while ($fields = $db->fetchrow())
                    {
                        // Si l'espace contient au moins 1 utilisateur, il n'est pas vide
                        if ($list['wsp'][$fields['id_workspace']]['empty']) $list['wsp'][$fields['id_workspace']]['empty'] = false;
                        
                        $list['wsp'][$fields['id_workspace']]['users'][] = $fields['id'];
                        if (!isset($list['usr'][$fields['id']])) $list['usr'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                    }
                }

                if (!empty($list['grp']))
                {
                    $db->query("
                        SELECT      u.*,
                                    gu.id_group
    
                        FROM        ploopi_user u,
                                    ploopi_group g,
                                    ploopi_group_user gu
    
                        WHERE       u.id = gu.id_user
                        AND         g.id = gu.id_group
                        AND         gu.id_group IN (".implode(',',array_keys($list['grp'])).")
                        {$search_pattern_user}
                        ORDER BY    u.lastname, u.firstname, u.login
                    ");
                        
                    $listgroup = array();
                    while ($fields = $db->fetchrow())
                    {
                        $list['grp'][$fields['id_group']]['users'][] = $fields['id'];
                        if (!isset($list['usr'][$fields['id']])) $list['usr'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                    }
                }
                            
                if (!empty($list['usr']))
                {
                    // On vérifie que chaque espace contient bien qqchose
                    foreach($list['wsp'] as $id_wsp => $wsp)
                    {
                        // On ne teste que les espaces encore vides (ceux qui n'ont pas encore au moins 1 utilisateur)
                        if ($wsp['empty'])
                            foreach($wsp['groups'] as $id_grp) 
                                if ($list['wsp'][$id_wsp]['empty'] && !empty($list['grp'][$id_grp]['users'])) 
                                    $list['wsp'][$id_wsp]['empty'] = false;
                    }
                    
                    foreach($list['wsp'] as $id_wsp => $wsp)
                    {
                        if (!$wsp['empty'])
                        {
                            ?>
                            <div class="system_tickets_select_workgroup">
                                <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><?php echo $wsp['label']; ?></span></p>
                            </div>
                            <?php
                            if (!empty($wsp['users']))
                            {
                                foreach($wsp['users'] as $id_user)
                                {
                                    if ($booEmptySearch) $booEmptySearch = false;
                                    
                                    ?>
                                    <a class="system_tickets_select_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&user_id=<?php echo $id_user; ?>', 'div_ticket_users_selected');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo "{$list['usr'][$id_user]['lastname']} {$list['usr'][$id_user]['firstname']}"; ?></span></p>
                                    </a>
                                    <?php
                                }
                            }
            
                            if (!empty($wsp['groups']))
                            {
                                foreach($wsp['groups'] as $id_grp)
                                {
                                    if (!empty($list['grp'][$id_grp]['users']))
                                    {
                                        ?>
                                        <a class="system_tickets_select_usergroup" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&group_id=<?php echo $id_grp; ?>', 'div_ticket_users_selected');">
                                            <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><?php echo $list['grp'][$id_grp]['label']; ?></span></p>
                                        </a>
                                        <?php
                                        foreach($list['grp'][$id_grp]['users'] as $id_user)
                                        {
                                            if ($booEmptySearch) $booEmptySearch = false;
                                            ?>
                                            <a class="system_tickets_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&user_id=<?php echo $id_user; ?>', 'div_ticket_users_selected');">
                                                <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo "{$list['usr'][$id_user]['lastname']} {$list['usr'][$id_user]['firstname']}"; ?></span></p>
                                            </a>
                                            <?php
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            if ($booEmptySearch)
            {
                ?>
                <div style="padding:4px;" class="error">
                    Aucune réponse
                </div>
                <?php
            }
            ploopi_die();
            
            ?>
        </div>
        <div class="system_tickets_select_legend">
            <p class="ploopi_va">
                <span style="font-weight:bold;">Légende:</span>
                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span>Espace de Travail</span>
                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span>Groupe d'Utilisateur</span>
                <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span>Utilisateur</span>
            </p>
        </div>
        <?php
        ploopi_die();
    break;

}
