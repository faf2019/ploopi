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
 * Edition des messages et sujets
 *
 * @package forum
 * @subpackage public
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$strForumAction = '';
$strForumNavigTitle = '';
$strForumClose = '';

$booForumIsAdminModerGlb = forum_IsAdminOrModer($objForumCat->fields['id'],_FORUM_ACTION_ADMIN);

// Control if categorie is open
if(($objForumCat->fields['visible'] == 0 || $objForumCat->fields['closed'] == 1) && !$booForumIsAdminModerGlb)
  ploopi_redirect('admin.php?op=forum_error&num_error=1');

$objForumMess = new forum_mess();
$objForumSubject = new forum_mess();

switch ($op)
{
  case 'mess_add': //i know id_subject
    // control
    if(!isset($_GET['id_subject']))
      ploopi_redirect('admin.php?op=forum_error&num_error=2');

    $objForumMess->init_description();
    $objForumSubject->open($_GET['id_subject']);
    $strForumAction = 'admin.php?op=mess_save&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumSubject->fields['id_subject'];
    $objForumMess->fields['title'] = 'RE:&nbsp;'.$objForumSubject->fields['title'];
    $strForumNavigTitle = $objForumSubject->fields['title'];
    $strForumNavigReturn = ploopi_urlencode("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id_subject']}");
    $strForumBlocTitle = _FORUM_MESS_LABEL_TITLE_ADD;
    // action forbiden ?
    if($objForumSubject->fields['closed'] == 1 && !$booForumIsAdminModerGlb)
      ploopi_redirect('admin.php?op=forum_error&num_error=1');

    // Message quote
    if(isset($_GET['id_quote']))
    {
      $objForumQuote = new forum_mess();
      $objForumQuote->open($_GET['id_quote']);
      $objForumMess->fields['content'] = '<div class="forum_quoted_user">'._FORUM_MESS_MESS_OF.'&nbsp;<b>'.$objForumQuote->fields['author'].'</b> :</div><div class="forum_quoted_message">'.$objForumQuote->fields['content'].'</div>';
      unset($objForumQuote);
    }
    break;
  case 'mess_edit' : // i know id_mess
    // control
    if(!isset($_GET['id_mess']))
      ploopi_redirect('admin.php?op=forum_error&num_error=2');

    $objForumMess->open($_GET['id_mess']);
    $objForumSubject->open($objForumMess->fields['id_subject']);
    $strForumAction = 'admin.php?op=mess_save&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumMess->fields['id_subject'].'&id_mess='.$objForumMess->fields['id'];
    if($objForumMess->fields['id'] == $objForumMess->fields['id_subject'])
    {
      $strForumNavigTitle = $objForumMess->fields['title'];
      $strForumNavigReturn = ploopi_urlencode("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumMess->fields['id_subject']}");
    }
    else
    {
      $strForumNavigTitle = $objForumSubject->fields['title'];
      $strForumNavigReturn = ploopi_urlencode("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}");
    }
    $strForumBlocTitle = _FORUM_MESS_LABEL_TITLE_EDIT;
    // action forbiden ?
    if(($objForumCat->fields['closed'] == 1
        || $objForumMess->fields['closed'] == 1
        || $objForumMess->fields['id_author'] != $_SESSION['ploopi']['user']['id']
        || $objForumSubject->fields['validated_id_user'] > 0)
        && !$booForumIsAdminModerGlb)
      ploopi_redirect('admin.php?op=forum_error&num_error=1');

    unset($objForumSubject);
    break;
  case 'subject_add' : // i know id_cat
    $objForumMess->init_description();
    $intForumValidate = -1;
    $strForumAction = 'admin.php?op=subject_save&id_cat='.$objForumCat->fields['id'];
    $strForumNavigReturn = ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}");
    $strForumBlocTitle = _FORUM_SUBJECT_LABEL_TITLE_ADD;
    break;
  case 'subject_edit' : // i know id_mess (it's ~ like a message)
    if(!isset($_GET['id_mess'])) ploopi_redirect('admin.php?op=forum_error&num_error=2');

    $objForumMess->open($_GET['id_mess']);
    $strForumAction = 'admin.php?op=subject_save&id_cat='.$objForumCat->fields['id'].'&id_subject='.$objForumMess->fields['id_subject'].'&id_mess='.$objForumMess->fields['id'];
    $strForumNavigReturn = ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}");
    $strForumBlocTitle = _FORUM_SUBJECT_LABEL_TITLE_EDIT;
    break;
}

echo $skin->open_simplebloc();
?>
<div class="forum_mess_edit">
  <div style="clear:both; padding:0; margin:4px 0 20px 0;">
    <font class="forum_navig">
    <?php echo _FORUM_LABEL_YOU_ARE_HERE; ?>&nbsp;
    </font>
    <button type="button" class="button_navig" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=categ"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>
      <?php echo _FORUM_LABEL_CAT; ?>
    </button>
    <button type="button" class="<? echo ($op !== 'subject_add' && $op !== 'subject_edit') ? 'button_navig' : 'button_navig_select'; ?>" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>
      <?php echo  ploopi_strcut($objForumCat->fields['title'],30); ?>
    </button>
    <?php
    if($op !== 'subject_add' && $op !== 'subject_edit')
    {
    ?>
    <button type="button" class="button_navig_select" onclick="javascript:document.location.href='<?php echo $strForumNavigReturn; ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_MESS; ?>"/>
      <?php echo  ploopi_strcut($strForumNavigTitle,30); ?>
    </button>
    <?php
    }
    ?>
  </div>

  <?php
  echo $skin->open_simplebloc($strForumBlocTitle);
  ?>
  <form method="post" action="<?php echo ploopi_urlencode($strForumAction); ?>" onSubmit="javascript:return form_validate(this);">
  <div style="padding:2px; margin:0;">
    <div style="float:left;padding:8px 0 0 0; margin:0;clear:both;font-weight:bold;"><?php echo _FORUM_MESS_LABEL_TITLE;  ?>:</div>
    <div style="clear:both;float:left;"><input type="text" id="forum_title" name="forum_title" class="text" value="<?php echo $objForumMess->fields['title']; ?>" style="width:380px" maxlength="255"/></div>
    <?php
    if($booForumIsAdminModerGlb)
    {
      if($objForumMess->new == false && $objForumMess->fields['validated'] == 0)
      {
      ?>
      <div style="float:left;padding:0 0 0 5px; margin:0;">
        <input type="button" class="button" style="width:80px;" value="Valider"
          onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_edit_validate&id_cat={$objForumMess->fields['id_cat']}&id_mess={$objForumMess->fields['id']}"); ?>'" />
      </div>
      <?php
      }
    }
    ?>
    <div style="clear:both;padding:8px 0 0 0;font-weight:bold;"><?php echo _FORUM_MESS_LABEL_MESSAGE; ?>:</div>
    <div style="clear:both;">
        <?php
        // TODO dans fckconfig.js si on active FCKConfig.EnterMode = 'br'; on est coincé dans les citations
        include_once './FCKeditor/fckeditor.php' ;

        $objFCKeditor = new FCKeditor('fck_forum_content') ;

        $objFCKeditor->BasePath = "./FCKeditor/";

        // default value
        $objFCKeditor->Value = $objForumMess->fields['content'];

        // width & height
        $objFCKeditor->Width='100%';
        $objFCKeditor->Height='300';

        $objFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH;
        $objFCKeditor->Config['CustomConfigurationsPath'] =  _PLOOPI_BASEPATH."/modules/forum/fckeditor/fckconfig.js"  ;
        $objFCKeditor->Config['EditorAreaCSS'] = _PLOOPI_BASEPATH."/modules/forum/fckeditor/fck_editorarea.css" ;
        $objFCKeditor->Create('FCKeditor_1') ;
        ?>
    </div>
  </div>
  <?php
  $strForumLabelButton = ($op == 'subject_add') ? _FORUM_SUBJECT_SAVE_NEW : _FORUM_SUBJECT_SAVE
  ?>
  <div style="clear:both;float:right;padding:4px;">
    <input type="button" class="flatbutton" value="<?php echo _FORUM_RETURN; ?>" onclick="javascript:document.location.href='<?php echo $strForumNavigReturn; ?>';">
    <input type="submit" class="flatbutton" value="<?php echo $strForumLabelButton; ?>">
  </div>
  </form>
  <?php echo $skin->close_simplebloc();  ?>
</div>
<?php echo $skin->close_simplebloc();  ?>
