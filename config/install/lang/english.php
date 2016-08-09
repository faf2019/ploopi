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
 * Language file 'English' used during the installation procedure of Ploopi.
 *
 * @package ploopi
 * @subpackage install
 * @copyright Ovensia, Hexad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Defining constants
 */

define ('_PLOOPI_INSTALL_TEXT',             'Welcome in the installation of PLOOPI...');

define ('_PLOOPI_INSTALL_WELCOME_TEXT',     'Welcome to installing of Ploopi...');

define ('_PLOOPI_INSTALL_YES',              'yes');
define ('_PLOOPI_INSTALL_NO',               'no');

// Global
define ('_PLOOPI_INSTALL_REQUIRED',         'Minimum requirement : v.');
define ('_PLOOPI_INSTALL_INSTALLED',        'Installed : v.');
define ('_PLOOPI_INSTALL_JAVASCRIPT',       'Control activation JavaScript');
define ('_PLOOPI_INSTALL_ERROR_JAVASCRIPT', 'Ploopi requires JavaScript');
define ('_PLOOPI_INSTALL_MORE_PARAM',       'Advanced Settings - click here.');

// Menu
// define ('_PLOOPI_INSTALL_LANGUAGE_AND_CTRL','Sélection du langage et contrôle des minimums requis');
define ('_PLOOPI_INSTALL_LICENSE',                 'License');
define ('_PLOOPI_INSTALL_LANGUAGE_AND_FIRST_CTRL', 'Control of minimums');
define ('_PLOOPI_INSTALL_PARAM_INSTALL',           'Setting the installation');
define ('_PLOOPI_INSTALL_PARAM_DB',                'Setting the Database');
define ('_PLOOPI_INSTALL_END',                     'Installation Complete');

// Button
define ('_PLOOPI_INSTALL_NEXT_BUTTON',      'Next Step >>');
define ('_PLOOPI_INSTALL_PREC_BUTTON',      '<< Previous step');
define ('_PLOOPI_INSTALL_REFRESH_BUTTON',   'Apply');
define ('_PLOOPI_INSTALL_FINISH_BUTTON',    'Finish');

// Icon
define ('_PLOOPI_INSTALL_URL_ICO',          '/gfx/web.png');
define ('_PLOOPI_INSTALL_ICO_OK',           '/gfx/p_green.png');
define ('_PLOOPI_INSTALL_ICO_ERROR',        '/gfx/p_red.png');

// Form message
define ('_PLOOPI_INSTALL_FIELD_MUST',       '<sup>* </sup>Required field');

/*********
* Stage 1
*********/
/**
* license GPL2
*/
define ('_PLOOPI_INSTALL_LICENSE_TXT',      '<br/><br/><center><h2><a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">Ploop is licensed under GPL2<br/>Click to read the license online</a></h2></center>');
define ('_PLOOPI_INSTALL_LICENSE_ACCEPT',   'I accept the terms of the license');

/*********
* Stage 2
*********/
/**
* Test Sample (only the first line is obligatory)
* define ('_PLOOPI_INSTALL_MYTEST',         'Ecriture dans le répertoire "data"');
* define ('_PLOOPI_INSTALL_MYTEST_MESS',    'Le répertoire data contiendra tous vos fichiers (hors base de données). Il est donc fortement conseillé de localiser "data" hors de ploopi et sur un disque sécurisé (raid, sauvegardes régulières,..)');
* define ('_PLOOPI_INSTALL_MYTEST_WARNING', 'Vous devez donner à apache les droits en écriture sur le répertoire "./data"');
* define ('_PLOOPI_INSTALL_MYTEST_URL_INFO','http://www.wikipedia.com');
*/

define ('_PLOOPI_INSTALL_CHOOSE_LANGUAGE',       'Select the installation language');
define ('_PLOOPI_INSTALL_APACHE',                'Version Control -> Apache HTTPD server');
define ('_PLOOPI_INSTALL_APACHE_MESS',           '%1s / %2s');
define ('_PLOOPI_INSTALL_APACHE_URL_INFO',       'http://httpd.apache.org/');

define ('_PLOOPI_INSTALL_PHP',                   'Version Control -> PHP Engine');
define ('_PLOOPI_INSTALL_PHP_MESS',              '%1s / %2s<ul><li>magic_quotes_gpc: %3s</li><li>memory_limit: %4s</li><li>post_max_size: %5s</li><li>upload_max_filesize: %6s</li></ul>');
define ('_PLOOPI_INSTALL_PHP_URL_INFO',          'http://fr.php.net/');

define ('_PLOOPI_INSTALL_STEM',                  'Control library PHP : STEM (PECL)');
define ('_PLOOPI_INSTALL_STEM_URL_INFO',         'http://pecl.php.net/package/stem');

define ('_PLOOPI_INSTALL_GD',                    'Control library PHP : GD');
define ('_PLOOPI_INSTALL_GD_URL_INFO',           'http://www.libgd.org/Main_Page');

define ('_PLOOPI_INSTALL_MCRYPT',                'Control library PHP : MCRYPT');
define ('_PLOOPI_INSTALL_MCRYPT_URL_INFO',       'http://mcrypt.sourceforge.net/');

define ('_PLOOPI_INSTALL_PDO',                   'Control library PHP : PDO');
define ('_PLOOPI_INSTALL_PDO_URL_INFO',          'http://fr.php.net/pdo/');

define ('_PLOOPI_INSTALL_PEAR',                  'Control library PHP : PEAR');
define ('_PLOOPI_INSTALL_PEAR_URL_INFO',         'http://pear.php.net/manual/fr');
define ('_PLOOPI_INSTALL_SELECT_PEAR',           '<sup>* </sup>PEAR installation directory');
define ('_PLOOPI_INSTALL_SELECT_PEAR_JS',        'PEAR installation directory');

define ('_PLOOPI_INSTALL_PEAR_INFO',                      '---- Control package PEAR : PEAR_Info');
define ('_PLOOPI_INSTALL_PEAR_CACHE_LITE',                '---- Control package PEAR : CACHE_Lite');
define ('_PLOOPI_INSTALL_PEAR_HTTP_REQUEST',              '---- Control package PEAR : HTTP_Request2');
define ('_PLOOPI_INSTALL_PEAR_TEXT_HIGHLIGHTER',          '---- Control package PEAR : Text_Highlighter');
define ('_PLOOPI_INSTALL_PEAR_HORDE_TEXT_DIFF',           '---- Control package PEAR : Horde_Text_Diff');
define ('_PLOOPI_INSTALL_PEAR_XML_FEED_PARSER',           '---- Control package PEAR : XML_Feed_Parser');
define ('_PLOOPI_INSTALL_PEAR_XML_SERIALIZER',            '---- Control package PEAR : XML_Serializer');
define ('_PLOOPI_INSTALL_PEAR_OLE',                       '---- Control package PEAR : OLE');
define ('_PLOOPI_INSTALL_PEAR_SPREADSHEET_EXCEL_WRITER',  '---- Control package PEAR : Spreadsheet_Excel_Writer');
define ('_PLOOPI_INSTALL_PEAR_NET_USERAGENT_DETECT',      '---- Control package PEAR : Net_UserAgent_Detect');

/*********
* Stage 3
*********/

define ('_PLOOPI_INSTALL_CONFIG_WRITE',          'Writing in the "config" directory');
define ('_PLOOPI_INSTALL_CONFIG_WRITE_WARNING',  'You need to give apache write permission on the directory "./config"');

define ('_PLOOPI_INSTALL_CONFIG_MODEL',          'Control Model configuration file');
define ('_PLOOPI_INSTALL_CONFIG_MODEL_WARNING',  'config.php.model file is missing or can not be read.');

// Data Directory
define ('_PLOOPI_INSTALL_SELECT_DATA',       '<sup>* </sup>Directory for saving files:');
define ('_PLOOPI_INSTALL_SELECT_DATA_JS',    'Directory Data');

define ('_PLOOPI_INSTALL_DATA_EXIST',        'Writing data in the %1s directory');
define ('_PLOOPI_INSTALL_DATA_EXIST_MESS',   '%1s directory will contain all your files (not database). It is therefore strongly advised to locate this folder outside Ploop and secure disk (raid, regular backups, ..)');
define ('_PLOOPI_INSTALL_DATA_EXIST_WARNING','%1s directory does not exist or is not a directory');

define ('_PLOOPI_INSTALL_DATA_WRITE',        'Writing data in the %1s directory');
define ('_PLOOPI_INSTALL_DATA_WRITE_MESS',   '%1s directory will contain all your files (not database). It is therefore strongly advised to locate this folder outside Ploop and secure disk (raid, regular backups, ..)%2s');
define ('_PLOOPI_INSTALL_DATA_WRITE_WARNING','You need to give apache write permission on the directory %1s');
define ('_PLOOPI_INSTALL_SELECT_DATA_INFO_PLACE', '<br/>This directory has ');

// TMP Directory
define ('_PLOOPI_INSTALL_SELECT_TMP',       '<sup>* </sup>Temporary directory :');
define ('_PLOOPI_INSTALL_SELECT_TMP_JS',    'Temporary directory');

define ('_PLOOPI_INSTALL_TMP_EXIST',        'Writing data in the %1s directory');
define ('_PLOOPI_INSTALL_TMP_EXIST_WARNING','%1s directory does not exist or is not a directory');

define ('_PLOOPI_INSTALL_TMP_WRITE',        'Writing data in the %1s directory');
define ('_PLOOPI_INSTALL_TMP_WRITE_MESS',   'This directory has %1s');
define ('_PLOOPI_INSTALL_TMP_WRITE_WARNING','You need to give apache write permission on the directory %1s');

// CGI SECTION
define ('_PLOOPI_INSTALL_CGI_NO_EXIST',         'Using CGI scripts');
define ('_PLOOPI_INSTALL_CGI_NO_EXIST_WARNING', '%1s directory does not exist or is not a directory');

define ('_PLOOPI_INSTALL_CGI_EXIST',            'Using CGI scripts');
define ('_PLOOPI_INSTALL_CGI_EXIST_WARNING',    'You need to give apache write permission on the directory %1s');

define ('_PLOOPI_INSTALL_CGI_ACTIVE',        'Enabling CGI');

define ('_PLOOPI_INSTALL_CGI_PATH',          '<sup>* </sup>CGI directory (default must be ./cgi) :');
define ('_PLOOPI_INSTALL_CGI_PATH_JS',       'CGI directory');

define ('_PLOOPI_INSTALL_PARAM_PLOOPI',      '« PLOOPI » setting');

define ('_PLOOPI_INSTALL_URL_BASE',          '<sup>* </sup>Site address:');
define ('_PLOOPI_INSTALL_URL_BASE_JS',       'Site address');
define ('_PLOOPI_INSTALL_SITE_NAME',         '<sup>* </sup>site name:');
define ('_PLOOPI_INSTALL_SITE_NAME_JS',      'Site name');
define ('_PLOOPI_INSTALL_ADMIN_LOGIN',       '<sup>* </sup>Administrator Login:');
define ('_PLOOPI_INSTALL_ADMIN_LOGIN_JS',    'Administrator Login');
define ('_PLOOPI_INSTALL_ADMIN_PWD',         '<sup>* </sup>Administrator Password:');
define ('_PLOOPI_INSTALL_ADMIN_PWD_JS',      'Administrator Password');
define ('_PLOOPI_INSTALL_SECRET_SENTENCE',   '<sup>* </sup>Passphrase:');
define ('_PLOOPI_INSTALL_SECRET_SENTENCE_JS','Passphrase');
define ('_PLOOPI_INSTALL_ADMIN_MAIL',        'Email administrator:');
define ('_PLOOPI_INSTALL_ADMIN_MAIL_JS',     'Email administrator');
define ('_PLOOPI_INSTALL_SYS_MAIL',          'Email System:');
define ('_PLOOPI_INSTALL_SYS_MAIL_JS',       'Email System');
define ('_PLOOPI_INSTALL_URL_ENCODE',        'Encoding URL visible:');
define ('_PLOOPI_INSTALL_SESSION_HANDLER',   'Session handler:');

define ('_PLOOPI_INSTALL_FRONT_OFFICE',      '« FrontOffice » setting');
define ('_PLOOPI_INSTALL_FRONT_ACTIVE',      'Activation:');
define ('_PLOOPI_INSTALL_FRONT_REWRITE',     'URL Rewriting:');

define ('_PLOOPI_INSTALL_WEB_CONNECT',       'Internet connection');
define ('_PLOOPI_INSTALL_WEB_CONNECT_MESS',  'Some modules Ploopi need to connect to the internet. This test tells you if the server arrives to open an internet connection.');

define ('_PLOOPI_INSTALL_PROXY_HOST',        'Serveur - Proxy');
define ('_PLOOPI_INSTALL_PROXY_PORT',        'Port - Proxy');
define ('_PLOOPI_INSTALL_PROXY_USER',        'User - Proxy');
define ('_PLOOPI_INSTALL_PROXY_PASS',        'Password - Proxy');

/*********
* Stage 4
*********/
define ('_PLOOPI_INSTALL_DATA_BASE',         'Setting up the database');
define ('_PLOOPI_INSTALL_DATA_BASE_MESS',    '%1s / %2s');
define ('_PLOOPI_INSTALL_DATA_BASE_WARNING', '%1s');
define ('_PLOOPI_INSTALL_DB_TYPE',           '<sup>* </sup>Base Type:');
define ('_PLOOPI_INSTALL_DB_TYPE_JS',        'Base Type');
define ('_PLOOPI_INSTALL_DB_SERVER',         '<sup>* </sup>Server:');
define ('_PLOOPI_INSTALL_DB_SERVER_JS',      'Server');
define ('_PLOOPI_INSTALL_DB_LOGIN',          '<sup>* </sup>User:');
define ('_PLOOPI_INSTALL_DB_LOGIN_JS',       'User');
define ('_PLOOPI_INSTALL_DB_PWD',            'Password:');
define ('_PLOOPI_INSTALL_DB_DATABASE_NAME',  '<sup>* </sup>Name of the database to use:');
define ('_PLOOPI_INSTALL_DB_DATABASE_NAME_JS',      'Name of the database to use');
define ('_PLOOPI_INSTALL_DB_DATABASE_SELECT',       'Or Select an existing database:');
define ('_PLOOPI_INSTALL_DB_DATABASE_SELECT_NEW',   '-- New Base --');

define ('_PLOOPI_INSTALL_DB_ERR_CONNECT',    'Unable to connect to the database');
define ('_PLOOPI_INSTALL_DB_ERR_TEST',       'Unable to perform the necessary tests');
define ('_PLOOPI_INSTALL_DB_ERR_NAME_DB',    'The name of the database must be informed');

define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_DB',        'Creating the database \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_DB_WARNING','Pour créer la nouvelle base de donnée \'%1s\', le compte \'%2s\' doit avoir des droits de \'CREATE DATABASE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_USE',              'Using the database \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_USE_WARNING',      'Impossible d\'utiliser la base de donnée \'%1s\'');
define ('_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST',     'Attention %1s contains data from another site Ploopi.');
define ('_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST_FIELD', 'Overwrite the existing database ?');

define ('_PLOOPI_INSTALL_DATA_BASE_CREATE',           'Create a table in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_WARNING',   'Pour ajouter des table à \'%1s\', le compte \'%2s\' doit avoir le droit de \'CREATE TABLE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_INSERT',           'Add data in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_INSERT_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_SELECT',           'Search data in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_SELECT_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE',           'Modify data in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_DELETE',           'Deleting data in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE_WARNING',   '');

define ('_PLOOPI_INSTALL_DATA_BASE_DROP',             'Delete a table in \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_DROP_WARNING',     'Pour supprimer des tables dans \'%1s\', le compte \'%2s\' doit avoir le droit de \'DROP TABLE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_DROP_DB',          'Suppression test base \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_DROP_DB_WARNING',  '');

define ('_PLOOPI_INSTALL_ERR_FILE_INSTALL',           'Installing Ploopi');
define ('_PLOOPI_INSTALL_ERR_FILE_INSTALL_WARNING',   'Installation impossible setup file missing');
define ('_PLOOPI_INSTALL_ERR_INSTALL',                'Installing Ploopi');
define ('_PLOOPI_INSTALL_ERR_INSTALL_WARNING',        'Error during the installation process Ploopi');

define ('_PLOOPI_INSTALL_END_OK', '<b>CONGRATULATIONS</b><br>'
                                   .'<br>The installation is now complete.'
                                   .'<br>'
                                   .'<br><b>You must now delete (or rename) the file ./config/install.php</b>'
                                   .'<br>'
                                   .'<br>you can log in using your account "Administrateur"'
                                   .'<br>'
                                   .'<br><a href="../index.php" class="link">Continue</a>');

?>
