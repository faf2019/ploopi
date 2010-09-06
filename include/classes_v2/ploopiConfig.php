<?php
/*
    Copyright (c) 2009-2010 Ovensia
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
 * Gestion des paramètres de Ploopi.
 *
 * @package ploopi
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès aux paramètres de Ploopi.
 *
 * @package ploopi
 * @subpackage config
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopiConfig
{
    private static $arrConfig = null;


    /**
     * Méthode d'accès au paramètres de configuration de l'application
     *
     * @param string $strName nom du paramètre
     * @return mixed valeur du paramètre
     */
    public static function get($strName)
    {
        if (is_null(self::$arrConfig)) self::$arrConfig = array(
            '_PLOOPI_ERROR_REPORTING' => _PLOOPI_ERROR_REPORTING,
            '_PLOOPI_ERROR_DISPLAY' => _PLOOPI_DISPLAY_ERRORS,
            '_PLOOPI_ERROR_LOGFILE' => _PLOOPI_PATHDATA.'/error.log'
        );

        if (isset(self::$arrConfig[$strName])) return self::$arrConfig[$strName];
        else throw new ploopiException("Config variable &laquo; {$strName} &raquo; unknown");;
    }

    /**
     * Retourne le chemin web de l'application
     * Ex: /projets/ploopi
     *
     * @return string chemin web de l'application
     */
    public static function getSelfPath()
    {
        return php_sapi_name() == 'cli' ? '' : rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    }

    /**
     * Retourne l'url de l'application
     * Ex: http://serveur/projets/ploopi
     *
     * @return string url de l'application
     */
    public static function getBasePath()
    {
        return php_sapi_name() == 'cli' ? '' : ((!empty($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_SSL_REQUEST']) && ($_SERVER['HTTP_X_SSL_REQUEST'] == 1 || $_SERVER['HTTP_X_SSL_REQUEST'] == true || $_SERVER['HTTP_X_SSL_REQUEST'] == 'on'))) ? 'https://' : 'http://').((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80' && empty($_SERVER['HTTP_HOST'])) ? ":{$_SERVER['SERVER_PORT']}" : '').rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    }

    /**
     * Retourne l'empreinte (unique) de l'application
     *
     * @return string empreinte MD5 de l'application
     */
    public static function getFingerPrint()
    {
        return md5(self::getBasePath().'/'.self::get('_PLOOPI_DB_SERVER').'/'.self::get('_PLOOPI_DB_DATABASE'));
    }
}

