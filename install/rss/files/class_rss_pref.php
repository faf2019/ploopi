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
 * Gestion des préférences
 *
 * @package rss
 * @subpackage pref
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_rss_pref
 *
 * @package rss
 * @subpackage pref
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class rss_pref extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return rss_pref
     */

    function rss_pref()
    {
        parent::data_object('ploopi_mod_rss_pref','id_module','id_user','id_feed_cat_filter');
    }

    /**
     * Enregistre le flux favori (préférence) pour un utilisateur dans un module
     *
     * @return mixed valeur de la clé primaire
     */
    function save()
    {
        global $db;

        $db->query("DELETE FROM ploopi_mod_rss_pref where id_user = '{$this->fields['id_user']}' and  id_module = '{$this->fields['id_module']}'");
        parent::save();
    }

}
?>
