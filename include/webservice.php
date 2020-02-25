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
 * Point d'entrée pour les webservices
 *
 * @package ploopi
 * @subpackage webservice
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

if (empty($_REQUEST['module'])) ploopi\system::kill();

$strControllerFile = "ploopi\\{$_REQUEST['module']}\\controller";
if (ploopi\loader::classExists($strControllerFile)) {
    $strControllerFile::get()->dispatch();
}
else {
    // Rétrocompatibilité

    $webservice_path = "./modules/{$_REQUEST['module']}";
    $webservice_rootfile = "{$webservice_path}/webservice.php";

    if (is_dir($webservice_path) && file_exists($webservice_rootfile)) include $webservice_rootfile;
}
