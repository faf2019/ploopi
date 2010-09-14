DROP TABLE IF EXISTS `ploopi_mod_dbreport_query`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_query` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `standard` tinyint(1) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_queryfield`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_queryfield` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tablename` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `fieldname` varchar(100) default NULL,
  `label` varchar(100) default NULL,
  `function` varchar(255) default NULL,
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `sort` varchar(20) default NULL,
  `criteria` varchar(100) default NULL,
  `type_criteria` varchar(20) default NULL,
  `or` varchar(100) default NULL,
  `type_or` varchar(20) default NULL,
  `intervals` varchar(255) default NULL,
  `operation` varchar(16) default NULL,
  `position` int(10) unsigned NOT NULL default '0',
  `series` tinyint(1) unsigned NOT NULL default '0',
  `id_query` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_query` (`id_query`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_querytable`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_querytable` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tablename` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  `id_query` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_query` (`id_query`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `ws_id` VARCHAR( 32 ) NOT NULL AFTER `standard` ,
ADD `ws_code` VARCHAR( 32 ) NOT NULL AFTER `ws_id` ,
ADD `ws_ip` VARCHAR( 16 ) NOT NULL AFTER `ws_code` ;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_query_module_type`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_query_module_type` (
  `id_query` int(10) unsigned NOT NULL default '0',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_query`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `ws_activated` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `standard` ;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_queryrelation`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_queryrelation` (
  `id_query` int(10) unsigned NOT NULL default '0',
  `tablename_src` varchar(100) NOT NULL,
  `tablename_dest` varchar(100) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_query`,`tablename_src`,`tablename_dest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `timestp_update` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_dbreport_query` ADD `locked` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ws_ip` ;
ALTER TABLE `ploopi_mod_dbreport_query` ADD INDEX ( `locked` ) ;
