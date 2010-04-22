<?php
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
 * Administration du module Gallery
 *
 * @package gallery
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$tabIndex = 1;

include_once './modules/gallery/class/class_gallery_tpl.php';
$objGalleryTpl = new gallery_tpl();

/**
 * Gestion des OP
 */
switch($op)
{
    // save
    case 'gallery_tpl_save':
        if (isset($_GET['id_tpl'])) $objGalleryTpl->open($_GET['id_tpl']);
        $objGalleryTpl->setvalues($_POST,'gallery_tpl_');
        $objGalleryTpl->save();

        ploopi_redirect('admin.php?id_tpl='.$objGalleryTpl->fields['id'].'&ploopi_mod_msg=_GALLERY_MESS_OK_1');
    break;

    // delete
    case 'gallery_tpl_delete':
        if($objGalleryTpl->open($_GET['id_tpl']))
            $objGalleryTpl->delete();
            
        ploopi_redirect('admin.php?ploopi_mod_msg=2&ploopi_mod_msg=_GALLERY_MESS_OK_2');
    break;
    
    default:
    break;
}

/**
 * Ajout / Modification de nom de bloc
 */
if(isset($_GET['id_tpl']) && is_numeric($_GET['id_tpl'])) // MODIF NOM DE BLOC
{
    $mode = 'modif';
    
    $objGalleryTpl->open($_GET['id_tpl']);
    
    $formParam = '&id_tpl='.$objGalleryTpl->fields['id'];
}
else // NOUVEAU NOM DE BLOC
{
    $mode = 'new';
    
    $objGalleryTpl->init_description();

    $formParam = '';
}

echo $skin->open_simplebloc();
?>
<form name="form_modify_tpl" action="<?php echo ploopi_urlencode('admin.php?op=gallery_tpl_save'.$formParam); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return gallery_tpl_validate(this);">
<div style="padding:2px;">
    <div class="ploopi_form">
        <p>
            <label style="width: 160px;"><?php echo _GALLERY_TPL_LABEL_NAME; ?><sup style="font-size:.7em">*</sup>:</label>
            <input type="text" class="text" name="gallery_tpl_block" value="<?php echo $objGalleryTpl->fields['block']; ?>" tabindex="<?php echo $tabIndex++; ?>" />
        </p>
        <p>
            <label style="width: 160px;"><?php echo _GALLERY_TPL_LABEL_DESCRIPTION; ?>:</label>
            <input type="text" class="text" name="gallery_tpl_description" value="<?php echo $objGalleryTpl->fields['description']; ?>" tabindex="<?php echo $tabIndex++; ?>" />
        </p>
        <p>
            <label style="width: 160px;"><?php echo _GALLERY_TPL_LABEL_NOTE; ?>:</label>
            <textarea class="text" name="gallery_tpl_note" tabindex="<?php echo $tabIndex++; ?>" ><?php echo $objGalleryTpl->fields['note']; ?></textarea>
        </p>
        <fieldset class="fieldset">
            <legend><?php echo _GALLERY_TPL_LEGEND_CSS; ?></legend>
            <div style="padding: 0 4px; margin: 0; font-style: italic;"><?php echo _GALLERY_TPL_TEXT_CSS; ?></div>
            <p>
                <label style="width: 160px;"><?php echo _GALLERY_TPL_LABEL_CSS; ?>:</label>
                <input type="text" class="text" name="gallery_tpl_addtoheadcss" value="<?php echo $objGalleryTpl->fields['addtoheadcss']; ?>" tabindex="<?php echo $tabIndex++; ?>" />
            </p>
            <p>
                <label style="width: 160px;"><?php echo _GALLERY_TPL_LABEL_CSS_IE; ?>:</label>
                <input type="text" class="text" name="gallery_tpl_addtoheadcssie" value="<?php echo $objGalleryTpl->fields['addtoheadcssie']; ?>" tabindex="<?php echo $tabIndex++; ?>" />
            </p>
        </fieldset>
    </div>
</div>
<div style="clear:both; padding:4px; text-align:right;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="<?php echo $tabIndex++; ?>">
    <?php
    if(!empty($formParam))
    {    
        ?>
        <input type="button" class="flatbutton" style="color: #990000;font-weight:bold;" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=gallery_tpl_delete{$formParam}"); ?>','<?php echo _GALLERY_TPL_CONFIRM_DELETE; ?>');" />
        <?php
    } 
    if($mode == 'modif')
    {    
        ?>
        <input type="button" class="flatbutton" value="<?php echo _GALLERY_NEW; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php'); ?>'" />
        <?php
    } 
    ?>
</div>
</form>
<?php
echo $skin->close_simplebloc();


/**
 * Liste des nom de bloc
 */
echo $skin->open_simplebloc(_GALLERY_LABEL_BLOC_TPL_LIST);
 
$array_columns = array();
$array_values = array();

$array_columns['left']['block'] = 
    array(    
        'label' => _GALLERY_ADMIN_TPL_TABLIB_NAME,
        'width' => 150,
        'options' => array('sort' => true)
    );

$array_columns['auto']['description'] = 
    array(    
        'label' => _GALLERY_ADMIN_TPL_TABLIB_DESCRIPTION,
        'options' => array('sort' => true)
    );
    
$array_columns['actions_right']['actions'] = 
    array(
        'label' => '', 
        'width' => 70
    );


$sqlGalleryTpl =  "
        SELECT  *
        FROM    ploopi_mod_gallery_tpl
        WHERE   id_module = {$_SESSION['ploopi']['moduleid']}
        {$sqllimitgroup}
        ORDER BY block DESC
        ";

$resultSqlGalleryTpl = $db->query($sqlGalleryTpl);

$c=0;

while ($fields = $db->fetchrow($resultSqlGalleryTpl))
{
    $open = ploopi_urlencode("admin.php?id_tpl={$fields['id']}");
    $delete = ploopi_urlencode("admin.php?op=gallery_tpl_delete&id_tpl={$fields['id']}");

    $array_values[$c]['values']['block']        = array('label' => $fields['block']);
    $array_values[$c]['values']['description']  = array('label' => $fields['description']);
    $array_values[$c]['values']['actions']      = array('label' => '
        <a href="'.$open.'" title="'._GALLERY_TPL_LIST_MODIFY.'"><img src="./modules/gallery/img/ico_modify.png" alt="'._GALLERY_TPL_LIST_MODIFY.'"></a>
        <a href="javascript:ploopi_confirmlink(\''.$delete.'\',\''._GALLERY_TPL_CONFIRM_DELETE.'\')"><img border="0" src="./modules/gallery/img/ico_trash.png"></a>');

    $array_values[$c]['description'] = "Editer le nom du bloc";
    $array_values[$c]['link'] = $open;
    $c++;
}

$skin->display_array($array_columns, $array_values, 'gallery_tpl_list', array('sortable' => true, 'orderby_default' => 'block'));

echo $skin->close_simplebloc();
?>