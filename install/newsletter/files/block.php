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
 * Affichage du bloc de menu
 *
 * @package newsletter
 * @subpackage block
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('newsletter', false, false, false);

$newsletter_menu = isset($_GET['newsletter_menu']) ? $_GET['newsletter_menu'] : ''; 

if ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && empty($newsletter_menu) && (!ploopi_isactionallowed(-1, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))) $newsletter_menu = 'consult';

$block->addmenu(_NEWSLETTER_CONSULT, ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&newsletter_menu=consult"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $newsletter_menu == 'consult');

if (ploopi_isactionallowed(-1,$_SESSION['ploopi']['workspaceid'],$menu_moduleid))
{
    $block->addmenu('<strong>'._NEWSLETTER_ADMIN.'</strong>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && empty($newsletter_menu));
}
?>

