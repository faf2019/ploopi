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
 * Classe d'acc�s � la table ploopi_mod_rss_filter_cat
 *
 * @package rss
 * @subpackage categorie(s) used by filter
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
class rss_filter_cat extends data_object
{

  /**
   * Constructeur de la classe
   *
   * @return rss_filter_cat
   */
  function rss_filter_cat()
  {
    parent::data_object('ploopi_mod_rss_filter_cat','id_filter', 'id_cat');
  }

  /**
   * Enregistre un lien Filtre avec une Cat�gorie
   *
   * @return mixed valeur de la cl� primaire
   */
  function save()
  {
    $this->setuwm();
    return parent::save();
  }

  /**
   * Enregistre un lien Filtre avec tableau de Cat�gories
   *
   * @param int $intIdFilter identifiant du filtre
   * @param array $arrCat Tableau d'identifiant de cat�gorie
   *
   * @return boolean true si enregistrement ok
   */
  function saveArrCat($intIdFilter,$arrCat)
  {
    if(!is_numeric($intIdFilter) || !$intIdFilter>0) return false;
    if(!is_array($arrCat)) return false;

    // Supprime toutes les cat�gories attach�es au filtre
    if(!$this->cleanFilterCat($intIdFilter)) return false;

    // Enregistre toutes les cat�gories du tableau pass� en param
    foreach($arrCat as $idCat)
    {
      $this->new = true;
      $this->fields['id_filter'] = $intIdFilter;
      $this->fields['id_cat'] = $idCat;
      $this->setuwm();
      if(!parent::save()) return false;
    }
    return true;
  }

  /**
   * Supprime tous les liens des cat�gories � un filtre
   *
   * @param int $intIdFilter identifiant du filtre
   *
   * @return boolean
   */
  function cleanFilterCat($intIdFilter='')
  {
    global $db;

    if(!is_numeric($intIdFilter) || !($intIdFilter>0))
    {
      if(!is_numeric($this->fields['id_filter']) || !$this->fields['id_filter']>0)
      return false;
      else
      $intIdFilter = $this->fields['id_filter'];
    }

    $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

    $strRssSqlDelete = "DELETE FROM ploopi_mod_rss_filter_cat
    WHERE ploopi_mod_rss_filter_cat.id_filter = '{$intIdFilter}''
    AND ploopi_mod_rss_filter_cat.id_workspace IN ({$wk})";
    return $db->query($strRssSqlDelete);
  }

  /**
   * Recup�re un tableau des cat�gories attach�es � un filtre
   *
   * @param int $intIdFilter identifiant du filtre
   *
   * @return array $arrCat[id du filtre][] = id de la cat�gorie
   */
  function getListCat($intIdFilter=0)
  {
    global $db;

    $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

    $arrCat = array();
    $strSqlRequest = "SELECT cat.id_filter,
    cat.id_cat
    FROM ploopi_mod_rss_filter_cat cat
    WHERE cat.id_workspace IN ({$wk})";

    if($intIdFilter>0) $strSqlRequest .= " AND cat.id_filter = '{$intIdFilter}'";

    if($db->query($strSqlRequest))
    {
      while ($row = $db->fetchrow())
      {
        $arrCat[$row['id_filter']][] = $row['id_cat'];
      }
    }

    return $arrCat;
  }
}
?>
