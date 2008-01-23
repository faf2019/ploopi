ALTER TABLE `dims_mod_doc_file` ADD `version` INT( 10 ) NOT NULL AFTER `nbclick` ;
ALTER TABLE `dims_mod_doc_file` CHANGE `version` `version` INT( 10 ) NOT NULL DEFAULT '1';
UPDATE `dims_mod_doc_file` SET `version` = 1;
ALTER TABLE `dims_mod_doc_file` CHANGE `name` `name` VARCHAR( 255 ) NULL DEFAULT NULL;

DROP TABLE IF EXISTS `dims_mod_doc_file_history`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_file_history` (
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
) TYPE=MyISAM;

ALTER TABLE `dims_mod_doc_file` DROP `file`;

DROP TABLE IF EXISTS `dims_mod_doc_file_draft`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_file_draft` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_docfile` int(10) unsigned default NULL,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `timestp_create` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `extension` varchar(20) NOT NULL,
  `parents` varchar(255) default NULL,
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_group` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

ALTER TABLE `dims_mod_doc_folder` ADD `readonly_content` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `readonly`;
ALTER TABLE `dims_mod_doc_file_draft` CHANGE `id_docfile` `id_docfile` INT( 10 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `dims_mod_doc_folder` ADD `published` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `nbelements` ;
ALTER TABLE `dims_mod_doc_ext` ADD INDEX ( `filetype` );
ALTER TABLE `dims_mod_doc_file` ADD INDEX ( `name` );
ALTER TABLE `dims_mod_doc_file` ADD INDEX ( `id_folder` );
ALTER TABLE `dims_mod_doc_file` ADD INDEX ( `extension` );

ALTER TABLE `dims_mod_doc_file` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `dims_mod_doc_file_draft` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_doc_folder` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';

ALTER TABLE `dims_mod_doc_file` ADD `md5id` CHAR( 32 ) NOT NULL AFTER `id`;
UPDATE `dims_mod_doc_file` SET `md5id` = md5(concat(`timestp_create`,'_',`id`,'_',`version`));
ALTER TABLE `dims_mod_doc_file` ADD INDEX ( `md5id` );

ALTER TABLE `dims_mod_doc_file_draft` ADD `md5id` CHAR( 32 ) NOT NULL AFTER `id`;
UPDATE `dims_mod_doc_file_draft` SET `md5id` = md5(concat(`timestp_create`,'_',`id`));
ALTER TABLE `dims_mod_doc_file_draft` ADD INDEX ( `md5id` );

ALTER TABLE `dims_mod_doc_folder` ADD `waiting_validation` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `published` ;
ALTER TABLE `dims_mod_doc_file` ADD `metadata` LONGTEXT NOT NULL AFTER `version` ;
ALTER TABLE `dims_mod_doc_file` ADD `words_overall` INT(10) NOT NULL AFTER `metadata` ;
ALTER TABLE `dims_mod_doc_file` ADD `words_indexed` INT(10) NOT NULL AFTER `words_overall` ;

-- --------------------------------------------------------

-- 
-- Structure de la table `dims_mod_doc_keyword`
-- 

DROP TABLE IF EXISTS `dims_mod_doc_keyword`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_keyword` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(32) NOT NULL,
  `twoletters` char(2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `twoletter` (`twoletters`),
  KEY `keyword` (`keyword`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `dims_mod_doc_keyword_file`
-- 

DROP TABLE IF EXISTS `dims_mod_doc_keyword_file`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_keyword_file` (
  `id_file` int(10) unsigned NOT NULL default '0',
  `id_keyword` int(10) unsigned NOT NULL default '0',
  `weight` mediumint(5) unsigned NOT NULL default '0',
  KEY `id_file` (`id_file`),
  KEY `id_keyword` (`id_keyword`),
  KEY `weight` (`weight`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `dims_mod_doc_meta`
-- 

DROP TABLE IF EXISTS `dims_mod_doc_meta`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_meta` (
  `id_file` int(10) unsigned NOT NULL default '0',
  `meta` varchar(32) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `value` (`value`),
  KEY `meta` (`meta`),
  KEY `id_file` (`id_file`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `dims_mod_doc_parser`
-- 

DROP TABLE IF EXISTS `dims_mod_doc_parser`;
CREATE TABLE IF NOT EXISTS `dims_mod_doc_parser` (
  `id` int(10) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `extension` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;


INSERT INTO `dims_mod_doc_parser` (`id`, `label`, `path`, `extension`) VALUES
(1, 'Microsoft WORD', 'catdoc -s 8859-15 -d 8859-15 -f ascii %f', 'doc'),
(2, 'Acrobat PDF', 'pdftotext -nopgbrk %f -', 'pdf'),
(3, 'TEXTE', 'cat %f', 'txt'),
(4, 'Microsoft EXCEL', 'xls2csv %f', 'xls'),
(6, 'Microsoft PowerPoint', 'catppt -s 8859-15 -d 8859-15 -f ascii %f', 'ppt'),
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

ALTER TABLE `dims_mod_doc_file_draft` ADD `readonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `size`;
ALTER TABLE `dims_mod_doc_file` ADD `readonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `size` ;
ALTER TABLE `dims_mod_doc_keyword_file` ADD `meta` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_doc_keyword_file` ADD INDEX ( `meta` );
UPDATE `dims_mod_doc_parser` SET `path` = 'catppt -s 8859-15 -d 8859-15 %f' WHERE `extension` = 'ppt';

ALTER TABLE `dims_mod_doc_keyword_file` ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `meta` ;
ALTER TABLE `dims_mod_doc_keyword_file` ADD INDEX ( `id_module` ) ;
ALTER TABLE `dims_mod_doc_keyword` ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `twoletters` ;
ALTER TABLE `dims_mod_doc_keyword` ADD INDEX ( `id_module` ) ;

OPTIMIZE TABLE `dims_mod_doc_keyword_file`;
OPTIMIZE TABLE `dims_mod_doc_keyword`;
