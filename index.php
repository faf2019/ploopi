<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

define('_PLOOPI_DIRNAME',  dirname(__FILE__));

include_once './include/classes/loader.php';

ploopi\loader::boot();
ploopi\loader::dispatch();
ploopi\system::kill();
?>
