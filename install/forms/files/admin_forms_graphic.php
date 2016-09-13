<?php
/*
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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
 * Constantes
 */
include_once './modules/forms/jpgraph/jpgraph.php';
include_once './modules/forms/jpgraph/jpgraph_ttf.inc.php';

/**
 * On commence par vérifier si l'identifiant du graphique est valide.
 * Si ok => on l'ouvre. Sinon, nouveau graphique.
 */

$objGraphic = new formsGraphic();

if (!empty($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
{
    $strTitle = _FORMS_GRAPHICMODIFICATION;
}
else
{
    $objGraphic->init_description();
    $objGraphic->fields['type'] = 'line';

    $objGraphic->fields['param_font'] = FF_VERA;
    $objGraphic->fields['param_transparency'] = '0.2';
    $objGraphic->fields['param_fill_transparency'] = '0.5';
    $objGraphic->fields['param_margin_left'] = '40';
    $objGraphic->fields['param_margin_right'] = '20';
    $objGraphic->fields['param_margin_top'] = '120';
    $objGraphic->fields['param_margin_bottom'] = '60';
    $objGraphic->fields['param_center_x'] = '0.5';
    $objGraphic->fields['param_center_y'] = '0.5';
    $objGraphic->fields['param_shadow_transparency'] = '0.8';
    $objGraphic->fields['param_label_angle'] = '0';
    $objGraphic->fields['param_font_size_title'] = '15';
    $objGraphic->fields['param_font_size_legend'] = '8';
    $objGraphic->fields['param_font_size_data'] = '10';
    $objGraphic->fields['param_mark_type'] = MARK_SQUARE;
    $objGraphic->fields['param_mark_transparency'] = '0.3';
    $objGraphic->fields['param_mark_width'] = '3';

    $strTitle = _FORMS_GRAPHICCREATION;
}

echo $skin->open_simplebloc($strTitle);

$arrParams = array();
$arrParams[] = "ploopi_op=forms_graphic_save";
$arrParams[] = "forms_id={$objForm->fields['id']}";
if (!$objGraphic->isnew()) $arrParams[] = "forms_graphic_id={$objGraphic->fields['id']}";
?>
<form name="form_field" action="<?php echo ovensia\ploopi\crypt::urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post"  onsubmit="javascript:return forms_graphic_validate(this);">
<div style="overflow:hidden">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><?php echo _FORMS_GRAPHIC_LABEL; ?>:</label>
                <input type="text" class="text" name="forms_graphic_label" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['label']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_GRAPHIC_TYPE; ?>:</label>
                <select class="select" name="forms_graphic_type" onchange="javascript:forms_graphic_type_onchange(this);">
                <?php
                global $forms_graphic_types;
                foreach($forms_graphic_types as $strKey => $strValue)
                {
                     ?>
                     <option value="<?php echo ovensia\ploopi\str::htmlentities($strKey); ?>" <?php echo ($strKey == $objGraphic->fields['type']) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($strValue); ?></option>
                     <?php
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_GRAPHIC_DESCRIPTION; ?>:</label>
                <textarea class="text" name="forms_graphic_description"><?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['description']); ?></textarea>
            </p>
            <p>
                <label>Afficher les données en pourcentage:</label>
                <input type="checkbox" name="forms_graphic_percent" value="1" <?php if ($objGraphic->fields['percent']) echo 'checked="checked"'; ?> style="width:16px;" />
            </p>

            <p class="ploopi_va forms_param_link" id="forms_graphic_params_link" style="display:block;">
                <a onclick="javascript:$('forms_graphic_params_link').style.display = 'none'; $('forms_graphic_params').style.display = 'block';" href="javascript:void(0);">
                <img src="./modules/forms/img/arrow_down.png" />
                Paramètres avancés
                </a>
            </p>

            <div id="forms_graphic_params" style="display:none;">
                <fieldset>
                    <legend>Paramètres généraux</legend>
                    <p>
                        <label>Police de caractère:</label>
                        <?php
                        $arrFonts = array(
                            FF_VERA => 'Vera',
                            FF_VERAMONO => 'Vera Mono',
                            FF_VERASERIF => 'Vera Serif',
                            FF_DV_SERIF => 'DejaVu Serif',
                            FF_DV_SANSSERIF => 'DejaVu Sans',
                            FF_DV_SANSSERIFMONO => 'DejaVu Sans Mono',
                            FF_DV_SANSSERIF => 'DejaVu Sans'
                        );
                        ?>
                        <select class="select" name="forms_graphic_param_font">
                            <?php
                            foreach($arrFonts as $key => $value)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_font'] == $key) echo 'selected="selected"'; ?> value="<?php echo ovensia\ploopi\str::htmlentities($key); ?>"><?php echo ovensia\ploopi\str::htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Taille du titre:</label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_font_size_title" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_font_size_title']); ?>" />
                    </p>
                    <p>
                        <label>Taille de la légende:</label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_font_size_legend" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_font_size_legend']); ?>" />
                    </p>
                    <p>
                        <label>Taille des données:</label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_font_size_data" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_font_size_data']); ?>" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Paramètres Courbes/Histogrammes</legend>
                    <p>
                        <label>Transparence du remplissage (%):</label>
                        <select class="select" name="forms_graphic_param_fill_transparency" style="width:80px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_fill_transparency'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Transparence des contours (%):</label>
                        <select class="select" name="forms_graphic_param_transparency" style="width:80px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_transparency'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Marges (px):<br /><em>Gauche, Droite, Haut, Bas</em></label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_margin_left" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_margin_left']); ?>" />
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_margin_right" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_margin_right']); ?>" />
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_margin_top" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_margin_top']); ?>" />
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_margin_bottom" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_margin_bottom']); ?>" />
                    </p>
                    <p>
                        <label>Rotation des libellés (%):</label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_label_angle" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_label_angle']); ?>" />
                    </p>
                </fieldset>
                <fieldset>
                    <legend>Paramètres Histogrammes</legend>
                    <p>
                        <label>Ombre portée, transparence (%):</label>
                        <select class="select" name="forms_graphic_param_shadow_transparency" style="width:80px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_shadow_transparency'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                </fieldset>

                <fieldset>
                    <legend>Paramètres Secteurs/Radars</legend>
                    <p>
                        <label>Centre (%):<br /><em>X, Y</em></label>
                        <select class="select" name="forms_graphic_param_center_x" style="width:80px;margin-right:2px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_center_x'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                        <select class="select" name="forms_graphic_param_center_y" style="width:80px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_center_y'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                </fieldset>
                <?php
                $arrMarks = array(
                    MARK_SQUARE => 'Carré',
                    MARK_CIRCLE => 'Cercle vide',
                    MARK_FILLEDCIRCLE => 'Cercle plein',
                    MARK_UTRIANGLE => 'Triangle vers le haut',
                    MARK_DTRIANGLE => 'Triangle vers le bas',
                    MARK_LEFTTRIANGLE => 'Triangle vers la gauche',
                    MARK_RIGHTTRIANGLE => 'Triangle vers la droite',
                    MARK_DIAMOND => 'Losange',
                    MARK_CROSS => 'Croix',
                    MARK_X => 'Croix 2',
                    MARK_STAR => 'Etoile',
                    MARK_FLASH => 'Eclair',
                );
                ?>
                <fieldset>
                    <legend>Paramètres Courbes/Radars</legend>
                    <p>
                        <label>Type de marqueur:</label>
                        <select class="select" name="forms_graphic_param_mark_type">
                            <?php
                            foreach($arrMarks as $key => $value)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_mark_type'] == $key) echo 'selected="selected"'; ?> value="<?php echo ovensia\ploopi\str::htmlentities($key); ?>"><?php echo ovensia\ploopi\str::htmlentities($value); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Largeur des marqueurs (px):</label>
                        <input type="text" class="text" style="width:40px;margin-right:2px;" name="forms_graphic_param_mark_width" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields['param_mark_width']); ?>" />
                    </p>
                    <p>
                        <label>Transparence des marqueurs (%):</label>
                        <select class="select" name="forms_graphic_param_mark_transparency" style="width:80px;">
                            <?php
                            for ($i=0;$i<=10;$i++)
                            {
                                ?>
                                <option <?php if ($objGraphic->fields['param_mark_transparency'] == $i/10) echo 'selected="selected"'; ?> value="<?php echo $i/10; ?>"><?php echo $i*10; ?> %</option>
                                <?php
                            }
                            ?>
                        </select>
                    </p>
                </fieldset>
            </div>
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
                            <?php
                            foreach($arrFields as $intIdField => $arrField)
                            {
                                if (!$arrField['separator'] && !$arrField['captcha'] && !$arrField['html'])
                                {
                                    ?>
                                     <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["pie_field"]) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($arrField['name']); ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </p>
                    <p>
                        <label>Couleur de dégradé 1:</label>
                        <input type="text" class="text forms_noselect color {hash:true}" name="forms_graphic_pie_color1" id="forms_graphic_pie_color1" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields["pie_color1"]); ?>" style="float:left;width:100px;cursor:pointer;" readonly="readonly">
                    </p>
                    <p>
                        <label>Couleur de dégradé 2:</label>
                        <input type="text" class="text forms_noselect color {hash:true}" name="forms_graphic_pie_color2" id="forms_graphic_pie_color2" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields["pie_color2"]); ?>" style="float:left;width:100px;cursor:pointer;" readonly="readonly">
                    </p>
                </fieldset>
            </div>
            <div id="forms_graphic_line" style="display:<?php echo in_array($objGraphic->fields['type'], array('line', 'linec', 'bar', 'barc', 'radar', 'radarc')) ? 'block' : 'none'; ?>">
                <p>
                    <label>Trame de temps:</label>
                    <select class="select" name="forms_graphic_timefield">
                        <option value="0">Date de validation</option>
                        <?php
                        foreach($arrFields as $intIdField => $arrField)
                        {
                            if (!$arrField['separator'] && !$arrField['captcha'] && !$arrField['html'] && $arrField['type'] == 'text' && $arrField['format'] == 'date')
                            {
                                ?>
                                 <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["timefield"]) ? 'selected="selected"' : ''; ?>>Champ "<?php echo ovensia\ploopi\str::htmlentities($arrField['name']); ?>"</option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_GRAPHIC_LINE_AGGREGATION; ?>:</label>
                    <select class="select" name="forms_graphic_line_aggregation">
                    <?php
                    global $forms_graphic_line_aggregation;
                    foreach($forms_graphic_line_aggregation as $strKey => $strValue)
                    {
                         ?>
                         <option value="<?php echo ovensia\ploopi\str::htmlentities($strKey); ?>" <?php echo ($strKey == $objGraphic->fields['line_aggregation']) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($strValue); ?></option>
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
                        <div style="display:<?php echo ovensia\ploopi\str::htmlentities($strDisplay); ?>;" id="forms_graphic_line<?php echo $intI; ?>_param">
                        <fieldset>
                            <legend><?php echo _FORMS_GRAPHIC_DATASET.' n°'.$intI; ?></legend>
                            <p>
                                <label>Filtre :</label>
                                <select class="select" name="forms_graphic_line<?php echo $intI; ?>_filter">
                                    <option value="">(aucun)</option>
                                    <?php
                                    foreach($arrFields as $intIdField => $arrField)
                                    {
                                        if (!$arrField['separator'] && !$arrField['captcha'] && !$arrField['html'])
                                        {
                                            ?>
                                             <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["line{$intI}_filter"]) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($arrField['name']); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                            <p>
                                <label>&nbsp;</label>
                                <select class="select" style="width:20%;" name="forms_graphic_line<?php echo $intI; ?>_filter_op">
                                    <option value="">(aucun)</option>
                                    <?php
                                    global $field_operators;
                                    foreach($field_operators as $strKey => $strValue)
                                    {
                                         ?>
                                         <option value="<?php echo ovensia\ploopi\str::htmlentities($strKey); ?>" <?php echo ($strKey == $objGraphic->fields["line{$intI}_filter_op"]) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($strValue); ?></option>
                                         <?php
                                    }
                                    ?>
                                </select>
                                <input style="width:43%;" type="text" class="text" name="forms_graphic_line<?php echo $intI; ?>_filter_value" id="forms_graphic_line<?php echo $intI; ?>_filter_value" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields["line{$intI}_filter_value"]); ?>">
                            </p>
                            <p>
                                <label>Champ affiché :</label>
                                <select class="select" name="forms_graphic_line<?php echo $intI; ?>_field">
                                    <option value="">(aucun)</option>
                                    <?php
                                    foreach($arrFields as $intIdField => $arrField)
                                    {
                                        if (!$arrField['separator'] && !$arrField['captcha'] && !$arrField['html'])
                                        {
                                            ?>
                                             <option value="<?php echo $intIdField; ?>" <?php echo ($intIdField == $objGraphic->fields["line{$intI}_field"]) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($arrField['name']); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                            <p>
                                <label>Opération :</label>
                                <select class="select" name="forms_graphic_line<?php echo $intI; ?>_operation">
                                    <?php
                                    global $forms_graphic_operation;
                                    foreach($forms_graphic_operation as $strKey => $strValue)
                                    {
                                         ?>
                                         <option value="<?php echo ovensia\ploopi\str::htmlentities($strKey); ?>" <?php echo ($strKey == $objGraphic->fields["line{$intI}_operation"]) ? 'selected="selected"' : ''; ?>><?php echo ovensia\ploopi\str::htmlentities($strValue); ?></option>
                                         <?php
                                    }
                                    ?>
                                </select>
                            </p>
                            <p>
                                <label>Couleur :</label>
                                <input type="text" class="text forms_noselect color {hash:true}" name="forms_graphic_line<?php echo $intI; ?>_color" id="forms_graphic_line<?php echo $intI; ?>_color" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields["line{$intI}_color"]); ?>" style="float:left;width:100px;cursor:pointer;" readonly="readonly" />
                            </p>
                            <p>
                                <label>Légende (optionnelle) :</label>
                                <input type="text" class="text" name="forms_graphic_line<?php echo $intI; ?>_legend" value="<?php echo ovensia\ploopi\str::htmlentities($objGraphic->fields["line{$intI}_legend"]); ?>" />
                            </p>

                        </fieldset>
                    </div>
                    <?php

                    if ($strDisplay == 'none')
                    {
                        ?>
                        <p class="ploopi_va forms_param_link" id="forms_graphic_line<?php echo $intI; ?>_link" style="display:block;">
                            <a href="javascript:void(0);" onclick="javascript:$('forms_graphic_line<?php echo $intI; ?>_link').style.display = 'none'; $('forms_graphic_line<?php echo $intI; ?>_param').style.display = 'block';">
                            <img src="./modules/forms/img/arrow_down.png" />
                            Paramétrer <?php echo _FORMS_GRAPHIC_DATASET.' n°'.$intI; ?>
                            </a>
                        </p>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ovensia\ploopi\crypt::urlencode("admin.php?op=forms_modify&forms_id={$objForm->fields['id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<?php
echo $skin->close_simplebloc();
?>
