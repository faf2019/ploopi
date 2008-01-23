-- MySQL dump 10.11
--
-- Host: localhost    Database: plootest
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `phpdig_clicks`
--

DROP TABLE IF EXISTS `phpdig_clicks`;
CREATE TABLE `phpdig_clicks` (
  `c_num` mediumint(9) NOT NULL,
  `c_url` varchar(255) NOT NULL default '',
  `c_val` varchar(255) NOT NULL default '',
  `c_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_clicks`
--

LOCK TABLES `phpdig_clicks` WRITE;
/*!40000 ALTER TABLE `phpdig_clicks` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_clicks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_engine`
--

DROP TABLE IF EXISTS `phpdig_engine`;
CREATE TABLE `phpdig_engine` (
  `spider_id` mediumint(9) NOT NULL default '0',
  `key_id` mediumint(9) NOT NULL default '0',
  `weight` smallint(4) NOT NULL default '0',
  KEY `key_id` (`key_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_engine`
--

LOCK TABLES `phpdig_engine` WRITE;
/*!40000 ALTER TABLE `phpdig_engine` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_engine` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_excludes`
--

DROP TABLE IF EXISTS `phpdig_excludes`;
CREATE TABLE `phpdig_excludes` (
  `ex_id` mediumint(11) NOT NULL auto_increment,
  `ex_site_id` mediumint(9) NOT NULL,
  `ex_path` text NOT NULL,
  PRIMARY KEY  (`ex_id`),
  KEY `ex_site_id` (`ex_site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_excludes`
--

LOCK TABLES `phpdig_excludes` WRITE;
/*!40000 ALTER TABLE `phpdig_excludes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_excludes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_includes`
--

DROP TABLE IF EXISTS `phpdig_includes`;
CREATE TABLE `phpdig_includes` (
  `in_id` mediumint(11) NOT NULL auto_increment,
  `in_site_id` mediumint(9) NOT NULL,
  `in_path` text NOT NULL,
  PRIMARY KEY  (`in_id`),
  KEY `in_site_id` (`in_site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_includes`
--

LOCK TABLES `phpdig_includes` WRITE;
/*!40000 ALTER TABLE `phpdig_includes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_includes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_keywords`
--

DROP TABLE IF EXISTS `phpdig_keywords`;
CREATE TABLE `phpdig_keywords` (
  `key_id` int(9) NOT NULL auto_increment,
  `twoletters` char(2) NOT NULL,
  `keyword` varchar(64) NOT NULL,
  PRIMARY KEY  (`key_id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `twoletters` (`twoletters`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_keywords`
--

LOCK TABLES `phpdig_keywords` WRITE;
/*!40000 ALTER TABLE `phpdig_keywords` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_logs`
--

DROP TABLE IF EXISTS `phpdig_logs`;
CREATE TABLE `phpdig_logs` (
  `l_id` mediumint(9) NOT NULL auto_increment,
  `l_includes` varchar(255) NOT NULL default '',
  `l_excludes` varchar(127) default NULL,
  `l_num` mediumint(9) default NULL,
  `l_mode` char(1) default NULL,
  `l_ts` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `l_time` float NOT NULL default '0',
  PRIMARY KEY  (`l_id`),
  KEY `l_includes` (`l_includes`),
  KEY `l_excludes` (`l_excludes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_logs`
--

LOCK TABLES `phpdig_logs` WRITE;
/*!40000 ALTER TABLE `phpdig_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_site_page`
--

DROP TABLE IF EXISTS `phpdig_site_page`;
CREATE TABLE `phpdig_site_page` (
  `site_id` int(4) NOT NULL,
  `days` int(4) NOT NULL default '0',
  `links` int(4) NOT NULL default '5',
  `depth` int(4) NOT NULL default '5',
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_site_page`
--

LOCK TABLES `phpdig_site_page` WRITE;
/*!40000 ALTER TABLE `phpdig_site_page` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_site_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_sites`
--

DROP TABLE IF EXISTS `phpdig_sites`;
CREATE TABLE `phpdig_sites` (
  `site_id` mediumint(9) NOT NULL auto_increment,
  `site_url` varchar(127) NOT NULL,
  `upddate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `username` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `port` smallint(6) default NULL,
  `locked` tinyint(1) NOT NULL default '0',
  `stopped` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_sites`
--

LOCK TABLES `phpdig_sites` WRITE;
/*!40000 ALTER TABLE `phpdig_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_spider`
--

DROP TABLE IF EXISTS `phpdig_spider`;
CREATE TABLE `phpdig_spider` (
  `spider_id` mediumint(9) NOT NULL auto_increment,
  `file` varchar(127) NOT NULL,
  `first_words` mediumtext NOT NULL,
  `upddate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `md5` varchar(50) default NULL,
  `site_id` mediumint(9) NOT NULL default '0',
  `path` varchar(127) NOT NULL,
  `num_words` int(11) NOT NULL default '1',
  `last_modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `filesize` int(11) NOT NULL default '0',
  PRIMARY KEY  (`spider_id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_spider`
--

LOCK TABLES `phpdig_spider` WRITE;
/*!40000 ALTER TABLE `phpdig_spider` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_spider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpdig_tempspider`
--

DROP TABLE IF EXISTS `phpdig_tempspider`;
CREATE TABLE `phpdig_tempspider` (
  `file` text NOT NULL,
  `id` mediumint(11) NOT NULL auto_increment,
  `level` tinyint(6) NOT NULL default '0',
  `path` text NOT NULL,
  `site_id` mediumint(9) NOT NULL default '0',
  `indexed` tinyint(1) NOT NULL default '0',
  `upddate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `error` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpdig_tempspider`
--

LOCK TABLES `phpdig_tempspider` WRITE;
/*!40000 ALTER TABLE `phpdig_tempspider` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpdig_tempspider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_annotation`
--

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

--
-- Dumping data for table `ploopi_annotation`
--

LOCK TABLES `ploopi_annotation` WRITE;
/*!40000 ALTER TABLE `ploopi_annotation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_annotation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_annotation_tag`
--

DROP TABLE IF EXISTS `ploopi_annotation_tag`;
CREATE TABLE `ploopi_annotation_tag` (
  `id_annotation` int(10) unsigned NOT NULL default '0',
  `id_tag` int(10) unsigned NOT NULL default '0',
  KEY `id_annotation` (`id_annotation`),
  KEY `id_tag` (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_annotation_tag`
--

LOCK TABLES `ploopi_annotation_tag` WRITE;
/*!40000 ALTER TABLE `ploopi_annotation_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_annotation_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_connecteduser`
--

DROP TABLE IF EXISTS `ploopi_connecteduser`;
CREATE TABLE `ploopi_connecteduser` (
  `sid` varchar(255) default '0',
  `ip` varchar(255) default NULL,
  `domain` varchar(128) NOT NULL default '',
  `user_id` int(10) unsigned default '0',
  `workspace_id` int(10) default NULL,
  `module_id` int(10) unsigned default '0',
  `timestp` varchar(14) default '00000000000000'
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_connecteduser`
--

LOCK TABLES `ploopi_connecteduser` WRITE;
/*!40000 ALTER TABLE `ploopi_connecteduser` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_connecteduser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_documents_ext`
--

DROP TABLE IF EXISTS `ploopi_documents_ext`;
CREATE TABLE `ploopi_documents_ext` (
  `ext` varchar(10) default NULL,
  `filetype` varchar(16) default NULL,
  KEY `ext` (`ext`),
  KEY `filetype` (`filetype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_documents_ext`
--

LOCK TABLES `ploopi_documents_ext` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_ext` DISABLE KEYS */;
INSERT INTO `ploopi_documents_ext` VALUES ('odt','document'),('doc','document'),('xls','spreadsheet'),('mp3','audio'),('wav','audio'),('ogg','audio'),('jpg','image'),('jpeg','image'),('png','image'),('gif','image'),('psd','image'),('xcf','image'),('svg','image'),('pdf','document'),('avi','video'),('wmv','video'),('ogm','video'),('mpg','video'),('mpeg','video'),('zip','archive'),('tgz','archive'),('gz','archive'),('rar','archive'),('bz2','archive'),('ace','archive');
/*!40000 ALTER TABLE `ploopi_documents_ext` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_documents_file`
--

DROP TABLE IF EXISTS `ploopi_documents_file`;
CREATE TABLE `ploopi_documents_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) default NULL,
  `ref` varchar(32) NOT NULL,
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

--
-- Dumping data for table `ploopi_documents_file`
--

LOCK TABLES `ploopi_documents_file` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_documents_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_documents_folder`
--

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

--
-- Dumping data for table `ploopi_documents_folder`
--

LOCK TABLES `ploopi_documents_folder` WRITE;
/*!40000 ALTER TABLE `ploopi_documents_folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_documents_folder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_group`
--

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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_group`
--

LOCK TABLES `ploopi_group` WRITE;
/*!40000 ALTER TABLE `ploopi_group` DISABLE KEYS */;
INSERT INTO `ploopi_group` VALUES (1,0,'system',1,1,'0',1,0,0),(3,1,'Groupe Principal',0,1,'0;1',2,1,0);
/*!40000 ALTER TABLE `ploopi_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_group_user`
--

DROP TABLE IF EXISTS `ploopi_group_user`;
CREATE TABLE `ploopi_group_user` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_group` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_group`,`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_group_user`
--

LOCK TABLES `ploopi_group_user` WRITE;
/*!40000 ALTER TABLE `ploopi_group_user` DISABLE KEYS */;
INSERT INTO `ploopi_group_user` VALUES (2,3);
/*!40000 ALTER TABLE `ploopi_group_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_index_element`
--

DROP TABLE IF EXISTS `ploopi_index_element`;
CREATE TABLE `ploopi_index_element` (
  `id` char(32) NOT NULL,
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) NOT NULL,
  `timestp_create` bigint(14) unsigned NOT NULL default '0',
  `timestp_modify` bigint(14) unsigned NOT NULL default '0',
  `timestp_lastindex` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
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

--
-- Dumping data for table `ploopi_index_element`
--

LOCK TABLES `ploopi_index_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_element` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_index_keyword`
--

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

--
-- Dumping data for table `ploopi_index_keyword`
--

LOCK TABLES `ploopi_index_keyword` WRITE;
/*!40000 ALTER TABLE `ploopi_index_keyword` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_keyword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_index_keyword_element`
--

DROP TABLE IF EXISTS `ploopi_index_keyword_element`;
CREATE TABLE `ploopi_index_keyword_element` (
  `id_keyword` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` int(10) unsigned NOT NULL default '0',
  KEY `id_keyword` (`id_keyword`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_index_keyword_element`
--

LOCK TABLES `ploopi_index_keyword_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_keyword_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_keyword_element` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_index_stem`
--

DROP TABLE IF EXISTS `ploopi_index_stem`;
CREATE TABLE `ploopi_index_stem` (
  `id` char(32) NOT NULL default '0',
  `stem` char(20) character set latin1 collate latin1_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `stem` (`stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_index_stem`
--

LOCK TABLES `ploopi_index_stem` WRITE;
/*!40000 ALTER TABLE `ploopi_index_stem` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_stem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_index_stem_element`
--

DROP TABLE IF EXISTS `ploopi_index_stem_element`;
CREATE TABLE `ploopi_index_stem_element` (
  `id_stem` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` int(10) unsigned NOT NULL default '0',
  KEY `id_stem` (`id_stem`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_index_stem_element`
--

LOCK TABLES `ploopi_index_stem_element` WRITE;
/*!40000 ALTER TABLE `ploopi_index_stem_element` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_index_stem_element` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_log`
--

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
  `dims_userid` int(10) default NULL,
  `dims_workspaceid` int(10) default NULL,
  `dims_moduleid` int(10) default NULL,
  `browser` varchar(64) default NULL,
  `system` varchar(64) default NULL,
  `date_year` int(10) default NULL,
  `date_month` int(10) default NULL,
  `date_day` int(10) default NULL,
  `date_hour` int(10) default NULL,
  `date_minute` int(10) default NULL,
  `date_second` int(10) default NULL,
  `total_exec_time` int(10) unsigned default '0',
  `sql_exec_time` int(10) unsigned default '0',
  `sql_percent_time` int(10) unsigned default '0',
  `php_percent_time` int(10) unsigned default '0',
  `numqueries` int(10) unsigned default '0',
  `page_size` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_log`
--

LOCK TABLES `ploopi_log` WRITE;
/*!40000 ALTER TABLE `ploopi_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_action`
--

DROP TABLE IF EXISTS `ploopi_mb_action`;
CREATE TABLE `ploopi_mb_action` (
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `id_action` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `id_workspace` int(10) default NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_action`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_action`
--

LOCK TABLES `ploopi_mb_action` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_action` DISABLE KEYS */;
INSERT INTO `ploopi_mb_action` VALUES (1,1,'Installer un Module',NULL,0,0),(1,2,'DÃ©sinstaller un Module',NULL,0,0),(1,3,'Modifier les ParamÃštres d\'un Module',NULL,0,0),(1,4,'Instancier / Utiliser un Module',NULL,0,0),(1,5,'Modifier les PropriÃ©tÃ©s d\'un Module',NULL,0,0),(1,6,'Modifier la Page d\'Accueil',NULL,0,0),(1,7,'Installer un Skin',NULL,0,0),(1,8,'DÃ©sinstaller un Skin',NULL,0,0),(1,9,'CrÃ©er un Groupe',NULL,0,0),(1,10,'Modifier un Groupe',NULL,0,0),(1,11,'Supprimer un Groupe',NULL,0,0),(1,12,'Cloner un Groupe',NULL,0,0),(1,13,'CrÃ©er un RÃŽle',NULL,0,0),(1,14,'Modifier un RÃŽle',NULL,0,0),(1,15,'Supprimer un RÃŽle',NULL,0,0),(1,16,'CrÃ©er un Profil',NULL,0,0),(1,17,'Modifier un Profil',NULL,0,0),(1,18,'Supprimer un Profil',NULL,0,0),(1,19,'Ajouter un Utilisateur',NULL,0,0),(1,20,'Modifier un Utilisateur',NULL,0,0),(1,21,'Supprimer un Utilisateur',NULL,0,0),(1,22,'DÃ©tacher un Module',NULL,0,0),(1,23,'Supprimer un Module',NULL,0,0),(1,24,'Mettre Ã  jour la MÃ©tabase',NULL,0,0),(1,25,'Connexion Utilisateur',NULL,0,0),(1,26,'Erreur de Connexion',NULL,0,0),(1,27,'DÃ©placer un Utilisateur',NULL,0,0),(1,28,'Attacher un Utilisateur',NULL,0,0),(1,29,'DÃ©tacher un Utilisateur',NULL,0,0),(1,32,'Mettre Ã  jour un module',NULL,0,0);
/*!40000 ALTER TABLE `ploopi_mb_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_field`
--

DROP TABLE IF EXISTS `ploopi_mb_field`;
CREATE TABLE `ploopi_mb_field` (
  `tablename` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `type` varchar(50) default NULL,
  `visible` tinyint(1) unsigned default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tablename`,`name`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_field`
--

LOCK TABLES `ploopi_mb_field` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_field` DISABLE KEYS */;
INSERT INTO `ploopi_mb_field` VALUES ('dims_user_type','label','label','varchar(255)',1,1),('dims_user_type','id','id','int(10) unsigned',1,1),('dims_user_filter_rules','value','value','varchar(255)',1,1),('dims_user_filter_rules','operator','operator','varchar(4)',1,1),('dims_user_filter_rules','fieldname','fieldname','varchar(255)',1,1),('dims_user_filter_rules','condition','condition','varchar(20)',1,1),('dims_user_filter_rules','id_group','id_group','int(10) unsigned',1,1),('dims_user_filter_rules','id','id','int(10) unsigned',1,1),('dims_user_action_log','timestp','timestp','timestamp(14)',1,1),('dims_user_action_log','id_record','id_record','varchar(255)',1,1),('dims_user_action_log','id_module','id_module','int(10) unsigned',1,1),('dims_user_action_log','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_user_action_log','id_user','id_user','int(10) unsigned',1,1),('dims_user_action_log','id_action','id_action','int(10) unsigned',1,1),('dims_user','address','address','text',1,1),('dims_user','comments','comments','text',1,1),('dims_user','fax','fax','varchar(32)',1,1),('dims_user','phone','phone','varchar(32)',1,1),('dims_user','email','email','varchar(255)',1,1),('dims_user','date_expire','date_expire','varchar(14)',1,1),('dims_user','date_creation','date_creation','timestamp(14)',1,1),('dims_user','login','login','varchar(32)',1,1),('dims_user','password','password','varchar(32)',1,1),('dims_user','firstname','firstname','varchar(100)',1,1),('dims_user','lastname','lastname','varchar(100)',1,1),('dims_user','id_ldap','id_ldap','int(10) unsigned',1,1),('dims_user','id_type','id_type','int(10)',1,1),('dims_rule_type','label','label','varchar(100)',1,1),('dims_user','id','id','int(10) unsigned',1,1),('dims_rule','id_profile','id_profile','int(10) unsigned',1,1),('dims_rule_type','id','id','int(10) unsigned',1,1),('dims_rule','id_type','id_type','int(10) unsigned',1,1),('dims_rule','persistent','persistent','tinyint(1) unsigned',1,1),('dims_rule','operator','operator','varchar(25)',1,1),('dims_rule','value','value','varchar(255)',1,1),('dims_rule','field','field','varchar(255)',1,1),('dims_rule','id_group','id_group','int(10) unsigned',1,1),('dims_rule','label','label','varchar(255)',1,1),('dims_rule','id','id','int(10)',1,1),('dims_role_profile','id_profile','id_profile','int(10) unsigned',1,1),('dims_role_profile','id_role','id_role','int(10) unsigned',1,1),('dims_role_action','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_action','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_action','id_action','id_action','int(10) unsigned',1,1),('dims_action','description','description','blob',1,1),('dims_action','label','label','varchar(255)',1,1),('dims_cms_object','id','id','int(11) unsigned',1,1),('dims_cms_object','label','label','varchar(255)',1,1),('dims_cms_object','id_module_type','id_module_type','int(10)',1,1),('dims_cms_object','script','script','varchar(255)',1,1),('dims_connecteduser','ip','ip','varchar(255)',1,1),('dims_connecteduser','sid','sid','varchar(255)',1,1),('dims_connecteduser','user_id','user_id','int(10) unsigned',1,1),('dims_connecteduser','group_id','group_id','int(10) unsigned',1,1),('dims_connecteduser','module_id','module_id','int(10) unsigned',1,1),('dims_connecteduser','timestp','timestp','varchar(14)',1,1),('dims_group','id','id','int(10) unsigned',1,1),('dims_group','id_group','id_group','int(10) unsigned',1,1),('dims_group','code','code','varchar(64)',1,1),('dims_group','system','system','tinyint(1) unsigned',1,1),('dims_group','label','label','varchar(255)',1,1),('dims_group','protected','protected','tinyint(1) unsigned',1,1),('dims_group','iprules','iprules','text',1,1),('dims_group','parents','parents','varchar(100)',1,1),('dims_group','skin','skin','varchar(255)',1,1),('dims_group','macrules','macrules','text',1,1),('dims_group','depth','depth','int(10) unsigned',1,1),('dims_group','mustdefinerule','mustdefinerule','tinyint(1) unsigned',1,1),('dims_group_user','id_group','id_group','int(10) unsigned',1,1),('dims_group_user','id_user','id_user','int(10) unsigned',1,1),('dims_group_user','id_profile','id_profile','int(10) unsigned',1,1),('dims_group_user','adminlevel','adminlevel','tinyint(3) unsigned',1,1),('dims_group_user_role','id_user','id_user','int(10) unsigned',1,1),('dims_group_user_role','id_group','id_group','int(10) unsigned',1,1),('dims_group_user_role','id_role','id_role','int(10) unsigned',1,1),('dims_homepage_column','id','id','int(10) unsigned',1,1),('dims_homepage_column','id_line','id_line','int(10) unsigned',1,1),('dims_homepage_column','position','position','int(10) unsigned',1,1),('dims_homepage_column','size','size','int(10) unsigned',1,1),('dims_homepage_column','id_module','id_module','int(10) unsigned',1,1),('dims_homepage_column','border','border','tinyint(1) unsigned',1,1),('dims_homepage_column','title','title','varchar(255)',1,1),('dims_homepage_line','id','id','int(10) unsigned',1,1),('dims_homepage_line','id_group','id_group','int(10) unsigned',1,1),('dims_homepage_line','position','position','int(10) unsigned',1,1),('dims_homepage_line','id_user','id_user','int(10) unsigned',1,1),('dims_log','id','id','int(10) unsigned',1,1),('dims_log','request_method','request_method','varchar(255)',1,1),('dims_log','query_string','query_string','varchar(255)',1,1),('dims_log','document_root','document_root','varchar(255)',1,1),('dims_log','remote_port','remote_port','int(10) unsigned',1,1),('dims_log','remote_addr','remote_addr','varchar(255)',1,1),('dims_log','path_translated','path_translated','varchar(255)',1,1),('dims_log','script_filename','script_filename','varchar(255)',1,1),('dims_log','script_name','script_name','varchar(255)',1,1),('dims_log','request_uri','request_uri','varchar(255)',1,1),('dims_log','dims_groupid','dims_groupid','int(10)',1,1),('dims_log','dims_userid','dims_userid','int(10)',1,1),('dims_log','dims_moduleid','dims_moduleid','int(10)',1,1),('dims_log','system','system','varchar(255)',1,1),('dims_log','browser','browser','varchar(255)',1,1),('dims_log','date_year','date_year','int(10)',1,1),('dims_log','date_month','date_month','int(10)',1,1),('dims_log','date_hour','date_hour','int(10)',1,1),('dims_log','date_day','date_day','int(10)',1,1),('dims_log','date_minute','date_minute','int(10)',1,1),('dims_mb_field','tablename','tablename','varchar(100)',1,1),('dims_log','date_second','date_second','int(10)',1,1),('dims_mb_field','name','name','varchar(100)',1,1),('dims_mb_field','type','type','varchar(50)',1,1),('dims_mb_field','label','label','varchar(255)',1,1),('dims_mb_field','visible','visible','tinyint(1) unsigned',1,1),('dims_mb_field','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_mb_relation','fieldsrc','fieldsrc','varchar(100)',1,1),('dims_mb_relation','tablesrc','tablesrc','varchar(100)',1,1),('dims_mb_relation','tabledest','tabledest','varchar(100)',1,1),('dims_mb_relation','fielddest','fielddest','varchar(100)',1,1),('dims_mb_relation','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_mb_schema','tablesrc','tablesrc','varchar(100)',1,1),('dims_mb_schema','tabledest','tabledest','varchar(100)',1,1),('dims_mb_table','name','name','varchar(100)',1,1),('dims_mb_schema','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_mb_table','label','label','varchar(255)',1,1),('dims_mb_table','visible','visible','tinyint(1) unsigned',1,1),('dims_mb_table','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_module','label','label','varchar(100)',1,1),('dims_module','id','id','int(10) unsigned',1,1),('dims_module','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_module','id_group','id_group','int(10) unsigned',1,1),('dims_module','active','active','tinyint(1) unsigned',1,1),('dims_module','public','public','tinyint(1) unsigned',1,1),('dims_module','herited','herited','tinyint(1) unsigned',1,1),('dims_module','shared','shared','tinyint(1) unsigned',1,1),('dims_module','adminrestricted','adminrestricted','tinyint(1) unsigned',1,1),('dims_module','viewmode','viewmode','int(10) unsigned',1,1),('dims_module','transverseview','transverseview','tinyint(1) unsigned',1,1),('dims_module','autoconnect','autoconnect','tinyint(1) unsigned',1,1),('dims_module_group','id_module','id_module','int(10) unsigned',1,1),('dims_module_group','id_group','id_group','int(10) unsigned',1,1),('dims_module_group','blockposition','blockposition','char(10)',1,1),('dims_module_group','position','position','tinyint(2)',1,1),('dims_module_type','id','id','int(10) unsigned',1,1),('dims_module_type','label','label','varchar(100)',1,1),('dims_module_type','instanciable','instanciable','tinyint(1) unsigned',1,1),('dims_module_type','publicparam','publicparam','tinyint(1) unsigned',1,1),('dims_module_type','managecontent','managecontent','tinyint(1) unsigned',1,1),('dims_module_type','description','description','longtext',1,1),('dims_module_type','version','version','varchar(20)',1,1),('dims_module_type','author','author','varchar(255)',1,1),('dims_param_choice','id','id','int(10) unsigned',1,1),('dims_param_choice','id_param_type','id_param_type','int(10) unsigned',1,1),('dims_param_choice','value','value','varchar(100)',1,1),('dims_param_choice','displayed_value','displayed_value','varchar(100)',1,1),('dims_param_default','id_module','id_module','int(10) unsigned',1,1),('dims_param_default','id_param_type','id_param_type','int(10) unsigned',1,1),('dims_param_default','value','value','varchar(255)',1,1),('dims_param_group','id_param_type','id_param_type','int(10) unsigned',1,1),('dims_param_group','id_module','id_module','int(10)',1,1),('dims_param_group','id_group','id_group','int(10) unsigned',1,1),('dims_param_group','value','value','varchar(255)',1,1),('dims_param_type','id','id','int(10) unsigned',1,1),('dims_param_type','id_module_type','id_module_type','int(10) unsigned',1,1),('dims_param_type','label','label','varchar(100)',1,1),('dims_param_type','default_value','default_value','varchar(100)',1,1),('dims_param_type','public','public','tinyint(1) unsigned',1,1),('dims_param_type','description','description','longtext',1,1),('dims_param_type','displayed_label','displayed_label','varchar(100)',1,1),('dims_param_user','id_param_type','id_param_type','int(10) unsigned',1,1),('dims_param_user','id_module','id_module','int(10) unsigned',1,1),('dims_param_user','id_user','id_user','int(10) unsigned',1,1),('dims_param_user','value','value','varchar(255)',1,1),('dims_profile','id','id','int(10) unsigned',1,1),('dims_profile','id_group','id_group','int(10) unsigned',1,1),('dims_profile','label','label','varchar(255)',1,1),('dims_profile','description','description','blob',1,1),('dims_profile','def','def','tinyint(1) unsigned',1,1),('dims_profile','shared','shared','tinyint(1) unsigned',1,1),('dims_role','id','id','int(10) unsigned',1,1),('dims_role','id_module','id_module','int(10) unsigned',1,1),('dims_role','id_group','id_group','int(10) unsigned',1,1),('dims_role','label','label','varchar(255)',1,1),('dims_role','description','description','blob',1,1),('dims_role','def','def','tinyint(1) unsigned',1,1),('dims_role','shared','shared','tinyint(1) unsigned',1,1),('dims_role_action','id_role','id_role','int(10) unsigned',1,1),('dims_role_action','id_action','id_action','int(10) unsigned',1,1),('dims_user_type','displayed_label','displayed_label','varchar(255)',1,1),('dims_user_type_fields','id','id','int(10) unsigned',1,1),('dims_user_type_fields','id_type','id_type','int(10) unsigned',1,1),('dims_user_type_fields','label','label','varchar(100)',1,1),('dims_user_type_fields','type_field','type_field','varchar(100)',1,1),('dims_user_type_fields','size_field','size_field','int(10) unsigned',1,1),('dims_user_type_fields','pos','pos','int(10) unsigned',1,1),('dims_user_type_fields','valeurs','valeurs','longtext',1,1),('dims_user_type_fields','displayed_label','displayed_label','varchar(255)',1,1);
/*!40000 ALTER TABLE `ploopi_mb_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_object`
--

DROP TABLE IF EXISTS `ploopi_mb_object`;
CREATE TABLE `ploopi_mb_object` (
  `id` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `script` varchar(255) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_object`
--

LOCK TABLES `ploopi_mb_object` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_object` DISABLE KEYS */;
INSERT INTO `ploopi_mb_object` VALUES (2,'Groupe d\'Utilisateur','ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=org&groupid=<IDRECORD>',1),(1,'Espace de Travail','ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=work&workspaceid=<IDRECORD>',1);
/*!40000 ALTER TABLE `ploopi_mb_object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_relation`
--

DROP TABLE IF EXISTS `ploopi_mb_relation`;
CREATE TABLE `ploopi_mb_relation` (
  `tablesrc` varchar(100) default NULL,
  `fieldsrc` varchar(100) default NULL,
  `tabledest` varchar(100) default NULL,
  `fielddest` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_relation`
--

LOCK TABLES `ploopi_mb_relation` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_relation` DISABLE KEYS */;
INSERT INTO `ploopi_mb_relation` VALUES ('dims_mb_relation','tabledest','dims_mb_field','tablename',1),('dims_mb_relation','fieldsrc','dims_mb_field','name',1),('dims_mb_relation','tablesrc','dims_mb_field','tablename',1),('dims_user_filter_rules','id_group','dims_group','id',1),('dims_user_type_fields','id_type','dims_type','id',1),('dims_user_action_log','id_user','dims_user','id',1),('dims_user_action_log','id_record','dims_record','id',1),('dims_user_action_log','id_module_type','dims_module_type','id',1),('dims_user_action_log','id_module','dims_module','id',1),('dims_user','id_type','dims_type','id',1),('dims_user_action_log','id_action','dims_action','id',1),('dims_user','id_ldap','dims_ldap','id',1),('dims_mod_news','id_newscat','dims_newscat','id',1),('dims_role_action','id_action','dims_action','id',1),('dims_role_action','id_role','dims_role','id',1),('dims_role_action','id_module_type','dims_module_type','id',1),('dims_role','id_module','dims_module','id',1),('dims_role','id_group','dims_group','id',1),('dims_profile','id_group','dims_group','id',1),('dims_param_user','id_param_type','dims_param_type','id',1),('dims_param_user','id_module','dims_module','id',1),('dims_param_user','id_user','dims_user','id',1),('dims_param_type','id_module_type','dims_module_type','id',1),('dims_param_group','id_group','dims_group','id',1),('dims_param_group','id_param_type','dims_param_type','id',1),('dims_param_group','id_module','dims_module','id',1),('dims_param_default','id_module','dims_module','id',1),('dims_param_default','id_param_type','dims_param_type','id',1),('dims_param_choice','id_param_type','dims_param_type','id',1),('dims_module_group','id_group','dims_group','id',1),('dims_module_group','id_module','dims_module','id',1),('dims_module','id_group','dims_group','id',1),('dims_module','id_module_type','dims_module_type','id',1),('dims_mb_table','id_module_type','dims_module_type','id',1),('dims_mb_schema','id_module_type','dims_module_type','id',1),('dims_mb_relation','id_module_type','dims_module_type','id',1),('dims_mb_field','id_module_type','dims_module_type','id',1),('dims_homepage_line','id_group','dims_group','id',1),('dims_homepage_line','id_user','dims_user','id',1),('dims_homepage_column','id_line','dims_line','id',1),('dims_homepage_column','id_module','dims_module','id',1),('dims_group_user_role','id_group','dims_group','id',1),('dims_group_user_role','id_role','dims_role','id',1),('dims_group_user_role','id_user','dims_user','id',1),('dims_group_user','id_group','dims_group','id',1),('dims_group_user','id_profile','dims_profile','id',1),('dims_group_user','id_user','dims_user','id',1),('dims_cms_object','id_module_type','dims_module_type','id',1),('dims_action','id_module_type','dims_module_type','id',1),('dims_role_profile','id_profile','dims_profile','id',1),('dims_role_profile','id_role','dims_role','id',1),('dims_rule','id_group','dims_group','id',1),('dims_rule','id_profile','dims_profile','id',1),('dims_rule','id_type','dims_type','id',1),('dims_mb_relation','fielddest','dims_mb_field','name',1),('dims_mb_field','tablename','dims_mb_table','name',1),('dims_mb_schema','tablesrc','dims_mb_table','name',1),('dims_mb_schema','tabledest','dims_mb_table','name',1);
/*!40000 ALTER TABLE `ploopi_mb_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_schema`
--

DROP TABLE IF EXISTS `ploopi_mb_schema`;
CREATE TABLE `ploopi_mb_schema` (
  `tablesrc` varchar(100) NOT NULL default '',
  `tabledest` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tabledest`,`tablesrc`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_schema`
--

LOCK TABLES `ploopi_mb_schema` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_schema` DISABLE KEYS */;
INSERT INTO `ploopi_mb_schema` VALUES ('dims_role_action','dims_action',1),('dims_user_action_log','dims_action',1),('dims_group_user','dims_group',1),('dims_group_user_role','dims_group',1),('dims_homepage_line','dims_group',1),('dims_module','dims_group',1),('dims_module_group','dims_group',1),('dims_param_group','dims_group',1),('dims_profile','dims_group',1),('dims_role','dims_group',1),('dims_rule','dims_group',1),('dims_user_filter_rules','dims_group',1),('dims_user','dims_ldap',1),('dims_homepage_column','dims_line',1),('dims_homepage_column','dims_module',1),('dims_module_group','dims_module',1),('dims_param_default','dims_module',1),('dims_param_group','dims_module',1),('dims_param_user','dims_module',1),('dims_role','dims_module',1),('dims_user_action_log','dims_module',1),('dims_action','dims_module_type',1),('dims_cms_object','dims_module_type',1),('dims_mb_field','dims_module_type',1),('dims_mb_relation','dims_module_type',1),('dims_mb_schema','dims_module_type',1),('dims_mb_table','dims_module_type',1),('dims_module','dims_module_type',1),('dims_param_type','dims_module_type',1),('dims_role_action','dims_module_type',1),('dims_user_action_log','dims_module_type',1),('dims_mod_news','dims_newscat',1),('dims_param_choice','dims_param_type',1),('dims_param_default','dims_param_type',1),('dims_param_group','dims_param_type',1),('dims_param_user','dims_param_type',1),('dims_group_user','dims_profile',1),('dims_role_profile','dims_profile',1),('dims_rule','dims_profile',1),('dims_user_action_log','dims_record',1),('dims_group_user_role','dims_role',1),('dims_role_action','dims_role',1),('dims_role_profile','dims_role',1),('dims_rule','dims_type',1),('dims_user','dims_type',1),('dims_user_type_fields','dims_type',1),('dims_group_user','dims_user',1),('dims_group_user_role','dims_user',1),('dims_homepage_line','dims_user',1),('dims_param_user','dims_user',1),('dims_user_action_log','dims_user',1);
/*!40000 ALTER TABLE `ploopi_mb_schema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_table`
--

DROP TABLE IF EXISTS `ploopi_mb_table`;
CREATE TABLE `ploopi_mb_table` (
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `visible` tinyint(1) unsigned default '1',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_mb_table`
--

LOCK TABLES `ploopi_mb_table` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_table` DISABLE KEYS */;
INSERT INTO `ploopi_mb_table` VALUES ('dims_user','dims_user',1,1),('dims_rule_type','dims_rule_type',0,1),('dims_rule','dims_rule',0,1),('dims_action','dims_action',0,1),('dims_role_profile','dims_role_profile',0,1),('dims_cms_object','dims_cms_object',0,1),('dims_group','dims_group',1,1),('dims_connecteduser','dims_connecteduser',0,1),('dims_group_user','dims_group_user',0,1),('dims_group_user_role','dims_group_user_role',0,1),('dims_homepage_column','dims_homepage_column',0,1),('dims_homepage_line','dims_homepage_line',0,1),('dims_log','dims_log',0,1),('dims_mb_field','dims_mb_field',0,1),('dims_mb_relation','dims_mb_relation',0,1),('dims_mb_schema','dims_mb_schema',0,1),('dims_module','dims_module',0,1),('dims_mb_table','dims_mb_table',0,1),('dims_module_group','dims_module_group',0,1),('dims_module_type','dims_module_type',0,1),('dims_param_choice','dims_param_choice',0,1),('dims_param_default','dims_param_default',0,1),('dims_param_group','dims_param_group',0,1),('dims_param_type','dims_param_type',0,1),('dims_param_user','dims_param_user',0,1),('dims_profile','dims_profile',0,1),('dims_role','dims_role',0,1),('dims_role_action','dims_role_action',0,1),('dims_user_action_log','dims_user_action_log',0,1),('dims_user_filter_rules','dims_user_filter_rules',0,1),('dims_user_type','dims_user_type',0,1),('dims_user_type_fields','dims_user_type_fields',0,1);
/*!40000 ALTER TABLE `ploopi_mb_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_mb_wce_object`
--

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

--
-- Dumping data for table `ploopi_mb_wce_object`
--

LOCK TABLES `ploopi_mb_wce_object` WRITE;
/*!40000 ALTER TABLE `ploopi_mb_wce_object` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_mb_wce_object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_module`
--

DROP TABLE IF EXISTS `ploopi_module`;
CREATE TABLE `ploopi_module` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) default NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `public` tinyint(1) unsigned default '0',
  `shared` tinyint(1) unsigned default '0',
  `herited` tinyint(1) unsigned default '0',
  `adminrestricted` tinyint(1) unsigned default '0',
  `viewmode` int(10) unsigned default '1',
  `transverseview` tinyint(1) unsigned default '0',
  `autoconnect` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_module`
--

LOCK TABLES `ploopi_module` WRITE;
/*!40000 ALTER TABLE `ploopi_module` DISABLE KEYS */;
INSERT INTO `ploopi_module` VALUES (1,'SystÃšme',1,0,1,0,0,0,0,1,0,0);
/*!40000 ALTER TABLE `ploopi_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_module_type`
--

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

--
-- Dumping data for table `ploopi_module_type`
--

LOCK TABLES `ploopi_module_type` WRITE;
/*!40000 ALTER TABLE `ploopi_module_type` DISABLE KEYS */;
INSERT INTO `ploopi_module_type` VALUES (1,'system',1,0,NULL,'1.0 Alpha 1','Netlor Concept','20070330000000');
/*!40000 ALTER TABLE `ploopi_module_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_module_workspace`
--

DROP TABLE IF EXISTS `ploopi_module_workspace`;
CREATE TABLE `ploopi_module_workspace` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `position` tinyint(2) NOT NULL default '0',
  `blockposition` char(10) NOT NULL default 'left',
  PRIMARY KEY  (`id_workspace`,`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_module_workspace`
--

LOCK TABLES `ploopi_module_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_module_workspace` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_module_workspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_param_choice`
--

DROP TABLE IF EXISTS `ploopi_param_choice`;
CREATE TABLE `ploopi_param_choice` (
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `displayed_value` varchar(100) default NULL,
  KEY `id_module_type` (`id_module_type`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_param_choice`
--

LOCK TABLES `ploopi_param_choice` WRITE;
/*!40000 ALTER TABLE `ploopi_param_choice` DISABLE KEYS */;
INSERT INTO `ploopi_param_choice` VALUES (1,'system_usemacrules','0','non'),(1,'system_usemacrules','1','oui'),(1,'system_recordstats','0','non'),(1,'system_recordstats','1','oui'),(1,'system_set_cache','0','non'),(1,'system_set_cache','1','oui'),(1,'system_generate_htpasswd','0','non'),(1,'system_generate_htpasswd','1','oui'),(1,'system_use_profiles','0','non'),(1,'system_use_profiles','1','oui'),(1,'system_same_login','1','oui'),(1,'system_same_login','0','non');
/*!40000 ALTER TABLE `ploopi_param_choice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_param_default`
--

DROP TABLE IF EXISTS `ploopi_param_default`;
CREATE TABLE `ploopi_param_default` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `value` text NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`name`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_param_default`
--

LOCK TABLES `ploopi_param_default` WRITE;
/*!40000 ALTER TABLE `ploopi_param_default` DISABLE KEYS */;
INSERT INTO `ploopi_param_default` VALUES (1,'system_recordstats','0',1),(1,'system_usemacrules','0',1),(1,'system_set_cache','0',1),(1,'system_generate_htpasswd','0',1),(1,'system_use_profiles','0',1),(1,'system_language','french',1),(1,'system_same_login','0',1),(1,'system_proxy_host','',1),(1,'system_proxy_port','',1),(1,'system_proxy_user','',1),(1,'system_proxy_pass','',1);
/*!40000 ALTER TABLE `ploopi_param_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_param_type`
--

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

--
-- Dumping data for table `ploopi_param_type`
--

LOCK TABLES `ploopi_param_type` WRITE;
/*!40000 ALTER TABLE `ploopi_param_type` DISABLE KEYS */;
INSERT INTO `ploopi_param_type` VALUES (1,'system_recordstats','1',0,'','Enregistrement des stats'),(1,'system_usemacrules','0',0,'','Activer le filtrage par Adresse MAC'),(1,'system_language_default','french',0,'','Langage par dÃ©faut'),(1,'system_set_cache','0',0,'','Activer le Cache'),(1,'system_groupdepth0_label','',0,'','IntitulÃ© des Groupes de Profondeur 0'),(1,'system_groupdepth1_label','',0,'','IntitulÃ© des Groupes de Profondeur 1'),(1,'system_groupdepth2_label','',0,'','IntitulÃ© des Groupes de Profondeur 2'),(1,'system_groupdepth3_label','',0,'','IntitulÃ© des Groupes de Profondeur 3'),(1,'system_groupdepth4_label','',0,'','IntitulÃ© des Groupes de Profondeur 4'),(1,'system_groupdepth5_label','',0,'','IntitulÃ© des Groupes de Profondeur 5'),(1,'system_groupdepth6_label','',0,'','IntitulÃ© des Groupes de Profondeur 6'),(1,'system_groupdepth7_label','',0,'','IntitulÃ© des Groupes de Profondeur 7'),(1,'system_groupdepth8_label','',0,'','IntitulÃ© des Groupes de Profondeur 8'),(1,'system_groupdepth9_label','',0,'','IntitulÃ© des Groupes de Profondeur 9'),(1,'system_generate_htpasswd','1',0,'','GÃ©nÃ©rer un fichier htpasswd'),(1,'showblock','1',1,'','Afficher le bloc'),(1,'showmenu','1',1,'','Visible dans les modules'),(1,'system_use_profiles','0',0,NULL,'Utiliser les Profils (Utilisateurs)'),(1,'system_language','',1,NULL,'Langue du systÃšme'),(1,'system_same_login','0',0,NULL,'Utiliser des logins identiques (fortement dÃ©conseillÃ©)'),(1,'system_proxy_host','',0,'','Adresse du proxy pour les requÃªtes sortantes'),(1,'system_proxy_port','',0,'','Port du proxy pour les requÃªtes sortantes'),(1,'system_proxy_user','',0,'','Utilisateur du proxy pour les requÃªtes sortantes'),(1,'system_proxy_pass','',0,'','Mot de Passe du proxy pour les requÃªtes sortantes');
/*!40000 ALTER TABLE `ploopi_param_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_param_user`
--

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

--
-- Dumping data for table `ploopi_param_user`
--

LOCK TABLES `ploopi_param_user` WRITE;
/*!40000 ALTER TABLE `ploopi_param_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_param_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_param_workspace`
--

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

--
-- Dumping data for table `ploopi_param_workspace`
--

LOCK TABLES `ploopi_param_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_param_workspace` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_param_workspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_profile`
--

DROP TABLE IF EXISTS `ploopi_profile`;
CREATE TABLE `ploopi_profile` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_workspace` int(10) default NULL,
  `label` varchar(255) NOT NULL default '',
  `description` blob,
  `def` tinyint(1) unsigned NOT NULL default '0',
  `shared` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_profile`
--

LOCK TABLES `ploopi_profile` WRITE;
/*!40000 ALTER TABLE `ploopi_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_role`
--

DROP TABLE IF EXISTS `ploopi_role`;
CREATE TABLE `ploopi_role` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned default '0',
  `id_workspace` int(10) default NULL,
  `label` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `def` tinyint(1) unsigned NOT NULL default '0',
  `shared` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_role`
--

LOCK TABLES `ploopi_role` WRITE;
/*!40000 ALTER TABLE `ploopi_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_role_action`
--

DROP TABLE IF EXISTS `ploopi_role_action`;
CREATE TABLE `ploopi_role_action` (
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_action` int(10) unsigned NOT NULL default '0',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_action`,`id_module_type`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_role_action`
--

LOCK TABLES `ploopi_role_action` WRITE;
/*!40000 ALTER TABLE `ploopi_role_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_role_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_role_profile`
--

DROP TABLE IF EXISTS `ploopi_role_profile`;
CREATE TABLE `ploopi_role_profile` (
  `id_role` int(10) unsigned NOT NULL default '0',
  `id_profile` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_profile`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_role_profile`
--

LOCK TABLES `ploopi_role_profile` WRITE;
/*!40000 ALTER TABLE `ploopi_role_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_role_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_session`
--

DROP TABLE IF EXISTS `ploopi_session`;
CREATE TABLE `ploopi_session` (
  `id` varchar(32) NOT NULL,
  `access` int(10) unsigned default NULL,
  `data` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_session`
--

LOCK TABLES `ploopi_session` WRITE;
/*!40000 ALTER TABLE `ploopi_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_share`
--

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
  KEY `search` (`id_module`,`id_object`,`id_record`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_share`
--

LOCK TABLES `ploopi_share` WRITE;
/*!40000 ALTER TABLE `ploopi_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_tag`
--

DROP TABLE IF EXISTS `ploopi_tag`;
CREATE TABLE `ploopi_tag` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(64) NOT NULL default '',
  `id_user` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_tag`
--

LOCK TABLES `ploopi_tag` WRITE;
/*!40000 ALTER TABLE `ploopi_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_ticket`
--

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
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_ticket`
--

LOCK TABLES `ploopi_ticket` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_ticket_dest`
--

DROP TABLE IF EXISTS `ploopi_ticket_dest`;
CREATE TABLE `ploopi_ticket_dest` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  KEY `id_user` (`id_user`),
  KEY `id_ticket` (`id_ticket`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_ticket_dest`
--

LOCK TABLES `ploopi_ticket_dest` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_dest` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_dest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_ticket_status`
--

DROP TABLE IF EXISTS `ploopi_ticket_status`;
CREATE TABLE `ploopi_ticket_status` (
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  `timestp` varchar(14) NOT NULL default '',
  KEY `id_ticket` (`id_ticket`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_ticket_status`
--

LOCK TABLES `ploopi_ticket_status` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_ticket_watch`
--

DROP TABLE IF EXISTS `ploopi_ticket_watch`;
CREATE TABLE `ploopi_ticket_watch` (
  `id_ticket` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `notify` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_ticket`,`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_ticket_watch`
--

LOCK TABLES `ploopi_ticket_watch` WRITE;
/*!40000 ALTER TABLE `ploopi_ticket_watch` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_ticket_watch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_user`
--

DROP TABLE IF EXISTS `ploopi_user`;
CREATE TABLE `ploopi_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_type` int(10) default '-1',
  `id_ldap` int(10) unsigned default NULL,
  `lastname` varchar(100) default NULL,
  `firstname` varchar(100) default NULL,
  `login` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `date_creation` varchar(14) default NULL,
  `date_expire` varchar(14) default '00000000000000',
  `email` varchar(255) default NULL,
  `phone` varchar(32) default NULL,
  `fax` varchar(32) default NULL,
  `comments` text,
  `address` text,
  `mobile` varchar(32) default NULL,
  `service` varchar(64) default NULL,
  `function` varchar(64) default NULL,
  `postalcode` varchar(16) default NULL,
  `city` varchar(64) default NULL,
  `country` varchar(64) default NULL,
  `ticketsbyemail` tinyint(1) unsigned NOT NULL default '0',
  `color` varchar(16) NOT NULL default '',
  `timezone` double NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login_unique` (`login`),
  FULLTEXT KEY `FT` (`city`,`country`,`function`,`firstname`,`lastname`,`service`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_user`
--

LOCK TABLES `ploopi_user` WRITE;
/*!40000 ALTER TABLE `ploopi_user` DISABLE KEYS */;
INSERT INTO `ploopi_user` VALUES (2,1,0,'Administrateur','','admin','feee4f3ca6345d6562972e7c3a9dad9b','20040701101222','00000000000000','','','','','','','','','','','',0,'',0);
/*!40000 ALTER TABLE `ploopi_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_user_action_log`
--

DROP TABLE IF EXISTS `ploopi_user_action_log`;
CREATE TABLE `ploopi_user_action_log` (
  `id_user` int(10) unsigned default '0',
  `id_action` int(10) unsigned default '0',
  `id_module_type` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) default NULL,
  `ip` varchar(15) NOT NULL default '',
  `timestp` varchar(14) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_user_action_log`
--

LOCK TABLES `ploopi_user_action_log` WRITE;
/*!40000 ALTER TABLE `ploopi_user_action_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_user_action_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_user_type`
--

DROP TABLE IF EXISTS `ploopi_user_type`;
CREATE TABLE `ploopi_user_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `displayed_label` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_user_type`
--

LOCK TABLES `ploopi_user_type` WRITE;
/*!40000 ALTER TABLE `ploopi_user_type` DISABLE KEYS */;
INSERT INTO `ploopi_user_type` VALUES (1,'','Non dÃ©fini');
/*!40000 ALTER TABLE `ploopi_user_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_user_type_fields`
--

DROP TABLE IF EXISTS `ploopi_user_type_fields`;
CREATE TABLE `ploopi_user_type_fields` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_type` int(10) unsigned NOT NULL default '0',
  `label` varchar(100) NOT NULL default '',
  `type_field` varchar(100) NOT NULL default '',
  `size_field` int(10) unsigned default NULL,
  `pos` int(10) unsigned default '0',
  `valeurs` longtext,
  `displayed_label` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`id_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_user_type_fields`
--

LOCK TABLES `ploopi_user_type_fields` WRITE;
/*!40000 ALTER TABLE `ploopi_user_type_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_user_type_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workflow`
--

DROP TABLE IF EXISTS `ploopi_workflow`;
CREATE TABLE `ploopi_workflow` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_record` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) unsigned NOT NULL default '0',
  `type_workflow` varchar(16) default '0',
  `id_workflow` int(10) unsigned default '0',
  `id_module_type` int(10) default '0',
  PRIMARY KEY  (`id`),
  KEY `search` (`id_module`,`id_object`,`id_record`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workflow`
--

LOCK TABLES `ploopi_workflow` WRITE;
/*!40000 ALTER TABLE `ploopi_workflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workspace`
--

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
  `admin_template` varchar(255) default 'NULL',
  `web_template` varchar(255) NOT NULL default '',
  `depth` int(10) NOT NULL default '0',
  `mustdefinerule` tinyint(1) unsigned default '0',
  `admin` tinyint(1) unsigned default '1',
  `public` tinyint(1) unsigned default '0',
  `web` tinyint(1) unsigned default '0',
  `admin_domainlist` longtext,
  `title` varchar(255) NOT NULL default '',
  `meta_description` longtext NOT NULL,
  `meta_keywords` longtext NOT NULL,
  `meta_author` varchar(255) NOT NULL default '',
  `meta_copyright` varchar(255) NOT NULL default '',
  `meta_robots` varchar(255) NOT NULL default 'index, follow, all',
  `web_domainlist` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workspace`
--

LOCK TABLES `ploopi_workspace` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace` DISABLE KEYS */;
INSERT INTO `ploopi_workspace` VALUES (2,1,'Espace Principal','',0,0,'0;1','','','dims','',2,0,1,0,0,'*\r\n','','','','','','','*'),(1,0,'system',NULL,1,0,'0',NULL,NULL,'dims','',1,0,1,0,0,NULL,'','','','','','index, follow, all','');
/*!40000 ALTER TABLE `ploopi_workspace` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workspace_group`
--

DROP TABLE IF EXISTS `ploopi_workspace_group`;
CREATE TABLE `ploopi_workspace_group` (
  `id_group` int(10) default NULL,
  `id_workspace` int(10) default NULL,
  `id_profile` int(10) unsigned default '0',
  `adminlevel` tinyint(3) unsigned default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workspace_group`
--

LOCK TABLES `ploopi_workspace_group` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workspace_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workspace_group_role`
--

DROP TABLE IF EXISTS `ploopi_workspace_group_role`;
CREATE TABLE `ploopi_workspace_group_role` (
  `id_group` int(10) default NULL,
  `id_workspace` int(10) default NULL,
  `id_role` int(10) unsigned default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workspace_group_role`
--

LOCK TABLES `ploopi_workspace_group_role` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_group_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `ploopi_workspace_group_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workspace_user`
--

DROP TABLE IF EXISTS `ploopi_workspace_user`;
CREATE TABLE `ploopi_workspace_user` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_profile` int(10) unsigned NOT NULL default '0',
  `adminlevel` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`id_user`,`id_workspace`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workspace_user`
--

LOCK TABLES `ploopi_workspace_user` WRITE;
/*!40000 ALTER TABLE `ploopi_workspace_user` DISABLE KEYS */;
INSERT INTO `ploopi_workspace_user` VALUES (2,2,1,99);
/*!40000 ALTER TABLE `ploopi_workspace_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ploopi_workspace_user_role`
--

DROP TABLE IF EXISTS `ploopi_workspace_user_role`;
CREATE TABLE `ploopi_workspace_user_role` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) NOT NULL default '0',
  `id_role` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_user`,`id_workspace`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ploopi_workspace_user_role`
--

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

-- Dump completed on 2008-01-21 18:42:33
