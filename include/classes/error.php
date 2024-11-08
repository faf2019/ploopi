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
 * Gestion des erreurs
 *
 * @package ploopi
 * @subpackage error
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class error
{
    /**
     * Messages d'erreur
     *
     * @var string
     */
    public static $msg = '';

    /**
     * Nombre d'erreurs
     *
     * @var integer
     */
    public static $nb = 0;

    /**
     * Niveau d'erreur
     *
     * @var integer
     */
    public static $level = 0;

    /**
     * Tableau des types d'erreur
     *
     * @var array
     */
    public static $errortype = array(
        E_ERROR          => 'Error',
        E_WARNING        => 'Warning',
        E_PARSE          => 'Parse Error',
        E_NOTICE         => 'Notice',
        E_CORE_ERROR     => 'Core Error',
        E_CORE_WARNING   => 'Core Warning',
        E_COMPILE_ERROR  => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR     => 'User Error',
        E_USER_WARNING   => 'User Warning',
        E_USER_NOTICE    => 'User Notice',
        E_STRICT         => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED         => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    );

    /**
     * Tableau des niveaux d'erreur
     *
     * @var array
     */
    public static $errorlevel = array(
        0 => 'OK',
        1 => 'WARNING',
        2 => 'CRITICAL ERROR'
    );


    /**
     * Gestionnaire d'erreur de Ploopi.
     *
     * @param int $errno le niveau d'erreur
     * @param string $errstr le message d'erreur
     * @param string $errfile le nom du fichier dans lequel l'erreur a été identifiée
     * @param int $errline le numéro de ligne à laquelle l'erreur a été identifiée
     * @param array $vars tableau avec toutes les variables qui existaient lorsque l'erreur a été déclenchée
     *
     * @see _PLOOPI_DISPLAY_ERRORS
     * @see _PLOOPI_ERROR_REPORTING
     */

    public static function handler($errno, $errstr, $errfile, $errline = 0, $vars = [])
    {
        // Fonctions appelées avec @ (PHP 8: 4437)
        if (in_array(error_reporting(), [4437, 0])) return false;

        // translate error_level into "readable" array
        $bit = _PLOOPI_ERROR_REPORTING;
        $res = array();

        while ($bit > 0)
        {
           for($i = 0, $n = 0; $i <= $bit; $i = 1 * pow(2, $n), $n++) {
               $end = $i;
           }
           $res[] = $end;
           $bit = $bit - $end;
        }

        // if error in error reporting levels
        if (in_array($errno,$res))
        {
            self::$nb++;

            if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR) self::$level = 2;
            else if (($errno == E_WARNING || $errno == E_USER_WARNING || $errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_DEPRECATED || $errno == E_USER_DEPRECATED) && self::$level < 2) self::$level = 1;

            if (self::$msg == '') self::$msg  = (php_sapi_name() == 'cli' ? '' : "[{$_SERVER['HTTP_HOST']}] ")."le ".date("d-m-Y H:i:s (T)")."\n\nVersion PHP : ".PHP_VERSION."\nOS : ".php_uname()."\nPloopi : "._PLOOPI_VERSION.' '._PLOOPI_REVISION."\n\n";

            $errstr = strip_tags($errstr);

            self::$msg .= "\nType d'erreur : ".self::$errortype[$errno]."\nMessage : $errstr\n";

            $arrTrace = debug_backtrace();
            if (_PLOOPI_DISPLAY_ERRORS) $strErrorStack = '';

            // parse debug trace
            $s = sizeof($arrTrace);
            for ($key = $s-1; $key>=0; $key--)
            {
                if (!empty($arrTrace[$key]['file']) && !empty($arrTrace[$key]['line']))
                {
                    self::$msg .= sprintf("Fichier : %s \nLigne : %s\n", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                    if (_PLOOPI_DISPLAY_ERRORS)
                    {
                        if (php_sapi_name() != 'cli') $strErrorStack .= sprintf("<div style=\"margin-left:10px;\">at <strong>%s</strong>  <em>line %d</em></div>", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                        else $strErrorStack .= sprintf("at %s line %d\n", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                    }
                }
            }

            if (_PLOOPI_DISPLAY_ERRORS)
            {
                // display message
                if (php_sapi_name() != 'cli')  // Affichage standard, sortie HTML
                {
                    header("Content-Disposition: inline");
                    header("Content-Type: text/html");

                    echo "<div class=\"ploopi_error\">
                            <div>
                            <strong>".self::$errortype[$errno]."</strong>
                            <br /><span>".htmlentities($errstr, ENT_COMPAT, 'ISO-8859-1')."</span>
                            </div>
                            {$strErrorStack}
                        </div>";

                }
                else // Affichage cli, sortie texte brut
                {
                    fwrite(STDOUT, "=== ".self::$errortype[$errno]." ==================================\r\n{$errstr}\r\n{$strErrorStack}\r\n");
                }

                // critical error
                if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR) system::kill();
            }
            else
            {
                // critical error
                if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR)
                {
                    buffer::clean();
                    system::kill('<html><body><div align="center">Une erreur est survenue sur le site.<br />Contactez l\'administrateur.</div></body></html>');
                }
            }
        }
    }

    /**
     * Active le gestionnaire d'erreur interne de Ploopi
     */
    public static function set_handler()
    {
        set_error_handler(array(__NAMESPACE__.'\\error', 'handler'));
        error_reporting(E_ALL);
    }

    /**
     * Désactive le gestionnaire d'erreur interne de Ploopi
     */
    public static function unset_handler()
    {
        restore_error_handler();
        error_reporting(0);
    }

    /**
     * Ecrit un message dans le syslog avec le contexte (utilisateur, espace, module, ip, sid, host, uri)
     * @param int $priority
     * @param string $message
     */
    public static function syslog($priority, $message)
    {
        $arrIp = ip::get();

        $u = isset($_SESSION['ploopi']['userid']) ? $_SESSION['ploopi']['userid'] : '';
        $w = isset($_SESSION['ploopi']['workspaceid']) ? $_SESSION['ploopi']['workspaceid'] : '';
        $m = isset($_SESSION['ploopi']['moduleid']) ? $_SESSION['ploopi']['moduleid'] : '';
        $ip = empty($arrIp) ? '' : current($arrIp);
        $sid = session_id();
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $host = isset($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST'] : '';
        $get = isset($_GET) ? urldecode(http_build_query($_GET)) : '';

        syslog($priority, "ploopi:{$message} - u:{$u} w:{$w} m:{$m} ip:{$ip} sid:{$sid} host:{$host} uri:{$uri} get:{$get}");
    }

}
