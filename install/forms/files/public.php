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
 * Partie publique du module
 *
 * @package forms
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ploopi_init_module('forms');

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsField.php';
include_once './modules/forms/classes/formsRecord.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);

switch($op)
{
    case 'forms_save':
        $objForm = new formsForm();
        if (isset($_POST['forms_id']) && $_POST['forms_id'] != '')
        {
            $objForm->open($_POST['forms_id']);
            $objForm->fields['autobackup'] = $_POST['forms_autobackup'];
            $objForm->fields['autobackup_date'] = ploopi_local2timestamp($_POST['forms_autobackup_date']);
            $objForm->save();
            ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$_POST['forms_id']}");
        }
        ploopi_redirect('admin.php');
    break;

    case 'forms_reply_display':
    case 'forms_reply_add':
    case 'forms_reply_modify':
        if (ploopi_isactionallowed(_FORMS_ACTION_ADDREPLY) || $op == 'forms_reply_display')
        {
            $objForm = new formsForm();

            if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
            {
                if (ploopi_set_flag('forms_nbclick', $_GET['forms_id'])) $objForm->fields['viewed']++;
                $objForm->save(false);

                include './modules/forms/public_forms_display.php';
            }
            else ploopi_redirect('admin.php');
        }
        else ploopi_redirect('admin.php');
    break;

    /**
     * Consultation des données
     */
    case 'forms_viewreplies':
    case 'forms_filter':
    case 'forms_deletedata':
        $objForm = new formsForm();

        if (empty($_REQUEST['forms_id']) || !is_numeric($_REQUEST['forms_id']) || !$objForm->open($_REQUEST['forms_id'])) ploopi_redirect('admin.php');

        if ($objForm->isPublished() && (!$objForm->fields['option_adminonly'] || ploopi_isactionallowed(_FORMS_ACTION_ADMIN)))
        {

            if ($op == 'forms_deletedata')
            {
                $objForm->deleteData();
                ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}");
            }
            else
            {
                /**
                 * Lecture des données du formulaire
                 */
                list($arrData, $intNumRows, $arrFormFilter) = $objForm->prepareData();

                include_once './modules/forms/public_forms_viewreplies.php';
            }
        }
        else ploopi_redirect('admin.php');
    break;

    default:
        include('./modules/forms/public_forms_list.php');
    break;
}
?>
