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
include_once './include/functions/date.php';
include_once './include/functions/string.php';
require './lib/rssgenesis/rss.genesis.php';

$module_name =  $row['label'];

$today = ploopi_createtimestamp();


$protocol = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
$hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$port = (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') ? ":{$_SERVER['SERVER_PORT']}" : '';
$path = (!empty($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] != '') ? dirname($_SERVER['SCRIPT_NAME']) : '/';
$baseurl = "{$protocol}{$hostname}{$port}{$path}";


if (isset($_REQUEST['headingid']))
{
    $where = " AND id_heading = {$headingid} ";
    $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'];
    $feed_url = $baseurl;
}
else
{
    $where = '';
    $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'];
    $feed_url = $baseurl;
}
/*
    'TEMPLATE_PATH'                 => $template_path,
    'ADDITIONAL_JAVASCRIPT'         => $additional_javascript,
    'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
    'SITE_TITLE'                    => htmlentities(),
    'WORKSPACE_TITLE'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
    'WORKSPACE_META_DESCRIPTION'    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description']),
    'WORKSPACE_META_KEYWORDS'       => implode(', ', array_keys($keywords)),
    'WORKSPACE_META_AUTHOR'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']),
    'WORKSPACE_META_COPYRIGHT'      => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright']),
    'WORKSPACE_META_ROBOTS'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_robots']),
    'PAGE_QUERYSTRING'              => $query_string,
    'NAV'                           => $nav,
*/


$select =   "
            SELECT      *
            FROM        ploopi_mod_webedit_article
            WHERE       id_module = {$ploopi_moduleid}
            {$where}
            AND         (timestp_published <= {$today} OR timestp_published = 0)
            AND         (timestp_unpublished >= {$today} OR timestp_unpublished = 0)
            ORDER BY    timestp DESC
            LIMIT       0,10
            ";

$db->query($select);
$items = $db->getarray();

$rss = new rssGenesis();


// CHANNEL
$rss->setChannel (
                              ploopi_xmlencode(utf8_encode($feed_title)), // Title
                              ploopi_xmlencode($feed_url), // Link
                              ploopi_xmlencode(utf8_encode($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description'])), // Description
                              'fr', // Language
                              ploopi_xmlencode(utf8_encode($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright'])), // Copyright
                              null, // Managing Editor
                              null, // WebMaster
                              null, // Rating
                              "auto", // PubDate
                              "auto", // Last Build Date
                              '', // Category
                              null, // Docs
                              null, // Time to Live
                              null, // Skip Days
                              null // Skip Hours
                            );


foreach($items as $key => $item)
{
    $url = "index.php?headingid={$item['id_heading']}&articleid={$item['id']}";

    if (_PLOOPI_FRONTOFFICE_REWRITERULE)
    {
        if (empty($item['metatitle'])) $item['metatitle'] = $item['title'];
        $url = ploopi_urlrewrite($url, "{$item['metatitle']} {$item['metakeywords']}");
    }

    $pubdate = substr($item['timestp'],0,4).'/'.substr($item['timestp'],4,2).'/'.substr($item['timestp'],6,2);

    $rss->addItem (
                             ploopi_xmlencode(utf8_encode($item['title'])), // Title
                             ploopi_xmlencode("{$baseurl}{$url}"), // Link
                             ploopi_xmlencode(utf8_encode($item['metadescription'])), // Description
                             $pubdate, //Publication Date
                             '' // Category
                           );
}


// FINISH
$rss->createFile (_PLOOPI_PATHDATA._PLOOPI_SEP.'ploopi_webedit.rss');


?>
