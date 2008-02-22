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

ploopi_init_module('forms');

include_once './modules/forms/class_form.php';
include_once './modules/forms/class_field.php';
include_once './modules/forms/class_reply.php';
include_once './modules/forms/class_reply_field.php';
include_once './lib/template/template.php';


global $field_formats; // from forms/include/global.php
global $field_operators; // from forms/include/global.php

global $articleid;
global $headingid;
global $template_name;

if (!empty($_REQUEST['op'])) $op = $_REQUEST['op'];

switch($op)
{
    case 'display':
        $forms_id  = $obj['object_id'];
        include_once './modules/forms/wce_forms_display.php';
    break;

    case 'saveform':
        $forms = new forms();
        if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id']) && $forms->open($_POST['forms_id']))
        {
            $reply = new reply();
            $reply->fields['date_validation'] = ploopi_createtimestamp();
            $reply->fields['id_module'] = $obj['module_id'];
            $reply->fields['id_user'] = 0; // anonymous
            $reply->fields['id_workspace'] = 0; // anonymous

            $email_array['Formulaire']['Titre'] = $forms->fields['label'];
            $email_array['Formulaire']['Date'] = $reply->fields['date_validation'];

            $reply->fields['id_form'] = $_POST['forms_id'];
            $reply->fields['ip'] = $_SERVER['REMOTE_ADDR'];
            $reply->save();

            $email_array = array();
            $email_array['Formulaire']['Opération'] = 'Nouvel Enregistrement';
            $email_array['Formulaire']['Adresse IP'] = $reply->fields['ip'];

            $sql =  "
                    SELECT  *
                    FROM    ploopi_mod_forms_field
                    WHERE   id_form = {$_POST['forms_id']}
                    ORDER BY position
                    ";

            $rs_fields = $db->query($sql);

            while ($fields = $db->fetchrow($rs_fields))
            {
                $value = '';
                $fieldok = false;
                $error = false;

                if ($fields['type'] == 'file' && isset($_FILES['field_'.$fields['id']]['name']))
                {
                    $fieldok = true;
                    $value = $_FILES['field_'.$fields['id']]['name'];
                    $path = _PLOOPI_PATHDATA.'forms-'.$obj['module_id']._PLOOPI_SEP.$_POST['forms_id']._PLOOPI_SEP.$reply->fields['id']._PLOOPI_SEP;
                    $error = ($_FILES['field_'.$fields['id']]['size'] > _PLOOPI_MAXFILESIZE);

                    if (!$error)
                    {
                        ploopi_makedir($path);
                        if (file_exists($path) && is_writable($path))
                        {
                            move_uploaded_file($_FILES['field_'.$fields['id']]['tmp_name'], $path.$value);
                            {
                                chmod($path.$value, 0660);
                            }
                        }
                    }
                }

                if (isset($_POST['field_'.$fields['id']]))
                {
                    $fieldok = true;
                    if (is_array($_POST['field_'.$fields['id']]))
                    {
                        foreach($_POST['field_'.$fields['id']] as $val)
                        {
                            if ($value != '') $value .= '||';
                            $value .= $val;
                        }
                    }
                    else $value = $_POST['field_'.$fields['id']];
                }
                else
                {
                    if ($fields['type'] == 'autoincrement' && $isnew) // not in form => need to be calculated
                    {
                        $fieldok = true;
                        $select = "SELECT max(value*1) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_form = '{$_POST['forms_id']}' AND id_field = '{$fields['id']}'";
                        $rs_maxinc = $db->query($select);
                        $fields_maxinc = $db->fetchrow($rs_maxinc);
                        $value = ($fields_maxinc['maxinc'] == '' || $fields_maxinc['maxinc'] == 0) ? 1 : $fields_maxinc['maxinc']+1;
                    }
                }

                if ($fieldok = true)
                {
                    $reply_field = new reply_field();
                    if (isset($reply_id))
                    {
                        $reply_field->open($reply_id, $fields['id']);
                    }

                    $reply_field->fields['id_field'] = $fields['id'];
                    $reply_field->fields['id_form'] = $_POST['forms_id'];
                    $reply_field->fields['id_reply'] = $reply->fields['id'];

                    if (!(($fields['type'] == 'autoincrement' || $fields['type'] == 'file') && $value == '')) $reply_field->fields['value'] = $value;

                    $reply_field->save();

                    $email_array['Contenu']["({$fields['id']}) {$fields['name']}"] = $reply_field->fields['value'];
                }

            }

            if ($forms->fields['email'] != '')
            {
                $list_email = explode(';',$forms->fields['email']);
                foreach($list_email as $email)
                {
                    $from[0] = array('name' => $email, 'address' => $email);
                    $to[] = array('name' => $email, 'address' => $email);
                }
                ploopi_send_form($from, $to, $email_array['Formulaire']['Titre'], $email_array);
            }
        }

        $form_action_params = array();
        if (!empty($_REQUEST['headingid'])) $form_action_params[] = "headingid={$_REQUEST['headingid']}";
        if (!empty($_REQUEST['articleid'])) $form_action_params[] = "articleid={$_REQUEST['articleid']}";
        if (!empty($_REQUEST['wce_mode'])) $form_action_params[] = "wce_mode={$_REQUEST['wce_mode']}";
        $form_action_params[] = 'op=end';

        $form_action = (!empty($form_action_params)) ? 'index.php?'.implode('&',$form_action_params) : 'index.php';
        ploopi_redirect($form_action);
    break;

    case 'end':
        $forms = new forms();
        $forms->open($obj['object_id']);
        ?>
        <div id="forms_response"><? echo nl2br($forms->fields['cms_response']); ?></div>
        <?
    break;


}
?>
