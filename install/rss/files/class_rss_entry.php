<?php
/*
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
 * Gestion des informations
 *
 * @package rss
 * @subpackage entry
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 * Inclusion de la class rss_feed.
 */

include_once './include/classes/data_object.php';
include_once './modules/rss/class_rss_feed.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_entry
 *
 * @package rss
 * @subpackage entry
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class rss_entry extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return rss_entry
     */
    
    function rss_entry()
    {
        parent::data_object('ploopi_mod_rss_entry', 'id');
    }

    /**
     * Enregistre une entrée d'un flux
     *
     * @return unknown
     */
    
    function save()
    {
        $ts = ploopi_unixtimestamp2timestamp($this->fields['published']);

        $rss_feed = new rss_feed();
        $rss_feed->open($this->fields['id_feed']);

        ploopi_search_create_index(_RSS_OBJECT_NEWS_ENTRY, sprintf("%06d%06d%s", $rss_feed->fields['id_cat'], $this->fields['id_feed'], $this->fields['id']), $this->fields['title'], strip_tags(html_entity_decode($this->fields['content'])), strip_tags(html_entity_decode("{$this->fields['title']} {$this->fields['subtitle']} {$this->fields['author']}")), true, $ts, $ts, $this->fields['id_user'], $this->fields['id_workspace'], $this->fields['id_module']);
        return(parent::save());
    }

    /**
     * Supprime une entrée d'un flux
     *
     * @return boolean true si suppression ok
     */
    
    function delete()
    {
        ploopi_search_remove_index(_RSS_OBJECT_NEWS_ENTRY, $this->fields['id']);
        return(parent::delete());
    }
}
?>
