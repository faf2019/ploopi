<?
/*
  Copyright (c) 2009 HeXad

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
 * @package gallery
 * @subpackage delete
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */


/**
 * Suppression des tpl
 */
$delete = "DELETE FROM ploopi_mod_gallery_tpl WHERE id_module = {$this->fields['id']}";
$db->query($delete);

/*
 * Suppression des directories 
 */
$sql = $db->query("SELECT id FROM ploopi_mod_gallery WHERE id_module = '{$this->fields['id']}'");
$arrIdGallery = $db->getarray($sql, true);
if(empty($arrIdGallery)) $arrIdGallery[] = '0';

$delete = 'DELETE FROM ploopi_mod_gallery_directories WHERE id_gallery IN ('.implode(',',$arrIdGallery).')';
$db->query($delete);

/*
 * Suppression des gallery 
 */
$db->query("DELETE FROM ploopi_mod_gallery WHERE id_module = '{$this->fields['id']}'");
?>