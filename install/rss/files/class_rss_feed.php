<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * @copyright Netlor, Ovensia
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
     */
    
    function save()
    {
        if (!empty($this->fields['url']))
        {
            $xmlrss = new xmlrss($this->fields['url']);
            if (!$xmlrss->error)
            {
                $xmlrss->parse();        
                $this->fields['title'] = (empty($xmlrss->feed['title'])) ? '' : ploopi_htmlpurifier($xmlrss->feed['title']);
                $this->fields['subtitle'] = (empty($xmlrss->feed['subtitle'])) ? '' : ploopi_htmlpurifier($xmlrss->feed['subtitle']);
                $this->fields['link'] = (empty($xmlrss->feed['link'])) ? '' : $xmlrss->feed['link'];
                $this->fields['updated'] = (empty($xmlrss->feed['updated'])) ? '' : $xmlrss->feed['updated'];
                $this->fields['author'] = (empty($xmlrss->feed['autor'])) ? '' : $xmlrss->feed['autor'];
                
                /*
                 * ploopi_print_r($xmlrss->header);
                 * ploopi_print_r($xmlrss->charset);
                 * ploopi_print_r($xmlrss->content);
                 * ploopi_print_r($xmlrss->feed);
                 * */
            }
        }
        parent::save();
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
                            $rss_entry->fields['id_feed'] = $this->fields['id'];
                            $rss_entry->fields['id'] = $entryid;
                            $rss_entry->fields['title'] = $entry['title'];
                            $rss_entry->fields['subtitle'] = $entry['subtitle'];
                            $rss_entry->fields['author'] = $entry['author'];
                            $rss_entry->fields['link'] = $entry['link'];
                            $rss_entry->fields['content'] = $entry['content'];
                            $rss_entry->fields['published'] = $entry['published'];
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
}
?>
