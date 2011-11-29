
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `ploopi_annotation`;
CREATE TABLE `ploopi_annotation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext,
  `object_label` varchar(255) NOT NULL default '',
  `type_annotation` varchar(16) default NULL,
  `date_annotation` varchar(14) default NULL,
  `private` tinyint(1) unsigned NOT NULL default '1',
  `id_record` varchar(255) default NULL,
  `id_object` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) default NULL,
  `id_element` char(32) NOT NULL default '0',
  `id_module` int(10) unsigned default '0',
  `id_module_type` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `id_record` (`id_record`),
  KEY `id_object` (`id_object`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_annotation` WRITE;
/*!40000 ALTER TABLE `ploopi_annotation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_annotation` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_annotation_tag`;
CREATE TABLE `ploopi_annotation_tag` (
  `id_annotation` int(10) unsigned NOT NULL default '0',
  `id_tag` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_annotation`,`id_tag`),
  KEY `id_tag` (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_annotation_tag` WRITE;
/*!40000 ALTER TABLE `ploopi_annotation_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_annotation_tag` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_confirmation_code`;
CREATE TABLE `ploopi_confirmation_code` (
  `action` varchar(255) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `code` varchar(32) NOT NULL,
  PRIMARY KEY  (`action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_confirmation_code` WRITE;
/*!40000 ALTER TABLE `ploopi_confirmation_code` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_confirmation_code` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_captcha`;
CREATE TABLE `ploopi_captcha` (
`id` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`cptuse` INT(10) UNSIGNED NOT NULL ,
`codesound` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`code` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`time` INT(20) UNSIGNED NOT NULL ,
INDEX (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_captcha` WRITE;
/*!40000 ALTER TABLE `ploopi_captcha` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_captcha` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_connecteduser`;
CREATE TABLE `ploopi_connecteduser` (
  `sid` char(32) NOT NULL default '0',
  `ip` char(15) default NULL,
  `domain` varchar(255) NOT NULL,
  `user_id` int(10) unsigned default '0',
  `workspace_id` int(10) default NULL,
  `module_id` int(10) unsigned default '0',
  `timestp` bigint(14) default '0',
  PRIMARY KEY  (`sid`),
  KEY `workspace_id` (`workspace_id`),
  KEY `user_id` (`user_id`),
  KEY `module_id` (`module_id`),
  KEY `timestp` (`timestp`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_connecteduser` WRITE;
/*!40000 ALTER TABLE `ploopi_connecteduser` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_connecteduser` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_documents_ext`;
CREATE TABLE `ploopi_documents_ext` (
  `ext` varchar(10) NOT NULL default '',
  `filetype` varchar(16) default NULL,
  PRIMARY KEY  (`ext`),
  KEY `filetype` (`filetype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_documents_ext` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_ext` DISABLE KEYS */;
INSERT INTO `ploopi_documents_ext` VALUES ('odt','document'),('doc','document'),('xls','spreadsheet'),('mp3','audio'),('wav','audio'),('ogg','audio'),('jpg','image'),('jpeg','image'),('png','image'),('gif','image'),('psd','image'),('xcf','image'),('svg','image'),('pdf','document'),('avi','video'),('wmv','video'),('ogm','video'),('mpg','video'),('mpeg','video'),('zip','archive'),('tgz','archive'),('gz','archive'),('rar','archive'),('bz2','archive'),('ace','archive');
/*!40000 ALTER TABLE `ploopi_documents_ext` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_documents_file`;
CREATE TABLE `ploopi_documents_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) default NULL,
  `ref` varchar(255) NOT NULL,
  `timestp_file` bigint(14) unsigned NOT NULL,
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `extension` varchar(20) default NULL,
  `parents` varchar(255) default NULL,
  `content` longtext NOT NULL,
  `nbclick` int(10) unsigned default '0',
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `name` (`name`),
  KEY `id_folder` (`id_folder`),
  KEY `extension` (`extension`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_documents_file` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_documents_file` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_documents_folder`;
CREATE TABLE `ploopi_documents_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `parents` varchar(255) default '0',
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `nbelements` int(10) unsigned NOT NULL default '0',
  `system` tinyint(1) unsigned NOT NULL default '0',
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `id_folder` (`id_folder`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_documents_folder` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_documents_folder` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_group`;
CREATE TABLE `ploopi_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_group` int(10) unsigned default '0',
  `label` varchar(255) NOT NULL default '',
  `system` tinyint(1) unsigned NOT NULL default '0',
  `protected` tinyint(1) unsigned default '0',
  `parents` varchar(100) default NULL,
  `depth` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `shared` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_workspace` (`id_workspace`),
  KEY `shared` (`shared`),
  KEY `system` (`system`),
  KEY `protected` (`protected`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_group` WRITE;
/*!40000 ALTER TABLE `ploopi_group` DISABLE KEYS */;
INSERT INTO `ploopi_group` VALUES (1,0,'system',1,1,'0',1,0,0),(3,1,'Groupe Principal',0,1,'0;1',2,1,1);
/*!40000 ALTER TABLE `ploopi_group` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_group_user`;
CREATE TABLE `ploopi_group_user` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_group` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`,`id_user`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_group_user` WRITE;
/*!40000 ALTER TABLE `ploopi_group_user` DISABLE KEYS */;
INSERT INTO `ploopi_group_user` VALUES (2,3);
/*!40000 ALTER TABLE `ploopi_group_user` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_index_element`;
CREATE TABLE `ploopi_index_element` (
  `id` char(32) NOT NULL,
  `id_record` char(64) NOT NULL,
  `id_object` smallint(5) unsigned NOT NULL default '0',
  `label` char(128) NOT NULL,
  `timestp_create` bigint(14) unsigned NOT NULL default '0',
  `timestp_modify` bigint(14) unsigned NOT NULL default '0',
  `timestp_lastindex` bigint(14) unsigned NOT NULL default '0',
  `id_user` smallint(5) unsigned NOT NULL default '0',
  `id_workspace` smallint(5) unsigned NOT NULL default '0',
  `id_module` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_user` (`id_user`),
  KEY `id_record` (`id_record`),
  KEY `id_object` (`id_object`),
  KEY `timestp_create` (`timestp_create`),
  KEY `timestp_modify` (`timestp_modify`),
  KEY `timestp_lastindex` (`timestp_lastindex`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_index_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_element` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_index_keyword`;
CREATE TABLE `ploopi_index_keyword` (
  `id` char(32) NOT NULL,
  `keyword` char(20) character set latin1 collate latin1_bin NOT NULL,
  `twoletters` char(2) character set latin1 collate latin1_bin NOT NULL,
  `length` tinyint(2) unsigned NOT NULL default '0',
  `id_stem` char(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `twoletter` (`twoletters`),
  KEY `length` (`length`),
  KEY `id_stem` (`id_stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_index_keyword` WRITE;
/*!40000 ALTER TABLE `ploopi_index_keyword` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_keyword` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_index_keyword_element`;
CREATE TABLE `ploopi_index_keyword_element` (
  `id_keyword` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` mediumint(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_keyword`,`id_element`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_index_keyword_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_keyword_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_keyword_element` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_index_stem`;
CREATE TABLE `ploopi_index_stem` (
  `id` char(32) NOT NULL default '0',
  `stem` char(20) character set latin1 collate latin1_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `stem` (`stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_index_stem` WRITE;
/*!40000 ALTER TABLE `ploopi_index_stem` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_stem` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_index_stem_element`;
CREATE TABLE `ploopi_index_stem_element` (
  `id_stem` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` mediumint(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_stem`,`id_element`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_index_stem_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_stem_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_stem_element` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_log`;
CREATE TABLE `ploopi_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `request_method` varchar(255) default NULL,
  `query_string` varchar(255) default NULL,
  `remote_addr` varchar(64) default NULL,
  `remote_port` int(10) unsigned default NULL,
  `script_filename` varchar(255) default NULL,
  `path_translated` varchar(255) default NULL,
  `script_name` varchar(255) default NULL,
  `request_uri` varchar(255) default NULL,
  `ploopi_userid` int(10) unsigned NOT NULL default '0',
  `ploopi_workspaceid` int(10) unsigned NOT NULL default '0',
  `ploopi_moduleid` int(10) unsigned NOT NULL default '0',
  `browser` varchar(64) default NULL,
  `system` varchar(64) default NULL,
  `ts` bigint(14) unsigned NOT NULL default '0',
  `total_exec_time` int(10) unsigned default '0',
  `sql_exec_time` int(10) unsigned default '0',
  `sql_percent_time` int(10) unsigned default '0',
  `php_percent_time` int(10) unsigned default '0',
  `numqueries` int(10) unsigned default '0',
  `page_size` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_log` WRITE;
/*!40000 ALTER TABLE `ploopi_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_action`;
CREATE TABLE `ploopi_mb_action` (
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `id_action` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `id_workspace` int(10) default NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  `role_enabled` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_action`,`id_module_type`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_object` (`id_object`),
  KEY `role_enabled` (`role_enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_action` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_action` DISABLE KEYS */;
INSERT INTO `ploopi_mb_action` VALUES (1,1,'Installer un Module',NULL,0,0,1),(1,2,'Désinstaller un Module',NULL,0,0,1),(1,3,'Modifier les Paramètres d\'un Module',NULL,0,0,1),(1,4,'Instancier / Utiliser un Module',NULL,0,0,1),(1,5,'Modifier les Propriétés d\'un Module',NULL,0,0,1),(1,6,'Modifier la Page d\'Accueil',NULL,0,0,1),(1,7,'Installer un Skin',NULL,0,0,1),(1,8,'Désinstaller un Skin',NULL,0,0,1),(1,9,'Créer un Groupe',NULL,0,0,1),(1,10,'Modifier un Groupe',NULL,0,0,1),(1,11,'Supprimer un Groupe',NULL,0,0,1),(1,12,'Cloner un Groupe',NULL,0,0,1),(1,13,'Créer un Rôle',NULL,0,0,1),(1,14,'Modifier un Rôle',NULL,0,0,1),(1,15,'Supprimer un Rôle',NULL,0,0,1),(1,16,'Créer un Profil',NULL,0,0,1),(1,17,'Modifier un Profil',NULL,0,0,1),(1,18,'Supprimer un Profil',NULL,0,0,1),(1,19,'Ajouter un Utilisateur',NULL,0,0,1),(1,20,'Modifier un Utilisateur',NULL,0,0,1),(1,21,'Supprimer un Utilisateur',NULL,0,0,1),(1,22,'Détacher un Module',NULL,0,0,1),(1,23,'Supprimer un Module',NULL,0,0,1),(1,24,'Mettre à jour la Métabase',NULL,0,0,1),(1,25,'Connexion Utilisateur',NULL,0,0,1),(1,26,'Erreur de Connexion',NULL,0,0,1),(1,27,'Déplacer un Utilisateur',NULL,0,0,1),(1,28,'Attacher un Utilisateur',NULL,0,0,1),(1,29,'Détacher un Utilisateur',NULL,0,0,1),(1,32,'Mettre à jour un module',NULL,0,0,1);
/*!40000 ALTER TABLE `ploopi_mb_action` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_field`;
CREATE TABLE `ploopi_mb_field` (
  `tablename` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `type` varchar(50) default NULL,
  `visible` tinyint(1) unsigned default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tablename`,`name`,`id_module_type`),
  KEY `visible` (`visible`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_field` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_field` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_object`;
CREATE TABLE `ploopi_mb_object` (
  `id` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `script` varchar(255) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_object` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_object` DISABLE KEYS */;
INSERT INTO `ploopi_mb_object` VALUES (2,'Groupe d\'Utilisateur','ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=org&groupid=<IDRECORD>',1),(1,'Espace de Travail','ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=work&workspaceid=<IDRECORD>',1);
/*!40000 ALTER TABLE `ploopi_mb_object` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_relation`;
CREATE TABLE `ploopi_mb_relation` (
  `tablesrc` varchar(100) default NULL,
  `fieldsrc` varchar(100) default NULL,
  `tabledest` varchar(100) default NULL,
  `fielddest` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_relation` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_relation` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_schema`;
CREATE TABLE `ploopi_mb_schema` (
  `tablesrc` varchar(100) NOT NULL default '',
  `tabledest` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tabledest`,`tablesrc`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_schema` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_schema` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_schema` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_table`;
CREATE TABLE `ploopi_mb_table` (
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `visible` tinyint(1) unsigned default '1',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_table` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_table` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mb_wce_object`;
CREATE TABLE `ploopi_mb_wce_object` (
  `id` int(11) unsigned NOT NULL,
  `label` varchar(255) default NULL,
  `id_module_type` int(10) NOT NULL default '0',
  `script` varchar(255) default NULL,
  `select_id` varchar(64) default NULL,
  `select_label` varchar(64) default NULL,
  `select_table` varchar(64) default NULL,
  PRIMARY KEY  (`id`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_mb_wce_object` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_wce_object` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_wce_object` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_mimetype`;
CREATE TABLE `ploopi_mimetype` (
  `ext` varchar(10) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `group` varchar(30) NOT NULL,
  PRIMARY KEY  (`ext`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
LOCK TABLES `ploopi_mimetype` WRITE;
/*!40000 ALTER TABLE `ploopi_mimetype` DISABLE KEYS */;
INSERT INTO `ploopi_mimetype` (`ext`, `mimetype`, `filetype`, `group`) VALUES
('txt', 'text/plain', 'text', 'text'),
('html', 'text/html', 'html', 'text'),
('htm', 'text/html', 'html', 'text'),
('shtml', 'text/html', 'html', 'text'),
('shtm', 'text/html', 'html', 'text'),
('xhtml', 'text/xhtml', 'html', 'text'),
('xhtm', 'text/xhtml', 'html', 'text'),
('css', 'text/css', 'html', 'text'),
('js', 'application/javascript', 'script', 'text'),
('latex', 'application/x-latex', 'text', 'text'),
('g', 'text/plain', 'text', 'text'),
('bas', 'text/plain', 'text', 'text'),
('h', 'text/plain', 'text', 'text'),
('c', 'text/plain', 'text', 'text'),
('cc', 'text/plain', 'text', 'text'),
('cpp', 'text/plain', 'text', 'text'),
('hpp', 'text/plain', 'text', 'text'),
('java', 'text/plain', 'text', 'text'),
('hh', 'text/plain', 'text', 'text'),
('m', 'text/plain', 'text', 'text'),
('f90', 'text/plain', 'text', 'text'),
('csv', 'text/csv', 'text', 'text'),
('tsv', 'text/tab-separated-values', 'text', 'text'),
('php', 'application/x-httpd-php', 'script', 'text'),
('php3', 'application/x-httpd-php', 'script', 'text'),
('php4', 'application/x-httpd-php', 'script', 'text'),
('phtml', 'application/x-httpd-php', 'script', 'text'),
('sql', 'text/x-sql', 'text', 'text'),
('323', 'text/h323', 'text', 'text'),
('tcl', 'application/x-tcl', 'text', 'text'),
('tex', 'application/x-tex', 'text', 'text'),
('ltx', 'application/x-tex', 'text', 'text'),
('texi', 'application/x-tex', 'text', 'text'),
('ctx', 'application/x-tex', 'text', 'text'),
('py', 'text/x-python', 'script', 'text'),
('pl', 'text/x-perl', 'script', 'text'),
('info', 'text/plain', 'text', 'text'),
('msl', 'text/plain', 'text', 'text'),
('sct', 'text/scriptlet', 'text', 'text'),
('tpl', 'text/css', 'html', 'text'),
('text', 'text/plain', 'text', 'text'),
('nfo', '', 'document', 'text'),
('png', 'image/png', 'image', 'image'),
('gif', 'image/gif', 'image', 'image'),
('jpg', 'image/jpeg', 'image', 'image'),
('jpeg', 'image/jpeg', 'image', 'image'),
('jpe', 'image/jpeg', 'image', 'image'),
('jfif', 'image/jpeg', 'image', 'image'),
('bmp', 'image/bmp', 'image', 'image'),
('pcx', 'image/pcx', 'image', 'image'),
('tif', 'image/tiff', 'image', 'image'),
('tiff', 'image/tiff', 'image', 'image'),
('pnm', 'image/x-portable-anymap', 'image', 'image'),
('pbm', 'image/x-portable-bitmap', 'image', 'image'),
('pgm', 'image/x-portable-graymap', 'image', 'image'),
('ppm', 'image/x-portable-pixmap', 'image', 'image'),
('xbm', 'image/x-xbitmap', 'image', 'image'),
('xpm', 'image/x-xpixmap', 'image', 'image'),
('ico', 'image/x-icon', 'image', 'image'),
('svg', 'image/svg+xml', 'image', 'image'),
('svgz', 'image/svg+xml', 'image', 'image'),
('rgb', 'image/x-rgb', 'image', 'image'),
('jng', 'image/x-jng', 'image', 'image'),
('cdr', 'image/x-coreldraw', 'image', 'image'),
('pat', 'image/x-coreldrawpattern', 'image', 'image'),
('cdt', 'image/x-coreldrawtemplate', 'image', 'image'),
('cpt', 'image/x-corelphotopaint', 'image', 'image'),
('gif87', 'image/gif', 'image', 'image'),
('pef', 'image/tiff', 'image', 'image'),
('png24', 'image/png', 'image', 'image'),
('png32', 'image/png', 'image', 'image'),
('png8', 'image/png', 'image', 'image'),
('ras', 'image/x-cmu-raster', 'image', 'image'),
('xwd', 'image/x-xwindowdump', 'image', 'image'),
('art', '', 'image', 'image'),
('arw', '', 'image', 'image'),
('bmp2', '', 'image', 'image'),
('bmp3', '', 'image', 'image'),
('cin', '', 'image', 'image'),
('cmyk', '', 'image', 'image'),
('cmyka', '', 'image', 'image'),
('cr2', '', 'image', 'image'),
('crw', '', 'image', 'image'),
('cur', '', 'image', 'image'),
('cut', '', 'image', 'image'),
('dcm', '', 'image', 'image'),
('dcx', '', 'image', 'image'),
('dds', '', 'image', 'image'),
('dfont', '', 'image', 'image'),
('djvu', '', 'image', 'image'),
('dng', '', 'image', 'image'),
('dpx', '', 'image', 'image'),
('exr', '', 'image', 'image'),
('fax', '', 'image', 'image'),
('fig', '', 'image', 'image'),
('fits', '', 'image', 'image'),
('gray', '', 'image', 'image'),
('icb', '', 'image', 'image'),
('icon', '', 'image', 'image'),
('ipl', '', 'image', 'image'),
('jp2', '', 'image', 'image'),
('jpc', '', 'image', 'image'),
('jpx', '', 'image', 'image'),
('k25', '', 'image', 'image'),
('kdc', '', 'image', 'image'),
('mat', '', 'image', 'image'),
('miff', '', 'image', 'image'),
('mono', '', 'image', 'image'),
('mrw', '', 'image', 'image'),
('mtv', '', 'image', 'image'),
('mvg', '', 'image', 'image'),
('nef', '', 'image', 'image'),
('orf', '', 'image', 'image'),
('otb', '', 'image', 'image'),
('pal', '', 'image', 'image'),
('palm', '', 'image', 'image'),
('pam', '', 'image', 'image'),
('pcd', '', 'image', 'image'),
('pcds', '', 'image', 'image'),
('pcl', '', 'image', 'image'),
('pct', '', 'image', 'image'),
('pfm', '', 'image', 'image'),
('picon', '', 'image', 'image'),
('pict', '', 'image', 'image'),
('pix', '', 'image', 'image'),
('pjpeg', '', 'image', 'image'),
('ptif', '', 'image', 'image'),
('pwp', '', 'image', 'image'),
('rad', '', 'image', 'image'),
('raf', '', 'image', 'image'),
('rgba', '', 'image', 'image'),
('rla', '', 'image', 'image'),
('rle', '', 'image', 'image'),
('sfw', '', 'image', 'image'),
('sgi', '', 'image', 'image'),
('sr2', '', 'image', 'image'),
('srf', '', 'image', 'image'),
('sun', '', 'image', 'image'),
('tga', '', 'image', 'image'),
('tiff64', '', 'image', 'image'),
('tim', '', 'image', 'image'),
('uil', '', 'image', 'image'),
('uyvy', '', 'image', 'image'),
('vicar', '', 'image', 'image'),
('viff', '', 'image', 'image'),
('vst', '', 'image', 'image'),
('wbmp', '', 'image', 'image'),
('wmz', '', 'image', 'image'),
('wpg', '', 'image', 'image'),
('x3f', '', 'image', 'image'),
('xc', '', 'image', 'image'),
('xcf', '', 'image', 'image'),
('ycbcr', '', 'image', 'image'),
('ycbcra', '', 'image', 'image'),
('yuv', '', 'image', 'image'),
('thm', 'application/vnd.eri.thm', 'image', 'thumbnails'),
('pdb', '', 'document', 'thumbnails'),
('man', 'application/x-troff-man', 'document', 'unix'),
('bz2', 'application/x-bzip', 'archive', 'archive'),
('gz', 'application/x-gzip', 'archive', 'archive'),
('tar', 'application/x-tar', 'archive', 'archive'),
('tgz', 'application/x-gzip', 'archive', 'archive'),
('zip', 'application/zip', 'archive', 'archive'),
('z', 'application/x-compress', 'archive', 'archive'),
('sit', 'application/x-stuffit', 'archive', 'archive'),
('sitx', 'application/x-stuffit', 'archive', 'archive'),
('lzh', 'application/lzh', 'archive', 'archive'),
('lhw', 'application/lzh', 'archive', 'archive'),
('lzs', 'application/lzh', 'archive', 'archive'),
('lzw', 'application/lzh', 'archive', 'archive'),
('ace', 'application/x-ace', 'archive', 'archive'),
('rar', 'application/x-rar', 'archive', 'archive'),
('arj', 'application/x-arj', 'archive', 'archive'),
('7z', 'application/x-7z-compressed', 'archive', 'archive'),
('rpm', 'application/x-redhat-package', 'package', 'package'),
('deb', 'application/x-debian-package', 'package', 'package'),
('udeb', 'application/x-debian-package', 'package', 'package'),
('aif', 'audio/aiff', 'audio', 'audio'),
('aiff', 'audio/aiff', 'audio', 'audio'),
('aifc', 'audio/aiff', 'audio', 'audio'),
('mid', 'audio/midi', 'audio', 'audio'),
('midi', 'audio/midi', 'audio', 'audio'),
('kar', 'audio/midi', 'audio', 'audio'),
('rmi', 'audio/midi', 'audio', 'audio'),
('mp3', 'audio/mpeg', 'audio', 'audio'),
('mp2', 'audio/mpeg', 'audio', 'audio'),
('mpa', 'audio/mpeg', 'audio', 'audio'),
('ogg', 'audio/ogg', 'audio', 'audio'),
('wav', 'audio/wav', 'audio', 'audio'),
('wma', 'audio/x-ms-wma', 'audio', 'audio'),
('au', 'audio/basic', 'audio', 'audio'),
('snd', 'audio/basic', 'audio', 'audio'),
('flac', 'audio/flac', 'audio', 'audio'),
('aac', 'audio/mp4', 'audio', 'audio'),
('m4a', 'audio/mp4', 'audio', 'audio'),
('mka', 'audio/x-matroska', 'audio', 'audio'),
('ac3', 'audio/ac3', 'audio', 'audio'),
('mpc', 'audio/x-musepack', 'audio', 'audio'),
('mod', 'audio/x-mod', 'audio', 'audio/tracker'),
('xm', 'audio/x-xm', 'audio', 'audio/tracker'),
('xi', 'audio/x-xi', 'audio', 'audio/tracker'),
('s3m', 'audio/x-s3m', 'audio', 'audio/tracker'),
('stm', 'audio/x-stm', 'audio', 'audio/tracker'),
('it', 'audio/x-it', 'audio', 'audio/tracker'),
('asf', 'video/x-ms-asf', 'video', 'video'),
('asx', 'video/x-ms-asf', 'video', 'video'),
('avi', 'video/avi', 'video', 'video'),
('mpg', 'video/mpeg', 'video', 'video'),
('mpeg', 'video/mpeg', 'video', 'video'),
('mpe', 'video/mpeg', 'video', 'video'),
('wmv', 'video/x-ms-wmv', 'video', 'video'),
('wmx', 'video/x-ms-wmx', 'video', 'video'),
('qt', 'video/quicktime', 'video', 'video'),
('mov', 'video/quicktime', 'video', 'video'),
('movie', 'video/x-sgi-movie', 'video', 'video'),
('mp4', 'audio/mp4', 'video', 'video'),
('flv', 'video/x-flv', 'video', 'video'),
('mkv', 'video/x-matroska', 'video', 'video'),
('3gp', 'video/3gpp', 'video', 'video'),
('dv', 'video/dv', 'video', 'video'),
('dif', 'video/dv', 'video', 'video'),
('dl', 'video/dl', 'video', 'video'),
('h264', 'video/h264', 'video', 'video'),
('viv', 'video/vivo', 'video', 'video'),
('vivo', 'video/vivo', 'video', 'video'),
('mng', 'video/x-mng', 'video', 'video'),
('gl', 'video/gl', 'video', 'video'),
('fli', 'video/fli', 'video', 'video'),
('ra', 'audio/vnd.rn-realaudio', 'video', 'real'),
('ram', 'audio/x-pn-realaudio', 'video', 'real'),
('rm', 'application/vnd.rn-realmedia', 'video', 'real'),
('rv', 'video/vnd.rn-realvideo', 'video', 'real'),
('rmvb', 'application/vnd.rn-realmedia-vbr', 'video', 'real'),
('smil', 'application/smil', 'video', 'real'),
('smi', 'application/smil', 'video', 'real'),
('avs', '', 'video', 'video'),
('dps', '', 'video', 'video'),
('m2v', '', 'video', 'video'),
('m4v', '', 'video', 'video'),
('ogm', '', 'video', 'video'),
('vid', '', 'video', 'video'),
('pls', 'audio/scpls', 'audio', 'playlist'),
('m3u', 'audio/x-mpegurl', 'audio', 'playlist'),
('mxu', 'video/vnd.mpegurl', 'video', 'playlist'),
('pla', 'audio/x-iriver-pla', 'audio', 'playlist'),
('xml', 'text/xml', 'text', 'xml'),
('xsl', 'text/xsl', 'text', 'xml'),
('sgml', 'text/x-sgml', 'text', 'xml'),
('sgm', 'text/x-sgml', 'text', 'xml'),
('flr', 'x-world/x-vrml', 'text', 'xml'),
('vrml', 'x-world/x-vrml', 'text', 'xml'),
('wrl', 'x-world/x-vrml', 'text', 'xml'),
('wrz', 'x-world/x-vrml', 'text', 'xml'),
('xaf', 'x-world/x-vrml', 'text', 'xml'),
('xof', 'x-world/x-vrml', 'text', 'xml'),
('rss', 'application/rss+xml', 'text', 'xml'),
('rdf', 'application/rdf+xml', 'text', 'xml'),
('atom', 'application/atom+xml', 'text', 'xml'),
('opml', 'application/opml+xml', 'text', 'xml'),
('xul', 'application/vnd.mozilla.xul+xml', 'text', 'xml'),
('abw', 'application/x-abiword', 'document', 'office'),
('gnumeric', 'application/x-gnumeric', 'document', 'office'),
('kwd', 'application/x-kword', 'document', 'office'),
('kwt', 'application/x-kword', 'document', 'office'),
('ksp', 'application/x-kspread', 'spreadsheet', 'office'),
('kpr', 'application/x-kpresenter', 'presentation', 'office'),
('kpt', 'application/x-kpresenter', 'presentation', 'office'),
('doc', 'application/msword', 'document', 'microsoft'),
('dot', 'application/msword', 'document', 'microsoft'),
('xls', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xla', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlc', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlm', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlt', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlw', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('pps', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('ppt', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('ppz', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('pot', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('hlp', 'application/mshelp', 'document', 'microsoft'),
('chm', 'application/mshelp', 'document', 'microsoft'),
('msg', 'application/vnd.ms-outlook', 'document', 'microsoft'),
('mpp', 'application/vnd.ms-project', 'document', 'microsoft'),
('wcm', 'application/vnd.ms-works', 'document', 'microsoft'),
('wdb', 'application/vnd.ms-works', 'document', 'microsoft'),
('wks', 'application/vnd.ms-works', 'document', 'microsoft'),
('wps', 'application/vnd.ms-works', 'document', 'microsoft'),
('mdb', 'application/x-msaccess', 'document', 'microsoft'),
('wmf', 'application/x-msmetafile', 'document', 'microsoft'),
('mny', 'application/x-msmoney', 'document', 'microsoft'),
('pub', 'application/x-mspublisher', 'presentation', 'microsoft'),
('scd', 'application/x-msschedule', 'document', 'microsoft'),
('trm', 'application/x-msterminal', 'document', 'microsoft'),
('wri', 'application/x-mswrite', 'document', 'microsoft'),
('vsd', 'application/vnd.visio', 'document', 'microsoft'),
('scr', '', 'exec', 'microsoft'),
('sxw', 'application/vnd.sun.xml.writer', 'document', 'open office'),
('stw', 'application/vnd.sun.xml.writer.template', 'document', 'open office'),
('sxg', 'application/vnd.sun.xml.writer.global', 'document', 'open office'),
('sxc', 'application/vnd.sun.xml.calc', 'spreadsheet', 'open office'),
('stc', 'application/vnd.sun.xml.calc.template', 'spreadsheet', 'open office'),
('sxi', 'application/vnd.sun.xml.impress', 'presentation', 'open office'),
('sti', 'application/vnd.sun.xml.impress.template', 'presentation', 'open office'),
('sxd', 'application/vnd.sun.xml.draw', 'image', 'open office'),
('std', 'application/vnd.sun.xml.draw.template', 'image', 'open office'),
('sxm', 'application/vnd.sun.xml.math', 'spreadsheet', 'open office'),
('odt', 'application/vnd.oasis.opendocument.text', 'document', 'open office'),
('otm', 'application/vnd.oasis.opendocument.text-master', 'document', 'open office'),
('ott', 'application/vnd.oasis.opendocument.text-template', 'document', 'open office'),
('odc', 'application/vnd.oasis.opendocument.chart', 'document', 'open office'),
('otc', 'application/vnd.oasis.opendocument.chart-template', 'document', 'open office'),
('odf', 'application/vnd.oasis.opendocument.formula', 'spreadsheet', 'open office'),
('odg', 'application/vnd.oasis.opendocument.graphics', 'image', 'open office'),
('otg', 'application/vnd.oasis.opendocument.graphics-template', 'image', 'open office'),
('odi', 'application/vnd.oasis.opendocument.image', 'image', 'open office'),
('oti', 'application/vnd.oasis.opendocument.image-template', 'image', 'open office'),
('odp', 'application/vnd.oasis.opendocument.presentation', 'presentation', 'open office'),
('otp', 'application/vnd.oasis.opendocument.presentation-template', 'presentation', 'open office'),
('ods', 'application/vnd.oasis.opendocument.spreadsheet', 'spreadsheet', 'open office'),
('ots', 'application/vnd.oasis.opendocument.spreadsheet-template', 'spreadsheet', 'open office'),
('oth', 'application/vnd.oasis.opendocument.text-web', 'html', 'open office'),
('rtf', 'text/rtf', 'document', 'rich text'),
('rtx', 'text/richtext', 'document', 'rich text'),
('pdf', 'application/pdf', 'document', 'adobe'),
('ai', 'application/postscript', 'image', 'adobe'),
('eps', 'application/postscript', 'document', 'adobe'),
('psd', 'image/psd', 'image', 'adobe'),
('ps', 'application/postscript', 'document', 'adobe'),
('dcr', 'application/x-director', 'document', 'adobe'),
('dir', 'application/x-director', 'document', 'adobe'),
('dxr', 'application/x-director', 'document', 'adobe'),
('swf', 'application/x-shockwave-flash', 'image', 'adobe'),
('swfl', 'application/x-shockwave-flash', 'image', 'adobe'),
('fla', 'application/x-shockwave-flash', 'image', 'adobe'),
('pdfa', '', 'document', 'adobe'),
('hqx', 'application/mac-binhex40', 'exec', 'binarie/executable'),
('exe', 'application/x-msdownload', 'exec', 'binarie/executable'),
('com', 'application/x-msdownload', 'exec', 'binarie/executable'),
('msi', 'application/x-msi', 'exec', 'binarie/executable'),
('class', 'application/x-java-class', 'exec', 'binarie/executable'),
('jar', 'application/java', 'exec', 'binarie/executable'),
('jad', 'text/vnd.sun.j2me.app-descriptor', 'exec', 'binarie/executable'),
('bin', '', 'exec', 'binarie/executable'),
('sh', 'application/x-sh', 'script', 'shell'),
('bat', 'application/x-msdownload', 'script', 'shell'),
('otf', 'font/opentype', 'document', 'fonts'),
('ttf', 'application/x-font-ttf', 'document', 'fonts'),
('ttc', 'application/x-font-ttf', 'document', 'fonts'),
('pfa', 'application/x-font-type1', 'document', 'fonts'),
('pfb', 'application/x-font-type1', 'document', 'fonts'),
('cer', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('crt', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('der', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('p12', 'application/x-pkcs12', 'certificate', 'encoding/certificat'),
('pfx', 'application/x-pkcs12', 'certificate', 'encoding/certificat'),
('p7b', 'application/x-pkcs7-certificates', 'certificate', 'encoding/certificat'),
('spc', 'application/x-pkcs7-certificates', 'certificate', 'encoding/certificat'),
('p7r', 'application/x-pkcs7-certreqresp', 'certificate', 'encoding/certificat'),
('p7c', 'application/x-pkcs7-mime', 'certificate', 'encoding/certificat'),
('p7m', 'application/x-pkcs7-mime', 'certificate', 'encoding/certificat'),
('p7s', 'application/x-pkcs7-signature', 'certificate', 'encoding/certificat'),
('iso', 'application/x-iso9660-image', 'cd', 'disk images'),
('nrg', 'application/x-extension-nrg', 'cd', 'disk images'),
('ccd', 'text/x-cdwizard', 'cd', 'disk images'),
('dmg', 'application/x-apple-diskimage', 'cd', 'disk images'),
('vcf', 'text/x-vcard', 'document', 'other'),
('vcs', 'text/x-vcalendar', 'calendar', 'other'),
('ics', 'text/calendar', 'calendar', 'other'),
('icz', 'text/calendar', 'calendar', 'other'),
('mht', 'message/rfc822', 'document', 'other'),
('mhtml', 'message/rfc822', 'document', 'other'),
('torrent', 'application/x-bittorrent', 'document', 'other');
/*!40000 ALTER TABLE `ploopi_mimetype` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_module`;
CREATE TABLE `ploopi_module` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) default NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `public` tinyint(1) unsigned default '0',
  `shared` tinyint(1) unsigned default '0',
  `herited` tinyint(1) unsigned default '0',
  `adminrestricted` tinyint(1) unsigned default '0',
  `viewmode` int(10) unsigned default '1',
  `transverseview` tinyint(1) unsigned default '0',
  `autoconnect` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_workspace` (`id_workspace`),
  KEY `active` (`active`),
  KEY `shared` (`shared`),
  KEY `herited` (`herited`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_module` WRITE;
/*!40000 ALTER TABLE `ploopi_module` DISABLE KEYS */;
INSERT INTO `ploopi_module` VALUES (1,'Système',1,0,1,1,0,0,0,0,1,0,0);
/*!40000 ALTER TABLE `ploopi_module` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_module_type`;
CREATE TABLE `ploopi_module_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(100) default NULL,
  `system` tinyint(1) unsigned NOT NULL default '0',
  `publicparam` tinyint(1) unsigned default '0',
  `description` longtext,
  `version` varchar(32) default NULL,
  `author` varchar(255) default NULL,
  `date` varchar(14) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_module_type` WRITE;
/*!40000 ALTER TABLE `ploopi_module_type` DISABLE KEYS */;
INSERT INTO `ploopi_module_type` VALUES (1,'system',1,0,'Noyau du système','1.3','Ovensia','20090129000000');
/*!40000 ALTER TABLE `ploopi_module_type` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_module_workspace`;
CREATE TABLE `ploopi_module_workspace` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `position` tinyint(2) NOT NULL default '0',
  `blockposition` char(10) NOT NULL default 'left',
  PRIMARY KEY  (`id_workspace`,`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_module_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_module_workspace` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_module_workspace` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_param_choice`;
CREATE TABLE `ploopi_param_choice` (
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `displayed_value` varchar(100) default NULL,
  PRIMARY KEY  (`id_module_type`,`name`,`value`),
  KEY `name` (`name`),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_param_choice` WRITE;
/*!40000 ALTER TABLE `ploopi_param_choice` DISABLE KEYS */;
INSERT INTO `ploopi_param_choice` VALUES (1,'system_generate_htpasswd','0','non'),(1,'system_generate_htpasswd','1','oui'),(1,'system_set_cache','0','non'),(1,'system_set_cache','1','oui'),(1,'system_focus_popup','0','non'),(1,'system_focus_popup','1','oui');
/*!40000 ALTER TABLE `ploopi_param_choice` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_param_default`;
CREATE TABLE `ploopi_param_default` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `value` text NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`name`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_param_default` WRITE;
/*!40000 ALTER TABLE `ploopi_param_default` DISABLE KEYS */;
INSERT INTO `ploopi_param_default` VALUES (1,'system_generate_htpasswd','0',1),(1,'system_language','french',1),(1,'system_set_cache','0',1),(1,'system_focus_popup','0',1);
/*!40000 ALTER TABLE `ploopi_param_default` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_param_type`;
CREATE TABLE `ploopi_param_type` (
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `default_value` text NOT NULL,
  `public` tinyint(1) unsigned NOT NULL default '0',
  `description` longtext,
  `label` varchar(100) default NULL,
  PRIMARY KEY  (`id_module_type`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_param_type` WRITE;
/*!40000 ALTER TABLE `ploopi_param_type` DISABLE KEYS */;
INSERT INTO `ploopi_param_type` VALUES (1,'system_generate_htpasswd','1',0,'','Générer un fichier htpasswd'),(1,'system_language','',1,'','Langue du système'),(1,'system_set_cache','0',0,'','Activer le Cache'),(1,'system_focus_popup','0',0,'','Activer le Focus sur les Popups');
/*!40000 ALTER TABLE `ploopi_param_type` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_param_user`;
CREATE TABLE `ploopi_param_user` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `id_user` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`name`,`id_user`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_param_user` WRITE;
/*!40000 ALTER TABLE `ploopi_param_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_param_user` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_param_workspace`;
CREATE TABLE `ploopi_param_workspace` (
  `id_module` int(10) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`name`,`id_workspace`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_param_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_param_workspace` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_param_workspace` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_role`;
CREATE TABLE `ploopi_role` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned default '0',
  `id_workspace` int(10) default NULL,
  `label` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `def` tinyint(1) unsigned NOT NULL default '0',
  `shared` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `shared` (`shared`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_role` WRITE;
/*!40000 ALTER TABLE `ploopi_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_role` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_role_action`;
CREATE TABLE `ploopi_role_action` (
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_action` int(10) unsigned NOT NULL default '0',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_action`,`id_module_type`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_role_action` WRITE;
/*!40000 ALTER TABLE `ploopi_role_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_role_action` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_session`;
CREATE TABLE `ploopi_session` (
  `id` char(32) NOT NULL,
  `access` int(10) unsigned default NULL,
  `data` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_session` WRITE;
/*!40000 ALTER TABLE `ploopi_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_session` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_share`;
CREATE TABLE `ploopi_share` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_record` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) unsigned NOT NULL default '0',
  `type_share` varchar(16) default '0',
  `id_share` int(10) unsigned default '0',
  `id_module_type` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `search` (`id_module`,`id_object`,`id_record`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_share` (`id_share`),
  KEY `type_share` (`type_share`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_share` WRITE;
/*!40000 ALTER TABLE `ploopi_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_share` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_subscription`;
CREATE TABLE `ploopi_subscription` (
  `id` char(32) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) NOT NULL default '0',
  `id_record` varchar(255) NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `allactions` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_object` (`id_object`),
  KEY `id_user` (`id_user`),
  KEY `id_action` (`allactions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_subscription` WRITE;
/*!40000 ALTER TABLE `ploopi_subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_subscription` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_subscription_action`;
CREATE TABLE `ploopi_subscription_action` (
  `id_subscription` char(32) NOT NULL,
  `id_action` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_subscription`,`id_action`),
  KEY `id_action` (`id_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_subscription_action` WRITE;
/*!40000 ALTER TABLE `ploopi_subscription_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_subscription_action` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_tag`;
CREATE TABLE `ploopi_tag` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` char(32) NOT NULL,
  `tag_clean` char(32) NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_tag` WRITE;
/*!40000 ALTER TABLE `ploopi_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_tag` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_ticket`;
CREATE TABLE `ploopi_ticket` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `message` longtext,
  `needed_validation` tinyint(1) unsigned NOT NULL default '0',
  `delivery_notification` tinyint(1) unsigned NOT NULL default '0',
  `status` int(10) unsigned NOT NULL default '0',
  `object_label` varchar(255) NOT NULL default '',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `lastreply_timestp` bigint(14) unsigned NOT NULL default '0',
  `lastedit_timestp` bigint(14) unsigned NOT NULL default '0',
  `count_read` int(10) unsigned NOT NULL default '0',
  `count_replies` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) default NULL,
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `root_id` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_object` (`id_object`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `parent_id` (`parent_id`),
  KEY `root_id` (`root_id`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_ticket` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_ticket_dest`;
CREATE TABLE `ploopi_ticket_dest` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id_user`,`id_ticket`),
  KEY `id_ticket` (`id_ticket`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_ticket_dest` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_dest` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_dest` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_ticket_status`;
CREATE TABLE `ploopi_ticket_status` (
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `timestp` bigint(14) NOT NULL,
  KEY `id_ticket` (`id_ticket`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_ticket_status` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_status` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_ticket_watch`;
CREATE TABLE `ploopi_ticket_watch` (
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `notify` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_ticket`,`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_ticket_watch` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_watch` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_watch` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_user`;
CREATE TABLE `ploopi_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `login` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `date_creation` bigint(14) NOT NULL default '0',
  `date_expire` bigint(14) NOT NULL default '0',
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `comments` text NOT NULL,
  `address` text NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `service` varchar(64) NOT NULL,
  `function` varchar(64) NOT NULL,
  `number` varchar(255) NOT NULL,
  `postalcode` varchar(16) NOT NULL,
  `city` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL,
  `ticketsbyemail` tinyint(1) unsigned NOT NULL default '0',
  `servertimezone` tinyint(1) unsigned NOT NULL default '1',
  `color` varchar(16) NOT NULL default '',
  `timezone` varchar(64) NOT NULL,
  `office` varchar(64) NOT NULL,
  `civility` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login_unique` (`login`),
  KEY `lastname` (`lastname`),
  KEY `firstname` (`firstname`),
  FULLTEXT KEY `FT` (`city`,`country`,`function`,`firstname`,`lastname`,`service`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_user` WRITE;
/*!40000 ALTER TABLE `ploopi_user` DISABLE KEYS */;
INSERT INTO `ploopi_user` VALUES (2,'Administrateur','','admin','feee4f3ca6345d6562972e7c3a9dad9b',20040701101222,0,'','','','','','','','','','','','',0,1,'','0','','');
/*!40000 ALTER TABLE `ploopi_user` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_user_action_log`;
CREATE TABLE `ploopi_user_action_log` (
  `id_user` int(10) unsigned default '0',
  `id_action` int(10) unsigned default '0',
  `id_module_type` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) default NULL,
  `ip` varchar(64) NOT NULL,
  `timestp` varchar(14) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_user_action_log` WRITE;
/*!40000 ALTER TABLE `ploopi_user_action_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_user_action_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_validation`;
CREATE TABLE `ploopi_validation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  `type_validation` varchar(16) default '0',
  `id_validation` int(10) unsigned default '0',
  `id_module_type` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `search` (`id_module`,`id_object`,`id_record`),
  KEY `type_workflow` (`type_validation`),
  KEY `id_workflow` (`id_validation`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_validation` WRITE;
/*!40000 ALTER TABLE `ploopi_validation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_validation` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_workspace`;
CREATE TABLE `ploopi_workspace` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_workspace` int(10) unsigned default '0',
  `label` varchar(255) NOT NULL default 'NULL',
  `code` varchar(64) default 'NULL',
  `system` tinyint(1) unsigned NOT NULL default '0',
  `protected` tinyint(1) unsigned NOT NULL default '0',
  `parents` varchar(255) default 'NULL',
  `iprules` text,
  `macrules` text,
  `template` varchar(255) default NULL,
  `depth` int(10) NOT NULL default '0',
  `mustdefinerule` tinyint(1) unsigned default '0',
  `backoffice` tinyint(1) unsigned default '1',
  `frontoffice` tinyint(1) unsigned default '0',
  `backoffice_domainlist` longtext,
  `title` varchar(255) NOT NULL default '',
  `meta_description` longtext NOT NULL,
  `meta_keywords` longtext NOT NULL,
  `meta_author` varchar(255) NOT NULL default '',
  `meta_copyright` varchar(255) NOT NULL default '',
  `meta_robots` varchar(255) NOT NULL default 'index, follow, all',
  `frontoffice_domainlist` longtext,
  PRIMARY KEY  (`id`),
  KEY `code` (`code`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace` DISABLE KEYS */;
INSERT INTO `ploopi_workspace` VALUES (2,1,'Espace Principal','',0,0,'0;1','','','dims',2,0,1,0,'*\r\n','','','','','','','*'),(1,0,'system',NULL,1,0,'0',NULL,NULL,'dims',1,0,1,0,NULL,'','','','','','index, follow, all','');
/*!40000 ALTER TABLE `ploopi_workspace` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_workspace_group`;
CREATE TABLE `ploopi_workspace_group` (
  `id_group` int(10) NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `adminlevel` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id_group`,`id_workspace`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_workspace_group` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workspace_group` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_workspace_group_role`;
CREATE TABLE `ploopi_workspace_group_role` (
  `id_group` int(10) NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `id_role` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`,`id_workspace`,`id_role`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_role` (`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_workspace_group_role` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_group_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workspace_group_role` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_workspace_user`;
CREATE TABLE `ploopi_workspace_user` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_profile` int(10) unsigned NOT NULL default '0',
  `adminlevel` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id_user`,`id_workspace`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_workspace_user` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_user` DISABLE KEYS */;
INSERT INTO `ploopi_workspace_user` VALUES (2,2,1,99);
/*!40000 ALTER TABLE `ploopi_workspace_user` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ploopi_workspace_user_role`;
CREATE TABLE `ploopi_workspace_user_role` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `id_role` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_user`,`id_workspace`,`id_role`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_role` (`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `ploopi_workspace_user_role` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_user_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workspace_user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `ploopi_mb_wce_object` ( `id` , `label` , `id_module_type` , `script` , `select_id` , `select_label` , `select_table` )
VALUES ('1', 'Affichage Trombinscope', '1', '?object=''display''', NULL , NULL , NULL);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displaymodule', '0', 0, '', 'Afficher la colonne "Module" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displaymodule', '0', 'non'), (1, 'system_search_displaymodule', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displaymodule', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayindexed', '0', 0, '', 'Afficher la colonne "Indexé le" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayindexed', '0', 'non'), (1, 'system_search_displayindexed', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayindexed', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayworkspace', '0', 0, '', 'Afficher la colonne "Espace" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayworkspace', '0', 'non'), (1, 'system_search_displayworkspace', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayworkspace', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayuser', '0', 0, '', 'Afficher la colonne "Utilisateur" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayuser', '0', 'non'), (1, 'system_search_displayuser', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayuser', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displaydatetime', '0', 0, '', 'Afficher la colonne "Ajouté le" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displaydatetime', '0', 'non'), (1, 'system_search_displaydatetime', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displaydatetime', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayobjecttype', '0', 0, '', 'Afficher la colonne "Type d\'Objet" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayobjecttype', '0', 'non'), (1, 'system_search_displayobjecttype', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayobjecttype', '1', 1);

ALTER TABLE `ploopi_user` ADD `building` VARCHAR( 255 ) NOT NULL AFTER `timezone` , ADD `floor` VARCHAR( 32 ) NOT NULL AFTER `building` ;
ALTER TABLE `ploopi_user` ADD `rank` VARCHAR( 32 ) NOT NULL ;

ALTER TABLE `ploopi_module` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT;

INSERT INTO `ploopi_module` ( `id` , `label` , `id_module_type` , `id_workspace` , `active` , `visible` , `public` , `shared` , `herited` , `adminrestricted` , `viewmode` , `transverseview` , `autoconnect` )
VALUES ('-1', 'Recherche', '1', '0', '1', '1', '0', '0', '0', '0', '1', '0', '0');

ALTER TABLE `ploopi_workspace` DROP INDEX `code` ;
ALTER TABLE `ploopi_workspace` CHANGE `code` `code` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

DROP TABLE IF EXISTS `ploopi_serializedvar`;
CREATE TABLE IF NOT EXISTS `ploopi_serializedvar` (
  `id` char(32) NOT NULL,
  `id_session` char(32) NOT NULL,
  `data` longtext,
  PRIMARY KEY  (`id`),
  KEY `id_session` (`id_session`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_user_action_log` ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_user` ;

ALTER TABLE `ploopi_user_action_log` ADD `user` VARCHAR( 100 ) NOT NULL ,
ADD `workspace` VARCHAR( 100 ) NOT NULL ,
ADD `action` VARCHAR( 100 ) NOT NULL ,
ADD `module_type` VARCHAR( 100 ) NOT NULL ,
ADD `module` VARCHAR( 100 ) NOT NULL ;

ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `user` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `workspace` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `action` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `module_type` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `module` );

INSERT INTO `ploopi_mb_action` ( `id_module_type` , `id_action` , `label` , `description` , `id_workspace` , `id_object` , `role_enabled` )
VALUES
('1', '39', 'Créer un Espace de Travail', NULL , '0', '0', '1'),
('1', '40', 'Modifier un Espace de Travail', NULL , '0', '0', '1'),
('1', '41', 'Supprimer un Espace de Travail', NULL , '0', '0', '1'),
('1', '42', 'Clôner un Espace de Travail', NULL , '0', '0', '1');

ALTER TABLE `ploopi_mb_table` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mb_table` ADD PRIMARY KEY ( `name` );
ALTER TABLE `ploopi_mb_field` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mb_field` ADD PRIMARY KEY ( `tablename` , `name` );

ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `tablesrc` );
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `fieldsrc` );
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `tabledest` );
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `fielddest` );
ALTER TABLE `ploopi_mb_field` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_mb_schema` ADD INDEX ( `tablesrc` );
ALTER TABLE `ploopi_mb_table` ADD INDEX ( `id_module_type` );

DELETE FROM `ploopi_param_default` WHERE name = 'system_set_cache';
DELETE FROM `ploopi_param_type` WHERE name = 'system_set_cache';
DELETE FROM `ploopi_param_choice` WHERE name = 'system_set_cache';

ALTER TABLE `ploopi_user` CHANGE `service` `service` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `function` `function` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `city` `city` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `country` `country` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `floor` `floor` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `office` `office` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `rank` `rank` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `ploopi_serializedvar` DROP PRIMARY KEY;
ALTER TABLE `ploopi_serializedvar` ADD PRIMARY KEY ( `id` , `id_session` ) ;

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES (1, 'system_jodwebservice', '', 0, '', 'URL du webservice JODConverter');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES (1, 'system_jodwebservice', '', 1);

--
-- Structure de la table `ploopi_mb_table`
--

DROP TABLE IF EXISTS `ploopi_mb_table`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_table` (
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `visible` tinyint(1) unsigned default '1',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_table`
--

INSERT INTO `ploopi_mb_table` (`name`, `label`, `visible`, `id_module_type`) VALUES
('ploopi_group', 'group', 1, 1),
('ploopi_workspace', 'workspace', 1, 1),
('ploopi_user', 'user', 1, 1),
('ploopi_module', 'module', 1, 1);

--
-- Structure de la table `ploopi_mb_field`
--

DROP TABLE IF EXISTS `ploopi_mb_field`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_field` (
  `tablename` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `type` varchar(50) default NULL,
  `visible` tinyint(1) unsigned default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tablename`,`name`),
  KEY `visible` (`visible`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_field`
--

INSERT INTO `ploopi_mb_field` (`tablename`, `name`, `label`, `type`, `visible`, `id_module_type`) VALUES
('ploopi_group', 'shared', 'shared', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'depth', 'depth', 'int(10) unsigned', 1, 1),
('ploopi_group', 'id_workspace', 'id_workspace', 'int(10) unsigned', 0, 1),
('ploopi_group', 'protected', 'protected', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'parents', 'parents', 'varchar(100)', 1, 1),
('ploopi_group', 'system', 'system', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'label', 'label', 'varchar(255)', 1, 1),
('ploopi_group', 'id_group', 'id_group', 'int(10) unsigned', 0, 1),
('ploopi_group', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_workspace', 'frontoffice_domainlist', 'frontoffice_domainlist', 'longtext', 1, 1),
('ploopi_workspace', 'meta_robots', 'meta_robots', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_copyright', 'meta_copyright', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_author', 'meta_author', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_keywords', 'meta_keywords', 'longtext', 1, 1),
('ploopi_workspace', 'frontoffice', 'frontoffice', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'backoffice_domainlist', 'backoffice_domainlist', 'longtext', 1, 1),
('ploopi_workspace', 'title', 'title', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_description', 'meta_description', 'longtext', 1, 1),
('ploopi_workspace', 'backoffice', 'backoffice', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'mustdefinerule', 'mustdefinerule', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'depth', 'depth', 'int(10)', 1, 1),
('ploopi_workspace', 'template', 'template', 'varchar(255)', 1, 1),
('ploopi_workspace', 'macrules', 'macrules', 'text', 1, 1),
('ploopi_workspace', 'iprules', 'iprules', 'text', 1, 1),
('ploopi_workspace', 'code', 'code', 'text', 1, 1),
('ploopi_workspace', 'system', 'system', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'protected', 'protected', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'parents', 'parents', 'varchar(255)', 1, 1),
('ploopi_workspace', 'label', 'label', 'varchar(255)', 1, 1),
('ploopi_workspace', 'id_workspace', 'id_workspace', 'int(10) unsigned', 0, 1),
('ploopi_workspace', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_user', 'rank', 'rank', 'varchar(255)', 1, 1),
('ploopi_user', 'civility', 'civility', 'varchar(16)', 1, 1),
('ploopi_user', 'office', 'office', 'varchar(255)', 1, 1),
('ploopi_user', 'floor', 'floor', 'varchar(255)', 1, 1),
('ploopi_user', 'building', 'building', 'varchar(255)', 1, 1),
('ploopi_user', 'timezone', 'timezone', 'varchar(64)', 1, 1),
('ploopi_user', 'color', 'color', 'varchar(16)', 1, 1),
('ploopi_user', 'servertimezone', 'servertimezone', 'tinyint(1) unsigned', 1, 1),
('ploopi_user', 'ticketsbyemail', 'ticketsbyemail', 'tinyint(1) unsigned', 1, 1),
('ploopi_user', 'country', 'country', 'varchar(255)', 1, 1),
('ploopi_user', 'city', 'city', 'varchar(255)', 1, 1),
('ploopi_user', 'service', 'service', 'varchar(255)', 1, 1),
('ploopi_user', 'function', 'function', 'varchar(255)', 1, 1),
('ploopi_user', 'number', 'number', 'varchar(255)', 1, 1),
('ploopi_user', 'postalcode', 'postalcode', 'varchar(16)', 1, 1),
('ploopi_user', 'mobile', 'mobile', 'varchar(32)', 1, 1),
('ploopi_user', 'address', 'address', 'text', 1, 1),
('ploopi_user', 'comments', 'comments', 'text', 1, 1),
('ploopi_user', 'fax', 'fax', 'varchar(32)', 1, 1),
('ploopi_user', 'phone', 'phone', 'varchar(32)', 1, 1),
('ploopi_user', 'email', 'email', 'varchar(255)', 1, 1),
('ploopi_user', 'date_expire', 'date_expire', 'bigint(14)', 1, 1),
('ploopi_user', 'login', 'login', 'varchar(32)', 1, 1),
('ploopi_user', 'date_creation', 'date_creation', 'bigint(14)', 1, 1),
('ploopi_user', 'firstname', 'firstname', 'varchar(100)', 1, 1),
('ploopi_user', 'lastname', 'lastname', 'varchar(100)', 1, 1),
('ploopi_user', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_module', 'autoconnect', 'autoconnect', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'transverseview', 'transverseview', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'viewmode', 'viewmode', 'int(10) unsigned', 1, 1),
('ploopi_module', 'adminrestricted', 'adminrestricted', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'herited', 'herited', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'public', 'public', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'shared', 'shared', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'visible', 'visible', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'active', 'active', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'id_workspace', 'id_workspace', 'int(10)', 0, 1),
('ploopi_module', 'id_module_type', 'id_module_type', 'int(10) unsigned', 0, 1),
('ploopi_module', 'label', 'label', 'varchar(100)', 1, 1),
('ploopi_module', 'id', 'id', 'int(10)', 0, 1);

--
-- Structure de la table `ploopi_mb_relation`
--

DROP TABLE IF EXISTS `ploopi_mb_relation`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_relation` (
  `tablesrc` varchar(100) default NULL,
  `fieldsrc` varchar(100) default NULL,
  `tabledest` varchar(100) default NULL,
  `fielddest` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  KEY `tablesrc` (`tablesrc`),
  KEY `fieldsrc` (`fieldsrc`),
  KEY `tabledest` (`tabledest`),
  KEY `fielddest` (`fielddest`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_relation`
--

INSERT INTO `ploopi_mb_relation` (`tablesrc`, `fieldsrc`, `tabledest`, `fielddest`, `id_module_type`) VALUES
('ploopi_group', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_group', 'id_group', 'ploopi_group', 'id', 1),
('ploopi_workspace', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_module', 'id_workspace', 'ploopi_workspace', 'id', 1);

--
-- Structure de la table `ploopi_mb_schema`
--

DROP TABLE IF EXISTS `ploopi_mb_schema`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_schema` (
  `tablesrc` varchar(100) NOT NULL default '',
  `tabledest` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tabledest`,`tablesrc`),
  KEY `tablesrc` (`tablesrc`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_schema`
--

INSERT INTO `ploopi_mb_schema` (`tablesrc`, `tabledest`, `id_module_type`) VALUES
('ploopi_group', 'ploopi_workspace', 1),
('ploopi_workspace', 'ploopi_workspace', 1),
('ploopi_group', 'ploopi_group', 1),
('ploopi_module', 'ploopi_workspace', 1);


UPDATE `ploopi_module_type` SET `version` = '1.8.9.0', `author` = 'Ovensia', `date` = '20101214000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;

INSERT INTO `ploopi_param_type` (`id_module_type` ,`name` ,`default_value` ,`public` ,`description` ,`label`) VALUES ('1', 'system_submenu_display', '1', '0', NULL , 'Afficher les sous-menus de (Mon Espace)');
INSERT INTO `ploopi_param_default` (`id_module` ,`name` ,`value` ,`id_module_type`) VALUES ('1', 'system_submenu_display', '1', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type` ,`name` ,`value` ,`displayed_value`) VALUES ('1', 'system_submenu_display', '1', 'oui'), ('1', 'system_submenu_display', '0', 'non');

ALTER TABLE `ploopi_workspace` ADD `priority` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0', ADD INDEX ( `priority` );
UPDATE `ploopi_workspace` SET priority = 1 * POW(10, depth-2);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES (1, 'system_unoconv', '', 0, '', 'Chemin vers UNOCONV');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES (1, 'system_unoconv', '/usr/bin/unoconv', 1);

ALTER TABLE `ploopi_user` ADD `service2` VARCHAR( 255 ) NOT NULL AFTER `service`;

UPDATE `ploopi_module_type` SET `version` = '1.8.9.5', `author` = 'Ovensia', `date` = '20111129000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
