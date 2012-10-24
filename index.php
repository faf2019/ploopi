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
 * Point d'entrée pour le frontoffice.
 * Renvoie vers le backoffice si le frontoffice n'est pas activé.
 *
 * @package ploopi
 * @subpackage index
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @author $Author$
 */

/**
 * Chargement de l'environnement
 */

include_once './include/boot.php';

define('_PLOOPI_DIRNAME',  dirname(__FILE__));

switch($ploopi_access_script)
{
    case 'index':
        include_once './include/start.php';
        include_once ($_SESSION['ploopi']['mode'] == 'frontoffice') ? './include/frontoffice.php' : './include/backoffice.php';
    break;
    
    case 'admin':
        include_once './include/start.php';
        include_once './include/backoffice.php';
    break;
    
    case 'admin-light':
    case 'index-light':
        include_once './include/start.php';
        include_once './include/light.php';
    break;
    
    case 'webservice':
        include_once './include/start_light.php';
        include_once './include/webservice.php';
    break;
    
    case 'backend':
        include_once './include/start_light.php';
        include_once './include/backend.php';
    break;
    
    case 'quick':
        include_once './include/quick.php';
    break;
    
}

ploopi_die();
?>
