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
      $strForumModerator = _FORUM_MODERATOR.':&nbsp;'.$value['login'];
    else
      $strForumModerator .= ', '.$value['login'];
  }    
}

// Message Param
$arrFiltre = &$_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess'];

$objForumSubject = new forum_mess();
$objForumSubject->open($_GET['id_subject']);

//If it's not the good list of subject reset page
if($arrFiltre['id'] != $objForumSubject->fields['id'])
{
  $arrFiltre['page'] = 1;
  $arrFiltre['id'] = $objForumSubject->fields['id'];
}

// Get nb mess by id_author
$strForumSqlQuery = "SELECT ploopi_mod_forum_mess.id_author, 
  COUNT(ploopi_mod_forum_mess.id_author) AS nbMess
  FROM ploopi_mod_forum_mess
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_author";
$objForumSqlResult = $db->query($strForumSqlQuery);
while ($arrForumFields = $db->fetchrow($objForumSqlResult))
{
  $arrForumUserNbMess[$arrForumFields['id_author']] = $arrForumFields['nbMess'];
}
unset($objForumSqlResult);

// Get nb enr in this subject
$strForumSqlQuery = "SELECT COUNT(ploopi_mod_forum_mess.id) AS nbEnr
  FROM ploopi_mod_forum_mess
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.id_subject =  {$objForumSubject->fields['id']}
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  GROUP BY ploopi_mod_forum_mess.id_subject";
$objForumSqlResult = $db->query($strForumSqlQuery);
$intForumNbEnr = $db->fetchrow($objForumSqlResult);
$intForumNbPages = ($intForumNbEnr['nbEnr'] > 0 && $arrFiltre['limit'] > 0) ? ceil($intForumNbEnr['nbEnr']/$arrFiltre['limit']) : 1;
unset($objForumSqlResult);

// Correct num page
if($arrFiltre['page']>$intForumNbPages || $arrFiltre['page'] == 0) $arrFiltre['page'] = $intForumNbPages;

if($arrFiltre['limit'] > 0) // Get LIMIT with page (if limit = 0 => no limit)
  $strForumLimit = ' LIMIT '.($arrFiltre['limit']*($arrFiltre['page']-1)).','.$arrFiltre['limit'];

// Principale Request
$strForumSqlQuery = "SELECT ploopi_mod_forum_mess.*,
    validator.login AS validatorLogin,
    moderate.login AS moderateLogin
  FROM ploopi_mod_forum_mess
  LEFT JOIN ploopi_user AS validator
    ON validator.id = ploopi_mod_forum_mess.validated_id_user
  LEFT JOIN ploopi_user AS moderate
    ON moderate.id = ploopi_mod_forum_mess.moderate_id_user
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.id_cat = {$objForumCat->fields['id']}
    AND ploopi_mod_forum_mess.id_subject =  {$objForumSubject->fields['id']}
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
  ORDER BY ploopi_mod_forum_mess.{$arrFiltre['orderby']} {$arrFiltre['orderin']}
  {$strForumLimit}";
  
echo $skin->open_simplebloc();
?>
<div class="forum_main">
  <div style="clear:both;padding:0; margin:4px 0 20px 0;">
    <font class="forum_navig">
    <?php echo _FORUM_LABEL_YOU_ARE_HERE; ?>&nbsp;
    </font>
    <button type="button" class="button_navig" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=categ"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo _FORUM_LABEL_CAT; ?>
    </button>
    <button type="button" class="button_navig" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject&id_cat={$objForumCat->fields['id']}"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_FOLDER; ?>"/>&nbsp;<?php echo  ploopi_strcut($objForumCat->fields['title'],30); ?>
    </button>
    <button type="button" class="button_navig_select" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}"); ?>'">
      <img style="border: none; margin: 0 0 -3px 0; padding: 0;" src="<?php echo _FORUM_IMG_16_MESS; ?>"/>&nbsp;<?php echo  ploopi_strcut($objForumSubject->fields['title'],30); ?>
    </button>
  </div>

  <div style="overflow: auto; padding: 0; margin: 0 0 2px 0;">
  <?php
  // Pages button's
  $strForumButtonsPages = forum_pages("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}",$intForumNbPages,$arrFiltre['page']); 
  if($strForumButtonsPages <> '')
  {
    ?>
    <div style="float: right; padding: 0; margin: 1px;"><?php echo $strForumButtonsPages; ?></div>
    <?php 
  } 
  if(($objForumCat->fields['closed'] == 0 && $objForumSubject->fields['closed'] == 0 && $objForumSubject->fields['validated'] == 1) || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="float:left; padding: 0 12px 0 0;">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_add&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_MESS_NEW; ?>" style="padding:0;margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo _FORUM_TOOLBAR_NEW_MESS; ?>
      </button>
    </div>  
    <?php 
    if($objForumCat->fields['closed'] == 0 && $booForumIsAdminModerGlb)
    {
      ?>
    <div style="float:left;">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject_openclose&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_LOCK; ?>" style="padding:0;margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo ($objForumSubject->fields['closed'] == 0) ? _FORUM_MESS_LABEL_CLOSE : _FORUM_MESS_LABEL_REOPEN; ?>
      </button>
    </div>
    <?
    }
  }
  ?>
  </div>
  <?php
  // Title of subject
  echo $skin->open_simplebloc($objForumSubject->fields['title']);
  ?>
  <div id="forum_main_mess" class="ploopi_explorer_main" style="visibility:visible;">
  
    <div id="forum_column_mess_0" class="ploopi_explorer_column" style="left: 150px;"></div>
    
    <div style="position: relative;">
      <div id="forum_values_outer_mess">
        <div id="forum_values_inner_mess">
        <?php
        $objForumSqlResult = $db->query($strForumSqlQuery);
        while ($arrForumFields = $db->fetchrow($objForumSqlResult))
        {
          // date time subject
          $arrForumDateMess = ploopi_timestamp2local($arrForumFields['timestp']);
          // date time validated
          $arrForumDateValid = ploopi_timestamp2local($arrForumFields['validated_timestp']);
          // date time last update
          $arrForumDateUpdate = ploopi_timestamp2local($arrForumFields['lastupdate_timestp']);
          // date time moderated
          $arrForumDateModer = ploopi_timestamp2local($arrForumFields['moderate_timestp']);
          //Nb mess by author
          if(!isset($arrForumUserNbMess[$arrForumFields['id_author']])) $arrForumUserNbMess[$arrForumFields['id_author']] = 0;
          
          if($arrForumFields['validated'] == 1 || $arrForumFields['id_author'] == $_SESSION['ploopi']['user']['id'] || $booForumIsAdminModerGlb)
          {
            // Title
            $strForumTitle = ($arrForumFields['validated'] == 0) ? '<font color="red">'.$arrForumFields['title'].'</font>&nbsp;-&nbsp;<i>'._FORUM_STAY_VALIDATED.'</i>' : $arrForumFields['title'];
                
            ?>
            <!-- Title of message --> 
            <div id="idMess_title_<?php echo $arrForumFields['id'];  ?>" name="idMess_title_<?php echo $arrForumFields['id'];  ?>" class="ploopi_explorer_line_1">
              <div id="idMess_title_left_<?php echo $arrForumFields['id']; ?>" class="ploopi_explorer_element" style="width: 150px; float: left; text-align: center;">
                <p><font style="font-weight:bold;"><?php echo $arrForumDateMess['date'].'&nbsp;'.$arrForumDateMess['time']; ?></font></p>
              </div>
              <div id="idMess_title_right_<?php echo $arrForumFields['id']; ?>" class="ploopi_explorer_element">
              <?php
              if($booForumIsAdminModerGlb)
              {
                if($arrForumFields['validated'] == 0) // isactionallowed...
                {
                  ?>
                  <div style="float:right;padding:0 1px;">
                    <input type="button" class="button" style="margin:1px;width:80px;" value="Valider" 
                      onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_validate&id_cat={$objForumCat->fields['id']}&id_mess={$arrForumFields['id']}"); ?>'" />
                  </div>
                  <?php
                }
                ?>
                <div style="float:right;padding: 0 4px;">Id:&nbsp;<? echo $arrForumFields['id']; ?></div>
                <?php
              }
                ?>
                <div><p style="font-size:1.1em;">&nbsp;<?php echo $strForumTitle; ?></p></div>
              </div>
            </div>
            
            <!-- Content of message -->
            <div id="idMess_main_<?php echo $arrForumFields['id']; ?>" class="ploopi_explorer_line_2">
              <!-- Author of message -->
              <div id="idMess_main_left_<?php echo $arrForumFields['id']; ?>" class="ploopi_explorer_element" style="width: 150px;float: left;text-align: left;">
                <p><?php echo $arrForumFields['author']; ?></p>
                <p><?php echo ($arrForumUserNbMess[$arrForumFields['id_author']]<=1) ? _FORUM_MESSAGE : _FORUM_MESSAGES; ?>&nbsp;:&nbsp;<?php echo $arrForumUserNbMess[$arrForumFields['id_author']]; ?></p>
              </div>
    
              <!-- Message -->
              <div id="idMess_main_right_<?php echo $arrForumFields['id']; ?>" class="forum_mess_content">
                  <p>
                  <?php 
                  echo $arrForumFields['content'];
                   
                  if(isset($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']) && $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']['id'] == $arrForumFields['id'])
                  {
                    echo $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']['mess'];
                    unset($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['info']);
                  }
                  ?>
                  </p>
                <div style="clear:both;padding:1px 0 0 0; margin:0;border-top:solid 1px black;">
                  <div style="float:right;text-align:center; padding: 0; margin:0;">
                  <?php
                  // Add button Quote if this cat/subject is not closed, massage is validated (or you're a moderator ;-) )
                  if(($objForumCat->fields['closed'] == 0 && $arrForumFields['closed'] == 0 && $arrForumFields['validated'] == 1) || $booForumIsAdminModerGlb)
                  { 
                    ?>
                    <input type="button" class="button" style="padding:0;margin:1px;width:80px;" value="<?php echo _FORUM_QUOTE; ?>" 
                      onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_add&id_cat={$objForumCat->fields['id']}&id_subject={$arrForumFields['id_subject']}&id_quote={$arrForumFields['id']}"); ?>'" />
                    <?php
                  }
                  // Add button edit/delete if this cat/subject is not closed, message not be validated or moderated by moderator and it's YOUR message (or you're a moderator ;-) )
                  if(($objForumCat->fields['closed'] == 0
                      && $arrForumFields['closed'] == 0 
                      && $arrForumFields['moderate_id_user'] == 0
                      && $arrForumFields['validated_id_user'] == 0
                      && $arrForumFields['id_author'] == $_SESSION['ploopi']['user']['id'])
                      || $booForumIsAdminModerGlb)
                  {
                    ?>
                    <input type="button" class="button" style="padding:0;margin:1px;width:80px;" value="<?php echo _FORUM_EDIT; ?>" 
                      onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_edit&id_cat={$objForumCat->fields['id']}&id_mess={$arrForumFields['id']}"); ?>'" />
                    <input type="button" class="button" style="padding:0;margin:1px;width:80px;" value="<?php echo _FORUM_DELETE; ?>" 
                      onclick="javascript:ploopi_confirmlink('<?php echo ploopi_urlencode("admin.php?op=mess_delete&id_cat={$objForumCat->fields['id']}&id_mess={$arrForumFields['id']}"); ?>','<?php echo _FORUM_CONFIRM_DELETE_MESS; ?>')" />
                    <?php
                  }
                  ?>
                  </div>
                  <?php
                  if($arrForumFields['validated_id_user'] > 0 || $arrForumFields['lastupdate_timestp'] > 0 || $arrForumFields['moderate_id_user'] > 0)
                  {
                    ?>
                    <div style="float:left;font-size:0.8em;padding:0;margin:0;">
                    <?php
                    // info validator
                    if($arrForumFields['validated_id_user'] > 0)
                      echo '<p style="padding:0;margin:0;">'._FORUM_MESS_VALIDATED.'&nbsp;'.$arrForumDateValid['date'].'&nbsp;'.$arrForumDateValid['time'].'&nbsp;'._FORUM_BY.'&nbsp;'.$arrForumFields['validatorLogin'].'</p>';
                    // info Last modification
                    if($arrForumFields['lastupdate_timestp'] > 0)
                      echo '<p style="padding:0;margin:0;">'._FORUM_MESS_LASTUPDATE.'&nbsp;'.$arrForumDateUpdate['date'].'&nbsp;'.$arrForumDateUpdate['time'].'</p>';
                    // info Moderated
                    if($arrForumFields['moderate_id_user'] > 0)
                      echo '<p style="padding:0;margin:0;">'._FORUM_MESS_MODERATED.'&nbsp;'.$arrForumDateModer['date'].'&nbsp;'.$arrForumDateModer['time'].'&nbsp;'._FORUM_BY.'&nbsp;'.$arrForumFields['moderateLogin'].'</p>';
                    ?>
                    </div>
                  <?php
                  } 
                  ?>
                </div>
              </div>
            </div>
          <?php
          }
          else
          {
            // Title of message when NO validate
            ?>
            <div id="idMess_title_<?php echo $arrForumFields['id']; ?>" class="ploopi_explorer_line_1" >
              <div class="ploopi_explorer_element" style="width: 150px; float: left; text-align: center;">
                <p><font style="font-weight:bold;color:#777777;"><?php echo $arrForumDateMess['date'].'&nbsp;'.$arrForumDateMess['time']; ?></font></p>
              </div>
              <div class="ploopi_explorer_element" style="text-align: left;">
                <div><p><font style="font-size:1.1em;color:#777777;">&nbsp;<?php echo _FORUM_MESS_MESS_OF.'&nbsp;'.$arrForumFields['author'].'&nbsp;-&nbsp;<i>'._FORUM_STAY_VALIDATED.'</i>'; ?></font></p></div>
              </div>
            </div>
            <?php
          }
        }
        ?>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">forum_array_renderupdate('mess');</script>
<?php
echo $skin->close_simplebloc();
?>
  <div style="overflow:auto;padding:0;margin: 0;">
  <?php
  // Pages button's
  $strForumButtonsPages = forum_pages("admin.php?op=mess&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}",$intForumNbPages,$arrFiltre['page']); 
  if($strForumButtonsPages <> '')
  {
    ?>
    <div style="float:right;margin:1px;"><?php echo $strForumButtonsPages; ?></div>
    <?php 
  }
  if(($objForumCat->fields['closed'] == 0 && $objForumSubject->fields['closed'] == 0 && $objForumSubject->fields['validated'] == 1) || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="float:left; padding: 0 12px 0 0;">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=mess_add&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_MESS_NEW; ?>" style="padding:0; margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo _FORUM_TOOLBAR_NEW_MESS; ?>
      </button>
    </div>
    <?php 
    if($objForumCat->fields['closed'] == 0 && $booForumIsAdminModerGlb)
    {
      ?>
    <div style="float:left;">
      <button type="button" class="button" onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=subject_openclose&id_cat={$objForumCat->fields['id']}&id_subject={$objForumSubject->fields['id']}"); ?>'">
        <img src="<?php echo _FORUM_IMG_16_LOCK; ?>" style="padding:0; margin:0 0 -3px 0;border:0px;">&nbsp;<?php echo ($objForumSubject->fields['closed'] == 0) ? _FORUM_MESS_LABEL_CLOSE : _FORUM_MESS_LABEL_REOPEN; ?>
      </button>
    </div>
    <?php
    }
  }
  if(!empty($strForumModerator))
  {
    ?>
    <div style="float:left; padding:0;margin:0 0 0 15px; font-size:0.9em;"><?php echo $strForumModerator; ?></div>
    <?php
  }
  if(($objForumCat->fields['closed'] == 0 && $objForumSubject->fields['closed'] == 0 && $objForumSubject->fields['validated'] == 1) || $booForumIsAdminModerGlb)
  {
    ?>
    <div style="clear:both; padding: 5px 0 0 0;">
      <?php ploopi_subscription(_FORUM_OBJECT_MESSAGE, $objForumSubject->fields['id'], array(_FORUM_ACTION_ADD_MESSAGE)); ?>      
    </div>
    <?php 
  }
  ?>
  </div>
</div>

<?php
echo $skin->close_simplebloc();
?>
