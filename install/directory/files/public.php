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

ploopi_init_module('directory');
include_once('./modules/directory/class_directory_contact.php');
include_once('./modules/directory/class_directory_favorites.php');

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

switch($op)
{
	case 'directory_save':
		$directory_contact = new directory_contact();
		if (!empty($_POST['contact_id']) && is_numeric($_POST['contact_id'])) $directory_contact->open($_POST['contact_id']);
		$directory_contact->setvalues($_POST,'directory_contact_');
		$directory_contact->setuwm();
		$directory_contact->save();
		ploopi_redirect($scriptenv);
	break;

	case 'directory_delete':
		if (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
		{
			$directory_contact = new directory_contact();
			if ($directory_contact->open($_GET['contact_id'])) $directory_contact->delete();
		}
		ploopi_redirect($scriptenv);
	break;

	case 'directory_favorites_add':
		$directory_favorites = new directory_favorites();
		if (!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
		{
			$directory_favorites->open(0, $_SESSION['ploopi']['userid'], $_GET['user_id']);
			$directory_favorites->save();
		}
		elseif (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
		{
			$directory_favorites->open($_GET['contact_id'], $_SESSION['ploopi']['userid'], 0);
			$directory_favorites->save();
		}
		ploopi_redirect($scriptenv);
	break;

	case 'directory_favorites_delete':
		if (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
		{
			$directory_favorites = new directory_favorites();
			if ($directory_favorites->open($_GET['contact_id'], $_SESSION['ploopi']['userid'], 0)) $directory_favorites->delete();
		}
		elseif (!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
		{
			$directory_favorites = new directory_favorites();
			if ($directory_favorites->open(0, $_SESSION['ploopi']['userid'], $_GET['user_id'])) $directory_favorites->delete();
		}
		ploopi_redirect($scriptenv);
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


if ($_SESSION['ploopi']['connected']) // user connected
{
	if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_myfavorites'])
	{
		$tabs['tabFavorites'] = array(	'title'	=> (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'])) ? _DIRECTORY_FAVORITES : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_myfavorites'],
										'url'	=> "{$scriptenv}?directoryTabItem=tabFavorites"
									);
	}
	elseif ($_SESSION['directory']['directoryTabItem'] == 'tabFavorites') $_SESSION['directory']['directoryTabItem'] = '';

	if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mycontacts'])
	{
		$tabs['tabMycontacts'] = array(	'title'	=> (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'])) ? _DIRECTORY_MYCONTACTS : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mycontacts'],
										'url'	=> "{$scriptenv}?directoryTabItem=tabMycontacts"
										);
	}
	elseif ($_SESSION['directory']['directoryTabItem'] == 'tabMycontacts') $_SESSION['directory']['directoryTabItem'] = '';

	if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_mygroup'])
	{
		$tabs['tabMygroup'] = array(	'title'	=> (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'])) ? _DIRECTORY_MYGROUP : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_mygroup'],
										'url'	=> "{$scriptenv}?directoryTabItem=tabMygroup"
									);
	}
	elseif ($_SESSION['directory']['directoryTabItem'] == 'tabMygroup') $_SESSION['directory']['directoryTabItem'] = '';
}

elseif ($_SESSION['directory']['directoryTabItem'] == 'tabUsers') $_SESSION['directory']['directoryTabItem'] = '';

if ($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_search'])
{
	$tabs['tabSearch'] = array(	'title'	=> (empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'])) ? _DIRECTORY_SEARCH : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['directory_label_search'],
								'url'	=> "{$scriptenv}?directoryTabItem=tabSearch"
							);
}
elseif ($_SESSION['directory']['directoryTabItem'] == 'tabSearch') $_SESSION['directory']['directoryTabItem'] = '';

echo $skin->create_tabs('',$tabs,$_SESSION['directory']['directoryTabItem']);

switch($op)
{
	case 'directory_modify':
		if (!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
		{
			$directory_contact = new directory_contact();
			$directory_contact->open($_GET['contact_id']);

			echo $skin->open_simplebloc($title.' / '._DIRECTORY_MODIFYCONTACT);
			include('./modules/directory/public_directory_form.php');
			echo $skin->close_simplebloc();
		}
		else ploopi_redirect($scriptenv);
	break;

	case 'directory_view':
		if (		(!empty($_GET['contact_id']) && is_numeric($_GET['contact_id']))
				|| 	(!empty($_GET['user_id']) && is_numeric($_GET['user_id']))
			)
			include('./modules/directory/public_directory_view.php');
		else
			ploopi_redirect($scriptenv);
	break;

	default:
		include('./modules/directory/public_directory.php');
	break;

}
?>
