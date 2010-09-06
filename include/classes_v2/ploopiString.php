<?php
/*
    Copyright (c) 2007-2010 Ovensia
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
 * Fonction de manipulation de chaînes.
 * Conversion, découpage, réécriture, etc..
 *
 * @package ploopi
 * @subpackage string
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopiString extends ploopiFactory
{
    private $_strString;

    public function __construct($strString)
    {
        $this->setString($strString);
    }

    public function getString() { return $this->_strString; }

    public function setString($strString)
    {
        if (is_object($strString) || is_array($strString)) throw new ploopiException('Not a string');
        $this->_strString = $strString;
        return $this;
    }

    /**
     * Insère un retour à la ligne HTML à chaque nouvelle ligne, améliore le comportement de la fonction php nl2br()
     *
     * @param string $strStr
     * @return string chaîne modifiée
     */

    public function nl2br()
    {
       $this->_strString = preg_replace("/\r\n|\n|\r/", "<br />", $this->_strString);
       return $this;
    }

    /**
     * Convertit tous les caractères accentués en caractères non accentués en préservant les majuscules/minuscules
     *
     * @param string $str chaîne à convertir
     * @return string chaîne modifiée
     */

    public function convertAccents()
    {
        $this->_strString = strtr(
            $this->_strString,
            "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿÞ",
            "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyys"
        );

        return $this;
    }


    /**
     * Décode les caractères iso non représentables
     *
     * @param boolean true si les caractères doivent être adaptés
     * @return chaîne décodée
     */
    public function iso8859Clean($booTranslit = true)
    {
        $this->_strString = strtr($this->_strString, array(
           "\x80" => "&#8364;", /* EURO SIGN */
           "\x82" => "&#8218;", /* SINGLE LOW-9 QUOTATION MARK */
           "\x83" => "&#402;",  /* LATIN SMALL LETTER F WITH HOOK */
           "\x84" => "&#8222;", /* DOUBLE LOW-9 QUOTATION MARK */
           "\x85" => "&#8230;", /* HORIZONTAL ELLIPSIS */
           "\x86" => "&#8224;", /* DAGGER */
           "\x87" => "&#8225;", /* DOUBLE DAGGER */
           "\x88" => "&#710;",  /* MODIFIER LETTER CIRCUMFLEX ACCENT */
           "\x89" => "&#8240;", /* PER MILLE SIGN */
           "\x8a" => "&#352;",  /* LATIN CAPITAL LETTER S WITH CARON */
           "\x8b" => "&#8249;", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
           "\x8c" => "&#338;",  /* LATIN CAPITAL LIGATURE OE */
           "\x8e" => "&#381;",  /* LATIN CAPITAL LETTER Z WITH CARON */
           "\x91" => "&#8216;", /* LEFT SINGLE QUOTATION MARK */
           "\x92" => "&#8217;", /* RIGHT SINGLE QUOTATION MARK */
           "\x93" => "&#8220;", /* LEFT DOUBLE QUOTATION MARK */
           "\x94" => "&#8221;", /* RIGHT DOUBLE QUOTATION MARK */
           "\x95" => "&#8226;", /* BULLET */
           "\x96" => "&#8211;", /* EN DASH */
           "\x97" => "&#8212;", /* EM DASH */

           "\x98" => "&#732;",  /* SMALL TILDE */
           "\x99" => "&#8482;", /* TRADE MARK SIGN */
           "\x9a" => "&#353;",  /* LATIN SMALL LETTER S WITH CARON */
           "\x9b" => "&#8250;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
           "\x9c" => "&#339;",  /* LATIN SMALL LIGATURE OE */
           "\x9e" => "&#382;",  /* LATIN SMALL LETTER Z WITH CARON */
           "\x9f" => "&#376;"   /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
        ));

        if ($booTranslit)
            $this->_strString = strtr($this->_strString, array(
               '&#8364;' => 'Euro', /* EURO SIGN */
               '&#8218;' => ',',    /* SINGLE LOW-9 QUOTATION MARK */
               '&#402;' => 'f',     /* LATIN SMALL LETTER F WITH HOOK */
               '&#8222;' => ',,',   /* DOUBLE LOW-9 QUOTATION MARK */
               '&#8230;' => '...',  /* HORIZONTAL ELLIPSIS */
               '&#8224;' => '+',    /* DAGGER */
               '&#8225;' => '++',   /* DOUBLE DAGGER */
               '&#710;' => '^',     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
               '&#8240;' => '0/00', /* PER MILLE SIGN */
               '&#352;' => 'S',     /* LATIN CAPITAL LETTER S WITH CARON */
               '&#8249;' => '<',    /* SINGLE LEFT-POINTING ANGLE QUOTATION */
               '&#338;' => 'OE',    /* LATIN CAPITAL LIGATURE OE */
               '&#381;' => 'Z',     /* LATIN CAPITAL LETTER Z WITH CARON */
               '&#8216;' => "'",    /* LEFT SINGLE QUOTATION MARK */
               '&#8217;' => "'",    /* RIGHT SINGLE QUOTATION MARK */
               '&#8220;' => '"',    /* LEFT DOUBLE QUOTATION MARK */
               '&#8221;' => '"',    /* RIGHT DOUBLE QUOTATION MARK */
               '&#8226;' => '*',    /* BULLET */
               '&#8211;' => '-',    /* EN DASH */
               '&#8212;' => '--',   /* EM DASH */
               '&#732;' => '~',     /* SMALL TILDE */
               '&#8482;' => '(TM)', /* TRADE MARK SIGN */
               '&#353;' => 's',     /* LATIN SMALL LETTER S WITH CARON */
               '&#8250;' => '>',    /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
               '&#339;' => 'oe',    /* LATIN SMALL LIGATURE OE */
               '&#382;' => 'z',     /* LATIN SMALL LETTER Z WITH CARON */
               '&#376;' => 'Y'      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
            ));

        return $this;
    }


    public function __toString() { return $this->_strString; }
}
