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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
