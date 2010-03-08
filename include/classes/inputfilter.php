<?php
/*
    Copyright (c) 2010 Ovensia
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
 * Filtrage des variables
 *
 * @package ploopi
 * @subpackage inputfilter
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 */

/**
 * Classe permettant de filter le contenu d'une variable et de supprimer les entités HTML.
 * Permet d'éviter les injections de type XSS
 */

class ploopi_inputfilter
{
    /**
     * Traite le contenu de la variable
     *
     * @param string $strSource
     * @return string
     */
    public function process($strSource)
    {
        $strSource = $this->decode($strSource);
        
        do {
            $intC = 0;
            $strSource = preg_replace('/(<)([^>]*?<)/' , '&lt;$2' , $strSource , -1 , $intC);
        } while ($intC > 0);
        
        $strSource = strip_tags($strSource);
        $strSource = str_replace('&lt;' , '>' , $strSource);
        
        return $strSource;        
    }
    
    /**
     * Essaye de convertir le texte en Plaintext.
     *
     * @copyright: Daniel Morris
     * @email: dan@rootcube.com
     * @param   string  $strSource
     * @return  string  Plaintext string
     */
    protected function decode($strSource)
    {
        // url decode
        $strSource = html_entity_decode($strSource, ENT_QUOTES, "ISO-8859-1");
        // convert decimal
        $strSource = preg_replace('/&#(\d+);/me', "chr(\\1)", $strSource); // decimal notation
        // convert hex
        $strSource = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $strSource); // hex notation
        
        return $strSource;
    }
    
    
}
?>