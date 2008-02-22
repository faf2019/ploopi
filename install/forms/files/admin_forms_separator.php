<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
?>
<?
$field = new field();
if (!empty($_GET['field_id']) && is_numeric($_GET['field_id'])) $field->open($_GET['field_id']);
else $field->init_description();

echo $skin->open_simplebloc(_FORMS_SEPARATORMODIFICATION);
?>

<form name="form_field" action="<? echo $scriptenv; ?>" method="post" onsubmit="javascript:return field_validate(this);">
<input type="hidden" name="forms_id" value="<? echo $_GET['forms_id']; ?>">
<?
if (!$field->new)
{
    ?>
    <input type="hidden" name="field_id" value="<? echo $field->fields['id']; ?>">
    <?
}
?>
<input type="hidden" name="op" value="forms_separator_save">
<div style="overflow:hidden">
    <div class="ploopi_form" style="float:left;width:50%;">
        <div style="padding:4px;">

            <p>
                <label><? echo _FORMS_FIELD_POSITION; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="fieldnew_position" value="<? echo $field->fields['position']; ?>">
            </p>
            <p>
                <label><? echo _FORMS_FIELD_INTERLINE; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_interline" value="<? echo $field->fields['interline']; ?>">
            </p>
            <p>
                <label><? echo _FORMS_FIELD_SEPARATOR_LEVEL; ?>:</label>
                <select class="select" name="field_separator_level" style="width:50px;">
                <?
                for ($i=1;$i<=5;$i++)
                {
                    $sel = ($i == $field->fields['separator_level']) ? 'selected' : '';
                    echo "<option value=\"{$i}\" {$sel}>{$i}</option>";
                }
                ?>
                </select>
            </p>
            <p>
                <label><? echo _FORMS_FIELD_SEPARATOR_FONTSIZE; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_separator_fontsize" value="<? echo $field->fields['separator_fontsize']; ?>">
            </p>
            <p>
                <label><? echo _FORMS_FIELD_NAME; ?>: </label>
                <input type="text" class="text" size="30" name="field_name" value="<? echo htmlentities($field->fields['name']); ?>">
            </p>
            <p>
                <label><? echo _FORMS_FIELD_DESCRIPTION; ?>: </label>
                <textarea class="text" style="height:40px;" name="field_description"><? echo htmlentities($field->fields['description']); ?></textarea>
            </p>
        </div>
    </div>
</div>

<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=forms_modify&forms_id={$_GET['forms_id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<? echo _PLOOPI_SAVE; ?>">
</div>
</form>

<? echo $skin->close_simplebloc(); ?>
