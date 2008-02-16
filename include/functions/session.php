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

function ploopi_session_reset()
{
    global $scriptenv;

    // session_destroy();
    $_SESSION['ploopi'] = array(
                    'login'         => '',
                    'password'      => '',
                    'userid'        => '',
                    'workspaceid'   => '',
                    'webworkspaceid'    => '',
                    'adminlevel'    => 0,

                    'connected'     => false,
                    'loginerror'    => false,
                    'paramloaded'   => false,
                    'mode'          => 'admin',

                    'remoteip'      => ploopi_getip(),
                    'host'          => $_SERVER['HTTP_HOST'],
                    'scriptname'    => $scriptenv,

                    'wcemoduleid'   => 0,

                    'hosts'         => array(),
                    'groups'        => array(),
                    'modules'       => array(),
                    'allworkspaces' => '',

                    'currentrequesttime'    => mktime(),
                    'lastrequesttime'       => mktime(),

                    'moduleid'      =>  '',
                    'mainmenu'      =>  '',
                    'action'        =>  'public',
                    'moduletabid'   =>  '',
                    'moduletype'    =>  '',
                    'moduletypeid'  =>  '',
                    'modulelabel'   =>  '',
                    'moduleicon'    =>  '',

                    'defaultskin'   =>  '',
                    'template_name' =>  '',
                    'template_path' =>  '',

                    'uri'   =>  '',

                    'newtickets'    => 0,

                    'fingerprint'   => _PLOOPI_FINGERPRINT,

                    'browser'       => (function_exists('ploopi_detect_browser')) ? ploopi_detect_browser($_SERVER['HTTP_USER_AGENT']) : array()
                );

    if (isset($_SESSION['ploopi']['browser']['PDA_NAME'])) $_SESSION['ploopi']['browser']['pda'] = ($_SESSION['ploopi']['browser']['PDA_NAME'] != '');
}

function ploopi_session_update()
{
    global $scriptenv;

    if (!isset($_SESSION['ploopi']['fingerprint']) || $_SESSION['ploopi']['fingerprint'] != _PLOOPI_FINGERPRINT) // problème d'empreinte, session invalide
    {
        session_destroy();
        ploopi_redirect("{$scriptenv}?ploopi_errorcode="._PLOOPI_ERROR_SESSIONINVALID);
    }

    $_SESSION['ploopi']['currentrequesttime'] = mktime();
    if (empty($_SESSION['ploopi']['lastrequesttime'])) $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];

    $diff = $_SESSION['ploopi']['currentrequesttime'] - $_SESSION['ploopi']['lastrequesttime'];

    if ($diff > _PLOOPI_SESSIONTIME && _PLOOPI_SESSIONTIME != '' && _PLOOPI_SESSIONTIME != 0)
    {
        session_destroy();
        ploopi_redirect("{$scriptenv}?ploopi_errorcode="._PLOOPI_ERROR_SESSIONEXPIRE);
    }
    else
    {
        $_SESSION['ploopi']['lastrequesttime'] = $_SESSION['ploopi']['currentrequesttime'];
        $_SESSION['ploopi']['remoteip'] = ploopi_getip();
    }

    $_SESSION['ploopi']['scriptname'] = $scriptenv;
}

function ploopi_getiprules($rules)
{
    $intervals = array();
    $iprules = array();
    $ip1 = 0;
    $ip2 = 0;


    if ($rules == '')
    {
        return FALSE;
    }

    //------------------------
    // string conversion
    //------------------------
    $intervals = explode(';',$rules);

    foreach ($intervals as $interval)
    {
        $ips = explode('-',trim($interval));

        if (count($ips) == 1)
        {
            $ips[0] = trim($ips[0]);
            if (strpos($ips[0],"*") !== false)
            {
                $ip1 = str_replace('*','0',$ips[0]);
                $ip2 = str_replace('*','255',$ips[0]);
            }
            else
            {
                $ip1 = $ip2 = $ips[0];
            }
        }
        elseif (count($ips) == 2)
        {
            $ip1 = trim($ips[0]);
            $ip2 = trim($ips[1]);
        }

        $ip1 = ip2long($ip1);
        $ip2 = ip2long($ip2);

        $iprules[$ip1] = $ip2;
    }


    return $iprules;
}

function ploopi_isipvalid($iprules)
{
    $ip_ok = false;

    if ($iprules)
    {
        $userip = ip2long($_SERVER['REMOTE_ADDR']);
        foreach($iprules as $startip => $endip)
        {
            if ($userip >= $startip && $userip <= $endip) $ip_ok = true;
        }
    }
    else $ip_ok = true;

    return($ip_ok);
}







?>
