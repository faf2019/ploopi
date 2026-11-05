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
  `building` varchar(255) NOT NULL,
  `floor` varchar(32) NOT NULL,
  `office` varchar(255) NOT NULL,
  `civility` varchar(16) NOT NULL,
  `id_heading` int(10) unsigned NOT NULL default '0',
  `rank` varchar(32) NOT NULL,
  `position` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  FULLTEXT KEY `FT` (`lastname`,`firstname`,`city`,`country`,`service`,`function`,`number`,`office`,`comments`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_directory_favorites`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_favorites` (
  `id_contact` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ploopi_user` int(10) unsigned NOT NULL default '0',
  `id_list` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_contact`,`id_user`,`id_ploopi_user`,`id_list`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_directory_heading`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_heading` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `position` int(10) unsigned NOT NULL default '0',
  `phone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `address` varchar(255) NOT NULL,
  `postalcode` varchar(32) NOT NULL,
  `city` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL,
  `id_heading` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_rubrique` (`id_heading`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_directory_speeddialing`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_speeddialing` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `number` varchar(32) NOT NULL,
  `shortnumber` varchar(16) NOT NULL,
  `heading` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

UPDATE `ploopi_mod_directory_speeddialing` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_directory_speeddialing` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_speeddialing` SET `number` = '' WHERE ISNULL(`number`);
ALTER TABLE `ploopi_mod_directory_speeddialing` CHANGE `number` `number` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_speeddialing` SET `shortnumber` = '' WHERE ISNULL(`shortnumber`);
ALTER TABLE `ploopi_mod_directory_speeddialing` CHANGE `shortnumber` `shortnumber` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_speeddialing` SET `heading` = '' WHERE ISNULL(`heading`);
ALTER TABLE `ploopi_mod_directory_speeddialing` CHANGE `heading` `heading` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `lastname` = '' WHERE ISNULL(`lastname`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `lastname` `lastname` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `firstname` = '' WHERE ISNULL(`firstname`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `firstname` `firstname` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `service` = '' WHERE ISNULL(`service`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `service` `service` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `function` = '' WHERE ISNULL(`function`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `function` `function` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `phone` = '' WHERE ISNULL(`phone`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `phone` `phone` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `mobile` = '' WHERE ISNULL(`mobile`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `mobile` `mobile` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `fax` = '' WHERE ISNULL(`fax`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `fax` `fax` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `address` = '' WHERE ISNULL(`address`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `address` `address` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `postalcode` = '' WHERE ISNULL(`postalcode`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `postalcode` `postalcode` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `city` = '' WHERE ISNULL(`city`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `city` `city` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `country` = '' WHERE ISNULL(`country`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `country` `country` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `comments` = '' WHERE ISNULL(`comments`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `comments` `comments` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `number` = '' WHERE ISNULL(`number`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `number` `number` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `building` = '' WHERE ISNULL(`building`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `building` `building` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `floor` = '' WHERE ISNULL(`floor`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `floor` `floor` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `office` = '' WHERE ISNULL(`office`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `office` `office` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `civility` = '' WHERE ISNULL(`civility`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `civility` `civility` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_contact` SET `rank` = '' WHERE ISNULL(`rank`);
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `rank` `rank` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `description` `description` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `phone` = '' WHERE ISNULL(`phone`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `phone` `phone` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `fax` = '' WHERE ISNULL(`fax`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `fax` `fax` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `address` = '' WHERE ISNULL(`address`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `address` `address` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `postalcode` = '' WHERE ISNULL(`postalcode`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `postalcode` `postalcode` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `city` = '' WHERE ISNULL(`city`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `city` `city` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_heading` SET `country` = '' WHERE ISNULL(`country`);
ALTER TABLE `ploopi_mod_directory_heading` CHANGE `country` `country` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_directory_list` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_directory_list` CHANGE `label` `label` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
