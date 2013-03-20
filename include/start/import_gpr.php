<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2012 Ovensia
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
 * Filtre les superglobales $_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER
 * Déchiffre l'URL si elle est chiffrée.
 *
 * @package ploopi
 * @subpackage security
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see ploopi_filtervar
 * @see ploopi_cipher
 */

include_once './include/functions/security.php';

/**
 * Traitement du paramètre spécial 'ploopi_url' via POST/GET
 */

foreach(array('POST', 'GET') as $strGlobalVar)
{
    if (!empty(${"_{$strGlobalVar}"}['ploopi_url']))
    {
        ${"_{$strGlobalVar}"}['ploopi_url'] = ploopi_filtervar(${"_{$strGlobalVar}"}['ploopi_url']);

        require_once './include/classes/cipher.php';
        $objCipher = new ploopi_cipher();

        $strPloopiUrl = ploopi_cipher::singleton()->decrypt(${"_{$strGlobalVar}"}['ploopi_url']);

        foreach(explode('&',$strPloopiUrl) as $strParam)
        {
            if (strstr($strParam, '=')) list($strKey, $strValue) = explode('=',$strParam);
            else {$strKey = $strParam; $strValue = '';}

            // Variable structurée ?
            // Traitement des variables de type var[dimension1][dimension2]=value
            if (($pos = strpos($strKey, '[')) !== false)
            {
                // Extraction des [xxx]
                preg_match_all('@\[([^\]]*)\]@', $strKey, $arrMatches);
                if (sizeof($arrMatches))
                {
                    $funcBuildVar = function($var, $key = 0) use ($arrMatches, $strValue, &$funcBuildVar)
                    {
                        // Cas général : on construit la variable en suivant la branche
                        if (isset($arrMatches[1][$key])) {
                            if (!isset($var[$arrMatches[1][$key]])) $var[$arrMatches[1][$key]] = array();
                            $var[$arrMatches[1][$key]] = $funcBuildVar($var[$arrMatches[1][$key]], $key+1);
                        }
                        // Cas particuler : terminaison de branche, on stocke la variable
                        else $var = urldecode($strValue);

                        return $var;
                    };

                    // Variable racine
                    $strRootKey = substr($strKey, 0, $pos);

                    if (!isset($_REQUEST[$strRootKey])) $_REQUEST[$strRootKey] = array();
                    $_REQUEST[$strRootKey] = ${"_{$strGlobalVar}"}[$strRootKey] = $funcBuildVar($_REQUEST[$strRootKey]);
                }
            }
            // variable simple
            else $_REQUEST[$strKey] = ${"_{$strGlobalVar}"}[$strKey] = urldecode($strValue);
        }

        unset($strKey);
        unset($strValue);
        unset($strParam);
        unset($strPloopiUrl);
        unset($objCipher);
        unset(${"_{$strGlobalVar}"}['ploopi_url']);
    }
}
unset($strGlobalVar);
unset($_REQUEST['ploopi_url']);

$_GET = ploopi_filtervar($_GET, null, !empty($_POST['ploopi_xhr']));;
$_POST = ploopi_filtervar($_POST, null, !empty($_POST['ploopi_xhr']));
$_REQUEST = ploopi_filtervar($_REQUEST, null, !empty($_POST['ploopi_xhr']));
$_COOKIE = ploopi_filtervar($_COOKIE);
$_SERVER = ploopi_filtervar($_SERVER);
