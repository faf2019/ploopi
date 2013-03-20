<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Initialisation du rendu frontoffice.
 * Chargement du module WebEdit.
 *
 * @package ploopi
 * @subpackage frontoffice
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$skin = null;
if (!empty($_SESSION['ploopi']['frontoffice']['template_path']) && file_exists("{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php"))
{
    include_once "{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php";
    $skin = new skin();
}

/**
 * Initialisation du module WebEdit
 */

ploopi_init_module('webedit', false, false, false);

include './modules/webedit/display.php';
