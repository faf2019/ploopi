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
 * @author St�phane Escaich
 */

global $db;
global $skin;
global $template_body;
global $ploopi_timer;
global $ploopi_viewmodes;
global $ploopi_system_levels;
global $ploopi_days;
global $ploopi_months;
global $ploopi_errormsg;
global $ploopi_civility;

$skin = null;
if (!empty($_SESSION['ploopi']['frontoffice']['template_path']) && file_exists("{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php"))
{
    include_once "{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php";
    $skin = new ovensia\ploopi\skin();
}

/**
 * Initialisation du module WebEdit
 */

ovensia\ploopi\module::init('webedit', false, false, false);

include './modules/webedit/display.php';
