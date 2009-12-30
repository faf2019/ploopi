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
 * Création/Edition de galerie
 *
 * @package gallery
 * @subpackage create/modify
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$tabIndex = 0;

$sqlGetTpl = $db->query("SELECT * FROM ploopi_mod_gallery_tpl WHERE id_module = {$_SESSION['ploopi']['moduleid']} {$sqllimitgroup} ORDER BY block");
$arrBlockTpl = $db->getarray($sqlGetTpl);

include_once './modules/gallery/class/class_gallery.php';
$objGallery = new gallery();

if(isset($_GET['id_gallery']) && is_numeric($_GET['id_gallery'])) // MODIF ADHERENT EN DRAFT
{
    $mode = 'modif';
    
    if(isset($_SESSION['ploopi']['gallery']['rep_selected'])) unset($_SESSION['ploopi']['gallery']['rep_selected']);
    
    echo $skin->open_simplebloc();
  
    $objGallery->open($_GET['id_gallery']);
    
    $nom_tpl = $objGallery->fields['template'];
    
    $arrDirSelectTmp = $objGallery->getdirectories();
    if(!empty($arrDirSelectTmp))
    {
        foreach ($arrDirSelectTmp as $key => $dirSelect)
        {
            $arrDirSelect[] = $dirSelect['id_directory'];
            $_SESSION['ploopi']['gallery']['rep_selected'][$dirSelect['id_directory']] = $dirSelect['id_directory'];
        }
    }
    else
        $arrDirSelect = false;
    
    $formParam = '&id_gallery='.$objGallery->fields['id'];
}
else // NOUVELLE GALERIE
{
    $mode = 'new';
    
    echo $skin->open_simplebloc();
  
    $nom_tpl = 'gallery';
    
    $objGallery->init_description();
    $arrDirSelect=false;

    $formParam = '';
}

?>
<form name="form_modify_gallery" action="<?php echo ploopi_urlencode('admin.php?op=gallery_save'.$formParam); ?>" method="POST" enctype="multipart/form-data" onsubmit="javascript:return gallery_validate(this);">
<div style="padding:2px;">
    <?php echo $skin->open_simplebloc(_GALLERY_LEGEND_GALLERY); ?>            
        <div>
            <div class="ploopi_form">
                <p>
                    <label style="width: 115px;"><?php echo _GALLERY_EDIT_LABEL_LABEL; ?><sup style="font-size:.7em">*</sup>:</label>
                    <input type="text" class="text" name="gallery_label" value="<?php echo $objGallery->fields['label']; ?>" style="float: left; width: 180px;" tabindex="<?php echo $tabIndex++; ?>" />
                </p>
                <p>
                    <label style="width: 115px;"><?php echo _GALLERY_EDIT_LABEL_DESCRIPTION; ?>:</label>
                    <input type="text" class="text" name="gallery_description" value="<?php echo $objGallery->fields['description']; ?>" tabindex="<?php echo $tabIndex++; ?>" />
                </p>
                <div style="margin: 0 0 5px 121px; width: 420px;">
                    <fieldset class="fieldset">
                        <legend><?php echo _GALLERY_LEGEND_CARACT; ?></legend>
                        <div style="clear:both; overflow: hidden;">
                            <div style="float: left;">
                                <div id="gallery_caract_left">
                                    <p>
                                        <label style="float: left; width: 200px;"><?php echo _GALLERY_EDIT_LABEL_NB_LINE; ?>:</label>
                                        <input type="text" class="text" name="gallery_nb_line" value="<?php echo $objGallery->fields['nb_line']; ?>" style="width: 50px;" tabindex="<?php echo $tabIndex++; ?>" />
                                    </p>
                                    <p>
                                        <label style="float: left; width: 200px;"><?php echo _GALLERY_EDIT_LABEL_NB_COL; ?>:</label>
                                        <input type="text" class="text" name="gallery_nb_col" value="<?php echo $objGallery->fields['nb_col']; ?>" style="width: 50px;" tabindex="<?php echo $tabIndex++; ?>" />
                                        (0 = nb colonne infini)
                                    </p>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div style="clear: both;">
                            <p>
                                <label style="width: 200px;"><?php echo _GALLERY_EDIT_LABEL_SIZE_THUMB; ?>:</label>
                                <input type="text" class="text" name="gallery_thumb_width" value="<?php echo $objGallery->fields['thumb_width']; ?>" style="width: 50px; float: left;" tabindex="<?php echo $tabIndex++; ?>" />
                                <label style="width: 10px; float: left;">&nbsp;X&nbsp;</label>
                                <input type="text" class="text" name="gallery_thumb_height" value="<?php echo $objGallery->fields['thumb_height']; ?>" style="width: 50px; float: left;" tabindex="<?php echo $tabIndex++; ?>" />
                                &nbsp;pixels
                            </p>
                            <p>
                                <label style="width: 200px;"><?php echo _GALLERY_EDIT_LABEL_COLOR_THUMB; ?>:</label>
                                <input type="text" class="text" name="gallery_thumb_color" id="gallery_thumb_color" value="<?php echo $objGallery->fields['thumb_color']; ?>" style="width: 55px; float: left;" readonly="readonly" tabindex="<?php echo $tabIndex++; ?>" />
                                <a href="javascript:void(0);" style="margin-left:2px;margin-top:2px;float:left;" onclick="javascript:ploopi_colorpicker_open('gallery_thumb_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                            </p>
                            <hr/>
                            <p>
                                <label style="width: 200px;"><?php echo _GALLERY_EDIT_LABEL_SIZE_VIEW; ?>:</label>
                                <input type="text" class="text" name="gallery_view_width" value="<?php echo $objGallery->fields['view_width']; ?>" style="width: 50px; float: left;" tabindex="<?php echo $tabIndex++; ?>" />
                                <label style="width: 10px; float: left;">&nbsp;X&nbsp;</label>
                                <input type="text" class="text" name="gallery_view_height" value="<?php echo $objGallery->fields['view_height']; ?>" style="width: 50px; float: left;" tabindex="<?php echo $tabIndex++; ?>" />
                                &nbsp;pixels
                            </p>
                            <p>
                                <label style="width: 200px;"><?php echo _GALLERY_EDIT_LABEL_COLOR_VIEW; ?>:</label>
                                <input type="text" class="text" name="gallery_view_color" id="gallery_view_color" value="<?php echo $objGallery->fields['view_color']; ?>" style="width: 55px; float: left;" readonly="readonly" tabindex="<?php echo $tabIndex++; ?>" />
                                <a href="javascript:void(0);" style="margin-left:2px;margin-top:2px;float:left;" onclick="javascript:ploopi_colorpicker_open('gallery_view_color', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                            </p>
                        </div>
                    </fieldset>
                </div>
                <div style="margin: 0 0 5px 121px;">
                    <p>
                        <label style="text-align: left; width: 287px;"><?php echo _GALLERY_EDIT_LABEL_TEMPLATE; ?>:</label>
                        <?php
                        if(!empty($arrBlockTpl))
                        {
                            ?>
                            <br/>
                            <select class="select" name="gallery_template" id="gallery_template" style="width: 418px; clear: both;" tabindex="<?php echo $tabIndex++; ?>" onchange="javascript:$('info_gallery').innerHTML = $('gallery_hidden_'+this.value).innerHTML;">
                            <?php 
                            $note = '';
                            $htmlNoteHidden = '';
                            foreach ($arrBlockTpl as $tpl)
                            {
                                $select = ($tpl['id'] == $objGallery->fields['template']) ? 'selected="selected"' : '';
                                if($tpl['id'] == $objGallery->fields['template']) $note = nl2br($tpl['note']);
                                ?>
                                <option value="<?php echo $tpl['id']; ?>" <?php echo $select; ?>><?php echo $tpl['block'].' - '.$tpl['description']; ?></option>
                                <?php
                                $htmlNoteHidden .= '<div id="gallery_hidden_'.$tpl['id'].'" style="display: none;">'.nl2br($tpl['note']).'</div>';
                            } 
                            ?>
                            </select>
                            <div id="info_gallery" style="padding: 2px 4px; font-style: italic; font-weight: bold;"><?php echo $note; ?></div>
                            <?php 
                            echo $htmlNoteHidden;
                        }
                        else
                        {
                            ?>
                            <input type="hidden" name="gallery_template" value="0"/><b>gallery</b>
                            <?php
                        }
                        ?>
                    </p>
                </div>                
            </div>
        </div>
    <?php echo $skin->close_simplebloc(); ?>
</div>
<div style="clear:both; padding:4px; text-align:right;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="<?php echo $tabIndex++; ?>">
    <?php
    if(!empty($formParam))
    {    
        ?>
        <input type="button" class="flatbutton" style="color: #990000;font-weight:bold;" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=gallery_delete{$formParam}"); ?>','<?php echo _GALLERY_EDIT_CONFIRM_DELETE; ?>');" />
        <?php
    } 
    ?>
</div>            
<div style="clear:both; padding:2px;">
    <?php echo $skin->open_simplebloc(_GALLERY_LEGEND_GALLERY_CONTENT); ?>            
        <div style="float:left; width:40%;">
            <div style="padding:2px;">
                <fieldset class="fieldset">
                    <legend><?php echo _GALLERY_LEGEND_DIRECTORIES; ?>&nbsp;<a href="javascript:void(0);" onclick="javascript:gallery_show_preview_rep('all')" style="font-style: italic;" title="<?php echo _GALLERY_EDIT_LABEL_VIEW_ALL; ?>"><img src="./modules/gallery/img/view.png"></a></legend>
                    <div>
                        <?php
                        $sqlDirectories = "SELECT * 
                            FROM ploopi_mod_doc_folder
                            WHERE foldertype = 'public'
                            AND waiting_validation = '0'
                            {$sqllimitgroup}
                            ORDER BY name";

                        $reqDirectories = $db->query($sqlDirectories);
                        if($db->numrows($reqDirectories))
                        {
                            while ($field = $db->fetchrow($reqDirectories))
                            {
                                $parent = true;
                                $arrTmp = &$arrDir;
                                
                                foreach (explode(',',$field['parents']) as $idDir)
                                {
                                    if(!empty($idDir))
                                    {
                                        if($field['id_folder'] == 0 || $parent)
                                        {
                                            if(!isset($arrTmp[$idDir])) $arrTmp[$idDir] = array();
                                            $arrTmp = &$arrTmp[$idDir];
                                        }
                                        else
                                        {
                                            if(!isset($arrTmp[$idDir])) $arrTmp[$idDir] = array();
                                            if(!isset($arrTmp[$idDir]['child'])) $arrTmp[$idDir]['child'] = array();
                                            $arrTmp = &$arrTmp[$idDir]['child'];
                                        }
                                    }    
                                    $parent = false;
                                }
        
                                // On enregistrer les données dans la bonne case grace au pointeur.
                                $arrTmp[$field['id']] = (isset($arrTmp[$field['id']]) ? $arrTmp[$field['id']] + $field : $field);
                                $arrTmp[$field['id']]['dir_selected'] = (!empty($arrDirSelect) && in_array($field['id'],$arrDirSelect));
                            }
                            gallery_show_directories($arrDir);
                        }
                        else
                        {
                            echo _GALLERY_EDIT_LABEL_NO_DIR;
                        }
                        ?>
                    </div>
                </fieldset>
            </div>
        </div>
        <div style="float:left; width:60%;">
            <div style="padding:2px;">
                <fieldset class="fieldset">
                    <legend><?php echo _GALLERY_LEGEND_PHOTOS; ?></legend>
                    <!-- div style="text-align: center;"><a>Voir toutes les photos des répertoires sélectionnés</a></div -->
                    <div id="id_gallery_photos_wait"></div>
                    <div id="id_gallery_photos">
                    </div>
                </fieldset>
            </div>
        </div>
    <?php echo $skin->close_simplebloc(); ?>
</div>
<div style="clear:both; padding:4px; text-align:right;">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" tabindex="<?php echo $tabIndex++; ?>">
    <?php
    if(!empty($formParam))
    {    
        ?>
        <input type="button" class="flatbutton" style="color: #990000;font-weight:bold;" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=gallery_delete{$formParam}"); ?>','<?php echo _GALLERY_EDIT_CONFIRM_DELETE; ?>');" />
        <?php
    } 
    ?>
</div>
</form>

<?php
echo $skin->close_simplebloc();
?>