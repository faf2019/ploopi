<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Op�rations sur les abonnements
 *
 * @package ploopi
 * @subpackage subscription
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

switch($ploopi_op)
{
    case 'subscription':
        if (empty($_GET['ploopi_subscription_id'])) ploopi_die();
        
        ploopi_subscription_refresh($_GET['ploopi_subscription_id'], (empty($_GET['next'])) ? '' : $_GET['next']);
        ploopi_die();
    break;
    
    
    case 'subscription_save':
        $strNext = '';
        if (!empty($_POST['ploopi_subscription_id']))
        {
            include_once './include/classes/subscription.php';
            
            if (!empty($_POST['ploopi_subscription_action']))
            {
                $objSubscription = new subscription();
                
                if ($objSubscription->open($_POST['ploopi_subscription_id'])) // abonnement existant
                {
                    $objSubscription->clean();
                }
                else // nouvel abonnement
                {
                    $objSubscription->fields['id'] = $_POST['ploopi_subscription_id'];
                    $objSubscription->fields['id_object'] = $_SESSION['subscription'][$_POST['ploopi_subscription_id']]['id_object'];
                    $objSubscription->fields['id_record'] = $_SESSION['subscription'][$_POST['ploopi_subscription_id']]['id_record'];
                    $objSubscription->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
                    $objSubscription->fields['id_user'] = $_SESSION['ploopi']['userid'];
                }

                if ($_POST['ploopi_subscription_action'][0] == 0) // abonnement � toutes les actions
                {
                    $objSubscription->fields['allactions'] = 1;
                    $objSubscription->save();
                }
                else
                {
                    $objSubscription->fields['allactions'] = 0;
                    $objSubscription->save();
                    foreach($_POST['ploopi_subscription_action'] as $intActionId)
                    {
                        $objSubscriptionAction = new subscription_action();
                        $objSubscriptionAction->fields['id_action'] = $intActionId;
                        $objSubscriptionAction->fields['id_subscription'] = $_POST['ploopi_subscription_id'];
                        $objSubscriptionAction->save();
                    }
                }
                $strNext = 'subscribed';
            }
            elseif (!empty($_POST['ploopi_subscription_unsubscribe']) && $_POST['ploopi_subscription_unsubscribe'])
            {
                $objSubscription = new subscription();
                if ($objSubscription->open($_POST['ploopi_subscription_id'])) // abonnement existant
                {
                    $objSubscription->delete(); // on le supprime
                    $strNext = 'unsubscribed';
                }
            }
        }
        
        ?>
        <script type="text/javascript">
            window.parent.ploopi_subscription('<? echo $_POST['ploopi_subscription_id']; ?>', '<? echo $strNext ?>');
        </script>
        <?
        
        ploopi_die();
    break;
}
?>