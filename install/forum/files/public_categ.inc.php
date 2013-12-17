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
 * Affichage des catégories
 *
 * @package forum
 * @subpackage public
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$strForumClassLine = '';
$arrForumInfoCat = array();

$booForumActionAllowed = ploopi_isactionallowed(_FORUM_ACTION_ADMIN);

$booForumIsAdminModerGlb = forum_IsAdminOrModer(-1,_FORUM_ACTION_ADMIN);

$_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['page'] = 1;
$_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['page'] = 1;

if($booForumIsAdminModerGlb) // More info for admin/moderator
{
  // Nb SUBJECTS NOT validated
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_cat,
    COUNT(ploopi_mod_forum_mess.id) AS nbSubjectNoValid
    FROM ploopi_mod_forum_mess
    WHERE $strForumSqlLimitGroupMess
      AND ploopi_mod_forum_mess.validated = 0
      AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
      AND ploopi_mod_forum_mess.id_subject = ploopi_mod_forum_mess.id
    GROUP BY ploopi_mod_forum_mess.id_cat";

  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    $arrForumInfoCat[$arrForumFields['id_cat']] = $arrForumFields;
  }

  // Nb responses NO validated
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_cat,
    COUNT(ploopi_mod_forum_mess.id) AS nbMessNoValid
    FROM ploopi_mod_forum_mess
    WHERE $strForumSqlLimitGroupMess
      AND ploopi_mod_forum_mess.validated = 0
      AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id
      AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
    GROUP BY ploopi_mod_forum_mess.id_cat";

  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    if(isset($arrForumInfoCat[$arrForumFields['id_cat']]))
      $arrForumInfoCat[$arrForumFields['id_cat']] += $arrForumFields;
    else
      $arrForumInfoCat[$arrForumFields['id_cat']] = $arrForumFields;
  }
}
else
{
  // Nb of MY SUBJECTS NOT validated
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_cat,
    COUNT(ploopi_mod_forum_mess.id) AS nbMYSubjectNoValid
    FROM ploopi_mod_forum_mess
    WHERE $strForumSqlLimitGroupMess
      AND ploopi_mod_forum_mess.validated = 0
      AND ploopi_mod_forum_mess.id_author = {$_SESSION['ploopi']['user']['id']}
      AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
      AND ploopi_mod_forum_mess.id_subject = ploopi_mod_forum_mess.id
    GROUP BY ploopi_mod_forum_mess.id_cat";

  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    if(isset($arrForumInfoCat[$arrForumFields['id_cat']]))
      $arrForumInfoCat[$arrForumFields['id_cat']] += $arrForumFields;
    else
      $arrForumInfoCat[$arrForumFields['id_cat']] = $arrForumFields;
  }

  // Nb of MY responses NO validated
  $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_cat,
    COUNT(ploopi_mod_forum_mess.id) AS nbMYMessNoValid
    FROM ploopi_mod_forum_mess
    WHERE $strForumSqlLimitGroupMess
      AND ploopi_mod_forum_mess.validated = 0
      AND ploopi_mod_forum_mess.id_author = {$_SESSION['ploopi']['user']['id']}
      AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id
      AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
    GROUP BY ploopi_mod_forum_mess.id_cat";

  $objForumSqlResult = $db->query($strForumSqlQuery);
  while ($arrForumFields = $db->fetchrow($objForumSqlResult))
  {
    if(isset($arrForumInfoCat[$arrForumFields['id_cat']]))
      $arrForumInfoCat[$arrForumFields['id_cat']] += $arrForumFields;
    else
      $arrForumInfoCat[$arrForumFields['id_cat']] = $arrForumFields;
  }
  // FILTER : Only visible categories
  $strForumSqlAddFiltre .= ' AND ploopi_mod_forum_cat.visible = 1';
}

// Nb SUBJECTS validated
$strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_cat,
  COUNT(ploopi_mod_forum_mess.id) AS nbSubjectValid
  FROM ploopi_mod_forum_mess
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.validated = 1
    AND ploopi_mod_forum_mess.id_subject = ploopi_mod_forum_mess.id
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_cat";

$objForumSqlResult = $db->query($strForumSqlQuery);
while ($arrForumFields = $db->fetchrow($objForumSqlResult))
{
  if(isset($arrForumInfoCat[$arrForumFields['id_cat']]))
    $arrForumInfoCat[$arrForumFields['id_cat']] += $arrForumFields;
  else
    $arrForumInfoCat[$arrForumFields['id_cat']] = $arrForumFields;
}

// Nb responses validated and all categories
$strForumSqlQuery = "SELECT ploopi_mod_forum_cat.*,
  COUNT(ploopi_mod_forum_mess.id) AS nbMessValid
  FROM ploopi_mod_forum_cat
  LEFT JOIN ploopi_mod_forum_mess
    ON (ploopi_mod_forum_cat.id = ploopi_mod_forum_mess.id_cat
        AND ploopi_mod_forum_mess.validated = 1
        AND ploopi_mod_forum_mess.id_subject <> ploopi_mod_forum_mess.id)
  WHERE $strForumSqlLimitGroupCat
  $strForumSqlAddFiltre
  AND ploopi_mod_forum_cat.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_cat.id
  ORDER BY ploopi_mod_forum_cat.position";
?>
<div class="forum_main">
  <div style="clear:both;padding:0; margin:4px 0 20px 0;">
    <font class="forum_navig">
    <?php echo _FORUM_LABEL_YOU_ARE_HERE; ?>&nbsp;
    </font>
    <button type="button" class="button_navig_select" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=categ"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo _FORUM_LABEL_CAT; ?>
    </button>
  </div>

<?php
// Menu
if($booForumActionAllowed)
{
  ?>
  <div style="overflow:auto;padding:0;margin: 0 0 2px 0;">
    <div style="float:left; padding: 0 12px 0 0;">
    <button type="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=categ_add"); ?>'">
      <img src="<?php echo _FORUM_IMG_16_FOLDER_NEW; ?>" style="border:none; margin:0 0 -3px 0; padding:0;">&nbsp;<?php echo _FORUM_TOOLBAR_NEW_CAT; ?>
    </button>
    </div>
  </div>
  <?php
}

  // Title of subject
  echo $skin->open_simplebloc(_FORUM_LABEL_CAT);
  ?>

  <div id="forum_main_categ" class="ploopi_explorer_main" style="visibility:visible;">
    <div id="forum_column_cat_0" class="ploopi_explorer_column" style="right: 85px;"></div>
    <div id="forum_column_cat_1" class="ploopi_explorer_column" style="right: 170px;"></div>
    <div id="forum_column_cat_3" class="ploopi_explorer_column" style="right: 300px;"></div>
    <?php
    // For button edit/delete
    if($booForumActionAllowed)
    {
      ?>
      <div id="forum_column_cat_2" class="ploopi_explorer_column" style="right: 390px;"></div>
      <?php
    }
    ?>
    <div id="forum_column_cat_4" class="ploopi_explorer_column" style="left: 50px;"></div>

    <div style="position: relative;">
      <div id="forum_title_categ" class="ploopi_explorer_title">
        <div class="ploopi_explorer_element" style="width: 85px; float: right; text-align: center;">
          <p><span><?php echo _FORUM_CAT_COL_MESS; ?></span></p>
        </div>
        <div class="ploopi_explorer_element" style="width: 85px; float: right; text-align: center;">
          <p><span><?php echo _FORUM_CAT_COL_SUBJECT; ?></span></p>
        </div>
        <div class="ploopi_explorer_element" style="width: 130px; float: right; text-align: center;">
          <p><span><?php echo _FORUM_CAT_COL_LASTMESS; ?></span></p>
        </div>
        <?php
        // For button edit/delete
        if($booForumActionAllowed)
        {
          ?>
          <div class="ploopi_explorer_element" style="width: 90px; float: right;"><p></p></div>
          <?php
        }
        ?>
        <div class="ploopi_explorer_element" style="width: 50px; float: left; text-align: center;">
          <p><span>&nbsp;</span></p>
        </div>
        <div class="ploopi_explorer_element" style="overflow: auto; text-align: left;">
          <p><span><?php echo _FORUM_CAT_COL_FORUM; ?></span></p>
        </div>
      </div>
      <div id="forum_values_outer_categ">
        <div id="forum_values_inner_categ">
        <?php
        $objForumSqlResult = $db->query($strForumSqlQuery);
        while ($arrForumFields = $db->fetchrow($objForumSqlResult))
        {
          // Search Moderat
          $booForumIsAdminModerCat = $booForumActionAllowed;
          $strForumModerator = '&nbsp;';
          $arrForumModerat = forum_ListModer($arrForumFields['id']);
          if(is_array($arrForumModerat) && count($arrForumModerat) > 0)
          {
            // if it's a admin or a moder
            $booForumIsAdminModerCat = ($booForumActionAllowed || array_key_exists($_SESSION['ploopi']['user']['id'],$arrForumModerat));
            foreach($arrForumModerat as $value)
            {
              if($strForumModerator == '&nbsp;')
                $strForumModerator = '--- &nbsp;'._FORUM_MODERATOR.':&nbsp;'.ploopi_htmlentities($value['login']);
              else
                $strForumModerator .= ', '.$value['login'];
            }
            $strForumModerator .= '&nbsp;---';
          }

          // Not show if not allowed
          if($arrForumFields['visible'] == 1 || $booForumIsAdminModerCat)
          {
            // Add info in $arrForumFields
            $arrForumFields['nbSubjectValid'] = (isset($arrForumInfoCat[$arrForumFields['id']]['nbSubjectValid'])) ? $arrForumInfoCat[$arrForumFields['id']]['nbSubjectValid'] : 0;
            $arrForumFields['nbSubjectNoValid'] = (isset($arrForumInfoCat[$arrForumFields['id']]['nbSubjectNoValid'])) ? $arrForumInfoCat[$arrForumFields['id']]['nbSubjectNoValid'] : 0;
            $arrForumFields['nbMessNoValid'] = (isset($arrForumInfoCat[$arrForumFields['id']]['nbMessNoValid'])) ? $arrForumInfoCat[$arrForumFields['id']]['nbMessNoValid'] : 0;
            $arrForumFields['nbMYSubjectNoValid'] = (isset($arrForumInfoCat[$arrForumFields['id']]['nbMYSubjectNoValid'])) ? $arrForumInfoCat[$arrForumFields['id']]['nbMYSubjectNoValid'] : 0;
            $arrForumFields['nbMYMessNoValid'] = (isset($arrForumInfoCat[$arrForumFields['id']]['nbMYMessNoValid'])) ? $arrForumInfoCat[$arrForumFields['id']]['nbMYMessNoValid'] : 0;

            // Class for lines table's
            $strForumClassLine = ($strForumClassLine == 'ploopi_explorer_line_2') ? 'ploopi_explorer_line_1' : 'ploopi_explorer_line_2';

            // Nb subject and messages in this categ (WITH no validated if you are allowed !)
            $strForumNbSubject = $arrForumFields['nbSubjectValid'];
            $strForumNbMess = $arrForumFields['nbMessValid'];

            // Add red/green color if not validated for subject and message
            if($booForumIsAdminModerCat)
            {
              if($arrForumFields['nbSubjectNoValid']>0)  $strForumNbSubject .= '&nbsp;/&nbsp;<font color="red">'.$arrForumFields['nbSubjectNoValid'].'</font>';
              if($arrForumFields['nbMessNoValid']>0)     $strForumNbMess .= '&nbsp;/&nbsp;<font color="red">'.$arrForumFields['nbMessNoValid']."</font>";
            }
            if($arrForumFields['nbMYSubjectNoValid']>0)  $strForumNbSubject .= '&nbsp;/&nbsp;<font color="green">'.$arrForumFields['nbMYSubjectNoValid'].'</font>';
            if($arrForumFields['nbMYMessNoValid']>0)     $strForumNbMess .= '&nbsp;/&nbsp;<font color="green">'.$arrForumFields['nbMYMessNoValid']."</font>";

            // Get the last message
            $strForumSqlQuery = "SELECT ploopi_mod_forum_mess.timestp,
              ploopi_mod_forum_mess.author,
              ploopi_mod_forum_mess.id
              FROM ploopi_mod_forum_mess
              WHERE ploopi_mod_forum_mess.id_cat = {$arrForumFields['id']}";
              if(!$booForumIsAdminModerCat)
                $strForumSqlQuery .= " AND ploopi_mod_forum_mess.validated = 1";
            $strForumSqlQuery .= " ORDER BY ploopi_mod_forum_mess.timestp DESC LIMIT 1";

            $objForumSqlInfoLastMess = $db->query($strForumSqlQuery);
            if($db->numrows())
            {
              $arrForumFieldsLastMess = $db->fetchrow($objForumSqlInfoLastMess);
              $arrForumDateLastMess = ploopi_timestamp2local($arrForumFieldsLastMess['timestp']);
              $strForumLastMess = $arrForumDateLastMess['date'].'&nbsp;'.$arrForumDateLastMess['time'].'<br/><font style="font-size:0.8em;">'._FORUM_BY.'&nbsp;'.ploopi_htmlentities($arrForumFieldsLastMess['author']).'</font>';
            }
            else
              $strForumLastMess = '';

            // Icon
            $strIcon = $strIcon2 = '';
            if($arrForumFields['visible'] == 0 && $booForumIsAdminModerCat)
            {
              if($arrForumFields['closed'] == 1)
                $strIcon = _FORUM_IMG_32_LOCK_HIDDEN;
              else
                $strIcon = (($arrForumFields['nbSubjectValid'] + $arrForumFields['nbMessValid']) > 0) ? _FORUM_IMG_32_FOLDER_HIDDEN : _FORUM_IMG_32_FOLDER_EMPTY_HIDDEN;
            }
            elseif($arrForumFields['closed'] == 1)
            {
              $strIcon = _FORUM_IMG_32_LOCK;
            }
            elseif(($arrForumFields['nbSubjectValid'] + $arrForumFields['nbMessValid']) > 0)
            {
              $strIcon = (($arrForumFields['nbSubjectNoValid'] + $arrForumFields['nbMessNoValid']) > 0 && $booForumIsAdminModerCat) ? _FORUM_IMG_32_FOLDER_TOVALID : _FORUM_IMG_32_FOLDER;
            }
            else
            {
              $strIcon = (($arrForumFields['nbSubjectNoValid'] + $arrForumFields['nbMessNoValid']) > 0 && $booForumIsAdminModerCat) ? _FORUM_IMG_32_FOLDER_EMPTY_TOVALID : _FORUM_IMG_32_FOLDER_EMPTY;
            }
            ?>
            <div id="idCat_<?php echo $arrForumFields['id']; ?>" class="<?php echo $strForumClassLine; ?>">
              <div class="ploopi_explorer_element" style="width: 85px; float: right; text-align: center;"><p><?php echo $strForumNbMess; ?></p></div>
              <div class="ploopi_explorer_element" style="width: 85px; float: right; text-align: center;"><p><?php echo $strForumNbSubject; ?></p></div>
              <div class="ploopi_explorer_element" style="width: 130px; float: right; text-align: right;">
                <?php
                if($strForumLastMess != '')
                {
                  ?>
                  <a class="ploopi_explorer_element" style="width: 130px; height: 100%; float: right; text-align: right;" href="<?php echo ploopi_urlencode("admin.php?op=search&id_mess={$arrForumFieldsLastMess['id']}"); ?>">
                    <p><?php echo $strForumLastMess; ?></p>
                  </a>
                 <?php
                }
                else
                {
                  ?><p></p><?php
                }
                ?>
              </div>
              <?php
              // if allowed add edit/delete on categories
              if($booForumActionAllowed)
              {
              ?>
                <div class="ploopi_explorer_element" style="width: 90px; float: right;text-align: center;z-index: 1000">
                  <input type="button" class="button" style="margin:1px;width:80px;" value="<?php echo _FORUM_EDIT; ?>"
                    onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=categ_edit&id_cat={$arrForumFields['id']}"); ?>'" />
                  <input type="button" class="button" style="margin:1px;width:80px;" value="<?php echo _FORUM_DELETE; ?>"
                    onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=categ_delete&id_cat={$arrForumFields['id']}"); ?>','<?php echo _FORUM_CONFIRM_DELETE_CAT; ?>');" />
                </div>
                <div class="ploopi_explorer_element" style="width: 22px; float: right;text-align: center;z-index: 1000">
                  <img class="ForumDragBox" style="z-index: 1000" src="<?php echo _FORUM_IMG_MOVE_V; ?>">
                </div>
              <?php
              }
              ?>
              <a class="ploopi_explorer_link" href="<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$arrForumFields['id']}"); ?>" title="">
                <div class="ploopi_explorer_element" style="width: 50px; float: left; text-align: center; height: 40px;"><p><img align="middle" src="<?php echo $strIcon; ?>" /></p></div>
                <!-- Title -->
                <div class="ploopi_explorer_element">
                  <p>
                    <b><?php echo ploopi_htmlentities($arrForumFields['title']); ?></b>
                    <font style="font-size:0.8em;padding:0;margin:0;">
                    <?php
                    if($arrForumFields['mustbe_validated'] == 1 && $arrForumFields['closed'] == 0)
                    {
                      ?>
                      &nbsp;<font style="font-style:italic;"><?php echo _FORUM_MESS_MUSTBEVALIDATED; ?></font>
                      <?php
                    }
                    ?>
                    <br/><?php echo ploopi_htmlentities($arrForumFields['description']); ?>
                    <br/><font style="text-align: center;font-style:italic;"><?php echo $strForumModerator; ?></font>
                    </font>
                  </p>
                </div>
              </a>
            </div>
            <?php
          }
        }
        ?>
        </div>
      </div>
    </div>
  </div>
  <?php
  echo $skin->close_simplebloc();
  ?>
</div>
<script type="text/javascript">forum_array_renderupdate('categ');</script>
<?php
if($booForumActionAllowed)
{ ?><script type="text/javascript">forumContentGereCat();</script><?php }
?>
