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
 * Classe permettant de filter le contenu d'une variable et de supprimer les entités HTML.
 * Permet d'éviter les injections de type XSS
 */

class inputfilter
{
    /**
     * Traite le contenu de la variable
     *
     * @param string $strSource
     * @return string
     */
    public static function process($strSource)
    {
        return filter_var(strip_tags(self::decode($strSource)));
    }

    /**
     * Essaye de convertir le texte en Plaintext.
     *
     * @copyright: Daniel Morris
     * @email: dan@rootcube.com
     * @param   string  $strSource
     * @return  string  Plaintext string
     */
    private static function decode($strSource)
    {
        // url decode
        $strSource = html_entity_decode($strSource, ENT_QUOTES, "ISO-8859-1");
        // convert decimal
        $strSource = preg_replace_callback('/&#(\d+);/m', function($matches) { return chr($matches[1]); }, $strSource);
        // convert hex
        $strSource = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($matches) { return chr((int)"0x{$matches[1]}"); }, $strSource);

        return $strSource;
    }
}
