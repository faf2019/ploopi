<?php
/*
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
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
$rssentry_select =  "
                SELECT      entry.*,
                            feed.id as feedid
                FROM        ploopi_mod_rss_entry entry,
                            ploopi_mod_rss_feed feed
                            
                WHERE       entry.id_feed = feed.id
                  AND       feed.id_workspace = {$wk} 
                  AND       feed.default = 1
                                        
                ORDER BY    entry.published DESC, 
                            entry.timestp DESC, 
                            entry.id
                ";

if($_SESSION['ploopi']['modules'][$template_moduleid]['nbitemdisplay']>0)
   $rssentry_select .= "LIMIT 0,{$_SESSION['ploopi']['modules'][$template_moduleid]['nbitemdisplay']}";

$rssentry_result = $db->query($rssentry_select);
while ($rssEntry_fields = $db->fetchrow($rssentry_result))
{
  $booFeedOpen = false;
  // Ce sont les flux par defaut donc ils ont déjà dû être stocké et mis à jour dans template.base.inc.php 
  if (isset($arrListFeed[$rssEntry_fields['feedid']]))
  {
    $arrRssFeedData = $arrListFeed[$rssEntry_fields['feedid']];
    $booFeedOpen = true;
  }
  
  if($booFeedOpen)
  {
    
    if (!empty($rssEntry_fields['published']) && is_numeric($rssEntry_fields['published']))
    {
        $published_date = date(_PLOOPI_DATEFORMAT,$rssEntry_fields['published']);
        $published_time = date(_PLOOPI_TIMEFORMAT,$rssEntry_fields['published']);
    }
    else
    {
        $published_date = $published_time = '';
    }
    
    $template_body->assign_block_vars('rssentryfusion', array(
                'FEED_TITLE' => strip_tags($arrRssFeedData['title'],'<b><i>'),
                'FEED_TITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['title'],'<b><i>')),
                'FEED_SUBTITLE' => strip_tags($arrRssFeedData['subtitle'],'<b><i>'),
                'FEED_SUBTITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['subtitle'],'<b><i>')),
                'FEED_LINK' => $arrRssFeedData['link'],
                'TITLE' => strip_tags($rssEntry_fields['title'],'<b><i>'),
                'TITLE_CLEANED' => htmlentities(strip_tags($rssEntry_fields['title'],'<b><i>')),
                'SUBTITLE' => strip_tags($rssEntry_fields['subtitle'],'<b><i>'),
                'SUBTITLE_CLEANED' => htmlentities(strip_tags($rssEntry_fields['subtitle'],'<b><i>')),
                'PUBLISHED_DATE' => $published_date,
                'PUBLISHED_TIME' => $published_time,
                'LINK' => $rssEntry_fields['link'],
                'CONTENT' => strip_tags($rssEntry_fields['content'], '<b><i><a>'),
                'CONTENT_CLEAN' => htmlentities(strip_tags($rssEntry_fields['content'], '<b><i><a>')),
                'CONTENT_CUT' => ploopi_strcut(strip_tags($rssEntry_fields['content']),200)
                ));    
  }
}
?>