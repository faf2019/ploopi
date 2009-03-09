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
 * Gestion des catégories
 *
 * @package rss
 * @subpackage category
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_cat
 *
 * @package rss
 * @subpackage category
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class rss_cat extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return rss_cat
     */
    function rss_cat()
    {
        parent::data_object('ploopi_mod_rss_cat');
    }

    /**
     * Sauvegarde une catégorie
     *
     * @return boolean true si enregistrement ok
     */
    function save()
    {
      $this->fields['tpl_tag'] = ploopi_convertaccents(strtolower(strtr(trim($this->fields['tpl_tag']), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '_'))));
      if($this->fields['tpl_tag'] =='' || $this->fields['tpl_tag'] == 'rss_')
        $this->fields['tpl_tag'] = NULL;
      else
        if(substr($this->fields['tpl_tag'],0,4) != 'rss_') $this->fields['tpl_tag'] = 'rss_'.$this->fields['tpl_tag'];

      parent::save();
    }

    /**
     *
     * Supprime une catégorie
     *
     * @return boolean true si suppression ok
     */
    function delete()
    {
      global $db;

      $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

      $rssfeed =  "UPDATE ploopi_mod_rss_feed
                     SET id_cat = 0
                     WHERE ploopi_mod_rss_feed.id_cat = {$this->fields['id']}
                        AND ploopi_mod_rss_feed.id_workspace = {$wk}";
      $db->query($rssfeed);

      parent::delete();
    }

    /**
     * Mise à jour des flux d'une catégorie
     *
     * @param int $intIdCat - Id de la catégorie (défaut: categorie de l'objet si déjà ouvert)
     * @return boolean true si ok
     */
    function updateFeedByCat($intIdCat = '')
    {
      if($intIdCat === '')
      {
        if(isset($this->fields['id']))
           $intIdCat = $this->fields['id'];
        else
           return false;
      }

      global $db;

      include_once './modules/rss/class_rss_feed.php';

      $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

      $rssfeed =  "SELECT      feed.id,
                               feed.lastvisit,
                               feed.revisit
                   FROM        ploopi_mod_rss_feed feed
                   WHERE       feed.id_workspace = {$wk}
                     AND       feed.id_cat = {$intIdCat}
                   ";

       $rssfeed_result = $db->query($rssfeed);
       while($rssfeed_row = $db->fetchrow($rssfeed_result))
       {
         if(($rssfeed_row['lastvisit'] == 0) || (ploopi_createtimestamp() - $rssfeed_row['lastvisit']) > $rssfeed_row['revisit'])
         {
           $objFeed = new rss_feed();
           $objFeed->open($rssfeed_row['id']);
           $objFeed->updatecache();
           unset($objFeed);
         }
       }
       return true;
    }
}
?>
