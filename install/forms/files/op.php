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
 * Opérations
 *
 * @package forms
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Opérations pour tous les utilisateurs connectés ou non
 */

switch($ploopi_op)
{
    case 'forms_tablelink_values':
        include_once './modules/forms/classes/formsForm.php';
        include_once './modules/forms/classes/formsField.php';

        //ploopi_die($_GET);

        if (!empty($_GET['forms_fields']) && !empty($_GET['forms_params']) && !empty($_GET['forms_requested']))
        {
            $arrParams = array();
            foreach($_GET['forms_params'] as $intFieldId => $strValue)
            {
                // Requête Ajax en UTF8
                $strValue = utf8_decode($strValue);

                $objField = new formsField();
                $objLinkedField = new formsField();
                if ($objField->open($intFieldId) && $objLinkedField->open($objField->fields['values']))
                {
                    $arrParams[$objLinkedField->fields['fieldname']] = $strValue;
                }
            }

            // Champ à remplir
            $objField = new formsField();
            $objForm = new formsForm();
            // On vérifie l'existence du champ, du formulaire et qu'il s'agit d'un formulaire CMS (donc public)
            if ($objField->open($_GET['forms_requested']) && ($_SESSION['ploopi']['connected'] || ($objForm->open($objField->fields['id_form']) && $objForm->fields['typeform'] == 'cms')))
            {
                // Valeur de la table liée
                $objFieldValues = new formsField();
                if ($objFieldValues->open($objField->fields['values']))
                {
                    ploopi_print_json(array_keys($objFieldValues->getValues($arrParams)));
                }
            }
        }

        ploopi_die();
    break;

    case 'forms_reply_save':
        include './modules/forms/op_reply_save.php';
    break;


    case 'forms_print':
        include_once './modules/forms/classes/formsForm.php';
        $objForm = new formsForm();
        if (!empty($_REQUEST['forms_id']) && is_numeric($_REQUEST['forms_id']) && $objForm->open($_REQUEST['forms_id']))
        {
            ?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
            <head>
                <meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
                <script type="text/javascript" src="./lib/protoaculous/protoaculous.min.js"></script>
                <link href="./modules/forms/templates/default/style.css" rel="stylesheet" type="text/css">
                <link href="./modules/forms/templates/default/print.css" rel="stylesheet" type="text/css">
                </head>
                <body>
                    <div class="forms_form">
                    <form id="form"></form>
                    </div>
                    <script type="text/javascript">
                        $('form').innerHTML = window.opener.document.forms_form_<? echo $_REQUEST['forms_id']; ?>.innerHTML;
                        Event.observe(window, 'load', function() {
                            <?
                            for ($i=1; $i<=$objForm->getNbPanels();$i++)
                            {
                                ?>
                                $('panel_<? echo $i; ?>').style.display = 'block';
                                <?
                            }
                            ?>
                            window.print();
                            window.close();
                        });
                    </script>
                </body>
            </html>
            <?
        }
        ploopi_die();
    break;
}


/**
 * Opérations pour les utilisateurs connectés uniquement
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On vérifie qu'on est bien dans le module FORMS.
     */

    if (ploopi_ismoduleallowed('forms'))
    {
        switch($ploopi_op)
        {
            /**
             * CONTROLEURS ADMIN
             */
            case 'form_import_file':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsForm.php';

                $objForm = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    if (!empty($_FILES['forms_import_file']) && file_exists($_FILES['forms_import_file']['tmp_name']))
                    {
                        // On fait sauter le timeout de php car le traitement peut être long
                        if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

                        $ptrFileHandler = fopen($_FILES['forms_import_file']['tmp_name'], 'r');
                        $arrData = array();
                        $arrKeys = array();
                        $intCount = 0;

                        // Import CSV
                        while (($arrLine = fgetcsv($ptrFileHandler)) !== false)
                        {
                            if (empty($arrKeys)) $arrKeys = $arrLine;
                            else
                            {
                                /**
                                 * On instancie l'enregistrement
                                 * @var formsRecord
                                 */
                                $objRecord = new formsRecord($objForm);
                                $objRecord->init_description();
                                $booValid = false;
                                foreach($arrLine as $strKey => $strValue)
                                {
                                    if (isset($arrKeys[$strKey]) && isset($objRecord->fields[$arrKeys[$strKey]]))
                                    {
                                        $booValid = true;
                                        $objRecord->fields[$arrKeys[$strKey]] = $strValue;
                                    }
                                }

                                if ($booValid)
                                {
                                    $objRecord->save();
                                    $intCount++;
                                }
                            }
                        }

                        fclose($ptrFileHandler);

                        ploopi_redirect("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}&ploopi_mod_msg=_FORMS_MESS_OK_8");
                    }
                }

                ploopi_redirect('admin.php');
            break;

            case 'forms_import':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsForm.php';

                $objForm = new formsForm();
                if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id']) && $objForm->open($_POST['forms_id']))
                {
                    $objForm = new form('forms_import_form', ploopi_urlencode("admin-light.php?ploopi_op=form_import_file&forms_id={$_POST['forms_id']}"));
                    $objForm->addField( new form_field( 'input:file', 'Fichier', '', 'forms_import_file', 'forms_import_file', array('description' => 'Format CSV') ) );
                    $objForm->addButton( new form_button('input:button', 'Fermer', null, null, array('onclick' => "ploopi_hidepopup('forms_import');")) );
                    $objForm->addButton( new form_button('input:submit', 'Importer', null, null, array('style' => 'margin-left:2px;')) );

                    echo $skin->create_popup('Import de données CSV', $objForm->render(), 'forms_import');
                }

                ploopi_die();
            break;

            case 'forms_preview':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsForm.php';

                $objForm = new formsForm();
                if (!empty($_POST['forms_id']) && is_numeric($_POST['forms_id']) && $objForm->open($_POST['forms_id']))
                {
                    echo $skin->create_popup('Aperçu du formulaire', $objForm->render(null, 'preview', false, false), 'forms_preview');
                }

                ploopi_die();
            break;

            case 'forms_field_save':
            case 'forms_separator_save':
            case 'forms_captcha_save':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsField.php';

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

                    if ($ploopi_op == 'forms_separator_save')
                    {
                        $field->fields['separator'] = 1;
                        if (!isset($_POST['field_option_pagebreak'])) $field->fields['option_pagebreak'] = 0;
                    }
                    elseif($ploopi_op == 'forms_captcha_save')
                    {
                        $field->fields['captcha'] = 1;
                        $field->fields['option_needed'] = 1;
                    }
                    else
                    {
                        if (!isset($_POST['field_option_needed'])) $field->fields['option_needed'] = 0;
                        if (!isset($_POST['field_option_formview'])) $field->fields['option_formview'] = 0;
                        if (!isset($_POST['field_option_arrayview'])) $field->fields['option_arrayview'] = 0;
                        if (!isset($_POST['field_option_exportview'])) $field->fields['option_exportview'] = 0;
                        if (!isset($_POST['field_option_wceview'])) $field->fields['option_wceview'] = 0;
                        if (!isset($_POST['field_option_adminonly'])) $field->fields['option_adminonly'] = 0;
                        if (!isset($_POST['field_option_pagebreak'])) $field->fields['option_pagebreak'] = 0;

                        if (!$field->fields['option_formview']) $field->fields['option_needed'] = 0;
                    }

                    $field->save();
                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$_GET['forms_id']}&ploopi_mod_msg=_FORMS_MESS_OK_3");
                }
                else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
            break;

            case 'forms_save':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsForm.php';

                $forms = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id'])) $forms->open($_GET['forms_id']);
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
                if (!isset($_POST['forms_option_adminonly'])) $forms->fields['option_adminonly'] = 0;

                if (!isset($_POST['forms_option_multidisplaysave'])) $forms->fields['option_multidisplaysave'] = 0;
                if (!isset($_POST['forms_option_multidisplaypages'])) $forms->fields['option_multidisplaypages'] = 0;

                // Sécurité pour les grosses tables
                if ($forms->fields['nbline'] > 500) $forms->fields['nbline'] = 500;
                if ($forms->fields['nbline'] <= 0) $forms->fields['nbline'] = 100;

                if (!empty($forms->fields['autobackup_date'])) $forms->fields['autobackup_date'] = ploopi_local2timestamp($forms->fields['autobackup_date']);

                $forms->setuwm();
                $forms->save();

                ploopi_redirect("admin.php?formsTabItem=formlist&op=forms_modify&forms_id={$forms->fields['id']}&ploopi_mod_msg=_FORMS_MESS_OK_1");
            break;

            case 'forms_delete':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsForm.php';

                $forms = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $forms->open($_GET['forms_id'])) $forms->delete();
                ploopi_redirect('admin.php?ploopi_mod_msg=_FORMS_MESS_OK_2');
            break;

            case 'forms_field_delete':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsField.php';

                if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
                {
                    $field = new formsField();
                    if ($field->open($_GET['field_id'])) $field->delete();
                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$field->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_4");
                }
                else ploopi_redirect('admin.php?ploopi_mod_msg=_FORMS_MESS_OK_4');
            break;


            case 'forms_field_moveup':
            case 'forms_field_movedown':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsField.php';

                if (!empty($_GET['field_id']) && is_numeric($_GET['field_id']))
                {
                    $field = new formsField();
                    $field->open($_GET['field_id']);

                    $select = "Select min(position) as minpos, max(position) as maxpos from ploopi_mod_forms_field where id_form = {$field->fields['id_form']}";
                    $db->query($select);
                    $fields = $db->fetchrow();

                    if ($ploopi_op == 'forms_field_movedown')
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
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsGraphic.php';

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
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsGraphic.php';

                $objGraphic = new formsGraphic();
                if (!empty($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
                {
                    $objGraphic->delete();
                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$objGraphic->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_6");
                }
                else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
            break;

            /*
            case "export":
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
                {
                    $forms = new formsForm();
                    $forms->open($_GET['forms_id']);
                    include './modules/forms/public_forms_export.php';
                }
                else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
            break;
            */









            case 'forms_reply_delete':
                ploopi_init_module('forms');

                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsRecord.php';

                $objForm = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    $objRecord = new formsRecord($objForm);

                    if (!empty($_GET['record_id']) && is_numeric($_GET['record_id']) && $objRecord->open($_GET['record_id']))
                    {
                        if (ploopi_isadmin() || (
                            ploopi_isactionallowed(_FORMS_ACTION_DELETE) && (
                                ($objForm->fields['option_modify'] == 'user' && $objRecord->fields['user_id'] == $_SESSION['ploopi']['userid']) ||
                                ($objForm->fields['option_modify'] == 'group' && $objRecord->fields['workspace_id'] == $_SESSION['ploopi']['workspaceid'])  ||
                                ($objForm->fields['option_modify'] == 'all')
                            )
                        ))
                        {
                            $objRecord->delete();
                        }
                    }

                    ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
                }

                ploopi_redirect('admin.php');
            break;


            case 'forms_download_file':
                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsRecord.php';
                include_once './modules/forms/classes/formsField.php';

                $objForm = new formsForm();

                // Ouverture formulaire
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    $objRecord = new formsRecord($objForm);
                    $objField = new formsField();

                    // Ouverture enregistrement + champ
                    if (!empty($_GET['record_id']) && !empty($_GET['field_id']) && is_numeric($_GET['record_id']) && is_numeric($_GET['field_id']) && $objRecord->open($_GET['record_id']) && $objField->open($_GET['field_id']))
                    {
                        if (!empty($objRecord->fields[$objField->fields['fieldname']]))
                        {
                            ploopi_downloadfile(
                                formsField::getFilePath($_GET['forms_id'], _PLOOPI_SEP.$_GET['record_id'], $objRecord->fields[$objField->fields['fieldname']]),
                                $objRecord->fields[$objField->fields['fieldname']]
                            );
                        }
                    }
                    // On ne passe ici qu'en cas d'échec de téléchargement
                    ploopi_redirect("admin.php?op=forms_viewreplies&forms_id={$_GET['forms_id']}");
                }
                // Formulaire invalide
                ploopi_redirect('admin.php');
            break;

            case 'forms_xml_switchdisplay':
                if (!empty($_GET['display']))
                {
                    $switch = (!isset($_GET['switch'])) ? 'empty' : $_GET['switch'];
                    $_SESSION['forms'][$_SESSION['ploopi']['moduleid']][$switch] = $_GET['display'];
                }
                ploopi_die();
            break;

            case 'forms_export':
                ploopi_init_module('forms');
                if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT) && !empty($_GET['forms_id']) && is_numeric($_GET['forms_id'])) include './modules/forms/op_export.php';
            break;

            case 'forms_print_array':
                ploopi_init_module('forms');
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id'])) include './modules/forms/op_print.php';
            break;

            case 'forms_delete_data':
                include_once './modules/form/include/global.php';

                if (ploopi_isactionallowed(_FORMS_ACTION_BACKUP))
                {
                    ?>
                    <div style="background:#f0f0f0;border-bottom:1px solid #c0c0c0;font-weight:bold;padding:2px;">Suppression des données</div>
                    <div style="padding:2px;">
                    <?php
                    if (!empty($_GET['form_id']) && !empty($_GET['form_delete_date']))
                    {
                        $form_delete_date = ploopi_local2timestamp($_GET['form_delete_date']);

                        $form_delete_date = ploopi_timestamp_add($form_delete_date, 0, 0, 0, 0, 1, 0);

                        $sql = "SELECT COUNT(*) as c FROM ploopi_mod_forms_reply WHERE id_form = '".$db->addslashes($_GET['form_id'])."' AND date_validation < {$form_delete_date}";
                        $db->query($sql);
                        $row = $db->fetchrow();

                        echo "{$row['c']} enregistement(s) ont été supprimés";

                        $sql =  "
                                DELETE  r, rf
                                FROM    ploopi_mod_forms_reply r,
                                        ploopi_mod_forms_reply_field rf
                                WHERE   r.id_form = '".$db->addslashes($_GET['form_id'])."'
                                AND     r.date_validation < {$form_delete_date}
                                AND     rf.id_reply = r.id
                                ";
                        $db->query($sql);
                    }
                    ?>
                    </div>
                    <div style="background:#f0f0f0;border-top:1px solid #c0c0c0;text-align:right;padding:2px;"><a href="javascript:void(0);" onmouseup="javascript:ploopi_hidepopup('forms_deletedata');document.location.reload();">Fermer</a></div>
                    <?php
                }
                ploopi_die();
            break;

            case 'forms_graphic_display':
                ob_start();
                ?>
                <div style="background-color:#fff;overflow:auto;">
                <?php
                if (isset($_POST['forms_graphic_id']) && isset($_POST['forms_graphic_width']))
                {
                    $strUrl = ploopi_urlencode("admin-light.php?ploopi_op=forms_graphic_generate&forms_graphic_id={$_POST['forms_graphic_id']}&forms_graphic_width={$_POST['forms_graphic_width']}&forms_rand=".microtime());
                    ?>
                    <p class="ploopi_va" style="padding:4px;background:#eee;border-bottom:1px solid #ccc;">
                        <a class="forms_export_link" href="<?php echo $strUrl; ?>"><img src="./modules/forms/img/mime/png.png" /> Télécharger l'image</a>
                    </p>
                    <img style="margin:4px;display:block;clear:both;" src="<?php echo $strUrl; ?>" />
                    <?
                }
                else
                {
                    echo "erreur";
                }
                ?>
                </div>
                <?php
                $strContent = ob_get_contents();
                ob_end_clean();
                ploopi_die($skin->create_popup('Graphique', $strContent, 'forms_popup_graphic'));
            break;

            case 'forms_graphic_generate':
                include_once './modules/forms/classes/formsGraphic.php';
                include_once './modules/forms/classes/formsField.php';

                $objGraphic = new formsGraphic();
                if (isset($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
                {
                    $intWidth = isset($_GET['forms_graphic_width']) && is_numeric($_GET['forms_graphic_width']) ? $_GET['forms_graphic_width'] : null;

                    $objGraphic->render($intWidth);
                }
                ploopi_die();
            break;

        }
    }

    /**
     * Autres opérations (appels externes)
     */

    /*
    switch($ploopi_op)
    {
        case 'forms_download_file':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                if (!empty($_GET['record_id']) && !empty($_GET['field_id']) && is_numeric($_GET['record_id']) && is_numeric($_GET['field_id']))
                {
                    include_once './modules/forms/classes/formsReplyField.php';
                    $reply_field = new formsReplyField();
                    if ($reply_field->open($_GET['record_id'], $_GET['field_id']))
                    {
                        $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$id_module._PLOOPI_SEP.$reply_field->fields['id_form']._PLOOPI_SEP.$_GET['record_id']._PLOOPI_SEP;
                        ploopi_downloadfile("{$path}{$reply_field->fields['value']}", $reply_field->fields['value']);
                    }
                }
            }
            ploopi_die();
        break;

        case 'forms_display':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/classes/formsForm.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/op_viewlist.php';
            }
            ploopi_die();
        break;

        case 'forms_export':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/classes/formsForm.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/public_forms_export.php';
            }
            ploopi_die();
        break;

        case 'forms_openreply':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/classes/formsForm.php';

                $record_id = $_GET['forms_record_id'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                $forms = new formsForm();
                $forms->open($id_form);

                include_once './modules/forms/op_display.php';

                $content = ob_get_contents();
                ob_end_clean();

                echo $skin->create_popup($forms->fields['label'], $content, 'popup_forms_openreply');
            }
            ploopi_die();
        break;

        case 'forms_save':
            if (!empty($_POST['forms_fuid']) && isset($_SESSION['forms'][$_POST['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/classes/formsForm.php';
                include_once './modules/forms/classes/formsReply.php';
                include_once './modules/forms/classes/formsReplyField.php';

                $record_id = $_POST['forms_record_id'];
                $id_form = $_SESSION['forms'][$_POST['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_POST['forms_fuid']]['id_module'];

                include_once './modules/forms/op_save.php';
                ?>
                <script type="text/javascript">
                    window.parent.document.location.reload();
                </script>
                <?php
            }
            ploopi_die();
        break;
    }
    */
}
?>
