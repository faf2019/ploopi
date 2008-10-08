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
 * Recup�re la liste des email ayant souscrit � ce module
 *
 * @return array Tableau des inscrits actif (sinon false)
 */
function newsletter_getArrSubscriber()
{
  global $db;
  
  $sql = "SELECT email 
          FROM ploopi_mod_newsletter_subscriber 
          WHERE active = 1 
            AND id_module = {$_SESSION['ploopi']['moduleid']}
         ";
  $result_sql = $db->query($sql);
  $arrSubscriber = $db->getarray($result_sql);

  if(!empty($arrSubscriber)) return $arrSubscriber;
  
  return false;
}
/**
 * R�cup�re la liste des newsletter dont le user courant est validateur
 *
 * @return array index� contenant la liste des id de newsletter dont cet user est validateur OU 'all' si il est validateur global
 */
function newsletter_ListNewsletterIsValidator()
{
  $arrListReturn = array();
  $arrList = ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER, 'newsletter',  $_SESSION['ploopi']['moduleid'], $_SESSION['ploopi']['user']['id']);
  if(count($arrList)>0)
     return 'all';
  else
  {
    $arrList = ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER, '',  $_SESSION['ploopi']['moduleid'], $_SESSION['ploopi']['user']['id']);
    foreach($arrList as $data) $arrListReturn[] = $data['id_record'];
  }
  return $arrListReturn;
}

/**
 * R�cup�re la liste des validateurs (avec detail si besoin)
 *
 * @param Int $intIdCat Id categorie
 * @param Int $intAction action
 * @param Boolean $booWidthDetail true = R�cup�re les detail
 * @param Int $inIdModule id_module
 * @return Array [id] = array(login, firstname, lastname) or Array [id] = 0 (without detail)
 */
function newsletter_ListValid($intIdNewsletter = -1, $intObject = -1, $booWidthDetail = true, $inIdModule = -1)
{
  global $db;
  
  $arrNewsletterValidatorData = array();

  if($inIdModule == -1) $inIdModule = $_SESSION['ploopi']['moduleid'];
  if($intObject == -1 && defined('_NEWSLETTER_OBJECT_NEWSLETTER')) $intObject = _NEWSLETTER_OBJECT_NEWSLETTER;
  
  $arrNewsletterValidator = array();
  if($intObject > -1)
  {
    if($intIdNewsletter > 0) $arrNewsletterValidator += ploopi_validation_get($intObject,$intIdNewsletter);
    $arrNewsletterValidator += ploopi_validation_get($intObject);
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
 * V�rifie si l'utilisateur connect� est un administrateur ou un validateur
 *  
 * @param int $intIdCat id categorie (optionnal)
 * @param int $intAction constant action (optionnal)
 * @param Int $inIdModule id_module
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
 * @return array tableau index� contenant la liste tri�e des templates
 */
function newsletter_gettemplates()
{
    $newsletter_templates = array();
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

    return($newsletter_templates);
}

?>