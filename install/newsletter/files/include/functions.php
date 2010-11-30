<?php
/*
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
 * Fonctions, constantes, variables globales
 *
 * @package newsletter
 * @subpackage functions
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Recupère la liste des email ayant souscrit à ce module
 *
 * @return array Tableau des inscrits actif (sinon false)
 */
function newsletter_getArrSubscriber()
{
  global $db;

  $sql = "SELECT email
          FROM ploopi_mod_newsletter_subscriber
          WHERE active = 1
            AND id_module = '{$_SESSION['ploopi']['moduleid']}'
         ";
  $result_sql = $db->query($sql);
  $arrSubscriber = $db->getarray($result_sql, true);

  if(!empty($arrSubscriber)) return $arrSubscriber;

  return false;
}

/**
 * Récupère la liste des newsletter dont le user courant est validateur
 *
 * @return array indexé contenant la liste des id de newsletter dont cet user est validateur OU 'all' si il est validateur global
 */
function newsletter_ListNewsletterIsValidator()
{
  // Recherche des groupes de l'utilisateur
  include_once './include/classes/user.php';
  $objUser = new user();
  $objUser->open($_SESSION['ploopi']['userid']);
  $arrGroups = $objUser->getgroups(true);
  
  $arrList = array();
  
  foreach(ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER, 'newsletter') as $value) 
  {
    if (($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) || ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']]))) $arrList[$value['id_record']] = $value['id_record'];
  }        
  
  if(count($arrList)>0) return 'all';
  else
  {
    $arrListReturn = array();
    
    foreach(ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER) as $value) 
    {
      if (($value['type_validation'] == 'user' && $value['id_validation'] == $_SESSION['ploopi']['userid']) || ($value['type_validation'] == 'group' && isset($arrGroups[$value['id_validation']]))) $arrListReturn[$value['id_record']] = $value['id_record'];
    }
            
    return $arrListReturn;
  }
}

/**
 * Récupère la liste des validateurs (avec detail si besoin)
 *
 * @param Int $intIdCat Id categorie
 * @param Int $intAction action
 * @param Boolean $booWidthDetail true = Récupère les detail
 * @param Int $intIdModule id_module
 * @return Array [id] = array(login, firstname, lastname) or Array [id] = 0 (without detail)
 */
function newsletter_ListValid($intIdNewsletter = -1, $intObject = -1, $booWidthDetail = true, $intIdModule = -1)
{
  include_once './include/classes/group.php';
  global $db;

  $arrNewsletterValidatorData = array();

  if($intIdModule == -1) $intIdModule = $_SESSION['ploopi']['moduleid'];
  if($intObject == -1 && defined('_NEWSLETTER_OBJECT_NEWSLETTER')) $intObject = _NEWSLETTER_OBJECT_NEWSLETTER;

  $arrNewsletterValidator = array();
  $arrNewsletterValidatorSearch = array();
  if($intObject > -1)
  {
    if($intIdNewsletter > 0) $arrNewsletterValidatorSearch += ploopi_validation_get($intObject,$intIdNewsletter);
    $arrNewsletterValidatorSearch += ploopi_validation_get($intObject);
    
    $arrNewsletterValidator = array();
    foreach($arrNewsletterValidatorSearch as $value)
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
          $arrNewsletterValidator[$value['id_validation']] = $value;
        }
      }
      else $arrNewsletterValidator[$value['id_validation']] = $value;
    }
  }

  if($booWidthDetail)
  {
    if(is_array($arrNewsletterValidator) && count($arrNewsletterValidator) > 0)
    {
      foreach($arrNewsletterValidator as $value) $arrNewsletterIdValidator[] = $value['id_validation'];

      $strNewsletterIdValidator = implode(',',$arrNewsletterIdValidator);

      $strNewsletterSqlQueryValidator = "SELECT ploopi_user.id,
                                              ploopi_user.login,
                                              ploopi_user.firstname,
                                              ploopi_user.lastname
                                         FROM ploopi_user WHERE ploopi_user.id IN ({$strNewsletterIdValidator})";
      $objNewsletterSqlResultValidator = $db->query($strNewsletterSqlQueryValidator);
      if($db->numrows($objNewsletterSqlResultValidator))
      {
        while($value = $db->fetchrow($objNewsletterSqlResultValidator))
          $arrNewsletterValidatorData[$value['id']] = array('login' => $value['login'],
                                                       'firstname' => $value['firstname'],
                                                       'lastname' => $value['lastname']);
      }
    }
  }
  else
  {
    if(is_array($arrNewsletterValidator) && count($arrNewsletterValidator) > 0)
    {
      foreach($arrNewsletterValidator as $value) $arrNewsletterValidatorData[$value['id_validation']] = 0;
    }
  }
  return $arrNewsletterValidatorData;
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur ou un validateur
 *
 * @param int $intIdCat id categorie (optionnal)
 * @param int $intAction constant action (optionnal)
 * @param Int $intIdModule id_module
 * @return boolean
 */
function newsletter_IsValidator($intIdLetter = -1, $intObject = -1, $intIdModule = -1)
{
  if($intIdModule == -1) $intIdModule = $_SESSION['ploopi']['moduleid'];

  $arrListValid = newsletter_ListValid($intIdLetter,$intObject,false,$intIdModule);

  if(is_array($arrListValid) && array_key_exists($_SESSION['ploopi']['user']['id'],$arrListValid))
    return true;

  return false;
}

/**
 * Retourne les templates de la newsletter dans un tableau
 *
 * @return array tableau indexé contenant la liste triée des templates
 */
function newsletter_gettemplates()
{
    $newsletter_templates = array();
    $newsletter_templates_default = array();

    $dirTemplate_Default = './modules/newsletter/template_default';

    if(is_dir($dirTemplate_Default))
    {
      $pdir = @opendir($dirTemplate_Default);

      while ($tpl = @readdir($pdir))
      {
          if ((substr($tpl, 0, 1) != '.') && is_dir($dirTemplate_Default."/{$tpl}"))
          {
              $newsletter_templates_default[] = $tpl;
          }
      }

      sort($newsletter_templates_default);
    }

    if(is_dir(_NEWSLETTER_TEMPLATES_PATH))
    {
      $pdir = @opendir(_NEWSLETTER_TEMPLATES_PATH);

      while ($tpl = @readdir($pdir))
      {
          if ((substr($tpl, 0, 1) != '.') && is_dir(_NEWSLETTER_TEMPLATES_PATH."/{$tpl}"))
          {
              $newsletter_templates[] = $tpl;
          }
      }

      sort($newsletter_templates);
    }

    $newsletter_templates = $newsletter_templates_default + $newsletter_templates;

    return($newsletter_templates);
}

?>