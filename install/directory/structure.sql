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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_directory_favorites`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_favorites` (
  `id_contact` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ploopi_user` int(10) unsigned NOT NULL default '0',
  `id_list` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_contact`,`id_user`,`id_ploopi_user`,`id_list`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_directory_speeddialing`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_speeddialing` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `number` varchar(32) NOT NULL,
  `shortnumber` varchar(16) NOT NULL,
  `heading` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;