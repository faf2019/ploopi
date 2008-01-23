DROP TABLE IF EXISTS `ploopi_mod_directory_contact`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_contact` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lastname` varchar(255) default NULL,
  `firstname` varchar(255) default NULL,
  `service` varchar(255) default NULL,
  `function` varchar(255) default NULL,
  `phone` varchar(32) default NULL,
  `mobile` varchar(32) default NULL,
  `fax` varchar(32) default NULL,
  `email` varchar(255) default NULL,
  `address` varchar(255) default NULL,
  `postalcode` varchar(32) default NULL,
  `city` varchar(64) default NULL,
  `country` varchar(64) default NULL,
  `comments` longtext,
  `level` varchar(10) default NULL,
  `id_dims_user` int(10) unsigned default NULL,
  `date_create` varchar(14) default NULL,
  `date_modify` varchar(14) default NULL,
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  FULLTEXT KEY `FT` (`lastname`,`firstname`,`service`,`city`,`country`,`function`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_directory_favorites`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_favorites` (
  `id_contact` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_ploopi_user` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_contact`,`id_user`,`id_ploopi_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
