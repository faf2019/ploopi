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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_comment`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_webedit_article_comment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_article` int(10) unsigned NOT NULL,
  `publish` tinyint(1) unsigned NOT NULL default '0',
  `comment` longtext NOT NULL,
  `email` varchar(255) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL,
  `id_module` int(10) unsigned NOT NULL,
  `id_workspace` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `publish` (`publish`),
  KEY `timestp` (`timestp`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_article` (`id_article`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_tag`;
CREATE TABLE `ploopi_mod_webedit_article_tag` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_tag` int(10) unsigned NOT NULL default '0',
  KEY `id_article` (`id_article`),
  KEY `id_tag` (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_webedit_heading_subscriber`;
CREATE TABLE `ploopi_mod_webedit_heading_subscriber` (
  `id_heading` int(10) unsigned NOT NULL default '0',
  `email` varchar(255) NOT NULL,
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_heading`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_webedit_tag`;
CREATE TABLE `ploopi_mod_webedit_tag` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(64) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `disabledfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_webedit_article` ADD `disabledfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `headcontent` LONGTEXT NOT NULL;
ALTER TABLE `ploopi_mod_webedit_article` ADD `headcontent` LONGTEXT NOT NULL;

ALTER TABLE `ploopi_mod_webedit_heading` ADD `private` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `visible` ;
ALTER TABLE `ploopi_mod_webedit_heading` ADD `private_visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `private` ;

ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `linkedpage` `linkedpage` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_webedit_article` ADD `width` INT( 10 ) NOT NULL DEFAULT '0', ADD `height` INT( 10 ) NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `width` INT( 10 ) NOT NULL DEFAULT '0', ADD `height` INT( 10 ) NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `ploopi_mod_webedit_article_object`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_webedit_article_object` (
  `id_article` int(10) unsigned NOT NULL,
  `id_wce_object` int(10) unsigned NOT NULL,
  `id_module_type` int(10) NOT NULL DEFAULT '0',
  `id_module` int(10) unsigned NOT NULL DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  PRIMARY KEY (`id_article`,`id_wce_object`,`id_module_type`,`id_module`,`id_record`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


UPDATE `ploopi_mod_webedit_article_backup` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article_backup` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_docfile` SET `id_docfile` = 0  WHERE ISNULL(`id_docfile`);
ALTER TABLE `ploopi_mod_webedit_docfile` CHANGE `id_docfile` `id_docfile` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_docfile` SET `md5id_docfile` = '' WHERE ISNULL(`md5id_docfile`);
ALTER TABLE `ploopi_mod_webedit_docfile` CHANGE `md5id_docfile` `md5id_docfile` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `color` = '' WHERE ISNULL(`color`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `color` `color` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `url` = '' WHERE ISNULL(`url`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `url` `url` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `sortmode` = '' WHERE ISNULL(`sortmode`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `sortmode` `sortmode` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `free1` = '' WHERE ISNULL(`free1`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `free1` `free1` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `free2` = '' WHERE ISNULL(`free2`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `free2` `free2` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_module` `id_module` tinyint(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `title` = '' WHERE ISNULL(`title`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metakeywords` = '' WHERE ISNULL(`metakeywords`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metakeywords` `metakeywords` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `content_cleaned` = '' WHERE ISNULL(`content_cleaned`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `content_cleaned` `content_cleaned` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metadescription` = '' WHERE ISNULL(`metadescription`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metadescription` `metadescription` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metatitle` = '' WHERE ISNULL(`metatitle`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metatitle` `metatitle` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `author` = '' WHERE ISNULL(`author`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `author` `author` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_heading` = 0  WHERE ISNULL(`id_heading`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_heading` `id_heading` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `tags` = '' WHERE ISNULL(`tags`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `tags` `tags` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `headcontent` = '' WHERE ISNULL(`headcontent`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `headcontent` `headcontent` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading_subscriber` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_webedit_heading_subscriber` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_article` = 0  WHERE ISNULL(`id_article`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_article` `id_article` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_wce_object` = 0  WHERE ISNULL(`id_wce_object`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_wce_object` `id_wce_object` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_tag` SET `tag` = '' WHERE ISNULL(`tag`);
ALTER TABLE `ploopi_mod_webedit_tag` CHANGE `tag` `tag` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_article` = 0  WHERE ISNULL(`id_article`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_article` `id_article` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `comment` `comment` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `nickname` = '' WHERE ISNULL(`nickname`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `nickname` `nickname` varchar(50) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `timestp` = 0  WHERE ISNULL(`timestp`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `timestp` `timestp` bigint(14) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `title` = '' WHERE ISNULL(`title`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metakeywords` = '' WHERE ISNULL(`metakeywords`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metakeywords` `metakeywords` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `content_cleaned` = '' WHERE ISNULL(`content_cleaned`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `content_cleaned` `content_cleaned` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metadescription` = '' WHERE ISNULL(`metadescription`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metadescription` `metadescription` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metatitle` = '' WHERE ISNULL(`metatitle`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metatitle` `metatitle` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `author` = '' WHERE ISNULL(`author`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `author` `author` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_heading` = 0  WHERE ISNULL(`id_heading`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_heading` `id_heading` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `tags` = '' WHERE ISNULL(`tags`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `tags` `tags` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `headcontent` = '' WHERE ISNULL(`headcontent`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `headcontent` `headcontent` longtext NOT NULL DEFAULT ''  COMMENT '' ;
