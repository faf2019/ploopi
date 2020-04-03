-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_booking_event`
--

DROP TABLE IF EXISTS `ploopi_mod_booking_event`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_event` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_resource` int(10) unsigned NOT NULL default '0',
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
  KEY `id_module` (`id_module`),
  KEY `id_resource` (`id_resource`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_booking_event_detail`
--

DROP TABLE IF EXISTS `ploopi_mod_booking_event_detail`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_event_detail` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `timestp_begin` bigint(14) unsigned NOT NULL default '0',
  `timestp_end` bigint(14) unsigned NOT NULL default '0',
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `canceled` tinyint(1) unsigned NOT NULL default '0',
  `cancelreason` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `emails` text NOT NULL,
  `id_event` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_event` (`id_event`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_booking_resource`
--

DROP TABLE IF EXISTS `ploopi_mod_booking_resource`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_resource` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `color` varchar(16) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `timestp_create` bigint(14) unsigned NOT NULL default '0',
  `timestp_modify` bigint(14) unsigned NOT NULL default '0',
  `id_resourcetype` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_resourcetype` (`id_resourcetype`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_booking_resource_workspace`
--

DROP TABLE IF EXISTS `ploopi_mod_booking_resource_workspace`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_resource_workspace` (
  `id_resource` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_resource`,`id_workspace`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `ploopi_mod_booking_resourcetype`
--

DROP TABLE IF EXISTS `ploopi_mod_booking_resourcetype`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_resourcetype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ploopi_mod_booking_subresource`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_subresource` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `timestp_create` bigint(14) unsigned NOT NULL DEFAULT '0',
  `timestp_modify` bigint(14) unsigned NOT NULL DEFAULT '0',
  `id_resource` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL DEFAULT '0',
  `id_workspace` int(10) unsigned NOT NULL DEFAULT '0',
  `id_module` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_resourcetype` (`id_resource`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_booking_event_subresource`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_booking_event_subresource` (
  `id_event` int(10) unsigned NOT NULL DEFAULT '0',
  `id_subresource` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_event`,`id_subresource`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


UPDATE `ploopi_mod_booking_event_detail` SET `cancelreason` = '' WHERE ISNULL(`cancelreason`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `cancelreason` `cancelreason` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event_detail` SET `message` = '' WHERE ISNULL(`message`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `message` `message` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event_detail` SET `emails` = '' WHERE ISNULL(`emails`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `emails` `emails` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_subresource` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_subresource` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_subresource` SET `reference` = '' WHERE ISNULL(`reference`);
ALTER TABLE `ploopi_mod_booking_subresource` CHANGE `reference` `reference` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resourcetype` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_resourcetype` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `reference` = '' WHERE ISNULL(`reference`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `reference` `reference` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `color` = '' WHERE ISNULL(`color`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `color` `color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `object` = '' WHERE ISNULL(`object`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `object` `object` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `periodicity` = '' WHERE ISNULL(`periodicity`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `periodicity` `periodicity` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `comment` `comment` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
