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
 * Fonctions utilisées durant la procédure d'installation de Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * get the list of language available in $replg path (ex: french.php done french in the list)
 *
 * @param string $replg
 * @return array
 */
function ploopi_list_language_enable($replg) {
  $ListFiles=array();
  $dp = opendir($replg);
  while ( $file = readdir($dp) ) {
    if (stripos($file,'.php')!==false) {
      $ListFiles[] = substr($file, 0, strpos($file, '.php'));
    }
  }
  closedir($dp);
  rsort($ListFiles);
  return $ListFiles;
}

/**
 * convert a language type fr, en,.. in french, english,... else false
 *
 * @param array $convert_languages (content fr => french, en => english, etc.)
 * @param string $languageDefault (ex: french) (default = null)
 * @return language
 *
 * @version 1.0
 * @since
 *
 * @category information
 */
function ploopi_catch_language_navigator($convert_languages,$languageDefault=Null) {
  //language detection of navigator
  if(is_array($convert_languages) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
  {
    $langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    if(isset($convert_languages[strtolower(substr(rtrim($langue[0]),0,2))]))
    {
      return $convert_languages[strtolower(substr(rtrim($langue[0]),0,2))];
    }
    elseif(!is_null($languageDefault))
      {
        return $languageDefault;
      }
  }
  return false;
}

/**
 * Remplace %1, %2,... in myString width array toReplace.
 * If controleType is true, you can typed all %x with b = boolean, n = integer, f = float, s = string.
 *
 * @param string $myString
 * @param array $toReplace
 * @param boolean $controlType (default = false)
 * @return string (empty if false)
 *
 * @version 1.0
 * @since
 *
 * @category string manipulation
 */
function ploopi_str_replace($myString, $toReplace,$controlType=false) {

  if(!is_array($toReplace)) return '';

  $fromReplace = array();

  foreach($toReplace as $myNum => $myValue)
  {
    $myNum++;
    if($controlType)
    {
      switch(gettype($myValue))
      {
        case 'boolean' :
          $fromReplace[] = '%'.$myNum.'b';
          break;
        case 'integer' :
          $fromReplace[] = '%'.$myNum.'n';
          break;
        case 'float' :
          $fromReplace[] = '%'.$myNum.'f';
          break;
        case 'string' :
          $fromReplace[] = '%'.$myNum.'s';
          break;
        default :
          $fromReplace[] = '';
          $toReplace[$myNum--] = '';
          break;
      }
    }
    else
    {
      $fromReplace[] = '%'.$myNum;
    }
  }
  return str_replace($fromReplace,$toReplace,$myString);
}

/**
 * Detect all databases supported by Ploopi that are compiled into the current
 * PHP installation.
 *
 * @param array $tab_DB list of database install must support
 * @return An array of database types compiled into PHP.
 *
 * @version 1.0
 * @since
 *
 * @category database
 */
function ploopi_detect_database_available($tab_DB) {
  $databases = array();

  foreach ($tab_DB as $type) {
    if (file_exists('./install.'. $type .'.inc.php')) {
      include_once './install.'. $type .'.inc.php';
      $function = $type .'_dispo';
      if ($function()) {
        $databases[$type] = $type;
      }
    }
  }
  return $databases;
}

/**
 * Get the apache version
 *
 * @return string (empty if false)
 *
 * @version 1.0
 * @since
 *
 * @category information
 */
function ploopi_apache_get_version() {
  $myVersion = apache_get_version();
  $myInfo = explode(" ",$myVersion);
  foreach($myInfo as $value)
  {
    if(stripos($value, 'apache')!==false)
    {
      $myInfo = explode("/",$value);
      return $myInfo[1];
    }
  }
  return '';
}

/**
 * Delete the "/" at the end of path if it is necessary
 *
 * @param string $myString
 * @return string
 *
 * @version 1.0
 * @since
 *
 * @category information
 */
function ploopi_del_end_slashe($myString) {
  $myString = trim($myString);
  $myString = strtr($myString,'\\','/');

  while(strpos($myString,'//')!==false)  $myString = str_replace('//','/',$myString);
  while(substr($myString,-1)=='/') $myString = substr($myString,0,-1);

  return $myString;
}

/**
 * Transform a bytes value in humain value (KBytes, MBytes,...) with arround to 2 decimals
 *
 * @param integer $Bytes
 * @return string (ex: 27.20 GByte)
 */
function ploopi_human_size($Bytes)
{
  $Type=array("bytes", "Kbytes", "Mbytes", "Gbytes", "Tbytes");
  $Index=0;
  while($Bytes>=1024 && $Index<3)
  {
    $Bytes/=1024;
    $Index++;
  }
  return("".round($Bytes,2)." ".$Type[$Index]);
}

/**
 * Detect error in array $myInfos[]['state']
 *
 * @param array $myInfos
 * @return boolean True if is find else False
 */
function ploopi_find_error_install($arrInstallInfos)
{
  if(is_array($arrInstallInfos))
  {
    foreach($arrInstallInfos as $arrInstallInfo)
    {
      if(isset($arrInstallInfo['state']) && $arrInstallInfo['state'] == false) return true;
    }
  }
  return false;
}
?>