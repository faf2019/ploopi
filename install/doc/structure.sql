DROP TABLE IF EXISTS `ploopi_mod_doc_file`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `md5id` char(32) NOT NULL,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `readonly` tinyint(1) unsigned NOT NULL default '0',
  `extension` varchar(20) default NULL,
  `parents` varchar(255) default NULL,
  `content` longtext NOT NULL,
  `nbclick` int(10) unsigned default '0',
  `version` int(10) NOT NULL default '1',
  `metadata` longtext NOT NULL,
  `words_overall` int(10) NOT NULL,
  `words_indexed` int(10) NOT NULL,
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `name` (`name`),
  KEY `id_folder` (`id_folder`),
  KEY `extension` (`extension`),
  KEY `md5id` (`md5id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_file_draft`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_file_draft` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `md5id` char(32) NOT NULL,
  `id_docfile` int(10) unsigned default '0',
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `timestp_create` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `readonly` tinyint(1) unsigned NOT NULL default '0',
  `extension` varchar(20) NOT NULL,
  `parents` varchar(255) default NULL,
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `md5id` (`md5id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_file_history`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_file_history` (
  `id_docfile` int(10) unsigned NOT NULL,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `version` int(10) NOT NULL default '1',
  `extension` varchar(20) NOT NULL,
  `id_user_modify` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_docfile`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_folder`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `foldertype` varchar(16) NOT NULL default 'private',
  `readonly` tinyint(1) unsigned NOT NULL default '0',
  `readonly_content` tinyint(1) unsigned NOT NULL default '0',
  `parents` varchar(255) default '0',
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `nbelements` int(10) unsigned NOT NULL default '0',
  `published` tinyint(1) unsigned NOT NULL default '1',
  `waiting_validation` int(10) unsigned NOT NULL default '0',
  `allow_feeds` tinyint(1) unsigned NOT NULL default '0',
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `id_folder` (`id_folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_keyword`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_keyword` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(32) NOT NULL,
  `twoletters` char(2) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `twoletter` (`twoletters`),
  KEY `keyword` (`keyword`),
  KEY `id_module` (`id_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_keyword_file`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_keyword_file` (
  `id_file` int(10) unsigned NOT NULL default '0',
  `id_keyword` int(10) unsigned NOT NULL default '0',
  `weight` mediumint(5) unsigned NOT NULL default '0',
  `meta` tinyint(1) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  KEY `id_file` (`id_file`),
  KEY `id_keyword` (`id_keyword`),
  KEY `weight` (`weight`),
  KEY `meta` (`meta`),
  KEY `id_module` (`id_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_meta`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_meta` (
  `id_file` int(10) unsigned NOT NULL default '0',
  `meta` varchar(32) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `value` (`value`),
  KEY `meta` (`meta`),
  KEY `id_file` (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_doc_parser`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_parser` (
  `id` int(10) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `extension` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mod_doc_parser` (`id`, `label`, `path`, `extension`) VALUES
(1, 'Microsoft WORD', 'catdoc -d UTF-8 %f', 'doc'),
(2, 'Acrobat PDF', 'pdftotext -nopgbrk %f -', 'pdf'),
(3, 'TEXTE', 'cat %f', 'txt'),
(4, 'Microsoft EXCEL', 'xls2csv %f', 'xls'),
(6, 'Microsoft PowerPoint', 'catppt -d UTF-8 %f', 'ppt'),
(7, 'OpenOffice 2.0 Writer', 'bin/oo2txt.sh %f', 'odt'),
(8, 'OpenOffice 1.0 Writer', 'bin/oo2txt.sh %f', 'sxw'),
(9, 'Rich Text Format RTF', 'unrtf --text --nopict %f', 'rtf'),
(10, 'HTML', 'html2text %f', 'html'),
(11, 'HTM', 'html2text %f', 'htm'),
(12, 'XML', 'bin/xml2txt %f', 'xml'),
(13, 'OpenOffice 2.0 Draw', 'bin/oo2txt.sh %f', 'odg'),
(14, 'OpenOffice 2.0 Calc', 'bin/oo2txt.sh %f', 'ods'),
(15, 'OpenOffice 2.0 Impress', 'bin/oo2txt.sh %f', 'odp'),
(16, 'CSV', 'cat %f', 'csv');

UPDATE `ploopi_mod_doc_folder` SET `readonly` = `readonly_content`;
ALTER TABLE `ploopi_mod_doc_folder` DROP `readonly_content`;
UPDATE `ploopi_mod_doc_file` fi, `ploopi_mod_doc_folder` fo SET fi.`readonly` = fo.`readonly` WHERE fi.`id_folder` = fo.`id`;

UPDATE ploopi_mod_doc_parser SET `path` = 'unoconv --format=txt --stdout %f' WHERE id IN(7,8,13,14,15);

INSERT INTO `ploopi_mod_doc_parser` (`label`, `path`, `extension`) VALUES
('Office Open XML XLSX', 'unoconv --format=txt --stdout %f', 'xlsx'),
('Office Open XML DOCX', 'unoconv --format=txt --stdout %f', 'docx');


UPDATE `ploopi_mod_doc_file_draft` SET `md5id` = '' WHERE ISNULL(`md5id`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `md5id` `md5id` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `id_docfile` = 0  WHERE ISNULL(`id_docfile`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `id_docfile` `id_docfile` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `size` = 0  WHERE ISNULL(`size`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `size` `size` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `extension` = '' WHERE ISNULL(`extension`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `extension` `extension` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_draft` SET `id_folder` = 0  WHERE ISNULL(`id_folder`);
ALTER TABLE `ploopi_mod_doc_file_draft` CHANGE `id_folder` `id_folder` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_meta` SET `meta` = '' WHERE ISNULL(`meta`);
ALTER TABLE `ploopi_mod_doc_meta` CHANGE `meta` `meta` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_meta` SET `value` = '' WHERE ISNULL(`value`);
ALTER TABLE `ploopi_mod_doc_meta` CHANGE `value` `value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_parser` SET `extension` = '' WHERE ISNULL(`extension`);
ALTER TABLE `ploopi_mod_doc_parser` CHANGE `extension` `extension` varchar(10) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `id_docfile` = 0  WHERE ISNULL(`id_docfile`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `id_docfile` `id_docfile` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `timestp_modify` = 0  WHERE ISNULL(`timestp_modify`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `timestp_modify` `timestp_modify` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `size` = 0  WHERE ISNULL(`size`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `size` `size` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file_history` SET `extension` = '' WHERE ISNULL(`extension`);
ALTER TABLE `ploopi_mod_doc_file_history` CHANGE `extension` `extension` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `md5id` = '' WHERE ISNULL(`md5id`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `md5id` `md5id` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `timestp_modify` = 0  WHERE ISNULL(`timestp_modify`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `timestp_modify` `timestp_modify` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `size` = 0  WHERE ISNULL(`size`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `size` `size` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `extension` = '' WHERE ISNULL(`extension`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `extension` `extension` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `nbclick` = 0  WHERE ISNULL(`nbclick`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `nbclick` `nbclick` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `metadata` = '' WHERE ISNULL(`metadata`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `metadata` `metadata` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `words_overall` = 0  WHERE ISNULL(`words_overall`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `words_overall` `words_overall` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `words_indexed` = 0  WHERE ISNULL(`words_indexed`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `words_indexed` `words_indexed` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `id_folder` = 0  WHERE ISNULL(`id_folder`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `id_folder` `id_folder` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `id_user_modify` = 0  WHERE ISNULL(`id_user_modify`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `id_user_modify` `id_user_modify` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_file` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_doc_file` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_keyword` SET `keyword` = '' WHERE ISNULL(`keyword`);
ALTER TABLE `ploopi_mod_doc_keyword` CHANGE `keyword` `keyword` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_keyword` SET `twoletters` = '' WHERE ISNULL(`twoletters`);
ALTER TABLE `ploopi_mod_doc_keyword` CHANGE `twoletters` `twoletters` char(2) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `timestp_modify` = 0  WHERE ISNULL(`timestp_modify`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `timestp_modify` `timestp_modify` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `id_folder` = 0  WHERE ISNULL(`id_folder`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `id_folder` `id_folder` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `id_user_modify` = 0  WHERE ISNULL(`id_user_modify`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `id_user_modify` `id_user_modify` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_doc_folder` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_doc_folder` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
