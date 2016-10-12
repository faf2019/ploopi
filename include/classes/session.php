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
 * Gestionnaire de sessions avec une base de données
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

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

class session
{
    private static $booCompress = true;

    private static $booUseDb = null;

    private static $booUseMc = null;

    private static $objDb = null;

    private static $objMc = null;

    private static $fpLock = null;

    /**
     * Tableau contenant les variables serialisées
     */
    private static $arrSv = null;

    private static function get_basepath() { return _PLOOPI_PATHDATA.'/session'; }

    public static function get_path() { return self::get_basepath().'/'.self::get_id(); }

    private static function compress($data) { return self::$booCompress ? @gzcompress($data, defined('_PLOOPI_SESSION_COMPRESSION') ? _PLOOPI_SESSION_COMPRESSION : -1) : $data; }

    private static function uncompress($data) { return self::$booCompress && $data != '' ? @gzuncompress($data) : $data; }

    public static function set_usemc($booUseMc) { self::$booUseMc = $booUseMc; }

    public static function get_usemc() {
        if (is_null(self::$booUseMc)) self::_initdb();
        return self::$booUseMc;
    }

    public static function get_mc() {
        if (is_null(self::$booUseMc)) self::_initdb();
        return self::$objMc;
    }

    public static function set_usedb($booUseDb) { self::$booUseDb = $booUseDb; }

    public static function get_usedb() {
        if (is_null(self::$booUseDb)) self::_initdb();
        return self::$booUseDb;
    }

    public static function set_db($objDb) { self::$objDb = $objDb; }

    public static function get_db() {
        if (is_null(self::$booUseDb)) self::_initdb();
        return self::$objDb;
    }

    public static function get_id() { return session_id(); }

    private static function _initdb()
    {
        switch(_PLOOPI_SESSION_HANDLER) {
            case 'db':
                self::set_usedb(true);
                if (_PLOOPI_DB_SERVER != _PLOOPI_SESSION_DB_SERVER || _PLOOPI_DB_DATABASE != _PLOOPI_SESSION_DB_DATABASE)
                {
                    self::$objDb = new db(_PLOOPI_SESSION_DB_SERVER, _PLOOPI_SESSION_DB_LOGIN, _PLOOPI_SESSION_DB_PASSWORD, _PLOOPI_SESSION_DB_DATABASE);
                    if(!self::$objDb->isconnected()) trigger_error(_PLOOPI_MSG_DBERROR, E_USER_ERROR);
                }
                else
                {
                    $db = db::get();
                    self::set_db($db);
                }
            break;

            case 'memcached':
                self::set_usemc(true);
                self::$objMc = new \Memcached();
                self::$objMc->addServer(_PLOOPI_MEMCACHED_SERVER, _PLOOPI_MEMCACHED_PORT);
            break;
        }
    }

    public static function open()
    {
        ini_set('session.gc_probability', 10);
        ini_set('session.gc_maxlifetime', _PLOOPI_SESSIONTIME);

        self::_initdb();

        return true;
    }

    public static function close() { return true; }

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
            self::$objDb->query("SELECT GET_LOCK('ploopi_lock_{$id}', 10)");
            return (self::$objDb->query("SELECT `data` FROM `ploopi_session` WHERE `id` = '".self::$objDb->addslashes($id)."'") && $arrRecord = self::$objDb->fetchrow()) ? self::uncompress($arrRecord['data']) : '';
        }
        elseif (self::$booUseMc)
        {
            $data = self::$objMc->get("session_{$id}");
            if ($data === false) return '';
            return $data;
        }
        else
        {
            fs::makedir(self::get_path());
            self::$fpLock = fopen(self::get_path().'.lock', "w");
            flock(self::$fpLock, LOCK_EX);
            return file_exists(self::get_path().'/data') ? self::uncompress(file_get_contents(self::get_path().'/data')) : '';
        }
    }

    /**
     * Ecriture de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     * @param string $data données de la session
     */

    public static function write($id, $data)
    {
        if (self::$booUseDb)
        {
            self::$objDb->query("REPLACE INTO `ploopi_session` VALUES ('".self::$objDb->addslashes($id)."', '".self::$objDb->addslashes(time())."', '".self::$objDb->addslashes(self::compress($data))."')");
            self::$objDb->query("SELECT RELEASE_LOCK('ploopi_lock_{$id}')");
        }
        elseif (self::$booUseMc)
        {
            self::$objMc->set("session_{$id}", $data, 0, _PLOOPI_SESSIONTIME);
        }
        else
        {
            fs::makedir(self::get_path());
            fwrite($resHandle = fopen(self::get_path().'/data', 'wb'), self::compress($data));
            fclose($resHandle);
            flock(self::$fpLock, LOCK_UN);
            fclose(self::$fpLock);
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
            self::$objDb->query("DELETE FROM `crypt::serializedvar` WHERE `id_session` = '".self::$objDb->addslashes($id)."'");
            self::$objDb->query("DELETE FROM `ploopi_session` WHERE `id` = '".self::$objDb->addslashes($id)."'");
        }
        elseif (self::$booUseMc)
        {
            self::$objMc->delete("session_{$id}");
        }
        else
        {
            fs::deletedir(self::get_path());
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
            self::$objDb->query("DELETE `crypt::serializedvar` FROM  `ploopi_session`, `crypt::serializedvar` WHERE `ploopi_session`.`access` < '".self::$objDb->addslashes((time() - $max))."' AND  `ploopi_session`.`id` =  `crypt::serializedvar`.`id_session`");
            // Delete session vars
            self::$objDb->query("DELETE `ploopi_session` FROM  `ploopi_session` WHERE `ploopi_session`.`access` < '".self::$objDb->addslashes((time() - $max))."'");
        }
        elseif (self::$booUseMc)
        {
        }
        else
        {
            $resFolder = opendir(self::get_basepath());

            $intDeletetime = time() - $max;

            while ($strIdSession = readdir($resFolder))
            {
                if (!in_array($strIdSession, array('.', '..')))
                {
                    $strSessionData = self::get_basepath().'/'.$strIdSession.'/data';
                    if (filemtime($strSessionData) < $intDeletetime)
                    {
                        fs::deletedir(self::get_basepath().'/'.$strIdSession);
                    }
                }
            }
        }

        return true;
    }


    /**
     * Réinitialise la session
     */

    public static function reset()
    {
        $ua_info = parse_user_agent();

        // Suppression des données de la session active
        // Regénération d'un ID
        session_regenerate_id(true);

        $_SESSION = array('ploopi' => array(
            'login' => '',
            'password' => '',
            'userid' => '',
            'workspaceid' => '',
            'webworkspaceid' => '',
            'adminlevel' => 0,

            'updateprofile' => false,

            'connected' => false,
            'loginerror' => false,
            'paramloaded' => false,
            'mode' => '',

            'remote_ip' => ip::get(),
            'remote_browser' => $ua_info['browser'].' '.$ua_info['version'],
            'remote_system' => $ua_info['platform'],

            'host' => $_SERVER['HTTP_HOST'],
            'scriptname' => basename($_SERVER['PHP_SELF']),
            'env' => '',

            'workspaces' => array(),
            'modules' => array(),
            'moduletypes' => array(),
            'allworkspaces' => '',

            'hosts' =>
                array(
                    'frontoffice' => array(),
                    'backoffice' => array()
                ),

            'currentrequesttime' => time(),
            'lastrequesttime' => time(),

            'moduleid' => '',
            'mainmenu' => '',
            'action' => 'public',
            'moduletype' => '',
            'moduletypeid' => '',
            'modulelabel' => '',

            'backoffice' =>
                array(
                    'moduleid' => '',
                    'workspaceid' => ''
                ),

            'frontoffice' =>
                array(
                    'moduleid' => '',
                    'workspaceid' => ''
                ),

            'template_name' =>  '',
            'template_path' =>  '',

            'uri'   =>  '',

            'newtickets'    => 0,

            'fingerprint'   => _PLOOPI_FINGERPRINT,

            'timezone'      => timezone_name_get(date_timezone_get($objDatetime =  date_create())),

            'msgcode'       => 0,
            'errorcode'     => 0,
            'token'         => '',
            'tokens'        => array()
        ));
    }

    /**
     * Met à jour les données et vérifie la validité de la session
     */

    public static function update()
    {
        global $session;

        $scriptname = basename($_SERVER['PHP_SELF']);

        if (!isset($_SESSION['ploopi']['fingerprint']) || $_SESSION['ploopi']['fingerprint'] != _PLOOPI_FINGERPRINT) // problème d'empreinte, session invalide
        {
            error::syslog(LOG_INFO, 'Empreinte invalide');
            system::logout(_PLOOPI_ERROR_SESSIONINVALID);
        }

        $_SESSION['ploopi']['currentrequesttime'] = time();
        if (empty($_SESSION['ploopi']['lastrequesttime'])) $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];

        $diff = $_SESSION['ploopi']['currentrequesttime'] - $_SESSION['ploopi']['lastrequesttime'];

        // Si la durée de sessoin est expirée et que l'on est connecté, on vide la session et on retourne à la page de login
        if ($diff > _PLOOPI_SESSIONTIME && _PLOOPI_SESSIONTIME != '' && _PLOOPI_SESSIONTIME != 0 && !empty($_SESSION['ploopi']['connected']))
        {
            error::syslog(LOG_INFO, 'Session expirée');
            system::logout(_PLOOPI_ERROR_SESSIONEXPIRE);
        }
        else // Sinon on met simplement à jour la date/heure de la dernière requête + IP
        {
            $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];
            $_SESSION['ploopi']['remote_ip'] = ip::get();
        }

        $_SESSION['ploopi']['scriptname'] = $scriptname;
    }



    /**
     * Vérifie si un drapeau a été posé et met à jour le drapeau
     *
     * @param string $var type de drapeau
     * @param string $value valeur à tester
     * @return bool
     */

    public static function setflag($var, $value)
    {
        if (!isset($_SESSION['flags'][$var])) $_SESSION['flags'][$var] = array();;

        if (!isset($_SESSION['flags'][$var][$value]))
        {
            $_SESSION['flags'][$var][$value] = 1;
            return(true);
        }
        else return(false);
    }


    /**
     * Lit une variable de module en session
     *
     * @param string $strVarName nom de la variable à lire
     * @param int $intModuleId identifiant du module (optionnel, le module courant si non défini)
     * @param integer $intWorkspaceId Identifiant de l'espace de travail (optionnel, l'espace courant si non défini)
     * @return mixed valeur de la variable
     */
    public static function getvar($strVarName, $intModuleId = null, $intWorkspaceId = null)
    {
        if (is_null($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
        if (is_null($intWorkspaceId)) $intWorkspaceId = $_SESSION['ploopi']['workspaceid'];

        if (!empty($_SESSION['ploopi']['modules'][$intModuleId]['moduletype']))
        {
            $strModuleType = $_SESSION['ploopi']['modules'][$intModuleId]['moduletype'];

            return isset($_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName]) ? $_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName] : null;
        }

        return null;
    }

    /**
     * Enregistre une variable de module en session
     *
     * @param string $strVarName nom de la variable
     * @param mixed $mixVar contenu de la variable
     * @param integer $intModuleId Identifiant du module (optionnel, le module courant si non défini)
     * @param integer $intWorkspaceId Identifiant de l'espace de travail (optionnel, l'espace courant si non défini)
     */
    public static function setvar($strVarName, $mixVar = null, $intModuleId = null, $intWorkspaceId = null)
    {
        if (is_null($intModuleId)) $intModuleId = $_SESSION['ploopi']['moduleid'];
        if (is_null($intWorkspaceId)) $intWorkspaceId = $_SESSION['ploopi']['workspaceid'];

        if (!empty($_SESSION['ploopi']['modules'][$intModuleId]['moduletype']))
        {
            $strModuleType = $_SESSION['ploopi']['modules'][$intModuleId]['moduletype'];

            $_SESSION['ploopi'][$strModuleType][$intModuleId][$intWorkspaceId][$strVarName] = $mixVar;
        }
    }
}
/*



    $allSlabs = $memcache->getExtendedStats('slabs');
    foreach($allSlabs as $server => $slabs) {
        foreach($slabs AS $slabId => $slabMeta) {
           if (!is_numeric($slabId)) {
                continue;
           }
           $cdump = $memcache->getExtendedStats('cachedump',(int)$slabId);
            foreach($cdump AS $keys => $arrVal) {
                if (!is_array($arrVal)) continue;
                foreach($arrVal AS $k => $v) {
                    echo $k .' - '.date('H:i d.m.Y',$v[1]).'<br />';
                }
           }
        }
    }
*/
