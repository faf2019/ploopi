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
 * Gestion des erreurs
 * 
 * @package ploopi
 * @subpackage error
 * @copyright Netlor, Ovensia
 * @license GPL
 */

global $ploopi_errors_msg;
global $ploopi_errors_nb;
global $ploopi_errors_level;
global $ploopi_errortype;
global $ploopi_errorlevel;

$ploopi_errors_msg = '';
$ploopi_errors_nb = 0;
$ploopi_errors_level = 0;

$ploopi_errortype = array (
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
                            E_RECOVERABLE_ERROR => 'Recoverable Error'
                            );

$ploopi_errorlevel = array (
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
                            
function ploopi_errorhandler($errno, $errstr, $errfile, $errline, $vars)
{
    global $ploopi_errors_msg;
    global $ploopi_errors_nb;
    global $ploopi_errors_level;

    global $ploopi_errortype;
    global $ploopi_errorlevel;
    
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
        $ploopi_errors_nb++;

        if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR) $ploopi_errors_level = 2;
        else if (($errno == E_WARNING || $errno == E_NOTICE || $errno == E_USER_NOTICE) && $ploopi_errors_level < 2) $ploopi_errors_level = 1;

        if ($ploopi_errors_msg == '') $ploopi_errors_msg  = "[{$_SERVER['HTTP_HOST']}] le ".date("d-m-Y H:i:s (T)")."\n\nVersion PHP : ".PHP_VERSION."\nOS : ".PHP_OS."\n\n";

        $ploopi_errors_msg .= "\nType d'erreur : {$ploopi_errortype[$errno]}\nMessage : $errstr\n";
        
        $arrTrace = debug_backtrace();
        if (_PLOOPI_DISPLAY_ERRORS) $strErrorStack = '';
        
        // parse debug trace
        $s = sizeof($arrTrace);
        for ($key = $s-1; $key>=0; $key--)
        {
            if (!empty($arrTrace[$key]['file']) && !empty($arrTrace[$key]['line'])) 
            {
                $ploopi_errors_msg .= sprintf("Fichier : %s \nLigne : %s\n", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
                if (_PLOOPI_DISPLAY_ERRORS) $strErrorStack .= sprintf("<div style=\"margin-left:10px;\">at <strong>%s</strong>  <em>line %d</em></div>", $arrTrace[$key]['file'],  $arrTrace[$key]['line']);
            }
        }
        
            
        if (_PLOOPI_DISPLAY_ERRORS)
        {
            // display message
            echo    "
                    <div class=\"ploopi_error\">
                        <div>
                        <strong>{$ploopi_errortype[$errno]}</strong>
                        <br /><span>{$errstr}</span>
                        </div>
                        {$strErrorStack}
                    </div>
                    ";
                    
            // critical error
            if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR) ploopi_die();
        }
        else
        {
            // critical error
            if ($errno == E_ERROR || $errno == E_PARSE || $errno == E_USER_ERROR)
            {
                while (@ob_end_clean());
                echo '<html><body><div align="center">Une erreur est survenue sur le site.<br />Contactez l\'administrateur.</div></body></html>';
                ploopi_die();
            }
        }
    }
}
?>
