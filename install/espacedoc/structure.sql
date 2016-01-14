DROP TABLE IF EXISTS `ploopi_mod_espacedoc_document`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_espacedoc_document` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `intitule` varchar(255) NOT NULL,
  `fichier` varchar(255) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL default '0',
  `id_theme` int(10) unsigned NOT NULL default '0',
  `id_sstheme` int(10) unsigned NOT NULL default '0',
  `timestp_create` bigint(14) unsigned NOT NULL default '0',
  `timestp_modify` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_theme` (`id_sstheme`),
  KEY `id_departement` (`id_theme`),
  KEY `timestp` (`timestp`),
  KEY `intitule` (`intitule`),
  KEY `nomfichier` (`fichier`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `timestp_create` (`timestp_create`),
  KEY `timestp_modify` (`timestp_modify`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_espacedoc_sstheme`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_espacedoc_sstheme` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_theme` int(10) unsigned NOT NULL default '0',
  `libelle` varchar(255) NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `actif` (`actif`),
  KEY `id_departement` (`id_theme`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_espacedoc_theme`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_espacedoc_theme` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `libelle` varchar(255) NOT NULL,
  `actif` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `actif` (`actif`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
