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
 * Fonctions permettant de mettre en place des m�canismes de chiffrement
 * Voir �galement la classe crypt.
 * .
 * @package ploopi
 * @subpackage crypt
 * @copyright Ovensia
 * @license GPL
 * 
 * @see ploopi_cipher
 */


/**
 * G�n�re un mot de passe pour �crire dans le fichier .htpasswd
 *
 * @param string $pass mot de passe en clair
 * @return string mot de passe chiffr�
 * 
 * @see crypt
 */

function ploopi_htpasswd($pass)
{
    return (crypt(trim($pass),CRYPT_STD_DES));
}

/**
 * Chiffre une cha�ne et l'encode en URL
 *
 * @param string $url URL en clair
 * @return string URL chiffr�e
 * 
 * @see urlencode
 */

function ploopi_urlencode($url)
{
    if (defined('_PLOOPI_URL_ENCODE') && _PLOOPI_URL_ENCODE)
    {
        require_once './include/classes/class_cipher.php';
        if (strstr($url,'?')) list($script, $params) = explode('?', $url, 2);
        else {$script = $url; $params = '';}
        $cipher = new ploopi_cipher();
        return("{$script}?ploopi_url=".urlencode($cipher->crypt($params)));
    }
    else return($url);
}

/**
 * Encode une cha�ne en MIME base64 avec compatibilit� du codage pour les URL (m�tode url-safe base64)
 * thx to massimo dot scamarcia at gmail dot com
 * Php version of perl's MIME::Base64::URLSafe, that provides an url-safe base64 string encoding/decoding (compatible with python base64's urlsafe methods)
 *
 * @param string $str cha�ne � coder
 * @return string cha�ne cod�e
 * 
 * @copyright massimo dot scamarcia at gmail dot com
 * 
 * @see base64_encode
 */

function ploopi_base64_encode($str) { return(str_replace(array('+','/','='), array('-','_',''), base64_encode($str))); }

/**
 * D�code une cha�ne en MIME base64 (m�tode url-safe base64)
 *
 * @param string $str cha�ne � d�coder
 * @return string cha�ne d�cod�e
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