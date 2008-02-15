<?php
//Installation
define ('_PLOOPI_INSTALL_TITLE',            'Installation PLOOPI');

define ('_PLOOPI_INSTALL_TEXT',             'Bienvenu dans l\'installation de PLOOPI...');

define ('_PLOOPI_INSTALL_YES',              'oui');
define ('_PLOOPI_INSTALL_NO',               'non');

// Global
define ('_PLOOPI_INSTALL_REQUIRED',         'Minimum requis : v.');
define ('_PLOOPI_INSTALL_INSTALLED',        'install� : v.');
define ('_PLOOPI_INSTALL_JAVASCRIPT',       'Contr�le activation JavaScript');
define ('_PLOOPI_INSTALL_ERROR_JAVASCRIPT', 'Ploopi n�cessite l\'activation de Javascript');
define ('_PLOOPI_INSTALL_MORE_PARAM',       'Param�trage avanc� - cliquez ici.');

// Menu
// define ('_PLOOPI_INSTALL_LANGUAGE_AND_CTRL','S�lection du langage et contr�le des minimums requis');
define ('_PLOOPI_INSTALL_LANGUAGE_AND_CTRL','Contr�le des minimums requis');
define ('_PLOOPI_INSTALL_PARAM_INSTALL',    'Param�trages de l\'installation');
define ('_PLOOPI_INSTALL_PARAM_DB',         'Param�trage de la Base de donn�s');
define ('_PLOOPI_INSTALL_END',              'Installation Termin�');

// Button
define ('_PLOOPI_INSTALL_NEXT_BUTTON',      'Etape suivante >>');
define ('_PLOOPI_INSTALL_PREC_BUTTON',      '<< Etape pr�c�dente');
define ('_PLOOPI_INSTALL_REFRESH_BUTTON',   'Appliquer');
define ('_PLOOPI_INSTALL_FINISH_BUTTON',    'Terminer');

// Icon 
define ('_PLOOPI_INSTALL_URL_ICO',          '/gfx/web.png');
define ('_PLOOPI_INSTALL_ICO_OK',           '/gfx/p_green.png');
define ('_PLOOPI_INSTALL_ICO_ERROR',        '/gfx/p_red.png');

// Form message
define ('_PLOOPI_INSTALL_FIELD_MUST',       '<sup>* </sup>Champs obligatoires');

/*********
* Stage 1
*********/
/**
* Test Sample (only the first line is obligatory)
* define ('_PLOOPI_INSTALL_MYTEST',         'Ecriture dans le r�pertoire "data"');
* define ('_PLOOPI_INSTALL_MYTEST_MESS',    'Le r�pertoire data contiendra tous vos fichiers (hors base de donn�es). Il est donc fortement conseill� de localiser "data" hors de ploopi et sur un disque s�curis� (raid, sauvegardes r�guli�res,..)');
* define ('_PLOOPI_INSTALL_MYTEST_WARNING', 'Vous devez donner �apache les droits en �criture sur le r�pertoire "./data"');
* define ('_PLOOPI_INSTALL_MYTEST_URL_INFO','http://www.wikipedia.com');
*/

define ('_PLOOPI_INSTALL_CHOOSE_LANGUAGE',       'S�lectionner le language d\'installation');

define ('_PLOOPI_INSTALL_APACHE',                'Contr�le de version -> Serveur HTTPD APACHE');
define ('_PLOOPI_INSTALL_APACHE_MESS',           '%1s / %2s');
define ('_PLOOPI_INSTALL_APACHE_URL_INFO',       'http://httpd.apache.org/');

define ('_PLOOPI_INSTALL_PHP',                   'Contr�le de version -> Moteur PHP');
define ('_PLOOPI_INSTALL_PHP_MESS',              '%1s / %2s<ul><li>magic_quotes_gpc: %3s</li><li>memory_limit: %4s</li><li>post_max_size: %5s</li><li>upload_max_filesize: %6s</li></ul>');
define ('_PLOOPI_INSTALL_PHP_URL_INFO',          'http://fr.php.net/');

define ('_PLOOPI_INSTALL_STEM',                  'Contr�le de librairie PHP : STEM (PECL)');
define ('_PLOOPI_INSTALL_STEM_URL_INFO',         'http://pecl.php.net/package/stem');

define ('_PLOOPI_INSTALL_GD',                    'Contr�le de librairie PHP : GD');
define ('_PLOOPI_INSTALL_GD_URL_INFO',           'http://www.libgd.org/Main_Page');

define ('_PLOOPI_INSTALL_MCRYPT',                'Contr�le de librairie PHP : MCRYPT');
define ('_PLOOPI_INSTALL_MCRYPT_URL_INFO',       'http://mcrypt.sourceforge.net/');

define ('_PLOOPI_INSTALL_PDO',                   'Contr�le de librairie PHP : PDO');
define ('_PLOOPI_INSTALL_PDO_URL_INFO',          'http://fr.php.net/pdo/');

define ('_PLOOPI_INSTALL_PEAR',                  'Contr�le de librairie PHP : PEAR');
define ('_PLOOPI_INSTALL_PEAR_URL_INFO',         'http://pear.php.net/manual/fr');
define ('_PLOOPI_INSTALL_SELECT_PEAR',           '<sup>* </sup>R�pertoire d\'installation PEAR');
define ('_PLOOPI_INSTALL_SELECT_PEAR_JS',        'R�pertoire d\'installation PEAR');

define ('_PLOOPI_INSTALL_PEAR_INFO',                 '---- Contr�le du package PEAR : PEAR_Info');
// define ('_PLOOPI_INSTALL_PEAR_INFO_URL_INFO',        'http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_CACHE_LITE',           '---- Contr�le du package PEAR : CACHE_Lite');
// define ('_PLOOPI_INSTALL_PEAR_CACHE_LITE_URL_INFO',  'http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_HTTP_REQUEST',         '---- Contr�le du package PEAR : HTTP_Request');
// define ('_PLOOPI_INSTALL_PEAR_HTTP_REQUEST_URL_INFO','http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_XML_FEED_PARSER',          '---- Contr�le du package PEAR : XML_Feed_Parser');
// define ('_PLOOPI_INSTALL_PEAR_XML_FEED_PARSER_URL_INFO', 'http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_XML_BEAUTIFIER',           '---- Contr�le du package PEAR : XML_Beautifier');
// define ('_PLOOPI_INSTALL_PEAR_XML_BEAUTIFIER_URL_INFO',  'http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_OLE',                      '---- Contr�le du package PEAR : OLE');
// define ('_PLOOPI_INSTALL_PEAR_OLE_URL_INFO',             'http://www.ploopi.org/trac/wiki/PloopiInstall');
define ('_PLOOPI_INSTALL_PEAR_SPREADSHEET_EXCEL_WRITER', '---- Contr�le du package PEAR : Spreadsheet_Excel_Writer');
// define ('_PLOOPI_INSTALL_PEAR_SPREADSHEET_EXCEL_WRITER_URL_INFO','http://www.ploopi.org/trac/wiki/PloopiInstall');

/*********
* Stage 2
*********/

define ('_PLOOPI_INSTALL_CONFIG_WRITE',          'Ecriture dans le r�pertoire "config"');
define ('_PLOOPI_INSTALL_CONFIG_WRITE_WARNING',  'Vous devez donner �apache les droits en �criture sur le r�pertoire "./config"');

define ('_PLOOPI_INSTALL_CONFIG_MODEL',          'Contr�le du fichier mod�le de configuration');
define ('_PLOOPI_INSTALL_CONFIG_MODEL_WARNING',  'Le fichier config.php.model est manquant ou ne peut �tre lu.');

// Data Directory
define ('_PLOOPI_INSTALL_SELECT_DATA',       '<sup>* </sup>R�pertoire d\'enregistrement des fichiers:');
define ('_PLOOPI_INSTALL_SELECT_DATA_JS',    'R�pertoire des donn�es');

define ('_PLOOPI_INSTALL_DATA_EXIST',        'Ecriture des donn�es dans le r�pertoire %1s');
define ('_PLOOPI_INSTALL_DATA_EXIST_MESS',   'Le r�pertoire %1s contiendra tous vos fichiers (hors base de donn�es). Il est donc fortement conseill� de localiser ce r�pertoire hors de ploopi et sur un disque s�curis� (raid, sauvegardes r�guli�res,..)');
define ('_PLOOPI_INSTALL_DATA_EXIST_WARNING','Le r�pertoire %1s n\'existe pas ou n\'est pas un r�pertoire');

define ('_PLOOPI_INSTALL_DATA_WRITE',        'Ecriture des donn�es dans le r�pertoire %1s');
define ('_PLOOPI_INSTALL_DATA_WRITE_MESS',   'Le r�pertoire %1s contiendra tous vos fichiers (hors base de donn�es). Il est donc fortement conseill� de localiser ce r�pertoire hors de ploopi et sur un disque s�curis� (raid, sauvegardes r�guli�res,..)%2s');
define ('_PLOOPI_INSTALL_DATA_WRITE_WARNING','Vous devez donner �apache les droits en �criture sur le r�pertoire %1s');
define ('_PLOOPI_INSTALL_SELECT_DATA_INFO_PLACE', '<br/>Ce r�pertoire dispose de ');

define ('_PLOOPI_INSTALL_PARAM_PLOOPI',      'Param�trage � PLOOPI �');

define ('_PLOOPI_INSTALL_SITE_NAME',         '<sup>* </sup>Nom du site:');
define ('_PLOOPI_INSTALL_SITE_NAME_JS',      'Nom du site');
define ('_PLOOPI_INSTALL_ADMIN_LOGIN',       '<sup>* </sup>Login Administrateur:');
define ('_PLOOPI_INSTALL_ADMIN_LOGIN_JS',    'Login Administrateur');
define ('_PLOOPI_INSTALL_ADMIN_PWD',         '<sup>* </sup>Mot de Passe Administrateur:');
define ('_PLOOPI_INSTALL_ADMIN_PWD_JS',      'Mot de Passe Administrateur');
define ('_PLOOPI_INSTALL_SECRET_SENTENCE',   '<sup>* </sup>Phrase Secr�te:');
define ('_PLOOPI_INSTALL_SECRET_SENTENCE_JS','Phrase Secr�te');
define ('_PLOOPI_INSTALL_ADMIN_MAIL',        'M�l Administrateur:');
define ('_PLOOPI_INSTALL_ADMIN_MAIL_JS',     'M�l Administrateur');
define ('_PLOOPI_INSTALL_URL_ENCODE',        'Encodage des URL visibles:');
define ('_PLOOPI_INSTALL_SESSION_BDD',       'Stocker les Sessions en BDD:');

define ('_PLOOPI_INSTALL_FRONT_OFFICE',      'Param�trage � FrontOffice �');
define ('_PLOOPI_INSTALL_FRONT_ACTIVE',      'Activation:');
define ('_PLOOPI_INSTALL_FRONT_REWRITE',     'R��criture d\'URL:');

define ('_PLOOPI_INSTALL_WEB_CONNECT',       'Connexion � internet');
define ('_PLOOPI_INSTALL_WEB_CONNECT_MESS',  'Certains modules de PLOOPI ont besoin de se connecter � internet. Ce test vous indique si le serveur arrive � ouvrir une connexion internet.');

define ('_PLOOPI_INSTALL_PROXY_HOST',        'Serveur - Proxy');
define ('_PLOOPI_INSTALL_PROXY_PORT',        'Port - Proxy');
define ('_PLOOPI_INSTALL_PROXY_USER',        'Utilisateur - Proxy');
define ('_PLOOPI_INSTALL_PROXY_PASS',        'Mot de passe - Proxy');


/*********
* Stage 3
*********/
define ('_PLOOPI_INSTALL_DATA_BASE',         'Param�trage de la base de donn�e %1s');
define ('_PLOOPI_INSTALL_DATA_BASE_MESS',    '%1s / %2s');
define ('_PLOOPI_INSTALL_DATA_BASE_WARNING', '%1s');
define ('_PLOOPI_INSTALL_DB_TYPE',           '<sup>* </sup>Type de base:');
define ('_PLOOPI_INSTALL_DB_TYPE_JS',        'Type de base');
define ('_PLOOPI_INSTALL_DB_SERVER',         '<sup>* </sup>Serveur:');
define ('_PLOOPI_INSTALL_DB_SERVER_JS',      'Serveur');
define ('_PLOOPI_INSTALL_DB_LOGIN',          '<sup>* </sup>Utilisateur:');
define ('_PLOOPI_INSTALL_DB_LOGIN_JS',       'Utilisateur');
define ('_PLOOPI_INSTALL_DB_PWD',            'Mot de passe:');
define ('_PLOOPI_INSTALL_DB_DATABASE_NAME',  '<sup>* </sup>Nom de la base � utiliser:');
define ('_PLOOPI_INSTALL_DB_DATABASE_NAME_JS',      'Nom de la base � utiliser');
define ('_PLOOPI_INSTALL_DB_DATABASE_SELECT',       'Ou S�lection d\'une base existante:');
define ('_PLOOPI_INSTALL_DB_DATABASE_SELECT_NEW',   '-- Nouvelle Base --');

define ('_PLOOPI_INSTALL_DB_ERR_CONNECT',    'Impossible de se connecter � la base');
define ('_PLOOPI_INSTALL_DB_ERR_TEST',       'Impossible de r�aliser les tests n�cessaires');
define ('_PLOOPI_INSTALL_DB_ERR_NAME_DB',    'Le nom de la base de donn�e doit �tre renseign�');

define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_DB',        'Cr�ation de la base de donn�es \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_DB_WARNING','Pour cr�er la nouvelle base de donn�e \'%1s\', le compte \'%2s\' doit avoir des droits de \'CREATE DATABASE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_USE',              'Utilisation de la base de donn�e \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_USE_WARNING',      'Impossible d\'utiliser la base de donn�e \'%1s\'');
define ('_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST',     'Attention %1s contient des donn�es d\'un autre site ploopi.');
define ('_PLOOPI_INSTALL_DATA_BASE_PLOOPI_EXIST_FIELD', 'Ecraser la base existante ?');

define ('_PLOOPI_INSTALL_DATA_BASE_CREATE',           'Cr�er une table dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_CREATE_WARNING',   'Pour ajouter des table � \'%1s\', le compte \'%2s\' doit avoir le droit de \'CREATE TABLE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_INSERT',           'Ajouter des donn�es dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_INSERT_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_SELECT',           'Rechercher des donn�es dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_SELECT_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE',           'Modifier des donn�es dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE_WARNING',   '');
define ('_PLOOPI_INSTALL_DATA_BASE_DELETE',           'Supprimer des donn�es dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_UPDATE_WARNING',   '');

define ('_PLOOPI_INSTALL_DATA_BASE_DROP',             'Supprimer une table dans \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_DROP_WARNING',     'Pour supprimer des tables dans \'%1s\', le compte \'%2s\' doit avoir le droit de \'DROP TABLE\'.');
define ('_PLOOPI_INSTALL_DATA_BASE_DROP_DB',          'Test de suppression de la base \'%1s\'');
//define ('_PLOOPI_INSTALL_DATA_BASE_DROP_DB_WARNING',  '');

define ('_PLOOPI_INSTALL_ERR_FILE_INSTALL',           'Installation de Ploopi');
define ('_PLOOPI_INSTALL_ERR_FILE_INSTALL_WARNING',   'Installation impossible, fichier d\'installation manquant');
define ('_PLOOPI_INSTALL_ERR_INSTALL',                'Installation de Ploopi');
define ('_PLOOPI_INSTALL_ERR_INSTALL_WARNING',        'Erreur pendant le processus d\'installation ploopi');

define ('_PLOOPI_INSTALL_END_OK', '<b>FELICITATION</b><br>'
                                   .'<br>L\'installation est maintenant termin�e.'
					               .'<br>'
					               .'<br><b>Vous devez maintenant supprimer (ou renommer) le fichier ./config/install.php</b>'
					               .'<br>'
					               .'<br>vous pouvez vous connecter en utilisant votre compte "Administrateur"'
					               .'<br>'
					               .'<br><a href="../index.php" class="link">Continuer</a>');
?>