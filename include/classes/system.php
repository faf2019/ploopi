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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Fonctions de base du coeur de Ploopi
 *
 * @package ploopi
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class system
{

    /**
     * Affiche un message et termine le script courant.
     * Peut envoyer un mail contenant les erreurs rencontrées durant l'exécution du script.
     * Peut vider le buffer en cours.
     * Ferme la session en cours.
     * Ferme la connexion à la base de données (si ouverte).
     *
     * @param mixed $var variable à afficher
     * @param boolean $flush true si la sortie doit être vidée (true par défaut)
     *
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see die
     * @see output::print_r
     */

    public static function kill($var = null, $flush = true)
    {
        global $db;

        global $ploopi_timer;

        $strHost = (php_sapi_name() != 'cli') ? $_SERVER['HTTP_HOST'] : '';

        if (!empty(error::$level) && error::$level)
        {
            if (defined('_PLOOPI_MAIL_ERRORS') && _PLOOPI_MAIL_ERRORS  && defined('_PLOOPI_SYSMAIL') && _PLOOPI_SYSMAIL != '')
            {
                mail(
                    _PLOOPI_SYSMAIL,
                    "[".error::$errorlevel[error::$level]."] sur [{$strHost}]",
                    "{error::$nb} erreur(s) sur {error::$msg}".
                    "\n_SERVER:\n".print_r($_SERVER, true).
                    "\n_POST:\n".print_r($_POST, true).
                    "\n_GET:\n".print_r($_GET, true),
                    "From: ".trim(current(explode(',', _PLOOPI_ADMINMAIL)))
                );
            }

            if (defined('_PLOOPI_LOG_ERRORS') && _PLOOPI_LOG_ERRORS && defined('_PLOOPI_LOG_ERRORS_FILE') && _PLOOPI_LOG_ERRORS_FILE != '')
            {
                file_put_contents(_PLOOPI_LOG_ERRORS_FILE, "=============================================\n".error::$msg, FILE_APPEND);
            }
        }

        if (!is_null($var))
        {
            if (is_string($var)) echo $var;
            else output::print_r($var);
        }

        if (php_sapi_name() != 'cli') session_write_close();

        if ($flush) while (ob_get_level()>1) ob_end_flush();

        die();
    }


    /**
     * Retourne le nombre de coeurs du serveur apache
     */

    public static function getnbcore() {
        return intval(`cat /proc/cpuinfo | grep processor | wc -l`);
    }


    /**
     * Exécute une commande
     * Crédit : http://stackoverflow.com/questions/2320608/php-stderr-after-exec/25879953#25879953
     */

    public static function exec($cmd, &$stdout=null, &$stderr=null) {
        $proc = proc_open($cmd,[
            1 => ['pipe','w'],
            2 => ['pipe','w'],
        ],$pipes);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        return proc_close($proc);
    }

    /**
     * Déconnecte l'utilisateur, nettoie la session, initialise le code d'erreur
     *
     * @param int $intErrorCode code d'erreur
     * @param int $intSleep durée d'attente avant la redirection en seconde
     */
    public static function logout($intErrorCode = 0, $intSleep = 1, $booRedirect = true, $arrSession = array())
    {
        global $arrParsedURI;

        // Suppression de l'information de connexion
        $objConnectedUser = new connecteduser();
        if ($objConnectedUser->open(session_id())) $objConnectedUser->delete();

        // Suppression des données de la session active
        // Ecriture (suppression effective)
        // Regénération d'un ID
        // Nouvelle session
        session::reset();
        //session_regenerate_id(true);
        session_write_close();
        session_start();

        // Mise à jour de la session post logout
        $_SESSION['ploopi']['errorcode'] = $intErrorCode;
        $_SESSION['ploopi'] = array_merge($_SESSION['ploopi'], $arrSession);


        if ($intSleep > 0) sleep($intSleep);

        // Redirection ?
        if ($booRedirect && isset($_SERVER['HTTP_REFERER']))
        {
            output::redirect($_SERVER['HTTP_REFERER'], false, false);
        }
        else
        {
            output::redirect(basename($arrParsedURI['path']), false);
        }
    }


    /**
     * Renvoie un tableau des templates disponibles (frontoffice ou backoffice)
     *
     * @param string $type au choix entre 'frontoffice' et 'backoffice', par défaut 'frontoffice'
     * @return array tableau des templates disponibles
     */

    public static function getavailabletemplates($type = 'frontoffice')
    {
        $templates = array();
        $basepath = '.'._PLOOPI_SEP.'templates'._PLOOPI_SEP.$type;

        clearstatcache();

        $p = @opendir(realpath($basepath));

        while ($template = @readdir($p))
        {
            $tplpath=realpath($basepath._PLOOPI_SEP.$template);

            if ((substr($template, 0, 1) != '.') && is_dir($tplpath) && file_exists($tplpath._PLOOPI_SEP.'index.tpl')) $templates[] = $template;
        }

        closedir($p);

        sort($templates);

        return($templates);
    }
    /**
     * Retourne la liste des espaces affectés par la vue du module (ascendante/descendante/globale/privée/transversale)
     *
     * @param int $moduleid identifiant du module
     * @return string chaine contenant la liste des espaces séparés par une virgule
     */

    public static function viewworkspaces($moduleid = -1)
    {

        if ($_SESSION['ploopi']['workspaceid'] == '') $current_workspaceid = _PLOOPI_SYSTEMGROUP; // HOME PAGE / NO GROUP;
        else $current_workspaceid = $_SESSION['ploopi']['workspaceid'];

        $workspaces = '';

        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

        switch($_SESSION['ploopi']['modules'][$moduleid]['viewmode'])
        {
            default:
            case _PLOOPI_VIEWMODE_PRIVATE:

                $workspaces = $current_workspaceid;

            break;

            case _PLOOPI_VIEWMODE_DESC:

                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);
                $workspaces = explode(';', $objWorkspace->fields['parents']);
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

            case _PLOOPI_VIEWMODE_ASC:

                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);
                $workspaces = array_keys($objWorkspace->getChildren());
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

            case _PLOOPI_VIEWMODE_GLOBAL:

                $workspaces = $_SESSION['ploopi']['allworkspaces'];

            break;

            case _PLOOPI_VIEWMODE_ASCDESC:

                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);

                $workspaces = explode(';', $objWorkspace->fields['parents']);
                $workspaces = array_merge($workspaces, array_keys($objWorkspace->getChildren()));
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

        }

        if ($_SESSION['ploopi']['modules'][$moduleid]['transverseview'])
        {

            if (!isset($objWorkspace))
            {
                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);
            }

            $arrBrothers = $objWorkspace->getbrothers();

            if (!empty($arrBrothers)) $workspaces .= ','.implode(',', $arrBrothers);

        }

        return $workspaces;
    }

    /**
     * Retourne la liste des espaces inversement affectés par la vue du module (ascendante/descendante/globale/privée/transversale)
     *
     * @param int $moduleid identifiant du module
     * @return string chaine contenant la liste des espaces séparés par une virgule
     */

    public static function viewworkspaces_inv($moduleid = -1)
    {

        if ($_SESSION['ploopi']['workspaceid'] == '') $current_workspaceid = _PLOOPI_SYSTEMGROUP; // HOME PAGE / NO GROUP;
        else $current_workspaceid = $_SESSION['ploopi']['workspaceid'];

        $workspaces = '';

        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid']; // get session value if not defined

        switch($_SESSION['ploopi']['modules'][$moduleid]['viewmode'])
        {
            default:
            case _PLOOPI_VIEWMODE_PRIVATE:

                $workspaces = $current_workspaceid;

            break;

            case _PLOOPI_VIEWMODE_ASC:

                $workspaces = explode(';',$_SESSION['ploopi']['workspaces'][$current_workspaceid]['parents']);
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

            case _PLOOPI_VIEWMODE_DESC:

                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);
                $workspaces = array_keys($objWorkspace->getChildren());
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

            case _PLOOPI_VIEWMODE_GLOBAL:

                $workspaces = $_SESSION['ploopi']['allworkspaces'];

            break;


            case _PLOOPI_VIEWMODE_ASCDESC:

                $workspaces = explode(';',$_SESSION['ploopi']['workspaces'][$current_workspaceid]['parents']);

                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);

                $workspaces = array_merge($workspaces, array_keys($objWorkspace->getChildren()));
                $workspaces[] = $current_workspaceid;
                $workspaces = implode(',', $workspaces);

            break;

        }

        if ($_SESSION['ploopi']['modules'][$moduleid]['transverseview'])
        {

            if (!isset($objWorkspace))
            {
                $objWorkspace = new workspace();
                $objWorkspace->open($current_workspaceid);
            }

            $arrBrothers = $objWorkspace->getbrothers();

            if (!empty($arrBrothers)) $workspaces .= ','.implode(',', $arrBrothers);

        }

        return $workspaces;
    }
}
