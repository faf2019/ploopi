<?php
/*
 Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */
ploopi\module::init('forms');

global $field_types;
global $field_formats;
global $field_operators;
global $form_types;
global $forms_graphic_types;
global $forms_graphic_line_aggregation;
global $forms_graphic_operation;

include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsField.php';
include_once './modules/forms/classes/formsRecord.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$sqllimitgroup = ' AND ploopi_mod_forms_form.id_workspace IN ('.ploopi\system::viewworkspaces($_SESSION['ploopi']['moduleid']).')';

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

switch($op)
{
    case 'forms_save':
        // Paramétrage de l'archivage : droit dédié, sur un formulaire de l'instance courante
        if (ploopi\acl::isactionallowed(_FORMS_ACTION_BACKUP) && isset($_POST['forms_id']) && ($objForm = formsForm::openInModule($_POST['forms_id'])))
        {
            $objForm->fields['autobackup'] = isset($_POST['forms_autobackup']) ? (int)$_POST['forms_autobackup'] : 0;
            $objForm->fields['autobackup_date'] = ploopi\date::local2timestamp($_POST['forms_autobackup_date']);
            $objForm->save();
            ploopi\output::redirect("admin.php?op=forms_viewreplies&forms_id={$_POST['forms_id']}");
        }
        ploopi\output::redirect('admin.php');
    break;

    case 'forms_reply_display':
    case 'forms_reply_add':
    case 'forms_reply_modify':
        if (ploopi\acl::isactionallowed(_FORMS_ACTION_ADDREPLY) || $op == 'forms_reply_display')
        {
            $objForm = formsForm::openInModule(isset($_GET['forms_id']) ? $_GET['forms_id'] : null);

            /**
             * Ouvrir une réponse existante pour la modifier : mêmes conditions que le
             * bouton « Modifier » de la liste des réponses
             */
            if ($objForm && $op == 'forms_reply_modify')
            {
                $objRecord = new formsRecord($objForm);
                if (empty($_GET['record_id']) || !is_numeric($_GET['record_id']) || !$objRecord->open($_GET['record_id']) || !$objForm->isRecordEditable($objRecord->fields, _FORMS_ACTION_ADDREPLY)) $objForm = false;
            }

            if ($objForm)
            {
                if (ploopi\session::setflag('forms_nbclick', $_GET['forms_id'])) $objForm->fields['viewed']++;
                $objForm->quicksave();

                include './modules/forms/public_forms_display.php';
            }
            else ploopi\output::redirect('admin.php');
        }
        else ploopi\output::redirect('admin.php');
    break;

    /**
     * Consultation des données
     */
    case 'forms_viewreplies':
    case 'forms_filter':
    case 'forms_deletedata':
        $objForm = formsForm::openInModule(isset($_REQUEST['forms_id']) ? $_REQUEST['forms_id'] : null);

        if (!$objForm) ploopi\output::redirect('admin.php');

        if ($objForm->isPublished() && (!$objForm->fields['option_adminonly'] || ploopi\acl::isactionallowed(_FORMS_ACTION_ADMIN)))
        {

            if ($op == 'forms_deletedata')
            {
                // Même droit que le bouton « Supprimer les données filtrées »
                if (ploopi\acl::isactionallowed(_FORMS_ACTION_DELETE)) $objForm->deleteData();
                ploopi\output::redirect("admin.php?op=forms_viewreplies&forms_id={$objForm->fields['id']}");
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
        else ploopi\output::redirect('admin.php');
    break;

    default:
        include('./modules/forms/public_forms_list.php');
    break;
}
?>
