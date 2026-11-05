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

// suppression des données périmées (index sur ts) ; pas d'OPTIMIZE : sous InnoDB,
// il reconstruirait toute la table à chaque passage et l'espace libéré est réutilisé
ploopi\db::get()->query("DELETE FROM `ploopi_log` WHERE ts < {$strTsDelete}");

// journal des appels de l'API : 30 jours (les compteurs restent sur la clé)
// (table absente tant que la base n'est pas passée en 2.1.0)
$arrSystemVersion = ploopi\db::get()->fetchrow(ploopi\db::get()->query('SELECT version FROM ploopi_module_type WHERE id = 1'));
if ($arrSystemVersion && version_compare($arrSystemVersion['version'], '2.1.0', '>=')) ploopi\db::get()->query('DELETE FROM `ploopi_api_request` WHERE created_at < '.(time() - 30 * 86400));
