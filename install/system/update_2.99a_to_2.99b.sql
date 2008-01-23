CREATE TABLE IF NOT EXISTS `dims_workflow` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_record` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) unsigned NOT NULL default '0',
  `type_workflow` varchar(16) default '0',
  `id_workflow` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `search` (`id_module`,`id_object`,`id_record`)
) TYPE=MyISAM;

UPDATE `dims_module_type` SET `label` = 'system',
`publicparam` = '0',
`description` = NULL ,
`version` = '2.99b',
`author` = 'Netlor Concept',
`date` = '20070330000000' WHERE `id` =1 LIMIT 1 ;
