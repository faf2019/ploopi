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
    case 'directory_save':
        $directory_contact = new directory_contact();
        if (!empty($_POST['contact_id']) && is_numeric($_POST['contact_id'])) $directory_contact->open($_POST['contact_id']);
        $directory_contact->setvalues($_POST,'directory_contact_');
        $directory_contact->setuwm();
        $directory_contact->save();
        ploopi_redirect('admin.php');
    break;

    case 'directory_delete':
        if (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
        {
            $directory_contact = new directory_contact();
            if ($directory_contact->open($_GET['contact_id'])) $directory_contact->delete();
        }
        ploopi_redirect('admin.php');
    break;

    case 'directory_favorites_add':
        if (!empty($_POST['directory_favorites_id_list']) && is_array($_POST['directory_favorites_id_list']))
        {
            if (!empty($_POST['directory_favorites_id_user']) && is_numeric($_POST['directory_favorites_id_user']))
            {
                $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_ploopi_user = {$_POST['directory_favorites_id_user']} AND id_user = {$_SESSION['ploopi']['userid']} AND id_contact = 0");
                foreach($_POST['directory_favorites_id_list'] as $id_list)
                {
                    if ($id_list > 0)
                    {
                        $directory_favorites = new directory_favorites();
                        $directory_favorites->open(0, $_SESSION['ploopi']['userid'], $_POST['directory_favorites_id_user'], $id_list);
                        $directory_favorites->save();
                    }
                }
            }
            elseif (!empty($_POST['directory_favorites_id_contact']) && is_numeric($_POST['directory_favorites_id_contact']))
            {
                $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_ploopi_user = 0 AND id_user = {$_SESSION['ploopi']['userid']} AND id_contact = {$_POST['directory_favorites_id_contact']}");
                foreach($_POST['directory_favorites_id_list'] as $id_list)
                {
                    if ($id_list > 0)
                    {
                        $directory_favorites = new directory_favorites();
                        $directory_favorites->open($_POST['directory_favorites_id_contact'], $_SESSION['ploopi']['userid'], 0, $id_list);
                        $directory_favorites->save();
                    }
                }
            }
        }
        ploopi_redirect('admin.php');
    break;

    case 'directory_list_delete':
        if (!empty($_GET['directory_favorites_id_list']) && is_numeric($_GET['directory_favorites_id_list']))
        {
            $directory_list = new directory_list();
            if ($directory_list->open($_GET['directory_favorites_id_list'])) $directory_list->delete();
        }
        ploopi_redirect("admin.php?directoryTabItem=tabFavorites");
    break;
}

if (!empty($_GET['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = $_GET['directoryTabItem'];
if (empty($_SESSION['directory']['directoryTabItem'])) $_SESSION['directory']['directoryTabItem'] = 'favorites';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);

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
}


if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mygroup'])
{
    $tabs['tabMygroup'] = 
        array(    
            'title' => (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'],
            'url'   => "admin.php?directoryTabItem=tabMygroup"
        );
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabMygroup') $_SESSION['directory']['directoryTabItem'] = '';


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

switch($op)
{
    case 'directory_modify':
        if (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
        {
            $directory_contact = new directory_contact();
            $directory_contact->open($_GET['contact_id']);

            echo $skin->open_simplebloc($title.' / '._DIRECTORY_MODIFYCONTACT);
            include './modules/directory/public_directory_form.php';
            echo $skin->close_simplebloc();
        }
        else ploopi_redirect('admin.php');
    break;

    default:
        include './modules/directory/public_directory.php';
    break;

}
?>
