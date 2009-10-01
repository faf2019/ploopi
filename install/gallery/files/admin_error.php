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
 * Affichage des erreurs
 *
 * @package gallery
 * @subpackage erreur
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

// $intNumError Peut provenir d'un include !!!

if(!isset($intNumError)) $intNumError = 0;
$strMessageError = _GALLERY_ERROR_DEFAULT;

if(isset($_GET['id_error']) && is_numeric($_GET['id_error']))
  $intNumError = $_GET['id_error'];

if(defined('_GALLERY_ERROR_'.$intNumError))
  $strMessageError = constant('_GALLERY_ERROR_'.$intNumError);

echo $skin->open_simplebloc();
?>
<div style="margin : 5px auto; padding: 5px; width: 500px; border: 1px solid red; background-color: #FFFFAA; ">
  <h3 style="color: red; padding: 0; margin: 0 0 5px 0;"><?php echo _GALLERY_ERROR.'&nbsp;MODULE&nbsp;'.$_SESSION['ploopi']['modulelabel'].'&nbsp;:&nbsp;'.$intNumError; ?></h3>
  <hr style="padding: 0; margin: 0;"/>
  <?php echo $strMessageError; ?>
</div>
<?php
echo $skin->close_simplebloc();
?>