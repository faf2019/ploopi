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
include_once './modules/rss/class_rss_filter.php';

$rssfilter_select =   "
                    SELECT      ploopi_mod_rss_filter.id
                    FROM        ploopi_mod_rss_filter
                    WHERE       ploopi_mod_rss_filter.id_workspace IN ({$wk})
                      AND       ploopi_mod_rss_filter.tpl_tag IS NOT NULL
                      AND       ploopi_mod_rss_filter.tpl_tag <> ''
                    ";

$rssfilter_result = $db->query($rssfilter_select);

while ($rssfilter_fields = $db->fetchrow($rssfilter_result))
{
  $objRssFilter = new rss_filter();
  $objRssFilter->open($rssfilter_fields['id']);

  $strSql = $objRssFilter->makeRequest(0,true,$template_moduleid);

  if (!empty($strSql))
  {
    $result = $db->query($strSql);
    if($result)
    {
      $template_body->assign_block_vars($objRssFilter->fields['tpl_tag'],array());

      $template_body->assign_block_vars($objRssFilter->fields['tpl_tag'].'.rssfilter',array(
            'TITLE' => strip_tags($objRssFilter->fields['title'], '<b><i>'),
            'TITLE_CLEANED' => htmlentities(strip_tags($objRssFilter->fields['title'], '<b><i>'))
            ));
    }

    while ($fields = $db->fetchrow($result))
    {
      if (!empty($fields['published']) && is_numeric($fields['published']))
      {
          $published_date = date(_PLOOPI_DATEFORMAT,$fields['published']);
          $published_time = date(_PLOOPI_TIMEFORMAT,$fields['published']);
      }
      else
      {
          $published_date = $published_time = '';
      }

      $template_body->assign_block_vars($objRssFilter->fields['tpl_tag'].'.rssfilter.rssentry',array(
                  'FEED_TITLE' => strip_tags($fields['titlefeed'],'<b><i>'),
                  'FEED_TITLE_CLEANED' => htmlentities(strip_tags($fields['titlefeed'],'<b><i>')),
                  'FEED_SUBTITLE' => strip_tags($fields['subtitlefeed'],'<b><i>'),
                  'FEED_SUBTITLE_CLEANED' => htmlentities(strip_tags($fields['subtitlefeed'],'<b><i>')),
                  'FEED_LINK' => $fields['linkfeed'],
                  'TITLE' => strip_tags($fields['title'], '<b><i>'),
                  'TITLE_CLEANED' => htmlentities(strip_tags($fields['title'],'<b><i>')),
                  'SUBTITLE' => strip_tags($fields['subtitle'], '<b><i>'),
                  'SUBTITLE_CLEANED' => htmlentities(strip_tags($fields['subtitle'],'<b><i>')),
                  'PUBLISHED_DATE' => $published_date,
                  'PUBLISHED_TIME' => $published_time,
                  'LINK' => $fields['link'],
                  'CONTENT' => strip_tags($fields['content'], '<b><i><a>'),
                  'CONTENT_CLEANED' => htmlentities(strip_tags($fields['content'],'<b><i><a>')),
                  'CONTENT_CUT' => ploopi_strcut(strip_tags($fields['content']),200)
                  ));
    }
  }
}
?>