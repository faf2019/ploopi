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
 * Partie publique du module
 *
 * @package directory
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ploopi_init_module('directory');

/**
 * Inclusion des classes du module
 */

include_once './modules/directory/class_directory_contact.php';
include_once './modules/directory/class_directory_favorites.php';
include_once './modules/directory/class_directory_list.php';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

switch($op)
{
    case 'directory_view':
        if ((!empty($_GET['directory_id_contact']) && is_numeric($_GET['directory_id_contact'])) || (!empty($_GET['directory_id_user']) && is_numeric($_GET['directory_id_user'])))
        {
            ploopi_init_module('directory');
            include './modules/directory/public_directory_view.php';
        }
        return;
    break;
}

if (!empty($_GET['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = $_GET['directoryTabItem'];
if (empty($_SESSION['directory']['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = 'favorites';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mygroup'])
{
    $tabs['tabMygroup'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'],
            'url'   => "admin.php?directoryTabItem=tabMygroup"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabMygroup') $_SESSION['directory']['directoryTabItem'] = '';
/*
if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_organizationchart'])
{
    $tabs['tabOrganizationChart'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_organizationchart'])) ? _DIRECTORY_ORGANIZATIONCHART : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_organizationchart'],
            'url'   => "admin.php?directoryTabItem=tabOrganizationChart"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabOrganizationChart') $_SESSION['directory']['directoryTabItem'] = '';
*/

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_sharedcontacts'])
{
    $tabs['tabSharedContacts'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'])) ? _DIRECTORY_SHAREDCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'],
            'url'   => "admin.php?directoryTabItem=tabSharedContacts"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabSharedContacts') $_SESSION['directory']['directoryTabItem'] = '';

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mycontacts'])
{
    $tabs['tabMycontacts'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'],
            'url'   => "admin.php?directoryTabItem=tabMycontacts"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabMycontacts') $_SESSION['directory']['directoryTabItem'] = '';


if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
{
    $tabs['tabFavorites'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'])) ? _DIRECTORY_FAVORITES : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'],
            'url'   => "admin.php?directoryTabItem=tabFavorites"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabFavorites') $_SESSION['directory']['directoryTabItem'] = '';


if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_search'])
{
    $tabs['tabSearch'] =
        array(
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'])) ? _DIRECTORY_SEARCH : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'],
            'url'   => "admin.php?directoryTabItem=tabSearch"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabSearch') $_SESSION['directory']['directoryTabItem'] = '';

echo $skin->create_tabs($tabs,$_SESSION['directory']['directoryTabItem']);

$desc = $title = '';
switch($_SESSION['directory']['directoryTabItem'])
{
    case 'tabFavorites':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'])) ? _DIRECTORY_FAVORITES : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'];
        $desc = _DIRECTORY_FAVORITES_DESC;
    break;

    case 'tabMycontacts':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'];
        $desc = _DIRECTORY_MYCONTACTS_DESC;
    break;

    case 'tabMygroup':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'];
        $desc = _DIRECTORY_MYGROUP_DESC;
    break;

    case 'tabSearch':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'])) ? _DIRECTORY_SEARCH : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'];
        $desc = _DIRECTORY_SEARCH_DESC;
    break;

    case 'tabOrganizationChart':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_organizationchart'])) ? _DIRECTORY_ORGANIZATIONCHART : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_organizationchart'];
        $desc = _DIRECTORY_ORGANIZATIONCHART_DESC;
    break;

    case 'tabSharedContacts':
        $title = (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'])) ? _DIRECTORY_SHAREDCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_sharedcontacts'];
        $desc = _DIRECTORY_SHAREDCONTACTS_DESC;
    break;


}

include './modules/directory/public_directory.php';
?>
