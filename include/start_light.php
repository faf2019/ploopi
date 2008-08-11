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
 * Script de chargement de l'environnement Ploopi, version allégée utilisée pour les scripts cron.php, webservice.php, rss.php
 * Attention l'environnement chargé est minimal !
 * 
 * @package ploopi
 * @subpackage start
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Chargement de la partie commune de chargement de l'environnement
 */

include './include/start/common.php';

/**
 * Chargement des classes principales (dans la version light, pas grand chose)
 */

include_once './include/classes/workspace.php';

if ($ploopi_initsession) include './include/start/initsession.php';

switch($_SESSION['ploopi']['scriptname'])
{
    case 'cron.php':
    case 'webservice.php':
        $_SESSION['ploopi']['mode'] = 'backoffice';
    break;

    default:
    case 'rss.php':
        $_SESSION['ploopi']['mode'] = 'frontoffice';
    break;

}

switch ($_SESSION['ploopi']['mode'])
{
    case 'frontoffice':
        if (isset($ploopi_hosts['frontoffice'][0]))
            $_SESSION['ploopi']['workspaceid'] = $ploopi_hosts['frontoffice'][0];
        else ploopi_die();
    break;
    
    case 'backoffice':
        if (isset($ploopi_hosts['backoffice'][0]))
            $_SESSION['ploopi']['workspaceid'] = $ploopi_hosts['backoffice'][0];
        else ploopi_die();
        
        include './include/load_param.php';
        ploopi_loadparams();
    break;
}

?>