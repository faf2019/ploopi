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
 * Interface d'administration du module
 *
 * @package booking
 * @subpackage admin
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Initialisation du module
 */
ploopi_init_module('booking');

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

if (isset($_GET['booking_tab'])) $_SESSION['booking']['booking_tab'] = $_GET['booking_tab'];
if (empty($_SESSION['booking']['booking_tab'])) $_SESSION['booking']['booking_tab'] = 'resourcetype';

/**
 * Définition des onglets
 */
$tabs = array();
$tabs['resourcetype'] =  array(
    'title' => 'Types de ressources',
    'url' => "admin.php?booking_tab=resourcetype"
);

$tabs['resource'] = array(
    'title' => 'Ressources',
    'url' => "admin.php?booking_tab=resource"
);

$tabs['subresource'] = array(
    'title' => 'Sous-Ressources',
    'url' => "admin.php?booking_tab=subresource"
);

echo $skin->create_pagetitle(ploopi_htmlentities("{$_SESSION['ploopi']['modulelabel']} - Administration"));
echo $skin->create_tabs($tabs, $_SESSION['booking']['booking_tab']);

echo $skin->open_simplebloc();


switch($_SESSION['booking']['booking_tab'])
{
    case 'resourcetype':
        include_once './modules/booking/admin_resourcetype.php';
    break;

    case 'resource':
        include_once './modules/booking/admin_resource.php';
    break;

    case 'subresource':
        include_once './modules/booking/admin_subresource.php';
    break;
}


echo $skin->close_simplebloc();
?>
