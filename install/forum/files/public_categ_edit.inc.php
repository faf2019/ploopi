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
 * Edition des catégories
 *
 * @package forum
 * @subpackage public
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

if(!isset($objForumCat)) $objForumCat = new forum_cat();

echo $skin->open_simplebloc();

if(isset($_GET['id_cat']))
{
  $objForumCat->open($_GET['id_cat']);
  $strForumAction = 'admin.php?op=categ_save&id_cat='.$objForumCat->fields['id'];
  $strForumLabelButton = _FORUM_CAT_SAVE;
}
else
{
  $objForumCat->init_description();
  $strForumAction = 'admin.php?op=categ_save';
  $strForumLabelButton = _FORUM_CAT_SAVE_NEW;
  $objForumCat->fields['visible'] = 1;
}

$strForumClose = ($objForumCat->fields['closed'] == 1) ? 'checked' : '';
$strForumHidden = ($objForumCat->fields['visible'] == 1) ? 'checked' : '';

?>

<script type="text/javascript">
  function form_validate(form)
  {
    if (ploopi_validatefield('<?php echo _FORUM_CAT_LABEL_TITLE; ?>',form.forum_title,'string'))
    if (ploopi_validatefield('<?php echo _FORUM_CAT_LABEL_DESCRIPTION; ?>',form.forum_description,'string'))
      return(true);

    return(false);
  }
</script>

<div class="forum_main">
  <div style="clear:both;padding:0; margin:4px 0 20px 0;">
    <font class="forum_navig">
    <?php echo _FORUM_LABEL_YOU_ARE_HERE; ?>&nbsp;
    </font>
    <button type="button" class="button_navig_select" onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php?op=categ'); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo _FORUM_LABEL_CAT; ?>
    </button>
  </div>

  <?php
  echo $skin->open_simplebloc(_FORUM_CAT_LABEL_TITLE_EDIT);
  ?>
  <div class="forum_cat_edit">
    <form method="post" action="<?php echo ploopi_urlencode($strForumAction); ?>" onSubmit="javascript:return form_validate(this);">
    <div style="padding:2px; margin:0;">
      <div style="float:left;padding:8px 0 0 0; margin:0;clear:both;font-weight:bold;"><?php echo _FORUM_CAT_LABEL_TITLE; ?>:&nbsp;</div>
      <div style="clear:both;"><input type="text" id="forum_title" name="forum_title" class="text" value="<?php echo $objForumCat->fields['title']; ?>" style="width:380px" maxlength="255"/></div>
      <div style="float:left;padding:8px 0 0 0; margin:0;clear:both;font-weight:bold;"><?php echo _FORUM_CAT_LABEL_DESCRIPTION; ?>:&nbsp;</div>
      <div style="clear:both;"><input type="text" id="forum_description" name="forum_description" class="text" value="<?php echo $objForumCat->fields['description']; ?>" style="width:380px" maxlength="255"/></div>
      <div style="clear:both;padding:8px 0 0 0; margin:0;">
        <div style="font-weight:bold;width:190px;float:left;">
          <?php echo _FORUM_CAT_LABEL_VISIBLE; ?>
        </div>
        <p class="ploopi_va" style="float:left;cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event,'forum_visible_1');">
          <span><?php echo _PLOOPI_YES ?>&nbsp;</span><input type="radio" id="forum_visible_1" name="forum_visible" style="padding:0; margin:0; cursor:pointer;" value="1" <?php if($objForumCat->fields['visible'] == 1) echo 'CHECKED'; ?>>
        </p>
        <p class="ploopi_va" style="float:left;cursor:pointer;margin: 0 0 0 10px;" onclick="javascript:ploopi_checkbox_click(event, 'forum_visible_0');">
          <span><?php echo _PLOOPI_NO ?>&nbsp;</span><input type="radio" id="forum_visible_0" name="forum_visible" style="padding:0; margin:0; cursor:pointer;" value="0" <?php if($objForumCat->fields['visible'] != 1) echo 'CHECKED'; ?>>
        </p>
      </div>
      <div style="clear:both;padding:8px 0 0 0; margin:0;">
        <div style="font-weight:bold;width:190px;float:left;">
          <?php echo _FORUM_CAT_LABEL_CLOSE; ?>
        </div>
        <p class="ploopi_va" style="float:left;cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'forum_closed_1');">
          <span><?php echo _PLOOPI_YES ?>&nbsp;</span><input type="radio" id="forum_closed_1" name="forum_closed" style="padding:0; margin:0; cursor:pointer;" value="1" <?php if($objForumCat->fields['closed'] == 1) echo 'CHECKED'; ?>>
        </p>
        <p class="ploopi_va" style="float:left;cursor:pointer;margin: 0 0 0 10px;" onclick="javascript:ploopi_checkbox_click(event, 'forum_closed_0');">
          <span><?php echo _PLOOPI_NO ?>&nbsp;</span><input type="radio" id="forum_closed_0" name="forum_closed" style="padding:0; margin:0; cursor:pointer;" value="0" <?php if($objForumCat->fields['closed'] != 1) echo 'CHECKED'; ?>>
        </p>
      </div>
      <div style="clear:both;padding:8px 0 0 0; margin:0;">
        <div style="font-weight:bold;width:190px;float:left;">
          <?php echo _FORUM_CAT_LABEL_VALIDATE; ?>
        </div>
        <p class="ploopi_va" style="float:left;cursor:pointer;" onclick="javascript:ploopi_checkbox_click(event, 'forum_mustbe_validated_1');">
          <span><?php echo _PLOOPI_YES ?>&nbsp;</span><input type="radio" id="forum_mustbe_validated_1" name="forum_mustbe_validated" style="padding:0; margin:0; cursor:pointer;" value="1" <?php if($objForumCat->fields['mustbe_validated'] == 1) echo 'CHECKED'; ?>>
        </p>
        <p class="ploopi_va" style="float:left;cursor:pointer;margin: 0 0 0 10px;" onclick="javascript:ploopi_checkbox_click(event, 'forum_mustbe_validated_0');">
          <span><?php echo _PLOOPI_NO ?>&nbsp;</span><input type="radio" id="forum_mustbe_validated_0" name="forum_mustbe_validated" style="padding:0; margin:0; cursor:pointer;" value="0" <?php if($objForumCat->fields['mustbe_validated'] != 1) echo 'CHECKED'; ?>>
        </p>
      </div>
    </div>
    <div style="clear:both;padding: 10px 0;">
      <div style="border:1px solid #c0c0c0;overflow:hidden;">
      <?
        if($objForumCat->fields['id']>0)
          ploopi_validation_selectusers(_FORUM_OBJECT_CAT,$objForumCat->fields['id']);
        else
          ploopi_validation_selectusers(_FORUM_OBJECT_CAT);
      ?>
      </div>
    </div>
    <div style="clear:both;float:right;padding:4px;">
      <input type="button" class="flatbutton" value="<?php echo _FORUM_RETURN; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php?op=categ'); ?>';">
      <input type="submit" class="flatbutton" value="<?php echo $strForumLabelButton; ?>">
    </div>
    </form>
  </div>
  <?php echo $skin->close_simplebloc();  ?>
</div>
<?php echo $skin->close_simplebloc();  ?>
