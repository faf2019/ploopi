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
 * Entrée de la partie gestion de galerie
 *
 * @package gallery
 * @subpackage gestion
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

 /*
 * Gestion des OP
 */
switch($op)
{
    // save
    case 'gallery_save':
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        if (isset($_GET['id_gallery'])) $objGallery->open($_GET['id_gallery']);
        $objGallery->setvalues($_POST,'gallery_');
        $objGallery->save();
        
        if(isset($_POST['_gallery_directory']))
            $objGallery->savedirectories($_POST['_gallery_directory']);
        else
            $objGallery->deldirectories();
 
        ploopi_redirect('admin.php?op=gallery_modify&id_gallery='.$objGallery->fields['id'].'&ploopi_mod_msg=_GALLERY_MESS_OK_1');
        
    break;

    // delete
    case 'gallery_delete':
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        $objGallery->open($_GET['id_gallery']);
        $objGallery->delete();
        ploopi_redirect('admin.php?galleryTabItem='._GALLERY_TAB_LIST.'&ploopi_mod_msg=_GALLERY_MESS_OK_2');
    break;
    
    // clone
    case 'gallery_clone':
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        
        $objGallery->open($_GET['id_gallery']);
        $objGallery->saveclone();
        
        ploopi_redirect('admin.php?op=gallery_modify&id_gallery='.$objGallery->fields['id'].'&ploopi_mod_msg=_GALLERY_MESS_OK_1');
    break;
    
    default:
    break;
}

if (!empty($_GET['galleryTabItem'])) $_SESSION['ploopi']['gallery']['galleryTabItem'] = $_GET['galleryTabItem'];
if (!isset($_SESSION['ploopi']['gallery']['galleryTabItem'])) $_SESSION['ploopi']['gallery']['galleryTabItem'] = '';

$tabs[_GALLERY_TAB_LIST] =
    array(
        'title' => _GALLERY_TABLIB_LIST,
        'url' => "admin.php?galleryTabItem="._GALLERY_TAB_LIST
    );

$tabs[_GALLERY_TAB_NEW] =
    array(
        'title' => _GALLERY_TABLIB_NEW,
        'url' => "admin.php?galleryTabItem="._GALLERY_TAB_NEW
    );

if($op == 'gallery_modify')
{
    $_SESSION['ploopi']['gallery']['galleryTabItem'] = _GALLERY_TAB_EDIT;
    
    $tabs[_GALLERY_TAB_EDIT] =
        array(
            'title' => _GALLERY_TABLIB_EDIT,
            'url' => "admin.php?galleryTabItem="._GALLERY_TAB_EDIT
        );
}
    
echo $skin->create_tabs($tabs, $_SESSION['ploopi']['gallery']['galleryTabItem']);

/*
 * Affichage en fonction du menu
 */
switch($_SESSION['ploopi']['gallery']['galleryTabItem'])
{
    default:
    case _GALLERY_TAB_LIST:
        include './modules/gallery/admin_gallery_list.php';
    break;

    case _GALLERY_TAB_NEW:
    case _GALLERY_TAB_EDIT:
        include './modules/gallery/admin_gallery_edit.php';
    break;
}

 ?>