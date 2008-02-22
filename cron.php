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

// add the cron.php script into your cron table
// ie:
// * * * * * /usr/local/bin/php -f /var/www/ploopi/cron.php > /dev/null 2>&1
// * * * * * wget -q -O /dev/null http://localhost/.../cron.php 2>&1
// * * * * * lynx -dump http://localhost/.../cron.php > /dev/null 2>&1

include_once './include/start_light.php';

/*
ob_start();
session_start();

chdir (dirname($_SERVER['SCRIPT_FILENAME']));

if (!file_exists('./config/config.php'))
{
    include_once './config/install.php';
    ploopi_die();
}
include_once './config/config.php'; // load config (mysql, path, etc.)
include_once './include/errors.php';

include_once './include/classes/class_timer.php' ;

// execution timer
$ploopi_timer = new timer();
$ploopi_timer->start();

// set default header
include_once './include/header.php';

// load PLOOPI global classes
include_once './include/classes/class_data_object.php';
include_once './include/classes/class_user_action_log.php' ;
include_once './include/classes/class_param.php';
include_once './include/classes/class_connecteduser.php';

// initialize PLOOPI
include_once './include/global.php';        // load ploopi global functions & constants


if (file_exists('./db/class_db_'._PLOOPI_SQL_LAYER.'.php')) include_once './db/class_db_'._PLOOPI_SQL_LAYER.'.php';


$db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
if(!$db->connection_id) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);

*/

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

$time = round($ploopi_timer->getexectime(),3);
$time = sprintf("%d",$time*1000);

$sql_time = round($db->exectime_queries,3);
$sql_time = sprintf("%d",$sql_time*1000);

$sql_p100 = round(($sql_time*100)/$time,0);
$php_p100 = 100 - $sql_p100;

if ($ploopi_errors_level && _PLOOPI_MAIL_ERRORS && _PLOOPI_ADMINMAIL != '') echo mail(_PLOOPI_ADMINMAIL,"[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$_SERVER['HTTP_HOST']}]", "$ploopi_errors_nb erreur(s) sur $ploopi_errors_msg\n\nDUMP:\n$ploopi_errors_vars");
if (defined('_PLOOPI_ACTIVELOG') && _PLOOPI_ACTIVELOG)  include './modules/system/hit.php';
?>
