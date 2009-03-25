DROP TABLE IF EXISTS `ploopi_mod_directory_contact`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_contact` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lastname` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `service` varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postalcode` varchar(32) NOT NULL,
  `city` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL,
  `comments` longtext NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  `number` varchar(255) NOT NULL,
  `office` varchar(255) NOT NULL,
  `civility` varchar(16) NOT NULL,
  `id_heading` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  FULLTEXT KEY `FT` (`lastname`,`firstname`,`city`,`country`,`service`,`function`,`number`,`office`,`comments`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_directory_favorites`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_favorites` (
  `id_contact` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ploopi_user` int(10) unsigned NOT NULL default '0',
  `id_list` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_contact`,`id_user`,`id_ploopi_user`,`id_list`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_directory_heading`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_heading` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `position` int(10) unsigned NOT NULL default '0',
  `id_heading` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_rubrique` (`id_heading`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_directory_list`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_list` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(100) NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


ALTER TABLE `ploopi_mod_directory_contact` ADD `building` VARCHAR( 255 ) NOT NULL AFTER `number` ;
ALTER TABLE `ploopi_mod_directory_contact` ADD `floor` VARCHAR( 32 ) NOT NULL AFTER `building` ;
ALTER TABLE `ploopi_mod_directory_contact` ADD `rank` VARCHAR( 32 ) NOT NULL ;

ALTER TABLE `ploopi_mod_directory_contact` ADD `position` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_directory_heading` ADD `phone` VARCHAR( 32 ) NOT NULL AFTER `position` ,
ADD `fax` VARCHAR( 32 ) NOT NULL AFTER `phone` ,
ADD `address` VARCHAR( 255 ) NOT NULL AFTER `fax` ,
ADD `postalcode` VARCHAR( 32 ) NOT NULL AFTER `address` ,
ADD `city` VARCHAR( 64 ) NOT NULL AFTER `postalcode` ,
ADD `country` VARCHAR( 64 ) NOT NULL AFTER `city` ;