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
 * Popup d'ajout de tables dans une requête
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

include_once './include/classes/form.php';
include_once './include/classes/query.php';
include_once './modules/dbreport/classes/class_dbreport_query.php';

ob_start();

$objDbrQuery = new dbreport_query();

if (isset($_POST['dbreport_query_id']) && is_numeric($_POST['dbreport_query_id']) && $objDbrQuery->open($_POST['dbreport_query_id']))
{
    // Récupération des modules de la requête
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('id_module_type');
    $objQuery->add_from('ploopi_mod_dbreport_query_module_type');
    $objQuery->add_where('id_query = %d', $_POST['dbreport_query_id']);
    $arrModuleTypes = $objQuery->execute()->getarray(true);

    if (empty($arrModuleTypes)) echo "<div style=\"padding:4px;\">Vous devez d'abord sélectionner des modules</div>";
    else
    {
        // Récupération des tables de la requête
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('drt.tablename, mbt.label, mt.label as moduletype');
        $objQuery->add_from('ploopi_mod_dbreport_querytable drt');
        $objQuery->add_from('ploopi_mb_table mbt');
        $objQuery->add_from('ploopi_module_type mt');
        $objQuery->add_where('drt.id_query = %d', $_POST['dbreport_query_id']);
        $objQuery->add_where('drt.tablename = mbt.name');
        $objQuery->add_where('mbt.id_module_type = mt.id');
        $objRs = $objQuery->execute();

        // permet de déterminer si on peut choisir 1 table ou plusieurs
        // si aucune table sélectionnée, nécessisté de ne choisir qu'une table comme point de départ du graphe
        $booUniqueChoice = false;
        $arrTable = array();

        if ($objRs->numrows() == 0) // NOUVELLE REQUETE =>> AUCUNE TABLE SELECTIONNEE =>> ON PREND TOUT !
        {
            $booUniqueChoice = true;

            // Recherche de toutes les tables
            $objQuery = new ploopi_query_select();
            $objQuery->add_select('distinct mbt.*, mt.label as moduletype');
            $objQuery->add_from('ploopi_mb_table mbt');
            $objQuery->add_from('ploopi_mb_field mbf');
            $objQuery->add_from('ploopi_module_type mt');
            $objQuery->add_where('mbt.visible = 1');
            $objQuery->add_where('mbt.name = mbf.tablename');
            $objQuery->add_where('mbt.id_module_type = mt.id');
            $objQuery->add_where('mbt.id_module_type IN (%e)', implode(', ', $arrModuleTypes));
            $objQuery->add_orderby('mt.label, mbt.label');
            $objRs = $objQuery->execute();

            $arrTable = array();
            while ($row = $objRs->fetchrow())
            {
                $strTablename = $row['label'] == '' ? $row['name'] : $row['label'];
                $arrTable[$row['name']] = "[{$row['moduletype']}] {$strTablename}";
            }

        }
        else
        {
            while ($row = $objRs->fetchrow())
            {
                $strTablename = $row['label'] == '' ? $row['tablename'] : $row['label'];
                $arrTableDiff[$row['tablename']] = "[{$row['moduletype']}] $strTablename";
            }

            // Recherche des tables en relation avec les tables déjà choisies
            $objQuery = new ploopi_query_select();
            $objQuery->add_select('
                distinct(mbr.tablesrc),
                mbr.tabledest,
                tablesrc.visible as visiblesrc,
                tabledest.visible as visibledest,
                tablesrc.label as labelsrc,
                tabledest.label as labeldest,
                module_type_src.label as module_type_src,
                module_type_dest.label as module_type_dest
            ');
            $objQuery->add_from('
                (ploopi_mod_dbreport_querytable drt,
                 ploopi_mb_relation mbr,
                 ploopi_mb_table as tablesrc,
                 ploopi_mb_table as tabledest)
            ');
            $objQuery->add_leftjoin('ploopi_module_type as module_type_src ON module_type_src.id = tablesrc.id_module_type');
            $objQuery->add_leftjoin('ploopi_module_type as module_type_dest ON module_type_dest.id = tabledest.id_module_type');
            $objQuery->add_where('drt.id_query = %d', $_POST['dbreport_query_id']);
            $objQuery->add_where('(drt.tablename = mbr.tablesrc OR drt.tablename = mbr.tabledest)');
            $objQuery->add_where('tablesrc.name = mbr.tablesrc');
            $objQuery->add_where('tabledest.name = mbr.tabledest');
            $objQuery->add_where('tablesrc.id_module_type IN (%e)', implode(', ', $arrModuleTypes));
            $objQuery->add_where('tabledest.id_module_type IN (%e)', implode(', ', $arrModuleTypes));
            $objRs = $objQuery->execute();

            while ($row = $objRs->fetchrow())
            {
                if ($row['visiblesrc'])
                {
                    $strTablename = ($row['labelsrc'] == '') ? $row['tablesrc'] : $row['labelsrc'];
                    $arrTable[$row['tablesrc']] = "[{$row['module_type_src']}] $strTablename";
                }
                if ($row['visibledest'])
                {
                    $strTablename = ($row['labeldest'] == '') ? $row['tabledest'] : $row['labeldest'];
                    $arrTable[$row['tabledest']] = "[{$row['module_type_dest']}] $strTablename";
                }
            }

            // enlève les doublons
            $arrTable = array_unique($arrTable);

            // propose uniquement les nouvelles tables
            $arrTable = array_diff_assoc($arrTable, $arrTableDiff);

        }

        if (!empty($arrTable))
        {
            // tri
            asort($arrTable);

            $objForm = new form(
                'dbreport_querytable_add_form',
                ploopi_urlencode("admin.php?ploopi_op=dbreport_querytable_save&dbreport_query_id={$_POST['dbreport_query_id']}"),
                'post',
                array(
                    'class' => 'ploopi_generate_form dbreport_checkboxes',
                    'style_form' => 'height:250px;overflow-y:scroll;border-bottom:1px solid #aaa;background:#f8f8f8;padding:4px;'
                )
            );

            $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
            $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

            $objForm->addPanel($objPanel = &new form_panel('dbreport_panel_tables', 'Tables disponibles'));

            if ($booUniqueChoice)
            {
                foreach($arrTable as $strTablename => $strLabel)
                {
                    $objPanel->addField( new form_radio($strLabel, $strTablename, false, 'dbreport_tablenames', $strTablename, array('class_form' => 'onclick')) );
                }
            }
            else
            {
                foreach($arrTable as $strTablename => $strLabel)
                {
                    $objPanel->addField( new form_checkbox($strLabel, $strTablename, false, 'dbreport_tablenames[]', $strTablename, array('class_form' => 'onclick')) );
                }

            }
            echo $objForm->render();
        }
        else echo "<div style=\"padding:4px;\">Vous ne pouvez plus ajouter de table</div>";
    }
}
else echo "<div style=\"padding:4px;\">Requête inconnue</div>";

$strContent = ob_get_contents();
ob_end_clean();

ploopi_die($skin->create_popup('Ajout de tables', $strContent, 'dbreport_querytable_add'));
?>