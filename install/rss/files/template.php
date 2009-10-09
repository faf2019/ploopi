<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
    Copyright (c) 2008 HeXad
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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package rss
 * @subpackage template
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss',false,false,false);

include_once './modules/rss/class_rss_feed.php';

$arrListFeed = array();

$wk = ploopi_viewworkspaces($template_moduleid);

//Recupération des info sur les flux necessaire un peu partout après...
$rssfeed_select =   "
      SELECT      ploopi_mod_rss_feed.*
      FROM        ploopi_mod_rss_feed
      WHERE       ploopi_mod_rss_feed.id_workspace = {$wk}
      ";

$rssfeed_result = $db->query($rssfeed_select);
$arrFeedTmp = $db->getarray();
foreach($arrFeedTmp as $arrFeedData)
{
  if(!$arrFeedData['limit']>0) $arrFeedData['limit'] = $_SESSION['ploopi']['modules'][$template_moduleid]['nbitemdisplay'];
  $arrListFeed[$arrFeedData['id']] = $arrFeedData;
}

//mise à jour des flux
$objRssFeed = new rss_feed();
$objRssFeed->updateallfeed($arrListFeed,$template_moduleid);
unset($objRssFeed);

// ploopi_print_r($arrListFeed);

include './modules/rss/template.base.inc.php';
include './modules/rss/template.fusion.inc.php';
include './modules/rss/template.cat.inc.php';
include './modules/rss/template.filter.inc.php';

// ploopi_print_r($template_body);
unset($arrListFeed);
?>

