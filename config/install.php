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
 * Procédure d'installation de Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Init Session
 */
session_start();

/**
 * On teste l'existence du fichier de configuration
 */
if (file_exists('./config.php'))
{
  echo ('<div style="text-align:center;color:red;">Config exist !<br />You must delete ./config/install.php !</div>');
  die();
}

define ('_PLOOPI_ERROR_REPORTING', E_ALL);

define ('_PLOOPI_SERVER_OSTYPE', (substr(PHP_OS, 0, 3) == 'WIN') ? 'windows' : 'unix');

/**
 * Init parameter for installation
 */
chdir('..');

//Inclusion/Requirement
require_once './include/functions/errors.php';
ploopi_set_error_handler();

require_once './include/start/constants.php';
require_once './include/start/functions.php';
require_once './lib/template/template.php';

require_once './config/install/functions.inc.php';

// Systeme Request
$arrInstallRequestSys = array(
  'apache'    => '1.3',
  'php'       => '5.1'
);

$arrParamPhp = array(
  'magic_quotes_gpc'    => 0,
  'memory_limit'        => 128,
  'post_max_size'       => 16,
  'upload_max_filesize' => 16
);

// DataBase Request
$arrInstallRequestDB = array(
  'mysql'         => array('name' => 'MySQL','version' => '5','php' => 'mysql_connect', 'pdo' => 'PDO_MYSQL')
);
//  'postgresql'    => array('name' => 'PostgreSQL','version' => '7','php' => 'pg_connect','pdo' => 'PDO_PGSQL')

// $arrInstallRequestDB Cleaner with pdo driver available (or function php ;-) )
// Useful when you have several types of database
$arrInstallRequestDBTempo = array();
foreach($arrInstallRequestDB as $type => $detail)
{
  if(extension_loaded($detail['pdo']) || function_exists($detail['php']))
    $arrInstallRequestDBTempo[$type] = $detail;
}
$arrInstallRequestDB = $arrInstallRequestDBTempo;
unset($arrInstallRequestDBTempo);

//To convert language send by navigator to filename
$arrInstallConvertLanguages = array(
  'fr'    => 'french',
  'en'    => 'english'
);

$booInstallAddButtonRefresh = false; // if true, add Refresh button in template
$booInstallJamButtonNext = false; // if true, jam next button in template

//Clean / init
$arrInfos = array();

//init tab param
if(!isset($_SESSION['install'])) {
  // in order of apparaition in file 'config.php.model'
  $_SESSION['install'] = array(
    '<DB_TYPE>'         => 'mysqli',          // ok
    '<DB_SERVER>'       => 'localhost',       // ok
    '<DB_LOGIN>'        => 'root',            // ok
    '<DB_PASSWORD>'     => '',                // ok
    '<DB_DATABASE>'     => '',                // ok
    '<BASEPATH>'        => ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://').((!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).((!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') ? ":{$_SERVER['SERVER_PORT']}" : '').((!empty($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] != '') ? dirname($_SERVER['SCRIPT_NAME']) : '/'),
    '<DATAPATH>'        => './data',          // ok
    '<TMPPATH>'         => '/tmp',            // ok
    '<CGI>'             => false,             // ok
    '<CGIPATH>'         => './cgi',           // ok
    '<USE_DBSESSION>'   => true,              // ok
    '<URL_ENCODE>'      => true,              // ok
    '<SECRETKEY>'       => 'ma phrase secrete', // ok
    '<FRONTOFFICE>'     => true,              // ok
    '<REWRITERULE>'     => true,              // ok
    '<PEARPATH>'        => '/usr/share/php',  // ok
    '<INTERNETPROXY_HOST>' => '',
    '<INTERNETPROXY_PORT>' => '',
    '<INTERNETPROXY_USER>' => '',
    '<INTERNETPROXY_PASS>' => '',
    // Not in config.php
    '<ADMIN_LOGIN>'     => 'admin',           // ok
    '<ADMIN_PASSWORD>'  => 'admin',           // ok
    '<ADMIN_MAIL>'      => '',                // ok
    '<SYS_MAIL>'      => '',                  // ok
    '<LANGUAGE>'        => 'french',          // ok
    '<SITE_NAME>'       => 'PLOOPI',          // ok
    '<TIME_ZONE>'       => '1',
    '<AUTO_UPDATE>'     => true,
    'replace_database'  => false,
    'level_validate'    => 0,
    'accept_license'    => false
    );
}
/**
 * LANGUAGE
 */
// Select message translation
if(isset($_POST['language'])) $_SESSION['install']['<LANGUAGE>'] = $_POST['language'];
if(!file_exists('./config/install/lang/'.$_SESSION['install']['<LANGUAGE>'].'.php'))
{
  $_SESSION['install']['<LANGUAGE>'] = catch_language_navigator($convert_languages,'french');
}
// Include message translation (or not...)
if($_SESSION['install']['<LANGUAGE>'] != false && file_exists('./config/install/lang/'.$_SESSION['install']['<LANGUAGE>'].'.php'))
{
  require_once './config/install/lang/'.$_SESSION['install']['<LANGUAGE>'].'.php';
}
else
{
  unset($_SESSION['install']['<LANGUAGE>']);
}

/**
 * All stages of installation
 */
$arrInstallAllStages = array(
1 => _PLOOPI_INSTALL_LICENSE,
_PLOOPI_INSTALL_LANGUAGE_AND_FIRST_CTRL,
_PLOOPI_INSTALL_PARAM_INSTALL,
_PLOOPI_INSTALL_PARAM_DB,
_PLOOPI_INSTALL_END
);

/**
 * Stages
 */
// Error No javascript !!!
if(isset($_POST['nojs'])) $_POST['stage'] = 2;

// Select installation stage
if(!isset($_POST['stage'])) $_POST['stage'] = 1;

// Control if the new level is good (if the user he's not a cheater)
if($_POST['stage']>$_SESSION['install']['level_validate']+1)
{
  $_POST['stage'] = $_SESSION['install']['level_validate'];
}
else
{
  $_SESSION['install']['level_validate'] = $_POST['stage'];
}

ob_start();

/**
 * Template
 */
// Select template
$objInstallTemplate = new Template('./templates/install');
$objInstallTemplate->set_filenames(array('install' => 'install.tpl'));

/****************************************************************************************/

/**
 * LEVEL OF INSTALLATION / TRAITEMENT
 *
 * $infos[] must contain :
 *   id        = used for div id
 *   state     = only for test (true if status is ok, false else)
 *   title     = root for message (title,mess AND warning) used for translate
 *   form      = formulaire use to get information
 *   title_replace = array width all text to replace %1,%2,... in title
 *   mess_replace  = " in mess
 *   warn_replace  = " in warn
 *   form_hidden = 1  -> Form hidden width JS
 *                 0  -> Form visible WIDTH JS
 *                 not declare -> Form visible without JS
 *   form      = Array content one a few arrays with two or three datas :
 *                - label for field
 *                - input with html code form input (use class="form_input")
 *                - js (optionnal) if exist a javascrit control will be created for this form.
 *                              Contain ploopi_validatefield line
 */

/**
 * STAGE 1 = License GPL ------------------------------------------------------
 */

$stage = 1;
if($_POST['stage']>=$stage)
{
  if(isset($_POST['accept_license']))    $_SESSION['install']['accept_license'] = ($_POST['accept_license']=='true' ? true : false);

  if ($_SESSION['install']['accept_license']==true)
  { $strInstallAcceptLicenseYes = 'selected';$strInstallAcceptLicenseNo = ''; }
  else
  { $strInstallAcceptLicenseYes = '';$strInstallAcceptLicenseNo = 'selected'; }

   // Control Acceptation licence
   $arrInstallInfos[] = array('id' => 'div_accept_license',
           'title'   => _PLOOPI_INSTALL_LICENSE,
           'state'   => $_SESSION['install']['accept_license'],
           'form'    => array( array('label' => _PLOOPI_INSTALL_LICENSE_ACCEPT,
                                     'input' => '<select name="accept_license" id="accept_license" tabindex="%tabIndex%">
                                                   <option value="true" '.$strInstallAcceptLicenseYes.'>'._PLOOPI_INSTALL_YES.'</option>
                                                   <option value="false" '.$strInstallAcceptLicenseNo.'>'._PLOOPI_INSTALL_NO.'</option>
                                                 </select>'
                                    )
                              )
                            );

  // test or re-test and stop at the courant stage if an error is detected
  if(ploopi_find_error_install($arrInstallInfos))
  {
    $_POST['stage']=$stage;
  }
  elseif($_POST['stage']>$stage)
  {
     unset($arrInstallInfos);
  }

  // features of stage 1 (at the end for eventual comeback)
  if($_POST['stage']==$stage)
  {
    $strTxtLicense = _PLOOPI_INSTALL_LICENSE_TXT;

    if(file_exists('./doc/LICENSE') && is_readable('./doc/LICENSE'))
    {
       $strTxtLicense = file_get_contents('./doc/LICENSE');
       $strTxtLicense = htmlentities($strTxtLicense);
       $strTxtLicense = str_replace("\n\n",'<br /><br />',$strTxtLicense);
       $strTxtLicense = str_replace("\n",' ',$strTxtLicense);
    }
    $objInstallTemplate->assign_block_vars("stage$stage",array(
      'TEXT'    => _PLOOPI_INSTALL_WELCOME_TEXT,

      'LICENSE' => $strTxtLicense
    ));
  }
}

/**
 * STAGE 2 = Choose Language + Control requested ------------------------------------------------------
 */
$stage++;
if($_POST['stage']>=$stage)
{
  if(isset($_POST['dir_pear'])) $_SESSION['install']['<PEARPATH>'] = ploopi_del_end_slashe(trim($_POST['dir_pear']));

  $strVersionApache = ploopi_apache_get_version();

  // Control APACHE
  $arrInstallInfos[] = array(
            'id'        => 'div_apache',
            'state'     => (($strVersionApache != 'Apache with ServerTokens Prod') ? version_compare($strVersionApache,$arrInstallRequestSys['apache'],'>=') : true),
            'title'     => '_PLOOPI_INSTALL_APACHE',
            'mess_replace' => array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestSys['apache'],(($strVersionApache != 'Apache with ServerTokens Prod') ? _PLOOPI_INSTALL_INSTALLED.$strVersionApache : $strVersionApache))
  );
  // Control PHP
  $arrInstallInfos[] = array(
            'id'        => 'div_php',
            'state'     => version_compare(phpversion(),$arrInstallRequestSys['php'],">="),
            'title'     => '_PLOOPI_INSTALL_PHP',
            'mess_replace' => array(_PLOOPI_INSTALL_REQUIRED.$arrInstallRequestSys['php'],
                                    _PLOOPI_INSTALL_INSTALLED.phpversion(),
                                    ((intval(ini_get('magic_quotes_gpc')) == $arrParamPhp['magic_quotes_gpc']) ? 'OFF' : '<font style="color:red;">ON</font>'),
                                    ((intval(ini_get('memory_limit')) >= $arrParamPhp['memory_limit']) ? ini_get('memory_limit') : '<font style="color:red;">'.ini_get('memory_limit').'</font>'),
                                    ((intval(ini_get('post_max_size')) >= $arrParamPhp['post_max_size']) ? ini_get('post_max_size') : '<font style="color:red;">'.ini_get('post_max_size').'</font>'),
                                    ((intval(ini_get('upload_max_filesize')) >= $arrParamPhp['upload_max_filesize']) ? ini_get('upload_max_filesize') : '<font style="color:red;">'.ini_get('upload_max_filesize').'</font>'))
  );

  // Control extension GD
  $arrInstallInfos[] = array('id' => 'div_gd', 'state' => extension_loaded('gd'), 'title' => '_PLOOPI_INSTALL_GD');
  // Control extention mCrypt
  $arrInstallInfos[] = array('id' => 'div_mcrypt', 'state' => extension_loaded('mcrypt'), 'title' => '_PLOOPI_INSTALL_MCRYPT');
  // Control extention mCrypt
  $arrInstallInfos[] = array('id' => 'div_pdo', 'state' => extension_loaded('PDO'), 'title' => '_PLOOPI_INSTALL_PDO');
  // Control extension Stem
  $arrInstallInfos[] = array('id' => 'div_stem', 'state' => extension_loaded('stem'), 'title' => '_PLOOPI_INSTALL_STEM');
  // Control Pear extension
  ini_restore ('include_path');
  if(file_exists($_SESSION['install']['<PEARPATH>'].'/PEAR.php'))
  {
    if(!strpos(ini_get('include_path'),$_SESSION['install']['<PEARPATH>'])) ini_set('include_path',ini_get('include_path').(_PLOOPI_SERVER_OSTYPE == 'windows' ? ';' : ':').$_SESSION['install']['<PEARPATH>']);
    include_once 'PEAR.php';
    $arrInstallInfos[] = array('id' => 'div_pear',
                     'state' => true,
                     'title' => '_PLOOPI_INSTALL_PEAR',
                     'form'    => array( array('label' => _PLOOPI_INSTALL_SELECT_PEAR,
                                               'input' => '<input name="dir_pear" id="dir_pear" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<PEARPATH>'].'"/>',
                                               'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_PEAR_JS).'\',form.dir_pear,\'string\')'
                                              )
                                     )
                    );
    // Control packages pear's :
    // PEAR_info
    if(file_exists($_SESSION['install']['<PEARPATH>'].'/PEAR/Info.php'))
    {
      include_once 'PEAR/Info.php';
      $arrInstallInfos[] = array('id' => 'div_pear_info', 'state' => true, 'title' => '_PLOOPI_INSTALL_PEAR_INFO');
      $packPEAR = new PEAR_Info(); // Class PEAR_Info for test if modules pear are installed
      // Cache_Lite
      $arrInstallInfos[] = array('id' => 'div_pear_Cache_Lite', 'state' => $packPEAR->packageInstalled('Cache_Lite', '1.7.15'), 'title' => '_PLOOPI_INSTALL_PEAR_CACHE_LITE');
      // HTTP_Request2
      $arrInstallInfos[] = array('id' => 'div_pear_HTTP_Request2', 'state' => $packPEAR->packageInstalled('HTTP_Request2', '2.1.1'), 'title' => '_PLOOPI_INSTALL_PEAR_HTTP_REQUEST');
      // Text_Highlighter
      $arrInstallInfos[] = array('id' => 'div_pear_Text_Highlighter', 'state' => $packPEAR->packageInstalled('Text_Highlighter', '0.7.3'), 'title' => '_PLOOPI_INSTALL_PEAR_TEXT_HIGHLIGHTER');
      // Net_UserAgent_Detect
      $arrInstallInfos[] = array('id' => 'div_pear_Net_UserAgent_Detect', 'state' => $packPEAR->packageInstalled('Net_UserAgent_Detect', '2.5.2'), 'title' => '_PLOOPI_INSTALL_PEAR_NET_USERAGENT_DETECT');
      // Horde_Text_Diff
      $arrInstallInfos[] = array('id' => 'div_pear_Horde_Text_Diff', 'state' => $packPEAR->packageInstalled('Horde_Text_Diff', '2', 'pear.horde.org'), 'title' => '_PLOOPI_INSTALL_PEAR_HORDE_TEXT_DIFF');
      // Horde_Text_Diff
      $arrInstallInfos[] = array('id' => 'div_pear_XML_Serializer', 'state' => $packPEAR->packageInstalled('XML_Serializer', '0.20.2'), 'title' => '_PLOOPI_INSTALL_PEAR_XML_SERIALIZER');

    }
    else // PEAR_Info not installed
    {
      $arrInstallInfos[] = array('id' => 'div_pear_info', 'state' => false, 'title' => '_PLOOPI_INSTALL_PEAR_INFO');
    }
  }
  else // PEAR not installed
  {
    $arrInstallInfos[] = array('id' => 'div_pear',
                     'state' => false,
                     'title' => '_PLOOPI_INSTALL_PEAR',
                     'form'    => array( array('label' => _PLOOPI_INSTALL_SELECT_PEAR,
                                               'input' => '<input name="dir_pear" id="dir_pear" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<PEARPATH>'].'"/>',
                                               'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_PEAR_JS).'\',form.dir_pear,\'string\')'
                                              )
                                       )
                     );
  }
  // test or re-test and stop at the courant stage if an error is detected
  if(ploopi_find_error_install($arrInstallInfos))
  {
    $_POST['stage']=$stage;
  }
  elseif($_POST['stage']>$stage)
  {
     unset($arrInstallInfos);
  }

  // features of stage 2 (at the end for eventual comeback)
  if($_POST['stage']==$stage)
  {
    /*
    // List languages
    $arrInstallListLanguages = ploopi_list_language_enable('./config/install/lang/');
    $objInstallTemplate->assign_block_vars("stage$stage",array(
        'CHOOSE_LANGUAGE'   => _PLOOPI_INSTALL_CHOOSE_LANGUAGE,
    ));
    // Block languages
    foreach($arrInstallListLanguages as $strInstallLanguage)
    {
      $strInstallLanguageSelected = ($_SESSION['install']['<LANGUAGE>']==$strInstallLanguage) ? 'selected' : '';
      $objInstallTemplate->assign_block_vars("stage$stage.languages",array(
                    'LANGUAGE' => $strInstallLanguage,
                    'SELECTED' => $strInstallLanguageSelected
      ));
    }
    */
  }
} // end stage 2

/**
 * STAGE 3 = Control requested ------------------------------------------------------
 */
$stage++;
if($_POST['stage']>=$stage)
{
  /* if(isset($_POST['site_name']))     $_SESSION['install']['<SITE_NAME>'] = trim($_POST['site_name']); */
  if(isset($_POST['url_base']))      $_SESSION['install']['<BASEPATH>'] = trim($_POST['url_base']);
  if(isset($_POST['dir_data']))      $_SESSION['install']['<DATAPATH>'] = ploopi_del_end_slashe(trim($_POST['dir_data']));
  if(isset($_POST['dir_tmp']))       $_SESSION['install']['<TMPPATH>'] = ploopi_del_end_slashe(trim($_POST['dir_tmp']));
  if(isset($_POST['cgi_active']))    $_SESSION['install']['<CGI>'] = ($_POST['cgi_active']=='true' ? true : false);
  if(isset($_POST['dir_cgi']))       $_SESSION['install']['<CGIPATH>'] = ploopi_del_end_slashe(trim($_POST['dir_cgi']));
  if(isset($_POST['log_admin']))     $_SESSION['install']['<ADMIN_LOGIN>'] = trim($_POST['log_admin']);
  if(isset($_POST['pass_admin']))    $_SESSION['install']['<ADMIN_PASSWORD>'] = trim($_POST['pass_admin']);
  if(isset($_POST['secret']))        $_SESSION['install']['<SECRETKEY>'] = trim($_POST['secret']);
  if(isset($_POST['email_admin']))   $_SESSION['install']['<ADMIN_MAIL>'] = trim($_POST['email_admin']);
  if(isset($_POST['email_sys']))     $_SESSION['install']['<SYS_MAIL>'] = trim($_POST['email_sys']);
  if(isset($_POST['url_encode']))    $_SESSION['install']['<URL_ENCODE>'] = ($_POST['url_encode']=='true' ? true : false);
  if(isset($_POST['session_bdd']))   $_SESSION['install']['<USE_DBSESSION>'] = ($_POST['session_bdd']=='true' ? true : false);
  if(isset($_POST['front_active']))  $_SESSION['install']['<FRONTOFFICE>'] = ($_POST['front_active']=='true' ? true : false);
  if(isset($_POST['front_rewrite'])) $_SESSION['install']['<REWRITERULE>'] = ($_POST['front_rewrite']=='true' ? true : false);
  if(isset($_POST['proxy_host']))    $_SESSION['install']['<INTERNETPROXY_HOST>'] = trim($_POST['proxy_host']);
  if(isset($_POST['proxy_port']))    $_SESSION['install']['<INTERNETPROXY_PORT>'] = intval($_POST['proxy_port']);
  if(isset($_POST['proxy_user']))    $_SESSION['install']['<INTERNETPROXY_USER>'] = trim($_POST['proxy_user']);
  if(isset($_POST['proxy_pass']))    $_SESSION['install']['<INTERNETPROXY_PASS>'] = trim($_POST['proxy_pass']);

  // clean the base path
  while(substr($_SESSION['install']['<BASEPATH>'],-1)=='/')
    $_SESSION['install']['<BASEPATH>'] = substr($_SESSION['install']['<BASEPATH>'],0,-1);
  while(substr($_SESSION['install']['<BASEPATH>'],-7)=='/config')
    $_SESSION['install']['<BASEPATH>'] = substr($_SESSION['install']['<BASEPATH>'],0,-7);

  // Control config directories are writable
  if(!is_writable('./config'))
    $arrInstallInfos[] = array('id' => 'div_config', 'state' => false, 'title' => '_PLOOPI_INSTALL_CONFIG_WRITE');
  // Control if config.php.modle exist
  if(!file_exists('./config/config.php.model') || !is_readable('./config/config.php.model'))
    $arrInstallInfos[] = array('id' => 'div_config_model', 'state' => false, 'title' => '_PLOOPI_INSTALL_CONFIG_MODEL');
  // Control if file sql is ok
  if(!file_exists('./install/system/ploopi.sql') || !is_readable('./install/system/ploopi.sql'))
  $arrInstallInfos[] = array('id' => 'div_config', 'state' => false, 'title' => '_PLOOPI_INSTALL_SQL_FILE');

  //Path CGI
  if($_SESSION['install']['<CGI>']==false && $_SESSION['install']['<CGIPATH>'] == '')
    $_SESSION['install']['<CGIPATH>'] == './cgi';

  // Select yes/No
  if ($_SESSION['install']['<CGI>']==true)
  { $strInstallCgiActiveTrue = 'selected';$strInstallCgiActiveFalse = ''; }
  else
  { $strInstallCgiActiveTrue = '';$strInstallCgiActiveFalse = 'selected'; }
  if ($_SESSION['install']['<URL_ENCODE>']==true)
  { $strInstallUrlEncodeTrue = 'selected';$strUrlEncodeFalse = ''; }
  else
  { $strInstallUrlEncodeTrue = '';$strUrlEncodeFalse = 'selected'; }
  if($_SESSION['install']['<USE_DBSESSION>']==true)
  { $strInstallSessionBddTrue = 'selected';$strInstallSessionBddFalse = ''; }
  else
  { $strInstallSessionBddTrue = '';$strInstallSessionBddFalse = 'selected'; }
  if($_SESSION['install']['<FRONTOFFICE>']==true)
  { $strInstallFrontEncodeTrue = 'selected';$strInstallFrontEncodeFalse = ''; }
  else
  { $strInstallFrontEncodeTrue = '';$strInstallFrontEncodeFalse = 'selected'; }
  if($_SESSION['install']['<REWRITERULE>']==true)
  { $strInstallFrontRewriteTrue = 'selected';$strInstallFrontRewriteFalse = ''; }
  else
  { $strInstallFrontRewriteTrue = '';$strInstallFrontRewriteFalse = 'selected'; }

  // test if data Folder no exist
  if(!is_dir($_SESSION['install']['<DATAPATH>']))
  {
    $arrInstallInfos[] = array(
            'id'      => 'div_data',
            'state'   => false,
            'title'   => '_PLOOPI_INSTALL_DATA_EXIST',
            'title_replace' => array($_SESSION['install']['<DATAPATH>']),
            'mess_replace' => array($_SESSION['install']['<DATAPATH>']),
            'warn_replace' => array($_SESSION['install']['<DATAPATH>']),
            'form'    => array( array('label'  => _PLOOPI_INSTALL_SELECT_DATA,
                                      'input' => '<input name="dir_data" id="dir_data" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<DATAPATH>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_DATA_JS).'\',form.dir_data,\'string\')'
                                     )
                              )
                    );
  }
  else //Folder exist. Writable / no writable ?
  {
    $arrInstallInfos[] = array(
            'id'      => 'div_data',
            'state'   => is_writable($_SESSION['install']['<DATAPATH>']),
            'title'    => '_PLOOPI_INSTALL_DATA_WRITE',
            'title_replace' => array($_SESSION['install']['<DATAPATH>']),
            'mess_replace' => array($_SESSION['install']['<DATAPATH>'],_PLOOPI_INSTALL_SELECT_DATA_INFO_PLACE.ploopi_human_size(disk_free_space($_SESSION['install']['<DATAPATH>']))),
            'warn_replace' => array($_SESSION['install']['<DATAPATH>']),
            'form'    => array( array('label' => _PLOOPI_INSTALL_SELECT_DATA,
                                      'input' => '<input name="dir_data" id="dir_data" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<DATAPATH>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_DATA_JS).'\',form.dir_data,\'string\')'
                                     )
                              )
                    );
  }

  // test if TMP Folder no exist
  if(!is_dir($_SESSION['install']['<TMPPATH>']))
  {
    $arrInstallInfos[] = array(
            'id'      => 'div_tmp',
            'state'   => false,
            'title'   => '_PLOOPI_INSTALL_TMP_EXIST',
            'title_replace' => array($_SESSION['install']['<TMPPATH>']),
            'mess_replace' => array($_SESSION['install']['<TMPPATH>']),
            'warn_replace' => array($_SESSION['install']['<TMPPATH>']),
            'form'    => array( array('label'  => _PLOOPI_INSTALL_SELECT_TMP,
                                      'input' => '<input name="dir_tmp" id="dir_tmp" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<TMPPATH>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_TMP_JS).'\',form.dir_tmp,\'string\')'
                                     )
                              )
                    );
  }
  else //Folder exist. Writable / no writable ?
  {
    $arrInstallInfos[] = array(
            'id'      => 'div_tmp',
            'state'   => is_writable($_SESSION['install']['<TMPPATH>']),
            'title'    => '_PLOOPI_INSTALL_TMP_WRITE',
            'title_replace' => array($_SESSION['install']['<TMPPATH>']),
            'mess_replace' => array(ploopi_human_size(disk_free_space($_SESSION['install']['<TMPPATH>']))),
            'warn_replace' => array($_SESSION['install']['<TMPPATH>']),
            'form'    => array( array('label' => _PLOOPI_INSTALL_SELECT_TMP,
                                      'input' => '<input name="dir_tmp" id="dir_tmp" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<TMPPATH>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_TMP_JS).'\',form.dir_tmp,\'string\')'
                                     )
                              )
                    );
  }
    // test if TMP Folder no exist
  if((!is_dir($_SESSION['install']['<CGIPATH>'])) && ($_SESSION['install']['<CGI>'] == true))
  {
    // CGI Use and Path
    $arrInstallInfos[] = array(
              'id' => 'div_title_cgi',
              'title' => '_PLOOPI_INSTALL_CGI_NO_EXIST',
              'state'   => false,
              'title_replace' => array($_SESSION['install']['<CGIPATH>']),
              'mess_replace' => array($_SESSION['install']['<CGIPATH>']),
              'warn_replace' => array($_SESSION['install']['<CGIPATH>']),
              'form'    => array( array('label' => _PLOOPI_INSTALL_CGI_ACTIVE,
                                       'input' => '<select name="cgi_active" id="cgi_active" tabindex="%tabIndex%">
                                                     <option value="true" '.$strInstallCgiActiveTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                     <option value="false" '.$strInstallCgiActiveFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                   </select>'
                                      ),
                                 array('label' => _PLOOPI_INSTALL_CGI_PATH,
                                        'input' => '<input name="dir_cgi" id="dir_cgi" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<CGIPATH>'].'"/>',
                                        'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_CGI_JS).'\',form.dir_cgi,\'string\')'
                                       )
                               )
                     );
  }
  else
  {
    // CGI Use and Path
    $arrInstallInfos[] = array(
              'id' => 'div_title_cgi',
              'title' => '_PLOOPI_INSTALL_CGI_EXIST',
              'state'   => (($_SESSION['install']['<CGIPATH>'] != '' && is_readable($_SESSION['install']['<CGIPATH>'])) || $_SESSION['install']['<CGI>'] == false),
              'title_replace' => array($_SESSION['install']['<CGIPATH>']),
              'mess_replace' => array($_SESSION['install']['<CGIPATH>']),
              'warn_replace' => array($_SESSION['install']['<CGIPATH>']),
              'form'    => array( array('label' => _PLOOPI_INSTALL_CGI_ACTIVE,
                                       'input' => '<select name="cgi_active" id="cgi_active" tabindex="%tabIndex%">
                                                     <option value="true" '.$strInstallCgiActiveTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                     <option value="false" '.$strInstallCgiActiveFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                   </select>'
                                      ),
                                 array('label' => _PLOOPI_INSTALL_CGI_PATH,
                                        'input' => '<input name="dir_cgi" id="dir_cgi" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<CGIPATH>'].'"/>',
                                        'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SELECT_CGI_JS).'\',form.dir_cgi,\'string\')'
                                       )
                               )
                     );
  }
  // Personal informations
  $arrInstallInfos[] = array('id' => 'div_title_param_ploopi',
           'title' => '_PLOOPI_INSTALL_PARAM_PLOOPI',
           'form'    => array( /* array('label' => _PLOOPI_INSTALL_SITE_NAME,
                                     'input' => '<input name="site_name" id="site_name" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<SITE_NAME>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SITE_NAME_JS).'\',form.site_name,\'string\')'
                                    ),*/
                               /*array('label' => _PLOOPI_INSTALL_URL_BASE,
                                     'input' => '<input name="url_base" id="url_base" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<BASEPATH>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_URL_BASE_JS).'\',form.url_base,\'string\')'
                                    ),*/
                               array('label' => _PLOOPI_INSTALL_ADMIN_LOGIN,
                                     'input' => '<input name="log_admin" id="log_admin" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<ADMIN_LOGIN>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_ADMIN_LOGIN_JS).'\',form.log_admin,\'string\')'
                                    ),
                               array('label' => _PLOOPI_INSTALL_ADMIN_PWD,
                                     'input' => '<input name="pass_admin" id="pass_admin" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<ADMIN_PASSWORD>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_ADMIN_PWD_JS).'\',form.pass_admin,\'string\')'
                                    ),
                               array('label' => _PLOOPI_INSTALL_SECRET_SENTENCE,
                                     'input' => '<input name="secret" id="secret" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<SECRETKEY>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SECRET_SENTENCE_JS).'\',form.secret,\'string\')'
                                    ),
                               array('label' => _PLOOPI_INSTALL_ADMIN_MAIL,
                                     'input' => '<input name="email_admin" id="email_admin" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<ADMIN_MAIL>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_ADMIN_MAIL_JS).'\',form.email_admin,\'emptyemail\')'
                                    ),
                               array('label' => _PLOOPI_INSTALL_SYS_MAIL,
                                     'input' => '<input name="email_sys" id="email_sys" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<SYS_MAIL>'].'"/>',
                                     'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_SYS_MAIL_JS).'\',form.email_sys,\'emptyemail\')'
                                    ),
                               array('label' => _PLOOPI_INSTALL_URL_ENCODE,
                                     'input' => '<select name="url_encode" id="url_encode" tabindex="%tabIndex%">
                                                   <option value="true" '.$strInstallUrlEncodeTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                   <option value="false" '.$strInstallUrlEncodeFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                 </select>'
                                    ),
                               array('label' => _PLOOPI_INSTALL_SESSION_BDD,
                                     'input' => '<select name="session_bdd" id="session_bdd" tabindex="%tabIndex%">
                                                   <option value="true" '.$strInstallSessionBddTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                   <option value="false" '.$strInstallSessionBddFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                 </select>'
                                    )
                             )
                   );
  // FrontOffice and rewrite
  $arrInstallInfos[] = array('id' => 'div_title_frontoffice',
           'title' => '_PLOOPI_INSTALL_FRONT_OFFICE',
           'form'    => array( array('label' => _PLOOPI_INSTALL_FRONT_ACTIVE,
                                     'input' => '<select name="front_active" id="front_active" tabindex="%tabIndex%">
                                                   <option value="true" '.$strInstallFrontActiveTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                   <option value="false" '.$strInstallFrontActiveFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                 </select>'
                                    ),
                               array('label' => _PLOOPI_INSTALL_FRONT_REWRITE,
                                     'input' => '<select name="front_rewrite" id="front_rewrite" tabindex="%tabIndex%">
                                                   <option value="true" '.$strInstallFrontActiveTrue.'>'._PLOOPI_INSTALL_YES.'</option>
                                                   <option value="false" '.$strInstallFrontActiveFalse.'>'._PLOOPI_INSTALL_NO.'</option>
                                                 </select>'
                                    )
                             )
                   );

  // Test internet connection
  $booFormHidden = ($_POST['div_connect_form_hidden']=="1") ? true : false;
  require_once 'HTTP/Request2.php';
  $objRequest = new HTTP_Request2('http://www.ovensia.fr');

  if ($_SESSION['install']['<INTERNETPROXY_HOST>'] != '')
  {
    $arrConfig['proxy_host'] = $_SESSION['install']['<INTERNETPROXY_HOST>'];
    $arrConfig['proxy_port'] = $_SESSION['install']['<INTERNETPROXY_PORT>'];
    $arrConfig['proxy_user'] = $_SESSION['install']['<INTERNETPROXY_USER>'];
    $arrConfig['proxy_password'] = $_SESSION['install']['<INTERNETPROXY_PASS>'];
    $arrConfig['proxy_auth_scheme'] = HTTP_Request2::AUTH_BASIC;
    $objRequest->setConfig($arrConfig);
    $booFormHidden = '0';
  }
  else $booFormHidden = '1';

  $state = false;

  try {
    $objRep = $objRequest->send();
    $state = true;
  } catch (HTTP_Request2_Exception $e) { }

  // All form hidden or not
  $arrInstallInfos[] = array('id' => 'div_connect',
                             'state' => $state,
                             'title' => '_PLOOPI_INSTALL_WEB_CONNECT',
                             'form_hidden' => $booFormHidden,
                             'form'  => array( array('label' => _PLOOPI_INSTALL_PROXY_HOST,
                                                     'input' => '<input name="proxy_host" id="proxy_host" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<INTERNETPROXY_HOST>'].'"/>'
                                                    ),
                                               array('label' => _PLOOPI_INSTALL_PROXY_PORT,
                                                     'input' => '<input name="proxy_port" id="proxy_port" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<INTERNETPROXY_PORT>'].'"/>'
                                                    ),
                                               array('label' => _PLOOPI_INSTALL_PROXY_USER,
                                                     'input' => '<input name="proxy_user" id="proxy_user" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<INTERNETPROXY_USER>'].'"/>'
                                                     ),
                                               array('label' => _PLOOPI_INSTALL_PROXY_PASS,
                                                     'input' => '<input name="proxy_pass" id="proxy_pass" type="password" tabindex="%tabIndex%" value="'.$_SESSION['install']['<INTERNETPROXY_PASS>'].'"/>'
                                                    )
                                             )
                            );

  // test or re-test and stop at the courant stage if an error is detected
  if(ploopi_find_error_install($arrInstallInfos))
  {
    $_POST['stage']=$stage;
  }
  elseif($_POST['stage']>$stage)
  {
    unset($arrInstallInfos);
  }

  // features of stage 3 (at the end for eventual comeback)
  if($_POST['stage']==$stage)
  {
    /* exemple :
    $objInstallTemplate->assign_block_vars("stage$stage",array(
        'TEXT' => _PLOOPI_INSTALL_TEXT
    ));
    */
  }
} // end stage 3

/**
 * STAGE 4 = Parameter for DB -------------------------------------------------------
 */
$stage++;
if($_POST['stage']>=$stage)
{
  if(isset($_POST['db_type']))     $_SESSION['install']['<DB_TYPE>'] = $_POST['db_type'];
  if(isset($_POST['db_server']))   $_SESSION['install']['<DB_SERVER>'] = trim($_POST['db_server']);
  if(isset($_POST['db_login']))    $_SESSION['install']['<DB_LOGIN>'] = trim($_POST['db_login']);
  if(isset($_POST['db_pwd']))      $_SESSION['install']['<DB_PASSWORD>'] = trim($_POST['db_pwd']);
  if(isset($_POST['db_database_name'])) $_SESSION['install']['<DB_DATABASE>'] = trim($_POST['db_database_name']);
  $_SESSION['install']['<DB_DATABASE>'] = str_replace(' ','_',$_SESSION['install']['<DB_DATABASE>']);
  // database reserved by mysql. Dont' Touch !
  if($_SESSION['install']['<DB_TYPE>'] == 'mysql' && ($_SESSION['install']['<DB_DATABASE>'] === 'information_schema' || $_SESSION['install']['<DB_DATABASE>'] === 'mysql'))
    $_SESSION['install']['<DB_DATABASE>'] = '';
  // Replace database !
  $_SESSION['install']['replace_database'] = (isset($_POST['del_exist']) && intval($_POST['del_exist'])==1) ? true : false;

  // make a database menu
  $strInstallListTypeDb='';
  foreach($arrInstallRequestDB as $strInstallTypeDB => $arrDetail)
  {
    //if $strInstallTypeDB == 'mysql' && (function_exists('mysql_connect') || ()
    $strInstallSelected = ($strInstallTypeDB == $_SESSION['install']['<DB_TYPE>']) ? 'selected' : '';
    $strInstallListTypeDb .= '<option value="'.$strInstallTypeDB.'" '.$strInstallSelected.'>'.$arrDetail['name'].' (>='.$arrDetail['version'].')</option>';
  }

  // ATTENTION : some information in the last $arrInstallInfos will be modify
  $intInstallInfos = count($arrInstallInfos);

  // Principal Form for database configuration ($intInstallInfos used to modify this $arrInstallInfos)
  $arrInstallInfos[$intInstallInfos] = array('id' => 'div_title_database',
            'state'   => true,
            'title' => '_PLOOPI_INSTALL_DATA_BASE',
            'title_replace' => array($arrInstallRequestDB[$_SESSION['install']['<DB_TYPE>']]['name']),
            'mess_replace' => array('',''),
            'warn_replace' => array(''),
            'form'    => array( array('label' => _PLOOPI_INSTALL_DB_TYPE,
                                      'input' => '<select name="db_type" id="db_type" tabindex="%tabIndex%">'.$strInstallListTypeDb.'</select>'
                                     ),
                                array('label' => _PLOOPI_INSTALL_DB_SERVER,
                                      'input' => '<input name="db_server" id="db_server" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<DB_SERVER>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_DB_SERVER_JS).'\',form.db_server,\'string\')'
                                     ),
                                array('label' => _PLOOPI_INSTALL_DB_LOGIN,
                                      'input' => '<input name="db_login" id="db_login" type="text" tabindex="%tabIndex%" value="'.$_SESSION['install']['<DB_LOGIN>'].'"/>',
                                      'js'   => 'ploopi_validatefield(\''.addslashes(_PLOOPI_INSTALL_DB_LOGIN_JS).'\',form.db_login,\'string\')'
                                     ),
                                array('label' => _PLOOPI_INSTALL_DB_PWD,
                                      'input' => '<input name="db_pwd" id="db_pwd" type="password" tabindex="%tabIndex%" value="'.$_SESSION['install']['<DB_PASSWORD>'].'"/>'
                                     )
                              )
                  );

  $booFindDbListe = false;
  // test type database connection and get the list of database
  $strInstallSelected = ($_SESSION['install']['<DB_DATABASE>'] == '') ? 'selected' : '';
  if(file_exists('./config/install/install_'.$_SESSION['install']['<DB_TYPE>'].'.inc.php'))
  {
      include_once './config/install/install_'.$_SESSION['install']['<DB_TYPE>'].'.inc.php';
      //ALL DATABASE TESTS
      ploopi_Test_Database($arrInstallInfos,$intInstallInfos,$arrInstallRequestDB);
  }
  else
  {
    $arrInstallInfos[$intInstallInfos]['state'] = false;
    $arrInstallInfos[$intInstallInfos]['warn_replace'] = array(_PLOOPI_INSTALL_DB_ERR_TEST);
  }

  // test or re-test and stop at the courant stage if an error is detected
  if(ploopi_find_error_install($arrInstallInfos))
  {
    $_POST['stage']=$stage;
  }
  elseif($_POST['stage']>$stage)
  {
    unset($arrInstallInfos);
  }
} // end stage 4

/**
 * STAGE 5 = Final --------------------------------------------------
 */
$stage++;
if($_POST['stage']>=$stage)
{
  if(file_exists('./config/install/install_ploopi.inc.php'))
  {
    include_once './config/install/install_ploopi.inc.php';
    // Create Site
    if(ploopi_create_site($arrInstallInfos))
    {
      // End Message
      $objInstallTemplate->assign_block_vars("stage$stage",array('TEXT' => _PLOOPI_INSTALL_END_OK));
      unset($_SESSION['install']);
    }
  }
  else
  {
      // File Install_ploopi.inc.php not found !
      $arrInstallInfos[] = array('id' => 'div_err_install', 'state' => False, 'title' => '_PLOOPI_INSTALL_ERR_FILE_INSTALL');
  }
  // test or re-test and stop at the courant stage if an error is detected
  if(ploopi_find_error_install($arrInstallInfos))
  {
    $_POST['stage']=$stage;
  }
  elseif($_POST['stage']>$stage)
  {
    unset($arrInstallInfos);
  }
} // end stage 5

/****************************************************************************************/

/**
 * Generator for template =>
 *  management of infos, Suggest and warning
 *
 * !!! Don't touch !!! It is automatic '^_^
 */

// Global tag for template
$objInstallTemplate->assign_vars(array(
  'PAGE_TITLE'        => _PLOOPI_INSTALL_TITLE.' v'._PLOOPI_VERSION,
  'TEMPLATE_PATH'     => '../templates/install',
  'JS_MESS'           => _PLOOPI_INSTALL_JAVASCRIPT,
  'JS_ERROR'          => _PLOOPI_INSTALL_ERROR_JAVASCRIPT,
  'ICON_ERROR'        => _PLOOPI_INSTALL_ICO_ERROR,
  'STAGE'             => $_POST['stage']
));

// Creation of the menu
foreach($arrInstallAllStages as $strInstallNumStage => $strInstallLibStage)
{
  $strInstallClassStage = (intval($_POST['stage'])>=intval($strInstallNumStage)) ? 'menu_ok' : 'menu';
  $objInstallTemplate->assign_block_vars('menu',array(
    'LEVEL' => $strInstallNumStage,
    'NAME'  => $strInstallLibStage,
    'CLASS' => $strInstallClassStage
  ));
}
//init form control
$strInstallFormControlJS = '';

$booInstallError = false; // test if error detected in next tests
$intTabIndex = 0;
// transformation of the array $infos to template
if(isset($arrInstallInfos))
{
  foreach($arrInstallInfos as $arrInstallInfo)
  {
    // title
    $strInstallTplTitleTxt = (defined($arrInstallInfo['title'])) ? trim(constant($arrInstallInfo['title'])) : trim($arrInstallInfo['title']);
    // Info/Suggest Message
    $strInstallTplMessTxt = (defined($arrInstallInfo['title'].'_MESS')) ? trim(constant($arrInstallInfo['title'].'_MESS')) : '';
    // Error message
    if(!isset($arrInstallInfo['state'])) // NO Test
    {
      // no icon and no Warning message
      $strInstallTplIcon = '';
      $strInstallTplWarningTxt = '';
    }
    elseif($arrInstallInfo['state']==false) // state traitement
      {
        $booInstallError = true;
        // Icon Error
        $strInstallTplIcon = _PLOOPI_INSTALL_ICO_ERROR;
        // Warning message
        $strInstallTplWarningTxt = (defined($arrInstallInfo['title'].'_WARNING')) ? trim(constant($arrInstallInfo['title'].'_WARNING')) : '';
      }
      else
      {
        // Icon Valid
        $strInstallTplIcon = _PLOOPI_INSTALL_ICO_OK;
        // no Warning message
        $strInstallTplWarningTxt = '';
      }
    // Remplace
    if(isset($arrInstallInfo['title_replace']) && $strInstallTplTitleTxt !== '') $strInstallTplTitleTxt = ploopi_str_replace($strInstallTplTitleTxt,$arrInstallInfo['title_replace'],true);
    if(isset($arrInstallInfo['mess_replace']) && $strInstallTplMessTxt !== '')  $strInstallTplMessTxt = ploopi_str_replace($strInstallTplMessTxt,$arrInstallInfo['mess_replace'],true);
    if(isset($arrInstallInfo['warn_replace']) && $strInstallTplWarningTxt !== '')  $strInstallTplWarningTxt = ploopi_str_replace($strInstallTplWarningTxt,$arrInstallInfo['warn_replace'],true);

    // Class title
    $strInstallTplTitleClass = (isset($arrInstallInfo['state']) && $arrInstallInfo['state'] === false) ? 'info_error' : 'info_valid';
//    // Class Form
//    $strInstallTplFormClass = ($strInstallTplFormTxt != '') ? 'info_form' : 'info_noform';
    // Class Mess
    $strInstallTplMessClass = ($strInstallTplMessTxt != '') ? 'info_mess' : 'info_nomess';
    // Class Warning
    $strInstallTplWarningClass =  ($strInstallTplWarningTxt != '') ? 'info_warning' : 'info_nowarning';

    $strClassForm = 'noform';
    $strJsForm = '';
    $strInfoFormHidden = '';
    // form Exist - Hidden or Not ?
    if(isset($arrInstallInfo['form']))
    {
      $strClassForm = 'form';
      if(isset($arrInstallInfo['form_hidden']))
      {
        $strClassForm = ($arrInstallInfo['form_hidden']==true) ? 'hideform' : 'form';
        $strInstallTplTitleTxt .= '&nbsp;>&nbsp;<a class="more_param" href="javascript:changeHideView(\''.$arrInstallInfo['id'].'_form\');">'._PLOOPI_INSTALL_MORE_PARAM.'</a>';
      }
    }
    // Set Template
    $objInstallTemplate->assign_block_vars('infos',array(
        'ID'              => $arrInstallInfo['id'],
        'TITLE'           => $strInstallTplTitleTxt,
        'CLASS_TITLE'     => $strInstallTplTitleClass,
        'MESS'            => $strInstallTplMessTxt,
        'CLASS_MESS'      => $strInstallTplMessClass,
        'WARNING'         => $strInstallTplWarningTxt,
        'CLASS_WARNING'   => $strInstallTplWarningClass,
        'ID_FORM'         => $arrInstallInfo['id'].'_form',
        'CLASS_FORM'      => $strClassForm
    ));
    // Add Form to template
    if(isset($arrInstallInfo['form']))
    {
      foreach($arrInstallInfo['form'] as $arrInstallForm)
      {
        $strInstallFormLibClass  = 'label';
        $strInstallFormFieldClass = 'field';
        if(isset($arrInstallForm['js']) && trim($arrInstallForm['js']) != '')
        {
          $strInstallFormControlJS .= 'if ('.$arrInstallForm['js'].')'."\r\n";
          $strInstallFormLibClass = 'label_must';
          $strInstallFormFieldClass = 'field_must';
        }
        $intTabIndex++;
        $arrInstallForm['input'] = str_replace('%tabIndex%',$intTabIndex,$arrInstallForm['input']);
        $objInstallTemplate->assign_block_vars('infos.form_install', array(
                'LABEL'        => $arrInstallForm['label'],
                'CLASS_LABEL'  => $strInstallFormLibClass,
                'FIELD'        => $arrInstallForm['input'],
                'CLASS_FIELD'  => $strInstallFormFieldClass
              ));
      }
    }

    // State Icon
    if($strInstallTplIcon !== '') $objInstallTemplate->assign_block_vars('infos.state_icon', array('ICON' => $strInstallTplIcon));
    // Icon for link to url "more info"
    if(defined($arrInstallInfo['title'].'_URL_INFO')) $objInstallTemplate->assign_block_vars('infos.url_info', array('URL' => constant($arrInstallInfo['title'].'_URL_INFO'), 'URL_ICON' => _PLOOPI_INSTALL_URL_ICO));

  }
}

// Add javascript for control form
if($strInstallFormControlJS !== '')
{
  $objInstallTemplate->assign_vars(array('ADD_VALIDATEFIELD'  => 'javascript:return form_validate(this);',
                               'ADDITIONAL_JAVASCRIPT_CTRL'   => 'function form_validate(form) {'."\r\n".$strInstallFormControlJS.' return(true);'."\r\n".'return(false);'."\r\n".'}',
                               'ADD_MESS_FIELD_MUST'          => _PLOOPI_INSTALL_FIELD_MUST
                              ));
}
else
{
  $objInstallTemplate->assign_var('ADD_VALIDATEFIELD','javascript:return true;');
}

if($_POST['stage'] < count($arrInstallAllStages))
{
  $intTabINdex = 0;

  /**
   *  Management of buttons next, previous and refresh
   */
  // Next or End ?
  $strLabelButton = ($_POST['stage']<count($arrInstallAllStages)-1) ? _PLOOPI_INSTALL_NEXT_BUTTON : _PLOOPI_INSTALL_FINISH_BUTTON;
  // Button Enable or disable
  $strDisableButton = ($booInstallError == false && $booInstallJamButtonNext == false) ? '' : 'disabled="true"';
  $intTabINdex++;
  $objInstallTemplate->assign_block_vars('next_button',array('NEXT_BUTTON' => $strLabelButton, 'NEXT_BUTTON_DISABLE' => $strDisableButton, 'TABINDEX' => $intTabIndex));

  // If error(s) found or button refresh forced or template contain a form => add button refresh
  if($booInstallError || $booInstallAddButtonRefresh || $strInstallFormControlJS !== '')
  {
    $intTabINdex++;
    $objInstallTemplate->assign_block_vars('refresh_button',array('REFRESH_BUTTON' => _PLOOPI_INSTALL_REFRESH_BUTTON, 'TABINDEX' => $intTabIndex));
  }
  $intTabINdex++;
  if($_POST['stage']>1) $objInstallTemplate->assign_block_vars('prec_button',array('PREC_BUTTON' => _PLOOPI_INSTALL_PREC_BUTTON, 'TABINDEX' => $intTabIndex));
}
// For debug use it
//$template->assign_block_vars('debug',array('INFO' => ''));

$objInstallTemplate->pparse('install');

ob_end_flush();

//echo '<div style="clear:both;background-color:#ffffff;">';
//if(isset($_POST)) echo '$_POST';ploopi_print_r($_POST);
//if(isset($arrInstallInfos)) echo '$infos';ploopi_print_r($arrInstallInfos);
//if(isset($_SESSION)) echo '$_SESSION';ploopi_print_r($_SESSION);
//echo '</div>';

?>
