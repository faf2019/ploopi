DROP TABLE IF EXISTS `ploopi_mod_forum_cat`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forum_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `position` tinyint(3) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `description` longtext,
  `visible` tinyint(1) NOT NULL default '1',
  `closed` tinyint(1) NOT NULL default '0',
  `id_author` int(10) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_id_user` int(10) unsigned NOT NULL default '0',
  `lastupdate_timestp` bigint(14) unsigned NOT NULL default '0',
  `mustbe_validated` tinyint(1) unsigned NOT NULL default '1',
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

DROP TABLE IF EXISTS `ploopi_mod_forum_mess`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forum_mess` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_cat` int(10) unsigned default NULL,
  `id_subject` int(10) unsigned NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `title` varchar(255) default NULL,
  `content` longtext,
  `id_author` int(10) unsigned NOT NULL default '0',
  `author` varchar(255) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `validated` tinyint(1) unsigned NOT NULL default '1',
  `validated_id_user` int(10) unsigned NOT NULL default '0',
  `validated_timestp` bigint(14) unsigned NOT NULL default '0',
  `lastupdate_timestp` bigint(14) unsigned NOT NULL default '0',
  `moderate_id_user` int(10) unsigned NOT NULL default '0',
  `moderate_timestp` bigint(14) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  `id_workspace` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_subject` (`id_subject`),
  KEY `id_cat` (`id_cat`),
  KEY `timestp` (`timestp`),
  KEY `id_author` (`id_author`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;
