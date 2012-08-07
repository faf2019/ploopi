DROP TABLE IF EXISTS `ploopi_mod_webedit_article_object`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_webedit_article_object` (
  `id_article` int(10) unsigned NOT NULL,
  `id_wce_object` int(10) unsigned NOT NULL,
  `id_module_type` int(10) NOT NULL DEFAULT '0',
  `id_module` int(10) unsigned NOT NULL DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  PRIMARY KEY (`id_article`,`id_wce_object`,`id_module_type`,`id_module`,`id_record`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
