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
 * @package directory
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */

ploopi\module::init('directory', false, false, false);

if ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public')
{
    if (!empty($_GET['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = $_GET['directoryTabItem'];
    if (empty($_SESSION['directory']['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = 'tabFavorites';
}
else $_SESSION['directory']['directoryTabItem'] = '';

/**
 * Menu 'Mon Espace'
 */

if ($_SESSION["ploopi"]["workspaceid"] > 0)  // group selected
{
    if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_mygroup'])
    //if (ploopi\acl::isactionallowed(_DIRECTORY_ACTION_MYGROUP,-1,$menu_moduleid))
    {
        $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mygroup']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabMygroup"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabMygroup');
    }
}

/**
 * Menu 'Organigramme'
 */

/*
if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_organizationchart'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_organizationchart'])) ? _DIRECTORY_ORGANIZATIONCHART : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_organizationchart']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabOrganizationChart"));
}
*/

/**
 * Menu 'Contacts partagés'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_sharedcontacts'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_sharedcontacts'])) ? _DIRECTORY_SHAREDCONTACTS : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_sharedcontacts']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabSharedContacts"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabSharedContacts');
}

/**
 * Menu 'Numéros abrégés'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_speeddialing'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_speeddialing'])) ? _DIRECTORY_SPEEDDIALING : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_speeddialing']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabSpeedDialing"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabSpeedDialing');
}

/**
 * Menu 'Mes Contacts'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_mycontacts'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_mycontacts']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabMycontacts"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabMycontacts');
}

/**
 * Menu 'Mes Favoris'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_myfavorites'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_myfavorites'])) ? _DIRECTORY_FAVORITES : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_myfavorites']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabFavorites"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabFavorites');
}

/**
 * Menu 'Recherche'
 */

if ($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_search'])
{
    $block->addmenu((empty($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_search'])) ? _DIRECTORY_SEARCH : ploopi\str::htmlentities($_SESSION['ploopi']['modules'][$menu_moduleid]['directory_label_search']),ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&directoryTabItem=tabSearch"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $_SESSION['directory']['directoryTabItem'] == 'tabSearch');
}
?>
