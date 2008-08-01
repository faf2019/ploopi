DROP TABLE IF EXISTS `ploopi_mod_webedit_counter`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_webedit_counter` (
  `year` smallint(4) unsigned NOT NULL default '0',
  `month` tinyint(2) unsigned NOT NULL default '0',
  `day` tinyint(2) unsigned NOT NULL default '0',
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  `week` mediumint(6) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`year`,`month`,`day`,`id_article`),
  KEY `month` (`month`),
  KEY `day` (`day`),
  KEY `id_article` (`id_article`),
  KEY `id_module` (`id_module`),
  KEY `hits` (`hits`),
  KEY `week` (`week`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;