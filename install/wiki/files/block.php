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
 * @package wiki
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Initialisation du module
 */

ovensia\ploopi\module::init('wiki', false, false, false);

$strWikiMenu = isset($_GET['wiki_menu']) ? $_GET['wiki_menu'] : '';

$block->addmenu('Page de d�marrage', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $strWikiMenu == '');
$block->addmenu('Index par titre', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&wiki_menu=index_title"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $strWikiMenu == 'index_title');
$block->addmenu('Index par date', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&wiki_menu=index_date"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $strWikiMenu == 'index_date');
if (ovensia\ploopi\acl::isadmin()) $block->addmenu('<strong>R�indexer</strong>', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&wiki_menu=reindex"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && $strWikiMenu == 'reindex');
?>

