DROP TABLE IF EXISTS `ploopi_mod_dbreport_queryrelation`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_queryrelation` (
  `id_query` int(10) unsigned NOT NULL default '0',
  `tablename_src` varchar(100) NOT NULL,
  `tablename_dest` varchar(100) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id_query`,`tablename_src`,`tablename_dest`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `timestp_update` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';