<?
/*
  Copyright (c) 2009 HeXad

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
 * Liste des galeries disponibles
 *
 * @package gallery
 * @subpackage list
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

echo $skin->open_simplebloc(_GALLERY_TABLIB_LIST);
 
$array_columns = array();
$array_values = array();

$array_columns['left']['label'] = 
    array(    
        'label' => _GALLERY_LIST_LABEL,
        'width' => 300,
        'options' => array('sort' => true)
    );

$array_columns['auto']['desc'] = 
    array(    
        'label' => _GALLERY_LIST_DESCRIPTION,
        'options' => array('sort' => true)
    );


$array_columns['actions_right']['actions'] = 
    array(
        'label' => '', 
        'width' => 70
    );


$sqlGallery =  "
        SELECT  *
        FROM    ploopi_mod_gallery
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        {$sqllimitgroup}
        ORDER BY label DESC
        ";

$resultSqlGallery = $db->query($sqlGallery);

$c=0;

while ($fields = $db->fetchrow($resultSqlGallery))
{
    $clone = ploopi_urlencode("admin.php?op=gallery_clone&id_gallery={$fields['id']}");
    $open = ploopi_urlencode("admin.php?op=gallery_modify&id_gallery={$fields['id']}");
    $delete = ploopi_urlencode("admin.php?op=gallery_delete&id_gallery={$fields['id']}");

    $array_values[$c]['values']['label']        = array('label' => ploopi_htmlentities($fields['label']));
    $array_values[$c]['values']['desc']         = array('label' => ploopi_htmlentities($fields['description']));
    $array_values[$c]['values']['actions']      = array('label' => '
        <a href="'.$clone.'" title="'._GALLERY_LIST_CLONE.'"><img src="./modules/gallery/img/ico_clone.png" alt="'._GALLERY_LIST_CLONE.'"></a>
        <a href="'.$open.'" title="'._GALLERY_LIST_MODIFY.'"><img src="./modules/gallery/img/ico_modify.png" alt="'._GALLERY_LIST_MODIFY.'"></a>
        <a href="javascript:ploopi_confirmlink(\''.$delete.'\',\''._GALLERY_LIST_CONFIRM_DELETE.'\')"><img border="0" src="./modules/gallery/img/ico_trash.png"></a>');

    $array_values[$c]['description'] = "Ouvrir le Formulaire";
    $array_values[$c]['link'] = $open;
    $c++;
}

$skin->display_array($array_columns, $array_values, 'gallery_list', array('sortable' => true, 'orderby_default' => 'label'));
 
 
 echo $skin->close_simplebloc();
 ?>