<?php
/*
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
 * Opérations
 *
 * @package rss
 * @subpackage op
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On vérifie qu'on est bien dans le module RSS et on teste les différentes opérations possibles.
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
                    SELECT      count(feed.id) as nbfeeds,
                                IFNULL(cat.id,0) as id,
                                IFNULL(cat.title, '"._RSS_LABEL_NOCATEGORY."') as title,
                                IFNULL(cat.limit,0) as limitcat,
                                IFNULL(cat.tpl_tag,'') as tpltagcat
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

            $array_columns['auto']['cat_title'] = array('label' => _RSS_LABEL_CATEGORY, 'options' => array('sort' => true));
            $array_columns['right']['cat_nb'] = array('label' => _RSS_LABEL_FEEDS, 'width' => 55, 'options' => array('sort' => true));
            $array_columns['right']['cat_limit'] = array('label' => _RSS_LABEL_LIMIT, 'width' => 55);

            $c = 0;
            foreach($arrCat as $cat)
            {
                $array_values[$c]['values']['cat_title'] = array('label' => $cat['title']);
                $array_values[$c]['values']['cat_nb'] = array('label' => $cat['nbfeeds']);
                $array_values[$c]['values']['cat_limit'] = array('label' => $cat['limitcat']);
                $array_values[$c]['description'] = $cat['title'];
                if($cat['tpltagcat'] != '') $array_values[$c]['description'] .= ' (tag: '.$cat['tpltagcat'].')';

                $array_values[$c]['link'] = "javascript:void(0);";
                $array_values[$c]['onclick'] = "javascript:rss_explorer_catlist_choose({$cat['id']})";

                if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'] == $cat['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
                $c++;
            }

            echo $skin->open_simplebloc();
            ?>
            <h1>Catégories de Flux</h1>
            <?php
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
                    SELECT      feed.*
                    FROM        ploopi_mod_rss_feed feed
                    WHERE       feed.id_workspace IN ({$wk})
                    AND         feed.id_cat = '".$db->addslashes($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rsscat_id'])."'
                    ORDER BY    feed.title
                    ";

            $db->query($sql);
            $arrFeed = $db->getarray();

            $array_columns = array();
            $array_values = array();

            $array_columns['auto']['feed_title'] = array('label' => _RSS_LABEL_TITLE, 'options' => array('sort' => true));
            $array_columns['right']['feed_limit'] = array('label' => _RSS_LABEL_LIMIT, 'width' => 55);

            $c = 0;
            foreach($arrFeed as $feed)
            {
                $array_values[$c]['values']['feed_title'] = array('label' => strip_tags($feed['title'], '<b><i>'));
                $array_values[$c]['values']['feed_limit'] = array('label' => $feed['limit']);
                $array_values[$c]['description'] = $feed['title'];

                $array_values[$c]['link'] = "javascript:void(0);";
                $array_values[$c]['onclick'] = "javascript:rss_explorer_feedlist_choose({$feed['id']})";

                if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfeed_id'] == $feed['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
                $c++;
            }

            echo $skin->open_simplebloc();
            ?>
            <h1>Liste des Flux</h1>
            <?php
            $skin->display_array($array_columns, $array_values, 'array_rssexplorer_feedlist', array('height' => 250, 'sortable' => true, 'orderby_default' => 'title'));
            echo $skin->close_simplebloc();

            ploopi_die();
        break;

        case 'rss_explorer_feed_get':
            ploopi_init_module('rss');

            include_once './modules/rss/class_rss_cat.php';
            include_once './modules/rss/class_rss_feed.php';

            if (isset($_GET['rss_search_kw']) && $_GET['rss_search_kw'] != '') $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rss_search_kw'] = $_GET['rss_search_kw'];

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

            if ($rsscat_id != '')
            {
              $arrWhere[] = "IFNULL(cat.id, 0) = {$rsscat_id}";
              // UPDATE des flux
              $objCat = new rss_cat();
              $objCat->updateFeedByCat($rsscat_id);
              unset($objCat);
            }
            if ($rssfeed_id != '')
            {
              $arrWhere[] = "feed.id = {$rssfeed_id}";
              // UPDATE du flux
              $objFeed = new rss_feed();
              $objFeed->open($rssfeed_id);
              if(!$objFeed->isuptodate()) $objFeed->updatecache();
              unset($objFeed);
            }

            $limit = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nbitemdisplay'];
            if ($rssfeed_id != '')
            {
              $objFeed = new rss_feed();
              if($objFeed->open($rssfeed_id) && $objFeed->fields['limit']>0) $limit = $objFeed->fields['limit'];
            }
            elseif ($rsscat_id != '')
            {
              $objCat = new rss_cat();
              if($objCat->open($rsscat_id) && $objCat->fields['limit']>0) $limit = $objCat->fields['limit'];
            }

            $where = (!empty($arrWhere)) ? ' WHERE '.implode(' AND ', $arrWhere) : '';

            $sql =  "
                    SELECT      entry.*,
                                feed.title as titlefeed,
                                IFNULL(cat.id, 0) as id_cat,
                                IFNULL(cat.title, '"._RSS_LABEL_NOCATEGORY."') as titlecat,
                                IFNULL(cat.limit, 0) as limitcat
                    FROM        ploopi_mod_rss_entry entry

                    INNER JOIN  ploopi_mod_rss_feed feed
                    ON          feed.id = entry.id_feed
                    AND         feed.id_workspace IN ({$wk})

                    LEFT JOIN   ploopi_mod_rss_cat cat
                    ON          cat.id = feed.id_cat
                    AND         cat.id_workspace IN ({$wk})

                    {$where}

                    ORDER BY    entry.published DESC
                    LIMIT       0,{$limit}
                    ";

            $db->query($sql);

            $arrEntry = $db->getarray();

            $title = '';

            if ($rsscat_id != '')
            {
                include_once './modules/rss/class_rss_cat.php';
                $objRssCat = new rss_cat();
                if ($rsscat_id == 0) $title .= "&nbsp;&raquo;&nbsp;"._RSS_LABEL_NOCATEGORY;
                elseif ($objRssCat->open($rsscat_id)) $title .= "&nbsp;&raquo;&nbsp;{$objRssCat->fields['title']}";
                unset($objRssCat);
            }
            if ($rssfeed_id != '')
            {
                $objRssFeed = new rss_feed();
                if ($objRssFeed->open($rssfeed_id))
                    $title .= "&nbsp;&raquo;&nbsp;{$objRssFeed->fields['title']}";
                unset($objRssFeed);
            }

            $numrow = 1;

            echo $skin->open_simplebloc();
            ?>
            <h1>Actualités<?php echo $title; ?></h1>
            <div id="rss_explorer_feed_content">
                <?php
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
                        <a class="rss_entry<?php echo (++$numrow)%2; ?>" href="<?php echo $entry['link']; ?>" target="_blank">
                            <?php
                            if ($is_search)
                            {
                                ?><span style="float:right;border:1px solid #a0a0a0;background-color:#<?php echo $color; ?>;margin:4px;padding:4px;font-weight:bold;"><?php printf("%d %%", $rel); ?></span><?php
                            }
                            ?>
                            <b><?php echo strip_tags($entry['title'], '<b><i>'); ?></b>
                            <br /><i><?php echo ploopi_unixtimestamp2local($entry['published']); ?> &#149; <?php echo $entry['titlefeed']; ?></i>
                        <?php
                        if (!empty($entry['subtitle']))
                        {
                            ?>
                            <br /><?php echo strip_tags($entry['subtitle'], '<b><i>'); ?>
                            <?php
                        }
                        if (!empty($entry['content']))
                        {
                            ?>
                            <br /><?php echo strip_tags($entry['content'], '<b><i>'); ?>
                            <?php
                        }
                        ?>
                        </a>
                    </div>
                    <div class="rss_entry_annotation">
                    <?php
                    ploopi_annotation(_RSS_OBJECT_NEWS_ENTRY, sprintf("%06d%06d%s", $entry['id_cat'], $entry['id_feed'], $entry['id']), strip_tags($entry['title'], '<b><i>'));
                    ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            echo $skin->close_simplebloc();

            ploopi_die();
        break;

// REQUEST /////////////////////////////////////////////////////////////////////////////
        case 'rss_filter_list_get':
            ploopi_init_module('rss');

            if (isset($_GET['rssfilter_id']) && is_numeric($_GET['rssfilter_id']))
            {
                if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] == $_GET['rssfilter_id'])
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = ''; // reset
                else
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = $_GET['rssfilter_id'];

                $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';
            }

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $sql =  "
                     SELECT     filter.*
                     FROM        ploopi_mod_rss_filter filter
                     WHERE       filter.id_workspace IN ({$wk})
                    ";
            $db->query($sql);
            $arrFilters = $db->getarray();

            $array_columns = array();
            $array_values = array();

            $array_columns['auto']['title'] = array('label' => _RSS_LABEL_LABEL, 'options' => array('sort' => true));
            $array_columns['right']['condition'] = array('label' => '', 'width' => 40);
            $array_columns['actions_right']['actions'] = array('label' => _RSS_LABEL_ACTIONS, 'width' => 60);

            $c = 0;
            foreach($arrFilters as $filter)
            {
              $actions = '';
              if (ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY)) $actions .= '<a title="Modifier" href="'.ploopi_urlencode("admin.php?op=rssfilter_modify&rssfilter_id={$filter['id']}").'"><img alt="Modifier" src="./modules/rss/img/ico_modify.png" /></a>';
              if (ploopi_isactionallowed(_RSS_ACTION_FILTERDELETE)) $actions .= '<a title="Supprimer" href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?op=rssfilter_delete&rssfilter_id={$filter['id']}").'\',\'Êtes-vous certain de vouloir supprimer ce filtre ?\');"><img alt="Supprimer" src="./modules/rss/img/ico_trash.png" /></a>';
              if (empty($actions)) $actions = '&nbsp;';

              $array_values[$c]['values']['title'] = array('label' => $filter['title']);
              $array_values[$c]['values']['condition'] = array('label' => (($filter['condition'] == 1) ? 'ET' : 'OU'));
              $array_values[$c]['values']['actions'] = array('label' => $actions);

              $array_values[$c]['description'] = $filter['title'];
              if($filter['tpl_tag'] != '') $array_values[$c]['description'] .= ' (tag: '.$filter['tpl_tag'].')';

              $array_values[$c]['link'] = "javascript:void(0);";
              $array_values[$c]['onclick'] = "javascript:rss_filter_list_choose({$filter['id']});";

              if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] == $filter['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';
              //else $array_values[$c]['style'] = '';
              $c++;
            }

            echo $skin->open_simplebloc();
            echo '<h1>'._RSS_SQL_LABEL_LIST.'</h1>';
            $skin->display_array($array_columns, $array_values, 'array_rss_filter_list', array('height' => 200, 'sortable' => true, 'orderby_default' => 'title'));
            echo $skin->close_simplebloc();
            ploopi_die();
        break;

        case 'rss_filter_element_list_get':
        case 'rss_filter_element_edit_list_get':
            if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] == '') ploopi_die();

            ploopi_init_module('rss');

            include_once './modules/rss/class_rss_filter_element.php';

            // bloc suivant uniquement pour 'rss_filter_element_list_get' car ploopi_op : rss_filter_element_edit est effectué avant.
            if ($ploopi_op == 'rss_filter_element_list_get' &&
                   isset($_GET['rssfilter_id_element']) && is_numeric($_GET['rssfilter_id_element']))
            {
                if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] == $_GET['rssfilter_id_element'])
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = ''; // reset
                else
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = $_GET['rssfilter_id_element'];
            }

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $sql =  "
                    SELECT      filter_element.id,
                                filter_element.id_filter
                    FROM        ploopi_mod_rss_filter_element filter_element
                    WHERE       filter_element.id_filter = '".$db->addslashes($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])."'
                    ORDER BY    filter_element.id
                    ";
            $db->query($sql);
            $arrFilterElements = $db->getarray();

            $array_columns = array();
            $array_values = array();

            $array_columns['auto']['condition'] = array('label' => _RSS_LABEL_FILTER_CONDITION, 'options' => array('sort' => true));
            $array_columns['actions_right']['actions'] = array('label' => _RSS_LABEL_ACTIONS, 'width' => 60);

            $c = 0;
            $objFilterElement = new rss_filter_element();
            foreach($arrFilterElements as $arrFilterElement)
            {
              if($objFilterElement->open($arrFilterElement['id']));
              {
                $actions = '';

                $arrElement = $objFilterElement->getElement();
                if($ploopi_op == 'rss_filter_element_list_get') // Changement de page vers edition
                {
                  $array_values[$c]['link'] = "javascript:void(0);";
                  $array_values[$c]['onclick'] = "javascript:rss_filter_element_list_choose({$arrFilterElement['id']})";

                  if (ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY)) $actions .= '<a title="Modifier" href="'.ploopi_urlencode("admin.php?op=rssfilter_modify&rssfilter_id={$arrFilterElement['id_filter']}&rssfilter_id_element={$arrFilterElement['id']}").'"><img alt="Modifier" src="./modules/rss/img/ico_modify.png" /></a>';
                  if (ploopi_isactionallowed(_RSS_ACTION_FILTERDELETE)) $actions .= '<a title="Supprimer" href="javascript:void(0);" onClick="javascript:if(confirm(\''._RSS_DELETE_ELEMENT.'\')) rssfilter_element_list_delete('.$arrFilterElement['id'].');"><img alt="Supprimer" src="./modules/rss/img/ico_trash.png" /></a>';
                }
                elseif($ploopi_op == 'rss_filter_element_edit_list_get') // Mise a jour du block d'édition d'un élément
                {
                  $array_values[$c]['link'] = "javascript:void(0);";

                  $array_values[$c]['onclick'] = "javascript:rss_filter_element_edit({$arrFilterElement['id']});";

                  if (ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY)) $actions .= '<a title="Modifier" href="javascript:void(0);" onClick="javascript:rss_filter_element_edit('.$arrFilterElement['id'].');"><img alt="Modifier" src="./modules/rss/img/ico_modify.png" /></a>';
                  if (ploopi_isactionallowed(_RSS_ACTION_FILTERDELETE)) $actions .= '<a title="Supprimer" href="javascript:void(0);" onClick="javascript:if(confirm(\''._RSS_DELETE_ELEMENT.'\')) rssfilter_element_edit_delete('.$arrFilterElement['id'].');"><img alt="Supprimer" src="./modules/rss/img/ico_trash.png" /></a>';
                }

                if (empty($actions)) $actions = '&nbsp;';

                if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] == $arrFilterElement['id']) $array_values[$c]['style'] = 'background-color:#ffe0e0;';

                $strValue = ($arrElement['target']['compare'] == 'date') ? substr(ploopi_unixtimestamp2local($arrElement['value']),0,10) : $arrElement['value'];

                $array_values[$c]['values']['condition'] = array('label' => $arrElement['target']['label'].' '.$arrElement['compare']['label'].' '.$strValue);
                $array_values[$c]['values']['actions'] = array('label' => $actions);

                $array_values[$c]['description'] = $arrElement['target']['value'].' '.str_replace('%t',$arrElement['value'],$arrElement['compare']['sql']);

                $c++;
              }
            }

            echo $skin->open_simplebloc();
            echo '<h1>'._RSS_SQL_LABEL_DETAIL.'</h1>';
            $skin->display_array($array_columns, $array_values, 'array_rss_request_list', array('height' => 200, 'sortable' => true, 'orderby_default' => 'label'));
            echo $skin->close_simplebloc();
            ploopi_die();
        break;

        case 'rss_filter_element_edit':
            ploopi_init_module('rss');

            if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] == '') ploopi_die();

            if (isset($_GET['rssfilter_id_element']) && is_numeric($_GET['rssfilter_id_element']))
            {
                if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] == $_GET['rssfilter_id_element'])
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = ''; // reset
                else
                  $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = $_GET['rssfilter_id_element'];
            }

            include_once './modules/rss/class_rss_filter_element.php';

            $objFilterElement = new rss_filter_element();

            $arrTabTarget  = $objFilterElement->getTabTarget();
            $arrTabCompare = $objFilterElement->getTabCompare();

            $action = 'admin.php?rssTabItem=tabFilter&op=rssfilter_element_save&rssfilter_id='.$_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'];

            echo $skin->open_simplebloc();
            if($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element']>0)
            {
              if($objFilterElement->open($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element']))
              {
                $arrElement = $objFilterElement->getElement();
                $action .= '&rssfilter_id_element='.$_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'];
              }
              else
                $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';
            }
            ?>
            <form name="form_rssfilter_element" action="<?php echo ploopi_urlencode($action); ?>" method="post" onSubmit="return rssfilter_element_validate();">
            <div style="padding: 4px 2px;">
              <button type="button" style="float:left;" onClick="javascript:rss_filter_element_edit(0);">Nouveau</button>
              <select id="rss_element_target" name="rss_element_target" style="width:25%;float:left;margin-left: 4px;" onChange="javascript:rss_select_target('div_compare','div_type_control',rss_element_target);">
              <?php
              $strTypeCompare = '';

              foreach($arrTabTarget as $strName => $arrDetail)
              {
                if($strTypeCompare == '') $strTypeCompare = $arrDetail['compare'];

                $strSelected = ((isset($arrElement) && $strName == $arrElement['target']['value'])) ? 'selected' : '';

                echo '<option value="'.$strName.'" '.$strSelected.'>'.$arrDetail['label'].'</option>';
              }
              ?>
              </select>
              <div id="div_compare" style="width:25%;float:left;">
              <select id="rss_element_compare" name="rss_element_compare" style="width:100%;">
              <?php
              if(isset($arrElement['target']['compare'])) $strTypeCompare = $arrElement['target']['compare'];

              foreach($arrTabCompare[$strTypeCompare] as $strName => $arrDetail)
              {
                $strSelected = (isset($arrElement) && $strName == $arrElement['compare']['value']) ? $strSelected = 'selected' : '';
                echo '<option value="'.$strName.'" '.$strSelected.'>'.$arrDetail['label'].'</option>';
              }
              ?>
              </select>
              </div>
              <div id="div_type_control"><input type="hidden" id="type_control" value="<?php echo $strTypeCompare; ?>" /></div>
              <?php
              $strValue = '';
              if(isset($arrElement))
              {
                 $strValue = ($arrElement['target']['compare'] == 'date') ? substr(ploopi_unixtimestamp2local($arrElement['value']),0,10) : $arrElement['value'];
              }
              ?>
              <input type="text" id="rss_element_value" name="rss_element_value" style="width:38%;float:left;margin-right: 4px;" value="<?php echo $strValue; ?>">
              <input type="submit" class="button" value="<?php echo _PLOOPI_SAVE; ?>" />
            </div>
            </form>
            <?php
            echo $skin->close_simplebloc();
            ploopi_die();
        break;

        case 'rss_filter_feed_get' :
            ploopi_init_module('rss');

            // Mise à jour de tous les flux
            include_once './modules/rss/class_rss_feed.php';
            $objFeed = new rss_feed();
            $objFeed->updateallfeed();
            unset($objFeed);

            include_once './modules/rss/class_rss_filter.php';

            $objRssFilter = new rss_filter();

            $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

            $intRssFilter_id = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] : '';
            $intRssFilter_id_element = (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'])) ? $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] : '';
            if(is_numeric($intRssFilter_id) && $intRssFilter_id > 0)
            {
              $objRssFilter->open($intRssFilter_id);

              $sql = $objRssFilter->makeRequest($intRssFilter_id_element);
              if($sql == '')
              {
                 echo $skin->open_simplebloc();
                 ?>
                 <h1><?php echo _RSS_LABEL_NEWS; ?></h1>
                 <div id="rss_explorer_feed_content">
                    <?php echo _RSS_SQL_REQUEST_ERROR; ?>
                 </div>
                 <?php
                 echo $skin->close_simplebloc();
                 ploopi_die();
              }
            }
            else
            {
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

                      ORDER BY    entry.published DESC
                      LIMIT       0,{$_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['nbitemdisplay']}
                      ";
            }
            $db->query($sql);
            if($db->numrows()>0)
            {
              $arrEntry = $db->getarray();
              $title = '';
              $numrow = 1;

              echo $skin->open_simplebloc();
              ?>
              <h1><?php echo _RSS_LABEL_NEWS.' '.$title; ?></h1>
              <div id="rss_filter_feed_content">
                <?php
                foreach($arrEntry as $entry)
                {
                  ?>
                  <div class="rss_entry">
                      <a class="rss_entry<?php echo (++$numrow)%2; ?>" href="<?php echo $entry['link']; ?>" target="_blank">
                          <b><?php echo strip_tags($entry['title'], '<b><i>'); ?></b>
                          <br /><i><?php echo ploopi_unixtimestamp2local($entry['published']); ?> &#149; <?php echo $entry['titlefeed']; ?></i>
                      <?php
                      if (!empty($entry['subtitle']))
                      {
                          ?>
                          <br /><?php echo strip_tags($entry['subtitle'], '<b><i>'); ?>
                          <?php
                      }
                      if (!empty($entry['content']))
                      {
                          ?>
                          <br /><?php echo strip_tags($entry['content'], '<b><i>'); ?>
                          <?php
                      }
                      ?>
                      </a>
                  </div>
                  <div class="rss_entry_annotation">
                  <?php
                  ploopi_annotation(_RSS_OBJECT_NEWS_ENTRY, sprintf("%06d%06d%s", $entry['id_cat'], $entry['id_feed'], $entry['id']), strip_tags($entry['title'], '<b><i>'));
                  ?>
                  </div>
                <?php
                }
                ?>
              </div>
              <?php
              echo $skin->close_simplebloc();
            }
            else
            {
              echo $skin->open_simplebloc();
              ?>
              <h1><?php echo _RSS_LABEL_NEWS; ?></h1>
              <div id="rss_filter_feed_content">
                <?php echo _RSS_SQL_NO_RESULT; ?>
              </div>
              <?php
              echo $skin->close_simplebloc();
            }
            ploopi_die();
        break;

        case 'rssfilter_element_delete':
          if($_GET['rssfilter_id_element']>0)
          {
            if($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] == $_GET['rssfilter_id_element'])
               $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';

            include_once './modules/rss/class_rss_filter_element.php';
            $objRssFilterElement = new rss_filter_element();

            $objRssFilterElement->open($_GET['rssfilter_id_element']);
            $objRssFilterElement->delete();
          }
          ploopi_die();
        break;

    }
}
?>
