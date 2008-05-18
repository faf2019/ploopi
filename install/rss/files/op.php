<?php
/*
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
 * Op�rations
 *
 * @package rss
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * On v�rifie qu'on est bien dans le module RSS et on teste les diff�rentes op�rations possibles.
 */

if (ploopi_ismoduleallowed('rss'))
{
    switch($ploopi_op)
    {
        case 'rss_explorer_catlist_get':
            ploopi_init_module('rss');

            if (isset($_GET['rsscat_id']) && is_numeric($_GET['rsscat_id']))
            {
                if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] == $_GET['rsscat_id']) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] = ''; // reset
                else $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] = $_GET['rsscat_id'];
                $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] = '';
            }

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $sql =  "
                    SELECT      count(feed.id) as nbfeeds, IFNULL(cat.id,0) as id, IFNULL(cat.title, '"._RSS_LABEL_NOCATEGORY."') as title
                    FROM        ploopi_mod_rss_feed feed
                    LEFT JOIN   ploopi_mod_rss_cat cat
                    ON          cat.id = feed.id_cat
                    AND         cat.id_workspace IN ({$wk})
                    WHERE       feed.id_workspace IN ({$wk})
                    GROUP BY    cat.id
                    ";

            $db->query($sql);
            $arrCat = $db->getarray();

            $array_columns = array();
            $array_values = array();

            $array_columns['auto']['title'] = array('label' => _RSS_LABEL_CATEGORY, 'options' => array('sort' => true));
            $array_columns['right']['nb'] = array('label' => _RSS_LABEL_FEEDS, 'width' => 55, 'options' => array('sort' => true));

            $c = 0;
            foreach($arrCat as $cat)
            {
                $array_values[$c]['values']['title'] = array('label' => $cat['title']);
                $array_values[$c]['values']['nb'] = array('label' => $cat['nbfeeds']);
                $array_values[$c]['description'] = $cat['title'];

                $array_values[$c]['link'] = "javascript:void(0);";
                $array_values[$c]['onclick'] = "javascript:rss_explorer_catlist_choose({$cat['id']})";

                if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] == $cat['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
                //else $array_values[$c]['style'] = '';
                $c++;
            }

            echo $skin->open_simplebloc();
            ?>
            <h1>Cat�gories de Flux</h1>
            <?
            $skin->display_array($array_columns, $array_values, 'array_rssexplorer_catlist', array('height' => 200, 'sortable' => true, 'orderby_default' => 'title'));
            echo $skin->close_simplebloc();

            ploopi_die();
        break;


        case 'rss_explorer_feedlist_get':
            ploopi_init_module('rss');

            if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] == '') ploopi_die();

            if (isset($_GET['rssfeed_id']) && is_numeric($_GET['rssfeed_id']))
            {
                if ($_GET['rssfeed_id'] == $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id']) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] = ''; // reset
                else $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] = $_GET['rssfeed_id'];
            }

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $sql =  "
                    SELECT      *
                    FROM        ploopi_mod_rss_feed feed
                    WHERE       feed.id_workspace IN ({$wk})
                    AND         feed.id_cat = '".$db->addslashes($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'])."'
                    ORDER BY    feed.title
                    ";

            $db->query($sql);
            $arrFeed = $db->getarray();



            $array_columns = array();
            $array_values = array();

            $array_columns['auto']['title'] = array('label' => _RSS_LABEL_TITLE, 'options' => array('sort' => true));

            $c = 0;
            foreach($arrFeed as $feed)
            {
                $array_values[$c]['values']['title'] = array('label' => strip_tags($feed['title'], '<b><i>'));
                $array_values[$c]['description'] = $feed['title'];

                $array_values[$c]['link'] = "javascript:void(0);";
                $array_values[$c]['onclick'] = "javascript:rss_explorer_feedlist_choose({$feed['id']})";

                if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] == $feed['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
                $c++;
            }

            echo $skin->open_simplebloc();
            ?>
            <h1>Liste des Flux</h1>
            <?
            $skin->display_array($array_columns, $array_values, 'array_rssexplorer_feedlist', array('height' => 250, 'sortable' => true, 'orderby_default' => 'title'));
            echo $skin->close_simplebloc();

            ploopi_die();
        break;

        case 'rss_explorer_feed_get':
            ploopi_init_module('rss');

            if (isset($_GET['rss_search_kw']) && $_GET['rss_search_kw'] != '%%undefined%%') $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'] = $_GET['rss_search_kw'];

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $rsscat_id = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] : '';
            $rssfeed_id = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] : '';
            $rss_search_kw = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'] : '';

            $is_search = false;

            $arrWhere = array();

            if (!empty($rss_search_kw))
            {
                if (substr($rss_search_kw,0,6) == 'entry:') // direct access to entry
                {
                    $arrWhere[] = "entry.id = '".substr($rss_search_kw,-32,32)."'";
                }
                else
                {
                    $is_search = true;

                    $id_record = '';
                    if ($rsscat_id != '') $id_record = sprintf("%06d", $rsscat_id);
                    if ($rssfeed_id != '') $id_record .= sprintf("%06d", $rssfeed_id);

                    $arrRelevance = ploopi_search($rss_search_kw, _RSS_OBJECT_NEWS_ENTRY, $id_record, $_SESSION['ploopi']['moduleid'], array('orderby' => 'relevance', 'sort' => 'DESC', 'relevance_min' => 10));

                    $i = 0;
                    $arrEntryId = array();

                    while ($i<25 && $i<sizeof($arrRelevance))
                    {
                        $e = current($arrRelevance);
                        $arrEntryId[substr($e['id_record'],-32,32)] = $e['id_record'];
                        next($arrRelevance);
                        $i++;
                    }

                    $arrWhere[] = "entry.id IN ('".implode("','", array_keys($arrEntryId))."')";
                }
            }

            if ($rsscat_id != '') $arrWhere[] = "IFNULL(cat.id, 0) = {$rsscat_id}";
            if ($rssfeed_id != '') $arrWhere[] = "feed.id = {$rssfeed_id}";

            $where = (!empty($arrWhere)) ? ' WHERE '.implode(' AND ', $arrWhere) : '';

            $sql =  "
                    SELECT      entry.*,
                                feed.title as titlefeed,
                                IFNULL(cat.id, 0) as id_cat,
                                IFNULL(cat.title, '"._RSS_LABEL_NOCATEGORY."') as titlecat
                    FROM        ploopi_mod_rss_entry entry

                    INNER JOIN  ploopi_mod_rss_feed feed
                    ON          feed.id = entry.id_feed
                    AND         feed.id_workspace IN ({$wk})

                    LEFT JOIN   ploopi_mod_rss_cat cat
                    ON          cat.id = feed.id_cat
                    AND         cat.id_workspace IN ({$wk})

                    {$where}

                    ORDER BY    entry.published DESC
                    LIMIT       0,25
                    ";

            $db->query($sql);

            $arrEntry = $db->getarray();

            $title = '';

            if ($rsscat_id != '')
            {
                include_once './modules/rss/class_rss_cat.php';
                $rsscat = new rss_cat();
                if ($rsscat_id == 0) $title .= "&nbsp;&raquo;&nbsp;"._RSS_LABEL_NOCATEGORY;
                elseif ($rsscat->open($rsscat_id)) $title .= "&nbsp;&raquo;&nbsp;{$rsscat->fields['title']}";
            }
            if ($rssfeed_id != '')
            {
                include_once './modules/rss/class_rss_feed.php';
                $rssfeed = new rss_feed();
                if ($rssfeed->open($rssfeed_id))
                {
                    $title .= "&nbsp;&raquo;&nbsp;{$rssfeed->fields['title']}";
                    if (!$rssfeed->isuptodate()) $rssfeed->updatecache();
                }
            }

            $numrow = 1;

            echo $skin->open_simplebloc();
            ?>
            <h1>Actualit�s<? echo $title; ?></h1>
            <div id="rss_explorer_feed_content">
                <?
                foreach($arrEntry as $entry)
                {
                    if ($is_search)
                    {
                        $rel = $arrRelevance[$arrEntryId[$entry['id']]]['relevance'];

                        $blue = 128;
                        if ($rel>=50)
                        {
                            $red = 255-($blue*($rel-50))/50;
                            $green = 255;
                        }
                        else
                        {
                            $red = 255;
                            $green = (255-$blue)+($blue*$rel)/50;
                        }

                        $color = sprintf("%02X%02X%02X",$red,$green,$blue);
                    }

                    ?>
                    <div class="rss_entry">
                        <a class="rss_entry<? echo (++$numrow)%2; ?>" href="<? echo $entry['link']; ?>" target="_blank">
                            <?
                            if ($is_search)
                            {
                                ?><span style="float:right;border:1px solid #a0a0a0;background-color:#<? echo $color; ?>;margin:4px;padding:4px;font-weight:bold;"><? printf("%d %%", $rel); ?></span><?
                            }
                            ?>
                            <b><? echo strip_tags($entry['title'], '<b><i>'); ?></b>
                            <br /><i><? echo ploopi_unixtimestamp2local($entry['published']); ?> &#149; <? echo $entry['titlefeed']; ?></i>
                        <?
                        if (!empty($entry['subtitle']))
                        {
                            ?>
                            <br /><? echo strip_tags($entry['subtitle'], '<b><i>'); ?>
                            <?
                        }
                        if (!empty($entry['content']))
                        {
                            ?>
                            <br /><? echo strip_tags($entry['content'], '<b><i>'); ?>
                            <?
                        }
                        ?>
                        </a>
                    </div>
                    <div class="rss_entry_annotation">
                    <?
                    ploopi_annotation(_RSS_OBJECT_NEWS_ENTRY, sprintf("%06d%06d%s", $entry['id_cat'], $entry['id_feed'], $entry['id']), strip_tags($entry['title'], '<b><i>'));
                    ?>
                    </div>
                    <?
                }
                ?>
            </div>
            <?
            echo $skin->close_simplebloc();

            ploopi_die();
        break;

    }
}
?>
