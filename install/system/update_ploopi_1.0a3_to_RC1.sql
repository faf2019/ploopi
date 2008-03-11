ALTER TABLE `ploopi_log` CHANGE `dims_userid` `ploopi_userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dims_workspaceid` `ploopi_workspaceid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dims_moduleid` `ploopi_moduleid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_index_stem_element` CHANGE `weight` `weight` MEDIUMINT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `relevance` `relevance` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_index_element` CHANGE `id_record` `id_record` CHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `id_object` `id_object` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `label` `label` CHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `id_user` `id_user` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_workspace` `id_workspace` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_module` `id_module` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_index_keyword_element` CHANGE `weight` `weight` MEDIUMINT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `relevance` `relevance` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `ploopi_subscription`;
CREATE TABLE IF NOT EXISTS `ploopi_subscription` (
  `id` char(32) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_object` int(10) NOT NULL default '0',
  `id_record` varchar(255) NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `allactions` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_object` (`id_object`),
  KEY `id_user` (`id_user`),
  KEY `id_action` (`allactions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_subscription_action`;
CREATE TABLE IF NOT EXISTS `ploopi_subscription_action` (
  `id_subscription` char(32) NOT NULL,
  `id_action` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_subscription`,`id_action`),
  KEY `id_action` (`id_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

