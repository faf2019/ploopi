<?php
/*
    Copyright (c) 2008 HeXad
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
 * Gestion des inscrits à la newsletter
 *
 * @package newsletter
 * @subpackage send
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

echo $skin->open_simplebloc(_NEWSLETTER_LABEL_SUBSCRIBER);

/*
 * Edition d'une inscription
 */
if(!empty($_GET['email_subscrib']))
{
  $objSubscriber = new newsletter_subscriber();
  if($objSubscriber->open($_GET['email_subscrib'],$_SESSION['ploopi']['moduleid']))
  {
    // date time subject
    $arrNewsletterDateSubscribe = ploopi_timestamp2local($objSubscriber->fields['timestp_subscribe']);

    echo $skin->open_simplebloc(_NEWSLETTER_LABEL_SUBSCRIBER_MODIF.' '.$objSubscriber->fields['email'],'margin: 2px auto; width:400px;');
    ?>
    <div class="ploopi_form">
      <div style="padding:2px;">
        <form action="<? echo ploopi_urlencode('admin.php?op=subscrib_save&email_subscrib='.$objSubscriber->fields['email']); ?>" method="post" onSubmit="javascript:return newsletter_subscrib_validate(this);">
        <input type="hidden" name="subscrib_active" value="0">
        <p><!-- Email inscription -->
          <label><? echo _NEWSLETTER_LABEL_EMAIL; ?>&nbsp;:</label>
          <input class="text" id="subscrib_email" name="subscrib_email" type="text" size="50" maxlength="255" value="<? echo $objSubscriber->fields['email']; ?>">
        </p>
        <p class="ploopi_va" style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,'subscrib_active');"><!-- Email activé ou pas pour l'envoi -->
          <label><?php echo _NEWSLETTER_LABEL_ACTIVE; ?>&nbsp;:</label>
          <input type="checkbox" id="subscrib_active" name="subscrib_active" value="1" <?php if($objSubscriber->fields['active']) echo 'checked="checked"'; ?>>
        </p><!-- IP lors de l'inscription -->
        <p class="ploopi_va">
          <label><?php echo _NEWSLETTER_LABEL_IP; ?>&nbsp;:</label>
          <? echo str_replace(',','<br/>',$objSubscriber->fields['ip']); ?>
        </p>
        <p class="ploopi_va"><!-- date/heure d'inscription -->
          <label><?php echo _NEWSLETTER_LABEL_TIMESTP_SUBSCRIBE; ?>&nbsp;:</label>
          <? echo $arrNewsletterDateSubscribe['date'].' '.$arrNewsletterDateSubscribe['time']; ?>
        </p>
        <div style="padding:2px;text-align:right;">
          <input type="submit" value="<? echo _PLOOPI_SAVE; ?>" class="button">
        </div>
        </form>
      </div>
    </div>
    <?php
    echo $skin->close_simplebloc();
  }
  unset($objSubscriber);
  unset($arrNewsletterDateSubscribe);
}

/**
 * Gestion du filtrage
 */
if(!isset($_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['filter']))
  $_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['filter'] = '';

if(!isset($_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['alphaTabItem']))
  $_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['alphaTabItem'] = -1;

// Mise en place du filtre
if (isset($_POST['reset']))
  $filter = '';
else
{
  if(empty($_POST['filter']))
    $filter = $_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['filter'];
  else
    $filter = $_POST['filter'];
}
$_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['filter'] = $filter;

// Mise en  place du filtrage alphabétique
if(empty($_GET['alphaTabItem']))
  $alphaTabItem = $_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['alphaTabItem'];
else
  $alphaTabItem = (empty($_GET['alphaTabItem'])) ? -1 : $db->addslashes($_GET['alphaTabItem']);

$_SESSION['ploopi']['newsletter'][$_SESSION['ploopi']['moduleid']]['list_receiv']['alphaTabItem'] = $alphaTabItem;

/**
 * Bloc de choix alphabétique
 */
?>
<div style="padding:4px;">
    <?
    $tabs_char = array();

    for($i=1;$i<27;$i++) $tabs_char[$i] = array('title' => chr($i+64), 'url' => "admin.php?op=newsletter_list_to&alphaTabItem={$i}&id_newsletter={$_GET['id_newsletter']}");

    $tabs_char[99] = array('title' => "&nbsp;tous&nbsp;", 'url' => "admin.php?op=newsletter_list_to&alphaTabItem=99&id_newsletter={$_GET['id_newsletter']}");

    echo $skin->create_tabs($tabs_char,$alphaTabItem);
    ?>
</div>
<?
/**
 * Bloc de "filtre"
 */
?>
<form action="<? echo ploopi_urlencode('admin.php?op=newsletter_list_to&id_newsletter='.$_GET['id_newsletter']); ?>" method="post">
<p class="ploopi_va" style="padding:4px;border-bottom:2px solid #c0c0c0;">
    <span><? echo _NEWSLETTER_LABEL_EMAIL; ?> :</span>
    <input class="text" ID="system_user" name="filter" type="text" size="15" maxlength="255" value="<? echo htmlentities($filter); ?>">
    <input type="submit" value="<? echo _PLOOPI_FILTER; ?>" class="button">
    <input type="submit" name="reset" value="<? echo _PLOOPI_RESET; ?>" class="button">
</p>
</form>
<?
// Création de la requète des inscrits
$where = array();

if ($filter != '')
{
  $filter = $db->addslashes($filter);
  $where[] =  "subscrib.email LIKE '%{$filter}%'";
}

if ($alphaTabItem != 99) // Si on est pas sur 'tous'
{
  $where[] = "subscrib.email LIKE '".chr($alphaTabItem+96)."%'";
}

$where  = (empty($where)) ? '' : ' AND '.implode(' AND ', $where);

$sql =   "
            SELECT      send.*,
                        subscrib.timestp_subscribe,
                        IFNULL(subscrib.ip, '"._NEWSLETTER_DELETED."') as ip,
                        IFNULL(subscrib.email, '') as email_modif
            FROM        ploopi_mod_newsletter_send as send
            LEFT JOIN   ploopi_mod_newsletter_subscriber as subscrib
              ON       (subscrib.email = send.email_subscriber
                        AND subscrib.id_module = '{$_SESSION['ploopi']['moduleid']}')

            WHERE       send.id_letter = '".$db->addslashes($_GET['id_newsletter'])."'
            {$where}
            ";

$columns = array();
$values = array();
// titre des colonnes du tableau de résultat
$columns['auto']['email']             = array('label' => _NEWSLETTER_NAMECOLUMN_EMAIL, 'options' => array('sort' => true));
$columns['right']['ip']               = array('label' => _NEWSLETTER_NAMECOLUMN_IP, 'width' => '120', 'options' => array('sort' => true));
$columns['right']['timestp']          = array('label' => _NEWSLETTER_NAMECOLUMN_SUBSCRIBE, 'width' => '140', 'options' => array('sort' => true));
$columns['right']['timestpsend']      = array('label' => _NEWSLETTER_NAMECOLUMN_SEND, 'width' => '140', 'options' => array('sort' => true));

$c = 0;

$result = $db->query($sql);
/*
 * Affichage dans le tableau des inscrits
 */
while ($fields = $db->fetchrow($result))
{
  // date time d'envoi
  $arrNewsletterDateSend = ploopi_timestamp2local($fields['timestp_send']);

  $values[$c]['values']['email']       = array('label' => htmlentities($fields['email_subscriber']));
  $values[$c]['values']['ip']          = array('label' => str_replace(',','<br/>',$fields['ip']));
  $values[$c]['values']['timestpsend']    = array('label' => $arrNewsletterDateSend['date'].' '.$arrNewsletterDateSend['time']);
  // Date d'inscription
  if($fields['timestp_subscribe'])
  {
    $arrNewsletterDateSubscribe = ploopi_timestamp2local($fields['timestp_subscribe']);
    $values[$c]['values']['timestp']    = array('label' => $arrNewsletterDateSubscribe['date'].' '.$arrNewsletterDateSubscribe['time']);
  }
  else
    $values[$c]['values']['timestp']    = array('label' => _NEWSLETTER_DELETED);

  $c++;
}
$skin->display_array($columns, $values, 'array_subscriberlist', array('height' => 400, 'sortable' => true, 'orderby_default' => 'email'));
echo $skin->close_simplebloc();
?>