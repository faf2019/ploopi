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

$template_forms->assign_vars(array(
            'FORM_TITLE' => htmlentities($forms->fields['label']),
            'FORM_DESCRIPTION' => nl2br(htmlentities($forms->fields['description'])),
            'FORM_ONSUBMIT' => 'javascript:eval(form_validate);return(result);',
            'FORM_REPLY_ID' => $reply_id,
            'FORM_WIDTH' => (empty($forms->fields['width']) || $forms->fields['width'] == '*') ? '100%' : "{$forms->fields['width']}px"
            )
        );

while ($fields = $db->fetchrow($rs_fields))
{
    $template_forms->assign_block_vars('formfields', array());

    if ($fields['separator'])
    {
        $template_forms->assign_block_vars('formfields.switch_separator', array(
                    'NAME' => $fields['name'],
                    'LEVEL' => $fields['separator_level'],
                    'FONTSIZE' => $fields['separator_fontsize']
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
                $select = "SELECT max(value) as maxinc FROM ploopi_mod_forms_reply_field WHERE id_form = '{$forms->fields['id']}' AND id_field = '{$fields['id']}'";
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
                    'LABEL' => htmlentities($fields['name']),
                    'DESCRIPTION' => htmlentities($fields['description']),
                    'NEEDED' => $fields['option_needed'],
                    'INTERLINE' => $fields['interline'],
                    'VALUE' => htmlentities($value),
                    'TABINDEX' => 1000+$fields['position'],
                    'MAXLENGTH' => (empty($fields['maxlength'])) ? '255' : $fields['maxlength'],
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
                    'VALUE' => '', 'SELECTED' => ''
                    )
                );
                
                foreach($values as $value)
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                        'VALUE' => $value,
                        'SELECTED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected' : ''
                        )
                    );
                }
            break;

            case 'tablelink':
                $template_forms->assign_block_vars('formfields.switch_field.switch_select', array());

                $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                    'VALUE' => '', 'SELECTED' => ''
                    )
                );
                
                $select = "SELECT distinct(value) FROM ploopi_mod_forms_reply_field WHERE id_field = '{$values[0]}' AND value <> ''";
                $rs_detail = $db->query($select);

                while($row = $db->fetchrow($rs_detail))
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_select.values', array(
                        'VALUE' => $row['value'],
                        'SELECTED' => (isset($replies[$fields['id']]) && $row['value'] == $replies[$fields['id']][0]) ? 'selected' : ''
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
                            'CHECKED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked' : ''
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
                            'CHECKED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'checked' : ''
                            )
                        );
                    }
                }
            break;

            case 'file':
                $template_forms->assign_block_vars('formfields.switch_field.switch_file', array());
                if (!empty($replies[$fields['id']][0]))
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_file.switch_filename', array());
                }
            break;

            case 'color':
                $template_forms->assign_block_vars('formfields.switch_field.switch_color', array());

                $template_forms->assign_block_vars('formfields.switch_field.switch_color.values', array(
                    'VALUE' => '', 'SELECTED' => ''
                    )
                );
                
                foreach($values as $value)
                {
                    $template_forms->assign_block_vars('formfields.switch_field.switch_color.values', array(
                        'VALUE' => $value,
                        'SELECTED' => (isset($replies[$fields['id']]) && in_array($value, $replies[$fields['id']])) ? 'selected' : ''
                        )
                    );
                }
            break;
        }

    }
}
