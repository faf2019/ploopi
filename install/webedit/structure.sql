DROP TABLE IF EXISTS `ploopi_mod_webedit_article`;
CREATE TABLE `ploopi_mod_webedit_article` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `reference` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `metakeywords` mediumtext NOT NULL,
  `content` longtext,
  `content_cleaned` longtext,
  `metadescription` mediumtext NOT NULL,
  `metatitle` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(16) NOT NULL default '',
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `comments_allowed` tinyint(1) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `timestp_published` bigint(14) unsigned NOT NULL default '0',
  `timestp_unpublished` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_timestp` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_id_user` int(10) unsigned NOT NULL default '0',
  `id_heading` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `position` int(10) unsigned NOT NULL default '0',
  `status` varchar(16) NOT NULL default '',
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lastupdate_timestp` (`lastupdate_timestp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_backup`;
CREATE TABLE `ploopi_mod_webedit_article_backup` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `content` longtext NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_article`,`timestp`),
  KEY `timestp` (`timestp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_draft`;
CREATE TABLE `ploopi_mod_webedit_article_draft` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `reference` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `metakeywords` mediumtext NOT NULL,
  `content` longtext,
  `content_cleaned` longtext,
  `metadescription` mediumtext NOT NULL,
  `metatitle` mediumtext NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(16) NOT NULL default '',
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `comments_allowed` tinyint(1) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `timestp_published` bigint(14) unsigned NOT NULL default '0',
  `timestp_unpublished` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_timestp` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_id_user` int(10) unsigned NOT NULL default '0',
  `id_heading` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `position` int(10) unsigned NOT NULL default '0',
  `status` varchar(16) NOT NULL default '',
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_tag`;
CREATE TABLE `ploopi_mod_webedit_article_tag` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_tag` int(10) unsigned NOT NULL default '0',
  KEY `id_article` (`id_article`),
  KEY `id_tag` (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_counter`;
CREATE TABLE `ploopi_mod_webedit_counter` (
  `year` smallint(4) unsigned NOT NULL default '0',
  `month` tinyint(2) unsigned NOT NULL default '0',
  `day` tinyint(2) unsigned NOT NULL default '0',
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  `week` mediumint(6) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`year`,`month`,`day`,`id_article`),
  KEY `month` (`month`),
  KEY `day` (`day`),
  KEY `id_article` (`id_article`),
  KEY `id_module` (`id_module`),
  KEY `hits` (`hits`),
  KEY `week` (`week`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_docfile`;
CREATE TABLE `ploopi_mod_webedit_docfile` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_docfile` int(10) unsigned NOT NULL,
  `md5id_docfile` char(32) NOT NULL,
  `id_module_docfile` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_article`,`id_docfile`),
  KEY `md5id_docfile` (`md5id_docfile`),
  KEY `id_module` (`id_module`),
  KEY `id_module_docfile` (`id_module_docfile`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_heading`;
CREATE TABLE `ploopi_mod_webedit_heading` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `template` varchar(255) NOT NULL default '',
  `id_heading` int(10) unsigned NOT NULL default '0',
  `parents` varchar(255) NOT NULL default '',
  `depth` int(10) unsigned NOT NULL default '0',
  `position` int(10) unsigned NOT NULL default '0',
  `content_type` char(16) NOT NULL default 'article_first',
  `color` varchar(32) NOT NULL,
  `posx` int(10) unsigned NOT NULL default '0',
  `posy` int(10) unsigned NOT NULL default '0',
  `visible` tinyint(1) unsigned NOT NULL default '0',
  `linkedpage` int(10) unsigned NOT NULL default '0',
  `url` varchar(255) NOT NULL,
  `url_window` tinyint(1) unsigned NOT NULL default '0',
  `sortmode` varchar(16) NOT NULL,
  `feed_enabled` tinyint(1) unsigned NOT NULL default '1',
  `subscription_enabled` tinyint(1) unsigned NOT NULL default '1',
  `free1` varchar(255) NOT NULL,
  `free2` varchar(255) NOT NULL,
  `id_module` tinyint(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_heading_subscriber`;
CREATE TABLE `ploopi_mod_webedit_heading_subscriber` (
  `id_heading` int(10) unsigned NOT NULL default '0',
  `email` varchar(255) NOT NULL,
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_heading`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_webedit_tag`;
CREATE TABLE `ploopi_mod_webedit_tag` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(64) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
