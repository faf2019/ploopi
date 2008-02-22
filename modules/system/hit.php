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
?>
<?
include_once './modules/system/class_log.php';

$log_browserlist = array(
            '1'=>'robot',
            '2'=>'Netscape',
            '3'=>'Firefox',
            '4'=>'MSIE',
            '5'=>'Amaya',
            '6'=>'AOL',
            '7'=>'AvantGo',
            '8'=>'Bluefish',
            '9'=>'Dillo',
            '10'=>'Galeon',
            '11'=>'iCab',
            '12'=>'ICEBrowser',
            '13'=>'Konqueror',
            '14'=>'Lynx',
            '15'=>'Opera',
            '16'=>'Oregano',
            '17'=>'WebTv',
            '18'=>'Wget',
            '19'=>'Safari',
            '20'=>'Kanari'
            );

$log_systemlist = array(
            '1'=>'robot',
            '2'=>'os/2',
            '3'=>'beos',
            '4'=>'mac|ppc',
            '5'=>'unix|x11',
            '6'=>'IRIX',
            '7'=>'HP-UX',
            '8'=>'AIX',
            '9'=>'bsd (freebsd|openbsd|netbsd)',
            '10'=>'SunOS',
            '11'=>'undefined',
            '12'=>'linux',
            '13'=>'windows',
            '14'=>'windows 9x',
            '15'=>'windows XP (NT 5.1)',
            '16'=>'windows 2000 (NT 5.0)',
            '17'=>'windows 2000 (NT 5.0)',
            '18'=>'windows 95',
            '19'=>'windows 98',
            '20'=>'tv',
            '21'=>'QNX'
            );

function log_getsystem($user_agent)
{
    if (eregi('(win|windows) ?(9x ?4\.90|Me)', $user_agent)) return 14;
    if (eregi('(win|windows) ?(98)', $user_agent)) return 19;
    if (eregi('(win|windows) ?(2000)', $user_agent)) return 17;
    if (eregi('(win|windows) ?(95)', $user_agent)) return 18;
    if (eregi('(win|windows) ?(NT)', $user_agent))
    {
        if (eregi('(win|windows) ?NT ?(5\.1|6(\.0)?)', $user_agent)) return 15;
        if (eregi('(win|windows) ?NT ?(5(\.0)?)', $user_agent)) return 17; return 16;
    }
    if (eregi('(win|windows) ?XP', $user_agent)) return 15;
    if (eregi('(win|windows)', $user_agent)) return 13;
    if (eregi('(linux)', $user_agent)) return 12;
    if (eregi('SunOs', $user_agent)) return 10;
    if (eregi('(freebsd|openbsd|netbsd)', $user_agent)) return 9;
    if (eregi('(AIX)', $user_agent)) return 8;
    if (eregi('(QNX)', $user_agent)) return 21;
    if (eregi('(HP-UX)', $user_agent)) return 7;
    if (eregi('(IRIX)', $user_agent)) return 6;
    if (eregi('(unix|x11)', $user_agent)) return 5;
    if (eregi('(mac|ppc)', $user_agent)) return 4;
    if (eregi('beos', $user_agent)) return 3;
    if (eregi('os/2', $user_agent)) return 2;
    if (eregi('(bot|google|slurp|scooter|spider|infoseek|arachnoidea|altavista)', $user_agent)) return 1;
    if (eregi('tv', $user_agent)) return 20;

    return 0;
}

function log_getbrowser($user_agent)
{

    $browsertype='';

    if (eregi('MSIE[ \/]([0-9\.]+)', $user_agent, $log_version)) return '4**'.$log_version[1];
    if (eregi('Mozilla/([0-9.]+)', $user_agent, $log_version) && !eregi('compatible', $user_agent))
    {
        if (eregi('Netscape[[:alnum:]]*[/\ ]([0-9.]+)', $user_agent, $log_version))  return '2**'.$log_version[1];
        if (eregi('Firefox/([0-9.]+)', $user_agent, $log_version) || eregi('[^[]]m([0-9.]+)',$user_agent, $log_version))
        {
            return '3**'.$log_version[1];
        }

        return '2**'.$log_version[1];
    }

    if (eregi('(bot|google|slurp|scooter|spider|infoseek|arachnoidea|altavista)', $user_agent)) return '1**0';

    for ($i=5;$i<=19;$i++)
        if (eregi($log_browserlist[$i] . '[ \/]([0-9\.]+)', $user_agent, $log_version))
            return $i.'**'.$log_version[1];

    return '0**0';
 }


$log = new log();

$log->fields['request_method'] = $_SERVER['REQUEST_METHOD'];
$log->fields['query_string'] = $_SERVER['QUERY_STRING'];
//$log->fields['document_root'] = $_SERVER['DOCUMENT_ROOT'];
$log->fields['remote_addr'] = implode(',', ploopi_getip());
$log->fields['remote_port'] = $_SERVER['REMOTE_PORT'];
$log->fields['script_filename'] = $_SERVER['SCRIPT_FILENAME'];
//$log->fields['path_translated'] = $_SERVER['PATH_TRANSLATED'];
$log->fields['script_name'] = $_SERVER['SCRIPT_NAME'];
$log->fields['request_uri'] = $_SERVER['REQUEST_URI'];
$log->fields['ploopi_moduleid'] = $_SESSION['ploopi']['moduleid'];
$log->fields['ploopi_userid'] = $_SESSION['ploopi']['userid'];
$log->fields['ploopi_workspaceid'] = $_SESSION['ploopi']['workspaceid'];

$systemdate = ploopi_getdatetimedetail();
$log->fields['date_year'] = $systemdate[_PLOOPI_DATE_YEAR];
$log->fields['date_month'] = $systemdate[_PLOOPI_DATE_MONTH];
$log->fields['date_day'] = $systemdate[_PLOOPI_DATE_DAY];
$log->fields['date_hour'] = $systemdate[_PLOOPI_DATE_HOUR];
$log->fields['date_minute'] = $systemdate[_PLOOPI_DATE_MINUTE];
$log->fields['date_second'] = $systemdate[_PLOOPI_DATE_SECOND];

$browser = explode('**',log_getbrowser($_SERVER['HTTP_USER_AGENT']));
$system = log_getsystem($_SERVER['HTTP_USER_AGENT']);

$log->fields['browser'] = $log_browserlist[$browser[0]].' '.$browser[1];
$log->fields['system'] = $log_systemlist[$system];

$log->fields['total_exec_time'] = $ploopi_stats['total_exectime'];
$log->fields['sql_exec_time'] = $ploopi_stats['sql_exectime'];
$log->fields['sql_percent_time'] = $ploopi_stats['sql_ratiotime'];
$log->fields['php_percent_time'] = $ploopi_stats['php_ratiotime'];
$log->fields['numqueries'] = $ploopi_stats['numqueries'];
$log->fields['page_size'] = $ploopi_stats['pagesize'];

$log->save();
?>
