<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Opérations sur le planning
 *
 * @package planning
 * @subpackage op
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Switch sur les différentes opérations possibles
 */

switch($ploopi_op)
{
    case 'planning_setresources':
        if (empty($_REQUEST['planning_resources'])) $_REQUEST['planning_resources'] = array();
    case 'planning_refresh':
        include_once './modules/planning/public_planning.php';
        ploopi_die();
    break;

    case 'planning_print':
        ploopi_init_module('planning');
        ploopi_ob_clean();
        include_once './modules/planning/planning_print.php';
        ploopi_die();
    break; 
}
?>
