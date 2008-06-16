ALTER TABLE `ploopi_mod_webedit_heading` ADD `rssfeed_enabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `sortmode` ,
ADD `subscription_enabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `rssfeed_enabled` ;

CREATE TABLE `ploopi_mod_webedit_heading_subscriber` (
  `id_heading` int(10) unsigned NOT NULL default '0',
  `email` varchar(255) NOT NULL,
  `validated` tinyint(1) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id_heading`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_mod_webedit_article_tag` (
  `id_article` int(10) unsigned NOT NULL default '0',
  `id_tag` int(10) unsigned NOT NULL default '0',
  KEY `id_article` (`id_article`),
  KEY `id_tag` (`id_tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_mod_webedit_tag` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(64) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;