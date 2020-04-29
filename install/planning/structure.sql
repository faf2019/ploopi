--
-- Structure de la table `ploopi_mod_planning_event`
--

DROP TABLE IF EXISTS `ploopi_mod_planning_event`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_planning_event` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `object` varchar(255) NOT NULL,
  `periodicity` varchar(16) NOT NULL,
  `comment` mediumtext NOT NULL,
  `managed` tinyint(1) unsigned NOT NULL default '0',
  `timestp_request` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_planning_event_detail`
--

DROP TABLE IF EXISTS `ploopi_mod_planning_event_detail`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_planning_event_detail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestp_begin` bigint(14) unsigned NOT NULL default '0',
  `timestp_end` bigint(14) unsigned NOT NULL default '0',
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `canceled` tinyint(1) unsigned NOT NULL default '0',
  `id_event` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_event` (`id_event`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_planning_event_detail_resource`
--

DROP TABLE IF EXISTS `ploopi_mod_planning_event_detail_resource`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_planning_event_detail_resource` (
  `id_event_detail` int(10) unsigned NOT NULL default '0',
  `id_resource` int(10) unsigned NOT NULL default '0',
  `type_resource` enum('group','user') NOT NULL,
  `id_event` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_event_detail`,`id_resource`,`type_resource`),
  KEY `id_event` (`id_event`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


UPDATE `ploopi_mod_planning_event_detail_resource` SET `type_resource` = '' WHERE ISNULL(`type_resource`);
ALTER TABLE `ploopi_mod_planning_event_detail_resource` CHANGE `type_resource` `type_resource` enum('group','user') NOT NULL DEFAULT 'user' COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `object` = '' WHERE ISNULL(`object`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `object` `object` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `periodicity` = '' WHERE ISNULL(`periodicity`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `periodicity` `periodicity` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `comment` `comment` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
