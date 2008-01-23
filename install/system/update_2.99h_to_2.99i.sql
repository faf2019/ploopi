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

UPDATE `dims_module_type` SET `version` = '2.99i' WHERE `dims_module_type`.`id` =1;
