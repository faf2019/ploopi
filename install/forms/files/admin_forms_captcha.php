<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 Hexad
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
 * Interface de modification du captcha
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * On commence par v�rifier si l'identifiant du s�parateur est valide.
 * Si ok => on l'ouvre. Sinon, nouveau s�parateur.
 * Remarque : un s�parateur et un champ particulier, on utilise donc la classe field.
 */

$field = new field();

if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']) && $field->open($_GET['field_id']))
{
    $title = _FORMS_CAPTCHAMODIFICATION;
}
else
{
    $field->init_description();
    $title = _FORMS_CAPTCHACREATION;
}

echo $skin->open_simplebloc($title);

$arrParams = array();
$arrParams[] = "op=forms_captcha_save";
$arrParams[] = "forms_id={$_GET['forms_id']}";
if (!$field->new) $arrParams[] = "field_id={$field->fields['id']}";
?>

<form name="form_field" action="<?php echo ploopi_urlencode('admin.php?'.implode('&', $arrParams)); ?>" method="post" onsubmit="javascript:return field_validate(this);">
<div style="overflow:hidden">
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:4px;">

            <p>
                <label><?php echo _FORMS_FIELD_POSITION; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="fieldnew_position" value="<?php echo $field->fields['position']; ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_NAME; ?>: </label>
                <input type="text" class="text" size="30" name="field_name" value="<?php echo htmlentities($field->fields['name']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DESCRIPTION; ?>: </label>
                <textarea class="text" style="height:40px;" name="field_description"><?php echo htmlentities($field->fields['description']); ?></textarea>
            </p>
        </div>
    </div>
</div>

<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>

<?php echo $skin->close_simplebloc(); ?>