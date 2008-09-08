<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Outil permettant d'afficher la "charge" du serveur (expérimental).
 * On calcul la charge en calculant sur une période de temps donnée, le temps passé par apache à exécuter des scripts. 
 * La charge moyenne est calculée sur 1min, 5min, 15min, 30min, 1h, 24h 
 * 
 * @package system
 * @subpackage system_tools
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation des données
 */
$current_ts = ploopi_createtimestamp();
$intMaxRequests = 50;

$load = array();
$load['m1']['ts'] = ploopi_timestamp_add($current_ts, 0, -1);
$load['m5']['ts'] = ploopi_timestamp_add($current_ts, 0, -5);
$load['m15']['ts'] = ploopi_timestamp_add($current_ts, 0, -15);
$load['m30']['ts'] = ploopi_timestamp_add($current_ts, 0, -30);
$load['m60']['ts'] = ploopi_timestamp_add($current_ts, 0, -60);
$load['h24']['ts'] = ploopi_timestamp_add($current_ts, 0, -60*24);

$load['m1']['time'] = 60;
$load['m5']['time'] = 300;
$load['m15']['time'] = 900;
$load['m30']['time'] = 1800;
$load['m60']['time'] = 3600;
$load['h24']['time'] = 86400;

$load['m1']['title'] = '1 minute';
$load['m5']['title'] = '5 minutes';
$load['m15']['title'] = '15 minutes';
$load['m30']['title'] = '30 minutes';
$load['m60']['title'] = '1 heure';
$load['h24']['title'] = '1 journée';

// Calcul Charge :
foreach($load as $key => $l)
{
    $sql =  "
            SELECT      sum(total_exec_time) as total_exec_time, 
                        sum(sql_exec_time) as sql_exec_time, 
                        sum(numqueries) as numqueries, 
                        sum(page_size) as page_size,
                        count(*) as numpages
                        
            FROM        ploopi_log
    
            WHERE       ts >= {$l['ts']}
            ";
    
    
    $db->query($sql);
    
    if ($row = $db->fetchrow()) 
    {
        $load[$key]['res'] = $row;
        $load[$key]['res']['load'] = ($row['total_exec_time'] / ($l['time']*10)) / _PLOOPI_LOAD_NBCORE; // charge (ratio tps d'exec/tps écoulé) en %
        $load[$key]['res']['rps'] = $row['numqueries'] / $l['time']; // requêtes par seconde
        $load[$key]['res']['bw'] = $row['page_size'] / ($l['time']*1024); // bande passante (ko/s)
        $load[$key]['res']['pps'] = $row['numpages'] / $l['time']; // page per second
        $load[$key]['res']['tpp'] = ($row['numpages']) ? $row['total_exec_time'] / $row['numpages'] : 0; // time per page
        $load[$key]['res']['spp'] = ($row['numpages']) ? $row['total_exec_time'] / $row['numpages'] : 0; // sql per page
        
        
        $load[$key]['res']['load'] = sprintf("%.02f", $load[$key]['res']['load']);
        $load[$key]['res']['rps'] = sprintf("%.02f", $load[$key]['res']['rps']);
        $load[$key]['res']['bw'] = sprintf("%.02f", $load[$key]['res']['bw']);
        $load[$key]['res']['pps'] = sprintf("%.02f", $load[$key]['res']['pps']);
        $load[$key]['res']['tpp'] = sprintf("%.02f", $load[$key]['res']['tpp']);
    }
}




$columns = array();
$values = array();

$columns['left']['title'] = array('label' => 'Période', 'width' => 100, 'style' => 'text-align:right;');
$columns['left']['load'] = array('label' => 'Charge (%)', 'width' => 100, 'style' => 'text-align:right;');
$columns['left']['tpp'] = array('label' => 'Tps de Réponse (ms)', 'width' => 180, 'style' => 'text-align:right;');
$columns['left']['rps'] = array('label' => 'Requêtes/s', 'width' => 100, 'style' => 'text-align:right;');
$columns['left']['pps'] = array('label' => 'Pages/s', 'width' => 100, 'style' => 'text-align:right;');
$columns['left']['bw'] = array('label' => 'Bande passante (ko)', 'width' => 150, 'style' => 'text-align:right;');


$db->query($sql);

$c = 0;
// Calcul Charge :
foreach($load as $key => $l)
{
    $load_color = system_serverload_getcolor(0,100,$l['res']['load']);
    $tpp_color = system_serverload_getcolor(0,500,$l['res']['tpp']);
    
    $values[$c]['values']['title'] = array('label' => $l['title'], 'style' => 'text-align:right;');
    $values[$c]['values']['load'] = array('label' => $l['res']['load']. ' %', 'style' => "text-align:right;background-color:{$load_color}");
    $values[$c]['values']['tpp'] = array('label' => $l['res']['tpp'], 'style' => "text-align:right;background-color:{$tpp_color}");
    $values[$c]['values']['rps'] = array('label' => $l['res']['rps'], 'style' => 'text-align:right;');
    $values[$c]['values']['pps'] = array('label' => $l['res']['pps'], 'style' => 'text-align:right;');
    $values[$c]['values']['bw'] = array('label' => $l['res']['bw'].' ko/s', 'style' => 'text-align:right;');
    
    $values[$c]['description'] = '';
    $values[$c]['style'] = '';

    $c++;
}

?>
<p class="ploopi_va" style="padding:4px;background-color:#e0e0e0;border-bottom:2px solid #c0c0c0;font-weight:bold;">
    <span>Charge du système en temps réel (nombre de coeurs : <? echo _PLOOPI_LOAD_NBCORE; ?>)&nbsp;&nbsp;</span><img src="./img/loading.gif" style="visibility:hidden;" id="system_serverload_loading"/>
</p>
<?
$skin->display_array($columns, $values, 'array_load');
         

// Analyse des x dernières requêtes

$db->query( "
            SELECT  ts, 
                    browser,
                    system,
                    total_exec_time,
                    sql_exec_time,
                    numqueries,
                    page_size,
                    request_uri, 
                    remote_addr,
                    ploopi_userid, 
                    ploopi_workspaceid,
                    ploopi_moduleid
                    
            FROM    ploopi_log 

            ORDER BY ts DESC 
            
            LIMIT 0,{$intMaxRequests}
            ");

$columns = array();
$values = array();

$columns['left']['ts'] = array('label' => 'Date/Heure', 'width' => 135);
$columns['left']['env'] = array('label' => 'Client', 'width' => 160);
$columns['left']['exec'] = array('label' => 'Page (ms)', 'width' => 80);
$columns['left']['sql'] = array('label' => 'SQL (ms)', 'width' => 80);
$columns['left']['numq'] = array('label' => 'SQL (qy)', 'width' => 80);
$columns['left']['ip'] = array('label' => 'IP', 'width' => 100);
$columns['left']['ploopi'] = array('label' => 'Ploopi', 'width' => 60);
$columns['auto']['uri'] = array('label' => 'URI');


$c = 0;

while ($row = $db->fetchrow())
{
    $ldate = ploopi_timestamp2local($row['ts']);
    $values[$c]['values']['ts'] = array('label' => "{$ldate['date']} {$ldate['time']}", 'sort_label' => $row['ts']);
    $values[$c]['values']['env'] = array('label' => "{$row['browser']} {$row['system']}");
    $values[$c]['values']['exec'] = array('label' => $row['total_exec_time']);
    $values[$c]['values']['sql'] = array('label' => $row['sql_exec_time']);
    $values[$c]['values']['numq'] = array('label' => $row['numqueries']);
    $values[$c]['values']['uri'] = array('label' => $row['request_uri']);
    $values[$c]['values']['ip'] = array('label' => $row['remote_addr']);
    $values[$c]['values']['ploopi'] = array('label' => "{$row['ploopi_userid']}/{$row['ploopi_workspaceid']}/{$row['ploopi_moduleid']}");
    
    $values[$c]['description'] = '';
    $values[$c]['style'] = '';

    $c++;
}


?>
<div style="padding:4px;background-color:#e0e0e0;border-bottom:2px solid #c0c0c0;border-top:2px solid #c0c0c0;font-weight:bold;">
    Historique : <? echo $intMaxRequests; ?> dernières requêtes
</div>
<?
$skin->display_array($columns, $values, 'array_requests');
?>