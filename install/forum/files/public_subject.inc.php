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

$strForumClassLine = '';
$strForumLimit = '';
$strForumModerator = '';

// Control if it's admin
$booForumIsAdminModerGlb = ploopi_isactionallowed(_FORUM_ACTION_ADMIN);

// List moderator(s)
$arrForumModerat = forum_ListModer($objForumCat->fields['id']);
if(is_array($arrForumModerat) && count($arrForumModerat) > 0)
{
  // if it's a admin or a moder
  $booForumIsAdminModerGlb = ($booForumIsAdminModerGlb || array_key_exists($_SESSION['ploopi']['user']['id'],$arrForumModerat));
  foreach($arrForumModerat as $value)
  {
    if(empty($strForumModerator))
      $strForumModerator = _FORUM_MODERATOR.': '.ploopi_htmlentities($value['login']);
    else
      $strForumModerator .= ', '.$value['login'];
  }
}

// Navigate in subject... reset page for message
$_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['page'] = 1;

// Subject Param
$arrFiltre = &$_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject'];

//If it's not the good list of categories reset page
if($arrFiltre['id'] != $objForumCat->fields['id'])
{
  $arrFiltre['page'] = 1;
  $arrFiltre['id'] = $objForumCat->fields['id'];
}

// Get nb subject in this categ
$strForumSqlQuery = "SELECT COUNT(ploopi_mod_forum_mess.id) AS nbSubject
  FROM ploopi_mod_forum_mess
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.id = ploopi_mod_forum_mess.id_subject
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_cat";
$objForumSqlResult = $db->query($strForumSqlQuery);
$intForumNbSubject = $db->fetchrow($objForumSqlResult);
$intForumNbPages = ($intForumNbSubject['nbSubject'] > 0 && $arrFiltre['limit'] > 0) ? ceil($intForumNbSubject['nbSubject']/$arrFiltre['limit']) : 1;
unset($objForumSqlResult);

// Correct num page
if($arrFiltre['page']>$intForumNbPages || $arrFiltre['page'] == 0) $arrFiltre['page'] = $intForumNbPages;

// Find response validated by subject
$strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_subject,
COUNT(ploopi_mod_forum_mess.id) AS nbMessValid
FROM ploopi_mod_forum_mess
WHERE ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
  AND ploopi_mod_forum_mess.validated = 1
  AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id
  AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
GROUP BY ploopi_mod_forum_mess.id_subject";
$objForumSqlResult = $db->query($strForumSqlQuery);
while ($arrForumFields = $db->fetchrow($objForumSqlResult))
{
  $arrForumInfoMess[$arrForumFields['id_subject']] = $arrForumFields;
}

if($booForumIsAdminModerGlb)
{
  // Find response NO validated by subject
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_subject,
  COUNT(ploopi_mod_forum_mess.id) AS nbMessNoValid
  FROM ploopi_mod_forum_mess
  WHERE ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.validated = 0
    AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_subject";
  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    if(isset($arrForumInfoMess[$arrForumFields['id_subject']]))
      $arrForumInfoMess[$arrForumFields['id_subject']] += $arrForumFields;
    else
      $arrForumInfoMess[$arrForumFields['id_subject']] = $arrForumFields;
  }
}
else
{
  // Find YOUR response NO validated by subject
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_subject,
  COUNT(ploopi_mod_forum_mess.id) AS nbMYMessNoValid
  FROM ploopi_mod_forum_mess
  WHERE ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.validated = 0
    AND ploopi_mod_forum_mess.id_author = {$_SESSION['ploopi']['user']['id']}
    AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_subject";
  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    if(isset($arrForumInfoMess[$arrForumFields['id_subject']]))
      $arrForumInfoMess[$arrForumFields['id_subject']] += $arrForumFields;
    else
      $arrForumInfoMess[$arrForumFields['id_subject']] = $arrForumFields;
  }
  // Filter only messages validated or your messages
  $strForumSqlAddFiltre = " AND (ploopi_mod_forum_mess.validated = 1 OR ploopi_mod_forum_mess.id_author = {$_SESSION['ploopi']['user']['id']})";
}

switch($arrFiltre['orderby'])
{
  // Order by last mess
  case 'timestp':
  default :
    $strForumOrderBy = " ORDER BY timestpmax {$arrFiltre['orderin']}";
    break;
  case 'title':
    $strForumOrderBy = " ORDER BY ploopi_mod_forum_mess.title {$arrFiltre['orderin']}";
    break;
}

// Get LIMIT with page (if limit = 0 => no limit)
if($arrFiltre['limit'] > 0)
  $strForumLimit = ' LIMIT '.($arrFiltre['limit']*($arrFiltre['page']-1)).','.$arrFiltre['limit'];

// Principal request
$strForumSqlQuery = "SELECT ploopi_mod_forum_mess.*,
  MAX(ploopi_mod_forum_mess.timestp) AS timestpmax
  FROM ploopi_mod_forum_mess
  WHERE ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
    AND ploopi_mod_forum_mess.id = ploopi_mod_forum_mess.id_subject
    {$strForumSqlAddFiltre}
  GROUP BY ploopi_mod_forum_mess.id_subject
  {$strForumOrderBy}
  {$strForumLimit}";

echo $skin->open_simplebloc();
?>
<div class="forum_main">
  <div style="clear:both;padding:0; margin:4px 0 20px 0;">
    <font class="forum_navig">
    <?php echo _FORUM_LABEL_YOU_ARE_HERE; ?>&nbsp;
    </font>
    <button type="button" class="button_navig" onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php?op=categ'); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo _FORUM_LABEL_CAT; ?>
    </button>
    <button type="button" class="button_navig_select" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo ploopi_htmlentities(ploopi_strcut($objForumCat->fields['title'],30)); ?>
    </button>
  </div>
  <div style="overflow: auto; padding: 0; margin: 0 0 2px 0;">
  <?php
  // Pages button's
  $strForumButtonsPages = forum_pages("admin.php?op=subject&id_cat={$objForumCat->fields['id']}",$intForumNbPages,$arrFiltre['page']);
  if($strForumButtonsPages <> '')
  {
    ?>
    <div style="float: right; padding: 0; margin: 1px;"><?php echo $strForumButtonsPages; ?></div>
    <?php
  }
  if($objForumCat->fields['closed'] == 0 || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="float:left; padding: 0 12px 0 0;"">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject_add&id_cat={$objForumCat->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_FOLDER_NEW; ?>" style="padding:0;margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo _FORUM_TOOLBAR_NEW_SUBJECT; ?>
      </button>
    </div>
    <?php
  }
  ?>
  </div>
  <?php
  echo $skin->open_simplebloc(ploopi_htmlentities($objForumCat->fields['title']));
  ?>
  <div id="forum_main_subject" class="ploopi_explorer_main" style="visibility:visible;">

    <div id="forum_column_subject_0" class="ploopi_explorer_column" style="right: 75px;"></div>
    <div id="forum_column_subject_1" class="ploopi_explorer_column" style="right: 225px;"></div>
    <?php
    if($booForumIsAdminModerGlb)
    {
      ?>
      <div id="forum_column_subject_2" class="ploopi_explorer_column" style="right: 315px;"></div>
      <?php
    }
    ?>
    <div id="forum_column_subject_3" class="ploopi_explorer_column" style="left: 50px;"></div>

    <div style="position: relative;">
      <div id="forum_title_subject" class="ploopi_explorer_title">
        <div class="ploopi_explorer_element" style="width: 75px; float: right; text-align: center;">
          <p><span><?php echo _FORUM_SUBJECT_COL_MESS; ?></span></p>
        </div>
        <a href="<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}&order=timestp"); ?>" class="ploopi_explorer_element" style="width: 150px; float: right; text-align: center;">
          <p><span><?php echo _FORUM_SUBJECT_COL_LASTMESS; if($arrFiltre['orderby']=='timestp') { echo '&nbsp;'.constant(strtoupper('_FORUM_IMG_'.$arrFiltre['orderin'])); } ?></span></p>
        </a>
        <?php
        if($booForumIsAdminModerGlb)
        {
          ?>
          <div class="ploopi_explorer_element" style="width: 90px; float: right;"><p></p></div>
          <?php
        }
        ?>
        <div class="ploopi_explorer_element" style="width: 50px; float: left;"><p></p></div>
        <a href="<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}&order=title"); ?>" class="ploopi_explorer_element" style="overflow: auto; text-align: left;">
          <p><span><?php if($arrFiltre['orderby']=='title') { echo constant(strtoupper('_FORUM_IMG_'.$arrFiltre['orderin'])).'&nbsp;'; } echo _FORUM_SUBJECT_COL_SUBJECT; ?></span></p>
        </a>
      </div>
      <div id="forum_values_outer_subject">
        <div id="forum_values_inner_subject">
        <?php
        $objForumSqlResult = $db->query($strForumSqlQuery);
        while ($arrForumFields = $db->fetchrow($objForumSqlResult))
        {
          // Class for line
          $strForumClassLine = ($strForumClassLine == 'ploopi_explorer_line_2') ? 'ploopi_explorer_line_1' : 'ploopi_explorer_line_2';

          // Search the last message
          $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id,
            ploopi_mod_forum_mess.author,
            ploopi_mod_forum_mess.timestp
          FROM ploopi_mod_forum_mess
          WHERE ploopi_mod_forum_mess.id_subject = {$arrForumFields['id_subject']}
          {$strForumSqlAddFiltre}
          ORDER BY ploopi_mod_forum_mess.timestp DESC
          LIMIT 1";
          $objForumSqlLastMess = $db->query($strForumSqlQuery);
          $arrForumLastMess = $db->fetchrow($objForumSqlLastMess);
          unset($objForumSqlLastMess);

          // date time last message
          $arrForumDateLastMess = ploopi_timestamp2local($arrForumLastMess['timestp']);
          // Last message (response or subject)
          $strForumLastMess = $arrForumDateLastMess['date'].'&nbsp;'.$arrForumDateLastMess['time'].'<br/><font style="font-size:0.8em;">'._FORUM_BY.'&nbsp;'.ploopi_htmlentities($arrForumLastMess['author']).'</font>';

          $arrForumFields['nbMessValid'] = (isset($arrForumInfoMess[$arrForumFields['id']]['nbMessValid'])) ? $arrForumInfoMess[$arrForumFields['id']]['nbMessValid'] : 0;
          $arrForumFields['nbMessNoValid'] = (isset($arrForumInfoMess[$arrForumFields['id']]['nbMessNoValid'])) ? $arrForumInfoMess[$arrForumFields['id']]['nbMessNoValid'] : 0;
          $arrForumFields['nbMYMessNoValid'] = (isset($arrForumInfoMess[$arrForumFields['id']]['nbMYMessNoValid'])) ? $arrForumInfoMess[$arrForumFields['id']]['nbMYMessNoValid'] : 0;

          // Responses by subject
          $strForumResponse = $arrForumFields['nbMessValid'];
          if($arrForumFields['nbMessNoValid'] > 0) $strForumResponse .= '&nbsp;/&nbsp;<font color="red">'.$arrForumFields['nbMessNoValid'].'</font>';
          if($arrForumFields['nbMYMessNoValid'] > 0) $strForumResponse .= '&nbsp;/&nbsp;<font color="green">'.$arrForumFields['nbMYMessNoValid'].'</font>';

          //Icon
          if($objForumCat->fields['closed'] == 1 || $arrForumFields['closed'] == 1)
            $strIcon = _FORUM_IMG_32_LOCK;
          elseif($arrForumFields['nbMessValid'] > 0)
          {
            if($booForumIsAdminModerGlb && ($arrForumFields['nbMessNoValid'] > 0 || $arrForumFields['validated'] == 0))
              $strIcon =  _FORUM_IMG_32_FOLDER_TOVALID;
            else
              $strIcon =  _FORUM_IMG_32_FOLDER;
          }
          else
          {
            if($booForumIsAdminModerGlb && ($arrForumFields['nbMessNoValid'] > 0 || $arrForumFields['validated'] == 0))
              $strIcon = _FORUM_IMG_32_FOLDER_EMPTY_TOVALID;
            else
              $strIcon = _FORUM_IMG_32_FOLDER_EMPTY;
          }

          // Title
          $strForumTitle = ($arrForumFields['validated'] == 0) ? $strForumTitle = '<font color="red">'.ploopi_htmlentities($arrForumFields['title']).'</font>&nbsp;-&nbsp;<i>'._FORUM_STAY_VALIDATED.'</i>' : ploopi_htmlentities($arrForumFields['title']);

          // date time subject
          $arrForumDateCreate = ploopi_timestamp2local($arrForumFields['timestp']);

          ?>
          <div id="idSubject_<?php echo $arrForumFields['id']; ?>" name="idSubject_<?php echo $arrForumFields['id']; ?>" class="<?php echo $strForumClassLine; ?>">
            <div class="ploopi_explorer_element" style="width: 75px; height: 100%; float: right; text-align: center;">
              <p><span><?php echo $strForumResponse;  ?></span></p>
            </div>
            <a class="ploopi_explorer_element" style="width: 150px; height: 100%; float: right; text-align: right;" href="<?php echo ploopi_urlencode("admin.php?op=search&id_mess={$arrForumLastMess['id']}"); ?>">
              <p class="forum_test"><span><?php echo $strForumLastMess; ?></span></p>
            </a>
            <?php
            // if allowed add edit delete on subject
            if($booForumIsAdminModerGlb)
            {
            ?>
              <div class="ploopi_explorer_element" style="width: 90px; float: right;text-align: center;z-index: 1000">
                <input type="button" class="button" style="margin:1px;width:80px;" value="<?php echo _FORUM_EDIT; ?>"
                  onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject_edit&id_cat={$objForumCat->fields['id']}&id_mess={$arrForumFields['id']}"); ?>'" />
                <input type="button" class="button" style="margin:1px;width:80px;" value="<?php echo _FORUM_DELETE; ?>"
                  onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=subject_delete&id_cat={$objForumCat->fields['id']}&id_mess={$arrForumFields['id']}"); ?>','<?php echo _FORUM_CONFIRM_DELETE_SUBJECT; ?>');" />
              </div>
            <?php
            }
            ?>
            <a class="ploopi_explorer_link" href="<?php echo ploopi_urlencode("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$arrForumFields['id']}"); ?>">
              <div class="ploopi_explorer_element" style="width: 50px; float: left; text-align: center; height: 40px;">
                <p><img align="middle" src="<?php echo $strIcon; ?>"/></p>
              </div>
              <div class="ploopi_explorer_element">
                <p>
                  <b><?php echo $strForumTitle; ?></b>
                  <br/><font style="font-size:0.8em;"><?php echo ploopi_htmlentities($arrForumFields['author']).'&nbsp;('.$arrForumDateCreate['date'].'&nbsp;'.$arrForumDateCreate['time'].')'; ?></font>
                  <?php
                    if(isset($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']) && $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']['id'] == $arrForumFields['id'])
                    {
                      echo ploopi_htmlentities($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']['mess']);
                      unset($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']);
                    }
                  ?>
                </p>

              </div>
            </a>
          </div>
        <?php
        }
        ?>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript">forum_array_renderupdate('subject');</script>
  <?php
  echo $skin->close_simplebloc();
  ?>
  <div style="overflow:auto;padding:0;margin:0;">
  <?php
  // Pages button's
  $strForumButtonsPages = forum_pages("admin.php?op=subject&id_cat={$objForumCat->fields['id']}",$intForumNbPages,$arrFiltre['page']);
  if(!empty($strForumButtonsPages))
  {
    ?>
    <div style="float:right;margin:1px;"><?php echo $strForumButtonsPages; ?></div>
    <?php
  }
  if($objForumCat->fields['closed'] == 0 || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="float:left">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject_add&id_cat={$objForumCat->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_FOLDER_NEW; ?>" style="padding:0;margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo _FORUM_TOOLBAR_NEW_SUBJECT; ?>
      </button>
    </div>
    <?php
  }
  if(!empty($strForumModerator))
  {
    ?>
    <div style="float:left; padding:0;margin:0 0 0 15px; font-size:0.9em;"><?php echo $strForumModerator; ?></div>
    <?php
  }
  if($objForumCat->fields['closed'] == 0 || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="clear:both; padding: 5px 0 0 0;">
      <?php ploopi_subscription(_FORUM_OBJECT_SUBJECT, $objForumCat->fields['id'], array(_FORUM_ACTION_ADD_SUBJECT,_FORUM_ACTION_ADD_MESSAGE));  ?>
    </div>
    <?php
  }
  ?>
  </div>
</div>
<?php
echo $skin->close_simplebloc();
?>
