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
 * Gestion des Filtres
 *
 * @package rss
 * @subpackage filter
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_request
 *
 * @package rss
 * @subpackage request
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class rss_filter extends data_object
{
  /**
   * Tableau des catégories concernées par le filtre
   *
   * @var array
   */
  public $categ = array();

  /**
   * Tableau des flux concernées par le filtre
   *
   * @var array
   */
  public $feed = array();

   /**
   * Constructeur de la classe
   *
   * @return rss_request
   */
  function rss_filter()
  {
      parent::data_object('ploopi_mod_rss_filter');
  }

  /**
   * Ouvrir un filtre recupere la liste des cat/flux attachés à ce filtre
   *
   * @return int nombre d'enregistrement(s) trouvé(s)
   */
  function open($intIdFiltre)
  {

    $intFind = parent::open($intIdFiltre);
    if($intFind)
    {
      global $db;

      $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

      $strRssSqlCat = "SELECT id_cat
                          FROM ploopi_mod_rss_filter_cat
                          WHERE ploopi_mod_rss_filter_cat.id_filter = {$intIdFiltre}
                           AND  ploopi_mod_rss_filter_cat.id_workspace = {$wk}";
      $objRssSqlResult = $db->query($strRssSqlCat);
      $this->categ = $db->getarray($objRssSqlResult, true);

      $strRssSqlFeed = "SELECT id_feed
                          FROM ploopi_mod_rss_filter_feed
                          WHERE ploopi_mod_rss_filter_feed.id_filter = {$intIdFiltre}
                           AND  ploopi_mod_rss_filter_feed.id_workspace = {$wk}";
      $objRssSqlResult = $db->query($strRssSqlFeed, true);
      $this->feed = $db->getarray();
    }
    return $intFind;
  }

  /**
   * Enregistre une requête
   *
   * @return boolean true si la requète s'est enregistrée correctement
   */
  function save()
  {
    $this->setuwm();

    $this->fields['tpl_tag'] = ploopi_convertaccents(strtolower(strtr(trim($this->fields['tpl_tag']), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '_'))));
    if($this->fields['tpl_tag'] =='' || $this->fields['tpl_tag'] == 'rss_')
      $this->fields['tpl_tag'] = NULL;
    else
      if(substr($this->fields['tpl_tag'],0,4) != 'rss_') $this->fields['tpl_tag'] = 'rss_'.$this->fields['tpl_tag'];

    if($this->new)
      $this->fields['timestp'] = ploopi_createtimestamp();
    else
      $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();

    return parent::save();
  }

  /**
   * Supprime une requête avec ses détails
   *
   * @return boolean true si la requète s'est supprimée correctement
   */
  function delete()
  {
    global $db;

    $strRssSqlDelete = "DELETE FROM ploopi_mod_rss_filter_element
                          WHERE ploopi_mod_rss_filter_element.id_filter = {$this->fields['id']}";
    if($db->query($strRssSqlDelete))
    {
      return parent::delete();
    }
    else
    {
      return false;
    }
  }

  /**
   * Fabrique la requete au format sql correpondante au filtre
   *
   * @param $intIdFilterElement identifiant d'un element précis du filtre (optionnel)
   * @param $booUseLimit Utilisation des limites (optionnel, defaut = true)
   * @param $intIdModule identifiant du module (optionnel, utile pour une utilisation en front!)
   * @return string requete
   */
  function makeRequest($intIdFilterElement=0,$booUseLimit=true,$intIdModule=0)
  {
    global $db;

    if(!$intIdModule > 0) $intIdModule = $_SESSION['ploopi']['moduleid'];
    $wk = ploopi_viewworkspaces($intIdModule);

    include_once './modules/rss/class_rss_filter_element.php';

    $objRssFilterDetail = new rss_filter_element();
    if(is_int($intIdFilterElement) && $intIdFilterElement>0)
    {
       if($objRssFilterDetail->open($intIdFilterElement))
       {
         $strRssFilter = $objRssFilterDetail->lineSql(true);
         if($strRssFilter == '') return '';
       }
    }
    else
    {
      $strRssFilter = '';
      $strRssJoin = ($this->fields['condition'] == 1) ? ' AND ' : ' OR ';

      $strRssSqlDetail = "SELECT id
                          FROM ploopi_mod_rss_filter_element
                          WHERE ploopi_mod_rss_filter_element.id_filter = {$this->fields['id']}";
      $objRssSqlResult = $db->query($strRssSqlDetail);
      while ($arrRssFields = $db->fetchrow($objRssSqlResult))
      {
         if($objRssFilterDetail->open($arrRssFields['id']))
         {
           $strRssLineSql = $objRssFilterDetail->lineSql(true);
           if($strRssLineSql != '')
           {
             if($strRssFilter != '') $strRssFilter .= $strRssJoin;
             $strRssFilter .= $strRssLineSql;
           }
           else
           {
             return '';
           }
         }
      }
    }
    if(!isset($strRssFilter) || $strRssFilter == '') $strRssFilter = '1=1'; // astuce car si pas d'element on ne peux pas mettre de AND...

    //Recupération des flux correspondant aux catégories selectionnées.
    $arrListFeed = array();
    if(count($this->categ)>0)
    {
      $strRssFeed = 'SELECT feed.id
                          FROM ploopi_mod_rss_feed feed
                          WHERE feed.id_cat IN ('.implode(',',$this->categ).')
                            AND feed.id_workspace IN ('.$wk.')';

      if($db->query($strRssFeed))
         $arrListFeed = $db->getarray($strRssFeed, true);
    }

    //Mise en place du filtre sur le flux et/ou la catégorie
    if(count($this->feed)>0)
      $arrListFeed = array_merge($arrListFeed,$this->feed);

    $strRssFilterFeed = (count($arrListFeed)>0) ? ' AND entry.id_feed IN ('.implode(',',$arrListFeed).')' : '';

    // Mise en place de la limite
    $strLimit = '';
    if ($booUseLimit === true && $this->fields['limit']>0)
    {
      // Utilisation de la limite enregistrée dans le filtre
      $strLimit = 'LIMIT 0,'.$this->fields['limit'];
    }
    elseif($booUseLimit !== true)
    {
      // Utilisation de la limite par defaut
      $strLimit = 'LIMIT 0,'.$_SESSION['ploopi']['modules'][$intIdModule]['nbitemdisplay'];
    }

    if($strRssFilter != '')
    {
      $strRssFilter = "
                    SELECT      entry.*,
                                feed.title as titlefeed,
                                feed.subtitle as subtitlefeed,
                                feed.link as linkfeed,
                                IFNULL(cat.id, 0) as id_cat,
                                IFNULL(cat.title, '"._RSS_LABEL_NOCATEGORY."') as titlecat
                    FROM        ploopi_mod_rss_entry entry,
                                ploopi_mod_rss_feed feed

                    LEFT JOIN   ploopi_mod_rss_cat cat
                    ON          cat.id = feed.id_cat
                    AND         cat.id_workspace IN ({$wk})

                    WHERE  feed.id = entry.id_feed
                           AND ({$strRssFilter})
                           {$strRssFilterFeed}
                    ORDER BY    entry.published DESC
                    {$strLimit}
                    ";
    }
    return $strRssFilter;
  }

  // @todo Revoir cette méthode (Fonctionne mais peut surement être optimisée)

  /**
   * Mise a jour des flux concernés par une filtre filtre
   *
   * @param int $intIdFilter identifiant du filtre (optionnel, par defaut id du filtre ouvert)
   * @return boolean true si maj ok
   */
  function updateFeedByFilter($intIdFilter = 0)
  {
    if(!is_int($intIdFilter) || $intIdFilter <= 0)
    {
      if(isset($this->fields['id']))
         $intIdFilter = $this->fields['id'];
      else
         return false;
    }

    global $db;

    include_once './modules/rss/class_rss_cat.php';
    include_once './modules/rss/class_rss_feed.php';

    $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

    // Categories
    $rssFilter = "SELECT      cat.id_cat
                  FROM        ploopi_mod_rss_filter_cat cat
                  WHERE       cat.id_workspace = {$wk}
                     AND      cat.id_filter = {$intIdFilter}
                  ";

    $rssFilter_result_cat = $db->query($rssFilter);
    while($rssFilter_row = $db->fetchrow($rssFilter_result_cat))
    {
      $objCat = new rss_cat();
      $objCat->updateFeedByCat($rssFilter_row['id_cat']);
      unset($objCat);
    }

    // Flux précis
    $rssFilter = "SELECT      filter_feed.id_feed,
                              feed.lastvisit,
                              feed.revisit
                  FROM        ploopi_mod_rss_filter_feed filter_feed,
                              ploopi_mod_rss_feed feed
                  WHERE       filter_feed.id_workspace = {$wk}
                     AND      filter_feed.id_filter = {$intIdFilter}
                     AND      filter_feed.id_feed = feed.id
                  ";

    $rssFilter_result_feed = $db->query($rssFilter);
    while($rssFilter_row = $db->fetchrow($rssFilter_result_feed))
    {
      if(($rssFilter_row['lastvisit'] == 0) || (ploopi_createtimestamp() - $rssFilter_row['lastvisit']) > $rssFilter_row['revisit'])
      {
        $objFeed = new rss_feed();
        $objFeed->open($rssFilter_row['id_feed']);
        if(!$objFeed->isuptodate()) $objFeed->updatecache();
        unset($objFeed);
      }
    }

    // Pas de précision donc ça porte sur tous les flux
    if(!$rssFilter_result_cat && !$rssFilter_result_feed)
    {
       $objFeed = new rss_feed();
       $objFeed->updateallfeed();
       unset($objFeed);
    }
    return true;
  }
}
?>
