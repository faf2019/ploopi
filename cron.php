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
 * Point d'entr�e les appels via CRON
 *
 * @package ploopi
 * @subpackage cron
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * 
 * <code>
 * * * * * * /usr/local/bin/php -f /var/www/ploopi/cron.php > /dev/null 2>&1
 * * * * * * wget -q -O /dev/null http://localhost/.../cron.php 2>&1
 * * * * * * lynx -dump http://localhost/.../cron.php > /dev/null 2>&1
 * </code>
 */

/**
 * Chargement de l'environnement
 */

include_once './include/start.php';

$cron_rs = $db->query(  "
                        SELECT      ploopi_module.id,
                                    ploopi_module_type.label
                        FROM        ploopi_module
                        INNER JOIN  ploopi_module_type
                        ON          ploopi_module.id_module_type = ploopi_module_type.id
                        ");

while ($cron_fields = $db->fetchrow($cron_rs))
{
    $cronfile = "./modules/{$cron_fields['label']}/cron.php";
    $cron_moduleid = $cron_fields['id'];
    if (file_exists($cronfile)) include $cronfile;
}

ploopi_die();
?>
