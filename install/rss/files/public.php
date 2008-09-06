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
 * Partie publique du module
 *
 * @package rss
 * @subpackage public
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('rss');

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

$tabs['tabExplorer'] = array (  'title' => _RSS_LABEL_FEEDEXPLORER,
                                'url'   => "admin.php?rssTabItem=tabExplorer"
                            );

if ($_SESSION['ploopi']['connected'])
{
    $tabs['tabFilter'] = array (    'title' => _RSS_LABEL_FILTER_FEED,
                                    'url'   => "admin.php?rssTabItem=tabFilter"
                                );
    $tabs['tabNewFilter'] = array ( 'title' => _RSS_LABEL_FILTER_NEW,
                                    'url'   => "admin.php?rssTabItem=tabNewFilter"
                                );
}

if (!empty($_GET['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = $_GET['rssTabItem'];
if (!isset($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])) $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'] = '';

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->create_tabs($tabs,$_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem']);

switch($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssTabItem'])
{
    case 'tabExplorer':
      include './modules/rss/public_explorer.inc.php';
    break;
    
    case 'tabNewFilter':
      if(ploopi_isactionallowed(_RSS_ACTION_FILTERADD) || ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY))
      {
        $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = ''; // reset
        $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = ''; // reset
        include './modules/rss/public_filter_edit.inc.php';
      }
      ploopi_redirect('admin.php');
    break;
    
    case 'tabFilter':
      switch($op)
      {
        case 'rssfilter_new':
          if(ploopi_isactionallowed(_RSS_ACTION_FILTERADD) || ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY))
          {
            ploopi_redirect('admin.php?rssTabItem=tabNewFilter');
          }
          ploopi_redirect('admin.php');
        break;

        case 'rssfilter_save':
          if(ploopi_isactionallowed(_RSS_ACTION_FILTERADD) || ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY))
          {
            include_once './modules/rss/class_rss_filter.php';
            include_once './modules/rss/class_rss_filter_cat.php';
            include_once './modules/rss/class_rss_filter_feed.php';
  
            $objRssFilter = new rss_filter();
            $objRssFilterCat = new rss_filter_cat();
            $objRssFilterFeed = new rss_filter_feed();
            
            if(isset($_GET['rssfilter_id']) && $_GET['rssfilter_id'] > 0) $objRssFilter->open($_GET['rssfilter_id']);
            
            $objRssFilter->setvalues($_POST,'rssfilter_');
            $intLastId = $objRssFilter->save();
            
            if(isset($_POST['rssfiltercat_id_cat']))
              $objRssFilterCat->saveArrCat($objRssFilter->fields['id'],$_POST['rssfiltercat_id_cat']);
            else
              $objRssFilterCat->cleanFilterCat($objRssFilter->fields['id']);
  
            if(isset($_POST['rssfilterfeed_id_feed']))
              $objRssFilterFeed->saveArrFeed($objRssFilter->fields['id'],$_POST['rssfilterfeed_id_feed']);
            else
              $objRssFilterFeed->cleanFilterFeed($objRssFilter->fields['id']);
  
            ploopi_redirect("admin.php?rssTabItem=tabFilter&op=rssfilter_modify&rssfilter_id={$intLastId}");
          }
          ploopi_redirect('admin.php');
        break;
        
        case 'rssfilter_delete':
          if(ploopi_isactionallowed(_RSS_ACTION_FILTERDELETE) && !empty($_GET['rssfilter_id']) && is_numeric($_GET['rssfilter_id']))
          {          
            if($_GET['rssfilter_id']>0)
            { 
              if($_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] == $_GET['rssfilter_id'])
              {
                 $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id'] = '';
                 $_SESSION['rss'][$_SESSION['ploopi']['moduleid']]['rssfilter_id_element'] = '';
              }
              
              include_once './modules/rss/class_rss_filter.php';
              $objRssFilter = new rss_filter();
  
              $objRssFilter->open($_GET['rssfilter_id']);
              $objRssFilter->delete();
            }
            ploopi_redirect("admin.php?rssTabItem=tabFilter");
          }
          ploopi_redirect('admin.php');
        break;
        
        case 'rssfilter_modify':
          if(!ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY)) ploopi_redirect('admin.php');
          
          include './modules/rss/public_filter_edit.inc.php';
        break;

        case 'rssfilter_element_save':
          if(!ploopi_isactionallowed(_RSS_ACTION_FILTERADD) && !ploopi_isactionallowed(_RSS_ACTION_FILTERMODIFY)) ploopi_redirect('admin.php');
          
          if(!isset($_GET['rssfilter_id']) || !$_GET['rssfilter_id']>0) ploopi_die();
          
          include_once './modules/rss/class_rss_filter_element.php';
          
          $objRssFilterElement = new rss_filter_element();
          
          if($_GET['rssfilter_id_element']>0) $objRssFilterElement->open($_GET['rssfilter_id_element']);

          $objRssFilterElement->setvalues($_POST,'rss_element_');
          $objRssFilterElement->fields['id_filter'] = $_GET['rssfilter_id'];
          $intLastId = $objRssFilterElement->save();

          ploopi_redirect("admin.php?rssTabItem=tabFilter&op=rssfilter_modify&rssfilter_id={$_GET['rssfilter_id']}&rssfilter_id_element={$intLastId}");
        break;
        
        default:
          include './modules/rss/public_filter.inc.php';
        break;
        
        // /!\ rssfilter_element_delete in op.php for httpRequest /!\
        }
    break;
}
?>
