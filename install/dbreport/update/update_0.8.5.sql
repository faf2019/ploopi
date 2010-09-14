ALTER TABLE `ploopi_mod_dbreport_query` ADD `ws_id` VARCHAR( 32 ) NOT NULL AFTER `standard` ,
ADD `ws_code` VARCHAR( 32 ) NOT NULL AFTER `ws_id` ,
ADD `ws_ip` VARCHAR( 16 ) NOT NULL AFTER `ws_code` ;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_query_module_type`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_query_module_type` (
  `id_query` int(10) unsigned NOT NULL default '0',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_query`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;