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

namespace ploopi;

use ploopi;

/**
 * Retire les balises HTML du contenu d'une variable entrante.
 *
 * ATTENTION : ce filtre ne constitue PAS une protection contre les injections XSS et ne
 * doit jamais être invoqué comme telle.
 *
 * strip_tags() ne protège ni le contexte attribut (" onmouseover=... x="), ni le contexte
 * JavaScript, ni les URL "javascript:" — soit précisément les contextes que le backoffice
 * génère. La seule protection est l'échappement en sortie :
 *
 * @see str::htmlentities  pour tout affichage de donnée
 * @see str::htmlpurifier  pour du HTML riche
 *
 * Ce filtre n'est conservé que comme filet résiduel, sur les variables entrantes non
 * préfixées "fck_". Il peut être désactivé par _PLOOPI_FILTER_VARS.
 *
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see security::filtervar
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
        /**
         * Les valeurs non textuelles sont rendues telles quelles. $_SERVER en contient
         * (REQUEST_TIME, REQUEST_TIME_FLOAT) : l'ancienne écriture les transtypait en chaîne,
         * et transformait null en chaîne vide au prix d'une dépréciation en PHP 8.1.
         */
        if (!is_string($strSource)) return $strSource;

        // filter_var() sans filtre vaut FILTER_UNSAFE_RAW : l'appel était sans effet
        return strip_tags(self::_decode($strSource));
    }

    /**
     * Décode les entités HTML afin que strip_tags() voie les balises encodées.
     *
     * @copyright Daniel Morris
     * @email dan@rootcube.com
     * @param string $strSource texte non décodé
     *
     * @return string Texte décodé
     */
    private static function _decode($strSource)
    {
        if ($strSource === '') return $strSource;

        /**
         * L'application travaille en UTF-8 (@see loader::boot). Décoder en ISO-8859-1
         * transformait toute entité nommée en un octet isolé, produisant de l'UTF-8 invalide.
         */
        $strSource = html_entity_decode($strSource, ENT_QUOTES, 'UTF-8');

        // Entités décimales
        $strSource = preg_replace_callback('/&#(\d+);/m', function($arrMatches) { return inputfilter::_chr((int)$arrMatches[1]); }, $strSource);

        /**
         * Entités hexadécimales.
         * L'ancienne écriture appliquait chr((int)"0x1f") : le transtypage d'une chaîne
         * hexadécimale vaut 0 en PHP, ce qui injectait un octet NUL à la place de chaque
         * entité au lieu de la décoder.
         */
        $strSource = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($arrMatches) { return inputfilter::_chr(hexdec($arrMatches[1])); }, $strSource);

        return $strSource;
    }

    /**
     * Convertit un point de code Unicode en sa représentation UTF-8.
     *
     * chr() ramenait la valeur modulo 256 et corrompait donc toute entité hors ASCII.
     *
     * @param int $intCodePoint point de code
     * @return string caractère UTF-8, chaîne vide si le point de code est hors limites
     */
    public static function _chr($intCodePoint)
    {
        if ($intCodePoint < 0 || $intCodePoint > 0x10FFFF) return '';

        // mb_chr() n'existe qu'à partir de PHP 7.2, le projet acceptant PHP 7.0
        if (function_exists('mb_chr')) return (string)mb_chr($intCodePoint, 'UTF-8');

        return html_entity_decode('&#'.$intCodePoint.';', ENT_QUOTES, 'UTF-8');
    }
}
