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
 * Gestionnaire de sessions.
 * Remplace le gestionnaire de session par défaut.
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class session
{
    /**
     * Compression active
     *
     * @var boolean
     */
    private static $booCompress = true;

    /**
     * Gestion via base de données
     *
     * @var boolean
     */
    private static $booUseDb = null;

    /**
     * Gestion via Memcached
     *
     * @var boolean
     */
    private static $booUseMc = null;

    /**
     * Objet db
     *
     * @var db
     */
    private static $objDb = null;

    /**
     * Objet Memcached
     *
     * @var \Memcached
     */
    private static $objMc = null;

    /**
     * Lock fichier
     *
     * @var resource
     */
    private static $fpLock = null;


    /**
     * Retourne le chemin de stockage des fichiers
     *
     * @return string chemin
     */
    private static function get_basepath() { return _PLOOPI_PATHDATA.'/session'; }

    /**
     * Retourne le chemin et le nom du fichier de la session
     *
     * @return string chemin et nom du fichier de la session
     */
    public static function get_path() { return self::get_basepath().'/'.self::get_id(); }

    /**
     * Compresse les données
     *
     * @param string $data données
     *
     * @return string données compressées
     */
    private static function compress($data) { return self::$booCompress ? @gzcompress($data, defined('_PLOOPI_SESSION_COMPRESSION') ? _PLOOPI_SESSION_COMPRESSION : -1) : $data; }

    /**
     * Décompresse les données
     *
     * @param string $data données compressées
     *
     * @return données décompressées
     */
    private static function uncompress($data) { return self::$booCompress && $data != '' ? @gzuncompress($data) : $data; }

    /**
     * Indique au gestionnaire d'utiliser Memcached
     *
     * @param boolean $booUseMc true pour utiliser Memcached
     */
    public static function set_usemc($booUseMc) { self::$booUseMc = $booUseMc; }

    /**
     * Indique si le gestionnaire utilise Memcached
     *
     * @return boolean true si Memcached
     */
    public static function get_usemc() {
        if (is_null(self::$booUseMc)) self::_initdb();
        return self::$booUseMc;
    }

    /**
     * Retourne le connecteur Memcached
     *
     * @return \Memcached connecteur Memcached utilisé
     */
    public static function get_mc() {
        if (is_null(self::$booUseMc)) self::_initdb();
        return self::$objMc;
    }

    /**
     * Indique au gestionnaire d'utiliser db
     *
     * @param boolean $booUseDb true pour utiliser db
     */
    public static function set_usedb($booUseDb) { self::$booUseDb = $booUseDb; }

    /**
     * Indique si le gestionnaire utilise db
     *
     * @return boolean true si db
     */
    public static function get_usedb() {
        if (is_null(self::$booUseDb)) self::_initdb();
        return self::$booUseDb;
    }

    /**
     * Fournit le connecteur db
     *
     * @param db $objDb connecteur db à utiliser
     */

    public static function set_db($objDb) { self::$objDb = $objDb; }

    /**
     * Retourne le connecteur db
     *
     * @return db connecteur db utilisé
     */
    public static function get_db() {
        if (is_null(self::$booUseDb)) self::_initdb();
        return self::$objDb;
    }

    /**
     * Retourne l'identifiant de session
     *
     * @return string identifiant de session
     */
    public static function get_id() { return session_id(); }

    /**
     * Initialise le connecteur
     */
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


    /**
     * Ouvre le gestionnaire de session
     *
     * @return boolean true
     */
    public static function open()
    {
        self::_initdb();

        return true;
    }

    /**
     * Ferme le gestionnaire de session
     *
     * @return boolean true
     */
    public static function close() { return true; }

    /**
     * Lecture de la session
     *
     * @param string $id identifiant de la session
     * @return array contenu de la session
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
     * Ecriture de la session
     *
     * @param string $id identifiant de la session
     * @param string $data données de la session
     *
     * @return boolean true
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
            self::$objMc->set("session_{$id}", $data, _PLOOPI_SESSIONTIME);
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
     * Suppression de la session
     *
     * @param string $id identifiant de la session
     *
     * @return boolean true
     */

    public static function destroy($id)
    {
        if (self::$booUseDb)
        {
            self::$objDb->query("DELETE FROM `ploopi_serializedvar` WHERE `id_session` = '".self::$objDb->addslashes($id)."'");
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
     * Suppression des sessions périmées (Garbage collector)
     *
     * @param int $max durée d'une session en secondes
     *
     * @return boolean true
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
        $ua_info = [];

        try {
            $ua_info = parse_user_agent();
        }
        catch(Exception $e) { }

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
            'remote_browser' => empty($ua_info) ? '' : $ua_info['browser'].' '.$ua_info['version'],
            'remote_system' => empty($ua_info) ? '' : $ua_info['platform'],

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
     *
     * @return bool
     */

    public static function setflag($var, $value)
    {
        if (!isset($_SESSION['flags'][$var])) $_SESSION['flags'][$var] = array();;

        if (!isset($_SESSION['flags'][$var][$value]))
        {
            $_SESSION['flags'][$var][$value] = 1;
            return true;
        }
        else return false;
    }


    /**
     * Lit une variable de module en session
     *
     * @param string $strVarName nom de la variable à lire
     * @param int $intModuleId identifiant du module (optionnel, le module courant si non défini)
     * @param integer $intWorkspaceId Identifiant de l'espace de travail (optionnel, l'espace courant si non défini)
     *
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
