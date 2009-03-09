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
 * Classe d'accès à la table ploopi_mod_rss_request_element
 *
 * @package rss
 * @subpackage request element
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
class rss_filter_element extends data_object
{
  /**
   * RssTabTarget = array( champ sql cible => array( label => nom a afficher,
   *                                                 compare => type de donnée,
   *                                                 table => table concernée,
   *                                                 alias => alias à utiliser pour la table)
   *
   * @var array()
   */
  private $rssTabTarget = array(  'title'     => array('label' => _RSS_LABEL_TITLE, 'compare' => 'string', 'table' => 'ploopi_mod_rss_entry', 'alias' => 'entry'),
                                  'subtitle'  => array('label' => _RSS_LABEL_SUBTITLE, 'compare' => 'string', 'table' => 'ploopi_mod_rss_entry', 'alias' => 'entry'),
                                  'content'   => array('label' => _RSS_LABEL_CONTENT, 'compare' => 'string', 'table' => 'ploopi_mod_rss_entry', 'alias' => 'entry'),
                                  'link'      => array('label' => _RSS_LABEL_LINK, 'compare' => 'string', 'table' => 'ploopi_mod_rss_entry', 'alias' => 'entry'),
                                  'published' => array('label' => _RSS_LABEL_DATE_PUBLIC, 'compare' => 'date', 'table' => 'ploopi_mod_rss_entry', 'alias' => 'entry'),
                          );

  /**
   * $rssTabCompare = array( type de donnée => array( type de comparatif => array( label => label à afficher,
   *                                                                               sql => sql avec tag = %t a remplacer par la valeur,
   *                                                                               [row => champ cible si != de celui indiqué dans rssTabTarget])
   *
   * @var unknown_type
   */
  private $rssTabCompare = array( 'string'    => array('contain' => array('label' => _RSS_SQL_CONTENT,'sql' => 'like \'%%t%\''),
                                                       'no_contain' => array('label' => _RSS_SQL_NOCONTENT,'sql' => 'not like \'%%t%\''),
                                                       'is' => array('label' => _RSS_SQL_IS,'sql' => 'like \'%t\''),
                                                       'no_is' => array('label' => _RSS_SQL_NOIS,'sql' => 'not like \'%t\''),
                                                       'begin' => array('label' => _RSS_SQL_BEGIN,'sql' => 'like \'%t%\''),
                                                       'no_begin' => array('label' => _RSS_SQL_NOBEGIN,'sql' => 'not like \'%t%\'')
                                                 ),
                                  'date'      => array('is' => array('label' => _RSS_SQL_IS,'sql' => 'like \'%t\'', 'row' => 'published_day'),
                                                       'no_is' => array('label' => _RSS_SQL_NOIS,'sql' => 'not like \'%t\'','row' => 'published_day'),
                                                       'before' => array('label' => _RSS_SQL_BEFORE,'sql' => '< \'%t\''),
                                                       'after' => array('label' => _RSS_SQL_AFTER,'sql' => '> \'%t\'')
                                                 )
                           );

  /**
   * Constructeur de la classe
   *
   * @return rss_request
   */
  function rss_filter_element()
  {
    parent::data_object('ploopi_mod_rss_filter_element');
  }

  /**
   * Enregistrement d'un élément de détail de filtre
   *
   * @return boolean true si le détail du filtre s'est enregistré correctement
   */
  function save()
  {
    if(!array_key_exists($this->fields['target'],$this->rssTabTarget)) return '';

    if($this->rssTabTarget[$this->fields['target']]['compare'] == 'date')
      $this->fields['value'] = ploopi_timestamp2unixtimestamp(ploopi_local2timestamp($this->fields['value']));

    return parent::save();
  }

  /**
   * converti un detail en ligne de requete
   *
   * @param boolean $boolUseAlias Utilisation de l'alias dans la requete (defaut = false)
   *
   * @return string ligne au format sql
   */
  function lineSql($boolUseAlias = false)
  {
    if(!array_key_exists($this->fields['target'],$this->rssTabTarget)) return '';

    $strRssTypeCompare = $this->rssTabTarget[$this->fields['target']]['compare'];
    $strRssTable = $this->rssTabTarget[$this->fields['target']]['table'];

    if(!array_key_exists($strRssTypeCompare,$this->rssTabCompare)) return '';
    if(!array_key_exists($this->fields['compare'],$this->rssTabCompare[$strRssTypeCompare])) return '';

    $row_target = (isset($this->rssTabCompare[$strRssTypeCompare][$this->fields['compare']]['row'])) ? $this->rssTabCompare[$strRssTypeCompare][$this->fields['compare']]['row'] : $this->fields['target'];

    if($boolUseAlias)
      $strRssLineFilter  = $this->rssTabTarget[$this->fields['target']]['alias'].'.'.$row_target; //table Alias +champ
    else
      $strRssLineFilter  = $this->rssTabTarget[$this->fields['target']]['table'].'.'.$row_target; //table + champ

    $strRssLineFilter .= ' '.$this->rssTabCompare[$strRssTypeCompare][$this->fields['compare']]['sql']; // Comparatif

    $strRssLineFilter  = str_replace('%t',addslashes($this->fields['value']),$strRssLineFilter); // Valeur de comparaison

    return $strRssLineFilter;
  }

  /**
   * Récupère le tableau des caratéristiques de l'élément en cours
   *
   * @return array Tableau des caratéristiques de l'élément en cours
   */
  function getElement()
  {
    $arrElements['target'] = $this->rssTabTarget[$this->fields['target']];
    $arrElements['target']['value'] = $this->fields['target'];
    $arrElements['compare'] = $this->rssTabCompare[$arrElements['target']['compare']][$this->fields['compare']];
    $arrElements['compare']['value'] = $this->fields['compare'];
    $arrElements['value'] = $this->fields['value'];
    return $arrElements;
  }

  /**
   * Récupère le tableau des caratéristiques "Target" des éléments
   *
   * @return array Tableau des caratéristiques "Target" des éléments
   */
  function getTabTarget()
  {
    return $this->rssTabTarget;
  }

  /**
   * Récupère le tableau des caratéristiques "Compare" des éléments
   *
   * @return array Tableau des caratéristiques "Compare" des éléments
   */
  function getTabCompare()
  {
    return $this->rssTabCompare;
  }
}
?>
