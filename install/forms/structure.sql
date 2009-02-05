DROP TABLE IF EXISTS `ploopi_mod_forms_field`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `fieldname` varchar(255) NOT NULL default '',
  `separator` tinyint(1) unsigned default '0',
  `separator_level` int(10) unsigned default '0',
  `separator_fontsize` int(10) unsigned default '0',
  `type` varchar(16) default NULL,
  `format` varchar(16) default NULL,
  `values` longtext,
  `description` longtext,
  `position` int(10) unsigned default '0',
  `maxlength` int(10) unsigned default '0',
  `cols` int(10) unsigned default '0',
  `option_needed` tinyint(1) unsigned default '0',
  `option_arrayview` tinyint(1) unsigned default '1',
  `option_exportview` tinyint(1) unsigned default '1',
  `option_wceview` tinyint(1) unsigned NOT NULL default '0',
  `defaultvalue` varchar(255) default NULL,
  `interline` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_forms` (`id_form`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_forms_form`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `tablename` varchar(255) NOT NULL default '',
  `description` longtext,
  `pubdate_start` bigint(14) default NULL,
  `pubdate_end` bigint(14) default NULL,
  `email` varchar(255) default NULL,
  `option_onlyone` tinyint(1) unsigned default '0',
  `option_onlyoneday` tinyint(1) unsigned default '0',
  `width` varchar(5) NOT NULL default '*',
  `nbline` int(10) unsigned default '25',
  `model` varchar(32) default NULL,
  `typeform` varchar(16) default 'app',
  `option_modify` varchar(16) NOT NULL default 'nobody',
  `option_view` varchar(16) NOT NULL default 'global',
  `option_displayuser` tinyint(1) unsigned default '0',
  `option_displaygroup` tinyint(1) unsigned default '0',
  `option_displaydate` tinyint(1) unsigned default '0',
  `option_displayip` tinyint(1) unsigned default '0',
  `viewed` int(10) unsigned default '0',
  `autobackup` int(10) unsigned default '0',
  `autobackup_date` bigint(14) unsigned NOT NULL default '0',
  `cms_response` longtext,
  `cms_link` tinyint(1) unsigned default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` int(10) unsigned default '0',
  `id_module` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_forms_reply`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_reply` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned default '0',
  `id_workspace` tinyint(3) unsigned default '0',
  `id_module` int(10) unsigned default NULL,
  `date_validation` varchar(14) default NULL,
  `ip` varchar(15) default NULL,
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_forms` (`id_form`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_forms_reply_field`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_reply_field` (
  `id_reply` int(10) unsigned default '0',
  `id_form` int(10) unsigned NOT NULL default '0',
  `id_field` int(10) unsigned default '0',
  `value` longtext,
  KEY `id_reply` (`id_reply`),
  KEY `id_forms` (`id_form`),
  KEY `id_field` (`id_field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_forms_reply` CHANGE `id_workspace` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';