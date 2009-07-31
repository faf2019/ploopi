<?php
/*
    Copyright (c) 2008-2009 Ovensia
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
 * @package weathertools
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ploopi_init_module('weathertools', false, false, false);


$arrContent = array();

if (isset($_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_cartevigilance']) && $_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_cartevigilance'])
{
    $arrContent[] = '   
        <div style="padding:2px;">
            <a target="_blank" href="'.$_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_cartevigilance_link'].'"><img src="'.ploopi_urlencode("admin-light.php?ploopi_op=weathertools_getmap&weathertools_typemap=vigilance&weathertools_moduleid={$menu_moduleid}").'" /></a>
        </div>
    ';
}    
if (isset($_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_cartevigicrue']) && $_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_cartevigicrue'])
{
    $arrContent[] = '   
        <div style="padding:2px;">
           <a target="_blank" href="'.$_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_cartevigicrue_link'].'"><img src="'.ploopi_urlencode("admin-light.php?ploopi_op=weathertools_getmap&weathertools_typemap=vigicrue&weathertools_moduleid={$menu_moduleid}").'" /></a>
       </div>
    ';
}

if (!empty($arrContent)) $block->addcontent(implode('', $arrContent));

if (isset($_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_meteometar']) && $_SESSION['ploopi']['modules'][$menu_moduleid]['weathertools_display_meteometar']) $block->addmenu('Consulter les données météo (METAR)', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"));

if (ploopi_isactionallowed(WEATHERTOOLS_ACTION_ADMIN, $_SESSION['ploopi']['workspaceid'], $menu_moduleid)) $block->addmenu('<strong>Administration</strong>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));

?>
