DROP TABLE `ploopi_index_keyword`, `ploopi_index_stem`;

DROP TABLE IF EXISTS `ploopi_index_element`;
CREATE TABLE IF NOT EXISTS `ploopi_index_element` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_record` char(64) NOT NULL,
  `id_object` smallint(5) unsigned NOT NULL DEFAULT '0',
  `label` char(128) NOT NULL,
  `timestp_create` bigint(14) unsigned NOT NULL DEFAULT '0',
  `timestp_modify` bigint(14) unsigned NOT NULL DEFAULT '0',
  `timestp_lastindex` bigint(14) unsigned NOT NULL DEFAULT '0',
  `id_user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_workspace` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_module` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_user` (`id_user`),
  KEY `id_record` (`id_record`),
  KEY `id_object` (`id_object`),
  KEY `timestp_create` (`timestp_create`),
  KEY `timestp_modify` (`timestp_modify`),
  KEY `timestp_lastindex` (`timestp_lastindex`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_index_keyword_element`;
CREATE TABLE IF NOT EXISTS `ploopi_index_keyword_element` (
  `id_element` int(10) unsigned NOT NULL,
  `keyword` char(20) NOT NULL,
  `weight` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `ratio` float unsigned NOT NULL DEFAULT '0',
  `relevance` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `id_element` (`id_element`),
  KEY `keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

DROP TABLE IF EXISTS `ploopi_index_phonetic_element`;
CREATE TABLE IF NOT EXISTS `ploopi_index_phonetic_element` (
  `id_element` int(10) unsigned NOT NULL,
  `phonetic` char(20) NOT NULL,
  `weight` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `ratio` float unsigned NOT NULL DEFAULT '0',
  `relevance` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `id_element` (`id_element`),
  KEY `phonetic` (`phonetic`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

DROP TABLE IF EXISTS `ploopi_index_stem_element`;
CREATE TABLE IF NOT EXISTS `ploopi_index_stem_element` (
  `id_element` int(10) unsigned NOT NULL,
  `stem` char(20) NOT NULL,
  `weight` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `ratio` float unsigned NOT NULL DEFAULT '0',
  `relevance` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `id_element` (`id_element`),
  KEY `stem` (`stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;

UPDATE `ploopi_module_type` SET `version` = '1.9.3.4', `author` = 'Ovensia', `date` = '20130819000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
