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
 * Fonctions, constantes, variables globales
 *
 * @package system
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Objet : Espace de travail
 */
define ('_SYSTEM_OBJECT_WORKSPACE',     1);

/**
 * Objet : Groupe d'utilisateurs
 */
define ('_SYSTEM_OBJECT_GROUP',     2);

define ('_SYSTEM_ICON_SYSTEM_INSTALLMODULES', 'system_installmodules');
define ('_SYSTEM_ICON_SYSTEM_PARAMS', 'system_params');
define ('_SYSTEM_ICON_SYSTEM_USERS', 'system_users');
define ('_SYSTEM_ICON_SYSTEM_TOOLS', 'system_tools');
define ('_SYSTEM_ICON_SYSTEM_LOGS', 'system_logs');
define ('_SYSTEM_ICON_SYSTEM_INSTALLSKINS', 'system_installskins');

define ('_SYSTEM_ICON_GROUP', 'group');
define ('_SYSTEM_ICON_MODULES', 'modules');
define ('_SYSTEM_ICON_PARAMS', 'params');
define ('_SYSTEM_ICON_ROLES', 'roles');
define ('_SYSTEM_ICON_USERS', 'users');
define ('_SYSTEM_ICON_HOMEPAGE', 'homepage');


define ('_SYSTEM_TAB_GROUPLIST', 'grouplist');
define ('_SYSTEM_TAB_USERLIST', 'userlist');
define ('_SYSTEM_TAB_USERADD', 'useradd');
define ('_SYSTEM_TAB_USERATTACH', 'userattach');
define ('_SYSTEM_TAB_GROUPATTACH', 'groupattach');
define ('_SYSTEM_TAB_USERMOVE', 'usermove');
define ('_SYSTEM_TAB_RULELIST', 'rulelist');
define ('_SYSTEM_TAB_RULEADD', 'ruleadd');
define ('_SYSTEM_TAB_USERIMPORT', 'userimport');

define ('_SYSTEM_TAB_ROLEMANAGEMENT', 'rolemanagement');
define ('_SYSTEM_TAB_ROLEASSIGNMENT', 'roleassignment');
define ('_SYSTEM_TAB_MULTIPLEROLEASSIGNMENT', 'multipleroleassignment');

define ('_SYSTEM_TAB_MESSAGEINBOX', 'messageinbox');
define ('_SYSTEM_TAB_MESSAGEOUTBOX', 'messageoutbox');

define ('_SYSTEM_ACTION_INSTALLMODULE',     1);
define ('_SYSTEM_ACTION_UNINSTALLMODULE',   2);
define ('_SYSTEM_ACTION_UPDATEMODULE',      1);
define ('_SYSTEM_ACTION_USEMODULE',         4);
define ('_SYSTEM_ACTION_CONFIGUREMODULE',   5);
define ('_SYSTEM_ACTION_MODIFYHOMEPAGE',    6);
define ('_SYSTEM_ACTION_INSTALLSKIN',       7);
define ('_SYSTEM_ACTION_UNINSTALLSKIN',     8);
define ('_SYSTEM_ACTION_CREATEGROUP',       9);
define ('_SYSTEM_ACTION_MODIFYGROUP',       10);
define ('_SYSTEM_ACTION_DELETEGROUP',       11);
define ('_SYSTEM_ACTION_CLONEGROUP',        12);
define ('_SYSTEM_ACTION_CREATEROLE',        13);
define ('_SYSTEM_ACTION_MODIFYROLE',        14);
define ('_SYSTEM_ACTION_DELETEROLE',        15);
define ('_SYSTEM_ACTION_CREATEPROFIL',      16);
define ('_SYSTEM_ACTION_MODIFYPROFIL',      17);
define ('_SYSTEM_ACTION_DELETEPROFIL',      18);
define ('_SYSTEM_ACTION_CREATEUSER',        19);
define ('_SYSTEM_ACTION_MODIFYUSER',        20);
define ('_SYSTEM_ACTION_DELETEUSER',        21);
define ('_SYSTEM_ACTION_UNLINKMODULE',      22);
define ('_SYSTEM_ACTION_DELETEMODULE',      23);
define ('_SYSTEM_ACTION_UPDATEMETABASE',    24);
define ('_SYSTEM_ACTION_MOVEUSER',          27);
define ('_SYSTEM_ACTION_ATTACHUSER',        28);
define ('_SYSTEM_ACTION_DETACHUSER',        29);
define ('_SYSTEM_ACTION_ATTACHGROUP',       30);
define ('_SYSTEM_ACTION_DETACHGROUP',       31);
define ('_SYSTEM_ACTION_PARAMMODULE',       32);
define ('_SYSTEM_ACTION_CREATEWORKSPACE',   39);
define ('_SYSTEM_ACTION_MODIFYWORKSPACE',   40);
define ('_SYSTEM_ACTION_DELETEWORKSPACE',   41);
define ('_SYSTEM_ACTION_CLONEWORKSPACE',    42);

include_once "./modules/system/include/functions.php";
?>
