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
 * Affichage de la liste des Newsletters
 *
 * @package newsletter
 * @subpackage newsletter
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$array_columns = array();

//Colonne Titre
$array_columns['auto']['title'] = 
    array(
        'label' => _NEWSLETTER_NAMECOLUMN_TITLE, 
        'options' => array('sort' => true)
    );
//Colonne Status    
$array_columns['right']['status'] = 
    array(
        'label' => _NEWSLETTER_NAMECOLUMN_STATUS, 
        'width' => 170, 
        'options' => array('sort' => true)
    );

if($strNewsletterMenuBlock == 'consult')
{
  // Colonne Création    
  $array_columns['right']['datesend'] = 
      array(
          'label' => _NEWSLETTER_NAMECOLUMN_SEND, 
          'width' => 170, 
          'options' => array('sort' => true)
      );
}
// Colonne Création    
$array_columns['right']['create'] = 
    array(
        'label' => _NEWSLETTER_NAMECOLUMN_CREATE, 
        'width' => 170, 
        'options' => array('sort' => true)
    );
// Colonne "action"
$array_columns['actions_right']['actions'] = 
    array(
        'label' => _NEWSLETTER_NAMECOLUMN_ACTION,
        'width' => 70
    );

$c = 0;
$array_values = array();

// Recupération d'un tableau contenant la liste des newsletters dont le user courant est validateur (vide si aucun, 'all' si validateur global)
$ResultUserIsValidator = newsletter_ListNewsletterIsValidator();

/*
 * recherche la liste des newsletters
 */
$sql = "SELECT letter.id,
               letter.title,
               letter.status,
               letter.timestp,
               letter.author,
               letter.send_timestp,
               letter.send_user
        FROM ploopi_mod_newsletter_letter as letter
        WHERE letter.id_module = {$_SESSION['ploopi']['moduleid']}";
if($strNewsletterMenuBlock == 'consult')
  $sql .= ' AND letter.status = \'send\'';
else
  $sql .= ' AND (letter.status = \'draft\' OR letter.status = \'wait\')';

$sql .= ' AND letter.id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

$select_letter = $db->query($sql);

while ($fields = $db->fetchrow($select_letter))
{
  // Le User courant est un validateur et la letter est en attente de validation ?
  $UserIsValidator = (($ResultUserIsValidator == 'all' || (is_array($ResultUserIsValidator) && in_array($fields['id'],$ResultUserIsValidator))) && $fields['status'] == 'wait');
  
  // date time inscription
  $arrNewsletterDateCreate = ploopi_timestamp2local($fields['timestp']);
  
  //Passage des valeurs au tableau
  $array_values[$c]['values']['title']  = array('label' => htmlentities($fields['title']));
  $array_values[$c]['values']['create'] = array('label' => $arrNewsletterDateCreate['date'].' '.$arrNewsletterDateCreate['time'].'<br/>'.$fields['author']);
  $array_values[$c]['values']['status'] = array('label' => (defined('_NEWSLETTER_LABEL_STATUS_'.strtoupper($fields['status']))) ? constant('_NEWSLETTER_LABEL_STATUS_'.strtoupper($fields['status'])) : 'error');
  // Colonne Envoyé    
  if($strNewsletterMenuBlock == 'consult')
  {
    $arrNewsletterDateSend = ploopi_timestamp2local($fields['send_timestp']);
    $array_values[$c]['values']['datesend'] = array('label' => $arrNewsletterDateSend['date'].' '.$arrNewsletterDateSend['time'].'<br/>'.$fields['send_user']);
  }
  
  //Traitement des actions autorisées
  $action = '';
  $action .= '<img alt="'._NEWSLETTER_LABEL_DISPLAY.'" style="float:left; padding:2px; cursor:pointer;" src="./modules/newsletter/img/viewer.png" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest(\'admin-light.php\',\'ploopi_env=\'+_PLOOPI_ENV+\'&newsletter_menu=consult&id_newsletter='.$fields['id'].'\',false), \'600\', \'\', true,\'newsletter_popup_consult\');" />';
    
  if ((ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY) || $UserIsValidator) && $strNewsletterMenuBlock != 'consult') 
    $action .= '<a title="'._PLOOPI_MODIFY.'" href="'.ploopi_urlencode("admin.php?newsletterToolbarNewsletter=tabNewsletterNew&op=newsletter_modify&id_newsletter={$fields['id']}").'">
                    <img alt="'._PLOOPI_MODIFY.'" style="cursor:pointer;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_edit.png" />
                 </a>';
  if (ploopi_isactionallowed(_NEWSLETTER_ACTION_DELETE) & $strNewsletterMenuBlock != 'consult') 
    $action .= '<a title="'._PLOOPI_DELETE.'" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=newsletter_delete&id_newsletter={$fields['id']}").'\',\''.str_replace('\'','\\\'',_NEWSLETTER_CONFIRM_NEWSLETTER_DELETE).'\');">
                    <img alt="'._PLOOPI_DELETE.'" style="cursor:pointer;" src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" />
                 </a>';
  
  if(!empty($action))
    $array_values[$c]['values']['actions'] = array('label' => $action);
  else
    $array_values[$c]['values']['actions'] = array('label' => '---', 'style' => 'text-align:center;');
  
  if ((ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY) || $UserIsValidator) && $strNewsletterMenuBlock != 'consult') 
    $array_values[$c]['link'] = ploopi_urlencode("admin.php?newsletterToolbarNewsletter=tabNewsletterNew&op=newsletter_modify&id_newsletter={$fields['id']}");
  elseif($strNewsletterMenuBlock == 'consult')
  {
    $array_values[$c]['link'] = 'javascript:void(0);';
      $array_values[$c]['onclick'] = "javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&newsletter_menu=consult&id_newsletter={$fields['id']}',false), '600', '', true,'newsletter_popup_consult');";
  }
  $c++;
}
/*
 * Bloc des newsletter
 */
echo $skin->open_simplebloc(_NEWSLETTER_LABEL_NEWSLETTER_LIST);
$skin->display_array(
    $array_columns, 
    $array_values, 
    'array_newsletterlist', 
    array(
        'sortable' => true, 
        'orderby_default' => 'title'
    )
);
echo $skin->close_simplebloc();
?>