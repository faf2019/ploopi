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

    You should have received a copy of the GNU GeneralF Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Affichage frontoffice du planning
 *
 * @package booking
 * @subpackage wce
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/*
 * Particularités frontoffice :
 * - le module_id n'est pas en session, il faut utiliser $obj['module_id'];
 * - l'id article "hôte" est dispo via webedit_get_articleid()
 * - l'id rubrique "hôte" est dispo via webedit_get_headingid()
 * - le template "hôte" est dispo via webedit_get_template_name()
 */

if (isset($object))
{
    ploopi_init_module('booking');

    // Importe les variables globales du module (Rappel : on est dans une fonction !)
    global $arrBookingPeriodicity; 
    global $arrBookingSize; 
    global $arrBookingColor; 
    
    // Récupération du module_id du module intégré
    $booking_moduleid = $obj['module_id'];
    
    // Récupération des variables d'environnement de l'article en cours d'affichage
    global $articleid;
    global $headingid;
    global $template_name;
    global $template_path;
    
    if (file_exists("{$template_path}/class_skin.php"))
    {
        include_once "{$template_path}/class_skin.php";
        $skin = new skin();
        
        // Url de base de l'article
        // $url = webedit_get_url();
        $url = "index.php?headingid={$headingid}&articleid={$articleid}";

        $_SESSION['booking'][$booking_moduleid]['article_url'] = $url;
            
        // Récupération du paramètre "op"
        $op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];
        
        switch($object)
        {    
            case 'display': ?><div id="booking_main"><?php include_once './modules/booking/wce_display.php'; ?></div><?php break;
            case 'history': include_once './modules/booking/wce_history.php'; break;
        }

    }
    else echo "Problème de compatibilité avec ce template";
}
?>