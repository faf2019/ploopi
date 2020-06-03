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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

ALTER TABLE `ploopi_mod_forms_form` ADD `option_multidisplaysave` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_adminonly` ,
ADD `option_multidisplaypages` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_multidisplaysave` ;

ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_user` );

ALTER TABLE `ploopi_mod_forms_field` ADD `html` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `separator`;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_disablexhtmlfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_pagebreak`;
ALTER TABLE `ploopi_mod_forms_field` ADD `xhtmlcontent` LONGTEXT NOT NULL AFTER `description`;
ALTER TABLE `ploopi_mod_forms_field` ADD `xhtmlcontent_cleaned` LONGTEXT NOT NULL AFTER `xhtmlcontent`;
ALTER TABLE `ploopi_mod_forms_field` ADD `export_width` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_forms_form` ADD `export_landscape` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `cms_link` ,
ADD `export_fitpage_width` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `export_landscape` ,
ADD `export_fitpage_height` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `export_fitpage_width` ,
ADD `export_border` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `export_fitpage_height`;

DROP TABLE IF EXISTS `ploopi_mod_forms_group`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_forms_group` (
`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`label` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;


ALTER TABLE `ploopi_mod_forms_field` ADD `id_group` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_form`;

ALTER TABLE `ploopi_mod_forms_group` ADD `conditions` LONGTEXT NOT NULL ,
ADD `formula` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `ploopi_mod_forms_group` ADD `description` LONGTEXT NOT NULL AFTER `label`;

ALTER TABLE `ploopi_mod_forms_graphic` ADD `line1_filter` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `line5_color` ,
ADD `line2_filter` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `line1_filter` ,
ADD `line3_filter` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `line2_filter` ,
ADD `line4_filter` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `line3_filter` ,
ADD `line5_filter` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `line4_filter` ;

ALTER TABLE `ploopi_mod_forms_graphic` ADD `line1_legend` VARCHAR( 255 ) NOT NULL AFTER `line5_filter_value` ,
ADD `line2_legend` VARCHAR( 255 ) NOT NULL AFTER `line1_legend` ,
ADD `line3_legend` VARCHAR( 255 ) NOT NULL AFTER `line2_legend` ,
ADD `line4_legend` VARCHAR( 255 ) NOT NULL AFTER `line3_legend` ,
ADD `line5_legend` VARCHAR( 255 ) NOT NULL AFTER `line4_legend` ;



UPDATE `ploopi_mod_forms_form` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `pubdate_start` = 0  WHERE ISNULL(`pubdate_start`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `pubdate_start` `pubdate_start` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `pubdate_end` = 0  WHERE ISNULL(`pubdate_end`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `pubdate_end` `pubdate_end` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `email_from` = '' WHERE ISNULL(`email_from`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `email_from` `email_from` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_onlyone` = 0  WHERE ISNULL(`option_onlyone`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_onlyone` `option_onlyone` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_onlyoneday` = 0  WHERE ISNULL(`option_onlyoneday`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_onlyoneday` `option_onlyoneday` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `style` = '' WHERE ISNULL(`style`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `style` `style` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `nbline` = 0  WHERE ISNULL(`nbline`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `nbline` `nbline` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `model` = '' WHERE ISNULL(`model`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `model` `model` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `typeform` = '' WHERE ISNULL(`typeform`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `typeform` `typeform` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_displayuser` = 0  WHERE ISNULL(`option_displayuser`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_displayuser` `option_displayuser` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_displaygroup` = 0  WHERE ISNULL(`option_displaygroup`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_displaygroup` `option_displaygroup` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_displaydate` = 0  WHERE ISNULL(`option_displaydate`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_displaydate` `option_displaydate` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `option_displayip` = 0  WHERE ISNULL(`option_displayip`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `option_displayip` `option_displayip` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `viewed` = 0  WHERE ISNULL(`viewed`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `viewed` `viewed` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `autobackup` = 0  WHERE ISNULL(`autobackup`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `autobackup` `autobackup` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `cms_response` = '' WHERE ISNULL(`cms_response`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `cms_response` `cms_response` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `cms_link` = 0  WHERE ISNULL(`cms_link`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `cms_link` `cms_link` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_form` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_forms_form` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_group` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_forms_group` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_group` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_forms_group` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_group` SET `conditions` = '' WHERE ISNULL(`conditions`);
ALTER TABLE `ploopi_mod_forms_group` CHANGE `conditions` `conditions` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_group` SET `formula` = '' WHERE ISNULL(`formula`);
ALTER TABLE `ploopi_mod_forms_group` CHANGE `formula` `formula` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `separator` = 0  WHERE ISNULL(`separator`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `separator` `separator` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `separator_level` = 0  WHERE ISNULL(`separator_level`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `separator_level` `separator_level` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `separator_fontsize` = 0  WHERE ISNULL(`separator_fontsize`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `separator_fontsize` `separator_fontsize` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `type` = '' WHERE ISNULL(`type`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `type` `type` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `format` = '' WHERE ISNULL(`format`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `format` `format` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `values` = '' WHERE ISNULL(`values`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `values` `values` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `formula` = '' WHERE ISNULL(`formula`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `formula` `formula` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `xhtmlcontent` = '' WHERE ISNULL(`xhtmlcontent`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `xhtmlcontent` `xhtmlcontent` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `xhtmlcontent_cleaned` = '' WHERE ISNULL(`xhtmlcontent_cleaned`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `xhtmlcontent_cleaned` `xhtmlcontent_cleaned` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `position` = 0  WHERE ISNULL(`position`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `position` `position` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `maxlength` = 0  WHERE ISNULL(`maxlength`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `maxlength` `maxlength` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `style_field` = '' WHERE ISNULL(`style_field`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `style_field` `style_field` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `style_form` = '' WHERE ISNULL(`style_form`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `style_form` `style_form` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `cols` = 0  WHERE ISNULL(`cols`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `cols` `cols` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `option_needed` = 0  WHERE ISNULL(`option_needed`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `option_needed` `option_needed` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `option_arrayview` = 0  WHERE ISNULL(`option_arrayview`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `option_arrayview` `option_arrayview` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `option_exportview` = 0  WHERE ISNULL(`option_exportview`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `option_exportview` `option_exportview` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_forms_field` SET `defaultvalue` = '' WHERE ISNULL(`defaultvalue`);
ALTER TABLE `ploopi_mod_forms_field` CHANGE `defaultvalue` `defaultvalue` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `type` = '' WHERE ISNULL(`type`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `type` `type` enum('pie','pie3d','bar','barc','line','linec','radar','radarc') NOT NULL DEFAULT 'pie' COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line_aggregation` = '' WHERE ISNULL(`line_aggregation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line_aggregation` `line_aggregation` enum('hour','day','week','month') NOT NULL DEFAULT 'hour'  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `operation` = '' WHERE ISNULL(`operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `operation` `operation` enum('count','sum','avg') NOT NULL DEFAULT 'count' COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line1_operation` = '' WHERE ISNULL(`line1_operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line1_operation` `line1_operation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line2_operation` = '' WHERE ISNULL(`line2_operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line2_operation` `line2_operation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line3_operation` = '' WHERE ISNULL(`line3_operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line3_operation` `line3_operation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line4_operation` = '' WHERE ISNULL(`line4_operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line4_operation` `line4_operation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line5_operation` = '' WHERE ISNULL(`line5_operation`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line5_operation` `line5_operation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line1_color` = '' WHERE ISNULL(`line1_color`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line1_color` `line1_color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line2_color` = '' WHERE ISNULL(`line2_color`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line2_color` `line2_color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line3_color` = '' WHERE ISNULL(`line3_color`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line3_color` `line3_color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line4_color` = '' WHERE ISNULL(`line4_color`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line4_color` `line4_color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line5_color` = '' WHERE ISNULL(`line5_color`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line5_color` `line5_color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line1_filter_op` = '' WHERE ISNULL(`line1_filter_op`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line1_filter_op` `line1_filter_op` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line2_filter_op` = '' WHERE ISNULL(`line2_filter_op`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line2_filter_op` `line2_filter_op` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line3_filter_op` = '' WHERE ISNULL(`line3_filter_op`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line3_filter_op` `line3_filter_op` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line4_filter_op` = '' WHERE ISNULL(`line4_filter_op`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line4_filter_op` `line4_filter_op` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line5_filter_op` = '' WHERE ISNULL(`line5_filter_op`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line5_filter_op` `line5_filter_op` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line1_filter_value` = '' WHERE ISNULL(`line1_filter_value`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line1_filter_value` `line1_filter_value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line2_filter_value` = '' WHERE ISNULL(`line2_filter_value`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line2_filter_value` `line2_filter_value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line3_filter_value` = '' WHERE ISNULL(`line3_filter_value`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line3_filter_value` `line3_filter_value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line4_filter_value` = '' WHERE ISNULL(`line4_filter_value`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line4_filter_value` `line4_filter_value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line5_filter_value` = '' WHERE ISNULL(`line5_filter_value`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line5_filter_value` `line5_filter_value` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line1_legend` = '' WHERE ISNULL(`line1_legend`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line1_legend` `line1_legend` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line2_legend` = '' WHERE ISNULL(`line2_legend`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line2_legend` `line2_legend` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line3_legend` = '' WHERE ISNULL(`line3_legend`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line3_legend` `line3_legend` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line4_legend` = '' WHERE ISNULL(`line4_legend`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line4_legend` `line4_legend` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `line5_legend` = '' WHERE ISNULL(`line5_legend`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `line5_legend` `line5_legend` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `pie_color1` = '' WHERE ISNULL(`pie_color1`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `pie_color1` `pie_color1` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_forms_graphic` SET `pie_color2` = '' WHERE ISNULL(`pie_color2`);
ALTER TABLE `ploopi_mod_forms_graphic` CHANGE `pie_color2` `pie_color2` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
