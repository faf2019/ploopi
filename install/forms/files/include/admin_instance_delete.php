<?php
/*
    Copyright (c) 2011 Ovensia
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
 * Suppression des données liées à l'instance du module
 *
 * @package forms
 * @subpackage instance_delete
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ovensia\ploopi\module::init('forms', false, false, false);

include_once './modules/forms/classes/formsForm.php';

$objDOC = new ovensia\ploopi\data_object_collection('formsForm');
$objDOC->add_where('id_module = %d', $admin_moduleid);
foreach($objDOC->get_objects() as $objForm) $objForm->delete();
?>
