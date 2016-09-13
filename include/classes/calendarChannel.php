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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Classe de gestion des canaux du calendrier
 */

class calendarChannel
{
    /**
     * Titre
     */
    private $strTitle;

    /**
     * Couleur
     */
    private $strColor;


    /**
     * Constructeur de la classe
     *
     * @param string $strTitle Titre
     * @param string $strColor Couleur
     *
     * @return calendarChannel
     */
    public function __construct($strTitle = null, $strColor = null)
    {
        $this->strTitle = $strTitle;
        $this->strColor = $strColor;
    }

    /**
     * Getter par défaut
     *
     * @param string $strName nom de la propriété à lire
     * @return string valeur de la propriété si elle existe
     */
    public function __get($strName)
    {
        if (isset($this->{$strName})) return $this->{$strName};
        else return null;
    }
}
