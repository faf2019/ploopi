<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Opérations du module dbreport
 *
 * @package dbreport
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * On vérifie qu'on est bien dans le module dbreport.
 */

if (ploopi_ismoduleallowed('dbreport'))
{
    switch($ploopi_op)
    {
        // Popup d'ajout/modif d'une requête
        case 'dbreport_query_add':
        case 'dbreport_query_modify':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './include/classes/form.php';
            include_once './include/classes/query.php';
            include_once './modules/dbreport/classes/class_dbreport_query.php';

            $objDbrQuery = new dbreport_query();
            $strUrl = "admin-light.php?ploopi_op=dbreport_query_save";

            if ($ploopi_op == 'dbreport_query_modify' && isset($_POST['dbreport_query_id']) && is_numeric($_POST['dbreport_query_id']) && $objDbrQuery->open($_POST['dbreport_query_id']))
            {
                $strUrl .= "&dbreport_query_id={$_POST['dbreport_query_id']}";

                // Recherche des modules sélectionnés pour la requête
                $objQuery = new ploopi_query_select();
                $objQuery->add_select('id_module_type');
                $objQuery->add_from('ploopi_mod_dbreport_query_module_type');
                $objQuery->add_where('id_query = %d', $_POST['dbreport_query_id']);
                $objRs = $objQuery->execute();
                $arrModuleSel = $objRs->getarray(true);

                // Récupération des modules utilisés dans la requête
                $objQuery = new ploopi_query_select();
                $objQuery->add_select('DISTINCT mbt.id_module_type, 1 as active');
                $objQuery->add_from('ploopi_mod_dbreport_querytable drt');
                $objQuery->add_from('ploopi_mb_table mbt');
                $objQuery->add_where('drt.id_query = %d', $_POST['dbreport_query_id']);
                $objQuery->add_where('drt.tablename = mbt.name');
                $objRs = $objQuery->execute();
                $arrActiveModuleTypes = $objRs->getarray(true);

                $strTitle = "Modification d'une requête";
            }
            else
            {
                $objDbrQuery->init_description();
                $arrModuleSel = array();
                $arrActiveModuleTypes = array();

                $strTitle = "Création d'une nouvelle requête";
            }

            $objForm = new form( 'dbreport_form_query_add', ploopi_urlencode($strUrl), 'post', array('legend' => '* Champs obligatoires', 'style' => 'border-bottom:1px solid #aaa;background:#f8f8f8;padding:4px;') );
            $objForm->addField( new form_field('input:text', 'Libellé:', $objDbrQuery->fields['label'], 'dbreport_query_label', null, array('required' => true)) );

            if (ploopi_isactionallowed(dbreport::_ACTION_LOCK))
                $objForm->addField( new form_checkbox('Verrouiller:', '1', $objDbrQuery->fields['locked'], 'dbreport_query_locked') );

            // Panel "Modules dispos"
            $objForm->addPanel($objPanel = new form_panel('dbreport_panel_modules', 'Modules disponibles', array('style' => 'margin:0 4px;')));

            // Recherche des modules dispos (métabase)
            $objQuery = new ploopi_query_select();
            $objQuery->add_select('distinct(mt.id), mt.label');
            $objQuery->add_from('ploopi_mb_table mbt');
            $objQuery->add_from('ploopi_module_type mt');
            $objQuery->add_where('mbt.id_module_type = mt.id');
            $objQuery->add_orderby('mt.label');
            $objRs = $objQuery->execute();

            // Affichage des cases à cocher pour chaque module
            while ($row = $objRs->fetchrow())
            {
                $objPanel->addField( new form_checkbox("{$row['label']}:", $row['id'], in_array($row['id'], $arrModuleSel), '_dbreport_query_id_module_type[]', "dbreport_query_id_module_type_{$row['id']}", array('disabled' => isset($arrActiveModuleTypes[$row['id']]))));

                // Module utilisé : cas particulier, il faut ajouter un champ hidden pour "compenser" la propriété "disabled" de la checkbox précédente
                if (isset($arrActiveModuleTypes[$row['id']])) $objPanel->addField( new form_hidden($row['id'], '_dbreport_query_id_module_type[]') );
            }

            // Panel "Requête"

            $objForm->addPanel($objPanel = new form_panel('dbreport_panel_query', 'Requête', array('style' => 'margin:0 4px;')));
            $objPanel->addField( new form_field('input:text', 'Nombre de lignes max:', $objDbrQuery->fields['rowlimit'], 'dbreport_query_rowlimit') );

            // Panel "WebService" (modification uniquement)
            if (!$objDbrQuery->isnew())
            {

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_webservice', 'Webservice', array('style' => 'margin:0 4px;')));
                $objPanel->addField( new form_checkbox('Activé:', '1', $objDbrQuery->fields['ws_activated'], 'dbreport_query_ws_activated') );
                $objPanel->addField( new form_field('input:text', 'Identifiant unique:', $objDbrQuery->fields['ws_id'], 'dbreport_query_ws_id') );
                $objPanel->addField( new form_field('input:text', 'Code d\'accès:', $objDbrQuery->fields['ws_code'], 'dbreport_query_ws_code') );
                $objPanel->addField( new form_field('input:text', 'IP autorisée:', $objDbrQuery->fields['ws_ip'], 'dbreport_query_ws_ip') );
                if ($objDbrQuery->fields['ws_id'] != '')
                {
                    $strWsUri = $objDbrQuery->getwsuri();
                    $objPanel->addField( new form_text('URI:', "<a href=\"{$strWsUri}\" target=\"_blank\">{$strWsUri}</a>") );
                }
            }

            $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
            $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

            ploopi_die($skin->create_popup($strTitle, $objForm->render(), $ploopi_op));
        break;

        // Enregistrement d'une requête
        case 'dbreport_query_save':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './include/classes/query.php';
            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_query_module_type.php';

            // Si pas d'action, interdiction de modifier la valeur
            if (!ploopi_isactionallowed(dbreport::_ACTION_LOCK)) unset($_POST['dbreport_query_locked']);

            $objDbrQuery = new dbreport_query();
            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id'])) $objDbrQuery->open($_GET['dbreport_query_id']);
            $objDbrQuery->setvalues($_POST,'dbreport_query_');
            if (!isset($_POST['dbreport_query_ws_activated'])) $objDbrQuery->fields['ws_activated'] = 0;
            if (ploopi_isactionallowed(dbreport::_ACTION_LOCK) && !isset($_POST['dbreport_query_locked'])) $objDbrQuery->fields['locked'] = 0;
            $objDbrQuery->setuwm();
            $objDbrQuery->save();

            // Suppression des modules associés à la requêtes
            $objQuery = new ploopi_query_delete();
            $objQuery->add_from('ploopi_mod_dbreport_query_module_type');
            $objQuery->add_where('id_query = %d', $objDbrQuery->fields['id']);
            $objQuery->execute();

            if (!empty($_POST['_dbreport_query_id_module_type']) && is_array($_POST['_dbreport_query_id_module_type']))
            {
                foreach($_POST['_dbreport_query_id_module_type'] as $intModuleType)
                {
                    $objDbrQMT = new dbreport_query_module_type();
                    $objDbrQMT->fields['id_query'] = $objDbrQuery->fields['id'];
                    $objDbrQMT->fields['id_module_type'] = $intModuleType;
                    $objDbrQMT->save();
                }
            }
            ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQuery->fields['id']}");
        break;

        // Suppression d'une requête
        case 'dbreport_query_delete':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';

            $objDbrQuery = new dbreport_query();
            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id'])) $objDbrQuery->delete();

            ploopi_redirect('admin.php');
        break;


        // Enregistrement d'une transformation
        case 'dbreport_transformation_save':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';

            $objDbrQuery = new dbreport_query();
            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id'])) $objDbrQuery->open($_GET['dbreport_query_id']);
            $objDbrQuery->setvalues($_POST,'dbreport_query_');
            if (!isset($_POST['dbreport_query_transformation'])) $objDbrQuery->fields['transformation'] = '';
            $objDbrQuery->setuwm();
            $objDbrQuery->save();

            ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQuery->fields['id']}#dbreport_trans");
        break;

        // Enregistrement d'un graphique
        case 'dbreport_chart_save':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';

            $objDbrQuery = new dbreport_query();
            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id'])) $objDbrQuery->open($_GET['dbreport_query_id']);
            $objDbrQuery->setvalues($_POST,'dbreport_query_');
            // Traitement des checkboxes
            foreach(array('chart_legend_display', 'chart_indexes_display', 'chart_interlaced_display', 'chart_animation') as $strCheckbox)
                if (!isset($_POST["dbreport_query_{$strCheckbox}"]))
                    $objDbrQuery->fields[$strCheckbox] = 0;

            if (isset($_POST["fck_dbreport_query_chart_tooltip_format"])) {
                $objDbrQuery->fields['chart_tooltip_format'] = $_POST["fck_dbreport_query_chart_tooltip_format"];
            }
            if (isset($_POST["fck_dbreport_query_chart_indexes_format"])) {
                $objDbrQuery->fields['chart_indexes_format'] = $_POST["fck_dbreport_query_chart_indexes_format"];
            }


            $objDbrQuery->setuwm();
            $objDbrQuery->save();

            ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQuery->fields['id']}#dbreport_chart");
        break;

        case 'dbreport_canvas2png':
            if (!empty($_POST['image'])) {
                // Extraction des données
                list($type, $image) = explode(',', $_POST['image']);
                // Fichier temporaire
                $strFileName = tempnam(_PLOOPI_PATHDATA, 'dbreport_canvas');
                file_put_contents($strFileName, base64_decode($image));

                ploopi_downloadfile($strFileName, 'graphique.png', true, true, true);
            }
            ploopi_die();
        break;


        // Génération d'un graphique
        case 'dbreport_chart_generate':
            include_once './modules/dbreport/op_chart_generate.php';
        break;


        // Popup d'ajout d'un champ dans une requête
        case 'dbreport_queryfield_add':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/op_queryfield_add.php';
        break;

        // Ajout d'une sélection de champs dans une requête
        case 'dbreport_queryfield_save':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_queryfield.php';

            include_once './include/classes/mb.php';

            $objDbrQuery = new dbreport_query();

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
            {
                if (!empty($_POST['dbreport_fieldnames']) && is_array($_POST['dbreport_fieldnames']))
                {
                    foreach ($_POST['dbreport_fieldnames'] as $strFieldName)
                    {
                        $objDbrQueryField = new dbreport_queryfield();

                        $key = explode(".", $strFieldName);

                        $objMbField = new mb_field();
                        $objMbField->open($key[0],$key[1]);

                        $objDbrQueryField->fields['tablename'] = $key[0];
                        $objDbrQueryField->fields['fieldname'] = $key[1];
                        $objDbrQueryField->fields['label'] = $objMbField->fields['label'];
                        $objDbrQueryField->fields['id_query'] = $_GET['dbreport_query_id'];
                        $objDbrQueryField->fields['visible'] = 1;
                        $objDbrQueryField->save();

                        $objDbrQueryTable = new dbreport_querytable();
                        $objDbrQueryTable->fields['tablename'] = $strTableName;
                        $objDbrQueryTable->fields['id_query'] = $_GET['dbreport_query_id'];
                        $objDbrQueryTable->save();
                    }
                }
                else
                {
                    $objDbrQueryField = new dbreport_queryfield();
                    if (isset($_GET['dbreport_queryfield_id']) && is_numeric($_GET['dbreport_queryfield_id']) && $objDbrQueryField->open($_GET['dbreport_queryfield_id']))
                    {
                        $objDbrQueryField->setvalues($_POST,'dbreport_queryfield_');
                        $objDbrQueryField->save();
                    }
                }

                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$_GET['dbreport_query_id']}#dbreport_fields");
            }

            ploopi_redirect('admin.php');
        break;

        case 'dbreport_queryfield_modify':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/op_queryfield_modify.php';
        break;

        // Suppression d'un champ dans une requête
        case 'dbreport_queryfield_delete':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_queryfield.php';
            $objDbrQueryField = new dbreport_queryfield();

            if (isset($_GET['dbreport_queryfield_id']) && is_numeric($_GET['dbreport_queryfield_id']) && $objDbrQueryField->open($_GET['dbreport_queryfield_id']))
            {
                $objDbrQueryField->delete();
            }

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id'])) ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$_GET['dbreport_query_id']}#dbreport_fields");
            ploopi_redirect('admin.php');
        break;

        // Modification de la position d'un champ
        case 'dbreport_queryfield_position':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './include/classes/query.php';
            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_queryfield.php';

            $objDbrQueryField = new dbreport_queryfield();

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']))
            {
                if (isset($_GET['dbreport_fieldposition']) && isset($_GET['dbreport_queryfield_id']) && is_numeric($_GET['dbreport_queryfield_id']) && $objDbrQueryField->open($_GET['dbreport_queryfield_id']))
                {
                    $objQuery = new ploopi_query_select();
                    $objQuery->add_select('MIN(position) AS minposition, MAX(position) as maxposition');
                    $objQuery->add_from('ploopi_mod_dbreport_queryfield');
                    $objQuery->add_where('id_query = %d', $_GET['dbreport_query_id']);
                    $row = $objQuery->execute()->fetchrow();

                    if ($_GET['dbreport_fieldposition'] == 'lower')
                    {
                        $intLimitPos = $row['maxposition'];
                        $intMove = 1;
                    }
                    else
                    {
                        $intLimitPos = $row['minposition'];
                        $intMove = -1;
                    }

                    if ($intLimitPos != $objDbrQueryField->fields['position']) // ce n'est pas le dernier champ
                    {
                        // élément 1 qu'on va remplacer
                        $objQuery = new ploopi_query_update();
                        $objQuery->add_from('ploopi_mod_dbreport_queryfield');
                        $objQuery->add_set('position = 0');
                        $objQuery->add_where('position = %d AND id_query = %d', array($objDbrQueryField->fields['position'] + $intMove, $_GET['dbreport_query_id']));
                        $objQuery->execute();

                        // on déplace l'élément 2 vers le bas
                        $objQuery = new ploopi_query_update();
                        $objQuery->add_from('ploopi_mod_dbreport_queryfield');
                        $objQuery->add_set('position = %d', $objDbrQueryField->fields['position'] + $intMove);
                        $objQuery->add_where('position = %d AND id_query = %d', array($objDbrQueryField->fields['position'], $_GET['dbreport_query_id']));
                        $objQuery->execute();

                        // on remet l'élément 1 à la place du 2
                        $objQuery = new ploopi_query_update();
                        $objQuery->add_from('ploopi_mod_dbreport_queryfield');
                        $objQuery->add_set('position = %d', $objDbrQueryField->fields['position']);
                        $objQuery->add_where('position = 0 AND id_query = %d', $_GET['dbreport_query_id']);
                        $objQuery->execute();

                        // Mise à jour de la requête
                        $objDbrQuery = new dbreport_query();
                        if ($objDbrQuery->open($_GET['dbreport_query_id'])) $objDbrQuery->save();
                    }
                }

                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$_GET['dbreport_query_id']}#dbreport_fields");
            }

            ploopi_redirect('admin.php');
        break;

        // Popup d'ajout d'une table dans une requête
        case 'dbreport_querytable_add':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/op_querytable_add.php';
        break;

        // Ajout d'une sélection de table dans une requête
        case 'dbreport_querytable_save':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_querytable.php';
            include_once './modules/dbreport/classes/class_dbreport_queryrelation.php';

            $objDbrQuery = new dbreport_query();

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
            {
                $arrTableNames = is_array($_POST['dbreport_tablenames']) ? $_POST['dbreport_tablenames'] : array($_POST['dbreport_tablenames']);

                foreach ($arrTableNames as $strTableName)
                {
                    // Sélection des tables de la requête (pour chercher les relations avec les tables qu'on ajoute
                    $objQuery = new ploopi_query_select();
                    $objQuery->add_select('tablename');
                    $objQuery->add_from('ploopi_mod_dbreport_querytable');
                    $objQuery->add_where('id_query = %d', $objDbrQuery->fields['id']);
                    $arrTables = $objQuery->execute()->getarray(true);

                    // Sélection des relations en rapport avec la table qu'on ajoute
                    $objQuery = new ploopi_query_select();
                    $objQuery->add_from('ploopi_mb_relation mbr');
                    $objQuery->add_where('(mbr.tablesrc = %1$s AND mbr.tabledest IN (%2$t)) OR (mbr.tabledest = %1$s AND mbr.tablesrc IN (%2$t))', array($strTableName, implode(',', $arrTables)));
                    $objRs = $objQuery->execute();

                    // Enregistrement des relations propres à la requête
                    while($row = $objRs->fetchrow())
                    {
                        $objDbrQueryRelation = new dbreport_queryrelation();
                        $objDbrQueryRelation->fields['tablename_src'] = $row['tablesrc'];
                        $objDbrQueryRelation->fields['fieldname_src'] = $row['fieldsrc'];
                        $objDbrQueryRelation->fields['tablename_dest'] = $row['tabledest'];
                        $objDbrQueryRelation->fields['fieldname_dest'] = $row['fielddest'];
                        $objDbrQueryRelation->fields['id_query'] = $objDbrQuery->fields['id'];
                        $objDbrQueryRelation->save();
                    }

                    // Ajout de la table à la requête
                    $objDbrQueryTable = new dbreport_querytable();
                    $objDbrQueryTable->fields['tablename'] = $strTableName;
                    $objDbrQueryTable->fields['id_query'] = $objDbrQuery->fields['id'];
                    $objDbrQueryTable->save();
                }

                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQuery->fields['id']}");
            }
            ploopi_redirect('admin.php');
        break;

        // Suppression d'une table dans la requête
        case 'dbreport_querytable_delete':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_querytable.php';

            $objDbrQuery = new dbreport_query();

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
            {
                $objDbrQueryTable = new dbreport_querytable();

                if (isset($_GET['dbreport_querytable_id']) && is_numeric($_GET['dbreport_querytable_id']) && $objDbrQueryTable->open($_GET['dbreport_querytable_id'])) $objDbrQueryTable->delete();

                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$_GET['dbreport_query_id']}");
            }

            ploopi_redirect('admin.php');
        break;


        // Exécution de la requête
        case 'dbreport_query_exec':
            include_once './modules/dbreport/op_query_exec.php';
        break;

        case 'dbreport_queryrelation_modify':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';
            include_once './modules/dbreport/classes/class_dbreport_queryrelation.php';

            $objDbrQuery = new dbreport_query();
            $objDbrQueryRelation = new dbreport_queryrelation();


            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
            {
                if (isset($_GET['dbreport_queryrelation_src']) && isset($_GET['dbreport_queryrelation_dest']) && isset($_GET['dbreport_queryrelation_active']))
                {
                   list($strTableSrc, $strFieldSrc) = explode(',', $_GET['dbreport_queryrelation_src']);
                   list($strTableDest, $strFieldDest) = explode(',', $_GET['dbreport_queryrelation_dest']);

                   if ($objDbrQueryRelation->open($_GET['dbreport_query_id'], $strTableSrc, $strFieldSrc, $strTableDest, $strFieldDest)) {
                       $objDbrQueryRelation->fields['active'] = $_GET['dbreport_queryrelation_active'];
                       $objDbrQueryRelation->save();
                   }
                }
                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQuery->fields['id']}");
            }
            ploopi_redirect("admin.php");

        break;

        case 'dbreport_query_clone':
            ploopi_init_module('dbreport',false, false, false);

            if (!ploopi_isactionallowed(dbreport::_ACTION_MANAGE)) ploopi_logout();

            include_once './modules/dbreport/classes/class_dbreport_query.php';

            $objDbrQuery = new dbreport_query();

            if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
            {
                // Clonage objet et dépendances
                $objDbrQueryClone = clone $objDbrQuery;
                ploopi_redirect("admin.php?dbreport_op=query_modify&dbreport_query_id={$objDbrQueryClone->fields['id']}");
            }

            ploopi_redirect('admin.php');
        break;

        case 'dbreport_query_export':
            ploopi_init_module('dbreport',false, false, false);
            include_once './modules/dbreport/classes/class_dbreport_query.php';

            ob_start();

            $objDbrQuery = new dbreport_query();

            if (isset($_POST['dbreport_query_id']) && is_numeric($_POST['dbreport_query_id']) && $objDbrQuery->open($_POST['dbreport_query_id']))
            {
                ?>
                <div style="padding:2px;">
                    <form action="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=dbreport_query_exec&dbreport_query_id={$_POST['dbreport_query_id']}"); ?>" method="post">
                    <?php
                    // Lecture des paramètres de la requête
                    $arrParams = $objDbrQuery->getparams();

                    // La requête requiert des paramètres
                    if (!empty($arrParams))
                    {
                        ?>
                        <div style="padding:2px;"><b>Paramètres :</b></div>
                        <div class="ploopi_form">
                        <?
                        foreach($arrParams as $strParam => $arrParam)
                        {
                            ?>
                            <p>
                                <label><?php echo ploopi_htmlentities($strParam.' ('.$arrParam['label'].')'); ?>:</label>
                                <input type="text" class="text" name="<?php echo ploopi_htmlentities($strParam); ?>"  value="%"  />
                            </p>
                            <?
                        }
                        ?></div><?php
                    }
                    ?>

                    <div style="padding:2px;"><b>Choix du format :</b></div>
                    <div style="width:49%;float:left;">
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_ods');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_ods" value="ods" />
                            <img src="./modules/dbreport/img/mime/ods.png" />
                            <strong>ODS</strong><span>&nbsp;(OpenOffice Calc &#174;)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_xls');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_xls" value="xlsx" />
                            <img src="./modules/dbreport/img/mime/xls.png" />
                            <strong>XLSX</strong><span>&nbsp;(Microsoft Excel &#174;)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_csv');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_csv" value="csv" checked />
                            <img src="./modules/dbreport/img/mime/csv.png" />
                            <strong>CSV</strong><span style="color:red;">&nbsp;(RECOMMANDE)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_pdf');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_pdf" value="pdf" />
                            <img src="./modules/dbreport/img/mime/pdf.png" />
                            <strong>PDF</strong><span>&nbsp;(Adobe Acrobat &#174;)</span>
                        </p>
                    </div>
                    <div style="width:51%;float:right;">
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_html');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_html" value="html" />
                            <img src="./modules/dbreport/img/mime/html.png" />
                            <strong>HTML</strong><span>&nbsp;(HyperText Markup Language)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_odt');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_odt" value="odt" />
                            <img src="./modules/dbreport/img/mime/odt.png" />
                            <strong>ODT</strong><span>&nbsp;(OpenOffice Writer &#174;)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_xml');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_xml" value="xml" />
                            <img src="./modules/dbreport/img/mime/xml.png" />
                            <strong>XML</strong><span>&nbsp;(eXtensible Markup Language)</span>
                        </p>
                        <p class="ploopi_checkbox" style="padding:2px;" onclick="javascript:ploopi_checkbox_click(event,'dbreport_format_json');">
                            <input type="radio" class="radio" name="dbreport_format" id="dbreport_format_json" value="json" />
                            <img src="./modules/dbreport/img/mime/json.png" />
                            <strong>JSON</strong><span>&nbsp;(JavaScript Object Notation)</span>
                        </p>
                    </div>

                    <div style="clear:both;padding:2px;text-align:right;">
                        <input type="button" class="button" value="Annuler" onclick="javascript:ploopi_hidepopup('dbreport_query_export_popup');"/>
                        <input type="submit" class="button" value="Générer" />
                    </div>
                    </form>
                </div>
                <?php
            }

            $strContent = ob_get_contents();
            ob_end_clean();

            echo $skin->create_popup('Génération du résultat', $strContent, 'dbreport_query_export_popup');

            ploopi_die();
        break;
    }
}
