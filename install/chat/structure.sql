DROP TABLE IF EXISTS `ploopi_mod_chat_connected`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_chat_connected` (
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  `lastupdate_timestp` bigint(14) unsigned NOT NULL default '0',
  `connection_timestp` bigint(14) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_user`,`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_chat_msg`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_chat_msg` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `content` mediumtext NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

