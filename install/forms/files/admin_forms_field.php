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
 * On commence par vérifier si l'identifiant du champ est valide.
 * Si ok => on l'ouvre. Sinon, nouveau champ.
 */

$field = new field();

if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']) && $field->open($_GET['field_id'])) 
{
    $title = _FORMS_FIELDMODIFICATION;
}
else
{
    $field->init_description();
    $field->fields['option_arrayview'] = 1;
    $field->fields['option_exportview'] = 1;
    $title = _FORMS_FIELDCREATION;
}

echo $skin->open_simplebloc($title);
?>

<form name="form_field" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post" onsubmit="javascript:return forms_field_validate(this);">
<input type="hidden" name="forms_id" value="<?php echo $_GET['forms_id']; ?>">
<?php
if (!$field->new)
{
    ?>
    <input type="hidden" name="field_id" value="<?php echo $field->fields['id']; ?>">
    <?php
}
?>
<input type="hidden" name="op" value="forms_field_save">
<input type="hidden" name="field_values" value="<?php echo htmlentities($field->fields['values']); ?>">

<div style="overflow:hidden">
    <div style="float:left;width:50%;">
        <div class="ploopi_form" style="padding:4px;">
            <p>
                <label><?php echo _FORMS_FIELD_POSITION; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="fieldnew_position" value="<?php echo $field->fields['position']; ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_INTERLINE; ?>:</label>
                <input type="text" class="text" style="width:30px;" name="field_interline" value="<?php echo $field->fields['interline']; ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_NAME; ?>:</label>
                <input type="text" class="text" name="field_name" value="<?php echo htmlentities($field->fields['name']); ?>">
            </p>
            <?php
            if ($field->fields['fieldname'] == '') $field->fields['fieldname'] = forms_createphysicalname($field->fields['name']);
            ?>
            <p>
                <label><?php echo _FORMS_FIELD_FIELDNAME; ?>:</label>
                <input type="text" class="text" name="field_fieldname" value="<?php echo htmlentities($field->fields['fieldname']); ?>">
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_TYPE; ?>:</label>
                <select class="select" name="field_type" onchange="javascript:display_fieldvalues();display_fieldformats();display_fieldcols();display_tablelink();">
                <?php
                foreach($field_types as $key => $value)
                {
                    $sel = ($field->fields['type'] == $key) ? 'selected' : '';
                    echo "<option $sel value=\"{$key}\">{$value}</option>";
                }
                ?>
                </select>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DESCRIPTION; ?>:</label>
                <textarea class="text" style="height:40px;" name="field_description"><?php echo htmlentities($field->fields['description']); ?></textarea>
            </p>
            <p>
                <label><?php echo _FORMS_FIELD_DEFAULTVALUE; ?>:</label>
                <input type="text" class="text" size="30" name="field_defaultvalue" value="<?php echo htmlentities($field->fields['defaultvalue']); ?>">
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
                        echo "<option $sel value=\"{$key}\">{$value}</option>";
                    }
                    ?>
                    </select>
                </p>
                <p>
                    <label><?php echo _FORMS_FIELD_MAXLENGTH; ?>:</label>
                    <input type="text" class="text" style="width:50px;" name="field_maxlength" value="<?php echo $field->fields['maxlength']; ?>">
                </p>
            </div>


            <div id="fieldvalues" class="ploopi_form" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_VALUES; ?>:</label>
                    <span>
                        <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                            <select name="f_values" class="select" size="12" style="width:230px" onclick="document.form_field.newvalue.value=this.value;document.form_field.newvalue.focus();">
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
                                <input style="width:25px;margin:5px;" type="button" class="button" value="+" onclick="javascript:forms_field_move_value(document.form_field.f_values,1)">
                                <br />
                                <input style="width:25px;margin:5px;" type="button" class="button" value="-" onclick="javascript:forms_field_move_value(document.form_field.f_values,-1)">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                            <input style="width:250px;margin:5px 0px 5px 0px;" name="newvalue" type="text" class="text">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_ADD; ?>" onclick="javascript:forms_field_add_value(document.form_field.f_values, document.form_field.newvalue)">
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_MODIFY; ?>" onclick="javascript:forms_field_modify_value(document.form_field.f_values, document.form_field.newvalue)">
                            <input style="width:70px;" type="button" class="button" value="<?php echo _PLOOPI_DELETE; ?>" onclick="javascript:forms_field_delete_value(document.form_field.f_values)">
                            </td>
                        </tr>
                        </table>
                    </span>
                </p>
            </div>

            <div id="fieldcols" class="ploopi_form" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_MULTICOLDISPLAY; ?>:</label>
                    <select name="field_cols" class="select" style="width:50px;">
                    <?php
                    for ($i=1;$i<=5;$i++)
                    {
                        $sel = ($i == $field->fields['cols']) ? 'selected' : '';
                        echo "<option value=\"{$i}\" {$sel}>{$i}</option>";
                    }
                    ?>
                    </select>
                </p>
            </div>

            <div id="tablelink" style="display:none;">
                <p>
                    <label><?php echo _FORMS_FIELD_FORMFIELD; ?>:</label>
                    <select class="select" name="f_formfield" style="width:200px;">
                    <?php
                    $db->query( "
                                SELECT  forms.label, field.*
                                FROM    ploopi_mod_forms_form forms,
                                        ploopi_mod_forms_field field
                                WHERE   forms.id_module = {$_SESSION['ploopi']['moduleid']}
                                AND     forms.id = field.id_form
                                AND     field.separator = 0
                                ORDER BY label, position
                                ");

                    while ($row = $db->fetchrow())
                    {
                        $sel = ($field->fields['values'] == $row['id']) ? 'selected' : '';
                        echo "<option $sel value=\"{$row['id']}\">{$row['label']} | {$row['name']}</option>";
                    }

                    ?>
                    </select>
                </p>
            </div>
        </div>
    </div>

    <p style="clear:both;padding:4px;" class="ploopi_va">
        <input type="checkbox" class="checkbox" name="field_option_needed" id="field_option_needed" value="1" <?php if ($field->fields['option_needed']) echo 'checked'; ?>>
        <span style="cursor:pointer;margin-right:20px;" onclick="javascript:$('field_option_needed').checked = !$('field_option_needed').checked;"><?php echo _FORMS_FIELD_NEEDED; ?></span>
        <input type="checkbox" class="checkbox" name="field_option_arrayview" id="field_option_arrayview" value="1" <?php if ($field->fields['option_arrayview']) echo 'checked'; ?>>
        <span style="cursor:pointer;margin-right:20px;" onclick="javascript:$('field_option_arrayview').checked = !$('field_option_arrayview').checked;"><?php echo _FORMS_FIELD_ARRAYVIEW; ?></span>
        <input type="checkbox" class="checkbox" name="field_option_exportview" id="field_option_exportview" value="1" <?php if ($field->fields['option_exportview']) echo 'checked'; ?>>
        <span style="cursor:pointer;margin-right:20px;" onclick="javascript:$('field_option_exportview').checked = !$('field_option_exportview').checked;"><?php echo _FORMS_FIELD_EXPORTVIEW; ?></span>
        <input type="checkbox" class="checkbox" name="field_option_wceview" id="field_option_wceview" value="1" <?php if ($field->fields['option_wceview']) echo 'checked'; ?>>
        <span style="cursor:pointer;margin-right:20px;" onclick="javascript:$('field_option_wceview').checked = !$('field_option_wceview').checked;"><?php echo _FORMS_FIELD_WCEVIEW; ?></span>
    </p>
</div>


<div style="clear:both;background-color:#d0d0d0;border-top:1px solid #a0a0a0;padding:4px;overflow:auto;text-align:right;">
    <input type="button" class="flatbutton" value="<?php echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}"); ?>'">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SAVE; ?>">
</div>
</form>


<script language="javascript">
function display_fieldvalues()
{
    t = document.form_field.field_type;
    if (t.value == 'textarea' || t.value == 'text' || t.value == 'file' || t.value == 'autoincrement' || t.value == 'tablelink') document.getElementById('fieldvalues').style.display='none';
    else document.getElementById('fieldvalues').style.display='block';

    verifcolor = (t.value == 'color');
}

function display_fieldformats()
{
    t = document.form_field.field_type;
    if (t.value == 'text') document.getElementById('fieldformats').style.display='block';
    else document.getElementById('fieldformats').style.display='none';
}

function display_fieldcols()
{
    t = document.form_field.field_type;
    if (t.value == 'textarea' || t.value == 'text' || t.value == 'color' || t.value == 'select' || t.value == 'file' || t.value == 'autoincrement'  || t.value == 'tablelink') document.getElementById('fieldcols').style.display='none';
    else document.getElementById('fieldcols').style.display='block';
}

function display_tablelink()
{
    t = document.form_field.field_type;
    if (t.value == 'tablelink') document.getElementById('tablelink').style.display='block';
    else document.getElementById('tablelink').style.display='none';
}


if (window.attachEvent)
{
    window.attachEvent('onload', display_fieldvalues);
    window.attachEvent('onload', display_fieldformats);
    window.attachEvent('onload', display_fieldcols);
    window.attachEvent('onload', display_tablelink);
}
else
{
    window.onload = display_fieldvalues();
    window.onload = display_fieldformats();
    window.onload = display_fieldcols();
    window.onload = display_tablelink();
}
</script>


<?php
echo $skin->close_simplebloc();
?>
