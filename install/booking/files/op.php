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
 * Opérations du module Booking
 * 
 * @package booking
 * @subpackage op
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * On vérifie qu'on est bien dans le module Booking.
 */

if (ploopi_ismoduleallowed('booking'))
{
    /**
     * Opérations sur les types de ressources
     */
    include_once './modules/booking/op_resourcetype.php';
    
    /**
     * Opérations sur les ressources
     */
    include_once './modules/booking/op_resource.php';
    
    /**
     * Opérations sur les événements
     */
    include_once './modules/booking/op_event.php';
    
    /**
     * Opérations sur le planning
     */
    include_once './modules/booking/op_planning.php';    
    
}
else // on n'est pas dans le module, peut être une requête frontoffice ?
{
    if ($_SESSION['ploopi']['mode'] == 'frontoffice' && !empty($_GET['booking_moduleid']) && is_numeric($_GET['booking_moduleid']) && ploopi_ismoduleallowed('booking', $_GET['booking_moduleid'])) 
    {
        $booking_moduleid = $_GET['booking_moduleid'];
        
        include_once './modules/booking/op_event.php';
        
        /**
         * Opérations sur le planning
         */
        include_once './modules/booking/op_wce_planning.php';
            
        ploopi_die();
    }
}
?>
