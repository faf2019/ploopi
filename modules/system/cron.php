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
 * Gestion de la suppression automatique des logs
 *
 * @package system
 * @subpackage cron
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */


// semaine dernière
$strTsDelete = ploopi\date::timestamp_add(ploopi\date::createtimestamp(), 0, 0, 0, 0, -7, 0);

// suppression des données périmées
ploopi\db::get()->query("DELETE FROM `ploopi_log` WHERE ts < {$strTsDelete}");

// optimisation des tables
ploopi\db::get()->query("OPTIMIZE TABLE `ploopi_log`");
