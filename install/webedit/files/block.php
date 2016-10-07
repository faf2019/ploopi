<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @package webedit
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi\module::init('webedit', false, false, false);

$block->addmenu('Voir les articles', ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"), ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public'));


$webedit_menu = ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && !empty($_REQUEST['webedit_menu'])) ? $_REQUEST['webedit_menu'] : '';

/**
 * Il faut que l'utilisateur dispose au moins d'une action pour accéder à la partie 'admin'
 */

if (ploopi\acl::isactionallowed(-1, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Gestion du contenu</b>', ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin' && $webedit_menu == '');
}

/**
 * STATISTIQUES
 */

if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_STATS, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Statistiques</b>', ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&webedit_menu=stats"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin' && $webedit_menu == 'stats');
}

/**
 * Réindexation du contenu
 */

if (ploopi\acl::isactionallowed(_WEBEDIT_ACTION_REINDEX, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Réindexation</b>', ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&webedit_menu=reindex"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin' && $webedit_menu == 'reindex');
}

?>
