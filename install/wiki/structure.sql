DROP TABLE IF EXISTS `ploopi_mod_wiki_page`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_wiki_page` (
  `id` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `root` tinyint(1) unsigned NOT NULL default '0',
  `tags` varchar(255) NOT NULL,
  `revision` int(10) unsigned NOT NULL default '1',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `ts_created` bigint(14) unsigned NOT NULL default '0',
  `ts_modified` bigint(14) unsigned NOT NULL default '0',
  `ts_validated` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`,`id_module`),
  KEY `ts_created` (`ts_created`),
  KEY `ts_modified` (`ts_modified`),
  KEY `ts_validated` (`ts_validated`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `revision` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_wiki_page_history`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_wiki_page_history` (
  `id_page` varchar(255) NOT NULL,
  `revision` int(10) unsigned NOT NULL default '1',
  `ts_modified` bigint(14) unsigned NOT NULL default '0',
  `content` text NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_page`,`revision`,`id_module`),
  KEY `ts_modified` (`ts_modified`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `revision` (`revision`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ploopi_mod_wiki_page` CHANGE `content` `content` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_mod_wiki_page_history` CHANGE `content` `content` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;