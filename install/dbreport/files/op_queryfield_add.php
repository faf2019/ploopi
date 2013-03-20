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
 * Popup d'ajout de champs dans une requête
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

ploopi_init_module('dbreport', false, false, false);
global $arrDbReportTypes;

ob_start();

$objDbrQuery = new dbreport_query();

if (isset($_POST['dbreport_query_id']) && is_numeric($_POST['dbreport_query_id']) && $objDbrQuery->open($_POST['dbreport_query_id']))
{
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('drt.*, mbt.label, mt.label as mt_label');
    $objQuery->add_from('ploopi_mod_dbreport_querytable drt');
    $objQuery->add_from('ploopi_mb_table mbt');
    $objQuery->add_from('ploopi_module_type mt');
    $objQuery->add_where('drt.id_query = %d', $_POST['dbreport_query_id']);
    $objQuery->add_where('drt.tablename = mbt.name');
    $objQuery->add_where('mbt.id_module_type = mt.id');
    $objQuery->add_orderby('mt.label');
    $objRs = $objQuery->execute();

    if ($objRs->numrows())
    {
        $objForm = new form(
            'dbreport_queryfield_add_form',
            ploopi_urlencode("admin.php?ploopi_op=dbreport_queryfield_save&dbreport_query_id={$_POST['dbreport_query_id']}"),
            'post',
            array(
                'class' => 'ploopi_generate_form dbreport_checkboxes',
                'style_form' => 'height:250px;overflow-y:scroll;border-bottom:1px solid #aaa;background:#f8f8f8;padding:4px;'
            )
        );

        $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
        $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

        while($row = $objRs->fetchrow())
        {
            $objForm->addPanel($objPanel = new form_panel("dbreport_panel_{$row['mt_label']}_{$row['label']}", "Table &laquo; [{$row['mt_label']}] {$row['label']} &raquo;"));

            $objQuery = new ploopi_query_select();
            $objQuery->add_from('ploopi_mb_field');
            $objQuery->add_where('tablename = %s', $row['tablename']);
            $objQuery->add_where('visible = 1');
            $objQuery->add_orderby('name');
            $objRs2 = $objQuery->execute();

            while($row2 = $objRs2->fetchrow())
            {
                $objPanel->addField( new form_checkbox("<strong>".htmlentities($row2['label'])."</strong>".($row2['label'] != $row2['name'] ? "<span>&nbsp;(".htmlentities($row2['name']).")</span>" : '')."<em>".$arrDbReportTypes[dbreport_getbasictype($row2['type'])]."</em>", "{$row2['tablename']}.{$row2['name']}", false, 'dbreport_fieldnames[]', "{$row2['tablename']}.{$row2['name']}", array('class_form' => 'onclick')) );
            }
        }

        $strContent = $objForm->render();

    }
    else $strContent = "<div style=\"padding:4px;\">Vous devez d'abord sélectionner une ou plusieurs tables</div>";
}

ploopi_die($skin->create_popup('Ajout de champs', $strContent, 'dbreport_queryfield_add'));
?>
