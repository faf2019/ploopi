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
 * Suppression des données liées à l'utilisateur supprimé
 *
 * @package directory
 * @subpackage user_delete
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Intialisation du module
 */

ploopi_init_module('directory', false, false, false);

global $admin_redirect;

/**
 * Suppression des contacts de l'utilisateur
 */

$db->query("DELETE FROM ploopi_mod_directory_contact WHERE id_user = {$admin_userid} AND id_workspace = {$admin_workspaceid} AND id_module = {$admin_moduleid}");

echo _DIRECTORY_LABEL_DELETE_USER;

$admin_redirect = false;
?>
