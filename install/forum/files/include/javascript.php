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
 * Fonctions javascript dynamiques
 *
 * @package forum
 * @subpackage javascript
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

if(isset($_GET['op']) && ($_GET['op'] == 'mess_add' || $_GET['op'] == 'mess_edit' || $_GET['op'] == 'subject_add' || $_GET['op'] == 'subject_edit'))
{
  ploopi_init_module('forum');
  ?>
  function form_validate(form)
  {
    var fck_instance = $('fck_forum_content___Frame').contentWindow.FCKeditorAPI.GetInstance('fck_forum_content');
    // get fckeditor content
    $('fck_forum_content').value = fck_instance.GetData(true);
    <?
    if($_GET['op'] == 'subject_add' || $_GET['op'] == 'subject_edit')
      echo 'if (ploopi_validatefield(\''._FORUM_MESS_LABEL_TITLE.'\',form.forum_title,\'string\'))'."\r\n";
    ?>
    if (ploopi_validatefield('<?php echo _FORUM_MESS_LABEL_MESSAGE; ?>',form.fck_forum_content,'string'))
      return(true);

    return(false);
  }
<?
}
?>
