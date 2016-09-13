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
 * Classe abstraite permettant de formater un contenu SQL à la manière de printf
 */
abstract class sqlformat
{
    /**
     * Regex utilisée pour détecter point d'injection de données
     *
     * @var string
     */
    private static $strRegExFormat = '|%(([0-9]*)\$){0,1}([s,d,f,t,e,g,r])|';

    /**
     * Numéro du paramètre traité
     *
     * @var int
     * @see replace
     */
    private static $intNumParam = 0;

    /**
     * Tableau des valeurs de remplacement
     *
     * @var array
     * @see replace
     */
    private static $arrValues = null;

    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    private static $objDb = null;

    /**
     * Méthode de remplacement appelée en callback via preg_replace_callback
     *
     * @param array $arrMatches Tableau contenant le texte satisfaisant au masque de recherche
     * @return string chaîne modifiée
     * @link http://fr.php.net/manual/fr/function.preg-replace-callback.php
     */
    private static function cb_replace($arrMatches)
    {
        global $db;

        if (sizeof($arrMatches) == 4)
        {
            $intNumParam = empty($arrMatches[2]) ? ++self::$intNumParam - 1 : intval($arrMatches[2]) - 1;

            // La valeur correspondante du paramètre peut être un tableau de valeurs ou une valeur simple
            $mixValue = isset(self::$arrValues[$intNumParam]) ? self::$arrValues[$intNumParam] : null;

            switch($arrMatches[3])
            {
                case 't': // list string
                case 'e': // list integer
                case 'g': // list float
                    $arrValues = is_array($mixValue) ? $mixValue : preg_split('/,/', $mixValue);
                    $arrValues = arr::map('trim', $arrValues);
                    foreach($arrValues as &$strListValue)
                    {
                        switch($arrMatches[3])
                        {
                            case 't':
                                $strListValue = "'".self::$objDb->addslashes($strListValue)."'";
                            break;
                            case 'e':
                                $strListValue = intval($strListValue);
                            break;
                            case 'g':
                                $strListValue = floatval($strListValue);
                            break;
                        }
                    }
                    $strValue = implode(',', $arrValues);
                break;

                case 'd': // integer
                    $strValue = intval($mixValue);
                break;

                case 'f': // float
                    $strValue = floatval($mixValue);
                break;

                case 's': // string
                    $strValue = "'".self::$objDb->addslashes($mixValue)."'";
                break;

                case 'r': // raw
                default:
                    $strValue = $mixValue;
                break;
            }

            return $strValue;
        }
    }

    /**
     * Méthode publique de remplacement
     *
     * @param array $arrData tableau associatif contenant la chaîne SQL brute (rawsql) et les valeurs de remplacement (values)
     * @param resource $objDb connexion à la BDD
     * @return string chaîne modifiée
     */
    public static function replace($arrData, $objDb = null)
    {
        // Initialisation du numéro de paramètre en cours de traitement
        self::$intNumParam = 0;

        // Initialisation de la connexion à la BDD
        if (is_null($objDb)) { global $db; self::$objDb = $db; }
        else self::$objDb = $objDb;

        // Initialisation des valeurs de remplacement
        self::$arrValues = $arrData['values'];

        // Remplacement des variables selon la regex
        return preg_replace_callback(self::$strRegExFormat, array('self', 'cb_replace'), $arrData['rawsql']);
    }
}
