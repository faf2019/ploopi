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
 * Interface d'administration du module
 *
 * @package forms
 * @subpackage admin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('forms');

/**
 * On vérifie que l'utilisateur connecté est admin du module
 */

if (ploopi_isactionallowed(_FORMS_ACTION_ADMIN))
{
    include_once './modules/forms/classes/formsForm.php';
    include_once './modules/forms/classes/formsField.php';
    include_once './modules/forms/classes/formsGraphic.php';

    $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

    if (!empty($_GET['formsTabItem'])) $_SESSION['forms']['formsTabItem'] = $_GET['formsTabItem'];
    if (!isset($_SESSION['forms']['formsTabItem'])) $_SESSION['forms']['formsTabItem'] = '';

    $sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

    $tabs['formlist'] =
        array(
            'title' => _FORMS_LABELTAB_LIST,
            'url' => "admin.php?formsTabItem=formlist"
        );

    $tabs['formadd'] =
        array(
            'title' => _FORMS_LABELTAB_ADD,
            'url' => "admin.php?formsTabItem=formadd"
        );

    echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
    echo $skin->create_tabs($tabs, $_SESSION['forms']['formsTabItem']);

    switch($op)
    {
        case 'forms_save':
            $forms = new formsForm();
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

            if (!empty($forms->fields['autobackup_date'])) $forms->fields['autobackup_date'] = ploopi_local2timestamp($forms->fields['autobackup_date']);

            $forms->setuwm();
            $forms->save();

            ploopi_redirect("admin.php?formsTabItem=formlist&op=forms_modify&forms_id={$forms->fields['id']}&ploopi_mod_msg=_FORMS_MESS_OK_1");
        break;

        case 'forms_delete':
            $forms = new formsForm();
            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id'])) $forms->delete();
            ploopi_redirect('admin.php?ploopi_mod_msg=_FORMS_MESS_OK_2');
        break;

        case 'forms_field_delete':
            if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
            {
                $field = new formsField();
                if ($field->open($_GET['field_id'])) $field->delete();
                ploopi_redirect("admin.php?op=forms_modify&forms_id={$field->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_4");
            }
            else ploopi_redirect('admin.php?ploopi_mod_msg=_FORMS_MESS_OK_4');
        break;

        case 'forms_field_save':
        case 'forms_separator_save':
        case 'forms_captcha_save':
            $field = new formsField();

            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
            {
                if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
                {
                    $field->open($_GET['field_id']);
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
                    $select = "Select max(position) as maxpos from ploopi_mod_forms_field where id_form = {$_GET['forms_id']}";
                    $db->query($select);
                    $fields = $db->fetchrow();
                    $maxpos = $fields['maxpos'];
                    if (!is_numeric($maxpos)) $maxpos = 0;
                    $field->fields['position'] = $maxpos+1;
                    $field->fields['id_form'] = $_GET['forms_id'];
                }

                $field->setvalues($_POST,'field_');

                if ($op == 'forms_separator_save')
                {
                    $field->fields['separator'] = 1;
                }
                elseif($op == 'forms_captcha_save')
                {
                    $field->fields['captcha'] = 1;
                    $field->fields['option_needed'] = 1;
                }
                else
                {
                    if (!isset($_POST['field_option_needed'])) $field->fields['option_needed'] = 0;
                    if (!isset($_POST['field_option_arrayview'])) $field->fields['option_arrayview'] = 0;
                    if (!isset($_POST['field_option_exportview'])) $field->fields['option_exportview'] = 0;
                    if (!isset($_POST['field_option_wceview'])) $field->fields['option_wceview'] = 0;
                }

                $field->save();
                ploopi_redirect("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}&ploopi_mod_msg=_FORMS_MESS_OK_3");
            }
            else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
        break;

        case 'forms_field_moveup':
        case 'forms_field_movedown':
            if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
            {
                $field = new formsField();
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
                ploopi_redirect("admin.php?op=forms_modify&forms_id={$field->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_7");
            }
            else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
        break;

        case 'forms_graphic_save':
            $objGraphic = new formsGraphic();

            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
            {
                if (!empty($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id'])) $objGraphic->open($_GET['forms_graphic_id']);

                if ($objGraphic->isnew()) $objGraphic->fields['id_form'] = $_GET['forms_id'];

                $objGraphic->setvalues($_POST,'forms_graphic_');
                if (!isset($_POST['forms_graphic_percent'])) $objGraphic->fields['percent'] = 0;
                if (!isset($_POST['forms_graphic_filled'])) $objGraphic->fields['filled'] = 0;

                $objGraphic->save();

                ploopi_redirect("admin.php?op=forms_modify&forms_id={$objGraphic->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_5");
            }
            else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');

            ploopi_die();
        break;

        case 'forms_graphic_delete':
            $objGraphic = new formsGraphic();
            if (!empty($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
            {
                $objGraphic->delete();
                ploopi_redirect("admin.php?op=forms_modify&forms_id={$objGraphic->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_6");
            }
            else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
        break;


        case "export":
            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
            {
                $forms = new formsForm();
                $forms->open($_GET['forms_id']);
                include './modules/forms/public_forms_export.php';
            }
            else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
        break;

    }

    switch($_SESSION['forms']['formsTabItem'])
    {
        case 'formlist':
            switch($op)
            {
                case 'forms_separator_add':
                case 'forms_separator_modify':
                case 'forms_captcha_add':
                case 'forms_captcha_modify':
                case 'forms_field_add':
                case 'forms_field_modify':
                case 'forms_graphic_add':
                case 'forms_graphic_modify':
                case 'forms_modify':
                    include './modules/forms/admin_forms_modify.php';
                break;

                case 'forms_preview':
                    $forms = new formsForm();

                    if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id']))
                    {
                        include './modules/forms/public_forms_display.php';
                    }
                    else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
                break;

                default:
                    include './modules/forms/admin_forms_list.php';
                break;
            }
        break;

        case 'formadd':
            switch($op)
            {
                default:
                    include './modules/forms/admin_forms_modify.php';
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
        <?php
    }
}
?>
