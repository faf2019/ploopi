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
 * @package Newsletter
 * @subpackage delete
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Suppression des envois
 */

$db->query("SELECT id FROM ploopi_mod_newsletter_letter WHERE id_module = '{$this->fields['id']}'");
$arrIdNewsletter = $db->getarray();
if($arrIdNewsletter == false || !is_array($arrIdNewsletter)) $arrIdNewsletter[] = '0';

$delete = 'DELETE FROM ploopi_mod_newsletter_send WHERE id_letter IN (0,'.implode(',',$arrIdFilter).')';
$db->query($delete);

/**
 * Suppression des newsletter
 */

$delete = "DELETE FROM ploopi_mod_newsletter_letter WHERE id_module = '{$this->fields['id']}'";
$db->query($delete);

/**
 * Suppression des inscriptions
 */

$delete = "DELETE FROM ploopi_mod_newsletter_subscriber WHERE id_module = '{$this->fields['id']}'";
$db->query($delete);

/**
 * Suppression des paramètres
 */

$delete = "DELETE FROM ploopi_mod_newsletter_param WHERE id_module = '{$this->fields['id']}'";
$db->query($delete);
?>