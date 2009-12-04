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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_doc_meta`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_meta` (
  `id_file` int(10) unsigned NOT NULL default '0',
  `meta` varchar(32) NOT NULL,
  `value` varchar(255) NOT NULL,
  KEY `value` (`value`),
  KEY `meta` (`meta`),
  KEY `id_file` (`id_file`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_doc_parser`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_parser` (
  `id` int(10) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `extension` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ploopi_mod_doc_parser` (`id`, `label`, `path`, `extension`) VALUES 
(1, 'Microsoft WORD', 'catdoc -s 8859-15 -d 8859-15 -f ascii %f', 'doc'),
(2, 'Acrobat PDF', 'pdftotext -nopgbrk %f -', 'pdf'),
(3, 'TEXTE', 'cat %f', 'txt'),
(4, 'Microsoft EXCEL', 'xls2csv %f', 'xls'),
(6, 'Microsoft PowerPoint', 'catppt -s 8859-15 -d 8859-15 %f', 'ppt'),
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