<?php
/*
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
 * Gestion du skin 'eyeos'
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
 * inclusion de la classe parent
 */

include_once './include/classes/skin_common.php';

/**
 * Gestion de l'affichage du skin 'eyeos'
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
     * Construction de la classe skin 'eyeos'
     *
     * @return skin
     */

    function skin()
    {
        parent::skin_common('eyeos');
    }
    
    /**
     * Crée une barre d'onglets
     *
     * @param array $tabs tableau associatif d'onglets (propriétés : title, url, width)
     * @param string $tabsel clé de l'onglet sélectionné (par référence), sélectionne par défaut le premier onglet
     * @return string code html de la barre d'onglets
     */

    public function create_tabs($tabs, &$tabsel)
    {

        $res = "<div class=\"tabs\"><div class=\"tabs_inner\">";
        
        if (!isset($tabs[$tabsel])) $tabsel = -1;

        foreach($tabs AS $key => $value)
        {
            if ($tabsel == -1) $tabsel = $key;
            $res .= $this->create_tab($tabs[$key], ($tabsel==$key));
        }

        $res .= "</div></div>";

        return $res;
    }    
}
?>
