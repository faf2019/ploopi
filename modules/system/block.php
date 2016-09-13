<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Affichage du bloc de menu
 *
 * @package system
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */ 

ovensia\ploopi\module::init('system', false, false, false);

if (!empty($_REQUEST['system_level'])) $_SESSION['system']['level'] = $_REQUEST['system_level'];
if (empty($_SESSION['system']['level'])) $_SESSION['system']['level'] = _SYSTEM_WORKSPACES;

if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
    $block->addmenu(
        _PLOOPI_ADMIN_SYSTEM, 
        ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level=system"), 
        ($_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM && $_SESSION['system']['level'] == 'system')
    );

$block->addmenu(
    _PLOOPI_ADMIN_WORKSPACES, 
    ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level="._SYSTEM_WORKSPACES), 
    ($_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM && $_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
);
?>
