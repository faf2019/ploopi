<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2011 Ovensia
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
    // Sauvegarde rapide (et temporaire) des données du formulaire en cours de saisie
    case 'forms_quicksave':
        include_once './modules/forms/classes/formsForm.php';
        if (!empty($_POST['forms_form_id']))
        {
            $strVarName = formsForm::getVarName($_POST['forms_form_id']);
            // Lecture des valeurs pré-enregistrées du formulaire
            // $arrValues = ploopi_getsessionvar($strVarName);

            // Sauvegarde des nouvelles valeurs du formulaire
            foreach($_POST as $strKey => $mixValue)
            {
                if (substr($strKey, 0, 6) == 'field_')
                {
                    if (is_array($mixValue)) $mixValue = implode('||', $mixValue);
                    $arrValues[substr($strKey, 6)] = $mixValue;
                }
            }

            // Sauvegarde du panel sélectionné
            if (isset($_POST['forms_panel'])) $arrValues['panel'] = $_POST['forms_panel'];

            // Sauvegarde en session
            ploopi_setsessionvar($strVarName, $arrValues);
            // Sauvegarde en cookie (json + gz + base64), 30 jours
            setcookie($strVarName, ploopi_base64_encode(gzcompress(json_encode(ploopi_array_map('utf8_encode', $arrValues)), 9)), time()+86400*30);
        }
        ploopi_die();
    break;

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
        if (!empty($_REQUEST['forms_id']) && is_numeric($_REQUEST['forms_id']) && $objForm->open($_REQUEST['forms_id']) && isset($_REQUEST['record_id']))
        {
            ?>
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
            <head>
                <meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
                <script type="text/javascript" src="./lib/protoaculous/protoaculous.min.js"></script>
                <script type="text/javascript">
                ploopi = {};
                Event.observe(window, 'load', function() {
                    $('forms_form_<? echo $_REQUEST['forms_id']; ?>').innerHTML = $('forms_form_<? echo $_REQUEST['forms_id']; ?>').innerHTML.replace(/<(\/?)fieldset[^>]*>/ig, '').replace(/<legend[^>]*>.*<\/legend>/ig, '');
                    window.print();
                    window.close();
                });
                </script>
                <link href="./modules/forms/templates/default/style.css" rel="stylesheet" type="text/css">
                <link href="./modules/forms/templates/default/print.css" rel="stylesheet" type="text/css">
                </head>
                <body>
                <? $objForm->render($_REQUEST['record_id'], 'print'); ?>
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

            case 'forms_clone':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './include/classes/query.php';
                include_once './modules/forms/classes/formsForm.php';

                $objForm = new formsForm();
                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']) && $objForm->open($_GET['forms_id']))
                {
                    // Clone le formulaire
                    $objFormClone = clone $objForm;

                    if (isset($_GET['data']) && $_GET['data'] == 'true')
                    {
                        // Copie les données
                        $objQuerySel = new ploopi_query_select();
                        $objQuerySel->add_from($objForm->getDataTableName());

                        $objQuery = new ploopi_query_insert();
                        $objQuery->set_table($objFormClone->getDataTableName());
                        $objQuery->add_raw($objQuerySel->get_sql());
                        $objQuery->execute();
                    }

                    // Renvoi vers le clone
                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$objFormClone->fields['id']}");
                }

                ploopi_redirect('admin.php');
            break;

            case 'forms_import_file':
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
                    $arrFields = array();
                    foreach($objForm->getFields() as $objField) $arrFields[] = $objField->fields['fieldname'];

                    $objForm = new form('forms_import_form', ploopi_urlencode("admin-light.php?ploopi_op=forms_import_file&forms_id={$_POST['forms_id']}"));
                    $objForm->addField( new form_field( 'input:file', 'Fichier:', '', 'forms_import_file', 'forms_import_file', array('description' => 'Format CSV') ) );
                    $objForm->addField( new form_htmlfield('Séparateur de champs:', 'Virgule') );
                    $objForm->addField( new form_htmlfield('Séparateur de texte:', 'Double-quote') );
                    $objForm->addField( new form_htmlfield('Colonnes:', implode(', ', $arrFields)) );
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
            case 'forms_html_save':
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
                    }
                    else // nouveau
                    {
                        $field->fields['id_form'] = $_GET['forms_id'];
                    }

                    $field->setvalues($_POST,'field_');

                    if ($ploopi_op == 'forms_separator_save')
                    {
                        $field->fields['separator'] = 1;
                        if (!isset($_POST['field_option_pagebreak'])) $field->fields['option_pagebreak'] = 0;
                    }
                    if ($ploopi_op == 'forms_html_save')
                    {
                        $field->fields['html'] = 1;
                        if (!isset($_POST['field_option_disablexhtmlfilter'])) $field->fields['option_disablexhtmlfilter'] = 0;
                        if (!isset($_POST['field_option_pagebreak'])) $field->fields['option_pagebreak'] = 0;

                        if (isset($_POST['fck_field_xhtmlcontent']))
                        {
                            $field->fields['xhtmlcontent'] = $_POST['fck_field_xhtmlcontent'];
                            $field->fields['xhtmlcontent_cleaned'] = $field->fields['xhtmlcontent'];

                            // filtre activé ? nettoyage contenu XHTML
                            if (!$field->fields['option_disablexhtmlfilter']) $field->fields['xhtmlcontent_cleaned'] = ploopi_htmlpurifier($field->fields['xhtmlcontent_cleaned'], true);
                        }
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

                if (!isset($_POST['forms_export_landscape'])) $forms->fields['export_landscape'] = 0;
                if (!isset($_POST['forms_export_border'])) $forms->fields['export_border'] = 0;
                if (!isset($_POST['forms_export_fitpage_width'])) $forms->fields['export_fitpage_width'] = 0;
                if (!isset($_POST['forms_export_fitpage_height'])) $forms->fields['export_fitpage_height'] = 0;

                // Sécurité pour les grosses tables
                if ($forms->fields['nbline'] > 500) $forms->fields['nbline'] = 500;
                if ($forms->fields['nbline'] <= 0) $forms->fields['nbline'] = 100;

                if (!empty($forms->fields['autobackup_date'])) $forms->fields['autobackup_date'] = ploopi_local2timestamp($forms->fields['autobackup_date']);


                $forms->setuwm();
                $forms->save();

                ploopi_share_save(_FORMS_OBJECT_FORM, $forms->fields['id'], -1, 'forms_send_email');

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




            case 'forms_group_save':
                include_once './include/functions/crypt.php';

                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsGroup.php';

                $objGroup = new formsGroup();

                if (!empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
                {
                    if (!empty($_GET['forms_group_id']) && is_numeric($_GET['forms_group_id'])) $objGroup->open($_GET['forms_group_id']);

                    if ($objGroup->isnew()) $objGroup->fields['id_form'] = $_GET['forms_id'];

                    $objGroup->setvalues($_POST,'forms_group_');
                    $objGroup->fields['conditions'] = isset($_POST['_forms_group_cond']) ? ploopi_serialize($_POST['_forms_group_cond']) : '';
                    $objGroup->save();

                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$objGroup->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_9");
                }
                else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');

                ploopi_die();
            break;

            case 'forms_group_delete':
                ploopi_init_module('forms');

                if (!ploopi_isactionallowed(_FORMS_ACTION_ADMIN)) ploopi_redirect('admin.php');

                include_once './modules/forms/classes/formsGroup.php';

                $objGroup = new formsGroup();
                if (!empty($_GET['forms_group_id']) && is_numeric($_GET['forms_group_id']) && $objGroup->open($_GET['forms_group_id']))
                {
                    $objGroup->delete();
                    ploopi_redirect("admin.php?op=forms_modify&forms_id={$objGroup->fields['id_form']}&ploopi_mod_msg=_FORMS_MESS_OK_10");
                }
                else ploopi_redirect('admin.php?ploopi_mod_error=_FORMS_ERROR_2');
            break;


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

                            // Doit on recalculer les données (uniquement si agrégat)
                            $booCalculation = false;
                            $booAggregate = false;

                            foreach($objForm->getFields() as $objField)
                            {
                                if ($objField->fields['type'] == 'calculation')
                                {
                                    $booCalculation = true;

                                    // On va détecter si le calcul fait appel à un agrégat (dans ce cas il faut recalculer toutes les données du formulaire)
                                    $objParser = new formsArithmeticParser($objField->fields['formula']);

                                    // Extraction des variables de l'expression
                                    $arrVars = $objParser->getVars();

                                    // Pour chaque variable attendue dans l'expression
                                    foreach($arrVars as $strVar)
                                    {
                                        // Analyse de la variable
                                        if (preg_match('/C([0-9]+)_?([A-Z]{0,3})/', $strVar, $arrMatches) > 0)
                                        {
                                            if (!empty($arrMatches[2])) $booAggregate = true;
                                        }
                                    }
                                }
                            }

                            if ($booAggregate) $objForm->calculate();
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
                ploopi_init_module('forms');
                include_once './modules/forms/classes/formsForm.php';

                if (ploopi_isactionallowed(_FORMS_ACTION_BACKUP))
                {
                    ?>
                    <div style="background:#f0f0f0;border:1px solid #c0c0c0;padding:2px;"><strong>Suppression des données</strong>
                    <?php
                    $objForm = new formsForm();
                    if (!empty($_GET['form_id']) && $objForm->open($_GET['form_id']) && !empty($_GET['form_delete_date']))
                    {
                        $objForm->deleteToDate(ploopi_local2timestamp($_GET['form_delete_date'], '23:59:59'));
                    }
                    ?>
                    <br /><a href="javascript:void(0);" onmouseup="javascript:ploopi_hidepopup('forms_deletedata');document.location.reload();">Fermer</a>
                    </div>
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
}
