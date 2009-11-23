<?php
/*
    Copyright (c) 2007-2009 Ovensia
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
 * Interface de modification d'un graphique
 *
 * @package forms
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On commence par vérifier si l'identifiant du graphique est valide.
 * Si ok => on l'ouvre. Sinon, nouveau graphique.
 */

$objGraphic = new forms_graphic();

if (!empty($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
{
    $strTitle = _FORMS_GRAPHICMODIFICATION;
}
else
{
    $objGraphic->init_description();
    $strTitle = _FORMS_GRAPHICCREATION;
}

echo $skin->open_simplebloc($strTitle);

$arrParams = array();
$arrParams[] = "op=forms_graphic_save";
$arrParams[] = "forms_id={$forms->fields['id']}";
if (!$objGraphic->isnew()) $arrParams[] = "forms_graphic_id={$objGraphic->fields['id']}";
?>
<form name="form_field" action="<?php echo ploopi_urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:return forms_field_validate(this);">
<div style="overflow:hidden">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><?php echo _FORMS_GRAPHIC_LABEL; ?>:</label>
                <input type="text" class="text" name="forms_graphic_label" value="<?php echo htmlentities($objGraphic->fields['label']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_GRAPHIC_TYPE; ?>:</label>
                <select class="select" name="forms_graphic_type" onchange="javascript:forms_graphic_type_onchange(this);">
                <?php 
                global $forms_graphic_types;
                foreach($forms_graphic_types as $strKey => $strValue)
                {
                     ?>
                     <option value="<?php echo $strKey; ?>" <?php echo ($strKey == $objGraphic->fields['type']) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($strValue); ?></option>
                     <?php
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_GRAPHIC_DESCRIPTION; ?>:</label>
                <textarea class="text" name="forms_graphic_description"><?php echo htmlentities($objGraphic->fields['description']); ?></textarea>
            </p>
            <p>
                <label>Afficher les données en pourcentage:</label>
                <input type="checkbox" name="forms_graphic_percent" value="1" <?php if ($objGraphic->fields['percent']) echo 'checked="checked"'; ?> style="width:16px;" />
            </p>
        </div>
    </div>
    <div style="float:left;width:49%;">
        <div class="ploopi_form" style="padding:4px;">
            <div id="forms_graphic_pie" style="display:<?php echo in_array($objGraphic->fields['type'], array('pie', 'pie3d')) ? 'block' : 'none'; ?>">
                <fieldset>
                    <legend><?php echo _FORMS_GRAPHIC_DATASET; ?></legend>
                    <p>
                        <label>Champ :</label>
                        <select class="select" name="forms_graphic_pie_field">
                            <option value="">(aucun)</option>
                            <?
                            foreach($arrFields as $intIdField => $arrField)
                            {
                                if (!$arrField['separator'])
                                {
                                    ?>
                                     <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["pie_field"]) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($arrField['name']); ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Couleur de dégradé 1:</label>
                        <input type="text" class="text forms_noselect" name="forms_graphic_pie_color1" id="forms_graphic_pie_color1" value="<?php echo $objGraphic->fields["pie_color1"]; ?>" style="float:left;width:100px;border-bottom-color:<?php echo $objGraphic->fields["pie_color1"]; ?>;border-bottom-width:4px;background-color:#f0f0f0;cursor:pointer;" readonly="readonly" onclick="javascript:ploopi_colorpicker_open('forms_graphic_pie_color1', event);" onchange="javascript:this.style.borderBottomColor = this.value;">
                        <a style="display:block;float:left;margin-left:4px;margin-top:2px;" href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('forms_graphic_pie_color1', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                    </p>
                    <p>
                        <label>Couleur de dégradé 2:</label>
                        <input type="text" class="text forms_noselect" name="forms_graphic_pie_color2" id="forms_graphic_pie_color2" value="<?php echo $objGraphic->fields["pie_color2"]; ?>" style="float:left;width:100px;border-bottom-color:<?php echo $objGraphic->fields["pie_color2"]; ?>;border-bottom-width:4px;background-color:#f0f0f0;cursor:pointer;" readonly="readonly" onclick="javascript:ploopi_colorpicker_open('forms_graphic_pie_color2', event);" onchange="javascript:this.style.borderBottomColor = this.value;">
                        <a style="display:block;float:left;margin-left:4px;margin-top:2px;" href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('forms_graphic_pie_color2', event);"><img src="./img/colorpicker/colorpicker.png" align="top" border="0"></a>
                    </p>
                </fieldset>
            </div>
            
            <div id="forms_graphic_line" style="display:<?php echo in_array($objGraphic->fields['type'], array('line', 'linec', 'bar', 'barc', 'radar', 'radarc')) ? 'block' : 'none'; ?>">
                <p>
                    <label><?php echo _FORMS_GRAPHIC_LINE_AGGREGATION; ?>:</label>
                    <select class="select" name="forms_graphic_line_aggregation">
                    <?php 
                    global $forms_graphic_line_aggregation;
                    foreach($forms_graphic_line_aggregation as $strKey => $strValue)
                    {
                         ?>
                         <option value="<?php echo $strKey; ?>" <?php echo ($strKey == $objGraphic->fields['line_aggregation']) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($strValue); ?></option>
                         <?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>Remplissage (courbes/radars seulement):</label>
                    <input type="checkbox" name="forms_graphic_filled" value="1" <?php if ($objGraphic->fields['filled']) echo 'checked="checked"'; ?> style="width:16px;"/>
                </p>
                <?php
                for ($intI = 1; $intI <= 5; $intI++) 
                {
                    $strDisplay = 'none';
                    if ($intI == 1 || !empty($objGraphic->fields["line{$intI}_field"])) $strDisplay = 'block';
                    ?>
                        <div style="display:<?php echo $strDisplay; ?>;" id="forms_graphic_line<?php echo $intI; ?>_param">
                        <fieldset>
                            <legend><?php echo _FORMS_GRAPHIC_DATASET.' n°'.$intI; ?></legend>
                            <p>
                                <label>Champ :</label>
                                <select class="select" name="forms_graphic_line<?php echo $intI; ?>_field">
                                    <option value="">(aucun)</option>
                                    <?
                                    foreach($arrFields as $intIdField => $arrField)
                                    {
                                        if (!$arrField['separator'])
                                        {
                                            ?>
                                             <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["line{$intI}_field"]) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($arrField['name']); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                            <p>
                                <label>Fitre :</label>
                                <select class="select" style="width:20%;" name="forms_graphic_line<?php echo $intI; ?>_filter_op">
                                    <?php 
                                    global $field_operators;
                                    foreach($field_operators as $strKey => $strValue)
                                    {
                                         ?>
                                         <option value="<?php echo $strKey; ?>" <?php echo ($strKey == $objGraphic->fields["line{$intI}_filter_op"]) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($strValue); ?></option>
                                         <?php
                                    }
                                    ?>
                                </select>                            
                                <input style="width:43%;" type="text" class="text" name="forms_graphic_line<?php echo $intI; ?>_filter_value" id="forms_graphic_line<?php echo $intI; ?>_filter_value" value="<?php echo $objGraphic->fields["line{$intI}_filter_value"]; ?>">
                            </p>
                            <p>
                                <label>Opération :</label>
                                <select class="select" name="forms_graphic_line<?php echo $intI; ?>_operation">
                                    <?php 
                                    global $forms_graphic_operation;
                                    foreach($forms_graphic_operation as $strKey => $strValue)
                                    {
                                         ?>
                                         <option value="<?php echo $strKey; ?>" <?php echo ($strKey == $objGraphic->fields["line{$intI}_operation"]) ? 'selected="selected"' : ''; ?>><?php echo htmlentities($strValue); ?></option>
                                         <?php
                                    }
                                    ?>
                                </select>
                            </p>
                            <p>
                                <label>Couleur :</label>
                                <input type="text" class="text forms_noselect" name="forms_graphic_line<?php echo $intI; ?>_color" id="forms_graphic_line<?php echo $intI; ?>_color" value="<?php echo $objGraphic->fields["line{$intI}_color"]; ?>" style="float:left;width:100px;border-bottom-color:<?php echo $objGraphic->fields["line{$intI}_color"]; ?>;border-bottom-width:4px;background-color:#f0f0f0;cursor:pointer;" readonly="readonly" onclick="javascript:ploopi_colorpicker_open('forms_graphic_line<?php echo $intI; ?>_color', event);" onchange="javascript:this.style.borderBottomColor = this.value;">
                                <a style="display:block;float:left;margin-left:4px;margin-top:2px;" href="javascript:void(0);" onclick="javascript:ploopi_colorpicker_open('forms_graphic_line<?php echo $intI; ?>_color', event);"><img src="./img/colorpicker/colorpicker.png" style="display:block;"></a>
                            </p>
                        </fieldset>
                    </div>
                    <?php
                    
                    if ($strDisplay == 'none')
                    { 
                        ?>
                        <div style="display:block;" id="forms_graphic_line<?php echo $intI; ?>_link"><a href="javascript:void(0);" onclick="javascript:$('forms_graphic_line<?php echo $intI; ?>_link').style.display = 'none'; $('forms_graphic_line<?php echo $intI; ?>_param').style.display = 'block';">Paramétrer <?php echo _FORMS_GRAPHIC_DATASET.' n°'.$intI; ?></a></div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>    
</div>

<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_modify&forms_id={$forms->fields['id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<?php
echo $skin->close_simplebloc();
?>
