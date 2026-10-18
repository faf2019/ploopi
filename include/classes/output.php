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
 * Gestion de l'affichage
 *
 * @package ploopi
 * @subpackage buffer
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class output
{

    /**
     * Affiche des informations lisibles pour une variable php (basé sur la fonction php print_r())
     *
     * @param mixed $var variable à afficher
     * @param boolean $return true si le contenu doit être retourné, false si le contenu doit être affiché (false par défaut)
     * @return mixed rien si $return = false, sinon les informations lisibles de la variable (html)
     */

    public static function print_r($var, $return = false)
    {
        $p = '<pre style="text-align:left; background-color:#fff; color:#000; padding:5px; border:1px solid #000;">'.str::htmlentities((is_array($var) || is_object($var) ? print_r($var, true) : $var)).'</pre>';
        if($return) return($p);
        else echo $p;
    }


    /**
     * Version spéciale de output::redirect qui nécessite que les paramètres soient déjà urlencodés (via la fonction urlencode())
     */
    public static function redirect_trusted($url, $urlencode = true, $internal = true, $refresh = 0)
    {
        self::redirect($url, $urlencode, $internal, $refresh, true);
    }

    /**
     * Redirige le script vers une url et termine le script courant
     *
     * @param string $url URL de redirection
     * @param boolean $urlencode true si l'URL doit être chiffrée (true par défaut)
     * @param boolean $internal true si la redirection est interne au site (true par défaut)
     * @param int $refresh durée en seconde avant la redirection (0 par défaut)
     */

    public static function redirect($url, $urlencode = true, $internal = true, $refresh = 0, $trusted = false)
    {
        if ($internal) $url = _PLOOPI_BASEPATH.'/'.$url;

        /**
         * Une URL sortant du site n'est suivie que si l'appelant l'a explicitement déclarée
         * sûre. Sans ce contrôle, tout appel prenant sa destination d'une donnée entrante
         * ouvre une redirection arbitraire : system::logout() redirige par exemple sur
         * $_SERVER['HTTP_REFERER'], que le visiteur choisit.
         */
        if (!$trusted && !self::isinternalurl($url))
        {
            error::syslog(LOG_INFO, "Redirection externe refusée : {$url}");
            $url = _PLOOPI_BASEPATH;
            $urlencode = false;
        }

        if ($urlencode) $url = $trusted ? crypt::urlencode_trusted($url) : crypt::urlencode($url);

        if (empty($refresh) || !is_numeric($refresh))
        {
            header("Location: {$url}");
            system::kill();
        }
        else header("Refresh: {$refresh}; url={$url}");
    }

    /**
     * Indique si une URL désigne le site courant.
     *
     * Une URL relative en fait partie. Une URL absolue ne doit porter que http ou https, et
     * viser l'hôte courant. Les formes "//exemple.test/x" sont bien détectées comme externes,
     * parse_url() y reconnaissant un hôte.
     *
     * @param string $url URL à examiner
     * @return boolean true si l'URL reste sur le site courant
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Ovensia
     */

    public static function isinternalurl($url)
    {
        $url = trim((string)$url);

        if ($url === '') return true;

        // Un caractère de contrôle n'a rien à faire dans une URL de redirection
        if (preg_match('@[\x00-\x1f\x7f]@', $url)) return false;

        /**
         * Les navigateurs assimilent "\" à "/" : "//exemple.test", "/\exemple.test" et
         * "\\exemple.test" mènent tous les trois dehors. On normalise avant de comparer,
         * un motif d'expression rationnelle sur ces caractères étant trop facile à écrire
         * de travers.
         */
        if (substr(str_replace('\\', '/', $url), 0, 2) === '//') return false;

        $arrUrl = @parse_url($url);

        if ($arrUrl === false) return false;

        // Une URL sans hôte est relative au site courant
        if (empty($arrUrl['host']))
        {
            // ... sauf si elle porte un schéma exotique (javascript:, data:, mailto:...)
            if (!empty($arrUrl['scheme'])) return false;

            return true;
        }

        if (!empty($arrUrl['scheme']) && !in_array(strtolower($arrUrl['scheme']), array('http', 'https'))) return false;

        // Une URL portant des identifiants ne sert qu'à masquer sa destination réelle
        if (isset($arrUrl['user']) || isset($arrUrl['pass'])) return false;

        $arrBase = @parse_url(_PLOOPI_BASEPATH);
        $strBaseHost = empty($arrBase['host']) ? (empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST']) : $arrBase['host'];

        $strHost = $arrUrl['host'].(empty($arrUrl['port']) ? '' : ':'.$arrUrl['port']);

        // L'hôte courant peut être connu avec ou sans port
        return strcasecmp($arrUrl['host'], preg_replace('@:\d+$@', '', $strBaseHost)) === 0
            || strcasecmp($strHost, $strBaseHost) === 0;
    }

    /**
     * Renvoie l'attribut d'erreur 404 dans les entêtes
     */

    public static function h404() { header("HTTP/1.0 404 Not Found"); }

    /**
     * Ecrit dans un fichier log
     *
     * @param string $data Données à écrire
     */

    public static function log($data) {
        file_put_contents(_PLOOPI_PATHDATA.'/ploopi.log', date('Y-m-d H:i:s').' : '.$data."\n",FILE_APPEND);
    }



}
