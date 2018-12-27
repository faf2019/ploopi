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
 * Gestion des mécanismes de chiffrement
 *
 * @package ploopi
 * @subpackage crypt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see ploopi_cipher
 */

abstract class crypt {

    /**
     * Génère un mot de passe pour écrire dans le fichier .htpasswd
     *
     * @param string $pass mot de passe en clair
     * @return string mot de passe chiffré
     *
     * @see crypt
     */

    public static function htpasswd($pass)
    {
        return (crypt(trim($pass),CRYPT_STD_DES));
    }

    /**
     * Injecte le jeton courant dans une url existante
     * Notamment utilisé lors de la redirection vers une URL interne après la phase d'identification de l'utilisation
     */

    public static function urltoken($url)
    {
        // Analyse de l'url
        $arrParsedURL = parse_url($url);

        // Analyse des paramètres
        $arrParams = array();
        if (!empty($arrParsedURL['query'])) parse_str($arrParsedURL['query'], $arrParams);
        // Détection de la présence d'une URL déjà chiffrée !
        if (isset($arrParams['ploopi_url']) && $arrParams['ploopi_url'] != '') {
            // On décode l'URL
            parse_str($query = cipher::singleton()->decrypt($arrParams['ploopi_url']), $arrParams);

            // On reconstruit l'URL complète décodée
            $url = (isset($arrParsedURL['scheme']) ? "{$arrParsedURL['scheme']}://" : '').(isset($arrParsedURL['host']) ? $arrParsedURL['host'] : '').(isset($arrParsedURL['port']) ? ":{$arrParsedURL['port']}" : '')."{$arrParsedURL['path']}".(empty($query) ? '' : "?{$query}").(isset($arrParsedURL['fragment']) ? "#{$arrParsedURL['fragment']}" : '');
        }

        $ploopi_mainmenu = $ploopi_workspaceid = $ploopi_moduleid = $ploopi_action = null;

        // Détection de la présence de l'environnement ploopi (jeton inclus)
        if (isset($arrParams['ploopi_env'])) {
            $arrEnv = preg_split('@[/,]@', $arrParams['ploopi_env']);

            if (isset($arrEnv[0]) && is_numeric($arrEnv[0])) $ploopi_mainmenu = $arrEnv[0];

            if (isset($arrEnv[1]) && is_numeric($arrEnv[1])) $ploopi_workspaceid = $arrEnv[1];

            if (isset($arrEnv[2]) && is_numeric($arrEnv[2])) $ploopi_moduleid = $arrEnv[2];

            if (isset($arrEnv[3])) $ploopi_action = $arrEnv[3];
        }

        return self::urlencode($url, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action);
    }


    /**
     * Version spéciale de self::urlencode qui nécessite que les paramètres soient déjà urlencodés (via la fonction urlencode())
     *
     * @see self::urlencode
     */

    public static function urlencode_trusted($url, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true)
    {
        return self::urlencode($url, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, true);
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

    public static function urlencode($url, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true, $trusted = false)
    {
        if (isset($_SESSION['ploopi']['mode']) && $_SESSION['ploopi']['mode'] == 'frontoffice') $addenv = false;

        $arrParsedURL = parse_url($url);

        if (!isset($arrParsedURL['path'])) return(false);

        // Attention la variable 'HTTP_X_SSL_REQUEST' permet de détecter un frontend gérant le chiffrage SSL, cette solution n'est pas exhaustive
        if (!empty($arrParsedURL['scheme']) && $arrParsedURL['scheme'] == 'http' && isset($_SERVER['HTTP_X_SSL_REQUEST']) && ($_SERVER['HTTP_X_SSL_REQUEST'] == 1 || $_SERVER['HTTP_X_SSL_REQUEST'] == true || $_SERVER['HTTP_X_SSL_REQUEST'] == 'on')) $arrParsedURL['scheme'] = 'https';

        $strQueryEncode = self::queryencode(empty($arrParsedURL['query']) ? '' : $arrParsedURL['query'], $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, $trusted);

        return (isset($arrParsedURL['scheme']) ? "{$arrParsedURL['scheme']}://" : '').(isset($arrParsedURL['host']) ? $arrParsedURL['host'] : '').(isset($arrParsedURL['port']) ? ":{$arrParsedURL['port']}" : '')."{$arrParsedURL['path']}".(empty($strQueryEncode) ? '' : "?{$strQueryEncode}").(isset($arrParsedURL['fragment']) ? "#{$arrParsedURL['fragment']}" : '');
    }

    /**
     * Version spéciale de self::queryencode qui nécessite que les paramètres soient déjà urlencodés (via la fonction urlencode())
     *
     * @see self::queryencode
     */

    public static function queryencode_trusted($query, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true)
    {
        return self::queryencode($query, $ploopi_mainmenu, $ploopi_workspaceid, $ploopi_moduleid, $ploopi_action, $addenv, true);
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

    public static function queryencode($query, $ploopi_mainmenu = null, $ploopi_workspaceid = null, $ploopi_moduleid = null, $ploopi_action = null, $addenv = true, $trusted = false)
    {
        $arrParams = array();

        if (!empty($query)) parse_str($query, $arrParams);

        // Détection de la présence d'une URL déjà chiffrée !
        if (isset($arrParams['ploopi_url']) && $arrParams['ploopi_url'] != '') {
            // On décode l'URL
            parse_str(cipher::singleton()->decrypt($arrParams['ploopi_url']), $arrParams);
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
                "%s/%s/%s/%s/%s",
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

        $strParams = http_build_query($arrParams);


        if (defined('_PLOOPI_URL_ENCODE') && _PLOOPI_URL_ENCODE)
        {
            return "ploopi_url=".str::rawurlencode(cipher::singleton()->crypt($strParams));
        }
        else return $strParams;
    }

    /**
     * Encode une chaîne en MIME base64 avec compatibilité du codage pour les URL (méthode url-safe base64)
     *
     * @param string $str chaîne à coder
     * @return string chaîne codée
     *
     * @see base64_encode
     */

    public static function base64_encode($str) { return rtrim(strtr(base64_encode($str), '+/', '-_'), '='); }

    /**
     * Décode une chaîne en MIME base64 (méthode url-safe base64)
     *
     * @param string $str chaîne à décoder
     * @return string chaîne décodée
     *
     * @see base64_decode
     */

    public static function base64_decode($str) { return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT)); }

    /**
     * Sérialise et compresse une variable
     *
     * @param mixed $mixVar variable à sérialiser
     * @return string chaîne de la variable sérialisée
     */

    public static function serialize($mixVar) { return self::base64_encode(gzcompress(serialize($mixVar), 9)); }

    /**
     * Désérialise une chaîne
     *
     * @param string $str chaîne à décoder
     * @return mixed variable décodée
     */

    public static function unserialize($str)
    {
        $mixVar = null;

        error::unset_handler();
        $mixVar = unserialize(gzuncompress(self::base64_decode($str)));
        error::set_handler();

        return $mixVar;
    }
}
