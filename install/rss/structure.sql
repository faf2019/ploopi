DROP TABLE IF EXISTS `ploopi_mod_rss_cat`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_rss_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(255) default NULL,
  `timestamp` bigint(14) unsigned NOT NULL default '0',
  `title` varchar(100) default NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_entry`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_rss_entry` (
  `id` varchar(255) NOT NULL,
  `id_feed` int(10) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `link` varchar(255) default 'http://',
  `author` varchar(255) default '',
  `subtitle` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `content` longtext,
  `published` varchar(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `title` (`title`),
  KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_feed`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_rss_feed` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default 'http://',
  `title` varchar(255) NOT NULL default 'channel_title',
  `link` varchar(255) NOT NULL default '',
  `subtitle` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `updated` bigint(14) unsigned NOT NULL default '0',
  `default` tinyint(1) unsigned NOT NULL default '0',
  `lastvisit` varchar(14) NOT NULL default '',
  `revisit` int(10) unsigned NOT NULL default '0',
  `updating_cache` tinyint(1) unsigned NOT NULL default '0',
  `error` tinyint(1) unsigned NOT NULL default '0',
  `id_cat` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_pref`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_rss_pref` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_feed` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`id_user`,`id_feed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_request`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_rss_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `request` varchar(255) default NULL,
  `id_cat` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

UPDATE `ploopi_mod_rss_entry` SET id = md5( id );
ALTER TABLE `ploopi_mod_rss_entry` CHANGE `id` `id` CHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_mod_rss_entry` ADD `id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
