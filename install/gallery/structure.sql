--
-- Structure de la table `ploopi_mod_gallery`
--

CREATE TABLE IF NOT EXISTS `ploopi_mod_gallery` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` char(100) default NULL,
  `description` longtext,
  `template` varchar(255) NOT NULL,
  `nb_col` int(3) default NULL,
  `nb_line` int(3) default NULL,
  `thumb_width` int(4) default NULL,
  `thumb_height` int(4) default NULL,
  `thumb_color` varchar(7) NOT NULL default '#FFFFFF',
  `view_width` int(4) default NULL,
  `view_height` int(4) default NULL,
  `view_color` varchar(7) NOT NULL default '#FFFFFF',
  `create_id_user` int(10) unsigned NOT NULL,
  `create_user` varchar(255) NOT NULL,
  `create_timestp` bigint(14) NOT NULL,
  `lastupdate_id_user` int(10) unsigned NOT NULL,
  `lastupdate_user` varchar(255) NOT NULL,
  `lastupdate_timestp` bigint(14) NOT NULL,
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Structure de la table `ploopi_mod_gallery_directories`
--

CREATE TABLE IF NOT EXISTS `ploopi_mod_gallery_directories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_gallery` int(10) NOT NULL,
  `id_directory` int(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_gallery` (`id_gallery`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Structure de la table `ploopi_mod_gallery_tpl`
--

CREATE TABLE IF NOT EXISTS `ploopi_mod_gallery_tpl` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `block` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `addtoheadcss` varchar(255) NOT NULL,
  `addtoheadcssie` varchar(255) NOT NULL,
  `id_module` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  `id_workspace` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_gallery` ADD `thumb_transparence` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `thumb_color`;
ALTER TABLE `ploopi_mod_gallery` ADD `view_transparence` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `view_color`;

