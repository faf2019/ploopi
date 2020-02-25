<?php
/*
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
 * Suppression des données liées à l'instance du module
 *
 * @package news
 * @subpackage instance_delete
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ploopi\module::init('news2', false, false, false);

global $admin_redirect;

$db->query("DELETE FROM ploopi_mod_news2_entry WHERE id_module= $admin_moduleid");
$db->query("DELETE FROM ploopi_mod_news2_cat WHERE id_module= $admin_moduleid");

echo 'Suppression des News<br>Suppression des Catégories<br>Suppression des Paramètres';

$admin_redirect = false;

