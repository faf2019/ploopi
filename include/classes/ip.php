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
 * Fonctions de manipulation d'IPs
 *
 * @package ploopi
 * @subpackage ip
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class ip
{
    /**
     * Retourne un tableau d'IP pour le client.
     * On peut en effet obtenir plusieurs IP pour un même client, notamment s'il passe par un proxy.
     *
     * @param boolean $wan_only true si l'on ne veut que les adresses WAN (false par défaut)
     * @return array tableau d'IP
     */

    public static function get($wan_only = false)
    {
        $ip = '';
        $ret = array();

        if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
        elseif(getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
        else $ip = getenv("REMOTE_ADDR");

        $ip_list = explode(',', $ip);

        foreach($ip_list as $ip)
        {
            if (preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $ip) && sprintf("%u",ip2long($ip)) != sprintf("%u",ip2long('255.255.255.255')))
            {
                if (
                        !$wan_only ||
                        !(  (sprintf("%u",ip2long('10.0.0.0')) <= sprintf("%u",ip2long($ip)) && sprintf("%u",ip2long($ip)) <= sprintf("%u",ip2long('10.255.255.255')))
                        ||  (sprintf("%u",ip2long('172.16.0.0')) <= sprintf("%u",ip2long($ip)) && sprintf("%u",ip2long($ip)) <= sprintf("%u",ip2long('172.31.255.255')))
                        ||  (sprintf("%u",ip2long('192.168.0.0')) <= sprintf("%u",ip2long($ip)) && sprintf("%u",ip2long($ip)) <= sprintf("%u",ip2long('192.168.255.255')))
                        ||  (sprintf("%u",ip2long('169.254.0.0')) <= sprintf("%u",ip2long($ip)) && sprintf("%u",ip2long($ip)) <= sprintf("%u",ip2long('169.254.255.255')))
                        )
                    )
                {
                    $ret[] = $ip;
                }
            }
        }

        return $ret;
    }

    /**
     * Convertit une liste de range d'IP en liste de règles facilement exploitables (à base d'entiers)
     *
     * @param string $rules ranges d'IP
     * @return array tableau de règles
     */

    public static function getrules($rules)
    {
        $intervals = array();
        $iprules = array();
        $ip1 = 0;
        $ip2 = 0;

        if ($rules == '') return false;

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

    /**
     * Indique si l'IP du client fait partie du range d'IP fourni
     *
     * @param array $iprules tableau de range d'ip (fourni par ip::getrules)
     * @return boolean true si l'IP est incluse dans le range d'IP fourni
     */

    public static function isvalid($iprules)
    {
        $ip_ok = false;

        if ($iprules)
        {
            $arrUserip = ip::get();

            if (!empty($arrUserip)) $userip = $arrUserip[0];

            foreach($iprules as $startip => $endip)
            {
                if ($userip >= $startip && $userip <= $endip) $ip_ok = true;
            }
        }
        else $ip_ok = true;

        return($ip_ok);
    }
}
