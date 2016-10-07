<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Interface de modification d'un séparateur
 *
 * @package forms
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On commence par vérifier si l'identifiant du séparateur est valide.
 * Si ok => on l'ouvre. Sinon, nouveau séparateur.
 * Remarque : un séparateur et un champ particulier, on utilise donc la classe field.
 */

$field = new formsField();

if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']) && $field->open($_GET['field_id']))
{
    $title = _FORMS_SEPARATORMODIFICATION;
}
else
{
    $field->init_description();
    $title = _FORMS_SEPARATORCREATION;
}

echo $skin->open_simplebloc($title);

$arrParams = array();
$arrParams[] = "ploopi_op=forms_separator_save";
$arrParams[] = "forms_id={$_GET['forms_id']}";
if (!$field->new) $arrParams[] = "field_id={$field->fields['id']}";
?>

<form name="form_field" action="<?php echo ploopi\crypt::urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:return field_validate(this);">
<div style="overflow:hidden">
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:4px;">

            <p>
                <label><?php echo _FORMS_FIELD_POSITION; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_position" value="<?php echo ploopi\str::htmlentities($field->fields['position']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_INTERLINE; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_interline" value="<?php echo ploopi\str::htmlentities($field->fields['interline']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_GROUP; ?>:</label>
                <select class="select" name="field_id_group">
                    <option value="0">(Aucun)</option>
                    <?php
                    foreach($objForm->getGroups() as $intIdGroup => $objGroup)
                    {
                        ?>
                        <option value="<?php echo $intIdGroup; ?>" <?php if ($field->fields['id_group'] == $intIdGroup) echo 'selected="selected"'; ?>><?php echo ploopi\str::htmlentities($objGroup->fields['label']); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_SEPARATOR_LEVEL; ?>:</label>
                <select class="select" name="field_separator_level" style="width:50px;">
                <?php
                for ($i=1;$i<=5;$i++)
                {
                    $sel = ($i == $field->fields['separator_level']) ? 'selected' : '';
                    echo "<option value=\"{$i}\" {$sel}>{$i}</option>";
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_NAME; ?>: </label>
                <input type="text" class="text" size="30" name="field_name" value="<?php echo ploopi\str::htmlentities($field->fields['name']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DESCRIPTION; ?>: </label>
                <textarea class="text" style="height:40px;" name="field_description"><?php echo ploopi\str::htmlentities($field->fields['description']); ?></textarea>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_STYLE_FORM; ?>:</label>
                <input type="text" class="text" name="field_style_form" value="<?php echo ploopi\str::htmlentities($field->fields['style_form']); ?>">
            </p>
        </div>
    </div>
    <p style="clear:both;padding:0 0 4px 4px;" class="ploopi_va">
        <input type="checkbox" class="checkbox" name="field_option_pagebreak" id="field_option_pagebreak" value="1" <?php if ($field->fields['option_pagebreak']) echo 'checked'; ?> />
        <span style="cursor:pointer;margin-right:15px;" onclick="javascript:$('field_option_pagebreak').checked = !$('field_option_pagebreak').checked;"><?php echo _FORMS_FIELD_PAGEBREAK; ?></span>

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
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi\crypt::urlencode("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<?php echo $skin->close_simplebloc(); ?>
