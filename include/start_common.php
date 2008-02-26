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

ob_start();

///////////////////////////////////////////////////////////////////////////
// START TIMER
///////////////////////////////////////////////////////////////////////////

include_once './include/classes/class_timer.php' ;
$ploopi_timer = new timer();
$ploopi_timer->start();

///////////////////////////////////////////////////////////////////////////
// LOAD PLOOPI CONFIG
///////////////////////////////////////////////////////////////////////////

if (!file_exists('./config/config.php'))
{
    header("Location: ./config/install.php");
    ploopi_die();
}
include_once './config/config.php'; // load config (mysql, path, etc.)

///////////////////////////////////////////////////////////////////////////
// INITIALIZE ERROR HANDLER
///////////////////////////////////////////////////////////////////////////

include_once './include/errors.php';

///////////////////////////////////////////////////////////////////////////
// LOAD GLOBALS, VARS & FUNCTIONS
///////////////////////////////////////////////////////////////////////////
include_once './include/global_constants.php';
include_once './include/functions/session.php';
include_once './include/functions/string.php';
include_once './include/functions/system.php';

///////////////////////////////////////////////////////////////////////////
// VARIABLE FILTER
///////////////////////////////////////////////////////////////////////////
include_once './include/import_gpr.php';

// set default header
include_once './include/header.php';

///////////////////////////////////////////////////////////////////////////
// DB CONNECT
///////////////////////////////////////////////////////////////////////////

if (file_exists('./db/class_db_'._PLOOPI_SQL_LAYER.'.php')) include_once './db/class_db_'._PLOOPI_SQL_LAYER.'.php';

global $db;

$db = new ploopi_db(_PLOOPI_DB_SERVER, _PLOOPI_DB_LOGIN, _PLOOPI_DB_PASSWORD, _PLOOPI_DB_DATABASE);
if(!$db->connection_id) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);

///////////////////////////////////////////////////////////////////////////
// INITIALIZE SESSION HANDLER
///////////////////////////////////////////////////////////////////////////

if (defined('_PLOOPI_USE_DBSESSION') && _PLOOPI_USE_DBSESSION)
{
    include_once './include/classes/class_session.php' ;

    ini_set('session.save_handler', 'user');
    $session = new ploopi_session();
    session_set_save_handler(   array($session, 'open'),
                                array($session, 'close'),
                                array($session, 'read'),
                                array($session, 'write'),
                                array($session, 'destroy'),
                                array($session, 'gc')
                            );
}


session_start();

$ploopi_initsession = false;

if (empty($_SESSION) || ($_SESSION['ploopi']['host'] != $_SERVER['HTTP_HOST']))  { ploopi_session_reset(); $ploopi_initsession = true; }

ploopi_session_update();

///////////////////////////////////////////////////////////////////////////
// LOGOUT
///////////////////////////////////////////////////////////////////////////
if (isset($_REQUEST['ploopi_logout']))
{
    session_destroy();
    session_write_close();
    $db->close();

    header("location: {$scriptenv}");
}

///////////////////////////////////////////////////////////////////////////
// INCLUDES MAIN CLASSES
///////////////////////////////////////////////////////////////////////////

include_once './include/classes/class_data_object.php';

?>