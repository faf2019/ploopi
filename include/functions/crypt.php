<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Fonctions permettant de mettre en place des mécanismes de chiffrement
 * Voir également la classe crypt.
 *
 * @package ploopi
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see ploopi_cipher
 */


/**
 * Génère un mot de passe pour écrire dans le fichier .htpasswd
 *
 * @param string $pass mot de passe en clair
 * @return string mot de passe chiffré
 * 
 * @see crypt
 */

function ploopi_htpasswd($pass)
{
    return (crypt(trim($pass),CRYPT_STD_DES));
}

/**
 * Chiffre une chaîne et l'encode en URL
 *
 * @param string $url URL en clair
 * @param int $ploopi_mainmenu identifiant du menu principal actif (optionnel)
 * @param int $ploopi_workspaceid identifiant de l'espace de travail actif (optionnel)
 * @param int $ploopi_moduleid identifiant du module actif (optionnel)
 * @param string $ploopi_action type d'action (optionnel)
 * @return string URL chiffrée
 * 
 * @see urlencode
 */

function ploopi_urlencode($url, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null)
{
    $arrParsedURL = parse_url($url);
    
    if (!isset($arrParsedURL['path'])) return(false);

    $arrParams = array();
    
    // on parse les paramètres de l'URL et on met tout ça dans un tableau associatif param => valeur
    if (!empty($arrParsedURL['query']))
    {
        foreach(explode('&', $arrParsedURL['query']) as $param)
        {
            $arrParam = explode('=', $param);
            if (sizeof($arrParam) > 0) $arrParams[$arrParam[0]] = (isset($arrParam[1])) ? $arrParam[1] : null;
        }
    }
    
    // si les paramètres optionnels sont passés à la fonction, on les rajoute au tableau
    if (!empty($ploopi_mainmenu)) $arrParams['ploopi_mainmenu'] = $ploopi_mainmenu;
    if (!empty($ploopi_workspaceid)) $arrParams['ploopi_workspaceid'] = $ploopi_workspaceid;
    if (!empty($ploopi_moduleid)) $arrParams['ploopi_moduleid'] = $ploopi_moduleid;
    if (!empty($ploopi_action)) $arrParams['ploopi_action'] = $ploopi_action;
    
        
    // si des paramètres sont manquants, on va lire la valeur de la session
    if (!isset($arrParams['ploopi_mainmenu'])) $arrParams['ploopi_mainmenu'] = (is_null($ploopi_mainmenu)) ? $_SESSION['ploopi']['mainmenu'] : '';
    if (!isset($arrParams['ploopi_workspaceid'])) $arrParams['ploopi_workspaceid'] = (is_null($ploopi_workspaceid)) ? $_SESSION['ploopi']['workspaceid'] : '';
    if (!isset($arrParams['ploopi_moduleid'])) $arrParams['ploopi_moduleid'] = (is_null($ploopi_moduleid)) ? $_SESSION['ploopi']['moduleid'] : '';
    if (!isset($arrParams['ploopi_action'])) $arrParams['ploopi_action'] = (is_null($ploopi_action)) ? $_SESSION['ploopi']['action'] : '';

    // on génère le "super" paramètre "ploopi_env" qui regroupe ploopi_mainmenu, ploopi_workspaceid, ploopi_moduleid, ploopi_action
    $arrParams['ploopi_env'] = 
        sprintf(
            "%s,%s,%s,%s", 
            $arrParams['ploopi_mainmenu'], 
            $arrParams['ploopi_workspaceid'],
            $arrParams['ploopi_moduleid'],
            $arrParams['ploopi_action']
        );
    
    // on supprime les paramètres superflus 
    unset($arrParams['ploopi_mainmenu']);
    unset($arrParams['ploopi_workspaceid']);
    unset($arrParams['ploopi_moduleid']);
    unset($arrParams['ploopi_action']);
    
    // on génère la chaine de paramètres
    foreach($arrParams as $key => $value) 
    {
        // si pas de chiffrage, on encode les paramètres
        if (!defined('_PLOOPI_URL_ENCODE') || !_PLOOPI_URL_ENCODE) $value = urlencode($value);
        
        $arrParams[$key] = (is_null($value)) ? $key : "{$key}={$value}";
    }
    
    $strParams = implode('&', $arrParams);
    
    //ploopi_print_r($strParams);
    
    if (defined('_PLOOPI_URL_ENCODE') && _PLOOPI_URL_ENCODE)
    {
        require_once './include/classes/cipher.php';
        $cipher = new ploopi_cipher();
        return("{$arrParsedURL['path']}?ploopi_url=".urlencode($cipher->crypt($strParams)));
    }
    else 
    {
        $url = $arrParsedURL['path'];
        if (!empty($strParams)) $url .= '?'.$strParams;
        return($url);
    }
}

/**
 * Encode une chaîne en MIME base64 avec compatibilité du codage pour les URL (métode url-safe base64)
 * thx to massimo dot scamarcia at gmail dot com
 * Php version of perl's MIME::Base64::URLSafe, that provides an url-safe base64 string encoding/decoding (compatible with python base64's urlsafe methods)
 *
 * @param string $str chaîne à coder
 * @return string chaîne codée
 * 
 * @copyright massimo dot scamarcia at gmail dot com
 * 
 * @see base64_encode
 */

function ploopi_base64_encode($str) { return(str_replace(array('+','/','='), array('-','_',''), base64_encode($str))); }

/**
 * Décode une chaîne en MIME base64 (métode url-safe base64)
 *
 * @param string $str chaîne à décoder
 * @return string chaîne décodée
 * 
 * @see base64_decode
 */

function ploopi_base64_decode($str)
{
    $str = str_replace(array('-','_'),array('+','/'),$str);
    $mod4 = strlen($str) % 4;
    if ($mod4) $str .= substr('====', $mod4);
    return base64_decode($str);
}
?>