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
 * Affichage du bloc de menu
 *
 * @package rss
 * @subpackage block
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss', false, false, false);

include_once './modules/rss/class_rss_feed.php';

$wk = ploopi_viewworkspaces($menu_moduleid);

$title = '';

if (ploopi_isactionallowed(array(_RSS_ACTION_FEEDADD, _RSS_ACTION_FEEDMODIFY, _RSS_ACTION_FEEDDELETE, _RSS_ACTION_CATADD, _RSS_ACTION_CATMODIFY, _RSS_ACTION_CATDELETE), $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<b>'._RSS_LABEL_ADMIN.'</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'admin');
}

$block->addmenu('<b>'._RSS_LABEL_SEARCH.'</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"), $_SESSION['ploopi']['moduleid'] == $menu_moduleid && $_SESSION['ploopi']['action'] == 'public');

/* Flux */
$rssfeed_select =   "
    SELECT      feed.*,
                cat.title as titlecat
    FROM        ploopi_mod_rss_feed feed
    LEFT JOIN   ploopi_mod_rss_cat cat ON cat.id = feed.id_cat
    WHERE       feed.id_module = {$menu_moduleid}
    AND         feed.id_workspace IN ({$wk})
    ORDER BY    feed.title
";

$rssfeed_result = $db->query($rssfeed_select);

while ($rssfeed_fields = $db->fetchrow($rssfeed_result))
{
    $rss_feed = new rss_feed();
    if ($rss_feed->open($rssfeed_fields['id']))
    {
        if (!$rss_feed->isuptodate()) $rss_feed->updatecache();

        $block->addmenu("<b>{$rss_feed->fields['title']}</b>".(!empty($rss_feed->fields['subtitle']) ? '<br /><i>'.strip_tags($rss_feed->fields['subtitle'], '<b><i>').'</i>' : ''), $rss_feed->fields['link'], '', '_blank');

        $rssentry_select =  "
                            SELECT      ploopi_mod_rss_entry.*
                            FROM        ploopi_mod_rss_entry
                            WHERE       ploopi_mod_rss_entry.id_feed = {$rss_feed->fields['id']}
                            ORDER BY    published DESC, timestp DESC, id";

        if($rss_feed->fields['limit']>0)
          $rssentry_select .= " LIMIT 0,{$rss_feed->fields['limit']}";
        else
          $rssentry_select .= " LIMIT 0,{$_SESSION['ploopi']['modules'][$menu_moduleid]['nbitemdisplay']}";

        $rssentry_result = $db->query($rssentry_select);

        while($rssentry_row = $db->fetchrow($rssentry_result))
        {
            $ld = (!empty($rssentry_row['published']) && is_numeric($rssentry_row['published'])) ? ploopi_unixtimestamp2local($rssentry_row['published']) : '';

            $block->addmenu(strip_tags($rssentry_row['title'], '<b><i>').'<br />'.$ld, $rssentry_row['link'], '', '_blank');
        }
    }
    unset($rss_feed);
}

?>
