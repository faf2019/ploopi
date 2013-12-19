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
 * Injecte le jeton courant dans une url existante
 * Notamment utilisé lors de la redirection vers une URL interne après la phase d'identification de l'utilisation
 */

function ploopi_urltoken($url)
{
    // Analyse de l'url
    $arrParsedURL = parse_url($url);


    // Analyse des paramètres
    $arrParams = array();
    if (!empty($arrParsedURL['query'])) parse_str($arrParsedURL['query'], $arrParams);
    // Détection de la présence d'une URL déjà chiffrée !
    if (isset($arrParams['ploopi_url']) && $arrParams['ploopi_url'] != '') {
        // On décode l'URL
        require_once './include/classes/cipher.php';
        parse_str(ploopi_cipher::singleton()->decrypt($arrParams['ploopi_url']), $arrParams);
    }

    $ploopi_mainmenu = $ploopi_workspaceid = $ploopi_moduleid = $ploopi_action = null;

    // Détection de la présence de l'environnement ploopi (jeton inclus)
    if (isset($arrParams['ploopi_env'])) {
        $arrEnv = explode('-', $arrParams['ploopi_env']);

        if (isset($arrEnv[0]) && is_numeric($arrEnv[0])) $ploopi_mainmenu = $arrEnv[0];

        if (isset($arrEnv[1]) && is_numeric($arrEnv[1])) $ploopi_workspaceid = $arrEnv[1];

        if (isset($arrEnv[2]) && is_numeric($arrEnv[2])) $ploopi_moduleid = $arrEnv[2];

        if (isset($arrEnv[3])) $ploopi_action = $arrEnv[3];
    }

    echo ploopi_urlencode($url, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action);


    return ploopi_urlencode($url, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action);
}

/**
 * Version spéciale de ploopi_urlencode qui nécessite que les paramètres soient déjà urlencodés (via la fonction urlencode())
 *
 * @see ploopi_urlencode
 */

function ploopi_urlencode_trusted($url, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true)
{
    return ploopi_urlencode($url, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, true);
}

/**
 * Chiffre une url après avoir ajouté les paramètres d'environnement "ploopi"
 *
 * @param string $url URL en clair
 * @param int $ploopi_mainmenu identifiant du menu principal actif (optionnel)
 * @param int $ploopi_workspaceid identifiant de l'espace de travail actif (optionnel)
 * @param int $ploopi_moduleid identifiant du module actif (optionnel)
 * @param string $ploopi_action type d'action (optionnel)
 * @param boolean $addenv true si la fonction doit ajouter automatiquement les paramètres d'environnement (optionnel, true par défaut)
 * @param boolean $trusted true si l'url est déjà urlencodée (optionnel, false par défaut)
 * @return string URL chiffrée
 *
 * @see urlencode
 */

function ploopi_urlencode($url, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true, $trusted = false)
{
    if (isset($_SESSION['ploopi']['mode']) && $_SESSION['ploopi']['mode'] == 'frontoffice') $addenv = false;

    $arrParsedURL = parse_url($url);

    if (!isset($arrParsedURL['path'])) return(false);

    // Attention la variable 'HTTP_X_SSL_REQUEST' permet de détecter un frontend gérant le chiffrage SSL, cette solution n'est pas exhaustive
    if (!empty($arrParsedURL['scheme']) && $arrParsedURL['scheme'] == 'http' && isset($_SERVER['HTTP_X_SSL_REQUEST']) && ($_SERVER['HTTP_X_SSL_REQUEST'] == 1 || $_SERVER['HTTP_X_SSL_REQUEST'] == true || $_SERVER['HTTP_X_SSL_REQUEST'] == 'on')) $arrParsedURL['scheme'] = 'https';

    $strQueryEncode = ploopi_queryencode(empty($arrParsedURL['query']) ? '' : $arrParsedURL['query'], $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, $trusted);

    return (isset($arrParsedURL['scheme']) ? "{$arrParsedURL['scheme']}://" : '').(isset($arrParsedURL['host']) ? $arrParsedURL['host'] : '').(isset($arrParsedURL['port']) ? ":{$arrParsedURL['port']}" : '')."{$arrParsedURL['path']}".(empty($strQueryEncode) ? '' : "?{$strQueryEncode}").(isset($arrParsedURL['fragment']) ? "#{$arrParsedURL['fragment']}" : '');
}

/**
 * Version spéciale de ploopi_queryencode qui nécessite que les paramètres soient déjà urlencodés (via la fonction urlencode())
 *
 * @see ploopi_queryencode
 */

function ploopi_queryencode_trusted($query, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true)
{
    return ploopi_queryencode($query, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, true);
}

/**
 * Chiffre une chaîne de paramètres après avoir ajouté les paramètres d'environnement "ploopi"
 *
 * @param string $query paramètres en clair
 * @param int $ploopi_mainmenu identifiant du menu principal actif (optionnel)
 * @param int $ploopi_workspaceid identifiant de l'espace de travail actif (optionnel)
 * @param int $ploopi_moduleid identifiant du module actif (optionnel)
 * @param string $ploopi_action type d'action (optionnel)
 * @param boolean $addenv true si la fonction doit ajouter automatiquement les paramètres d'environnement (optionnel, true par défaut)
 * @param boolean $trusted true si l'url est déjà urlencodée (optionnel, false par défaut)
 * @return string chaîne de paramètres chiffrée
 */

function ploopi_queryencode($query, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true, $trusted = false)
{
    $arrParams = array();

    if (!empty($query)) parse_str($query, $arrParams);
    // Détection de la présence d'une URL déjà chiffrée !
    if (isset($arrParams['ploopi_url']) && $arrParams['ploopi_url'] != '') {
        // On décode l'URL
        require_once './include/classes/cipher.php';
        parse_str(ploopi_cipher::singleton()->decrypt($arrParams['ploopi_url']), $arrParams);
    }

    // si les paramètres optionnels sont passés à la fonction, on les rajoute au tableau
    if (!is_null($ploopi_mainmenu)) $arrParams['ploopi_mainmenu'] = $ploopi_mainmenu;
    if (!is_null($ploopi_workspaceid)) $arrParams['ploopi_workspaceid'] = $ploopi_workspaceid;
    if (!is_null($ploopi_moduleid)) $arrParams['ploopi_moduleid'] = $ploopi_moduleid;
    if (!is_null($ploopi_action)) $arrParams['ploopi_action'] = $ploopi_action;

    if ($addenv && isset($_SESSION['ploopi']['moduleid']))
    {
        // si des paramètres sont manquants, on va lire la valeur de la session
        if (!isset($arrParams['ploopi_mainmenu'])) $arrParams['ploopi_mainmenu'] = (is_null($ploopi_mainmenu)) ? $_SESSION['ploopi']['mainmenu'] : '';
        if (!isset($arrParams['ploopi_workspaceid'])) $arrParams['ploopi_workspaceid'] = (is_null($ploopi_workspaceid)) ? $_SESSION['ploopi']['workspaceid'] : '';
        if (!isset($arrParams['ploopi_moduleid'])) $arrParams['ploopi_moduleid'] = (is_null($ploopi_moduleid)) ? $_SESSION['ploopi']['moduleid'] : '';
        if (!isset($arrParams['ploopi_action'])) $arrParams['ploopi_action'] = (is_null($ploopi_action)) ? $_SESSION['ploopi']['action'] : '';

        // on génère le "super" paramètre "ploopi_env" qui regroupe ploopi_mainmenu, ploopi_workspaceid, ploopi_moduleid, ploopi_action
        $arrParams['ploopi_env'] = sprintf(
            "%s-%s-%s-%s-%s",
            $arrParams['ploopi_mainmenu'],
            $arrParams['ploopi_workspaceid'],
            $arrParams['ploopi_moduleid'],
            $arrParams['ploopi_action'],
            isset($_SESSION['ploopi']['token']) ? $_SESSION['ploopi']['token'] : ''
        );

        // on supprime les paramètres superflus
        unset($arrParams['ploopi_mainmenu']);
        unset($arrParams['ploopi_workspaceid']);
        unset($arrParams['ploopi_moduleid']);
        unset($arrParams['ploopi_action']);
    }

    // on génère la chaine de paramètres
    foreach($arrParams as $strKey => $strValue)
    {
        // urlencode les paramètres
        if (!$trusted) $strValue = ploopi_rawurlencode($strValue);

        $arrParams[$strKey] = (is_null($strValue) || $strValue == '') ? $strKey : "{$strKey}={$strValue}";
    }

    //$strParams = implode('&amp;', $arrParams);
    $strParams = implode('&', $arrParams);

    if (defined('_PLOOPI_URL_ENCODE') && _PLOOPI_URL_ENCODE)
    {
        require_once './include/classes/cipher.php';
        return "ploopi_url=".ploopi_rawurlencode(ploopi_cipher::singleton()->crypt($strParams));
    }
    else return $strParams;
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

function ploopi_base64_encode($str) { return strtr(base64_encode($str), '+/=', '-_,'); }

/**
 * Décode une chaîne en MIME base64 (métode url-safe base64)
 *
 * @param string $str chaîne à décoder
 * @return string chaîne décodée
 *
 * @see base64_decode
 */

function ploopi_base64_decode($str) { return base64_decode(strtr($str, '-_,', '+/=' )); }

/**
 * Sérialise et compresse une variable
 *
 * @param mixed $mixVar variable à sérialiser
 * @return string chaîne de la variable sérialisée
 */

function ploopi_serialize($mixVar) { return ploopi_base64_encode(gzcompress(serialize($mixVar), 9)); }

/**
 * Désérialise une chaîne
 *
 * @param string $str chaîne à décoder
 * @return mixed variable décodée
 */

function ploopi_unserialize($str)
{
    $mixVar = null;

    ploopi_unset_error_handler();
    $mixVar = unserialize(gzuncompress(ploopi_base64_decode($str)));
    ploopi_set_error_handler();

    return $mixVar;
}
