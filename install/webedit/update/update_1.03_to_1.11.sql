ALTER TABLE `dims_mod_wce_article_draft` CHANGE `author` `author` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article_draft` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article_draft` ADD `keywords` MEDIUMTEXT NOT NULL AFTER `title`;
ALTER TABLE `dims_mod_wce_article` CHANGE `author` `author` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article` ADD `keywords` MEDIUMTEXT NOT NULL AFTER `title`;
RENAME TABLE dims_mod_wce_article  TO dims_mod_webedit_article;
RENAME TABLE dims_mod_wce_article_draft  TO dims_mod_webedit_article_draft;
RENAME TABLE dims_mod_wce_heading  TO dims_mod_webedit_heading;
ALTER TABLE `dims_mod_webedit_article_draft` ADD `description` MEDIUMTEXT NOT NULL AFTER `content`;
ALTER TABLE `dims_mod_webedit_article` ADD `description` MEDIUMTEXT NOT NULL AFTER `content`;

CREATE TABLE `dims_mod_webedit_article_backup` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `content` longtext NOT NULL,
  PRIMARY KEY  (`id_article`,`timestp`),
  KEY `timestp` (`timestp`)
) TYPE=MyISAM;
