<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Gestion du skin 'exemple'
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see skin_common
 */

/**
 * Gestion de l'affichage du skin 'exemple'
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see skin_common
 */

class skin extends ploopi\skin
{
    /**
     * Construction de la classe skin 'exemple'
     *
     * @return skin
     */

    function __construct()
    {
        parent::__construct('exemple_login');
    }

}
?>
