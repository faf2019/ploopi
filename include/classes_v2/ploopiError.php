<?php
/*
    Copyright (c) 2010 Ovensia
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
 * Gestion d'erreur
 */
class ploopiError
{
    const _NOWORKSPACEDEFINED           = 1;
    const _LOGINERROR                   = 2;
    const _LOGINEXPIRE                  = 3;
    const _SESSIONEXPIRE                = 4;
    const _SESSIONINVALID               = 5;
    const _LOSTPASSWORD_UNKNOWN         = 11;
    const _LOSTPASSWORD_INVALID         = 12;
    const _LOSTPASSWORD_MANYRESPONSES   = 13;

    protected static $arrErrorType =
        array(
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

    protected static $arrErrorLevel =
        array(
            0 => 'OK',
            1 => 'WARNING',
            2 => 'CRITICAL ERROR'
        );

    protected static $strErrorsMsg = '';
    protected static $intErrorNb = 0;
    protected static $intErrorLevel = 0;

    private static $objErrorLog;

    /**
     * Fonction permettant de générer une exception sur une erreur (uniquement si _PLOOPI_ERROR_TO_EXCEPTION = true)
     *
     * @param int $intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param string $strFile nom du fichier d'origine
     * @param int $intLine numéro de la ligne d'origine
     * @param array $arrContext variables présentes au moment de l'erreur
     */
    public static function toException($intCode, $strMsg, $strFile, $intLine, $arrContext)
    {
        // L'erreur doit être traitée ?
        if (self::reportable($intCode)) throw new ploopiException($strMsg, $intCode, $strFile, $intLine, $arrContext, true);
    }


    /**
     * Handler permettant de gérer les erreurs (uniquement si _PLOOPI_ERROR_TO_EXCEPTION = false)
     *
     * @param int $intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param string $strFile nom du fichier d'origine
     * @param int $intLine numéro de la ligne d'origine
     * @param array $arrContext variables présentes au moment de l'erreur
     */
    public static function handler($intCode, $strMsg, $strFile, $intLine, $arrContext)
    {
        // L'erreur doit être traitée ?
        if (self::reportable($intCode))
        {
            // Récupération du trace
            $arrTrace = debug_backtrace();

            self::writeLog($intCode, $strMsg, $arrTrace);

            self::show($intCode, $strMsg, $arrTrace);

            // Kill si erreur critique
            if ($intCode == E_ERROR || $intCode == E_PARSE || $intCode == E_USER_ERROR) ploopiKernel::kill();
        }
    }

    /**
     * Détermine si une erreur doit être affichée en fonction du paramètre de config : _PLOOPI_ERROR_REPORTING
     *
     * @param unknown_type $intCode
     * @return unknown
     */
    private static function reportable($intCode)
    {
        // error_reporting() != 0 => permet d'éviter les erreurs "prévues" (fonctions précédées de "@")
        if (error_reporting() == 0) return false;

        // Transformation du niveau d'erreur en tableau interprétable
        $intErrorReporting = ploopiConfig::get('_PLOOPI_ERROR_REPORTING');
        $arrErrorLevels = array();

        while ($intErrorReporting > 0)
        {
           for($intI = 0, $intN = 0; $intI <= $intErrorReporting; $intI = 1 * pow(2, $intN), $intN++) $intEnd = $intI;

           $arrErrorLevels[] = $intEnd;
           $intErrorReporting = $intErrorReporting - $intEnd;
        }

        return in_array($intCode, $arrErrorLevels);
    }

    /**
     * Affiche un erreur (HTML)
     *
     * @param integer $intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param array $arrTrace historique de l'erreur
     */
    public static function show($intCode, $strMsg, $arrTrace)
    {
        // Compteur global du nombre d'erreurs
        self::$intErrorNb++;

        // Niveau d'erreur global rencontré dans le script
        self::$intErrorLevel = ($intCode == E_ERROR || $intCode == E_PARSE || $intCode == E_USER_ERROR) ? max(self::$intErrorLevel, 2) : max(self::$intErrorLevel, 1);

        //else if (($intCode->code == E_WARNING || $intCode->code == E_NOTICE || $intCode->code == E_USER_NOTICE) && self::$intErrorLevel < 2) self::$intErrorLevel = max(self::$intErrorLevel, 1);


        if (ploopiConfig::get('_PLOOPI_ERROR_DISPLAY'))
        {
            $strMessage = '';

            foreach($arrTrace as $intKey => $arrTraceInfo)
            {
                if (!empty($arrTraceInfo['file']) && !empty($arrTraceInfo['line']))
                {
                    if ($intKey == 0)
                    {
                        $arrTraceInfo['origin'] = sprintf(" with %s",
                            isset($arrTraceInfo['args'][1]) ? $arrTraceInfo['args'][1] : ''
                        );
                    }
                    else
                    {
                        $arrTraceInfo['origin'] = isset($arrTraceInfo['function']) ? sprintf(" with %s%s%s()",
                            isset($arrTraceInfo['class']) ? $arrTraceInfo['class'] : '',
                            isset($arrTraceInfo['type']) ? $arrTraceInfo['type'] : '',
                            $arrTraceInfo['function']
                        ) : '';
                    }

                    if (php_sapi_name() != 'cli') $strMessage .= sprintf("<div style=\"margin-left:10px;\">at <strong>%s</strong>  <em>line %d</em>%s</div>", $arrTraceInfo['file'],  $arrTraceInfo['line'], isset($arrTraceInfo['origin']) ? $arrTraceInfo['origin'] : '');
                    else $strMessage .= $arrTraceInfo['message'];
                }
            }


            // display message
            if (php_sapi_name() != 'cli')  // Affichage standard, sortie HTML
            {
                $strMsg = ploopiString::getInstance($strMsg)->nl2br()->getString();

                echo "
                    <div style=\"background-color:#ffff60; border:1px dotted #a60000; color:#a60000; padding:4px 10px; margin:10px; font-family:Courier, monospace; \">
                        <div>
                        <strong>".self::$arrErrorType[$intCode]."</strong> - <span>{$strMsg}</span>
                        </div>
                        {$strMessage}
                    </div>
                ";
            }
            else // Affichage cli, sortie texte brut
            {
                echo "=== {self::{$arrErrorType[$intCode]}} - ".strip_tags($strMsg)."\r{$strMessage}";
            }
        }
    }

    /**
     * Ecriture dans le fichier de log
     *
     * @param integer $intCode code de l'erreur
     * @param string $strMsg message d'erreur
     * @param array $arrTrace historique de l'erreur
     */
    public static function writeLog($intCode, $strMsg, $arrTrace)
    {
        if (ploopiConfig::get('_PLOOPI_ERROR_LOGFILE') != '')
        {
                $strMessage = self::$arrErrorType[$intCode]." - ".strip_tags($strMsg)."\n";

                //print_r($arrTrace);
                foreach($arrTrace as $intKey => $arrTraceInfo)
                {
                    if (!empty($arrTraceInfo['file']) && !empty($arrTraceInfo['line']))
                    {
                        if ($intKey == 0)
                        {
                            $arrTraceInfo['origin'] = sprintf(" with %s",
                                isset($arrTraceInfo['args'][1]) ? $arrTraceInfo['args'][1] : ''
                            );
                        }
                        else
                        {
                            $arrTraceInfo['origin'] = isset($arrTraceInfo['function']) ? sprintf(" with %s%s%s()",
                                isset($arrTraceInfo['class']) ? $arrTraceInfo['class'] : '',
                                isset($arrTraceInfo['type']) ? $arrTraceInfo['type'] : '',
                                $arrTraceInfo['function']
                            ) : '';
                        }

                        $arrTraceInfo['message'] = sprintf("at %s line %d%s", $arrTraceInfo['file'],  $arrTraceInfo['line'], isset($arrTraceInfo['origin']) ? $arrTraceInfo['origin'] : '');
                        $strMessage .= $arrTraceInfo['message']."\n";
                    }
                }


            if (!(self::$objErrorLog instanceof ploopiLogFile)) self::$objErrorLog = new ploopiLogFile(ploopiConfig::get('_PLOOPI_ERROR_LOGFILE'));
            self::$objErrorLog->write($strMessage);
        }
    }


}

