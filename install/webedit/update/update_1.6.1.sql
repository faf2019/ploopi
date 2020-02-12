DROP TABLE IF EXISTS `ploopi_mod_webedit_docfile`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_webedit_docfile` (
  `id_docfile` int(10) unsigned NOT NULL,
  `md5id_docfile` char(32) NOT NULL,
  `id_module_docfile` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_docfile`),
  KEY `md5id_docfile` (`md5id_docfile`),
  KEY `id_module` (`id_module`),
  KEY `id_module_docfile` (`id_module_docfile`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
