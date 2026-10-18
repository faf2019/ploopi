<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Fenêtres de ploopi4 (skin::version() >= 4) : balisage p4-*, comportement dans
 * js/tickets.js (attributs data-p4-ticket-*), aucun script ni gestionnaire en ligne.
 * Les autres gabarits gardent les fenêtres historiques.
 */
$booTicketsP4 = ploopi\skin::get()->version() >= 4;

if (!function_exists('ploopi_tickets_isallowed'))
{
    /**
     * L'utilisateur connecté est-il l'émetteur ou un destinataire du message (ou de son fil) ?
     *
     * @param int $intTicketId identifiant du message
     * @return boolean
     */
    function ploopi_tickets_isallowed($intTicketId)
    {
        if (!function_exists('system_ticket_isallowed')) include_once './modules/system/include/functions.php';
        return system_ticket_isallowed($intTicketId);
    }

    /**
     * Fenêtre de composition de ploopi4 (nouveau message, réponse, modification)
     *
     * @param array $arrOptions op, hidden (champs cachés), title, message, recipient (destinataire
     * d'une réponse), recipients (sélection de destinataires), id_user (présélection),
     * object_label, button
     */
    function ploopi_tickets_p4_form($arrOptions)
    {
        $skin = ploopi\skin::get();
        $arrOptions = array_merge(array('hidden' => array(), 'title' => '', 'message' => '', 'recipient' => '', 'recipients' => false, 'id_user' => null, 'object_label' => '', 'button' => 'Envoyer'), $arrOptions);
        ?>
        <div class="p4-ui p4-ticket-compose">
            <form method="post" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" target="ploopi_tickets_send" data-p4-ticket-form="<?php echo $arrOptions['recipients'] ? 'new' : 'reply'; ?>" novalidate>
                <input type="hidden" name="ploopi_op" value="<?php echo ploopi\str::htmlentities($arrOptions['op']); ?>">
                <?php foreach($arrOptions['hidden'] as $strName => $strValue) { ?>
                <input type="hidden" name="<?php echo ploopi\str::htmlentities($strName); ?>" value="<?php echo ploopi\str::htmlentities($strValue); ?>">
                <?php } ?>

                <?php if ($arrOptions['recipient'] != '') { ?>
                <p class="p4-ticket-to"><?php echo $skin->icon('user'); ?><span><?php echo _PLOOPI_LABEL_TICKET_RECIPIENT; ?> : <strong><?php echo ploopi\str::htmlentities($arrOptions['recipient']); ?></strong></span></p>
                <?php } ?>

                <div class="p4-field">
                    <label for="p4_ticket_title"><?php echo _PLOOPI_LABEL_TICKET_TITLE; ?></label>
                    <input class="p4-input" type="text" id="p4_ticket_title" name="ticket_title" value="<?php echo ploopi\str::htmlentities($arrOptions['title']); ?>" maxlength="255" autocomplete="off" required>
                </div>

                <div class="p4-field">
                    <label for="fck_ticket_message"><?php echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></label>
                    <textarea id="fck_ticket_message" name="fck_ticket_message" data-ploopi-editor="simple"><?php echo $arrOptions['message']; ?></textarea>
                </div>

                <?php if ($arrOptions['object_label'] != '') { ?>
                <p class="p4-ticket-object"><?php echo $skin->icon('link'); ?><span><?php echo _PLOOPI_LABEL_TICKET_LINKEDOBJECT; ?> : <strong><?php echo ploopi\str::htmlentities($arrOptions['object_label']); ?></strong></span></p>
                <?php } ?>

                <?php if ($arrOptions['recipients']) ploopi\ticket::selectusers($arrOptions['id_user']); ?>

                <p class="p4-ticket-error" role="alert" hidden></p>

                <div class="p4-ticket-actions">
                    <button type="button" class="p4-btn" data-p4-ticket-cancel><?php echo _PLOOPI_CANCEL; ?></button>
                    <button type="submit" class="p4-btn p4-btn-primary"><?php echo $skin->icon('send'); ?><span><?php echo ploopi\str::htmlentities($arrOptions['button']); ?></span></button>
                </div>
            </form>
            <iframe name="ploopi_tickets_send" title="Envoi" hidden></iframe>
        </div>
        <?php
    }
}

switch($ploopi_op)
{
    case 'tickets_getnum':
        list($newtickets, $lastticket) = ploopi\ticket::getnew();
        echo "{$newtickets},{$lastticket}";
        ploopi\system::kill();
    break;

    case 'tickets_alert':
        ob_start();
        ?>
        <p class="ploopi_va" style="padding:4px;">
            <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/newmail.png">
            <a href="<?php echo ploopi\crypt::urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE."&op=tickets"); ?>"><b>Vous avez reçu un nouveau message !</b></a>
        </p>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        echo ploopi\skin::get()->create_popup('Nouveau Message !', $content, 'popup_tickets_new_alert');
        ploopi\system::kill();
    break;

    case 'tickets_new':
        if (!$_SESSION['ploopi']['connected']) ploopi\system::kill();

        if ($booTicketsP4)
        {
            $arrHidden = array('ploopi_tickets_reload' => empty($_GET['ploopi_tickets_reload']) ? '' : $_GET['ploopi_tickets_reload']);
            foreach(array('ploopi_tickets_id_object', 'ploopi_tickets_id_record', 'ploopi_tickets_object_label') as $strParam)
            {
                if (isset($_GET[$strParam])) $arrHidden[$strParam] = $_GET[$strParam];
            }

            ob_start();
            ploopi_tickets_p4_form(array(
                'op' => 'tickets_send',
                'hidden' => $arrHidden,
                'recipients' => true,
                'id_user' => (empty($_GET['ploopi_tickets_id_user']) || !is_numeric($_GET['ploopi_tickets_id_user'])) ? null : (int) $_GET['ploopi_tickets_id_user'],
                'object_label' => isset($_GET['ploopi_tickets_object_label']) ? $_GET['ploopi_tickets_object_label'] : ''
            ));
            $content = ob_get_contents();
            ob_end_clean();
            echo ploopi\skin::get()->create_popup(_PLOOPI_LABEL_NEWTICKET, $content, 'system_popupticket');
            ploopi\system::kill();
        }

        ob_start();
        ?>
        <script type="text/javascript">
            ploopi_tickets_validate = function (form)
            {
                if(ploopi.validatefield('Titre',form.ticket_title,'string'))
                if(ploopi_ticket_validateTo('Destinataire',form.system_ticket_ctrl_user_to))
                    return true;
                return false;
            }
        </script>

        <div id="tickets_new">
            <form method="post" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);">
            <input type="hidden" name="ploopi_op" value="tickets_send">
            <input type="hidden" name="ploopi_tickets_reload" value="<?php if (!empty($_GET['ploopi_tickets_reload'])) echo ploopi\str::htmlentities($_GET['ploopi_tickets_reload']); ?>">
            <?php
            if (isset($_GET['ploopi_tickets_id_object']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_object" value="<?php echo ploopi\str::htmlentities($_GET['ploopi_tickets_id_object']); ?>">
                <?php
            }
            if (isset($_GET['ploopi_tickets_id_record']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_id_record" value="<?php echo ploopi\str::htmlentities($_GET['ploopi_tickets_id_record']); ?>">
                <?php
            }
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <input type="hidden" name="ploopi_tickets_object_label" value="<?php echo ploopi\str::htmlentities($_GET['ploopi_tickets_object_label']); ?>">
                <?php
            }
            ?>
            <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
            <div><input type="text" name="ticket_title" class="text" value="" style="width:98%"></div>
            <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
            <div>
                <textarea id="fck_ticket_message" name="fck_ticket_message" data-ploopi-editor="simple"></textarea>
            </div>

            <?php
            if (isset($_GET['ploopi_tickets_object_label']))
            {
                ?>
                <div><b><?php echo _PLOOPI_LABEL_TICKET_LINKEDOBJECT; ?></b>: <?php echo ploopi\str::htmlentities($_GET['ploopi_tickets_object_label']); ?></div>
                <?php
            }
            ?>
            <?php
            /*
            <p class="ploopi_va ploopi_checkbox_zone" style="padding:4px 0; cursor:pointer;" data-checkbox="ticket_needed_validation">
                <input type="checkbox" name="ticket_needed_validation" id="ticket_needed_validation" style="cursor:pointer;" value="1"><span><?php echo _PLOOPI_LABEL_TICKET_VALIDATIONREQUIRED; ?></span>
            </p>
            */
            ?>

            <div style="padding:8px 4px;margin:4px 0;background-color:#f0f0f0;border:1px solid #c0c0c0;">
                <?php
                ploopi\ticket::selectusers((empty($_GET['ploopi_tickets_id_user'])) ? null : $_GET['ploopi_tickets_id_user']);
                ?>
            </div>
            <div style="text-align:right;">
                    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi.popup.hide('system_popupticket');">
                    <input type="submit" class="flatbutton" value="Envoyer" style="font-weight:bold;">
            </div>
            </form>
            <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        echo ploopi\skin::get()->create_popup(_PLOOPI_LABEL_NEWTICKET, $content, 'system_popupticket');
        ploopi\system::kill();
    break;

    case 'tickets_replyto':
    case 'tickets_modify':
        if (!$_SESSION['ploopi']['connected']) ploopi\system::kill();

        ob_start();

        $ticket = new ploopi\ticket();

        /**
         * Réponse : émetteur ou destinataire du message seulement ; modification : son
         * émetteur seulement. Sans ce contrôle, « Citer » affichait tout message.
         */
        $booTicketAllowed = !empty($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) && $ticket->open($_GET['ticket_id'])
            && ($ploopi_op == 'tickets_replyto' ? ploopi_tickets_isallowed($_GET['ticket_id']) : $ticket->fields['id_user'] == $_SESSION['ploopi']['userid']);

        if (!$booTicketAllowed)
        {
            $popup_title = $ploopi_op == 'tickets_replyto' ? _PLOOPI_LABEL_TICKET_RESPONSE : _PLOOPI_LABEL_TICKET_MODIFICATION;
        }
        elseif ($booTicketsP4)
        {
            if ($ploopi_op == 'tickets_replyto')
            {
                $objUser = new ploopi\user();
                $strUserName = ($objUser->open($ticket->fields['id_user'])) ? "{$objUser->fields['lastname']} {$objUser->fields['firstname']}" : _PLOOPI_LABEL_TICKET_UNKNOWN_USER;
                $popup_title = _PLOOPI_LABEL_TICKET_RESPONSE;
                $strMessage = isset($_GET['quoted']) ? '<div class="system_tickets_quoted_user">Ticket de <b>'.ploopi\str::htmlentities($strUserName).'</b> :</div><div class="system_tickets_quoted_message">'.$ticket->fields['message'].'</div>' : '';
            }
            else $popup_title = _PLOOPI_LABEL_TICKET_MODIFICATION;

            ploopi_tickets_p4_form(array(
                'op' => $ploopi_op == 'tickets_replyto' ? 'tickets_send' : 'tickets_modify_next',
                'hidden' => array('ticket_id' => (int) $_GET['ticket_id'], 'ploopi_tickets_reload' => '1'),
                'title' => $ploopi_op == 'tickets_replyto' ? "RE: {$ticket->fields['title']}" : $ticket->fields['title'],
                // Le message est rendu dans l'éditeur (textarea) : échappé ici, interprété par l'éditeur
                'message' => ploopi\str::htmlentities($ploopi_op == 'tickets_replyto' ? $strMessage : $ticket->fields['message']),
                'recipient' => $ploopi_op == 'tickets_replyto' ? $strUserName : '',
                'button' => $ploopi_op == 'tickets_replyto' ? 'Envoyer' : 'Modifier'
            ));
        }
        else
        {
            if ($ploopi_op == 'tickets_replyto')
            {
                $objUser = new ploopi\user();
                $strUserName = ($objUser->open($ticket->fields['id_user'])) ? "{$objUser->fields['lastname']} {$objUser->fields['firstname']}" : _PLOOPI_LABEL_TICKET_UNKNOWN_USER;

                $ticket->fields['title'] = "RE: {$ticket->fields['title']}";
                $nextop = 'tickets_send';
                $button_value = 'Envoyer';
                $popup_title = _PLOOPI_LABEL_TICKET_RESPONSE;

                if (isset($_GET['quoted'])) $ticket->fields['message'] = '<div class="system_tickets_quoted_user">Ticket de <b>'.ploopi\str::htmlentities($strUserName).'</b> :</div><div class="system_tickets_quoted_message">'.$ticket->fields['message'].'</div>';
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
                    return (ploopi.validatefield('Titre',form.ticket_title,'string'));
                }
            </script>
            <div id="tickets_new">
                <form method="post" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" target="ploopi_tickets_send" onsubmit="javascript:return ploopi_tickets_validate(this);">
                <input type="hidden" name="ploopi_op" value="<?php echo $nextop; ?>">
                <input type="hidden" name="ticket_id" value="<?php echo ploopi\str::htmlentities($_GET['ticket_id']); ?>">
                <input type="hidden" name="ploopi_tickets_reload" value="1">
                <?php
                if ($ploopi_op == 'tickets_replyto')
                {
                    ?>
                    <div><span><?php echo _PLOOPI_LABEL_TICKET_RECIPIENT; ?> : </span><span style="font-weight:bold;"><?php echo ploopi\str::htmlentities($strUserName); ?></span></div>
                    <?php
                }
                ?>

                <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_TITLE; ?></div>
                <div><input type="text" name="ticket_title" class="text" value="<?php echo ploopi\str::htmlentities($ticket->fields['title']); ?>" style="width:98%"></div>
                <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_MESSAGE; ?></div>
                <div>
                    <textarea id="fck_ticket_message" name="fck_ticket_message" data-ploopi-editor="simple"><?php echo $ticket->fields['message']; ?></textarea>
                </div>

                <div style="text-align:right;">
                        <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:ploopi.popup.hide('system_popupticket');">
                        <input type="submit" class="flatbutton" value="<?php echo $button_value; ?>" style="font-weight:bold;">
                </div>
                </form>
                <iframe name="ploopi_tickets_send" style="display:none;"></iframe>
            </div>
            <?php
        }
        $content = ob_get_contents();
        ob_end_clean();
        echo ploopi\skin::get()->create_popup($popup_title, $content, 'system_popupticket');
        ploopi\system::kill();
    break;

    case 'tickets_send':
        $ticket = new ploopi\ticket();
        if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']))
        {
            // Réponse : émetteur ou destinataire du message seulement
            if (!ploopi_tickets_isallowed($_POST['ticket_id'])) ploopi\system::kill();

            $root_ticket = new ploopi\ticket();
            if ($ticket->fields['root_id'] && $root_ticket->open($ticket->fields['root_id']))
            {
                $root_ticket->fields['count_replies']++;
                $root_ticket->fields['lastreply_timestp'] = ploopi\date::createtimestamp();
                $root_ticket->save();
            }

            // La réponse reprend l'objet lié et le contexte du message, pas ses compteurs ni
            // son identifiant ('' dans une colonne entière : refusé en mode SQL strict)
            $response = new ploopi\ticket();
            $response->fields = $ticket->fields;
            unset($response->fields['id']);
            foreach(array('count_read', 'count_replies', 'status', 'lastedit_timestp', 'deleted', 'needed_validation', 'delivery_notification') as $strChamp) $response->fields[$strChamp] = 0;
            $response->fields['title'] = $_POST['ticket_title'];
            $response->fields['message'] = ploopi\str::htmlpurifier($_POST['fck_ticket_message']);
            $response->fields['id_user'] = $_SESSION['ploopi']['userid'];
            $response->fields['timestp'] = ploopi\date::createtimestamp();
            $response->fields['lastreply_timestp'] = $response->fields['timestp'];
            $response->fields['parent_id'] = $_POST['ticket_id'];
            $response->fields['root_id'] = $ticket->fields['root_id'];
            $id_resp = $response->save();

            ploopi\db::get()->query("DELETE FROM ploopi_ticket_watch WHERE id_ticket = {$ticket->fields['root_id']} AND id_user <> {$_SESSION['ploopi']['userid']}");
        }
        else
        {
            if (!empty($_POST['ticket_title']) && isset($_POST['fck_ticket_message']))
            {
                if (!empty($_POST['ploopi_tickets_id_object']) && !empty($_POST['ploopi_tickets_id_record']) && !empty($_POST['ploopi_tickets_object_label']))
                {
                    ploopi\ticket::send($_POST['ticket_title'], $_POST['fck_ticket_message'], isset($_POST['ticket_needed_validation']) ? $_POST['ticket_needed_validation'] : 0, isset($_POST['ticket_delivery_notification']) ? $_POST['ticket_delivery_notification'] : 0, $_POST['ploopi_tickets_id_object'], $_POST['ploopi_tickets_id_record'], $_POST['ploopi_tickets_object_label']);
                }
                else
                {
                    ploopi\ticket::send($_POST['ticket_title'], $_POST['fck_ticket_message'], isset($_POST['ticket_needed_validation']) ? $_POST['ticket_needed_validation'] : 0, isset($_POST['ticket_delivery_notification']) ? $_POST['ticket_delivery_notification'] : 0);
                }
            }
        }

        if (!empty($_POST['ploopi_tickets_reload']) && $_POST['ploopi_tickets_reload'])
        {
            ?>
            <script type="text/javascript">
                window.parent.location.href = '<?php echo ploopi\crypt::urlencode('admin.php?ploopi_mainmenu='._PLOOPI_MENU_MYWORKSPACE.'&op=tickets'); ?>';
            </script>
            <?php
        }
        else
        {
            ?>
            <script type="text/javascript">
                window.parent.ploopi.popup.hide('system_popupticket');
            </script>
            <?php
        }

        ploopi\system::kill();
    break;

    case 'tickets_modify_next':
        $ticket = new ploopi\ticket();
        // Modification : par l'émetteur du message seulement, titre et contenu seulement
        if (isset($_POST['ticket_id']) && is_numeric($_POST['ticket_id']) && isset($_POST['fck_ticket_message']) && $ticket->open($_POST['ticket_id']) && $ticket->fields['id_user'] == $_SESSION['ploopi']['userid'])
        {
            // Champs nommés : setvalues($_POST, 'ticket_') acceptait aussi ticket_id_user, ticket_status…
            if (isset($_POST['ticket_title'])) $ticket->fields['title'] = $_POST['ticket_title'];
            $ticket->fields['message'] = ploopi\str::htmlpurifier($_POST['fck_ticket_message']);
            $ticket->fields['lastedit_timestp'] = ploopi\date::createtimestamp();
            $ticket->save();
        }
        ?>
        <script type="text/javascript">
            window.parent.location.href = '<?php echo ploopi\crypt::urlencode('admin.php?ploopi_mainmenu='._PLOOPI_MENU_MYWORKSPACE.'&op=tickets'); ?>';
        </script>
        <?php
        ploopi\system::kill();
    break;

    case 'tickets_select_user':
        if (!$_SESSION['ploopi']['connected']) ploopi\system::kill();

        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) $_SESSION['ploopi']['tickets']['users_selected'][$_GET['user_id']] = $_GET['user_id'];
        if (isset($_GET['group_id']) && is_numeric($_GET['group_id']))
        {
            $objGroup = new ploopi\group();
            $objGroup->fields['id'] = $_GET['group_id'];
            $arrUsers = $objGroup->getusers();

            foreach($objGroup->getusers() as $user_id => $arrUser) $_SESSION['ploopi']['tickets']['users_selected'][$user_id] = $user_id;
        }
        if (isset($_GET['remove_user_id'])) unset($_SESSION['ploopi']['tickets']['users_selected'][$_GET['remove_user_id']]);

        ploopi\ticket::displayusers();

        ploopi\system::kill();
    break;

    case 'tickets_search_users':
        if (!$_SESSION['ploopi']['connected']) ploopi\system::kill();
        if (!isset($_POST['ploopi_ticket_userfilter']) && !isset($_POST['ploopi_ticket_typefilter'])) ploopi\system::kill();

        // ploopi4 : recherche à la frappe, rien à proposer tant que le champ est vide
        if ($booTicketsP4 && trim((string) $_POST['ploopi_ticket_userfilter']) === '') ploopi\system::kill();

        if (!$booTicketsP4)
        {
        ?>
        <div style="height:150px;overflow-y:auto;overflow-x:hidden;border:1px solid #c0c0c0;">
        <?php
        }
            $listgroup = array();
            $group = new ploopi\group();
            $workspace = new ploopi\workspace();

            // liste pour les espaces, groupes, utilisateurs
            $list = array();
            $list['wsp'] = array();
            $list['grp'] = array();
            $list['usr'] = array();

            $booEmptySearch = true;

            // Filtre utilisateur
            $filtered_search_field = ploopi\db::get()->addslashes($_POST['ploopi_ticket_userfilter']);
            $search_pattern_user = ($_POST['ploopi_ticket_typefilter'] == 'user') ? "AND (u.login LIKE '%{$filtered_search_field}%' OR u.lastname LIKE '%{$filtered_search_field}%' OR u.firstname LIKE '%{$filtered_search_field}%') " : '';

            // construction de la liste des groupes de travail et des groupes d'utilisateurs rattachés (pour l'utilisateur courant)
            foreach ($_SESSION['ploopi']['workspaces'] as $wsp) // pour chaque groupe de travail
            {
                if (isset($wsp['adminlevel']) && $wsp['backoffice'])
                {
                    if ($_POST['ploopi_ticket_typefilter'] != 'workspace' || $_POST['ploopi_ticket_userfilter'] == '' || ($_POST['ploopi_ticket_typefilter'] == 'workspace' && stristr(ploopi\str::convertaccents($wsp['label']), ploopi\str::convertaccents($_POST['ploopi_ticket_userfilter'])) !== false))
                    {
                        $list['wsp'][$wsp['id']]['label'] = $wsp['label'];
                        $list['wsp'][$wsp['id']]['empty'] = true;
                        $list['wsp'][$wsp['id']]['groups'] = array();
                        $list['wsp'][$wsp['id']]['users'] = array();
                        $workspace->fields['id'] = $wsp['id'];
                        foreach ($workspace->getgroups(true) as $grp)
                        {
                            if ($_POST['ploopi_ticket_typefilter'] != 'group' || ploopi\str::convertaccents($_POST['ploopi_ticket_userfilter']) == '' || ($_POST['ploopi_ticket_typefilter'] == 'group' && stristr(ploopi\str::convertaccents($grp['label']), ploopi\str::convertaccents($_POST['ploopi_ticket_userfilter'])) !== false))
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
                    ploopi\db::get()->query("
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
                    while ($fields = ploopi\db::get()->fetchrow())
                    {
                        // Si l'espace contient au moins 1 utilisateur, il n'est pas vide
                        if ($list['wsp'][$fields['id_workspace']]['empty']) $list['wsp'][$fields['id_workspace']]['empty'] = false;

                        $list['wsp'][$fields['id_workspace']]['users'][] = $fields['id'];
                        if (!isset($list['usr'][$fields['id']])) $list['usr'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                    }
                }

                if (!empty($list['grp']))
                {
                    ploopi\db::get()->query("
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
                    while ($fields = ploopi\db::get()->fetchrow())
                    {
                        $list['grp'][$fields['id_group']]['users'][] = $fields['id'];
                        if (!isset($list['usr'][$fields['id']])) $list['usr'][$fields['id']] = array('id' => $fields['id'], 'login' => $fields['login'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
                    }
                }

                if ($booTicketsP4)
                {
                    /**
                     * ploopi4 : résultats groupés par espace, chaque utilisateur ou groupe est un
                     * bouton (data-p4-ticket-add, js/tickets.js). Un utilisateur n'apparaît qu'une
                     * fois par espace, directement ou par l'un de ses groupes.
                     */
                    $skin = ploopi\skin::get();
                    $intResults = 0;
                    ob_start();
                    foreach($list['wsp'] as $id_wsp => $wsp)
                    {
                        $arrUsers = array_unique($wsp['users']);
                        $arrGroups = array();
                        foreach($wsp['groups'] as $id_grp)
                        {
                            if (!empty($list['grp'][$id_grp]['users'])) $arrGroups[$id_grp] = array_unique($list['grp'][$id_grp]['users']);
                        }
                        if (empty($arrUsers) && empty($arrGroups)) continue;
                        ?>
                        <div class="p4-ticket-ws">
                            <p class="p4-ticket-ws-label"><?php echo $skin->icon('building'); ?><span><?php echo ploopi\str::htmlentities($wsp['label']); ?></span></p>
                            <?php
                            foreach($arrGroups as $id_grp => $arrGrpUsers)
                            {
                                $intResults++;
                                ?>
                                <button type="button" class="p4-ticket-result" data-p4-ticket-add="group" data-p4-ticket-id="<?php echo (int) $id_grp; ?>"><?php echo $skin->icon('users-group'); ?><span><?php echo ploopi\str::htmlentities($list['grp'][$id_grp]['label']); ?></span><small><?php echo sizeof($arrGrpUsers); ?> membre(s)</small></button>
                                <?php
                                $arrUsers = array_merge($arrUsers, $arrGrpUsers);
                            }
                            foreach(array_unique($arrUsers) as $id_user)
                            {
                                $intResults++;
                                ?>
                                <button type="button" class="p4-ticket-result" data-p4-ticket-add="user" data-p4-ticket-id="<?php echo (int) $id_user; ?>"><?php echo $skin->icon('user'); ?><span><?php echo ploopi\str::htmlentities(trim("{$list['usr'][$id_user]['lastname']} {$list['usr'][$id_user]['firstname']}")); ?></span><small><?php echo ploopi\str::htmlentities($list['usr'][$id_user]['login']); ?></small></button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    $strResults = ob_get_contents();
                    ob_end_clean();

                    if ($intResults) echo $strResults;
                    else echo '<p class="p4-ticket-empty">Aucun destinataire trouvé.</p>';
                    ploopi\system::kill();
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
                                <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_workgroup.png"><span><?php echo ploopi\str::htmlentities($wsp['label']); ?></span></p>
                            </div>
                            <?php
                            if (!empty($wsp['users']))
                            {
                                foreach($wsp['users'] as $id_user)
                                {
                                    if ($booEmptySearch) $booEmptySearch = false;

                                    ?>
                                    <a class="system_tickets_select_user" href="javascript:void(0);" onclick="javascript:ploopi.xhr.todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&user_id=<?php echo $id_user; ?>', 'div_ticket_users_selected');">
                                        <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo ploopi\str::htmlentities("{$list['usr'][$id_user]['lastname']} {$list['usr'][$id_user]['firstname']}"); ?></span></p>
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
                                        <a class="system_tickets_select_usergroup" href="javascript:void(0);" onclick="javascript:ploopi.xhr.todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&group_id=<?php echo $id_grp; ?>', 'div_ticket_users_selected');">
                                            <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_group.png"><span><?php echo ploopi\str::htmlentities($list['grp'][$id_grp]['label']); ?></span></p>
                                        </a>
                                        <?php
                                        foreach($list['grp'][$id_grp]['users'] as $id_user)
                                        {
                                            if ($booEmptySearch) $booEmptySearch = false;
                                            ?>
                                            <a class="system_tickets_select_usergroup_user" href="javascript:void(0);" onclick="javascript:ploopi.xhr.todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&user_id=<?php echo $id_user; ?>', 'div_ticket_users_selected');">
                                                <p class="ploopi_va"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/ico_user.png"><span><?php echo ploopi\str::htmlentities("{$list['usr'][$id_user]['lastname']} {$list['usr'][$id_user]['firstname']}"); ?></span></p>
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

            if ($booTicketsP4)
            {
                echo '<p class="p4-ticket-empty">Aucun destinataire trouvé.</p>';
                ploopi\system::kill();
            }

            if ($booEmptySearch)
            {
                ?>
                <div style="padding:4px;" class="error">
                    Aucune réponse
                </div>
                <?php
            }
            ploopi\system::kill();

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
        ploopi\system::kill();
    break;

}
