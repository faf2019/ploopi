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
 * Popup de modification d'un champs dans une requête
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

ploopi_init_module('dbreport');

include_once './include/classes/form.php';
include_once './modules/dbreport/classes/class_dbreport_queryfield.php';

$objDbrQueryField = new dbreport_queryfield();

if (isset($_POST['dbreport_queryfield_id']) && is_numeric($_POST['dbreport_queryfield_id']) && $objDbrQueryField->open($_POST['dbreport_queryfield_id']))
{
    $objForm = new form(
        'dbreport_queryfield_modify_form',
        ploopi_urlencode("admin-light.php?ploopi_op=dbreport_queryfield_save&dbreport_query_id={$_POST['dbreport_query_id']}&dbreport_queryfield_id={$_POST['dbreport_queryfield_id']}")
    );

    $objForm->addField( new form_text('Table/Champ:', "{$objDbrQueryField->fields['tablename']} . {$objDbrQueryField->fields['fieldname']}", null, null, array('style' => 'font-weight:bold;')));
    $objForm->addField( new form_field('input:text', 'Intitulé/Alias:<br /><em>(doit être unique)</em>', $objDbrQueryField->fields['label'], 'dbreport_queryfield_label', 'dbreport_queryfield_label') );
    $objForm->addField( new form_field('input:text', 'Position', $objDbrQueryField->fields['position'], 'dbreport_queryfield_position', 'dbreport_queryfield_position', array('style' => 'width:50px;')));
    //$objForm->addField( new form_field('input:text', 'date:', '', 'xx', 'xx', array('datatype' => 'date')) );

    $objForm->addField( new form_select('Fonctions de chaînes:<br /><em><a target="_new" href="http://dev.mysql.com/doc/refman/5.0/fr/string-functions.html">Documentation</a></em>', array('' => '(Sélectionnez une fonction à ajouter)') + array_combine(dbreport::getFunctions('string'), dbreport::getFunctions('string')), '', null, 'dbreport_queryfield_functionlist', array('onchange' => 'dbreport_functionlist_onchange(this);')) );
    $objForm->addField( new form_select('Fonctions de dates:<br /><em><a target="_new" href="http://dev.mysql.com/doc/refman/5.0/fr/date-and-time-functions.html">Documentation</a></em>', array('' => '(Sélectionnez une fonction à ajouter)') + array_combine(dbreport::getFunctions('date'), dbreport::getFunctions('date')), '', null, 'dbreport_queryfield_functionlist', array('onchange' => 'dbreport_functionlist_onchange(this);')) );
    $objForm->addField( new form_select('Fonctions mathématiques:<br /><em><a target="_new" href="http://dev.mysql.com/doc/refman/5.0/fr/mathematical-functions.html">Documentation</a></em>', array('' => '(Sélectionnez une fonction à ajouter)') + array_combine(dbreport::getFunctions('math'), dbreport::getFunctions('math')), '', null, 'dbreport_queryfield_functionlist', array('onchange' => 'dbreport_functionlist_onchange(this);')) );
    $objForm->addField( new form_field('textarea', 'Fonction appliquée:', $objDbrQueryField->fields['function'], 'dbreport_queryfield_function', 'dbreport_queryfield_function') );

    $objForm->addField( new form_select('Opération:', array('' => '(Aucune opération)') + dbreport::getOperations(), $objDbrQueryField->fields['operation'], 'dbreport_queryfield_operation', 'dbreport_queryfield_operation', array('style' => 'width:200px;', 'onchange' => "if (this.value == 'intervals') { $('dbreport_queryfield_intervals_form').style.display = 'block'; } else { $('dbreport_queryfield_intervals_form').style.display = 'none'; $('dbreport_queryfield_intervals').value = ''; }")) );
    $objForm->addField( new form_field('input:text', 'Intervalles', $objDbrQueryField->fields['intervals'], 'dbreport_queryfield_intervals', 'dbreport_queryfield_intervals', array('style_form' => $objDbrQueryField->fields['operation'] == 'intervals' ? 'display:block;' : 'display:none')) );

    $objForm->addField( new form_select('Tri:', array('' => '(Aucun tri)') + dbreport::getSorts(), $objDbrQueryField->fields['sort'], 'dbreport_queryfield_sort', 'dbreport_queryfield_sort', array('style' => 'width:150px;')) );
    $objForm->addField( new form_select('Afficher:', array(1 => 'Oui', 0 => 'Non'), $objDbrQueryField->fields['visible'], 'dbreport_queryfield_visible', 'dbreport_queryfield_visible', array('style' => 'width:100px;')) );

    $objForm->addField( new form_select('Filtre:', array('' => '(Aucun critère)') + dbreport::getCriterias(), $objDbrQueryField->fields['type_criteria'], 'dbreport_queryfield_type_criteria', 'dbreport_queryfield_type_criteria', array('style' => 'width:150px;', 'onchange' => "if (this.value == '') { $('dbreport_queryfield_criteria_form').style.display = 'none'; $('dbreport_queryfield_criteria').value = ''; } else { $('dbreport_queryfield_criteria_form').style.display = 'block'; }")) );
    $objForm->addField( new form_field('input:text', '<em>Valeur du filtre:<br />(ou nom du paramètre pour le webservice. Ex:@param)</em>', $objDbrQueryField->fields['criteria'], 'dbreport_queryfield_criteria', 'dbreport_queryfield_criteria', array('style_form' => $objDbrQueryField->fields['type_criteria'] == '' ? 'display:none;' : 'display:block')) );

    $objForm->addField( new form_select('Filtre (Ou):', array('' => '(Aucun critère)') + dbreport::getCriterias(), $objDbrQueryField->fields['type_or'], 'dbreport_queryfield_type_or', 'dbreport_queryfield_type_or', array('style' => 'width:150px;', 'onchange' => "if (this.value == '') { $('dbreport_queryfield_or_form').style.display = 'none'; $('dbreport_queryfield_or').value = ''; } else { $('dbreport_queryfield_or_form').style.display = 'block'; }")) );
    $objForm->addField( new form_field('input:text', '<em>Valeur du filtre:<br />(ou nom du paramètre pour le webservice. Ex:@param)</em>', $objDbrQueryField->fields['or'], 'dbreport_queryfield_or', 'dbreport_queryfield_or', array('style_form' => $objDbrQueryField->fields['type_or'] == '' ? 'display:none;' : 'display:block')) );

    //$objForm->addField( new form_select('Série continue:', array(1 => 'Oui', 0 => 'Non'), $objDbrQueryField->fields['series'], 'dbreport_queryfield_series', 'dbreport_queryfield_series') );

    $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
    $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

    ploopi_die($skin->create_popup('Modification d\'un champ de la requête', $objForm->render(), 'dbreport_queryfield_modify'));
}

ploopi_die();

?>
