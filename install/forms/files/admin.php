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
ploopi_init_module('forms');
include_once './modules/forms/class_form.php';
include_once './modules/forms/class_field.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

$tabs['formlist'] = array('title' => _FORMS_LABELTAB_LIST, 'url' => "{$scriptenv}?ploopi_moduletabid=formlist");
$tabs['formadd'] = array('title' => _FORMS_LABELTAB_ADD, 'url' => "{$scriptenv}?ploopi_moduletabid=formadd");

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->create_tabs('',$tabs,$_SESSION['ploopi']['moduletabid']);

switch($op)
{
    /*
    case 'forms_generate_tables':
    case 'forms_generate_tables_from_list':
        // needed to generate Metabase
        include_once './modules/system/class_mb_table.php';
        include_once './modules/system/class_mb_field.php';
        include_once './modules/system/class_mb_schema.php';
        include_once './modules/system/class_mb_relation.php';

        $forms = new forms();
        if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id']))
        {
            $data_object = new data_object('form_'.forms_createphysicalname($forms->fields['label']),null);

            $data_object->fields['date_validation'] = '';
            $data_object->fields['ip'] = '';
            $data_object->fields['userid'] = '';
            $data_object->fields['workspaceid'] = '';
            $data_object->fields['login'] = '';
            $data_object->fields['firstname'] = '';
            $data_object->fields['lastname'] = '';
            $data_object->fields['groupname'] = '';
            $data_object->fields['groupcode'] = '';

            $mb_table = new mb_table();
            if ($mb_table->open($forms->fields['id'], $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type']))
            {
                $mb_table->delete();
            }

            $mb_table = new mb_table();

            $mb_table->fields['id'] = $forms->fields['id'];
            $mb_table->fields['name'] = 'form_'.forms_createphysicalname($forms->fields['label']);
            $mb_table->fields['label'] = $forms->fields['label'];
            $mb_table->fields['visible'] = 1;
            $mb_table->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
            $mb_table->save();

            $array_fields = $forms->getfields();

            $array_fields[]['name'] = 'date_validation';
            $array_fields[]['name'] = 'ip';
            $array_fields[]['name'] = 'userid';
            $array_fields[]['name'] = 'workspaceid';
            $array_fields[]['name'] = 'login';
            $array_fields[]['name'] = 'firstname';
            $array_fields[]['name'] = 'lastname';
            $array_fields[]['name'] = 'groupname';
            $array_fields[]['name'] = 'groupcode';

            foreach($array_fields as $id => $field)
            {

                if (isset($field['type']) && $field['type'] == 'tablelink')
                {
                    // creation db relation

                    $objfield = new field();
                    $objfield->open($field['values']);

                    $objform = new forms();
                    $objform->open($objfield->fields['id_form']);

                    $mb_schema = new mb_schema();
                    $mb_schema->fields['tablesrc'] = 'form_'.forms_createphysicalname($forms->fields['label']);
                    $mb_schema->fields['tabledest'] = 'form_'.forms_createphysicalname($objform->fields['label']);
                    $mb_schema->fields['id_tablesrc'] = $forms->fields['id'];
                    $mb_schema->fields['id_tabledest'] = $objform->fields['id'];
                    $mb_schema->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
                    $mb_schema->save();

                    $mb_relation = new mb_relation();
                    $mb_relation->fields['tablesrc'] = 'form_'.forms_createphysicalname($forms->fields['label']);
                    $mb_relation->fields['id_tablesrc'] = $forms->fields['id'];
                    $mb_relation->fields['fieldsrc'] = forms_createphysicalname($field['name']);
                    $mb_relation->fields['tabledest'] = 'form_'.forms_createphysicalname($objform->fields['label']);
                    $mb_relation->fields['id_tabledest'] = $objform->fields['id'];
                    $mb_relation->fields['fielddest'] = forms_createphysicalname($objfield->fields['name']);
                    $mb_relation->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['id_module_type'];
                    $mb_relation->save();
                }
                $mb_field = new mb_field();
                $mb_field->fields['tablename'] = $mb_table->fields['name'];
                $mb_field->fields['name'] = forms_createphysicalname($field['name']);
                $mb_field->fields['label'] = $field['name'];
                $mb_field->fields['type'] = 'varchar(255)';
                $mb_field->fields['visible'] = 1;
                $mb_field->fields['id_module_type'] = $mb_table->fields['id_module_type'];
                $mb_field->fields['id_table'] = $forms->fields['id'];
                $mb_field->save();

                $data_object->fields[forms_createphysicalname($field['name'])] = '';
            }

            $db->query("DROP TABLE IF EXISTS `{$data_object->tablename}`");
            $db->query($data_object->getsqlstructure());

            $select =   "
                            SELECT  fr.*,
                                    u.id as userid,
                                    u.firstname,
                                    u.lastname,
                                    u.login,
                                    g.id as workspaceid,
                                    g.code as groupcode,
                                    g.label as groupname
                            FROM    ploopi_mod_forms_reply fr,
                                    ploopi_module m
                            LEFT JOIN ploopi_user u ON fr.id_user = u.id
                            LEFT JOIN ploopi_workspace g ON fr.id_workspace = g.id
                            AND     fr.id_workspace IN ({$workspaces})
                            WHERE   fr.id_form = {$forms->fields['id']}
                            AND     fr.id_module = m.id
                            AND     fr.id_module = {$_SESSION['ploopi']['moduleid']}
                            ";

            $rs = $db->query($select);

            // construction du jeu de données brut (liste des réponses)
            while ($fields = $db->fetchrow($rs))
            {
                $data_object->fields['date_validation'] = $fields['date_validation'];
                $data_object->fields['ip'] = $fields['ip'];
                $data_object->fields['userid'] = $fields['userid'];
                $data_object->fields['workspaceid'] = $fields['workspaceid'];
                $data_object->fields['login'] = $fields['login'];
                $data_object->fields['firstname'] = $fields['firstname'];
                $data_object->fields['lastname'] = $fields['lastname'];
                $data_object->fields['groupname'] = $fields['groupname'];
                $data_object->fields['groupcode'] = $fields['groupcode'];

                $sql =  "
                        SELECT  rf.*, f.type
                        FROM    ploopi_mod_forms_reply_field rf,
                                ploopi_mod_forms_field f
                        WHERE   rf.id_reply = {$fields['id']}
                        AND     f.id = rf.id_field
                        AND     f.separator = 0
                        ";

                $rs_replies = $db->query($sql);

                while ($fields_replies = $db->fetchrow($rs_replies))
                {
                    $data_object->fields[forms_createphysicalname($array_fields[$fields_replies['id_field']]['name'])] = $fields_replies['value'];
                }

                $db->query($data_object->dump());
            }

            if ($op == 'forms_generate_tables_from_list') ploopi_redirect("{$scriptenv}?forms_id={$forms->fields['id']}&termine");
            else ploopi_redirect("{$scriptenv}?op=forms_modify&forms_id={$forms->fields['id']}&termine");
        }
        else ploopi_redirect($scriptenv);
    break;
    */
    case 'forms_save':
        $forms = new forms();
        if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id'])) $forms->open($_POST['forms_id']);
        $forms->setvalues($_POST,'forms_');
        $forms->fields['pubdate_start'] = ploopi_local2timestamp($forms->fields['pubdate_start']);
        $forms->fields['pubdate_end'] = ploopi_local2timestamp($forms->fields['pubdate_end']);
        if (!isset($_POST['forms_option_onlyone'])) $forms->fields['option_onlyone'] = 0;
        if (!isset($_POST['forms_option_onlyoneday'])) $forms->fields['option_onlyoneday'] = 0;
        if (!isset($_POST['forms_option_displayuser'])) $forms->fields['option_displayuser'] = 0;
        if (!isset($_POST['forms_option_displaygroup'])) $forms->fields['option_displaygroup'] = 0;
        if (!isset($_POST['forms_option_displaydate'])) $forms->fields['option_displaydate'] = 0;
        if (!isset($_POST['forms_option_displayip'])) $forms->fields['option_displayip'] = 0;
        if (!isset($_POST['forms_cms_link'])) $forms->fields['cms_link'] = 0;

        $forms->fields['autobackup_date'] = ploopi_local2timestamp($forms->fields['autobackup_date']);

        $forms->setugm();
        $forms->save();

        ploopi_redirect("{$scriptenv}?ploopi_moduletabid=formlist&op=forms_modify&forms_id={$forms->fields['id']}");
    break;

    case 'forms_delete':
        $forms = new forms();
        if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id'])) $forms->delete();
        ploopi_redirect($scriptenv);
    break;

    case 'forms_field_delete':
        if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
        {
            $field = new field();
            if ($field->open($_GET['field_id'])) $field->delete();
            ploopi_redirect("{$scriptenv}?op=forms_modify&forms_id={$field->fields['id_form']}");
        }
        else ploopi_redirect($scriptenv);
    break;

    case 'forms_field_save':
    case 'forms_separator_save':
        $field = new field();

        if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id']))
        {
            if (!empty($_POST['field_id']) && is_numeric($_POST['field_id']))
            {
                $field->open($_POST['field_id']);
                if (!empty($_POST['fieldnew_position']) && is_numeric($_POST['fieldnew_position']) && $_POST['fieldnew_position'] != $field->fields['position']) // nouvelle position définie
                {
                    if ($_POST['fieldnew_position'] < 1) $_POST['fieldnew_position'] = 1;
                    else
                    {
                        $db->query("Select max(position) as maxpos from ploopi_mod_forms_field where id_form = {$field->fields['id_form']}");
                        $fields = $db->fetchrow();
                        if ($_POST['fieldnew_position'] > $fields['maxpos']) $_POST['fieldnew_position'] = $fields['maxpos'];
                    }

                    $db->query("update ploopi_mod_forms_field set position = 0 where position = {$field->fields['position']} and id_form = {$field->fields['id_form']}");
                    if ($_POST['fieldnew_position'] > $field->fields['position'])
                    {
                        $db->query("update ploopi_mod_forms_field set position=position-1 where position BETWEEN ".($field->fields['position']-1)." AND {$_POST['fieldnew_position']} and id_form = {$field->fields['id_form']}");
                    }
                    else
                    {
                        $db->query("update ploopi_mod_forms_field set position=position+1 where position BETWEEN {$_POST['fieldnew_position']} AND ".($field->fields['position']-1)." and id_form = {$field->fields['id_form']}");
                    }
                    $db->query("update ploopi_mod_forms_field set position={$_POST['fieldnew_position']} where position=0 and id_form = {$field->fields['id_form']}");
                    $field->fields['position'] = $_POST['fieldnew_position'];
                }
            }
            else // nouveau
            {
                $select = "Select max(position) as maxpos from ploopi_mod_forms_field where id_form = {$_POST['forms_id']}";
                $db->query($select);
                $fields = $db->fetchrow();
                $maxpos = $fields['maxpos'];
                if (!is_numeric($maxpos)) $maxpos = 0;
                $field->fields['position'] = $maxpos+1;
                $field->fields['id_form'] = $_POST['forms_id'];
            }
            $field->setvalues($_POST,'field_');

            if ($op == 'forms_separator_save')
            {
                $field->fields['separator'] = 1;
            }
            else
            {
                if (!isset($_POST['field_option_needed'])) $field->fields['option_needed'] = 0;
                if (!isset($_POST['field_option_arrayview'])) $field->fields['option_arrayview'] = 0;
                if (!isset($_POST['field_option_exportview'])) $field->fields['option_exportview'] = 0;
                if (!isset($_POST['field_option_wceview'])) $field->fields['option_wceview'] = 0;
            }
            $field->save();
            ploopi_redirect("{$scriptenv}?op=forms_modify&forms_id={$_POST['forms_id']}");
        }
        else ploopi_redirect($scriptenv);
    break;

    case 'forms_field_moveup':
    case 'forms_field_movedown':
        if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
        {
            $field = new field();
            $field->open($_GET['field_id']);

            $select = "Select min(position) as minpos, max(position) as maxpos from ploopi_mod_forms_field where id_form = {$field->fields['id_form']}";
            $db->query($select);
            $fields = $db->fetchrow();

            if ($op == 'forms_field_movedown')
            {
                if ($fields['maxpos'] != $field->fields['position']) // ce n'est pas le dernier champ
                {
                    $db->query("update ploopi_mod_forms_field set position=0 where position=".($field->fields['position']+1)." and id_form = {$field->fields['id_form']}");
                    $db->query("update ploopi_mod_forms_field set position=".($field->fields['position']+1)." where position=".$field->fields['position']." and id_form = {$field->fields['id_form']}");
                    $db->query("update ploopi_mod_forms_field set position=".$field->fields['position']." where position=0 and id_form = {$field->fields['id_form']}");
                }
            }
            else
            {
                if ($fields['minpos'] != $field->fields['position']) // ce n'est pas le premier champ
                {
                    $db->query("update ploopi_mod_forms_field set position=0 where position=".($field->fields['position']-1)." and id_form = {$field->fields['id_form']}");
                    $db->query("update ploopi_mod_forms_field set position=".($field->fields['position']-1)." where position=".$field->fields['position']." and id_form = {$field->fields['id_form']}");
                    $db->query("update ploopi_mod_forms_field set position=".$field->fields['position']." where position=0 and id_form = {$field->fields['id_form']}");
                }
            }
            ploopi_redirect("{$scriptenv}?op=forms_modify&forms_id={$field->fields['id_form']}");
        }
        else ploopi_redirect($scriptenv);
    break;

    case "export":
        if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
        {
            $forms = new forms();
            $forms->open($_GET['forms_id']);
            include('./modules/forms/public_forms_export.php');
        }
        else ploopi_redirect($scriptenv);
    break;

}

switch($_SESSION['ploopi']['moduletabid'])
{
    case 'formlist':
        switch($op)
        {
            case 'forms_separator_add':
            case 'forms_separator_modify':
            case 'forms_field_add':
            case 'forms_field_modify':
            case 'forms_modify':
                include('./modules/forms/admin_forms_modify.php');
            break;

            case 'forms_preview':
                $forms = new forms();

                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id']))
                {
                    include('./modules/forms/public_forms_display.php');
                }
                else ploopi_redirect($scriptenv);
            break;

            default:
                include('./modules/forms/admin_forms_list.php');
            break;
        }
    break;

    case 'formadd':
        switch($op)
        {
            default:
                include('./modules/forms/admin_forms_modify.php');
            break;
        }
    break;

}


if (isset($_GET['termine']))
{
    ?>
    <script type="text/javascript">
        alert('Terminé !');
    </script>
    <?
}
?>
