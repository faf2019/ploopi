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
 * Affichage du bloc de menu
 *
 * @package system
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */ 

ploopi\module::init('system', false, false, false);

if (!empty($_REQUEST['system_level'])) $_SESSION['system']['level'] = $_REQUEST['system_level'];
if (empty($_SESSION['system']['level'])) $_SESSION['system']['level'] = _SYSTEM_WORKSPACES;

/**
 * Gabarits de génération 4 (ploopi4) : une entrée par écran, avec icône et section.
 * Les autres gabarits gardent les deux entrées historiques.
 */
if (ploopi\skin::get()->version() >= 4)
{
    $booSystemCurrent = $_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM;

    $block->addmenu(
        _SYSTEM_MENU_WORKSPACES,
        ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level="._SYSTEM_WORKSPACES),
        $booSystemCurrent && in_array($_SESSION['system']['level'], array(_SYSTEM_WORKSPACES, _SYSTEM_GROUPS)),
        '',
        'sitemap',
        _SYSTEM_MENU_ORGANIZATION
    );

    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
    {
        // Écran courant du niveau « système » (lu avant admin_system.php, qui le mémorise)
        $strSysItem = isset($_GET['sysToolbarItem']) ? $_GET['sysToolbarItem'] : (isset($_SESSION['system']['sysToolbarItem']) ? $_SESSION['system']['sysToolbarItem'] : '');

        $arrSysItems = array();

        // Mise à jour proposée quand la base est en retard sur les fichiers
        ploopi\db::get()->query('SELECT version FROM ploopi_module_type WHERE id = 1');
        $row = ploopi\db::get()->fetchrow();
        if ($row && version_compare(_PLOOPI_VERSION, $row['version']) > 0) $arrSysItems['systemupdate'] = array(_SYSTEM_MENU_SYSTEMUPDATE, 'refresh');

        $arrSysItems['install'] = array(_SYSTEM_MENU_INSTALLMODULES, 'package');
        $arrSysItems['params'] = array(_SYSTEM_MENU_PARAMS, 'adjustments-horizontal');
        $arrSysItems['directory'] = array(_SYSTEM_MENU_DIRECTORY, 'address-book');
        $arrSysItems['tools'] = array(_SYSTEM_MENU_TOOLS, 'tool');

        // Sans choix mémorisé, admin_system.php affiche le premier écran
        if (!isset($arrSysItems[$strSysItem])) $strSysItem = key($arrSysItems);

        foreach($arrSysItems as $strItem => $arrItem)
        {
            $block->addmenu(
                $arrItem[0],
                ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level=system&sysToolbarItem={$strItem}"),
                $booSystemCurrent && $_SESSION['system']['level'] == 'system' && $strSysItem == $strItem,
                '',
                $arrItem[1],
                _SYSTEM_MENU_SYSTEM
            );
        }
    }

    return;
}

if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
    $block->addmenu(
        _PLOOPI_ADMIN_SYSTEM, 
        ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level=system"), 
        ($_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM && $_SESSION['system']['level'] == 'system')
    );

$block->addmenu(
    _PLOOPI_ADMIN_WORKSPACES, 
    ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level="._SYSTEM_WORKSPACES), 
    ($_SESSION['ploopi']['moduleid'] == _PLOOPI_MODULE_SYSTEM && $_SESSION['system']['level'] == _SYSTEM_WORKSPACES)
);
?>
