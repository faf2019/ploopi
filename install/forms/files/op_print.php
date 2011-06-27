<?php
/*
    Copyright (c) 2007-2011 Ovensia
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
 * Export des données d'un formulaire aux formats XLS et CSV
 *
 * @package forms
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see ploopi_ob_clean
 * @link http://pear.php.net/package/Spreadsheet_Excel_Writer
 */

/**
 * On supprime tous les buffers autres que le buffer principal et on vide le buffer principal
 */

ploopi_ob_clean();

include_once './modules/forms/classes/formsForm.php';

$objForm = new formsForm();

if (empty($_GET['forms_export_format']) || empty($_GET['forms_id']) || !is_numeric($_GET['forms_id']) || !$objForm->open($_GET['forms_id'])) ploopi_die();

$objForm->export($_GET['forms_export_format'], false);

ploopi_die();
?>
