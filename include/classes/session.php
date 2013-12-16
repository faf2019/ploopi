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
 * Gestionnaire de sessions avec une base de données
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion des fonctions filesystem
 */
include_once './include/functions/filesystem.php';

/**
 * Inclusion de la classe permettant de gérer les variables sérialisées
 */
include_once './include/classes/serializedvar.php';

/**
 * Classe permettant de remplacer le gestionnaire de session par défaut.
 * Les sessions sont stockées dans la base de données.
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopi_session
{
    private static $booCompress = true;

    private static $booUseDb = false;

    private static $objDb = null;

    /**
     * Tableau contenant les variables serialisées
     */
    private static $arrSv = null;

    private static function get_basepath() { return _PLOOPI_PATHDATA._PLOOPI_SEP.'session'; }

    public static function get_path() { return self::get_basepath()._PLOOPI_SEP.self::get_id(); }

    private static function compress(&$data) { return self::$booCompress ? @gzcompress($data, defined('_PLOOPI_SESSION_COMPRESSION') ? _PLOOPI_SESSION_COMPRESSION : -1) : $data; }

    private static function uncompress(&$data) { return self::$booCompress && $data != '' ? @gzuncompress($data) : $data; }

    public static function set_usedb($booUseDb) { self::$booUseDb = $booUseDb; }

    public static function get_usedb() { return self::$booUseDb; }

    public static function set_db($objDb) { self::$objDb = $objDb; }

    public static function get_db() { return self::$objDb; }

    public static function get_id() { return session_id(); }

    public static function open()
    {
        ini_set('session.gc_probability', 10);
        ini_set('session.gc_maxlifetime', _PLOOPI_SESSIONTIME);

        if (defined('_PLOOPI_USE_DBSESSION') && _PLOOPI_USE_DBSESSION)
        {
            self::set_usedb(true);
            if (_PLOOPI_DB_SERVER != _PLOOPI_SESSION_DB_SERVER || _PLOOPI_DB_DATABASE != _PLOOPI_SESSION_DB_DATABASE)
            {
                self::$objDb = new ploopi_db(_PLOOPI_SESSION_DB_SERVER, _PLOOPI_SESSION_DB_LOGIN, _PLOOPI_SESSION_DB_PASSWORD, _PLOOPI_SESSION_DB_DATABASE);
                if(!self::$objDb->isconnected()) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);
            }
            else
            {
                global $db;
                self::set_db($db);
            }
        }
    }

    public static function close() { }

    /**
     * Chargement de la session depuis la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public static function read($id)
    {
        if (self::$booUseDb)
        {
            return (self::$objDb->query("SELECT `data` FROM `ploopi_session` WHERE `id` = '".self::$objDb->addslashes($id)."'") && $arrRecord = self::$objDb->fetchrow()) ? self::uncompress($arrRecord['data']) : '';
        }
        else
        {
            return file_exists(self::get_path()._PLOOPI_SEP.'data') ? self::uncompress(file_get_contents(self::get_path()._PLOOPI_SEP.'data')) : false;
        }
    }

    /**
     * Ecriture de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     * @param string $data données de la session
     */

    public static function write($id, &$data)
    {
        if (self::$booUseDb)
        {
            self::$objDb->query("REPLACE INTO `ploopi_session` VALUES ('".self::$objDb->addslashes($id)."', '".self::$objDb->addslashes(time())."', '".self::$objDb->addslashes(self::compress($data))."')");
        }
        else
        {
            ploopi_makedir(self::get_path());
            fwrite($resHandle = fopen(self::get_path()._PLOOPI_SEP.'data', 'wb'), self::compress($data));
            fclose($resHandle);
        }

        return true;
    }

    /**
     * Suppression de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public static function destroy($id)
    {
        if (self::$booUseDb)
        {
            self::$objDb->query("DELETE FROM `ploopi_serializedvar` WHERE `id_session` = '".self::$objDb->addslashes($id)."'");
            self::$objDb->query("DELETE FROM `ploopi_session` WHERE `id` = '".self::$objDb->addslashes($id)."'");
        }
        else
        {
            ploopi_deletedir(self::get_path());
        }

        return true;
    }

    /**
     * Suppression des sessions périmées (Garbage collector).
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param int $max durée d'une session en secondes
     */

    public static function gc($max)
    {
        if (self::$booUseDb)
        {
            // Delete serialized vars
            self::$objDb->query("DELETE `ploopi_serializedvar` FROM  `ploopi_session`, `ploopi_serializedvar` WHERE `ploopi_session`.`access` < '".self::$objDb->addslashes((time() - $max))."' AND  `ploopi_session`.`id` =  `ploopi_serializedvar`.`id_session`");
            // Delete session vars
            self::$objDb->query("DELETE `ploopi_session` FROM  `ploopi_session` WHERE `ploopi_session`.`access` < '".self::$objDb->addslashes((time() - $max))."'");
        }
        else
        {
            $resFolder = opendir(self::get_basepath());

            $intDeletetime = time() - $max;

            while ($strIdSession = readdir($resFolder))
            {
                if (!in_array($strIdSession, array('.', '..')))
                {
                    $strSessionData = self::get_basepath()._PLOOPI_SEP.$strIdSession._PLOOPI_SEP.'data';
                    if (filemtime($strSessionData) < $intDeletetime)
                    {
                        ploopi_deletedir(self::get_basepath()._PLOOPI_SEP.$strIdSession);
                    }
                }
            }
        }

        return true;
    }
}
