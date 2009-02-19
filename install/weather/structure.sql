DROP TABLE IF EXISTS `ploopi_mod_weather`;
CREATE TABLE `ploopi_mod_weather` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `city` varchar(255) NOT NULL,
  `datetime_update` bigint(14) unsigned NOT NULL default '0',
  `nbDays` int(2) NOT NULL default '5',
  `data` text NOT NULL,
  `partnerid` varchar(255) NOT NULL,
  `partnerkey` varchar(255) NOT NULL,
  `codecity` varchar(255) NOT NULL,
  `si` char(1) NOT NULL default 'm',
  `id_user` int(10) unsigned NOT NULL,
  `id_module` int(10) unsigned NOT NULL,
  `id_workspace` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

