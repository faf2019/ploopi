<?php
/*
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
 * Fonctions de gestion des abonnements sur un enregistrement d'un objet.
 * Permet à un utilisateur d'être averti des modifications d'un objet.
 *
 * @package ploopi
 * @subpackage subscription
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Insère le bloc d'abonnement pour un enregistrement d'un objet
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param mixed $allowedactions tableau des actions auxquelle on peut s'abonner
 * @param string $optional_title titre optionnel
 */

function ploopi_subscription($id_object, $id_record, $allowedactions = null, $optional_title = '')
{
    $ploopi_subscription_id = md5("{$_SESSION['ploopi']['moduleid']}_{$_SESSION['ploopi']['userid']}_{$id_object}_".addslashes($id_record));

    $_SESSION['subscription'][$ploopi_subscription_id] = array(    'id_object' => $id_object,
                                                                    'id_record' => $id_record,
                                                                    'id_module' => $_SESSION['ploopi']['moduleid'],
                                                                    'id_user' => $_SESSION['ploopi']['userid'],
                                                                    'allowedactions' => $allowedactions,
                                                                    'optional_title' => $optional_title
    );
    ?>
    <div id="ploopi_subscription_<?php echo $ploopi_subscription_id; ?>">
    <?php ploopi_subscription_refresh($ploopi_subscription_id); ?>
    </div>
    <?php
}

/**
 * Rafraichit le bloc d'abonnement pour un enregistrement d'un objet
 *
 * @param string identifiant du bloc d'abonnement
 * @param string $next 'suscribed' / 'unsubscribed'
 */

function ploopi_subscription_refresh($ploopi_subscription_id, $next = '')
{
    global $db;

    include_once './include/classes/subscription.php';

    $objSubscription = new subscription();
    $booSubscribed = ($objSubscription->open($ploopi_subscription_id));

    $arrActions = array();

    if ($booSubscribed)
    {
        $strTitle = "Vous êtes abonné {$_SESSION['subscription'][$ploopi_subscription_id]['optional_title']}";
        $arrActions = $objSubscription->getactions();
        $strChecked = ($objSubscription->fields['allactions']) ? 'checked' : '';
        $strIconName = 'subscription1';

    }
    else
    {
        $strTitle = "Vous n'êtes pas abonné {$_SESSION['subscription'][$ploopi_subscription_id]['optional_title']}";
        $strChecked = '';
        $strIconName = 'subscription0';
    }

    $div_id = "subscription_detail_{$ploopi_subscription_id}";
    if (empty($_SESSION['ploopi']['switchdisplay'][$div_id])) $_SESSION['ploopi']['switchdisplay'][$div_id] = 'none';

    ?>
    <div style="overflow:hidden;">
        <a id="annotation_count_<?php echo $ploopi_subscription_id; ?>" class="ploopi_subscription_viewdetail" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('<?php echo $div_id; ?>');ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_switchdisplay&id=<?php echo $div_id ?>&display='+$('<?php echo $div_id ?>').style.display);">
            <img border="0" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/<?php echo $strIconName; ?>.png">
            <span><?php echo $strTitle; ?></span>
        </a>
    </div>

    <div style="display:<?php echo $_SESSION['ploopi']['switchdisplay'][$div_id]; ?>;" id="<?php echo $div_id; ?>" class="ploopi_subscription_detail">

        <form action="" method="post" id="ploopi_form_subscription_<?php echo $ploopi_subscription_id; ?>" target="form_subscription_target_<?php echo $ploopi_subscription_id; ?>">
        <input type="hidden" name="ploopi_op" value="subscription_save">
        <input type="hidden" name="ploopi_subscription_id" value="<?php echo $ploopi_subscription_id; ?>">
        <div style="float:left;width:300px;">

            <?php
            if ($booSubscribed)
            {
                ?>
                <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', -1);">
                    <input type="checkbox" class="checkbox" id="ploopi_subscription_unsubscribe" name="ploopi_subscription_unsubscribe" value="1" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', -1);" />
                    <span class="ploopi_subscription_unsubscribe"><?php echo _PLOOPI_LABEL_SUBSCRIPTION_UNSUSCRIBE; ?></span>
                </div>
                <?php
            }
            ?>
            <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', 0);">
                <input type="checkbox" class="checkbox" id="ploopi_subscription_action_0" name="ploopi_subscription_action[]" value="0" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', 0);" <?php echo $strChecked; ?> />
                <span style="font-weight:bold;"><?php echo _PLOOPI_LABEL_SUBSCRIPTION_ALLACTIONS; ?></span>
            </div>
            <?php

            if (empty($_SESSION['subscription'][$ploopi_subscription_id]['allowedactions'])) // pas de liste d'actions
            {
                $where = " AND id_object = {$_SESSION['subscription'][$ploopi_subscription_id]['id_object']} ";
            }
            else
            {
                $where = " AND id_action IN ('".implode("','", $_SESSION['subscription'][$ploopi_subscription_id]['allowedactions'])."')";
            }

            $sql =  "
                    SELECT      *
                    FROM        ploopi_mb_action
                    WHERE       id_module_type = {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type']}
                    {$where}
                    ORDER BY    id_action
                    ";

            $db->query($sql);

            while ($row = $db->fetchrow())
            {
                $strChecked = (($booSubscribed && $objSubscription->fields['allactions']) || in_array($row['id_action'], $arrActions)) ? 'checked' : '';
                ?>
                <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', <?php echo $row['id_action']; ?>);">
                    <input type="checkbox" class="checkbox" id="ploopi_subscription_action_<?php echo $row['id_action']; ?>" name="ploopi_subscription_action[]" value="<?php echo $row['id_action']; ?>" onclick="javascript:ploopi_subscription_checkaction('<?php echo $ploopi_subscription_id; ?>', <?php echo $row['id_action']; ?>);" <?php echo $strChecked; ?> />
                    <span><?php echo $row['label']; ?></span>
                </div>

                <?php
            }

            if ($next != '')
            {
                switch($next)
                {
                    case 'subscribed':
                        ?>
                        <div class="subscription_saved"><?php echo _PLOOPI_LABEL_SUBSCRIPTION_SAVED; ?></div>
                        <?php
                    break;

                    case 'unsubscribed':
                        ?>
                        <div class="subscription_canceled"><?php echo _PLOOPI_LABEL_SUBSCRIPTION_DELETE; ?></div>
                        <?php
                    break;
                }
            }
            ?>
        </div>
        <div style="padding:4px;"><?php echo _PLOOPI_LABEL_SUBSCRIPTION_DESCIPTION; ?></div>
        <div style="clear:both;padding:4px;text-align:right;">
            <input type="button" onclick="javascript:$('ploopi_form_subscription_<?php echo $ploopi_subscription_id; ?>').reset();ploopi_switchdisplay('<?php echo $div_id; ?>');" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>">
            <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
        </div>

        </form>
        <iframe name="form_subscription_target_<?php echo $ploopi_subscription_id; ?>" src="./img/blank.gif" style="display:none;"></iframe>
    </div>
    <?php

}

/**
 * Détermine si l'utilisateur courant est abonné à un objet, un enregistrement ou une action.
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_action identifiant de l'action
 * @return boolean true si l'utilisateur est abonné
 */

function ploopi_subscription_subscribed($id_object, $id_record, $id_action = -1)
{
    global $db;

    $where = ($id_action != -1) ? " AND (sa.id_action = {$id_action} OR s.allactions = 1) " : '';

    $sql =  "
            SELECT      count(*) as c

            FROM        ploopi_subscription s

            LEFT JOIN   ploopi_subscription_action sa
            ON          sa.id_subscription = s.id

            WHERE       s.id_object = {$id_object}
            AND         s.id_module = {$_SESSION['ploopi']['moduleid']}
            AND         s.id_user = {$_SESSION['ploopi']['userid']}
            AND         s.id_record = '".$db->addslashes($id_record)."'
            {$where}
            ";

    $db->query($sql);
    $row = $db->fetchrow();
    return ($row['c']>0);
}

/**
 * Renvoie un tableau des utilisateurs abonnés à une objet, un enregistrement et une liste d'action
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param array $arrActionIds tableau d'identifiant d'actions
 * @return array tableau d'utilisateurs abonnés
 */

function ploopi_subscription_getusers($id_object, $id_record, $arrActionIds = null)
{
    global $db;

    $where = (is_null($arrActionIds)) ? '' : ' AND (sa.id_action IN ('.implode(',', $arrActionIds).') OR s.allactions = 1) ';

    $sql =  "
            SELECT      u.*

            FROM        ploopi_subscription s

            LEFT JOIN   ploopi_subscription_action sa
            ON          sa.id_subscription = s.id

            INNER JOIN  ploopi_user u
            ON          u.id = s.id_user

            WHERE       s.id_object = {$id_object}
            AND         s.id_module = {$_SESSION['ploopi']['moduleid']}
            AND         s.id_record = '".$db->addslashes($id_record)."'
            AND         s.id_user != {$_SESSION['ploopi']['userid']}
            {$where}

            GROUP BY    u.id
            ";

    $db->query($sql);

    $arrUsers = array();
    while ($row = $db->fetchrow()) $arrUsers[$row['id']] = $row;

    return($arrUsers);
}

/**
 * Envoie une notification par ticket
 *
 * @param int $id_object identifiant de l'objet
 * @param string $id_record identifiant de l'enregistrement
 * @param int $id_action identifiant de l'action
 * @param string $object_title titre de l'objet
 * @param array $arrUsers tableau des destinataires (utilisateurs)
 * @param string $message contenu du message
 */

function ploopi_subscription_notify($id_object, $id_record, $id_action, $object_title, $arrUsers, $message = '')
{
    include_once './include/classes/mb.php';

    if (is_array($arrUsers))
    {
        $objAction = new mb_action();
        $objAction->open($_SESSION['ploopi']['moduletypeid'], $id_action);


        foreach($arrUsers as $intUserId)
        {
            $_SESSION['ploopi']['tickets']['users_selected'] = array();
            $_SESSION['ploopi']['tickets']['users_selected'][] = $intUserId;

            if ($message != '') $message = '<br /><br /><span style="color:#a60000;">'.$message.'</span>';

            ploopi_tickets_send(
                "Alerte abonnement : <i>{$objAction->fields['label']}</i> sur <b>{$object_title}</b> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})",
                "Ceci est un message automatique déclenché par <em>{$_SESSION['ploopi']['user']['lastname']} {$_SESSION['ploopi']['user']['firstname']}</em> sur abonnement à l'action <em>{$objAction->fields['label']}</em> sur l'objet &laquo; <b>{$object_title}</b> &raquo; du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}.
                {$message}<br /><br />Vous pouvez accéder à cet objet en cliquant sur le lien ci-dessous.",
                false,
                0,
                $id_object,
                $id_record,
                $object_title,
                true
                );
        }
    }
}
