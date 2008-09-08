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

include_once './modules/rss/class_rss_pref.php';

$wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);
   
$title = '';

$block_rssfeed_cat_filter_id = empty($_GET['block_rssfeed_filter_id']) ? 0 : $_GET['block_rssfeed_filter_id'];

if ($block_rssfeed_cat_filter_id != '0')
{
    $rsspref = new rss_pref();
    $rsspref->fields['id_user'] = $_SESSION['ploopi']['userid'];
    $rsspref->fields['id_module'] = $menu_moduleid;
    $rsspref->fields['id_feed_cat_filter'] = $block_rssfeed_cat_filter_id;
    $rsspref->save();
}
else
{
    $db->query("SELECT id_feed_cat_filter FROM ploopi_mod_rss_pref WHERE id_user = '{$_SESSION['ploopi']['userid']}' AND id_module = '{$menu_moduleid}'");
    if ($fpref = $db->fetchrow())
           $block_rssfeed_cat_filter_id = $fpref['id_feed_cat_filter'];
}

if (ploopi_isactionallowed(-1,$_SESSION['ploopi']['workspaceid'],$menu_moduleid))
{
    $block->addmenu('<b>'._RSS_LABEL_ADMIN.'</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}

if ($_SESSION['ploopi']['connected']) $block->addmenu('<b>'._RSS_LABEL_SEARCH.'</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"));

/* Flux */
$rssfeed_select =   "
                    SELECT      feed.*,
                                cat.title as titlecat
                    FROM        ploopi_mod_rss_feed feed
                    LEFT JOIN   ploopi_mod_rss_cat cat ON cat.id = feed.id_cat
                    WHERE       feed.id_module = {$menu_moduleid}
                    AND         feed.id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).")
                    ORDER BY    feed.title
                    ";

$rssfeed_result = $db->query($rssfeed_select);

$sel = ($block_rssfeed_cat_filter_id == -1) ? 'selected' : '';
$strFeedsCatFiltersOptions = "<option $sel value=\"-1\">"._PLOOPI_ALL.'</option>';

while($rssfeed_row = $db->fetchrow($rssfeed_result))
{
    if (!$block_rssfeed_cat_filter_id || empty($block_rssfeed_cat_filter_id))
    {
      $block_rssfeed_cat_filter_id = $rssfeed_row['id'];
      $rsspref = new rss_pref();
      $rsspref->fields['id_user'] = $_SESSION['ploopi']['userid'];
      $rsspref->fields['id_module'] = $menu_moduleid;
      $rsspref->fields['id_feed_cat_filter'] = $block_rssfeed_cat_filter_id;
      $rsspref->save();
    }
    $sel = ($block_rssfeed_cat_filter_id == $rssfeed_row['id']) ? 'selected' : '';

    $strFeedsCatFiltersOptions .= "<option $sel value=\"{$rssfeed_row['id']}\">{$rssfeed_row['title']}</option>";
}

/* Categorie */
$rsscat_select =   "
                    SELECT      cat.*
                    FROM        ploopi_mod_rss_cat cat
                    WHERE       cat.id_module = {$menu_moduleid}
                    AND         cat.id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).")
                    ORDER BY    cat.title
                    ";

$rsscat_result = $db->query($rsscat_select);

if($db->numrows($rsscat_result)) $strFeedsCatFiltersOptions .= '<option value="-1">-------------</option>';

while($rsscat_row = $db->fetchrow($rsscat_result))
{
   $rsscat_row['id'] = 'C'.$rsscat_row['id'];
   if (!$block_rssfeed_cat_filter_id || empty($block_rssfeed_cat_filter_id))
   {
     $block_rssfeed_cat_filter_id = $rsscat_row['id'];
     $rsspref = new rss_pref();
     $rsspref->fields['id_user'] = $_SESSION['ploopi']['userid'];
     $rsspref->fields['id_module'] = $menu_moduleid;
     $rsspref->fields['id_feed_cat_filter'] = $block_rssfeed_cat_filter_id;
     $rsspref->save();
   }
   $sel = ($block_rssfeed_cat_filter_id == $rsscat_row['id']) ? 'selected' : '';

   $strFeedsCatFiltersOptions .= "<option $sel value=\"{$rsscat_row['id']}\">(C) {$rsscat_row['title']}</option>";
}

/* Filter */
$rssfilter_select =   "
                    SELECT      filter.*
                    FROM        ploopi_mod_rss_filter filter
                    WHERE       filter.id_module = {$menu_moduleid}
                    AND         filter.id_workspace IN (".ploopi_viewworkspaces($menu_moduleid).")
                    ORDER BY    filter.title
                    ";

$rssfilter_result = $db->query($rssfilter_select);

if($db->numrows($rssfilter_result)) $strFeedsCatFiltersOptions .= '<option value="-1">-------------</option>';

while($rssfilter_row = $db->fetchrow($rssfilter_result))
{
   $rssfilter_row['id'] = 'F'.$rssfilter_row['id'];
   if (!$block_rssfeed_cat_filter_id || empty($block_rssfeed_cat_filter_id))
   {
     $block_rssfeed_cat_filter_id = $rssfilter_row['id'];
     $rsspref = new rss_pref();
     $rsspref->fields['id_user'] = $_SESSION['ploopi']['userid'];
     $rsspref->fields['id_module'] = $menu_moduleid;
     $rsspref->fields['id_feed_cat_filter'] = $block_rssfeed_cat_filter_id;
     $rsspref->save();
   }
   $sel = ($block_rssfeed_cat_filter_id == $rssfilter_row['id']) ? 'selected' : '';

   $strFeedsCatFiltersOptions .= "<option $sel value=\"{$rssfilter_row['id']}\">(F) {$rssfilter_row['title']}</option>";
}


// Affichage des flux
include_once './modules/rss/class_rss_feed.php';

if (substr($block_rssfeed_cat_filter_id,0,1) == 'C') // Categorie
{
   include_once './modules/rss/class_rss_cat.php';
   
   $objRssCat = new rss_cat();
   if($objRssCat->open(substr($block_rssfeed_cat_filter_id,1)))
   {
     $sql =  "
            SELECT      entry.*,
                        feed.title as titlefeed
            FROM        ploopi_mod_rss_entry entry,
                        ploopi_mod_rss_feed feed
            WHERE       entry.id_feed = feed.id
               AND      feed.id_cat = {$objRssCat->fields['id']}
               AND      feed.id_workspace IN ({$wk})
            ORDER BY    entry.published DESC";
            
     if($objRssCat->fields['limit']>0)
       $sql .= " LIMIT 0,{$objRssCat->fields['limit']}";
     else
       $sql .= " LIMIT 0,{$_SESSION['ploopi']['modules'][$menu_moduleid]['nbitemdisplay']}";

     $rssentry_result = $db->query($sql);
     while($rssentry_row = $db->fetchrow($rssentry_result))
     {
        $ld = (!empty($rssentry_row['published']) && is_numeric($rssentry_row['published'])) ? ploopi_unixtimestamp2local($rssentry_row['published']) : '';
     
        $block->addmenu(strip_tags($rssentry_row['title'], '<b><i>').'<br />'.$ld, $rssentry_row['link'], '', '_blank');        
     }
     unset($objRssCat);
   }
}
elseif (substr($block_rssfeed_cat_filter_id,0,1) == 'F') // Filtre
{
   include_once './modules/rss/class_rss_filter.php';
            
   $objRssFilter = new rss_filter();

   $objRssFilter->open(substr($block_rssfeed_cat_filter_id,1));
   $objRssFilter->updateFeedByFilter();
              
   $sql = $objRssFilter->makeRequest();
   if($sql != '')
   {
     $rssentry_result = $db->query($sql);
     while($rssentry_row = $db->fetchrow($rssentry_result))
     {
        $ld = (!empty($rssentry_row['published']) && is_numeric($rssentry_row['published'])) ? ploopi_unixtimestamp2local($rssentry_row['published']) : '';
           $block->addmenu(strip_tags($rssentry_row['title'], '<b><i>').'<br />'.$ld, $rssentry_row['link'], '', '_blank');        
     }
   }
   unset($objRssFilter);
}
elseif (intval($block_rssfeed_cat_filter_id) > 0)  // Un flux
{
    $rss_feed = new rss_feed();
    $rss_feed->open($block_rssfeed_cat_filter_id);

    if (!$rss_feed->isuptodate()) $rss_feed->updatecache();
    
    $block->addmenu("<b>{$rss_feed->fields['title']}</b>".(!empty($rss_feed->fields['subtitle']) ? '<br /><i>'.strip_tags($rss_feed->fields['subtitle'], '<b><i>').'</i>' : ''), $rss_feed->fields['link'], '', '_blank');

    $sql =  "
          SELECT      ploopi_mod_rss_entry.*
          FROM        ploopi_mod_rss_entry
          WHERE       ploopi_mod_rss_entry.id_feed = {$block_rssfeed_cat_filter_id}
          ORDER BY    published DESC, timestp DESC, id";

    if($rss_feed->fields['limit']>0)
       $sql .= " LIMIT 0,{$rss_feed->fields['limit']}";
    else
       $sql .= " LIMIT 0,{$_SESSION['ploopi']['modules'][$menu_moduleid]['nbitemdisplay']}";
       
    $rssentry_result = $db->query($sql);
    while($rssentry_row = $db->fetchrow($rssentry_result))
    {
        $ld = (!empty($rssentry_row['published']) && is_numeric($rssentry_row['published'])) ? ploopi_unixtimestamp2local($rssentry_row['published']) : '';

        $block->addmenu(strip_tags($rssentry_row['title'], '<b><i>').'<br />'.$ld, $rssentry_row['link'], '', '_blank');        
    }
}
elseif (intval($block_rssfeed_cat_filter_id) <= 0)  // Tout
{
    
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
}

if ($strFeedsCatFiltersOptions != '')
{
    $content =  "
                <div style=\"padding:2px;\">
                    <form name=\"bloc_rss_switch\">
                    <select name=\"block_rssfeed_filter_id\" class=\"select\" style=\"width:95%;\" OnChange=\"javascript:bloc_rss_switch.submit()\">{$strFeedsCatFiltersOptions}</select>
                    </form>
                    <div style=\"font-weight:bold;padding:2px 0px;\">{$title}</div>
                </div>
                ";
    $block->addcontent($content);
}
?>
