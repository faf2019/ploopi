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
    case 'subscription':
        if (empty($_GET['ploopi_subscription_id'])) ploopi_die();
        
        include_once './include/classes/class_subscription.php';
        
        $objSubscription = new subscription();
        $booSubscribed = ($objSubscription->open($_GET['ploopi_subscription_id']));
        
        $arrActions = array();
        
        if ($booSubscribed)
        {
            $strTitle = "Vous êtes abonné {$_SESSION['subscriptions'][$_GET['ploopi_subscription_id']]['optional_title']}";
            $arrActions = $objSubscription->getactions();
            $strChecked = ($objSubscription->fields['allactions']) ? 'checked' : '';
            $strIconName = 'subscription1';
            
        }
        else
        {
            $strTitle = "Vous n'êtes pas abonné {$_SESSION['subscriptions'][$_GET['ploopi_subscription_id']]['optional_title']}";
            $strChecked = '';
            $strIconName = 'subscription0';
        }
        
        $div_id = "subscription_detail_{$_GET['ploopi_subscription_id']}";
        if (empty($_SESSION['ploopi']['switchdisplay'][$div_id])) $_SESSION['ploopi']['switchdisplay'][$div_id] = 'none';

        ?>
        <div style="overflow:hidden;">
            <a id="annotations_count_<? echo $_GET['ploopi_subscription_id']; ?>" class="ploopi_subscription_viewdetail" href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('<? echo $div_id; ?>');ploopi_xmlhttprequest('admin-light.php','ploopi_op=ploopi_switchdisplay&id=<? echo $div_id ?>&display='+$('<? echo $div_id ?>').style.display);">
                <img border="0" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/system/<? echo $strIconName; ?>.png">
                <span><? echo $strTitle; ?></span>
            </a>
        </div>
        
        <div style="display:<? echo $_SESSION['ploopi']['switchdisplay'][$div_id]; ?>;" id="<? echo $div_id; ?>" class="ploopi_subscription_detail">

            <form action="" method="post" id="ploopi_form_subscription_<? echo $_GET['ploopi_subscription_id']; ?>" target="form_subscription_target_<? echo $_GET['ploopi_subscription_id']; ?>">
            <input type="hidden" name="ploopi_op" value="subscription_save">
            <input type="hidden" name="ploopi_subscription_id" value="<? echo $_GET['ploopi_subscription_id']; ?>">
            <div style="float:left;width:300px;">
    
                <?
                if ($booSubscribed)
                {
                    ?>
                    <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', -1);">
                        <input type="checkbox" class="checkbox" id="ploopi_subscription_unsubscribe" name="ploopi_subscription_unsubscribe" value="1" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', -1);" />
                        <span class="ploopi_subscription_unsubscribe"><? echo _PLOOPI_LABEL_SUBSCRIPTION_UNSUSCRIBE; ?></span>
                    </div>            
                    <?
                }
                ?>
                <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', 0);">
                    <input type="checkbox" class="checkbox" id="ploopi_subscription_action_0" name="ploopi_subscription_action[]" value="0" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', 0);" <? echo $strChecked; ?> />
                    <span style="font-weight:bold;"><? echo _PLOOPI_LABEL_SUBSCRIPTION_ALLACTIONS; ?></span>
                </div>            
                <?
                
                if (empty($_SESSION['subscriptions'][$_GET['ploopi_subscription_id']]['allowedactions'])) // pas de liste d'actions
                {
                    $where = " AND id_object = {$_SESSION['subscriptions'][$_GET['ploopi_subscription_id']]['id_object']} ";
                }
                else
                {
                    $where = " AND id_action IN ('".implode("','", $_SESSION['subscriptions'][$_GET['ploopi_subscription_id']]['allowedactions'])."')";
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
                    <div class="ploopi_subscription_checkbox" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', <? echo $row['id_action']; ?>);">
                        <input type="checkbox" class="checkbox" id="ploopi_subscription_action_<? echo $row['id_action']; ?>" name="ploopi_subscription_action[]" value="<? echo $row['id_action']; ?>" onclick="javascript:ploopi_subscription_checkaction('<? echo $_GET['ploopi_subscription_id']; ?>', <? echo $row['id_action']; ?>);" <? echo $strChecked; ?> />
                        <span><? echo $row['label']; ?></span>
                    </div>            
            
                    <?
                }
                
                if (isset($_GET['next']) && $_GET['next'] != '')
                {
                    switch($_GET['next'])
                    {
                        case 'subscribed':
                            ?>
                            <div class="subscription_saved"><? echo _PLOOPI_LABEL_SUBSCRIPTION_SAVED; ?></div>
                            <?
                        break;
                        
                        case 'unsubscribed':
                            ?>
                            <div class="subscription_canceled"><? echo _PLOOPI_LABEL_SUBSCRIPTION_DELETE; ?></div>
                            <?
                        break;
                    }
                }
                ?>
            </div>
            <div style="padding:4px;"><? echo _PLOOPI_LABEL_SUBSCRIPTION_DESCIPTION; ?></div>
            <div style="clear:both;padding:4px;text-align:right;">
                <input type="button" onclick="ploopi_getelem('form_subscription_<? echo $_GET['ploopi_subscription_id']; ?>').ploopi_op.value=''; ploopi_getelem('form_subscription_<? echo $_GET['ploopi_subscription_id']; ?>').submit()" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>">
                <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
            </div>
            
            </form>
            <iframe name="form_subscription_target_<? echo $_GET['ploopi_subscription_id']; ?>" src="./img/blank.gif" style="width:0;height:0;display:none;"></iframe>
        </div>
        <?
        ploopi_die();
    break;
    
    
    case 'subscription_save':
        $strNext = '';
        if (!empty($_POST['ploopi_subscription_id']))
        {
            include_once './include/classes/class_subscription.php';
            include_once './include/classes/class_subscription_action.php';
            
            
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
                    $objSubscription->fields['id_object'] = $_SESSION['subscriptions'][$_POST['ploopi_subscription_id']]['id_object'];
                    $objSubscription->fields['id_record'] = $_SESSION['subscriptions'][$_POST['ploopi_subscription_id']]['id_record'];
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