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
 * @package webedit
 * @subpackage block
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('webedit', false, false, false);

$block->addmenu('Voir les articles', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"), ($_SESSION['ploopi']['moduleid']==$menu_moduleid && $_SESSION['ploopi']['action'] == 'public'));

/**
 * Il faut que l'utilisateur dispose au moins d'une action pour accéder à la partie 'admin'
 */

if (ploopi_isactionallowed(-1, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Gestion du contenu</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}

/**
 * STATISTIQUES
 */

if (ploopi_isactionallowed(_WEBEDIT_ACTION_STATS, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Statistiques</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&webedit_menu=stats"));
}

/**
 * Réindexation du contenu
 */

if (ploopi_isactionallowed(_WEBEDIT_ACTION_REINDEX, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>Réindexation</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&webedit_menu=reindex"));
}

?>
