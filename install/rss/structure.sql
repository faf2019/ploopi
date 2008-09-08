DROP TABLE IF EXISTS `ploopi_mod_rss_cat`;
CREATE TABLE `ploopi_mod_rss_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(255) default NULL,
  `timestamp` bigint(14) unsigned NOT NULL default '0',
  `title` varchar(100) default NULL,
  `limit` tinyint(4) unsigned NOT NULL default '0',
  `tpl_tag` varchar(255) default NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_entry`;
CREATE TABLE `ploopi_mod_rss_entry` (
  `id` char(32) NOT NULL,
  `id_feed` int(10) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `link` varchar(255) default 'http://',
  `author` varchar(255) default '',
  `subtitle` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `content` longtext,
  `published` int(20) unsigned NOT NULL,
  `published_day` int(20) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`),
  KEY `link` (`link`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_feed`;
CREATE TABLE `ploopi_mod_rss_feed` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default 'http://',
  `title` varchar(255) NOT NULL default 'channel_title',
  `link` varchar(255) NOT NULL default '',
  `subtitle` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `limit` tinyint(4) unsigned NOT NULL default '0',
  `tpl_tag` varchar(255) default NULL,
  `updated` bigint(14) unsigned NOT NULL default '0',
  `lastvisit` varchar(14) NOT NULL default '',
  `revisit` int(10) unsigned NOT NULL default '0',
  `updating_cache` tinyint(1) unsigned NOT NULL default '0',
  `error` tinyint(1) unsigned NOT NULL default '0',
  `id_cat` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_filter`;
CREATE TABLE `ploopi_mod_rss_filter` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `condition` tinyint(1) unsigned NOT NULL default '1',
  `limit` tinyint(3) unsigned NOT NULL default '0',
  `tpl_tag` varchar(255) character set latin1 collate latin1_bin default NULL,
  `timestp` bigint(14) unsigned NOT NULL,
  `lastupdate_timestp` bigint(14) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_filter_cat`;
CREATE TABLE `ploopi_mod_rss_filter_cat` (
  `id_filter` int(10) unsigned NOT NULL default '0',
  `id_cat` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_filter`,`id_cat`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_filter_element`;
CREATE TABLE `ploopi_mod_rss_filter_element` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_filter` int(10) unsigned NOT NULL,
  `target` varchar(100) NOT NULL,
  `compare` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_filter_feed`;
CREATE TABLE `ploopi_mod_rss_filter_feed` (
  `id_filter` int(10) unsigned NOT NULL default '0',
  `id_feed` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_filter`,`id_feed`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_rss_pref`;
CREATE TABLE `ploopi_mod_rss_pref` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_feed_cat_filter` varchar(11) NOT NULL default '0',
  PRIMARY KEY  (`id_module`,`id_user`,`id_feed_cat_filter`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;