<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2008-2010 HeXad
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
 * Rendu d'un formulaire dans une page de contenu (WebEdit) via le moteur de template
 *
 * @package forms
 * @subpackage wce
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Construction de l'url de validation
 */

$form_action_params = array();
if (!empty($_REQUEST['headingid'])) $form_action_params[] = "headingid={$_REQUEST['headingid']}";
if (!empty($_REQUEST['articleid'])) $form_action_params[] = "articleid={$_REQUEST['articleid']}";
if (!empty($_REQUEST['webedit_mode'])) $form_action_params[] = "webedit_mode={$_REQUEST['webedit_mode']}";

$id_captcha = id_captcha($forms->fields['id']);

$form_action = (!empty($form_action_params)) ? ploopi_urlencode('index.php?'.implode('&',$form_action_params).'&id_captcha='.$id_captcha) : ploopi_urlencode('index.php?id_captcha='.$id_captcha);
 
/**
 * Définition des variables décrivant le formulaire
 */

$template_forms->assign_vars(array(
    'FORM_ID' => $forms->fields['id'],
    'FORM_TITLE' => $forms->fields['label'],
    'FORM_DESCRIPTION' => nl2br($forms->fields['description']),
    'HEADINGID' => (empty($_REQUEST['headingid'])) ? '' : $_REQUEST['headingid'],
    'ARTICLEID' => (empty($_REQUEST['articleid'])) ? '' : $_REQUEST['articleid'],
    'WCE_MODE' => (empty($_REQUEST['wce_mode'])) ? '' : $_REQUEST['wce_mode'],
    'ACTION' => $form_action,
    'TEMPLATE_PATH' => './templates/frontoffice/'.$template_name
));

/**
 * On crée un bloc par champ
 */
while ($fields = $db->fetchrow($rs_fields))
{
    $template_forms->assign_block_vars('formfields', array());

    if ($fields['separator'])
    {
        $template_forms->assign_block_vars('formfields.switch_separator', array(
            'NAME' => $fields['name'],
            'LEVEL' => $fields['separator_level'],
            'STYLE' => htmlentities($fields['style'])
        ));
    }
    elseif($fields['captcha'])
    {
        $template_forms->assign_block_vars('formfields.switch_captcha', array(
                    'ID' => $fields['id'],
                    'LABELID' => 'captcha_'.$fields['id'],
                    'NAME' => 'captcha_'.$fields['id'],
                    'LABEL' => htmlentities($fields['name']),
                    'DESCRIPTION' => htmlentities($fields['description']),
                    'IDCAPTCHA'     => $id_captcha,
                    'URLTOCAPTCHA'      => ploopi_urlencode('index-light.php?ploopi_op=ploopi_get_captcha&id_captcha='.$id_captcha),
                    'URLTOCAPTCHASOUND' => ploopi_urlencode(urldecode('index-light.php?ploopi_op=ploopi_get_captcha_sound&id_captcha='.$id_captcha),null,null,null,null,true,true) // Passage au flash nécessite constament une url_encodée
                    )
                );
    }
    else
    {
        $value = '';

        if ($fields['type'] == 'autoincrement')
        {
            if (isset($replies[$fields['id']][0]) && $replies[$fields['id']][0] != '')
            {
                $value = $replies[$fields['id']][0];
            }
            else
            {
                $select = "SELECT max(value) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_form = '{$forms_id}' AND id_field = '{$fields['id']}'";
                $rs_maxinc = $db->query($select);
                $fields_maxinc = $db->fetchrow($rs_maxinc);
                $maxinc = ($fields_maxinc['maxinc'] == '' || $fields_maxinc['maxinc'] == 0) ? 1 : $fields_maxinc['maxinc']+1;
                $value = "$maxinc (à valider)";
            }
        }
        else $value = (isset($replies[$fields['id']][0])) ? $replies[$fields['id']][0] : '';

        $template_forms->assign_block_vars('formfields.switch_field',array(
                    'ID' => $fields['id'],
                    'LABELID' => 'field_'.$fields['id'],
                    'NAME' => 'field_'.$fields['id'],
                    'LABEL' => $fields['name'],
                    'DESCRIPTION' => $fields['description'],
                    'NEEDED' => $fields['option_needed'],
                    'INTERLINE' => $fields['interline'],
                    'VALUE' => $value,
                    'TABINDEX' => 1000+$fields['position'],
                    'MAXLENGTH' => (empty($fields['maxlength'])) ? '255' : $fields['maxlength'],
                    'STYLE' => htmlentities($fields['style']),
                    'CONTENT' => ''
                    )
                );

        if ($fields['option_needed']) $template_forms->assign_block_vars('formfields.switch_field.switch_required',array());

        $values = explode('||',$fields['values']);

        if (isset($_GET["field_{$fields['id']}"])) $replies[$fields['id']][0] = $_GET["field_{$fields['id']}"];
        if (isset($_POST["field_{$fields['id']}"])) $replies[$fields['id']][0] = $_POST["field_{$fields['id']}"];

        switch($fields['type'])
        {
            case 'autoincrement':
                $template_forms->assign_block_vars('formfields.switch_field.switch_autoincrement', array());
            break;

            case 'text':
                if (isset($field_formats[$fields['format']]))
                {
                    switch($field_formats[$fields['format']])
                    {
                        case 'Date':
                            $template_forms->assign_block_vars('formfields.switch_field.switch_text_date', array());
                        break;

                        default:
                            $template_forms->assign_block_vars('formfields.switch_field.switch_text', array());
                        break;
                    }
                }
                else
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_text', array());
                }

            break;

            case 'textarea':
                    $template_forms->assign_block_vars('formfields.switch_field.switch_textarea', array());
            break;

            case 'select':
                $template_forms->assign_block_vars('formfields.switch_field.switch_select', array());

                $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                    'VALUE' => '', 'SELECTED' => '', 'SELECTED_XHTML' => ''
                    )
                );

                foreach($values as $value)
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                        'VALUE' => $value,
                        'SELECTED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected' : '',
                        'SELECTED_XHTML' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected="selected"' : ''
                        )
                    );
                }
            break;

            case 'tablelink':
                $template_forms->assign_block_vars('formfields.switch_field.switch_select', array());

                $select = "SELECT distinct(value) FROM ploopi_mod_forms_reply_field WHERE id_field = '{$values[0]}' AND value <> '' ORDER BY value";
                $rs_detail = $db->query($select);

                while($row = $db->fetchrow($rs_detail))
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                        'VALUE' => $row['value'],
                        'SELECTED' => (isset($replies[$fields['id']]) && $row['value'] == $replies[$fields['id']][0]) ? 'selected' : '',
                        'SELECTED_XHTML' => (isset($replies[$fields['id']]) && $row['value'] == $replies[$fields['id']][0]) ? 'selected="selected"' : ''
                        )
                    );
                }
            break;

            case 'checkbox':
                $template_forms->assign_block_vars('formfields.switch_field.switch_checkbox', array());

                $c_size = ceil(sizeof($values) / $fields['cols']);

                for ($c = 1; $c<=$fields['cols']; $c++) // columns
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_checkbox.columns', array(
                        'WIDTH' => 100 / $fields['cols']
                        )
                    );

                    for ($d = ($c-1)*$c_size; $d < ($c)*$c_size && isset($values[$d]); $d++)
                    {
                        $value = $values[$d];

                        $template_forms->assign_block_vars('formfields.switch_field.switch_checkbox.columns.values', array(
                            'ID' => $d,
                            'VALUE' => $value,
                            'NAME' => "field_{$fields['id']}[]",
                            'CHECKED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked' : '',
                            'CHECKED_XHTML' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked="checked"' : ''
                            )
                        );
                    }
                }
            break;

            case 'radio':
                $template_forms->assign_block_vars('formfields.switch_field.switch_radio', array());

                $c_size = ceil(sizeof($values) / $fields['cols']);

                for ($c = 1; $c<=$fields['cols']; $c++) // columns
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_radio.columns', array(
                        'WIDTH' => 100 / $fields['cols']
                        )
                    );

                    for ($d = ($c-1)*$c_size; $d < ($c)*$c_size && isset($values[$d]); $d++)
                    {
                        $value = $values[$d];
                        $template_forms->assign_block_vars('formfields.switch_field.switch_radio.columns.values', array(
                            'ID' => $d,
                            'VALUE' => $value,
                            'NAME' => "field_{$fields['id']}[]",
                            'CHECKED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked' : '',
                            'CHECKED_XHTML' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked="checked"' : ''
                            )
                        );
                    }
                }
            break;

            case 'file':
                $template_forms->assign_block_vars('formfields.switch_field.switch_file', array());
            break;

            case 'color':
                $template_forms->assign_block_vars('formfields.switch_field.switch_color', array());

                $template_forms->assign_block_vars('formfields.switch_field.switch_color.values', array(
                    'VALUE' => '', 'SELECTED' => '', 'SELECTED_XHTML' => ''
                    )
                );

                foreach($values as $value)
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_color.values', array(
                        'VALUE' => $value,
                        'SELECTED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected' : '',
                        'SELECTED_XHTML' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected="selected"' : ''
                        )
                    );
                }
            break;
        }

    }
}
