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
 * Gestion des flux
 *
 * @package rss
 * @subpackage feed
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see xmlrss
 */

/**
 * Inclusion de la classe parent.
 * Inclusion de la class xmlrss qui permet de parser le contenu des flux et de passer à traver un proxy.
 */

include_once './include/classes/data_object.php';
include_once './modules/rss/class_xmlrss.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_feed
 *
 * @package rss
 * @subpackage feed
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @see xmlrss
 */

class rss_feed extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return rss_feed
     */
    function rss_feed()
    {
        parent::data_object('ploopi_mod_rss_feed');
    }


    /**
     * Récupère les infos sur le flux et l'enregistre
     *
     * @return mixed valeur de la clé primaire
     */
    function save()
    {
        if (!empty($this->fields['url']))
        {
            $xmlrss = new xmlrss($this->fields['url']);
            if (!$xmlrss->error)
            {
                $xmlrss->parse();
                $this->fields['title'] = (empty($xmlrss->feed['title'])) ? '' : $xmlrss->feed['title'];
                $this->fields['subtitle'] = (empty($xmlrss->feed['subtitle'])) ? '' : $xmlrss->feed['subtitle'];
                $this->fields['link'] = (empty($xmlrss->feed['link'])) ? '' : $xmlrss->feed['link'];
                $this->fields['updated'] = (empty($xmlrss->feed['updated'])) ? '' : $xmlrss->feed['updated'];
                $this->fields['author'] = (empty($xmlrss->feed['autor'])) ? '' : $xmlrss->feed['autor'];

                $this->fields['tpl_tag'] = ploopi_convertaccents(strtolower(strtr(trim($this->fields['tpl_tag']), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '_'))));
                if($this->fields['tpl_tag'] =='' || $this->fields['tpl_tag'] == 'rss_')
                  $this->fields['tpl_tag'] = NULL;
                else
                  if(substr($this->fields['tpl_tag'],0,4) != 'rss_') $this->fields['tpl_tag'] = 'rss_'.$this->fields['tpl_tag'];


                /*
                 * ploopi_print_r($xmlrss->header);
                 * ploopi_print_r($xmlrss->charset);
                 * ploopi_print_r($xmlrss->content);
                 * ploopi_print_r($xmlrss->feed);
                 * */
            }
        }
        return parent::save();
    }

    /**
     * Supprime un flux
     *
     * @return boolean true si suppression ok
     */
    function delete()
    {
      global $db;

      include_once './modules/rss/class_rss_entry.php';

      $wk = ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']);

      $rssentry =  "SELECT      entry.id
                   FROM        ploopi_mod_rss_entry entry
                   WHERE       entry.id_workspace IN ({$wk})
                     AND       entry.id_feed = '{$this->fields['id']}'
                   ";

       $rssentry_result = $db->query($rssentry);
       while($rssentry_row = $db->fetchrow($rssentry_result))
       {
         $objEntry = new rss_entry();
         $objEntry->open($rssentry_row['id']);
         $objEntry->delete();
         unset($objEntry);
       }
       return parent::delete();
    }

    /**
     * Vérifie qu'un flux est à jour.
     *
     * @return boolean true si le flux est à jour
     */
    function isuptodate()
    {
        return (!($this->fields['lastvisit'] == 0 || ploopi_createtimestamp() - $this->fields['lastvisit'] > $this->fields['revisit']));
    }

    /**
     * Met à jour le cache du flux
     *
     * @return none
     */
    function updatecache()
    {
        include_once './modules/rss/class_xmlrss.php';
        include_once './modules/rss/class_rss_entry.php';
        include_once './modules/rss/class_rss_feed.php';

        global $db;

        $xmlrss = new xmlrss($this->fields['url']);
        if (!$xmlrss->error)
        {
            $xmlrss->parse();

            if (!$xmlrss->error)
            {
                foreach($xmlrss->feed['entries'] as $entry)
                {
                    $rss_entry = new rss_entry();
                    if (!empty($entry['id']))
                    {
                        $entryid = md5($entry['id']);
                        if (!$rss_entry->open($entryid))
                        {
                            $published_day = ploopi_unixtimestamp2local($entry['published']);
                            $published_day = substr($published_day, 0, 10);
                            $published_day = ploopi_timestamp2unixtimestamp(ploopi_local2timestamp($published_day));

                            $rss_entry->fields['id_feed'] = $this->fields['id'];
                            $rss_entry->fields['id'] = $entryid;
                            $rss_entry->fields['title'] = $entry['title'];
                            $rss_entry->fields['subtitle'] = $entry['subtitle'];
                            $rss_entry->fields['author'] = $entry['author'];
                            $rss_entry->fields['link'] = $entry['link'];
                            $rss_entry->fields['content'] = $entry['content'];
                            $rss_entry->fields['published'] = $entry['published'];
                            $rss_entry->fields['published_day'] = $published_day;
                            $rss_entry->fields['timestp'] = ploopi_createtimestamp();
                            $rss_entry->fields['id_user'] = $this->fields['id_user'];
                            $rss_entry->fields['id_workspace'] = $this->fields['id_workspace'];
                            $rss_entry->fields['id_module'] = $this->fields['id_module'];
                            $rss_entry->save();
                        }
                    }
                }
            }
        }

        if ($xmlrss->error) $this->fields['error'] += 1;

        // update lastvisit
        $this->fields['lastvisit'] = ploopi_createtimestamp();
        $this->save();
    }

    /**
     * Met à jour le cache de tous les flux ou les flux indiqués dans le tableau en parametre (pour les filtres par ex.)
     * Attention ! Format du tableau = $tableau[$id_feed]=fields_feed[];
     *
     * @return none
     */
    function updateallfeed($arrFeed = '',$intIdModule='')
    {
      include_once './modules/rss/class_rss_feed.php';

      if(is_array($arrFeed) && count($arrFeed)>0)
      {
        foreach($arrFeed as $idFeed => $dataFeed)
        {
          if(($dataFeed['lastvisit'] == 0) || (ploopi_createtimestamp() - $dataFeed['lastvisit']) > $dataFeed['revisit'])
          {
            $objFeed = new rss_feed();
            $objFeed->open($idFeed);
            $objFeed->updatecache();
            unset($objFeed);
          }
        }
      }
      else
      {
        global $db;

        if(!$intIdModule>0) $intIdModule = $_SESSION['ploopi']['moduleid'];

        $wk = ploopi_viewworkspaces($intIdModule);

        $rssfeed =  "SELECT      feed.id,
                                 feed.lastvisit,
                                 feed.revisit
                     FROM        ploopi_mod_rss_feed feed
                     WHERE       feed.id_workspace IN ({$wk})
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
      }
    }
}
?>
