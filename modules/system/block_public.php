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
 * Affichage du bloc de menu
 *
 * @package system
 * @subpackage block
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */ 

$op = empty($_REQUEST['op']) ? '' : $_REQUEST['op'];

$block->addmenu(
    _PLOOPI_LABEL_MYTICKETS, 
    ploopi_urlencode('admin.php?op=tickets', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
    $op == 'tickets'
);

$block->addmenu(
    _PLOOPI_LABEL_MYANNOTATIONS, 
    ploopi_urlencode('admin.php?op=annotation', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
    $op == 'annotation'
);

$block->addmenu(
    _PLOOPI_LABEL_MYPROFILE, 
    ploopi_urlencode('admin.php?op=profile', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
    $op == 'profile'
);

$block->addmenu(
    _PLOOPI_LABEL_MYDATA, 
    ploopi_urlencode('admin.php?op=actions', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
    $op == 'actions'
);

$block->addmenu(
    _PLOOPI_LABEL_MYPARAMS, 
    ploopi_urlencode('admin.php?op=param', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
    $op == 'param'
);
?>
