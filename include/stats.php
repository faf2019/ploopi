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
$ploopi_stats = array();

if (isset($ploopi_content)) $ploopi_stats['pagesize'] = strlen($ploopi_content);
else $ploopi_stats['pagesize'] = 0;

if (isset($db))
{
    $ploopi_stats['numqueries'] = $db->num_queries;
    $ploopi_stats['sql_exectime'] = round($db->exectime_queries*1000,0);
}
else
{
    $ploopi_stats['numqueries'] = 0;
    $ploopi_stats['sql_exectime'] = 0;
}

if (isset($ploopi_timer))
{
    $ploopi_stats['total_exectime'] = round($ploopi_timer->getexectime()*1000,0);
    $ploopi_stats['sql_ratiotime'] = round(($ploopi_stats['sql_exectime']*100)/$ploopi_stats['total_exectime'] ,0);
    $ploopi_stats['php_ratiotime'] = 100 - $ploopi_stats['sql_ratiotime'];
}
else
{
    $ploopi_stats['total_exectime'] = 0;
    $ploopi_stats['sql_ratiotime'] = 0;
    $ploopi_stats['php_ratiotime'] = 0;
}

if (isset($_SESSION))
{
    $ploopi_stats['sessionsize'] = strlen(session_encode());
}
else
{
    $ploopi_stats['sessionsize'] = 0;
}
?>
