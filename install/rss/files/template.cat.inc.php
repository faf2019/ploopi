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
$intIdCatTmp = 0;

$rssentry_select =  "
      SELECT      entry.*,
                  feed.id as feedid,
                  cat.id as catid,
                  cat.title as cattitle,
                  cat.description as catdescription,
                  IF(cat.limit>0,cat.limit,{$_SESSION['ploopi']['modules'][$template_moduleid]['nbitemdisplay']}) as catlimit,
                  cat.tpl_tag as cattpltag

      FROM        ploopi_mod_rss_cat cat,
                  ploopi_mod_rss_feed feed,
                  ploopi_mod_rss_entry entry

      WHERE       feed.id_cat = cat.id
        AND       entry.id_feed = feed.id
        AND       cat.id_workspace IN ({$wk})
        AND       cat.tpl_tag IS NOT NULL
        AND       cat.tpl_tag <> ''

      ORDER BY    cat.title DESC,
                  entry.published DESC,
                  entry.timestp DESC,
                  entry.id
      ";

$rssentry_result = $db->query($rssentry_select);
while ($rssEntry_fields = $db->fetchrow($rssentry_result))
{
  // si les mess sont déjà chargé a leur limite et qu'on est toujours dans la meme categorie on ne fait rien
  if($intCpt <= $rssEntry_fields['catlimit'] || $intIdCatTmp !== $rssEntry_fields['catid'])
  {
    $arrRssFeedData = $arrListFeed[$rssEntry_fields['feedid']];

    if($intIdCatTmp !== $rssEntry_fields['catid'])
    {
      // Tag categorie
      $template_body->assign_block_vars($rssEntry_fields['cattpltag'], array());

      $template_body->assign_block_vars($rssEntry_fields['cattpltag'].'.rsscat', array(
            'TITLE' => strip_tags($rssEntry_fields['cattitle'],'<b><i>'),
            'TITLE_CLEANED' => htmlentities(strip_tags($rssEntry_fields['cattitle'],'<b><i>')),
            'DESCRIPTION' => strip_tags($rssEntry_fields['catdescription'],'<b><i>'),
            'DESCRIPTION_CLEANED' => htmlentities(strip_tags($rssEntry_fields['catdescription'],'<b><i>'))
            ));
      $intIdCatTmp = $rssEntry_fields['catid'];
      $intCpt = 0;
    }

    if($intCpt <= $rssEntry_fields['catlimit'])
    {
      $intCpt++;

      if (!empty($rssEntry_fields['published']) && is_numeric($rssEntry_fields['published']))
      {
          $published_date = date(_PLOOPI_DATEFORMAT,$rssEntry_fields['published']);
          $published_time = date(_PLOOPI_TIMEFORMAT,$rssEntry_fields['published']);
      }
      else
      {
          $published_date = $published_time = '';
      }

      $template_body->assign_block_vars($rssEntry_fields['cattpltag'].'.rsscat.rssentry', array(
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
}
unset($intCpt);
unset($intIdCatTmp);
?>