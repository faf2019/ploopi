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
 * Fonctions MySQL utilisées durant la procédure d'installation de Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Test all base functions in Mysql
 *
 * @param array $arrInstallInfos
 * @param integer $intInstallInfos
 * @param array $arrInstallRequestDB
 * @return true/false
 */
function ploopi_Test_Database($arrInstallInfos,$intInstallInfos,$arrInstallRequestDB)
{
  //Connect to server (Not connected to database !!!)
  try {
    $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']};","{$_SESSION['install']['<DB_LOGIN>']}", "{$_SESSION['install']['<DB_PASSWORD>']}");
  }
  catch (PDOException $objPDOError) {
    $arrInstallInfos[$intInstallInfos]['state'] = false;
    $arrInstallInfos[$intInstallInfos]['mess_replace'] = array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],
                                                               _PLOOPI_INSTALL_INSTALLED.'?');
    $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_CONNECT);
    return false;
  }

  $arrInstallInfos[$intInstallInfos]['mess_replace'] = array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],
                                                             _PLOOPI_INSTALL_INSTALLED.$objPDO->getAttribute(PDO::ATTR_SERVER_VERSION));
  $arrInstallInfos[$intInstallInfos]['form'][] = array('label' => _PLOOPI_INSTALL_DB_DATABASE_NAME,
                                                       'input' => '<input name="db_database_name" id="db_database_name" type="text" value="'.$_SESSION['install']['<DB_DATABASE>'].'"/>');

  if(!version_compare($objPDO->getAttribute(PDO::ATTR_SERVER_VERSION),$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],'>='))
  {
    $arrInstallInfos[$intInstallInfos]['state'] = false;
    return false;
  }
  else // Database Version is OK
  {
    // List of databases (if we can)
    $objRequest = $objPDO->query('SHOW DATABASES');
    if($objRequest !== false)
    {
      $strInstallListDbName = '';
      foreach ( $objRequest as $arrInstalledDB)
      {
        if($_SESSION['install']['<DB_TYPE>'] !== 'mysql' ||
              ($_SESSION['install']['<DB_TYPE>'] === 'mysql' && ($arrInstalledDB['Database'] !== 'information_schema' && $arrInstalledDB['Database'] !== 'mysql'))) // Database Reserved by mysql
        {
          if($arrInstalledDB['Database'] === $_SESSION['install']['<DB_DATABASE>'])
          {
            $strInstallSelected = 'selected';
            $booFindDbListe = true;
          }
          else
          {
            $strInstallSelected = '';
          }
          $strInstallListDbName .= '<option value="'.$arrInstalledDB['Database'].'" '.$strInstallSelected.'>'.$arrInstalledDB['Database'].'</option>';
        }
      }
      //template : List of database
      if($strInstallListDbName != '')
      {
        $arrInstallInfos[$intInstallInfos]['form'][] = array('label' => _PLOOPI_INSTALL_DB_DATABASE_SELECT,
                                                             'input' => '<select name="db_database_select" id="db_database_select" onChange="javascript:duplicSelectToField(this,\'db_database_name\');">'.
                                                                        '<option value="">'._PLOOPI_INSTALL_DB_DATABASE_SELECT_NEW.'</option>'.
                                                                        $strInstallListDbName.
                                                                        '</select>');
      }
    }

    //If database name is ok
    if(trim($_SESSION['install']['<DB_DATABASE>']) != '')
    {
      if(!$booFindDbListe) // The database NO exist
      {
        // CREATE DATABASE
        $intRequest = $objPDO->exec("CREATE DATABASE `{$_SESSION['install']['<DB_DATABASE>']}`");
        $arrInstallInfos[] = array('id' => 'div_db_create_db',
                                   'state' => ($intRequest !== false) ? true : false,
                                   'title' => '_PLOOPI_INSTALL_DATA_BASE_CREATE_DB',
                                   'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                   'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
        if($intRequest === false) return false;
      }
      $intRequest = null;

      // Disconnect
      $objPDO = null;
      //Connect to server AND database
      try {
        $objPDO = new PDO("mysql:host={$_SESSION['install']['<DB_SERVER>']};dbname={$_SESSION['install']['<DB_DATABASE>']}","{$_SESSION['install']['<DB_LOGIN>']}", "{$_SESSION['install']['<DB_PASSWORD>']}");
      }
      catch (PDOException $objPDOError) {
        $arrInstallInfos[$intInstallInfos]['state'] = false;
        $arrInstallInfos[$intInstallInfos]['mess_replace'] = array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['version'],
                                                                   _PLOOPI_INSTALL_INSTALLED.'?');
        $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_CONNECT);
        return false;
      }

      // Test if it's not a ploopi database
      $objRequest = $objPDO->query('SELECT * FROM `ploopi_user`');
      if($objRequest !== false)
      {
        if($_SESSION['install']['replace_database'])
        {
          $strInstallCheckedYes = 'checked';$strInstallCheckedNo = '';
        }
        else
        {
          $strInstallCheckedYes = '';$strInstallCheckedNo = 'checked';
        }

        $arrInstallInfos[] = array('id' => 'div_db_plopi_exist',
                                   'state' => $_SESSION['install']['replace_database'],
                                   'title' => '_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST',
                                   'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                   'form'  => array(array('label' => _PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST_FIELD,
                                                      'input' => _PLOOPI_INSTALL_YES.': <INPUT type="radio" name="del_exist" value="1" '.$strInstallCheckedYes.'> '.
                                                               _PLOOPI_INSTALL_NO.': <INPUT type="radio" name="del_exist" value="0" '.$strInstallCheckedNo.'>'))
                                  );
      }
      $objRequest = null;

      $intRequest = $objPDO->exec('DROP TABLE IF EXISTS `ploopi_install_test`');
      $intRequest = null;
      // CREATE TABLE
      $intRequest = $objPDO->exec('CREATE TABLE `ploopi_install_test` (`id` int NULL)');
      $arrInstallInfos[] = array('id' => 'div_db_create',
                                 'state' => ($intRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_CREATE',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
      $intRequest = null;
      // INSERT
      $intRequest = $objPDO->exec('INSERT INTO `ploopi_install_test` (`id`) VALUES (\'1\')');
      $arrInstallInfos[] = array('id' => 'div_db_insert',
                                 'state' => ($intRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_INSERT',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
      $intRequest = null;
      // SELECT
      $objRequest = $objPDO->query('SELECT * FROM `ploopi_install_test`');
      $arrInstallInfos[] = array('id' => 'div_db_select',
                                 'state' => ($objRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_SELECT',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
      $objRequest = null;
      // UPDATE
      $intRequest = $objPDO->exec('UPDATE `ploopi_install_test` SET `id` = \'2\' WHERE `id` = 1');
      $arrInstallInfos[] = array('id' => 'div_db_update',
                                 'state' => ($intRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_UPDATE',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
      $intRequest = null;
      // DELETE
      $intRequest = $objPDO->exec('DELETE FROM `ploopi_install_test`');
      $arrInstallInfos[] = array('id' => 'div_db_delete',
                                 'state' => ($intRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_DELETE',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
      $intRequest = null;
      // DROP TABLE
      $intRequest = $objPDO->exec('DROP TABLE `ploopi_install_test`');
      $arrInstallInfos[] = array('id' => 'div_db_drop',
                                 'state' => ($intRequest !== false) ? true : false,
                                 'title' => '_PLOOPI_INSTALL_DATA_BASE_DROP',
                                 'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                 'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));

      $intRequest = null;
      if(!$booFindDbListe) // The database NO exist at the begining DROP after test
      {
        // DROP DATABASE
        $intRequest = $objPDO->exec("DROP DATABASE `{$_SESSION['install']['<DB_DATABASE>']}`");
        $arrInstallInfos[] = array('id' => 'div_db_drop_db',
                                   'state' => ($intRequest !== false) ? true : false,
                                   'title' => '_PLOOPI_INSTALL_DATA_BASE_DROP_DB',
                                   'title_replace' => array($_SESSION['install']['<DB_DATABASE>']),
                                   'warn_replace' => array($_SESSION['install']['<DB_DATABASE>'],$_SESSION['install']['<DB_LOGIN>']));
        $intRequest = null;
      }
    }
    else
    {
      $arrInstallInfos[$intInstallInfos]['state'] = false;
      $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_NAME_DB);
      return false;
    }
  }
  return true;
}
?>