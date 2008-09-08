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

$intCpt = 0;
$intIdFeedTmp = 0;
$arrRssFeedPassed = array();

$rssEntry_sql =  "
        SELECT      entry.*,
                    feed.id as feedid
                    
        FROM        ploopi_mod_rss_entry entry,
                    ploopi_mod_rss_feed feed
                    
        WHERE       entry.id_feed = feed.id
           AND      feed.id_workspace = {$wk}
           
        ORDER BY    feed.title,
                    entry.published DESC, 
                    entry.timestp DESC, 
                    entry.id
        ";
                    
$rssEntry_result = $db->query($rssEntry_sql);
while($rssEntry_fields = $db->fetchrow($rssEntry_result))
{
  // si les mess sont déjà chargé a leur limite et qu'on est toujours dans le meme flux on ne fait rien
  if($intCpt <= $arrListFeed[$rssEntry_fields['feedid']]['limit'] || !isset($arrRssFeedPassed[$rssEntry_fields['feedid']]))
  {
    $arrRssFeedData = $arrListFeed[$rssEntry_fields['feedid']];
    
    $booFeedTag = ($arrRssFeedData['tpl_tag'] != '' && $arrRssFeedData['tpl_tag'] != NULL) ? true : false;
  
    if(!isset($arrRssFeedPassed[$rssEntry_fields['feedid']]))
    {
      $intCpt = 0;
      
      // Entete de flux standard
      $template_body->assign_block_vars('rssfeed', array(
              'TITLE' => strip_tags($arrRssFeedData['title'],'<b><i>'),
              'TITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['title'],'<b><i>')),
              'SUBTITLE' => strip_tags($arrRssFeedData['subtitle'],'<b><i>'),
              'SUBTITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['subtitle'],'<b><i>')),
              'LINK' => $arrRssFeedData['link']
              ));
      
      // Entete de flux avec tag particulier pour le template        
      if($booFeedTag)
      {
        $template_body->assign_block_vars($arrRssFeedData['tpl_tag'],array());
        
        $template_body->assign_block_vars($arrRssFeedData['tpl_tag'].'.rssfeed', array(
                'TITLE' => strip_tags($arrRssFeedData['title'],'<b><i>'),
                'TITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['title'],'<b><i>')),
                'SUBTITLE' => strip_tags($arrRssFeedData['subtitle'],'<b><i>'),
                'SUBTITLE_CLEANED' => htmlentities(strip_tags($arrRssFeedData['subtitle'],'<b><i>')),
                'LINK' => $arrRssFeedData['link']
                ));
      }
      $arrRssFeedPassed[$rssEntry_fields['feedid']] = true;
    }
    
    $intCpt++;
    
    if($intCpt<=$arrRssFeedData['limit'])
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
      
      // Flux standard
      $template_body->assign_block_vars('rssfeed.rssentry', array(
                  'TITLE' => strip_tags($rssEntry_fields['title'],'<b><i>'),
                  'TITLE_CLEAN' => htmlentities(strip_tags($rssEntry_fields['title'],'<b><i>')),
                  'SUBTITLE' => strip_tags($rssEntry_fields['subtitle'],'<b><i>'),
                  'SUBTITLE_CLEAN' => htmlentities(strip_tags($rssEntry_fields['subtitle'],'<b><i>')),
                  'PUBLISHED_DATE' => $published_date,
                  'PUBLISHED_TIME' => $published_time,
                  'LINK' => $rssEntry_fields['link'],
                  'CONTENT' => strip_tags($rssEntry_fields['content'], '<b><i><a>'),
                  'CONTENT_CLEAN' => htmlentities(strip_tags($rssEntry_fields['content'], '<b><i><a>')),
                  'CONTENT_CUT' => ploopi_strcut(strip_tags($rssEntry_fields['content']),200)
                  ));
    
      // Flux avec tag particulier pour le template
      if($booFeedTag)
      {
        $template_body->assign_block_vars($arrRssFeedData['tpl_tag'].'.rssfeed.rssentry', array(
                    'TITLE' => strip_tags($rssEntry_fields['title'],'<b><i>'),
                    'TITLE_CLEAN' => htmlentities(strip_tags($rssEntry_fields['title'],'<b><i>')),
                    'SUBTITLE' => strip_tags($rssEntry_fields['subtitle'],'<b><i>'),
                    'SUBTITLE_CLEAN' => htmlentities(strip_tags($rssEntry_fields['subtitle'],'<b><i>')),
                    'PUBLISHED_DATE' => $published_date,
                    'PUBLISHED_TIME' => $published_time,
                    'LINK' => $rssEntry_fields['link'],
                    'CONTENT' => strip_tags($rssEntry_fields['content'], '<b><i><a>'),
                    'CONTENT_CLEAN' => htmlentities(strip_tags($rssEntry_fields['content'], '<b><i><a>')),
                    'CONTENT_CUT' => ploopi_strcut(strip_tags($rssEntry_fields['content']),200)
                    ));
      }
    }
  }
}
unset($intCpt);
unset($intIdFeedTmp);
unset($arrRssFeedPassed);
?>