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

define ('_PLOOPI_VERSION', '1.0RC3b');
define ('_PLOOPI_REVISION', '80428');

define ('_PLOOPI_MSG_DBERROR',          'Database connection error, please contact administrator');

define ('_SYSTEM_SYSTEMADMIN',  0);
define ('_SYSTEM_WORKSPACES',   'work');
define ('_SYSTEM_GROUPS',       'org');

define ('_SYSTEM_OBJECT_ANNOTATION',    1);

define ('_PLOOPI_ERROR_NOWORKSPACEDEFINED',     1);
define ('_PLOOPI_ERROR_LOGINERROR',             2);
define ('_PLOOPI_ERROR_LOGINEXPIRE',            3);
define ('_PLOOPI_ERROR_SESSIONEXPIRE',          4);
define ('_PLOOPI_ERROR_SESSIONINVALID',         5);

define ('_PLOOPI_CACHE_DEFAULT_LIFETIME',   '60');

define ('_PLOOPI_SYSTEMGROUP',      '1'); // virtual system group
define ('_PLOOPI_MODULE_SYSTEM',    '1');
define ('_PLOOPI_NOWORKSPACE',      '-1');

define ('_PLOOPI_MENU_WORKSPACES',  1);
define ('_PLOOPI_MENU_MYWORKSPACE', 2);
define ('_PLOOPI_MENU_ABOUT',       3);
define ('_PLOOPI_MENU_ANNOTATIONS', 4);
define ('_PLOOPI_MENU_TICKETS',     5);
define ('_PLOOPI_MENU_SEARCH',      6);

define ('_PLOOPI_DATE_YEAR',    1);
define ('_PLOOPI_DATE_MONTH',   2);
define ('_PLOOPI_DATE_DAY',     3);
define ('_PLOOPI_DATE_HOUR',    4);
define ('_PLOOPI_DATE_MINUTE',  5);
define ('_PLOOPI_DATE_SECOND',  6);

// DO NOT MODIFY !
define ('_PLOOPI_DATEFORMAT_FR',    'd/m/Y');
define ('_PLOOPI_DATEFORMAT_US',    'Y-m-d');

define ('_PLOOPI_DATEFORMAT_EREG_FR',       '([0-9]{1,2})[-,/,.]([0-9]{1,2})[-,/,.]([0-9]{4})');
define ('_PLOOPI_DATEFORMAT_EREG_US',       '([0-9]{4})[-,/,.]([0-9]{1,2})[-,/,.]([0-9]{1,2})');

define ('_PLOOPI_TIMEFORMAT',           'H:i:s');
define ('_PLOOPI_TIMEFORMAT_EREG',      '([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})');
define ('_PLOOPI_TIMEFORMATDISP',           'H:i');
define ('_PLOOPI_TIMEFORMATDISP_EREG',      '([0-9]{1,2})[:,h]([0-9]{1,2})');
define ('_PLOOPI_DATETIMEFORMAT_MYSQL',         'Y-m-d H:i:s');
define ('_PLOOPI_DATETIMEFORMAT_MYSQL_EREG',    '([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})');
define ('_PLOOPI_TIMESTAMPFORMAT_MYSQL',        'YmdHis');
define ('_PLOOPI_TIMESTAMPFORMAT_MYSQL_EREG',   '([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})');

// NOW YOU CAN MODIFY !
define ('_PLOOPI_DATEFORMAT',           _PLOOPI_DATEFORMAT_FR);

// PREDEFINED ACTIONS
define ('_PLOOPI_ACTION_ADMIN',         0);
define ('_SYSTEM_ACTION_LOGIN_OK',      25);
define ('_SYSTEM_ACTION_LOGIN_ERR',     26);

// MODULE VIEW MODE
define ('_PLOOPI_VIEWMODE_UNDEFINED',   0);
define ('_PLOOPI_VIEWMODE_PRIVATE',     1);
define ('_PLOOPI_VIEWMODE_DESC',        2);
define ('_PLOOPI_VIEWMODE_ASC',         3);
define ('_PLOOPI_VIEWMODE_GLOBAL',      4);

// USER LEVEL
define ('_PLOOPI_ID_LEVEL_VISITOR',          0);
define ('_PLOOPI_ID_LEVEL_USER',            10);
define ('_PLOOPI_ID_LEVEL_GROUPMANAGER',    15);
define ('_PLOOPI_ID_LEVEL_GROUPADMIN',      20);
define ('_PLOOPI_ID_LEVEL_SYSTEMADMIN',     99);

define('_PLOOPI_INDEXATION_METAWEIGHT',     999999);

global $scriptenv;
$scriptenv = basename($_SERVER['SCRIPT_FILENAME']);

if (!defined ('_PLOOPI_BASEPATH')) define ('_PLOOPI_BASEPATH', ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://').((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') ? ":{$_SERVER['SERVER_PORT']}" : '').((!empty($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] != '') ? dirname($_SERVER['SCRIPT_NAME']) : '/')); 
if (!defined ('_PLOOPI_USE_OUTPUT_COMPRESSION')) define ('_PLOOPI_USE_OUTPUT_COMPRESSION', false); 

define ('_PLOOPI_SERVER_OSTYPE', (substr(PHP_OS, 0, 3) == 'WIN') ? 'windows' : 'unix');

switch(_PLOOPI_SERVER_OSTYPE)
{
    case 'unix':
        define ('_PLOOPI_SEP', '/');
    break;

    case 'windows':
        define ('_PLOOPI_SEP', '\\');
    break;
}

if (defined('_PLOOPI_PEARPATH') && (strstr(ini_get('include_path'), _PLOOPI_PEARPATH) == false) && file_exists(_PLOOPI_PEARPATH)) ini_set('include_path', ini_get('include_path').':'._PLOOPI_PEARPATH);

?>
