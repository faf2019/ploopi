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
 * Entrée Administration Gallery
 *
 * @package Gallery
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('gallery');

echo $skin->create_pagetitle(ploopi_htmlentities($_SESSION['ploopi']['modulelabel']));

$sqllimitgroup = ' AND id_workspace IN ('.ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).')';

$op = (empty($_REQUEST['op'])) ? '' : $_REQUEST['op'];

switch ($_SESSION['ploopi']['gallery']['gallery_menu'])
{
    case 'error':
        include './modules/rhs/admin_error.php';
        break;
    
    case 'gallery':
        if (ploopi_isactionallowed(array(_GALLERY_ACTION_ADD_GALLERY, _GALLERY_ACTION_MODIFY_GALLERY, _GALLERY_ACTION_DELETE_GALLERY)))
            include_once './modules/gallery/admin_gallery.php';
        break;
    case 'admin':
        if (ploopi_isactionallowed(_GALLERY_ACTION_ADMIN_GALLERY))
            include_once './modules/gallery/admin_admin.php';
        break;
    default:
        break;
}
?>