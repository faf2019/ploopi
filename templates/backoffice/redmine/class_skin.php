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

namespace ploopi;

use ploopi;

/**
 * Gestion du skin 'redmine'
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see skin_common
 */

/**
 * Gestion de l'affichage du skin 'redmine'
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see skin_common
 */

class skin extends skin_common
{
    /**
     * Construction de la classe skin 'redmine'
     *
     * @return skin
     */

    function __construct()
    {
        parent::__construct('redmine');
    }

    /**
     * Crée un titre de page
     *
     * @param string $title titre de la page
     * @param string $style styles optionnels
     * @return string code html du titre
     */

    public function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<h2 class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}</p>{$title}</h2>";
        else $res = "<h2 class=\"pagetitle\"><p>{$additionnal_title}</p>{$title}</h2>";

        return $res;
    }

    /**
     * Créé un bas de bloc (ferme le dernier bloc ouvert)
     *
     * @return string code html du pied du bloc
     */

    function close_simplebloc()
    {
        return '</div></div>';
    }
}
?>
