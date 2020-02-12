<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * "Constructeur" de l'instance.
 * Initialise les données du module lors de l'instanciation du module.
 *
 * @package webedit
 * @subpackage create
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Inclusion de la classe heading
 */

include_once './modules/webedit/class_heading.php';

/**
 * On crée la rubrique racine
 */
$webedit_heading = new webedit_heading();
$webedit_heading->fields['label'] = 'Racine';
$webedit_heading->fields['id_heading'] = 0;
$webedit_heading->fields['depth'] = 1;
$webedit_heading->fields['parents'] = 0;
$webedit_heading->fields['id_module'] = $this->fields['id'];
$webedit_heading->fields['id_workspace'] = 0;
$webedit_heading->fields['id_user'] = 0;
$webedit_heading->fields['position'] = 1;
$webedit_heading->fields['visible'] = 1;
$webedit_heading->save();
?>
