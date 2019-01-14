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
 * @subpackage admin
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
//Colonne date send
$array_columns['right']['send'] =
    array(
        'label' => _NEWSLETTER_NAMECOLUMN_SEND,
        'width' => 170,
        'options' => array('sort' => true)
    );
//Colonne date creation
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

$sql = "SELECT letter.id,
               letter.title,
               letter.status,
               letter.timestp,
               letter.author,
               letter.send_timestp,
               letter.send_user
        FROM ploopi_mod_newsletter_letter as letter
        WHERE letter.id_module = '{$_SESSION['ploopi']['moduleid']}'
          AND letter.status = 'send'
          AND letter.id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

$select_letter = $db->query($sql);

while ($fields = $db->fetchrow($select_letter))
{
  // date time create et envoi
  $arrNewsletterDateCreate = ploopi_timestamp2local($fields['timestp']);
  $arrNewsletterDateSend = ploopi_timestamp2local($fields['send_timestp']);

  $array_values[$c]['values']['title']  = array('label' => ploopi_htmlentities($fields['title']));
  $array_values[$c]['values']['create'] = array('label' => ploopi_htmlentities($arrNewsletterDateCreate['date'].' '.$arrNewsletterDateCreate['time']).'<br/>'.ploopi_htmlentities($fields['author']));
  $array_values[$c]['values']['send'] = array('label' => ploopi_htmlentities($arrNewsletterDateSend['date'].' '.$arrNewsletterDateSend['time']).'<br/>'.ploopi_htmlentities($fields['send_user']));

  // traitement des actions dispo
  $action = '';
  $action .= '<img alt="'._NEWSLETTER_LABEL_DISPLAY.'" style="float:left; padding:2px; cursor:pointer;" src="./modules/newsletter/img/viewer.png" onclick="javascript:ploopi_showpopup(ploopi_xmlhttprequest(\'admin-light.php\',\'ploopi_env=\'+_PLOOPI_ENV+\'&newsletter_menu=consult&id_newsletter='.$fields['id'].'\',false), \'600\', \'\', true,\'newsletter_popup_consult\');" />';

  $action .= '<a title="'._NEWSLETTER_LABEL_GENERATE_PDF.'" href="'.ploopi_urlencode("admin.php?op=newsletter_pdf&id_newsletter={$fields['id']}").'">
                    <img alt="'._NEWSLETTER_LABEL_GENERATE_PDF.'" style="cursor:pointer;" src="./modules/newsletter/img/pdf.png" />
              </a>';

  $action .= '<a title="'._NEWSLETTER_LABEL_LIST_TO.'" href="'.ploopi_urlencode("admin.php?op=newsletter_list_to&id_newsletter={$fields['id']}").'">
                    <img alt="'._NEWSLETTER_LABEL_LIST_TO.'" style="cursor:pointer;" src="./modules/newsletter/img/destin.png" />
              </a>';

  if(!empty($action))
    $array_values[$c]['values']['actions'] = array('label' => $action);
  else
    $array_values[$c]['values']['actions'] = array('label' => '---', 'style' => 'text-align:center;');

  $array_values[$c]['link'] = 'javascript:void(0);';
  $array_values[$c]['onclick'] = "javascript:ploopi_showpopup(ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&newsletter_menu=consult&id_newsletter={$fields['id']}',false), '600', '', true,'newsletter_popup_consult');";
  $c++;
}
/*
 * Bloc des newsletter
 */
echo $skin->open_simplebloc(_NEWSLETTER_LABEL_NEWSLETTER_LIST);
if(isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletter_return_send']))
{
  echo '<div style="padding:4px; background-color:#e0e0e0; clear:both; border-width: 1px 0;border-color:#c0c0c0; border-style:solid; text-align: center; font-weight: bold;">'.
        $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletter_return_send'].
       '</div>';
  unset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletter_return_send']);
}
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