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

function ploopi_subscription($id_object, $id_record, $allowedactions = null, $optional_title = '')
{
    $ploopi_subscription_id = md5("{$_SESSION['ploopi']['moduleid']}_{$_SESSION['ploopi']['userid']}_{$id_object}_".addslashes($id_record));

    $_SESSION['subscriptions'][$ploopi_subscription_id] = array(    'id_object' => $id_object,
                                                                    'id_record' => $id_record,
                                                                    'id_module' => $_SESSION['ploopi']['moduleid'],
                                                                    'id_user' => $_SESSION['ploopi']['userid'],
                                                                    'allowedactions' => $allowedactions,
                                                                    'optional_title' => $optional_title
    );
    ?>
    <div id="ploopi_subscription_<? echo $ploopi_subscription_id; ?>"></div>
    <script type="text/javascript">
        ploopi_subscription('<? echo $ploopi_subscription_id; ?>');
    </script>
    <?    
}

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


function ploopi_subscription_notify($id_object, $id_record, $id_action, $object_title, $arrUsers, $message = '')
{
    include_once './modules/system/class_mb_action.php';
    $objAction = new mb_action();
    $objAction->open($_SESSION['ploopi']['moduletypeid'], $id_action);
    
    foreach($arrUsers as $intUserId)
    {
        $_SESSION['ploopi']['tickets']['users_selected'] = array();
        $_SESSION['ploopi']['tickets']['users_selected'][] = $intUserId;
        
        if ($message != '') $message = '<br /><br /><span style="color:#a60000;">'.$message.'</span>';
        
        ploopi_tickets_send(   "Alerte abonnement : <i>{$objAction->fields['label']}</i> sur <b>{$object_title}</b> (module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']})", 
                                "Ceci est un message automatique déclenché par un abonnement à l'action <i>{$objAction->fields['label']}</i> sur l'objet &laquo; <b>{$object_title}</b> &raquo; du module {$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['label']}
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
?>