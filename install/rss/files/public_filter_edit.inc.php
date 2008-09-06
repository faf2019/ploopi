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
 * Edition / Modification des Filtres sur les flux
 *
 * @package rss
 * @subpackage public
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

include_once './modules/rss/class_rss_filter.php';

if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = '';
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';

if (isset($_GET['rssfilter_id']) && $_GET['rssfilter_id'] != $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])
   $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';

if (isset($_GET['rssfilter_id'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = $_GET['rssfilter_id'];
if (isset($_GET['rssfilter_id_element'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = $_GET['rssfilter_id_element'];

$objFilter     = new rss_filter();

$arrFilterCat  = array();
$arrFilterFeed = array(); 

if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'])) 
   $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = '';

if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'])) 
   $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';

if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] > 0)
{
  $objFilter->open($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']);
  
  $arrFilterCat  = $objFilter->categ;
  $arrFilterFeed = $objFilter->feed;
  
  echo $skin->open_simplebloc(_RSS_LABEL_FILTER_MODIF);
}
else
{
  $objFilter->init_description();
  
  echo $skin->open_simplebloc(_RSS_LABEL_FILTER_NEW);
}

?>
<div>
    <div id="rss_filter_edit">
      <?php

      $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);
      
      echo $skin->open_simplebloc();
      
      $objRssFilters = new rss_filter();
      
      if (isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']) && $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']>0)
      {
        $objRssFilters->open($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']);
        echo '<h1>'._RSS_SQL_MODIF_FILTER.'</h1>';
      }
      else
      {
        $objRssFilters->init_description();
        echo '<h1>'._RSS_SQL_NEW_FILTER.'</h1>';
      }
      
      $action = 'admin.php?rssTabItem=tabFilter&op=rssfilter_save';
      if($objRssFilters->fields['id']>0) $action .= '&rssfilter_id='.$objRssFilters->fields['id'];
      ?>
      <form name="form_rssfilter" action="<? echo ploopi_urlencode($action); ?>" method="post" onsubmit="return rssfilter_validate(this);">
      <div class="ploopi_form">
        <div style="padding:2px;">
          <p>
            <label><? echo _RSS_LABEL_TITLE; ?>:</label>
            <input class="text" type="text" name="rssfilter_title" value="<? echo htmlentities($objRssFilters->fields['title']); ?>" tabindex="100" />
          </p>
          <p>
            <label><? echo _RSS_LABEL_LIMIT; ?>:</label>
            <input class="text" type="text" name="rssfilter_limit" style="width:50px;" value="<? echo htmlentities($objRssFilters->fields['limit']); ?>" tabindex="101" /><?php echo _RSS_COMMENT_O_NOLIMIT; ?>
          </p>
          <p>
            <label><? echo _RSS_LABEL_TPL_TAG; ?>:</label>
            <input class="text" type="text" name="rssfilter_tpl_tag" style="width:200px;" value="<? echo $objRssFilters->fields['tpl_tag'] ?>" tabindex="102" /><br/>
            <label>&nbsp;</label><?php echo _RSS_COMMENT_FILTER_TPL_TAG; ?><br/>
            <label>&nbsp;</label><?php echo _RSS_COMMENT_WARNING_TPL_TAG; ?>
          </p>
          <?php
          $sql = "SELECT    cat.id,
                            cat.title
                   FROM     ploopi_mod_rss_cat cat
                   WHERE    cat.id_workspace IN ({$wk})
                   ORDER BY title DESC";
          $db->query($sql);
          if ($db->numrows() == 0)
          {                     
            echo '<p>Aucune Catégorie Disponible</p>';
          }
          else
          {
            $i = 1;
            while ($row = $db->fetchrow())
            {
              $strChecked = ''; 
              $strChecked = (in_array($row['id'],$arrFilterCat)) ? 'checked="checked"' : '';
              
              echo '<p class="ploopi_va" style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,\'rssfiltercat_id_cat_'.$i.'\');">';
              if($i == 1) echo '<label>'._RSS_LABEL_CATEGORY.':</label>'; else echo '<label>&nbsp;</label>';
              echo '<input type="checkbox" value="'.$row['id'].'" id="rssfiltercat_id_cat_'.$i.'" name="rssfiltercat_id_cat[]" '.$strChecked.' />&nbsp;'.$row['title'];
              echo '</p>';
              $i++;                      
            }
          }
          $sql = "SELECT    feed.id,
                            feed.title
                   FROM     ploopi_mod_rss_feed feed
                   WHERE    feed.id_workspace IN ({$wk})
                   ORDER BY title DESC";
          $db->query($sql);
          if ($db->numrows() == 0)
          {                     
            echo '<p>Aucun Flux Disponible</p>';
          }
          else
          {
            $i = 1;
            while ($row = $db->fetchrow())
            {
              $strChecked = ''; 
              $strChecked = (in_array($row['id'],$arrFilterFeed)) ? 'checked="checked"' : '';
              
              echo '<p class="ploopi_va" style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,\'rssfilterfeed_id_feed_'.$i.'\');">';
              if($i == 1) echo '<label>'._RSS_LABEL_FEEDS.':</label>'; else echo '<label>&nbsp;</label>';
              echo '<input type="checkbox" value="'.$row['id'].'" id="rssfilterfeed_id_feed_'.$i.'" name="rssfilterfeed_id_feed[]" '.$strChecked.' />&nbsp;'.$row['title'];
              echo '</p>';
              $i++;
            }                      
          }
          ?>
          <p>
          <label>&nbsp;</label><?php echo _RSS_INFO_CAT_FEED; ?>
          </p>
          <p class="ploopi_va" style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,'rssfilter_condition_1');">
            <label><? echo _RSS_LABEL_CONDITION; ?>:</label>
            <input type="radio" id="rssfilter_condition_1" name="rssfilter_condition" style="padding:0; margin:0; cursor:pointer;" value="1" <?php if($objRssFilters->fields['condition'] == 1 || $objRssFilters->new) echo 'CHECKED'; ?>>&nbsp;<?php echo _RSS_LABEL_CONDITION_AND ?>
          </p>
          <p class="ploopi_va" style="cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,'rssfilter_condition_0');">
            <label>&nbsp;</label>
            <input type="radio" id="rssfilter_condition_0" name="rssfilter_condition" style="padding:0; margin:0; cursor:pointer;" value="0" <?php if($objRssFilters->fields['condition'] != 1 && !$objRssFilters->new) echo 'CHECKED'; ?>>&nbsp;<?php echo _RSS_LABEL_CONDITION_OR ?>
          </p>
          
        </div>
      </div>
      <div style="padding:2px;text-align:right;">
          <input type="button" class="button" value="<? echo _RSS_SQL_NEW_FILTER; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?rssTabItem=tabNewFilter&op=rssfilter_new"); ?>';" tabindex="105" />
          <input type="button" class="button" value="<? echo _RSS_RETURN; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode("admin.php?rssTabItem=tabFilter"); ?>';" tabindex="104" />
          <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="103" />
      </div>
      </form>
      <?php
      echo $skin->close_simplebloc();
      ?>
    </div>
    <div id="rss_filter_element_edit"></div>
    <div id="rss_filter_element_list"></div>
</div>
<script type="text/javascript">
  ploopi_window_onload_stock(rss_filter_element_edit);
  
<?
if ($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id']>0)
{
  ?>
  ploopi_window_onload_stock(rss_filter_element_edit_list_get);
  <?
}
?>
</script>

<? echo $skin->close_simplebloc(); ?>
