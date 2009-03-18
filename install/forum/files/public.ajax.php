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
 * Partie public - AJAX
 *
 * @package forum
 * @subpackage public
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

switch($op)
{
  // Save the order of categories
  case 'ajax_save_posit_cat':
    if(isset($_GET['forum_values_inner_categ']) && ploopi_isactionallowed(_FORUM_ACTION_ADMIN))
    {
      $objForumCatMove = new forum_cat;
      $i = 1;
      foreach($_GET['forum_values_inner_categ'] as $idCat)
      {
        $objForumCatMove->open($idCat);
        if($objForumCatMove->fields['position'] != $i)
        {
          $objForumCatMove->fields['position'] = $i;
          $objForumCatMove->save();
        }
        $i++;
      }
    }
    break;
  default:
    break;
}
ploopi_die();
?>