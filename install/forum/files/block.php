<?php
/*
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
 * Affichage du bloc de menu
 *
 * @package forum
 * @subpackage block
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

ploopi_init_module('forum');

$block->addmenu(_FORUM_LABEL_FORUM, ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public", ($_SESSION['ploopi']['moduleid']==$menu_moduleid && $_SESSION['ploopi']['action'] == 'public')));
/*
if(ploopi_isactionallowed(_FORUM_ACTION_ADMIN))
  $block->addmenu(_FORUM_LABEL_ADMIN, ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
*/
?>

