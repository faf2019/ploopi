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
 * Fonctions PHP
 *
 * @package forum
 * @subpackage functions
 * @copyright HeXad, Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Get the page of a subject
 *
 * @param Int $intIdSubject id of subject research
 * @param Int $intIdCat Id categorie of subject research
 * @param Array $arrInfo Info for "Order By" and "Limit"
 * @return Array $arrReturn with 'page' and 'id'
 */
function forum_GetSubjectPage($intIdSubject,$arrInfo)
{
  global $strForumSqlLimitGroupMess;
  global $db;

  $arrReturn = array('page' => 0,
                      'id_subject' => $intIdSubject,
                      'id_cat' => 0);

  // Control
  if(intval($intIdSubject) <= 0 || !is_array($arrInfo) || $arrInfo['limit'] <= 0
    || ($arrInfo['orderin'] != 'ASC' && $arrInfo['orderin'] != 'DESC'))
     return $arrReturn;

  // Rech detail of subject
  $objForumSubject = new forum_mess();
  if(!$objForumSubject->open($intIdSubject)) return $arrReturn;

  //Modify the arrReturn
  $arrReturn['id_cat'] = $objForumSubject->fields['id_cat'];
  if($arrInfo['limit'] == 0)
  {
    $arrReturn['page'] = 1;
    return $arrReturn;
  }

  $arrReturn['id'] = $objForumSubject->fields['id_cat'];

  switch($arrInfo['orderin'])
  {
    case 'ASC':
      $strForumCompare = '<';
      break;
    case 'DESC':
      $strForumCompare = '>';
      break;
    default:
      return $arrReturn;
      break;
  }
  switch($arrInfo['orderby'])
  {
    // Order by last mess
    case 'timestp':
      $strForumFilter = "AND(ploopi_mod_forum_mess.timestp {$strForumCompare} {$objForumSubject->fields['timestp']}
                             OR(ploopi_mod_forum_mess.timestp = {$objForumSubject->fields['timestp']}
                                AND ploopi_mod_forum_mess.id {$strForumCompare}= {$objForumSubject->fields['id']}))";
      $strForumOrderBy = "ORDER BY ploopi_mod_forum_mess.timestp {$arrInfo['orderin']}";
      break;
    case 'title':
      $strForumFilter = "AND(ploopi_mod_forum_mess.title {$strForumCompare} '{$objForumSubject->fields['title']}'
                             OR(ploopi_mod_forum_mess.title = '{$objForumSubject->fields['title']}'
                                AND ploopi_mod_forum_mess.id {$strForumCompare}= {$objForumSubject->fields['id']}))";
      $strForumOrderBy = "ORDER BY ploopi_mod_forum_mess.title {$arrInfo['orderin']}";
      break;
    default :
      return $arrReturn;
      break;
  }

  // Principal request
  $strForumSqlQuery = "SELECT COUNT(ploopi_mod_forum_mess.id) AS ndPage
    FROM ploopi_mod_forum_mess
    WHERE ploopi_mod_forum_mess.id_cat = {$objForumSubject->fields['id_cat']}
      AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
      AND ploopi_mod_forum_mess.id = ploopi_mod_forum_mess.id_subject
      {$strForumFilter}
    {$strForumOrderBy}";
  $objForumSqlResult = $db->query($strForumSqlQuery);

  if(!$db->numrows($objForumSqlResult)) return $arrReturn;

  $arrForumFields = $db->fetchrow($objForumSqlResult);

  $arrReturn['page'] = ceil($arrForumFields['ndPage']/$arrInfo['limit']);

  return $arrReturn;
}

/**
 * Get the page of a message
 *
 * @param Int $intIdMess Id message of message research
 * @param Array $arrInfo Info for "Order By" and "Limit"
 * @return Array $arrReturn with 'page' and 'id'
 */
function forum_GetMessPage($intIdMess,$arrInfoGlb)
{
  global $strForumSqlLimitGroupMess;
  global $db;

  $arrInfo = &$arrInfoGlb['mess'];

  $arrReturn = array('page' => 0,
                      'page_subject' => 0,
                      'id_mess' => $intIdMess,
                      'id_subject' => 0,
                      'id_cat' => 0);

  // Control
  if(intval($intIdMess) <= 0 || !is_array($arrInfo) || ($arrInfo['orderin'] != 'ASC' && $arrInfo['orderin'] != 'DESC'))
    return $arrReturn;

  // Rech detail of mess
  $objForumMess = new forum_mess();
  if(!$objForumMess->open($intIdMess)) return $arrReturn;

  //Modify the arrReturn
  $arrReturn['id_subject'] = $objForumMess->fields['id_subject'];
  $arrReturn['id_cat'] = $objForumMess->fields['id_cat'];

  if($arrInfo['limit'] == 0)
  {
    $arrReturn['page'] = 1;
    return $arrReturn;
  }

  switch ($arrInfo['orderin'])
  {
    case 'ASC':
      $strForumCompare = '<';
      break;
    case 'DESC':
      $strForumCompare = '>';
      break;
    default :
      return $arrReturn;
      break;
  }

  // Rech nb of message before
  $strForumSqlQuery = "SELECT COUNT(ploopi_mod_forum_mess.id) AS intPage
  FROM ploopi_mod_forum_mess
  WHERE $strForumSqlLimitGroupMess
    AND ploopi_mod_forum_mess.id_cat = {$objForumMess->fields['id_cat']}
    AND ploopi_mod_forum_mess.id_subject =  {$objForumMess->fields['id_subject']}
    AND ploopi_mod_forum_mess.id_module = {$_SESSION['ploopi']['moduleid']}
    AND (ploopi_mod_forum_mess.timestp {$strForumCompare} {$objForumMess->fields['timestp']}
         OR (ploopi_mod_forum_mess.timestp = {$objForumMess->fields['timestp']}
             AND ploopi_mod_forum_mess.id {$strForumCompare}= {$objForumMess->fields['id']}))
  ORDER BY ploopi_mod_forum_mess.timestp {$arrInfo['orderin']}";
  $objForumSqlResult = $db->query($strForumSqlQuery);

  if(!$db->numrows($objForumSqlResult)) return $arrReturn;

  $arrForumFields = $db->fetchrow($objForumSqlResult);

  $arrReturn['page'] = ceil($arrForumFields['intPage']/$arrInfo['limit']);

  // ok... now find the page of subject...
  $arrSearchSubject = forum_GetSubjectPage($objForumMess->fields['id_subject'],$arrInfoGlb['subject']);
  if($arrSearchSubject['page'] > 0)
    $arrReturn['page_subject'] = $arrSearchSubject['page'];
  else
    $arrReturn['page'] = 0;

  return $arrReturn;
}

/**
 * Create buttons for page select
 *
 * @param String $strForumUrl = Url to redirect
 * @param Int $intForumNbPages Number max of page
 * @param Int $intForumNumPage Actual num page
 * @return string for echo
 */
function forum_pages($strForumUrl,$intForumNbPages,$intForumNumPage)
{
  if($intForumNbPages <= 1) return '';

  if($intForumNumPage > $intForumNbPages) $intForumNumPage = $intForumNbPages;

  $strForumReturn = '<div style="margin:0;padding:0;">';
  $strForumReturn .= ' <div class="forum_info_pages">'._FORUM_PAGE.'&nbsp;'.$intForumNumPage.'&nbsp;'._FORUM_PAGE_ON.'&nbsp;'.$intForumNbPages.'</div>';
  // First - Previous
  if($intForumNbPages > 5)
  {
    if($intForumNumPage > 1) // enable
    {
      // First
      $strForumReturn .= '<input type="button" class="button" style="float:left;margin:1px;" onclick="javascript:document.location.href=\''.ploopi_urlencode("{$strForumUrl}&page=1").'\'" value="'._FORUM_PAGE_FIRST.'"/>';
      // Before
      $strForumReturn .= '<input type="button" class="button" style="float:left;margin:1px;" onclick="javascript:document.location.href=\''.ploopi_urlencode($strForumUrl."&page=".($intForumNumPage-1)).'\'" value="'._FORUM_PAGE_BEFORE.'"/>';
    }
    else // disable
    {
      // First
      $strForumReturn .= '<input type="button" class="forum_button_disabled" style="float:left;" value="'._FORUM_PAGE_FIRST.'" />';
      // Before
      $strForumReturn .= '<input type="button" class="forum_button_disabled" style="float:left;" value="'._FORUM_PAGE_BEFORE.'" />';
    }
  }

  // Define begin and end button with the actual page
  $intForumBegin = ($intForumNumPage-2);
  $intForumEnd = ($intForumNumPage+2);

  if($intForumBegin < 1)
  {
    $intForumEnd += abs($intForumBegin)+1;
    $intForumBegin += abs($intForumBegin)+1;
    if($intForumEnd > $intForumNbPages) $intForumEnd = $intForumNbPages;
  }
  if($intForumEnd > $intForumNbPages)
  {
    $intForumBegin -= ($intForumEnd - $intForumNbPages);
    $intForumEnd -= ($intForumEnd - $intForumNbPages);
    if($intForumBegin < 1) $intForumBegin = 1;
  }
  // Button
  for($i=$intForumBegin; $i<=$intForumEnd; $i++)
  {
    $strForumStyle = '';
    if($i == $intForumNumPage) $strForumStyle = 'font-weight:bold;';
    $strForumReturn .= '<input type="button" class="button" style="'.$strForumStyle.'margin:1px;float:left;" onclick="javascript:document.location.href=\''.ploopi_urlencode("{$strForumUrl}&page={$i}").'\'" value="'.$i.'"/>';
  }
  // Next - Last
  if($intForumNbPages > 5)
  {
    if($intForumNumPage < $intForumNbPages) // enable
    {
      // Next
      $strForumReturn .= '<input type="button" class="button" style="margin:1px;float:left;" onclick="javascript:document.location.href=\''.ploopi_urlencode($strForumUrl."&page=".($intForumNumPage+1)).'\'" value="'._FORUM_PAGE_NEXT.'"/>';
      // Last
      $strForumReturn .= '<input type="button" class="button" style="margin:1px;float:left;" onclick="javascript:document.location.href=\''.ploopi_urlencode("{$strForumUrl}&page={$intForumNbPages}").'\'" value="'._FORUM_PAGE_LAST.'"/>';

    }
    else // disable
    {
      // Next
      $strForumReturn .= '<input type="button" class="forum_button_disabled" style="float:left;" value="'._FORUM_PAGE_NEXT.'" />';
      // Last
      $strForumReturn .= '<input type="button" class="forum_button_disabled" style="float:left;" value="'._FORUM_PAGE_LAST.'" />';
    }
  }
  $strForumReturn .= '</div>';
  return $strForumReturn;
}

/**
 * Get the detailed (or not) list of moderators
 *
 * @param Int $intIdCat Id categorie
 * @param Int $intAction action
 * @param Boolean $booWidthDetail get user detail
 * @param Int $inIdModule id_module
 * @return Array [id] = array(login, firstname, lastname) or Array [id] = 0 (without detail)
 */
function forum_ListModer($intIdCat = -1, $intObject = -1, $booWidthDetail = true, $inIdModule = -1)
{
  include_once './include/classes/group.php';
  global $db;

  $arrForumModeratData = array();

  if($inIdModule == -1) $inIdModule = $_SESSION['ploopi']['moduleid'];
  if($intObject == -1 && defined('_FORUM_OBJECT_CAT')) $intObject = _FORUM_OBJECT_CAT;

  if($intObject > -1)
  {
    $arrForumModeratSearch = ploopi_validation_get($intObject, $intIdCat);
    
    $arrForumModerat = array();
    foreach($arrForumModeratSearch as $value)
    {
      if ($value['type_validation'] == 'group') // recherche des utilisateurs du groupe
      {
        $value['type_validation'] = 'user'; //petite astuce pour récupérer l'enregistrement comme si c'était un utilisateur
        $objGroup = new group();
        $objGroup->open($value['id_validation']);
        $arrUsers = $objGroup->getusers();
        foreach($arrUsers as $arrUser)
        {
          $value['id_validation'] = $arrUser['id'];
          $arrForumModerat[$value['id_validation']] = $value;
        }
      }
      else $arrForumModerat[$value['id_validation']] = $value;
    }      
  }

  if($booWidthDetail)
  {
    if(is_array($arrForumModerat) && count($arrForumModerat) > 0)
    {
      foreach($arrForumModerat as $value) $arrForumIdModerat[] = $value['id_validation'];

      $strForumIdModerat = implode(',',$arrForumIdModerat);

      $strForumSqlQueryModerat = "SELECT ploopi_user.* FROM ploopi_user WHERE ploopi_user.id IN ({$strForumIdModerat})";
      $objForumSqlResultModerat = $db->query($strForumSqlQueryModerat);
      if($db->numrows($objForumSqlResultModerat))
      {
        while($value = $db->fetchrow($objForumSqlResultModerat))
          $arrForumModeratData[$value['id']] = array('login' => $value['login'],
                                                  'firstname' => $value['firstname'],
                                                  'lastname' => $value['lastname']);
      }
    }
  }
  else
  {
    if(is_array($arrForumModerat) && count($arrForumModerat) > 0)
    {
      foreach($arrForumModerat as $value) $arrForumModeratData[$value['id_validation']] = 0;
    }
  }
  return $arrForumModeratData;
}

/**
 * Find if the current user is admin or moderator
 *  if function known inIdCar return only for this categories
 *  if function known intAction she make a ploopi_actionallowed
 *
 * @param int $intIdCat id categorie (optionnal)
 * @param int $intAction constant action (optionnal)
 * @param Boolean $booWidthDetail get user detail
 * @param Int $inIdModule id_module
 * @return boolean
 */
function forum_IsAdminOrModer($intIdCat = -1, $intObject = -1, $booWidthDetail = true, $intIdModule = -1)
{
  if($intIdModule == -1) $intIdModule = $_SESSION['ploopi']['moduleid'];

  if(ploopi_isactionallowed($intObject)) return true;

  $arrListModer = forum_ListModer($intIdCat,$intObject,$booWidthDetail,$intIdModule);

  if(is_array($arrListModer) && count($arrListModer) > 0 && array_key_exists($_SESSION['ploopi']['user']['id'],$arrListModer))
    return true;

  return false;
}

/**
 * Control and correct $_SESSION['ploopi']['forum'][$intIdModule]['arrays']...
 *
 */
function forum_CtrlParam()
{
// Control PARAM for subject
if($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['orderby'] != 'timestp'
      && $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderby'] != 'title')
  $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['orderby'] = 'timestp';

if($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['orderin'] != 'ASC'
      && $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['orderin'] != 'DESC')
  $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['subject']['orderin'] = 'ASC';

// Control PARAM for message
if($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderby'] != 'timestp')
  $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderby'] = 'timestp';

if($_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderin'] != 'ASC'
      && $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderin'] != 'DESC')
  $_SESSION['ploopi']['forum'][$_SESSION['ploopi']['moduleid']]['arrays']['mess']['orderin'] = 'ASC';
}
?>