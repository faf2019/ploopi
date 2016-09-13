<?php
/*
    Copyright (c) 2008 Ovensia
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
 * @package booking
 * @subpackage block
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */

ovensia\ploopi\module::init('booking', false, false, false);


$block->addmenu('Voir le planning', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&booking_menu=planning"), ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && isset($_GET['booking_menu']) && $_GET['booking_menu'] == 'planning'));

$block->addmenu('Suivi des demandes', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&booking_menu=monitoring"), ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && isset($_GET['booking_menu']) && $_GET['booking_menu'] == 'monitoring'));

// Administration des données (ressources, types de ressources)
if (ovensia\ploopi\acl::isactionallowed(array(_BOOKING_ACTION_ADMIN_TYPERESOURCE, _BOOKING_ACTION_ADMIN_RESOURCE), $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
    $block->addmenu('<b>Administration</b>', ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"), ($_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin'));

?>
