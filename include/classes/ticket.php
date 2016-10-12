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

namespace ploopi;

use ploopi;

/**
 * Gestion des tickets
 *
 * @package ploopi
 * @subpackage ticket
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

define ('_PLOOPI_TICKETS_NONE',     0);
define ('_PLOOPI_TICKETS_OPENED',   1);
define ('_PLOOPI_TICKETS_DONE',     2);

/**
 * Classe d'accès à la table ploopi_ticket
 *
 * @package ploopi
 * @subpackage ticket
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return subscription_action
     */

    public function __construct()
    {
        parent::__construct('ploopi_ticket', 'id');
    }

    /**
     * Enregistre le ticket
     *
     * @return boolean true si le ticket s'est enregistré correctement
     */

    public function save()
    {
        $db = db::get();

        if (!$this->new && $this->fields['needed_validation'] > _PLOOPI_TICKETS_NONE && $this->fields['status'] < _PLOOPI_TICKETS_DONE)
        {
            // update ticket status

            $sql =  "
                    SELECT  td.id_user,
                            MAX( IF( ISNULL(ts.status), 0, ts.status)) as max_status

                    FROM    ploopi_ticket_dest td

                    LEFT JOIN   ploopi_ticket_status ts
                    ON      ts.id_ticket = td.id_ticket
                    AND     ts.id_user = td.id_user

                    WHERE   td.id_ticket = {$this->fields['id']}

                    GROUP BY td.id_user
                    ";

            $rs_status = $db->query($sql);
            $global_status = _PLOOPI_TICKETS_DONE;
            while ($fields_status = $db->fetchrow($rs_status))
            {
                if ($fields_status['max_status'] < $global_status) $global_status = $fields_status['max_status'];
            }

            $this->fields['status'] = $global_status;

        }

        // enregistrement d'un nouveau ticket
        if ($this->new)
        {
            $ret = parent::save();
            // update root_id
            if (empty($this->fields['root_id'])) $this->fields['root_id'] = $this->fields['id'];
            if (empty($this->fields['parent_id'])) $this->fields['parent_id'] = $this->fields['id'];
            if ($this->fields['parent_id'] == $this->fields['id']) $this->fields['parent_id'] = 0;
            parent::save();
        }
        else $ret = parent::save();

        return($ret);
    }

    /**
     * Gestion des tickets (messagerie interne).
     * gestion des destinataires, envoi, etc...
     *
     * @package ploopi
     * @subpackage ticket
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    /**
     * Insère un bloc pour la sélection de destinataires
     *
     * @param mixed $id_user identifiant utilisateur ou tableau d'utilisateurs présélectionnés
     */

    public static function selectusers($id_user = null)
    {
        if (isset($_SESSION['ploopi']['tickets']['users_selected'])) unset($_SESSION['ploopi']['tickets']['users_selected']);

        if (!empty($id_user))
        {
            if (is_array($id_user)) foreach($id_user as $idu) $_SESSION['ploopi']['tickets']['users_selected'][$idu] = $idu;
            else $_SESSION['ploopi']['tickets']['users_selected'][$id_user] = $id_user;
        }
        ?>
        <p class="ploopi_va">
            <span><?php echo _PLOOPI_LABEL_TICKET_RECIPIENTSEARCH; ?>:</span>
            <select type="text" id="ploopi_ticket_typefilter" class="select">
                <option value="user">Utilisateur</option>
                <option value="group">Groupe</option>
                <option value="workspace">Espace</option>
            </select>
            <input type="text" id="ploopi_ticket_userfilter" class="text" />
            <?php /* Ne pas modifier, genère une erreur curieuse sinon... */ $strUrl = crypt::queryencode("ploopi_op=tickets_search_users"); ?>
            <img id="ploopi_ticket_search_btn" style="cursor:pointer;" onclick="javascript:ploopi_tickets_select_users('<?php echo $strUrl; ?>', ploopi_getelem('ploopi_ticket_typefilter').value, ploopi_getelem('ploopi_ticket_userfilter').value, 'div_ticket_search_result');" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/tickets/search.png">
        </p>
        <div id="div_ticket_search_result" style="padding:2px 0 6px 0;">
        </div>
        <div style="font-weight:bold;"><?php echo _PLOOPI_LABEL_TICKET_RECIPIENTS ?>:</div>
        <div id="div_ticket_users_selected" style="padding:2px 0 0 0;">
        <?php if (!empty($_SESSION['ploopi']['tickets']['users_selected'])) ticket::displayusers(); ?>
        </div>
        <script type="text/javascript">
        ploopi_tickets_selectusers_init();
        </script>
        <?php
    }

    /**
     * Envoie un ticket
     *
     * @param string $title titre du ticket
     * @param string $message contenu du ticket
     * @param boolean $needed_validation true si le ticket nécessite une validation
     * @param boolean $delivery_notification true si l'émetteur doit être averti de la lecture du message
     * @param int $id_object identifiant de l'objet lié
     * @param string $id_record identifiant de l'enregistrement lié
     * @param string $object_label libellé de l'enregistrement lié
     * @param boolean $system true s'il s'agit d'un ticket émis par le système
     */

    public static function send($title, $message, $needed_validation = 0, $delivery_notification = 0, $id_object = '', $id_record = '', $object_label = '', $system = false)
    {
        if (!empty($_SESSION['ploopi']['userid']))
        {
            $id_user = $_SESSION['ploopi']['userid'];
            $id_workspace = $_SESSION['ploopi']['workspaceid'];
            $id_module = $_SESSION['ploopi']['moduleid'];
            $id_module_type = $_SESSION['ploopi']['moduletypeid'];

            if ($system) $id_user = 0;
        }
        else
        {
            $id_user = $id_workspace = $id_module = $id_module_type = 0;
        }

        $email_message = null;
        if (isset($_SESSION['ploopi']['tickets']['users_selected']) && file_exists("{$_SESSION['ploopi']['template_path']}/ticket.tpl") && is_readable("{$_SESSION['ploopi']['template_path']}/ticket.tpl"))
        {
            $email_message = str::make_links($message);
            // initialisation du moteur de template
            $tplmail = new \Template($_SESSION['ploopi']['template_path']);
            $tplmail->set_filenames(array('mail' => 'ticket.tpl'));

            // Préparation du contenu du mail
            $mb_object = new mb_object();
            if ($id_object != '' && $id_record != '' && $id_module_type != 0 && $mb_object->open($id_object, $id_module_type))
            {
                $tplmail->assign_block_vars('sw_linkedobject',array());

                $object_script =
                    str_replace(
                        array(
                            '<IDRECORD>',
                            '<IDMODULE>',
                            '<IDWORKSPACE>'
                        ),
                        array(
                            $id_record,
                            $id_module,
                            $id_workspace
                        ),
                        $mb_object->fields['script']
                    );

                $url = _PLOOPI_BASEPATH.'/'.crypt::urlencode("admin.php?ploopi_mainmenu=1&{$object_script}");

                $tplmail->assign_vars(array(
                    'OBJECT_URL' => $url,
                    'OBJECT_TYPE' => $mb_object->fields['label'],
                    'OBJECT_LABEL' => $object_label,
                    'MODULE_LABEL' => $_SESSION['ploopi']['modules'][$id_module]['label']
                    )
                );
            }

            if ($id_user == 0)
            {
                $email_from[0] =
                    array(
                        'address'   => trim(current(explode(',', _PLOOPI_ADMINMAIL))),
                        'name'  => empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']
                    );
            }
            else
            {
                if (!empty($_SESSION['ploopi']['user']['email']))
                {
                    $email_from[0] =
                        array(
                            'address' => $_SESSION['ploopi']['user']['email'],
                            'name' => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                        );
                }
                else
                {
                    $email_from[0] =
                        array(
                            'address'   => trim(current(explode(',', _PLOOPI_ADMINMAIL))),
                            'name'  => "{$_SESSION['ploopi']['user']['firstname']} {$_SESSION['ploopi']['user']['lastname']}"
                        );
                }
            }

            $email_subject = strip_tags($title);

            $tplmail->assign_vars(array(
                'USER_FROM_NAME' => $email_from[0]['name'].' ['._PLOOPI_BASEPATH.']',
                'USER_FROM_EMAIL' => $email_from[0]['address'],
                'HTTP_HOST' => _PLOOPI_BASEPATH,
                'MAIL_CONTENT' => $email_message
                )
            );

            ob_start();
            $tplmail->pparse('mail');
            $email_message = trim(ob_get_contents());
            ob_end_clean();
        }

        // Création du ticket
        $ticket = new self();
        $ticket->fields['id_object'] = $id_object;
        $ticket->fields['id_record'] = $id_record;
        $ticket->fields['id_module'] = $id_module;
        $ticket->fields['id_workspace'] = $id_workspace;
        $ticket->fields['id_user'] = $id_user;
        $ticket->fields['object_label'] = $object_label;
        $ticket->fields['title'] = $title;
        $ticket->fields['message'] = str::htmlpurifier($message);
        $ticket->fields['needed_validation'] = $needed_validation;
        $ticket->fields['delivery_notification'] = $delivery_notification;
        $ticket->fields['timestp'] = date::createtimestamp();
        $ticket->fields['lastreply_timestp'] = $ticket->fields['timestp'];
        $id_ticket = $ticket->save();

        // Envoi du ticket aux destinataires
        foreach(array_unique($_SESSION['ploopi']['tickets']['users_selected']) as $user_id)
        {
            $user = new user();
            if ($user->open($user_id))
            {
               // Envoi d'une copie par mail si nécessaire
                if (!empty($email_message) && $user->fields['ticketsbyemail'] == 1 && !empty($user->fields['email']))
                {

                    $email_to[0] =
                        array(
                            'address' => $user->fields['email'],
                            'name' => "{$user->fields['firstname']} {$user->fields['lastname']}"
                        );


                    mail::send($email_from, $email_to, $email_subject, $email_message);
                }

                $ticket_dest = new ticket_dest();
                $ticket_dest->fields['id_user'] = $user_id;
                $ticket_dest->fields['id_ticket'] = $id_ticket;
                $ticket_dest->save();
            }
        }

        unset($_SESSION['ploopi']['tickets']['users_selected']);

    }

    /**
     * Renvoie l'identifiant du dernier ticket reçu par l'utilisateur connecté et le nombre de nouveaux tickets
     *
     * @return array indice 0 : nombre nouveau tickets, 1 : id du dernier ticket
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     */

    public static function getnew()
    {
        $db = db::get();

        $sql =  "
                SELECT      t.id

                FROM        ploopi_ticket t

                INNER JOIN  ploopi_ticket_dest td
                ON          td.id_ticket = t.id

                LEFT JOIN   ploopi_ticket_watch tw
                ON          tw.id_ticket = t.id
                AND         tw.id_user = {$_SESSION['ploopi']['userid']}

                WHERE       ((t.id_user = {$_SESSION['ploopi']['userid']} AND t.deleted = 0) OR (td.id_user = {$_SESSION['ploopi']['userid']} AND td.deleted = 0))
                AND         isnull(tw.notify)

                GROUP BY t.id
                ORDER BY t.id DESC
                ";

        $rs = $db->query($sql);

        $tickets_new = $db->numrows($rs);

        $tickets_lastid = ($row = $db->fetchrow()) ? $row['id'] : 0;

        return(array($tickets_new, $tickets_lastid));
    }

    /**
     * Insère un bloc pour afficher les destinataires d'un ticket et les supprimer
     */

    public static function displayusers()
    {
        $skin = skin::get();

        if (!empty($_SESSION['ploopi']['tickets']['users_selected']))
        {
            ?>
            <input type="hidden" id="system_ticket_ctrl_user_to" name="system_ticket_ctrl_user_to" value="<?php echo implode(',',$_SESSION['ploopi']['tickets']['users_selected']); ?>" />
            <?php
            foreach($_SESSION['ploopi']['tickets']['users_selected'] as $user_id)
            {
                $user = new user();
                $user->open($user_id);

                $color = (!isset($color) || $color == $skin->values['bgline2']) ? $skin->values['bgline1'] : $skin->values['bgline2'];
                ?>
                <p class="ploopi_va" style="padding:2px;">
                    <a class="system_tickets_delete_user" href="javascript:void(0);" onclick="ploopi_xmlhttprequest_todiv('admin.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_select_user&remove_user_id=<?php echo $user->fields['id']; ?>', 'div_ticket_users_selected');">
                        <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/btn_delete.png" />
                        <span><?php echo str::htmlentities("{$user->fields['lastname']} {$user->fields['firstname']}")." ("._PLOOPI_LABEL_TICKET_DELETERECIPIENT.')'; ?></span>
                    </a>
                </p>
                <?php
            }
        }
    }


}

