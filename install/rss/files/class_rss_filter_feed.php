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
 * @subpackage filter detail
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_filter_feed
 *
 * @package rss
 * @subpackage feed(s) used by filter
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
class rss_filter_feed extends data_object
{

  /**
   * Constructeur de la classe
   *
   * @return rss_filter_feed
   */
  function rss_filter_feed()
  {
    parent::data_object('ploopi_mod_rss_filter_feed', 'id_filter', 'id_feed');
  }

  /**
   * Enregistre un lien Filtre avec un flux
   *
   * @return mixed valeur de la clé primaire
   */
  function save()
  {
    $this->setuwm();
    return parent::save();
  }

  /**
   * Enregistre un lien Filtre avec tableau de flux
   *
   * @param int $intIdFilter identifiant du filtre
   * @param array $arrFeed Tableau d'identifiant de flux
   *
   * @return boolean true si enregistrement ok
   */
  function saveArrFeed($intIdFilter,$arrFeed)
  {
    if(!is_numeric($intIdFilter) || !$intIdFilter>0) return false;
    if(!is_array($arrFeed)) return false;

    if(!$this->cleanFilterFeed($intIdFilter)) return false;

    foreach($arrFeed as $idFeed)
    {
      $this->new = true;

      $this->fields['id_filter'] = $intIdFilter;
      $this->fields['id_feed'] = $idFeed;
      $this->setuwm();
      if(!parent::save()) return false;
    }
    return true;
  }

  /**
   * Supprime tous les liens des flux à un filtre
   *
   * @param int $intIdFilter identifiant du filtre
   *
   * @return boolean
   */
  function cleanFilterFeed($intIdFilter='')
  {
    global $db;

    if(!is_numeric($intIdFilter) || !$intIdFilter>0)
    {
      if(!is_numeric($this->fields['id_filter']) || !$this->fields['id_filter']>0)
         return false;
      else
         $intIdFilter = $this->fields['id_filter'];
    }

    $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

    $strRssSqlDelete = "DELETE FROM ploopi_mod_rss_filter_feed
                          WHERE ploopi_mod_rss_filter_feed.id_filter = {$intIdFilter}
                            AND ploopi_mod_rss_filter_feed.id_workspace = $wk";
    return $db->query($strRssSqlDelete);
  }

  /**
   * Recupère un tableau des flux attachées à un filtre
   *
   * @param int $intIdFilter identifiant du filtre
   *
   * @return array $arrFeed[id du filtre][] = id du flux
   */
  function getListFeed($intIdFilter=0)
  {
    global $db;

    $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

    $arrFeed = array();
    $strSqlRequest = "SELECT feed.id_filter,
                              feed.id_feed
                     FROM ploopi_mod_rss_filter_feed feed
                     WHERE feed.id_workspace = {$wk}";

    if($intIdFilter>0) $strSqlRequest .= " AND feed.id_filter = {$intIdFilter}";

    if($db->query($strSqlRequest))
    {
      while ($row = $db->fetchrow())
      {
        $arrFeed[$row['id_filter']][] = $row['id_feed'];
      }
    }

    return $arrFeed;
  }
}
?>
