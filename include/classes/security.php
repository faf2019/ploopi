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

        $strPassword = '';

        foreach($arrChar as $str) $strPassword .= substr($str,rand(0,strlen($str)-1),1);
        for($c = strlen($strPassword); $c < $length; $c++) $strPassword .= substr($strChar,rand(0,strlen($strChar)-1),1);

        return(str_shuffle($strPassword));
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
            if ((!defined(_PLOOPI_FILTER_VARS) || _PLOOPI_FILTER_VARS) && substr(is_null($strVarName) ? '' : $strVarName,0,4) != 'fck_') $mixVar = inputfilter::process($mixVar);
        }

        return $mixVar;
    }

}
