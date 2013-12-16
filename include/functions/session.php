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
 * Fonctions de mise à jour du contenu de la session
 *
 * @package ploopi
 * @subpackage session
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/functions/ip.php';
include_once './include/classes/session.php';

/**
 * Réinitialise la session
 */

function ploopi_session_reset()
{
    require_once 'Net/UserAgent/Detect.php';

    // Désactivation du handler d'erreur à cause d'un bug non corrigé de la classe Detect ( http://pear.php.net/bugs/bug.php?id=15764 )
    ploopi_unset_error_handler();
    $strRemoteBrowser = Net_UserAgent_Detect::getBrowserString();
    $strRemoteSystem = Net_UserAgent_Detect::getOSString();
    ploopi_set_error_handler();

    // Suppression des données de la session active
    // Regénération d'un ID
    session_regenerate_id(true);

    $_SESSION['ploopi'] = array(
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

        'remote_ip' => ploopi_getip(),
        'remote_browser' => $strRemoteBrowser,
        'remote_system' => $strRemoteSystem,

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
    );
}

/**
 * Met à jour les données et vérifie la validité de la session
 */

function ploopi_session_update()
{
    global $session;

    $scriptname = basename($_SERVER['PHP_SELF']);

    if (!isset($_SESSION['ploopi']['fingerprint']) || $_SESSION['ploopi']['fingerprint'] != _PLOOPI_FINGERPRINT) // problème d'empreinte, session invalide
    {
        ploopi_logout(_PLOOPI_ERROR_SESSIONINVALID);
    }

    $_SESSION['ploopi']['currentrequesttime'] = time();
    if (empty($_SESSION['ploopi']['lastrequesttime'])) $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];

    $diff = $_SESSION['ploopi']['currentrequesttime'] - $_SESSION['ploopi']['lastrequesttime'];

    // Si la durée de sessoin est expirée et que l'on est connecté, on vide la session et on retourne à la page de login
    if ($diff > _PLOOPI_SESSIONTIME && _PLOOPI_SESSIONTIME != '' && _PLOOPI_SESSIONTIME != 0 && !empty($_SESSION['ploopi']['connected']))
    {
        ploopi_logout(_PLOOPI_ERROR_SESSIONEXPIRE);
    }
    else // Sinon on met simplement à jour la date/heure de la dernière requête + IP
    {
        $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];
        $_SESSION['ploopi']['remote_ip'] = ploopi_getip();
    }

    $_SESSION['ploopi']['scriptname'] = $scriptname;
}
