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
 * Affichage du backend rss des pages publiées en frontoffice
 *
 * @package webedit
 * @subpackage rss
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusions des fonctions sur les dates et les chaînes (l'appel via rss.php est minimal, les fonctions ne sont donc pas déjà incluses)
 */
include_once './include/functions/date.php';
include_once './include/functions/string.php';

/**
 * La classe heading
 */
include_once './modules/webedit/class_heading.php';

/**
 * RSSGenesis qui permet de générer le flux
 */
require './lib/rssgenesis/rss.genesis.php';

$module_name =  $row['label'];

$today = ploopi_createtimestamp();

if (isset($_REQUEST['headingid']))
{
    $objHeading = new webedit_heading();
    $objHeading->open($_REQUEST['headingid']);
    
    $where = " AND id_heading = {$_REQUEST['headingid']} ";
    $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'].' - '.$objHeading->fields['label'];
    $feed_description = $objHeading->fields['description'];
}
else
{
    $where = '';
    $feed_title = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title'];
    $feed_description = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description'];
}


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
                              ploopi_xmlencode($basepath), // Link
                              ploopi_xmlencode(utf8_encode($feed_description)), // Description
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
                     ploopi_xmlencode("{$basepath}/{$url}"), // Link
                     ploopi_xmlencode(utf8_encode($item['metadescription'])), // Description
                     $pubdate, //Publication Date
                     '' // Category
                   );
}


// FINISH
$rss->createFile (_PLOOPI_PATHDATA._PLOOPI_SEP.'ploopi_webedit.rss');


?>
