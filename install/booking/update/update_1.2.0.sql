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
