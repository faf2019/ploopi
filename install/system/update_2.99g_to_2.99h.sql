DROP TABLE IF EXISTS `dims_documents_ext`;
CREATE TABLE IF NOT EXISTS `dims_documents_ext` (
  `ext` varchar(10) default NULL,
  `filetype` varchar(16) default NULL,
  KEY `ext` (`ext`),
  KEY `filetype` (`filetype`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `dims_documents_file`;
CREATE TABLE IF NOT EXISTS `dims_documents_file` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `size` int(10) unsigned default '0',
  `extension` varchar(20) default NULL,
  `parents` varchar(255) default NULL,
  `content` longtext NOT NULL,
  `nbclick` int(10) unsigned default '0',
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `name` (`name`),
  KEY `id_folder` (`id_folder`),
  KEY `extension` (`extension`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `dims_documents_folder`;
CREATE TABLE IF NOT EXISTS `dims_documents_folder` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `description` varchar(255) default NULL,
  `parents` varchar(255) default '0',
  `timestp_create` bigint(14) default NULL,
  `timestp_modify` bigint(14) default NULL,
  `nbelements` int(10) unsigned NOT NULL default '0',
  `id_folder` int(10) unsigned default '0',
  `id_user_modify` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_group` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `id_folder` (`id_folder`)
) TYPE=MyISAM;


INSERT INTO `dims_documents_ext` (`ext`, `filetype`) VALUES 
('odt', 'document'),
('doc', 'document'),
('xls', 'spreadsheet'),
('mp3', 'audio'),
('wav', 'audio'),
('ogg', 'audio'),
('jpg', 'image'),
('jpeg', 'image'),
('png', 'image'),
('gif', 'image'),
('psd', 'image'),
('xcf', 'image'),
('svg', 'image'),
('pdf', 'document'),
('avi', 'video'),
('wmv', 'video'),
('ogm', 'video'),
('mpg', 'video'),
('mpeg', 'video'),
('zip', 'archive'),
('tgz', 'archive'),
('gz', 'archive'),
('rar', 'archive'),
('bz2', 'archive'),
('ace', 'archive');


ALTER TABLE `dims_param_default` CHANGE `value` `value` VARCHAR(255);

UPDATE `dims_module_type` SET `version` = '2.99h' WHERE `dims_module_type`.`id` =1;
