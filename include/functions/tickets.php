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
?>
<?
define ('_PLOOPI_TICKETS_NONE',     0);
define ('_PLOOPI_TICKETS_OPENED',   1);
define ('_PLOOPI_TICKETS_DONE',     2);

function ploopi_tickets_selectusers($show_message = false, $userlist = null, $width = 500)
{
    if (isset($_SESSION['ploopi']['tickets']['users_selected'])) unset($_SESSION['ploopi']['tickets']['users_selected']);
    ?>
    <table cellpadding="0" cellspacing="0" style="width:<? echo $width; ?>;">
    <?
    if ($show_message)
    {
        ?>
        <tr>
            <td><textarea name="ploopi_ticket_message" class="text" style="width:<? echo $width-10; ?>px;height:50px"></textarea></td>
        </tr>
        <?
    }
    if (is_null($userlist))
    {
        ?>
        <tr>
            <td>
            <table style="padding:2px 0 0 0" cellspacing="0">
                <tr>
                    <td>Recherche destinataires:&nbsp;&nbsp;</td>
                    <td><input type="text" id="ploopi_ticket_userfilter" class="text">&nbsp;</td>
                    <td><img onmouseover="javascript:this.style.cursor='pointer';" onclick="ploopi_xmlhttprequest_todiv('admin.php','ploopi_op=tickets_search_users&ploopi_ticket_userfilter='+ploopi_getelem('ploopi_ticket_userfilter').value,'','div_ticket_search_result');" style="border:0px" src="./img/icon_loupe.png"></a></td>
                </tr>
            </table>
            </td>
        </tr>
        <?
    }
    else
    {
        foreach($userlist as $userid)
        {
            $_SESSION['ploopi']['tickets']['users_selected'][$userid] = $userid;
        }
    }
    ?>
    </table>
    <?
    if (is_null($userlist))
    {
        ?>
        <div id="div_ticket_search_result" style="padding:2px 0 6px 0;">
        </div>
        Destinataires qui vont recevoir un message :
        <div id="div_ticket_users_selected" style="padding:2px 0 0 0;">
        </div>
        <?
    }
}

function ploopi_tickets_send($title, $message, $needed_validation = 0, $delivery_notification = 0, $id_object = '', $id_record = '', $object_label = '')
{
    include_once './modules/system/class_user.php';
    include_once './modules/system/class_ticket.php';
    include_once './modules/system/class_ticket_dest.php';
    include_once './modules/system/class_mb_object.php';

    /*if ($message == '')
    {
        if (isset($_POST['ploopi_ticket_message'])) $message = $_POST['ploopi_ticket_message'];
        if (isset($_GET['ploopi_ticket_message'])) $message = $_GET['ploopi_ticket_message'];
    }*/

    if (!empty($_SESSION['ploopi']['userid']))
    {
        $id_user = $_SESSION['ploopi']['userid'];
        $id_group = $_SESSION['ploopi']['workspaceid'];
        $id_module = $_SESSION['ploopi']['moduleid'];
        $id_module_type = $_SESSION['ploopi']['moduletypeid'];
    }
    else
    {
        $id_user = $id_group = $id_module = $id_module_type = 0;
    }

    if (isset($_SESSION['ploopi']['tickets']['users_selected']))
    {
        // initialisation du moteur de template
        $tplmail = new Template($_SESSION['ploopi']['template_path']);
        $tplmail->set_filenames(array('mail' => 'mail.tpl'));

        $ticket = new ticket();
        $ticket->fields['id_object'] = $id_object;
        $ticket->fields['id_record'] = $id_record;
        $ticket->fields['id_module'] = $id_module;
        $ticket->fields['id_user'] = $id_user;
        $ticket->fields['object_label'] = $object_label;
        $ticket->fields['title'] = $title;
        $ticket->fields['message'] = $message;
        $ticket->fields['needed_validation'] = $needed_validation;
        $ticket->fields['delivery_notification'] = $delivery_notification;
        $ticket->fields['timestp'] = ploopi_createtimestamp();
        $ticket->fields['lastreply_timestp'] = $ticket->fields['timestp'];
        $id_ticket = $ticket->save();

        $email_message = ploopi_make_links($message);

        $http_host = dirname($_SERVER['HTTP_REFERER']);

        if ($id_object != '' && $id_record != '' && $id_module_type != 0)
        {
            $tplmail->assign_block_vars('sw_linkedobject',array());

            $mb_object = new mb_object();
            $mb_object->open($id_object, $id_module_type);

            $object_script = str_replace('<IDRECORD>',$id_record,$mb_object->fields['script']);
            $object_script = str_replace('<IDMODULE>',$id_module,$object_script);

            $url = "{$http_host}/".ploopi_urlencode("admin.php?ploopi_mainmenu=1&ploopi_workspaceid={$id_group}&{$object_script}");

            $tplmail->assign_vars(array(
                'OBJECT_URL' => $url,
                'OBJECT_TYPE' => $mb_object->fields['label'],
                'OBJECT_LABEL' => $object_label,
                'MODULE_LABEL' => $_SESSION['ploopi']['modules'][$id_module]['label']
                )
            );

            //$email_message .="<br /><br /><span><strong>Objet li�</strong>: </span><a href=\"{$url}\">{$_SESSION['ploopi']['modules'][$id_module]['label']} / {$mb_object->fields['label']} <b>\"{$object_label}\"</b></a>";
        }

        if ($id_user == 0)
        {
                $email_from[0] = array( 'address'   => _PLOOPI_ADMINMAIL,
                                        'name'  => _PLOOPI_ADMINMAIL
                                    );
        }
        else
        {
            if (!empty($_SESSION['ploopi']['user']['email']))
            {
                $email_from[0] = array( 'address'   => $_SESSION['ploopi']['user']['email'],
                                        'name'  => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                                    );
            }
            else
            {
                $email_from[0] = array( 'address'   => _PLOOPI_ADMINMAIL,
                                        'name'  => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                                    );
            }
        }

        $email_subject = "[{$http_host}] - Nouveau ticket de {$email_from[0]['name']} : {$title}";

        $tplmail->assign_vars(array(
            'USER_FROM_NAME' => $email_from[0]['name'],
            'USER_FROM_EMAIL' => $email_from[0]['address'],
            'HTTP_HOST' => $http_host,
            'MAIL_CONTENT' => $email_message
            )
        );


        /*$email_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<body>Bonjour,<br /><br />Vous avez re�u un nouveau ticket envoy� par <strong>'.$email_from[0]['name'].'</strong> depuis le site <a href="'.$http_host.'">'.$http_host.'</a> : <br /><hr /><br />'.$email_message.'</body></html>';
*/

        ob_start();
        $tplmail->pparse('mail');
        $email_message = trim(ob_get_contents());
        ob_end_clean();

        foreach($_SESSION['ploopi']['tickets']['users_selected'] as $user_id)
        {
            $user = new user();
            $user->open($user_id);
            if ($user->fields['ticketsbyemail'] == 1 && !empty($user->fields['email']))
            {
                $email_to[0] = array(   'address'   => $user->fields['email'],
                                        'name'  => "{$user->fields['firstname']} {$user->fields['lastname']}"
                                    );

                ploopi_send_mail($email_from, $email_to, $email_subject, $email_message);
            }

            $ticket_dest = new ticket_dest();
            $ticket_dest->fields['id_user'] = $user_id;
            $ticket_dest->fields['id_ticket'] = $id_ticket;
            $ticket_dest->save();

        }

        unset($_SESSION['ploopi']['tickets']['users_selected']);
    }

}

function ploopi_tickets_new($id_object = '', $id_record = '', $object_label = '')
{
    return('<a href="#" onclick="javascript:ploopi_tickets_new(event, '.$id_object.',\''.addslashes($id_record).'\',\''.addslashes(addslashes($object_label)).'\');"><img style="border:0px;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/email_read.png"></a>');
}

?>
