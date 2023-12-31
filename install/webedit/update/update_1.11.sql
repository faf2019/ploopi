ALTER TABLE `dims_mod_webedit_article_draft` ADD `description` MEDIUMTEXT NOT NULL AFTER `content`;
ALTER TABLE `dims_mod_webedit_article` ADD `description` MEDIUMTEXT NOT NULL AFTER `content`;

CREATE TABLE `dims_mod_webedit_article_backup` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`id_article`,`timestp`),
  KEY `timestp` (`timestp`)
) TYPE=MyISAM;
