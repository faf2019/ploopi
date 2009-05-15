DROP TABLE IF EXISTS `ploopi_mod_directory_speeddialing`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_speeddialing` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `number` varchar(32) NOT NULL,
  `shortnumber` varchar(16) NOT NULL,
  `heading` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
