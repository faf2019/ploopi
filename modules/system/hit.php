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

include_once './modules/system/class_log.php';

$log = new log();

$log->fields['request_method'] = $_SERVER['REQUEST_METHOD'];
$log->fields['query_string'] = $_SERVER['QUERY_STRING'];
$log->fields['remote_addr'] = implode(',', ploopi_getip());
$log->fields['remote_port'] = $_SERVER['REMOTE_PORT'];
$log->fields['script_filename'] = $_SERVER['SCRIPT_FILENAME'];
$log->fields['script_name'] = $_SERVER['SCRIPT_NAME'];
$log->fields['request_uri'] = $_SERVER['REQUEST_URI'];
$log->fields['ploopi_moduleid'] = (empty($_SESSION['ploopi']['moduleid'])) ? 0 : $_SESSION['ploopi']['moduleid'];
$log->fields['ploopi_userid'] = (empty($_SESSION['ploopi']['userid'])) ? 0 : $_SESSION['ploopi']['userid'];
$log->fields['ploopi_workspaceid'] = (empty($_SESSION['ploopi']['workspaceid'])) ? 0 : $_SESSION['ploopi']['workspaceid'];;

$systemdate = ploopi_getdatetimedetail();
$log->fields['date_year'] = $systemdate[_PLOOPI_DATE_YEAR];
$log->fields['date_month'] = $systemdate[_PLOOPI_DATE_MONTH];
$log->fields['date_day'] = $systemdate[_PLOOPI_DATE_DAY];
$log->fields['date_hour'] = $systemdate[_PLOOPI_DATE_HOUR];
$log->fields['date_minute'] = $systemdate[_PLOOPI_DATE_MINUTE];
$log->fields['date_second'] = $systemdate[_PLOOPI_DATE_SECOND];

/*

implémenter pear / Net_UserAgent_Detect

$log->fields['browser'] = $log_browserlist[$browser[0]].' '.$browser[1];
$log->fields['system'] = $log_systemlist[$system];

 */

$log->fields['browser'] = '';
$log->fields['system'] = '';

if (empty($ploopi_stats)) include './include/stats.php';

$log->fields['total_exec_time'] = $ploopi_stats['total_exectime'];
$log->fields['sql_exec_time'] = $ploopi_stats['sql_exectime'];
$log->fields['sql_percent_time'] = $ploopi_stats['sql_ratiotime'];
$log->fields['php_percent_time'] = $ploopi_stats['php_ratiotime'];
$log->fields['numqueries'] = $ploopi_stats['numqueries'];
$log->fields['page_size'] = $ploopi_stats['pagesize'];

$log->save();
?>
