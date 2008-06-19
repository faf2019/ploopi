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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package rss
 * @subpackage template
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss');

include_once './modules/rss/class_rss_feed.php';

$rssfeed_select =   "
                    SELECT      ploopi_mod_rss_feed.id,
                                ploopi_mod_rss_cat.title as titlecat
                    FROM        ploopi_mod_rss_feed
                    LEFT JOIN   ploopi_mod_rss_cat ON ploopi_mod_rss_cat.id = ploopi_mod_rss_feed.id_cat
                    WHERE       ploopi_mod_rss_feed.id_module = {$template_moduleid}
                    AND         ploopi_mod_rss_feed.default = 1
                    ";

$rssfeed_result = $db->query($rssfeed_select);

while ($rssfeed_fields = $db->fetchrow($rssfeed_result))
{
    $rss_feed = new rss_feed();
    if ($rss_feed->open($rssfeed_fields['id']))
    {

        if (!$rss_feed->isuptodate()) $rss_feed->updatecache();

        $template_body->assign_block_vars('rssfeed', array(
                'TITLE' => strip_tags($rss_feed->fields['title'],'<b><i>'),
                'SUBTITLE' => strip_tags($rss_feed->fields['subtitle'],'<b><i>'),
                'TITLE_CLEANED' => htmlentities(strip_tags($rss_feed->fields['title'])),
                'SUBTITLE_CLEANED' => htmlentities(strip_tags($rss_feed->fields['subtitle'])),
                'LINK' => $rss_feed->fields['link']
                ));

        $rsscache_select =  "
                            SELECT      ploopi_mod_rss_entry.*
                            FROM        ploopi_mod_rss_entry
                            WHERE       ploopi_mod_rss_entry.id_feed = {$rss_feed->fields['id']}
                            ORDER BY    published DESC, timestp DESC, id
                            LIMIT       0,10
                            ";

        $rsscache_result = $db->query($rsscache_select);

        while($rsscache_fields = $db->fetchrow($rsscache_result))
        {
            if (!empty($rsscache_fields['published']) && is_numeric($rsscache_fields['published']))
            {
                $published_date = date(_PLOOPI_DATEFORMAT,$rsscache_fields['published']);
                $published_time = date(_PLOOPI_TIMEFORMAT,$rsscache_fields['published']);
            }
            else
            {
                $published_date = $published_time = '';
            }
            
            $template_body->assign_block_vars('rssfeed.rssentry', array(
                        'TITLE' => strip_tags($rsscache_fields['title'],'<b><i>'),
                        'SUBTITLE' => strip_tags($rsscache_fields['subtitle'],'<b><i>'),
                        'DATE' => $published_date,
                        'TIME' => $published_time,
                        'PUBLISHED_DATE' => $published_date,
                        'PUBLISHED_TIME' => $published_time,
                        'LINK' => $rsscache_fields['link']
                        ));
        }
    }
}


?>

