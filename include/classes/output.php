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

namespace ploopi;

use ploopi;

/**
 * Gestion de l'affichage
 *
 * @package ploopi
 * @subpackage buffer
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
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
     *
     * @see output::redirect
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
        if ($urlencode) $url = $trusted ? crypt::urlencode_trusted($url) : crypt::urlencode($url);

        if (empty($refresh) || !is_numeric($refresh))
        {
            header("Location: {$url}");
            die();
        }
        else header("Refresh: {$refresh}; url={$url}");
    }

    /**
     * Renvoie une erreur 404 dans les entêtes
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see header
     */

    public static function h404() { header("HTTP/1.0 404 Not Found"); }

}
