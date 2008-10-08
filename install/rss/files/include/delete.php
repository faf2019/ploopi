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
 * Suppression des données relatives au module lors d'une suppression d'instance
 *
 * @package rss
 * @subpackage delete
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Suppression des catégories
 */

$delete = "DELETE FROM ploopi_mod_rss_cat WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des entrées
 */

$delete = "DELETE FROM ploopi_mod_rss_entry WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des flux
 */

$delete = "DELETE FROM ploopi_mod_rss_feed WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des elements de filtres
 */
$db->query("SELECT id FROM ploopi_mod_rss_filter WHERE id_module = {$this->fields['id']}");
$arrIdFilter = $db->getarray();
if($arrIdFilter == false || !is_array($arrIdFilter)) $arrIdFilter[] = '0';

$delete = 'DELETE FROM ploopi_mod_rss_filter_element WHERE id_filter IN (0,'.implode(',',$arrIdFilter).')';
$db->query($delete);

/**
 * Suppression des filtres (attention pas avant la suppr des elements !!!)
 */
$delete = "DELETE FROM ploopi_mod_rss_filter WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des catégories de filtre
 */

$delete = "DELETE FROM ploopi_mod_rss_filter_cat WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des flux de filtre
 */

$delete = "DELETE FROM ploopi_mod_rss_filter_feed WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/**
 * Suppression des préférences
 */

$delete = "DELETE FROM ploopi_mod_rss_pref WHERE id_module = {$this->fields['id']}";
$db->query($delete);

?>