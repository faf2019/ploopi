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

ploopi_init_module('rss');

include_once('./modules/rss/class_rss_feed.php');
include_once './lib/template/template.php';
//include_once('./modules/rss/class_rssrequest.php');

if (!empty($_REQUEST['op'])) $op = $_REQUEST['op'];

global $template_name;

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
                                            ploopi_mod_rss_feed.title AS feed_title,
                                            ploopi_mod_rss_feed.link AS feed_link

                                FROM        ploopi_mod_rss_entry,
                                            ploopi_mod_rss_feed

                                WHERE       ploopi_mod_rss_feed.id = ploopi_mod_rss_entry.id_feed
                                AND         ploopi_mod_rss_entry.id_feed = {$rssfeed_id}
                                ORDER BY    ploopi_mod_rss_entry.published DESC, ploopi_mod_rss_entry.timestp DESC, ploopi_mod_rss_entry.id
                                LIMIT       0,10
                                ";

                $result = $db->query($select);

                $opened=false;
                $titlecat="";

                while ($fields = $db->fetchrow($result))
                {
                    $ld = ploopi_timestamp2local($fields['timestp']);
                    $published = ploopi_unixtimestamp2local($fields['published']);

                    $template_rss->assign_block_vars('rss_entry',array(
                                'ID' => $fields['id'],
                                'TITLE' => strip_tags($fields['title'], '<b><i>'),
                                'SUBTITLE' => strip_tags($fields['subtitle'], '<b><i><a>'),
                                'DESCRIPTION' => strip_tags($fields['subtitle'], '<b><i><a>'),
                                'CONTENT' => strip_tags($fields['content'], '<b><i><a>'),
                                'LINK' => $fields['link'],
                                'FEED_TITLE' => $fields['feed_title'],
                                'FEED_LINK' => $fields['feed_link'],
                                'PUBLISHED_DATE' => substr($published, 0, 10),
                                'PUBLISHED_TIME' => substr($published, 10, 5)
                                )
                            );
                }
            }
        break;

        /*
         * case 'request':
            $rssrequest_id = $obj['object_id'];
            $rssrequest = new rssrequest();
            $rssrequest->open($rssrequest_id);

            $request_title = str_replace('<OR>','OU',$rssrequest->fields['request']);
            $request_title = str_replace('<AND>','ET',$request_title);

            $array_request = explode('<OR>',$rssrequest->fields['request']);
            $keywords = array();

            foreach($array_request as $key1 => $value1)
            {
                $array_request[$key1] = trim($array_request[$key1],' ');
                $array_request[$key1] = explode('<AND>',$array_request[$key1]);
                foreach($array_request[$key1] as $key2 => $value2)
                {
                    $array_request[$key1][$key2] = trim($array_request[$key1][$key2] ,' ');
                    $keywords[] = $array_request[$key1][$key2];
                }

            }

            $search = '(';
            $pattern = "(UCASE(ploopi_mod_rss_entry.title) LIKE UCASE('%<KEYWORD>%') OR UCASE(ploopi_mod_rss_entry.description) LIKE UCASE('%<KEYWORD>%'))";

            foreach($array_request as $keyOR => $valueOR)
            {
                if ($keyOR > 0) $search .= ') OR (';
                foreach($array_request[$keyOR] as $keyAND => $valueAND)
                {
                    if ($keyAND > 0) $search .= ' AND ';
                    $search .= str_replace('<KEYWORD>',$valueAND,$pattern);
                }
            }

            $search .= ')';

            $where = " AND ($search)";

            if ($rssrequest->fields['id_cat'] != 0)
            {
                $where .= " AND ploopi_mod_rss_feed.id_cat = {$rssrequest->fields['id_cat']}";
            }

            $select =   "
                    SELECT      ploopi_mod_rss_entry.*,
                                ploopi_mod_rss_feed.title AS feed_title,
                                ploopi_mod_rss_feed.link AS feed_link
                    FROM        ploopi_mod_rss_entry,
                                ploopi_mod_rss_feed
                    WHERE       ploopi_mod_rss_feed.id = ploopi_mod_rss_entry.id_feed
                                $where
                    ORDER BY    timestp DESC, id
                    LIMIT       0,10
                    ";

            $db->query($select);
            $feed_title = '';

            while ($rsscache_fields = $db->fetchrow())
            {
                $ld = ploopi_timestamp2local($rsscache_fields['timestp']);

                foreach($keywords as $key => $word)
                {
                    $rsscache_fields['title'] = eregi_replace("($word)", "<b>\\1</b>", $rsscache_fields['title']);
                    $rsscache_fields['description'] = eregi_replace("($word)", "<b>\\1</b>", $rsscache_fields['description']);
                }
                $template_rss->assign_block_vars('rss',array(
                        'ID' => $rsscache_fields['id'],
                        'TITLE' => $rsscache_fields['title'],
                        'LINK' => $rsscache_fields['link'],
                        'DESCRIPTION' => ploopi_make_links($rsscache_fields['description']),
                        'FEED_TITLE' => $rsscache_fields['feed_title'],
                        'FEED_LINK' => $rsscache_fields['feed_link'],
                        'DATE' => $ld['date'],
                        'TIME' => $ld['time']
                        )
                    );

            }

        break;
        */
    }

    $template_rss->pparse('rss_display');
}
else echo "<b>ERREUR : template rss manquant !</b>";
?>
