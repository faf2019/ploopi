DROP TABLE IF EXISTS `ploopi_mod_forms_field`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `fieldname` varchar(255) NOT NULL default '',
  `captcha` tinyint(1) unsigned NOT NULL default '0',
  `separator` tinyint(1) unsigned default '0',
  `separator_level` int(10) unsigned default '0',
  `separator_fontsize` int(10) unsigned default '0',
  `type` varchar(16) default NULL,
  `format` varchar(16) default NULL,
  `values` longtext,
  `description` longtext,
  `position` int(10) unsigned default '0',
  `maxlength` int(10) unsigned default '0',
  `style` varchar(255) NOT NULL,
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
  `description` longtext,
  `pubdate_start` bigint(14) default NULL,
  `pubdate_end` bigint(14) default NULL,
  `email_from` varchar(255) NOT NULL,
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

DROP TABLE IF EXISTS `ploopi_mod_forms_graphic`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_graphic` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `id_form` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `timefield` int(10) unsigned NOT NULL default '0',
  `type` enum('pie','pie3d','bar','barc','line','linec','radar','radarc') NOT NULL,
  `percent` tinyint(1) unsigned NOT NULL default '0',
  `filled` tinyint(1) unsigned NOT NULL default '0',
  `line_aggregation` enum('hour','day','week','month') NOT NULL,
  `operation` enum('count','sum','avg') NOT NULL,
  `line1_field` int(10) unsigned NOT NULL default '0',
  `line2_field` int(10) unsigned NOT NULL default '0',
  `line3_field` int(10) unsigned NOT NULL default '0',
  `line4_field` int(10) unsigned NOT NULL default '0',
  `line5_field` int(10) unsigned NOT NULL default '0',
  `line1_operation` varchar(16) NOT NULL,
  `line2_operation` varchar(16) NOT NULL,
  `line3_operation` varchar(16) NOT NULL,
  `line4_operation` varchar(16) NOT NULL,
  `line5_operation` varchar(16) NOT NULL,
  `line1_color` varchar(16) NOT NULL,
  `line2_color` varchar(16) NOT NULL,
  `line3_color` varchar(16) NOT NULL,
  `line4_color` varchar(16) NOT NULL,
  `line5_color` varchar(16) NOT NULL,
  `line1_filter_op` varchar(16) NOT NULL,
  `line2_filter_op` varchar(16) NOT NULL,
  `line3_filter_op` varchar(16) NOT NULL,
  `line4_filter_op` varchar(16) NOT NULL,
  `line5_filter_op` varchar(16) NOT NULL,
  `line1_filter_value` varchar(255) NOT NULL,
  `line2_filter_value` varchar(255) NOT NULL,
  `line3_filter_value` varchar(255) NOT NULL,
  `line4_filter_value` varchar(255) NOT NULL,
  `line5_filter_value` varchar(255) NOT NULL,
  `pie_field` int(10) unsigned NOT NULL default '0',
  `pie_color1` varchar(16) NOT NULL,
  `pie_color2` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_forms_field` CHANGE `style` `style_field` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_mod_forms_field` ADD `style_form` VARCHAR( 255 ) NOT NULL AFTER `style_field`;
ALTER TABLE `ploopi_mod_forms_form` CHANGE `width` `style` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
UPDATE `ploopi_mod_forms_form` SET `style` = '';
ALTER TABLE `ploopi_mod_forms_field` ADD `formula` VARCHAR( 255 ) NOT NULL AFTER `values`;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_adminonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_wceview` ;
ALTER TABLE `ploopi_mod_forms_form` ADD `option_adminonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_displayip` ;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_formview` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_needed` ;
UPDATE `ploopi_mod_forms_field` SET `option_formview` = 1;

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_font` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `param_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.2',
ADD `param_fill_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_margin_left` INT( 10 ) UNSIGNED NOT NULL DEFAULT '40',
ADD `param_margin_right` INT( 10 ) UNSIGNED NOT NULL DEFAULT '20',
ADD `param_margin_top` INT( 10 ) UNSIGNED NOT NULL DEFAULT '120',
ADD `param_margin_bottom` INT( 10 ) UNSIGNED NOT NULL DEFAULT '60',
ADD `param_center_x` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_center_y` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_shadow_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.8',
ADD `param_label_angle` DOUBLE UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_font_size_title` INT( 10 ) UNSIGNED NOT NULL DEFAULT '15',
ADD `param_font_size_legend` INT( 10 ) UNSIGNED NOT NULL DEFAULT '8',
ADD `param_font_size_data` INT( 10 ) UNSIGNED NOT NULL DEFAULT '10';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_mark_type` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `param_mark_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.3';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_mark_width` INT( 10 ) UNSIGNED NOT NULL DEFAULT '3';

UPDATE `ploopi_mod_forms_graphic` SET `param_font` = 18;
UPDATE `ploopi_mod_forms_graphic` SET `param_mark_type` = 1;

ALTER TABLE `ploopi_mod_forms_field` ADD `option_pagebreak` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_adminonly` ;