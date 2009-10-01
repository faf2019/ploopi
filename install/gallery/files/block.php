<?php
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
 * Affichage du bloc de menu
 *
 * @package gallery
 * @subpackage block
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

ploopi_init_module('gallery',false,false,false);

if(isset($_GET['gallery_menu'])) $_SESSION['ploopi']['gallery']['gallery_menu'] = $_GET['gallery_menu'];

/*
$block->addmenu(_GALLERY_LABEL_SHOW, 
        ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&gallery_menu=show"),
        ($_SESSION['ploopi']['moduleid']==$menu_moduleid && $_SESSION['ploopi']['action'] == 'public' && isset($_SESSION['ploopi']['gallery']['gallery_menu']) && $_SESSION['ploopi']['gallery']['gallery_menu'] == 'show')
    );
*/
if (ploopi_isactionallowed(array(_GALLERY_ACTION_ADD_GALLERY, _GALLERY_ACTION_MODIFY_GALLERY, _GALLERY_ACTION_DELETE_GALLERY),  $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<strong>'._GALLERY_LABEL_ADMIN_GALLERY.'</strong>', 
        ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&gallery_menu=gallery"),
        ($_SESSION['ploopi']['moduleid']==$menu_moduleid && $_SESSION['ploopi']['action'] == 'admin' && isset($_SESSION['ploopi']['gallery']['gallery_menu']) && $_SESSION['ploopi']['gallery']['gallery_menu'] == 'gallery')
    );
}

if (ploopi_isactionallowed(_GALLERY_ACTION_ADMIN_GALLERY, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
{
    $block->addmenu('<strong>'._GALLERY_LABEL_ADMIN.'</strong>', 
        ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin&gallery_menu=admin"),
        ($_SESSION['ploopi']['moduleid']==$menu_moduleid && $_SESSION['ploopi']['action'] == 'admin' && isset($_SESSION['ploopi']['gallery']['gallery_menu']) && $_SESSION['ploopi']['gallery']['gallery_menu'] == 'admin')
    );
}

?>

