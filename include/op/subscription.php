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
 * Opérations sur les abonnements
 *
 * @package ploopi
 * @subpackage subscription
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

switch($ploopi_op)
{
    case 'subscription':
        if (empty($_GET['ploopi_subscription_id'])) ovensia\ploopi\system::kill();

        ovensia\ploopi\subscription::display_refresh($_GET['ploopi_subscription_id'], (empty($_GET['next'])) ? '' : $_GET['next']);
        ovensia\ploopi\system::kill();
    break;

    case 'subscription_save':
        $strNext = '';
        if (!empty($_POST['ploopi_subscription_id']))
        {
            if (!empty($_POST['ploopi_subscription_action']))
            {
                $objSubscription = new ovensia\ploopi\subscription();

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

                if ($_POST['ploopi_subscription_action'][0] == 0) // abonnement à toutes les actions
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
                        $objSubscriptionAction = new ovensia\ploopi\subscription_action();
                        $objSubscriptionAction->fields['id_action'] = $intActionId;
                        $objSubscriptionAction->fields['id_subscription'] = $_POST['ploopi_subscription_id'];
                        $objSubscriptionAction->save();
                    }
                }
                $strNext = 'subscribed';
            }
            else
            {
                $objSubscription = new ovensia\ploopi\subscription();
                if ($objSubscription->open($_POST['ploopi_subscription_id'])) // abonnement existant
                {
                    $objSubscription->delete(); // on le supprime
                    $strNext = 'unsubscribed';
                }
            }
        }

        ?>
        <script type="text/javascript">
            window.parent.ovensia\ploopi\subscription::display('<?php echo ovensia\ploopi\str::htmlentities($_POST['ploopi_subscription_id']); ?>', '<?php echo $strNext ?>');
        </script>
        <?php

        ovensia\ploopi\system::kill();
    break;
}
