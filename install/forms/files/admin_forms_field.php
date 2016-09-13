<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Interface de modification d'un champ
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Includes
 */

include_once './modules/forms/classes/formsArithmeticParser.php';

/**
 * On commence par vérifier si l'identifiant du champ est valide.
 * Si ok => on l'ouvre. Sinon, nouveau champ.
 */

$field = new formsField();

if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']) && $field->open($_GET['field_id']))
{
    $title = _FORMS_FIELDMODIFICATION;
}
else
{
    $field->init_description();
    $field->fields['option_formview'] = 1;
    $field->fields['option_arrayview'] = 1;
    $field->fields['option_exportview'] = 1;
    $title = _FORMS_FIELDCREATION;
}

echo $skin->open_simplebloc($title);

$arrParams = array();
$arrParams[] = "ploopi_op=forms_field_save";
$arrParams[] = "forms_id={$_GET['forms_id']}";
if (!$field->new) $arrParams[] = "field_id={$field->fields['id']}";
?>

<form name="form_field" action="<?php echo ovensia\ploopi\crypt::urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:return forms_field_validate(this);">
<input type="hidden" name="field_values" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['values']); ?>">
<div style="overflow:hidden">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><?php echo _FORMS_FIELD_POSITION; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_position" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['position']); ?>" />
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_INTERLINE; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_interline" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['interline']); ?>" />
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_GROUP; ?>:</label>
                <select class="select" name="field_id_group">
                    <option value="0">(Aucun)</option>
                    <?php
                    foreach($objForm->getGroups() as $intIdGroup => $objGroup)
                    {
                        ?>
                        <option value="<?php echo $intIdGroup; ?>" <?php if ($field->fields['id_group'] == $intIdGroup) echo 'selected="selected"'; ?>><?php echo ovensia\ploopi\str::htmlentities($objGroup->fields['label']); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_NAME; ?>:</label>
                <input type="text" class="text" name="field_name" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['name']); ?>" />
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_FIELDNAME; ?>:</label>
                <input type="text" class="text" name="field_fieldname" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['fieldname']); ?>" maxlength="64" />
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_TYPE; ?>:</label>
                <select class="select" name="field_type" onchange="javascript:forms_display_fieldvalues();forms_display_fieldformats();forms_display_tablelink();forms_display_calculation();">
                <?php
                foreach($field_types as $key => $value)
                {
                    $sel = ($field->fields['type'] == $key) ? 'selected' : '';
                    echo "<option $sel value=\"{$key}\">".ovensia\ploopi\str::htmlentities($value)."</option>";
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DESCRIPTION; ?>:</label>
                <textarea class="text" style="height:40px;" name="field_description"><?php echo ovensia\ploopi\str::htmlentities($field->fields['description']); ?></textarea>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DEFAULTVALUE; ?>:</label>
                <input type="text" class="text" size="30" name="field_defaultvalue" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['defaultvalue']); ?>" />
            </p>
        </div>
    </div>


    <div style="float:left;width:49%;">
        <div style="padding:4px;">
            <div id="fieldformats" class="ploopi_form" style="display:block;">
                <p>
                    <label><?php echo _FORMS_FIELD_FORMAT; ?>:</label>
                    <select class="select" name="field_format">
                    <?php
                    foreach($field_formats as $key => $value)
                    {
                        $sel = ($field->fields['format'] == $key) ? 'selected' : '';
                        echo "<option $sel value=\"{$key}\">".ovensia\ploopi\str::htmlentities($value)."</option>";
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_FIELD_MAXLENGTH; ?>:</label>
                    <input type="text" class="text" style="width:50px;" name="field_maxlength" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['maxlength']); ?>" />
                </p>
            </div>

            <div id="fieldvalues" class="ploopi_form" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_VALUES; ?>:</label>
                    <span>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                            <select name="f_values" class="select" size="10" style="width:230px;height:100px;" onclick="javascript:document.form_field.newvalue.value=this.value;document.form_field.newvalue.focus();">
                            <?php
                            if ($field->fields['type'] == 'radio' || $field->fields['type'] == 'select' || $field->fields['type'] == 'checkbox' || $field->fields['type'] == 'color')
                            {
                                foreach(explode('||',$field->fields['values']) as $value)
                                {
                                    if ($value != '')
                                    {
                                        if ($field->fields['type'] == 'color') echo "<option value=\"$value\" style=\"background-color:$value;\"></option>";
                                        else echo "<option value=\"$value\">$value</option>";

                                    }
                                }
                            }
                            ?>
                            </select>
                            </td>
                            <td valign="top">
                                <input style="width:25px;margin:5px;" type="button" class="button" value="+" onclick="javascript:forms_field_move_value(document.form_field.f_values,1)" />
                                <br />
                                <input style="width:25px;margin:5px;" type="button" class="button" value="-" onclick="javascript:forms_field_move_value(document.form_field.f_values,-1)" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                            <input style="width:250px;margin:5px 0px 5px 0px;" name="newvalue" type="text" class="text" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_ADD; ?>" onclick="javascript:forms_field_add_value(document.form_field.f_values, document.form_field.newvalue)" />
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_MODIFY; ?>" onclick="javascript:forms_field_modify_value(document.form_field.f_values, document.form_field.newvalue)" />
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:forms_field_delete_value(document.form_field.f_values)" />
                            </td>
                        </tr>
                        </table>
                    </span>
                </p>
            </div>

            <div id="tablelink" class="ploopi_form" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_FORMFIELD; ?>:</label>
                    <select class="select" name="f_formfield" style="width:200px;">
                    <?php

                    $db->query( "
                                SELECT  forms.label, field.*
                                FROM    ploopi_mod_forms_form forms,
                                        ploopi_mod_forms_field field
                                WHERE   forms.id = field.id_form
                                AND     field.separator = 0
                                AND     field.captcha = 0
                                AND     field.html = 0
                                ORDER BY label, position
                                ");

                    $strTable = '';
                    while ($row = $db->fetchrow())
                    {
                        if ($row['label'] != $strTable)
                        {
                            if (!empty($strTable)) echo '</optgroup>';
                            $strTable = $row['label'];
                            echo '<optgroup label="'.ovensia\ploopi\str::htmlentities($strTable).'">';
                        }

                        $strFormat = isset($field_formats[$row['format']]) ? " ({$field_formats[$row['format']]})" : '';
                        ?>
                        <option value="<?php echo $row['id']; ?>" <?php if ($field->fields['values'] == $row['id']) echo 'selected="selected"'; ?>>
                        <?php echo ovensia\ploopi\str::htmlentities("{$row['name']}{$strFormat}"); ?>
                        </option>
                        <?php
                    }
                    if (!empty($strTable)) echo '</optgroup>';

                    ?>
                    </select>
                </p>
            </div>

            <div id="calculation" class="ploopi_form" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_FORMULA_EDITOR; ?>:</label>
                    <select class="select" onchange="javascript:forms_setcolumn(this);">
                    <option value="">(Choisissez un champ du formulaire)</option>
                    <?php
                    // Pour chaque champ du formulaire
                    foreach($objForm->getFields() as $objField)
                    {
                        // On ne peut pas utiliser le champ courant dans la formule
                        if ($objField->fields['id'] != $field->fields['id'] && ($objField->fields['type'] == 'calculation' || in_array($objField->fields['format'], array('integer', 'float'))))
                        {
                            ?>
                            <optgroup label="<?php echo ovensia\ploopi\str::htmlentities("C{$objField->fields['position']} - {$objField->fields['name']}"); ?>">
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>"><?php echo "Valeur"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_CNT"><?php echo "Nombre"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_SUM"><?php echo "Somme"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_MIN"><?php echo "Min"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_MAX"><?php echo "Max"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_AVG"><?php echo "Moyenne"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_STD"><?php echo "Ecart-type"; ?></option>
                            <option value="<?php echo ovensia\ploopi\str::htmlentities($objField->fields['position']); ?>_VAR"><?php echo "Variance"; ?></option>
                            </optgroup>
                            <?php
                        }
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>&nbsp;</label>
                    <select class="select" onchange="javascript:forms_setfunction(this);">
                    <option value="">(Choisissez une fonction)</option>
                    <?php
                    // Pour chaque fonction
                    foreach(formsArithmeticParser::getFunctionsDef() as $strFunction => $strDef)
                    {
                        ?><option value="<?php echo ovensia\ploopi\str::htmlentities($strFunction); ?>"><?php echo ovensia\ploopi\str::htmlentities($strDef); ?></option><?php
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label>&nbsp;</label>
                    <?php
                    // Pour chaque fonction
                    foreach(formsArithmeticParser::getOperatorsDef() as $strOperator => $strDef)
                    {
                        ?><input type="button" class="button" value="<?php echo ovensia\ploopi\str::htmlentities($strOperator); ?>" style="width:25px;margin-right:2px;" title="<?php echo ovensia\ploopi\str::htmlentities($strDef); ?>" onclick="javascript:forms_setoperator(this);" /><?php
                    }
                    ?><input type="button" class="button" value="(" style="width:25px;margin-right:2px;" onclick="javascript:forms_setoperator(this);" /><input type="button" class="button" value=")" style="width:25px;margin-right:2px;" onclick="javascript:forms_setoperator(this);" />
                </p>
                <p>
                    <label>&nbsp;</label>
                    <textarea class="text" name="field_formula" id="field_formula"><?php echo ovensia\ploopi\str::htmlentities($field->fields['formula']); ?></textarea>
                </p>
            </div>

            <div id="fieldstyles" class="ploopi_form" style="display:block;">
                <p>
                    <label><?php echo _FORMS_FIELD_STYLE_FORM; ?>:</label>
                    <input type="text" class="text" name="field_style_form" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['style_form']); ?>" />
                </p>
                <p>
                    <label><?php echo _FORMS_FIELD_STYLE_FIELD; ?>:</label>
                    <input type="text" class="text" name="field_style_field" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['style_field']); ?>" />
                </p>
                <p>
                    <label><?php echo _FORMS_FIELD_EXPORT_WIDTH; ?>:</label>
                    <input type="text" class="text" name="field_export_width" value="<?php echo ovensia\ploopi\str::htmlentities($field->fields['export_width']); ?>" />
                </p>
            </div>

        </div>
    </div>

    <p style="clear:both;padding:0 0 4px 4px;" class="ploopi_va">
        <input type="checkbox" class="checkbox" name="field_option_formview" id="field_option_formview" value="1" <?php if ($field->fields['option_formview']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_formview').checked = !$('field_option_formview').checked;"><?php echo _FORMS_FIELD_FORMVIEW; ?></span>

        <input type="checkbox" class="checkbox" name="field_option_arrayview" id="field_option_arrayview" value="1" <?php if ($field->fields['option_arrayview']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_arrayview').checked = !$('field_option_arrayview').checked;"><?php echo _FORMS_FIELD_ARRAYVIEW; ?></span>

        <input type="checkbox" class="checkbox" name="field_option_exportview" id="field_option_exportview" value="1" <?php if ($field->fields['option_exportview']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_exportview').checked = !$('field_option_exportview').checked;"><?php echo _FORMS_FIELD_EXPORTVIEW; ?></span>

        <input type="checkbox" class="checkbox" name="field_option_pagebreak" id="field_option_pagebreak" value="1" <?php if ($field->fields['option_pagebreak']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_pagebreak').checked = !$('field_option_pagebreak').checked;"><?php echo _FORMS_FIELD_PAGEBREAK; ?></span>
    </p>
    <p style="clear:both;padding:0 0 4px 4px;" class="ploopi_va">
        <input type="checkbox" class="checkbox" name="field_option_needed" id="field_option_needed" value="1" <?php if ($field->fields['option_needed']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_needed').checked = !$('field_option_needed').checked;"><?php echo _FORMS_FIELD_NEEDED; ?></span>

        <input type="checkbox" class="checkbox" name="field_option_adminonly" id="field_option_adminonly" value="1" <?php if ($field->fields['option_adminonly']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_adminonly').checked = !$('field_option_adminonly').checked;"><?php echo _FORMS_FIELD_ADMINONLY; ?></span>

        <?php
        if ($objForm->fields['typeform'] == 'cms')
        {
            ?>
            <input type="checkbox" class="checkbox" name="field_option_wceview" id="field_option_wceview" value="1" <?php if ($field->fields['option_wceview']) echo 'checked'; ?> />
            <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_wceview').checked = !$('field_option_wceview').checked;"><?php echo _FORMS_FIELD_WCEVIEW; ?></span>
            <?php
        }
        ?>
    </p>
</div>


<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ovensia\ploopi\crypt::urlencode("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}"); ?>'" />
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>" />
</div>
</form>


<script language="javascript">
ploopi_window_onload_stock(function() {
    forms_display_fieldvalues();
    forms_display_fieldformats();
    forms_display_tablelink();
    forms_display_calculation();
});
</script>


<?php
echo $skin->close_simplebloc();
?>
