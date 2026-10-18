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
 * Gestion des mécanismes de sécurité.
 * Validation de mots de passe, filtrage de contenu, vérification de droits.
 *
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class security
{
    /**
     * Vérifie la solidité d'un mot de passe
     *
     * @param string $password mot de passe à vérifier
     * @param int $min_length longueur mini
     * @param int $max_length longueur maxi
     * @return boolean true si le mot de passe est suffisamment solide
     */

    public static function checkpasswordvalidity($password, $min_length = 0, $max_length = 20)
    {
        if (empty($min_length)) $min_length = _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE;
        $password = str::convertaccents($password);

        return $validity = (
            strlen($password) >= $min_length &&
            strlen($password) <= $max_length &&
            preg_match('/^.*[A-Z].*/', $password) &&
            preg_match('/^.*[a-z].*/', $password) &&
            preg_match('/^.*[0-9].*/', $password) &&
            preg_match('/^.*[!@#\$%\^&\*\(\)_\-\+\}\{"":;\'?\/><\.,\]\[].*/', $password)
        );
    }

    /**
     * Génère un mot de passe paramétrable
     *
     * @param int $length longueur du mot de passe
     * @param boolean $use_char_up true si le mot de passe doit inclure au moins un caractère majuscule
     * @param boolean $use_char_numb true si le mot de passe doit inclure au moins un caractère numérique
     * @param boolean $use_ponc  true si le mot de passe doit inclure au moins un caractère de ponctuation
     * @return string le mot de passe généré
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     */

    public static function generatepassword($length = 8, $use_char_up = true, $use_char_numb = true, $use_ponc = true)
    {
        if ($length<4) $length=4;

        $arrChar = array();
        $arrChar[] = "abcdefghijklmnopqrstuvwxz";
        if ($use_char_up) $arrChar[] = "ABCDEFGHIJKLMNOPQRSTUVWXZ";
        if ($use_char_numb) $arrChar[] = "0123456789";
        if ($use_ponc) $arrChar[] = ":?!@#$%&*";

        $strChar = implode('', $arrChar);

        $arrPassword = array();

        // Au moins un caractère issu de chaque jeu demandé
        foreach($arrChar as $str) $arrPassword[] = $str[random_int(0, strlen($str)-1)];

        for($c = sizeof($arrPassword); $c < $length; $c++) $arrPassword[] = $strChar[random_int(0, strlen($strChar)-1)];

        /**
         * Mélange de Fisher-Yates.
         * str_shuffle() s'appuie sur le générateur non cryptographique de PHP : le mot de
         * passe produit ici sert notamment à la procédure de mot de passe oublié, il ne doit
         * pas être prédictible.
         */
        for($i = sizeof($arrPassword)-1; $i > 0; $i--)
        {
            $j = random_int(0, $i);
            $strTmp = $arrPassword[$i];
            $arrPassword[$i] = $arrPassword[$j];
            $arrPassword[$j] = $strTmp;
        }

        return implode('', $arrPassword);
    }

    /**
     * Génère un jeton aléatoire imprévisible, au format hexadécimal.
     *
     * Utilisé pour tout secret à usage unique : jeton anti-CSRF, code de confirmation.
     * random_bytes() lève une exception si aucune source d'entropie sûre n'est disponible :
     * l'échec est volontairement bruyant, un jeton prédictible valant mieux d'être absent.
     *
     * @param int $intBytes nombre d'octets aléatoires (16 par défaut, soit 32 caractères)
     * @return string jeton hexadécimal de 2 * $intBytes caractères
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     *
     * @see random_bytes
     */

    public static function generatetoken($intBytes = 16)
    {
        if ($intBytes < 8) $intBytes = 8;

        return bin2hex(random_bytes($intBytes));
    }

    /**
     * Filtre le contenu d'une variable.
     * Gère les tableaux multi-dimensionnels.
     *
     * @param mixed $var variable à filtrer
     * @param string $varname nom de la variable (permet notamment de traiter un cas particulier avec les variables préfixées fck_)
     * @return mixed variable filtrée
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     */

    public static function filtervar($mixVar, $strVarName = null)
    {
        if (is_array($mixVar))
        {
            foreach($mixVar as $strKey => $mixValue)
            {
                $mixVar[$strKey] = self::filtervar($mixValue, is_null($strVarName) ? $strKey : $strVarName);
            }
        }
        else
        {
            /**
             * defined() attend le NOM de la constante. En passant sa valeur, le test était
             * inopérant : _PLOOPI_FILTER_VARS à false ne désactivait rien, et l'absence de
             * la constante provoquait une erreur fatale en PHP 8.
             */
            $booFilter = !defined('_PLOOPI_FILTER_VARS') || _PLOOPI_FILTER_VARS;

            if ($booFilter && substr(is_null($strVarName) ? '' : $strVarName,0,4) != 'fck_') $mixVar = inputfilter::process($mixVar);
        }

        return $mixVar;
    }

}
