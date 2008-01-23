DROP TABLE IF EXISTS `dims_documents_ext`;
CREATE TABLE IF NOT EXISTS `dims_documents_ext` (
  `ext` varchar(10) default NULL,
  `filetype` varchar(16) default NULL,
  KEY `ext` (`ext`),
  KEY `filetype` (`filetype`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `dims_documents_file`;
CREATE TABLE IF NOT EXISTS `dims_documents_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
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
) TYPE=MyISAM;

DROP TABLE IF EXISTS `dims_documents_folder`;
CREATE TABLE IF NOT EXISTS `dims_documents_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `parents` varchar(255) default '0',
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `nbelements` int(10) unsigned NOT NULL default '0',
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
) TYPE=MyISAM;


INSERT INTO `dims_documents_ext` (`ext`, `filetype`) VALUES 
('odt', 'document'),
('doc', 'document'),
('xls', 'spreadsheet'),
('mp3', 'audio'),
('wav', 'audio'),
('ogg', 'audio'),
('jpg', 'image'),
('jpeg', 'image'),
('png', 'image'),
('gif', 'image'),
('psd', 'image'),
('xcf', 'image'),
('svg', 'image'),
('pdf', 'document'),
('avi', 'video'),
('wmv', 'video'),
('ogm', 'video'),
('mpg', 'video'),
('mpeg', 'video'),
('zip', 'archive'),
('tgz', 'archive'),
('gz', 'archive'),
('rar', 'archive'),
('bz2', 'archive'),
('ace', 'archive');


ALTER TABLE `dims_param_default` CHANGE `value` `value` VARCHAR(255);

ALTER TABLE `dims_group` ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_group` ADD `shared` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_group` DROP `typegroup`;
UPDATE `dims_group` SET id_workspace = 1 WHERE depth = 2 AND id_workspace = 0;

CREATE TABLE `phpdig_clicks` (
  `c_num` mediumint(9) NOT NULL,
  `c_url` varchar(255) NOT NULL default '',
  `c_val` varchar(255) NOT NULL default '',
  `c_time` timestamp NOT NULL
) TYPE=MyISAM;

CREATE TABLE `phpdig_engine` (
  `spider_id` mediumint(9) NOT NULL default '0',
  `key_id` mediumint(9) NOT NULL default '0',
  `weight` smallint(4) NOT NULL default '0',
  KEY `key_id` (`key_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_excludes` (
  `ex_id` mediumint(11) NOT NULL auto_increment,
  `ex_site_id` mediumint(9) NOT NULL,
  `ex_path` text NOT NULL,
  PRIMARY KEY  (`ex_id`),
  KEY `ex_site_id` (`ex_site_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_includes` (
  `in_id` mediumint(11) NOT NULL auto_increment,
  `in_site_id` mediumint(9) NOT NULL,
  `in_path` text NOT NULL,
  PRIMARY KEY  (`in_id`),
  KEY `in_site_id` (`in_site_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_keywords` (
  `key_id` int(9) NOT NULL auto_increment,
  `twoletters` char(2) NOT NULL,
  `keyword` varchar(64) NOT NULL,
  PRIMARY KEY  (`key_id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `twoletters` (`twoletters`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_logs` (
  `l_id` mediumint(9) NOT NULL auto_increment,
  `l_includes` varchar(255) NOT NULL default '',
  `l_excludes` varchar(127) default NULL,
  `l_num` mediumint(9) default NULL,
  `l_mode` char(1) default NULL,
  `l_ts` timestamp NOT NULL,
  `l_time` float NOT NULL default '0',
  PRIMARY KEY  (`l_id`),
  KEY `l_includes` (`l_includes`),
  KEY `l_excludes` (`l_excludes`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_sites` (
  `site_id` mediumint(9) NOT NULL auto_increment,
  `site_url` varchar(127) NOT NULL,
  `upddate` timestamp NOT NULL,
  `username` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `port` smallint(6) default NULL,
  `locked` tinyint(1) NOT NULL default '0',
  `stopped` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`site_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_site_page` (
  `site_id` int(4) NOT NULL,
  `days` int(4) NOT NULL default '0',
  `links` int(4) NOT NULL default '5',
  `depth` int(4) NOT NULL default '5',
  PRIMARY KEY  (`site_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_spider` (
  `spider_id` mediumint(9) NOT NULL auto_increment,
  `file` varchar(127) NOT NULL,
  `first_words` mediumtext NOT NULL,
  `upddate` timestamp NOT NULL,
  `md5` varchar(50) default NULL,
  `site_id` mediumint(9) NOT NULL default '0',
  `path` varchar(127) NOT NULL,
  `num_words` int(11) NOT NULL default '1',
  `last_modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `filesize` int(11) NOT NULL default '0',
  PRIMARY KEY  (`spider_id`),
  KEY `site_id` (`site_id`)
) TYPE=MyISAM;

CREATE TABLE `phpdig_tempspider` (
  `file` text NOT NULL,
  `id` mediumint(11) NOT NULL auto_increment,
  `level` tinyint(6) NOT NULL default '0',
  `path` text NOT NULL,
  `site_id` mediumint(9) NOT NULL default '0',
  `indexed` tinyint(1) NOT NULL default '0',
  `upddate` timestamp NOT NULL,
  `error` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `site_id` (`site_id`)
) TYPE=MyISAM;

ALTER TABLE `dims_workspace` DROP `typegroup`;
ALTER TABLE `dims_documents_folder` ADD `system` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `nbelements` ;
ALTER TABLE `dims_documents_file` ADD `label` VARCHAR( 255 ) NOT NULL AFTER `name` ;

INSERT INTO `dims_mb_action` (`id_module_type` ,`id_action` ,`label` ,`description` ,`id_workspace` ,`id_object`) VALUES ('1', '32', 'Mettre à jour un module', NULL , '0', '0');

ALTER TABLE `dims_documents_file` ADD `ref` VARCHAR( 32 ) NOT NULL AFTER `description` ,
ADD `timestp_file` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ref` ;
UPDATE `dims_module_type` SET `version` = '3.0' WHERE `dims_module_type`.`id` =1;
