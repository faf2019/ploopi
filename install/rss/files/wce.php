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
 * Gestion des objets insérables dans une page de contenu (WebEdit)
 *
 * @package rss
 * @subpackage wce
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss');

include_once './modules/rss/class_rss_feed.php';
include_once './modules/rss/class_rss_filter.php';
include_once './modules/rss/class_rss_cat.php';
include_once './lib/template/template.php';

if (!empty($_REQUEST['op'])) $op = $_REQUEST['op'];

global $template_name;

$rss_moduleid = $obj['module_id']; // vient de l'appel de webedit.

$template_rss = new Template("./templates/frontoffice/{$template_name}");

$tplfile = '';

if (file_exists("./templates/frontoffice/{$template_name}/rss_display.tpl")) $tplfile = 'rss_display.tpl';
elseif (file_exists("./templates/frontoffice/{$template_name}/rss.tpl")) $tplfile = 'rss.tpl';

if ($tplfile != '')
{
    $template_rss->set_filenames(array('rss_display' => $tplfile));
    switch($op)
    {
        case 'display':
            $rssfeed_id = $obj['object_id'];
            $rssfeed = new rss_feed();
            $rssfeed->open($rssfeed_id);

            if (!empty($rssfeed->fields['url']))
            {
                if (!$rssfeed->isuptodate()) $rssfeed->updatecache();

                $where = '';

                $select =   "
                                SELECT      ploopi_mod_rss_entry.*,
                                            ploopi_mod_rss_feed.title AS titlefeed,
                                            ploopi_mod_rss_feed.link AS linkfeed

                                FROM        ploopi_mod_rss_entry,
                                            ploopi_mod_rss_feed

                                WHERE       ploopi_mod_rss_feed.id = ploopi_mod_rss_entry.id_feed
                                AND         ploopi_mod_rss_entry.id_feed = {$rssfeed_id}
                                ORDER BY    ploopi_mod_rss_entry.published DESC,
                                            ploopi_mod_rss_entry.timestp DESC,
                                            ploopi_mod_rss_entry.id
                                ";
                if($rssfeed->fields['limit']>0)
                  $select .= " LIMIT 0,{$rssfeed->fields['limit']}";
                else
                  $select .= " LIMIT 0,{$_SESSION['ploopi']['modules'][$rss_moduleid ]['nbitemdisplay']}";

                $result = $db->query($select);

                while ($fields = $db->fetchrow($result))
                {
                  if (!empty($fields['published']) && is_numeric($fields['published']))
                  {
                    $published_date = date(_PLOOPI_DATEFORMAT,$fields['published']);
                    $published_time = date(_PLOOPI_TIMEFORMAT,$fields['published']);
                  }
                  else
                  {
                    $date = ploopi_timestamp2local($fields['timestp']);
                    $published_date = $date['date'];
                    $published_time = $date['time'];
                  }

                  $template_rss->assign_block_vars('rss_entry',array(
                              'ID' => $fields['id'],
                              'TITLE' => strip_tags($fields['title'], '<b><i>'),
                              'SUBTITLE' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'TITLE_CLEAN' => htmlentities(strip_tags($fields['title'], '<b><i>')),
                              'SUBTITLE_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'DESCRIPTION' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'DESCRIPTION_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'CONTENT' => strip_tags($fields['content'], '<b><i><a>'),
                              'CONTENT_CLEAN' => htmlentities(strip_tags($fields['content'], '<b><i><a>')),
                              'CONTENT_CUT' => ploopi_strcut(strip_tags($fields['content']),200),
                              'LINK' => $fields['link'],
                              'FEED_TITLE' => $fields['titlefeed'],
                              'FEED_LINK' => $fields['linkfeed'],
                              'PUBLISHED_DATE' => $published_date,
                              'PUBLISHED_TIME' => $published_time
                              )
                          );
                }
            }
        break;
        case 'display_categ':
            $intRssCat_id = $obj['object_id'];
            $objRssCat = new rss_cat();
            $objRssCat->open($intRssCat_id);

            if (!empty($objRssCat->fields['id']))
            {
               $objRssCat->updateFeedByCat();

               $where = '';

               $select =   "
                                SELECT      ploopi_mod_rss_entry.*,
                                            ploopi_mod_rss_feed.title AS titlefeed,
                                            ploopi_mod_rss_feed.link AS linkfeed

                                FROM        ploopi_mod_rss_entry,
                                            ploopi_mod_rss_feed

                                WHERE       ploopi_mod_rss_feed.id = ploopi_mod_rss_entry.id_feed
                                AND         ploopi_mod_rss_entry.id_feed = ploopi_mod_rss_feed.id
                                AND         ploopi_mod_rss_feed.id_cat = {$intRssCat_id}
                                ORDER BY    ploopi_mod_rss_entry.published DESC,
                                            ploopi_mod_rss_entry.timestp DESC,
                                            ploopi_mod_rss_entry.id
                                ";
                if($objRssCat->fields['limit']>0)
                  $select .= " LIMIT 0,{$objRssCat->fields['limit']}";
                else
                  $select .= " LIMIT 0,{$_SESSION['ploopi']['modules'][$rss_moduleid ]['nbitemdisplay']}";

               $result = $db->query($select);

               while ($fields = $db->fetchrow($result))
               {
                  if (!empty($fields['published']) && is_numeric($fields['published']))
                  {
                    $published_date = date(_PLOOPI_DATEFORMAT,$fields['published']);
                    $published_time = date(_PLOOPI_TIMEFORMAT,$fields['published']);
                  }
                  else
                  {
                    $date = ploopi_timestamp2local($fields['timestp']);
                    $published_date = $date['date'];
                    $published_time = $date['time'];
                  }

                  $template_rss->assign_block_vars('rss_entry',array(
                              'ID' => $fields['id'],
                              'TITLE' => strip_tags($fields['title'], '<b><i>'),
                              'SUBTITLE' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'TITLE_CLEAN' => htmlentities(strip_tags($fields['title'], '<b><i>')),
                              'SUBTITLE_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'DESCRIPTION' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'DESCRIPTION_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'CONTENT' => strip_tags($fields['content'], '<b><i><a>'),
                              'CONTENT_CLEAN' => htmlentities(strip_tags($fields['content'], '<b><i><a>')),
                              'CONTENT_CUT' => ploopi_strcut(strip_tags($fields['content']),200),
                              'LINK' => $fields['link'],
                              'FEED_TITLE' => $fields['titlefeed'],
                              'FEED_LINK' => $fields['linkfeed'],
                              'PUBLISHED_DATE' => $published_date,
                              'PUBLISHED_TIME' => $published_time
                              )
                          );
               }
            }
          break;
        case 'display_filter':
            $rssfilter_id = $obj['object_id'];

            $objRssFilter = new rss_filter();
            $objRssFilter->open($rssfilter_id);

            $objRssFilter->updateFeedByFilter();

            $strSql = $objRssFilter->makeRequest();

            if (!empty($strSql))
            {
                $result = $db->query($strSql);

                while ($fields = $db->fetchrow($result))
                {
                  if (!empty($fields['published']) && is_numeric($fields['published']))
                  {
                      $published_date = date(_PLOOPI_DATEFORMAT,$fields['published']);
                      $published_time = date(_PLOOPI_TIMEFORMAT,$fields['published']);
                  }
                  else
                  {
                    $date = ploopi_timestamp2local($fields['timestp']);
                    $published_date = $date['date'];
                    $published_time = $date['time'];
                  }

                  $template_rss->assign_block_vars('rss_entry',array(
                              'ID' => $fields['id'],
                              'TITLE' => strip_tags($fields['title'], '<b><i>'),
                              'SUBTITLE' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'TITLE_CLEAN' => htmlentities(strip_tags($fields['title'], '<b><i>')),
                              'SUBTITLE_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'DESCRIPTION' => strip_tags($fields['subtitle'], '<b><i><a>'),
                              'DESCRIPTION_CLEAN' => htmlentities(strip_tags($fields['subtitle'], '<b><i><a>')),
                              'CONTENT' => strip_tags($fields['content'], '<b><i><a>'),
                              'CONTENT_CLEAN' => htmlentities(strip_tags($fields['content'], '<b><i><a>')),
                              'CONTENT_CUT' => ploopi_strcut(strip_tags($fields['content']),200),
                              'LINK' => $fields['link'],
                              'FEED_TITLE' => $fields['titlefeed'],
                              'FEED_LINK' => $fields['linkfeed'],
                              'PUBLISHED_DATE' => $published_date,
                              'PUBLISHED_TIME' => $published_time
                              )
                          );
                }
            }
          break;
    }

    $template_rss->pparse('rss_display');
}
else echo "<b>ERREUR : template rss manquant !</b>";
?>
