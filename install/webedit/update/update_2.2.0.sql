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