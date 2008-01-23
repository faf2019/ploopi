DROP TABLE IF EXISTS `ploopi_mod_news_cat`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_news_cat` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(255) default NULL,
  `id_module` int(10) unsigned default '0',
  `id_user` tinyint(10) unsigned default '0',
  `date_create` datetime default NULL,
  `date_modify` datetime default NULL,
  `title` varchar(100) default NULL,
  `id_workspace` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_news_entry`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_news_entry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_cat` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) default NULL,
  `resume` varchar(255) default NULL,
  `content` longtext,
  `url` varchar(100) default NULL,
  `urltitle` varchar(100) default NULL,
  `source` varchar(100) default NULL,
  `published` tinyint(1) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `date_publish` varchar(14) default NULL,
  `hot` tinyint(1) unsigned default '0',
  `nbclick` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
