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
 * Installation du portail
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Create config.php + mount sql database for ploopi
 *
 * @param unknown_type $arrInstallInfos
 * @return unknown
 */

function ploopi_create_site(&$arrInstallInfos)
{
  try {
    $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']}","{$_SESSION['install']['<DB_LOGIN>']}","{$_SESSION['install']['<DB_PASSWORD>']}");
  }
  catch (PDOException $objInstallDBError)
  {
    $arrErrors = $objInstallDBError->errorInfo();
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }

  $intRequest = $objPDO->exec("USE `{$_SESSION['install']['<DB_DATABASE>']}`");
  if($intRequest === false)
  {
    // database no existe
    $intRequest = $objPDO->exec("CREATE DATABASE `{$_SESSION['install']['<DB_DATABASE>']}`");
    if($intRequest === false)
    {
      $arrErrors = $objPDO->errorInfo();
      $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
      return false;
    }
  }

  $objPDO = Null;
  try {
    $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']};dbname={$_SESSION['install']['<DB_DATABASE>']}","{$_SESSION['install']['<DB_LOGIN>']}", "{$_SESSION['install']['<DB_PASSWORD>']}");
  }
  catch (PDOException $objInstallDBError)
  {
    $arrErrors = $objInstallDBError->errorInfo();
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }

  $strModelFile = './config/config.php.model';
  $strConfigFile = './config/config.php';
  $strSqlFile = './install/system/ploopi.sql';
  $strContent = '';
  foreach($_SESSION['install'] as $strTag => $strValue)
  {
    if(substr($strTag,0,1)=='<' && substr($strTag,-1,1)=='>')
    {
      if($strTag=='<USE_DBSESSION>' || $strTag=='<URL_ENCODE>' || $strTag=='<FRONTOFFICE>' || $strTag=='<REWRITERULE>' || $strTag=='<CGI>')
      {
        $strValue = ($strValue) ? 'true' : 'false';
      }
      $arrTags[] =  $strTag;
      $arrReplace[] = $strValue;
    }
  }

  $f = fopen( $strModelFile, "r" );
  while (!feof($f)) $strContent .= fgets($f, 4096);
  fclose($f);

  $strContent = str_replace($arrTags, $arrReplace, $strContent);

  $fc = fopen( $strConfigFile, "w" );
  fwrite($fc, $strContent);
  fclose($fc);

  chmod($strConfigFile, 0640);
    
  $arrRequests = array();
  $strSql = '';

  $fs = fopen ($strSqlFile, "r");
  while (!feof($fs))
  {
    $strSql .= fgets($fs, 4096);
  }
  fclose ($fs);

  $strSql = trim($strSql);

  //  $arrRequests = explode(";\n",$strSql);
  $arrRequests = preg_split('/;[\s]{0,}\n/',$strSql);

  foreach ($arrRequests AS $strKey => $strRequest)
  {
    $strRequest = trim($strRequest);
    $strRequest = preg_replace('#(.*)(\-)(.*)(([01][0-9])|(2[0-3]))\:([0-5][0-9])\:([0-5][0-9])(.*)(([\r\n])|($))#','$1$2$3$4.$7.$8$9$10',$strRequest);
    if ($strRequest!='')
    {
      $intRequest = $objPDO->exec("$strRequest");
      if($intRequest === false)
      {
        $arrErrors = $objPDO->errorInfo();
        $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2].'<br/>'.$strRequest);
        return false;
      }
      $intRequest = null;
    }
  }
  $strAdminPassword = empty($_SESSION['install']['<ADMIN_PASSWORD>']) ? 'admin' : $_SESSION['install']['<ADMIN_PASSWORD>'];
  $intRequest = $objPDO->exec("UPDATE `ploopi_user` SET `login` = '{$_SESSION['install']['<ADMIN_LOGIN>']}', `password` = '".md5("{$_SESSION['install']['<SECRETKEY>']}/{$_SESSION['install']['<ADMIN_LOGIN>']}/".md5($strAdminPassword))."', email = '{$_SESSION['install']['<ADMIN_MAIL>']}' WHERE  `login` = 'admin'");
  if($intRequest === false)
  {
    $arrInstallInfos[] = array('id' => 'div_error_end', 'state' => false, 'title' => _PLOOPI_INSTALL_ERR_INSTALL_WARNING.'<br/>'.$arrErrors[0].'<br/>'.$arrErrors[1].'<br/>'.$arrErrors[2]);
    return false;
  }
  else
  {
    return true;
  }
}
?>