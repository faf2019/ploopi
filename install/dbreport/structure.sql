DROP TABLE IF EXISTS `ploopi_mod_dbreport_query`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_query` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `standard` tinyint(1) unsigned DEFAULT '0',
  `ws_activated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ws_id` varchar(32) NOT NULL,
  `ws_code` varchar(32) NOT NULL,
  `ws_ip` varchar(16) NOT NULL,
  `transformation` enum('','pivot_table') NOT NULL DEFAULT '',
  `pivot_x` int(10) unsigned NOT NULL DEFAULT '0',
  `pivot_y` int(10) unsigned NOT NULL DEFAULT '0',
  `pivot_val` int(10) unsigned NOT NULL DEFAULT '0',
  `chart` varchar(32) NOT NULL DEFAULT 'line',
  `chart_title` varchar(255) NOT NULL,
  `chart_subtitle` varchar(255) NOT NULL,
  `chart_x` int(10) unsigned NOT NULL DEFAULT '0',
  `chart_y` int(10) unsigned NOT NULL DEFAULT '0',
  `chart_val` int(10) unsigned NOT NULL DEFAULT '0',
  `chart_width` int(10) unsigned NOT NULL DEFAULT '500',
  `chart_height` int(10) unsigned NOT NULL DEFAULT '300',
  `chart_background` varchar(8) NOT NULL DEFAULT '#FFFFFF',
  `chart_colorset` varchar(16) NOT NULL DEFAULT 'default',
  `chart_color` varchar(8) NOT NULL DEFAULT '#FF0000',
  `chart_font` varchar(32) NOT NULL,
  `chart_border_width` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `chart_border_color` varchar(8) NOT NULL DEFAULT '#A0A0A0',
  `chart_title_font_size` tinyint(2) unsigned NOT NULL DEFAULT '16',
  `chart_title_font_color` varchar(8) NOT NULL DEFAULT '#000000',
  `chart_axis_font_size` tinyint(2) unsigned NOT NULL DEFAULT '10',
  `chart_axis_font_color` varchar(8) NOT NULL DEFAULT '#888888',
  `chart_axis_x_thickness` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `chart_axis_y_thickness` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `chart_axis_color` varchar(8) NOT NULL DEFAULT '#C0C0C0',
  `chart_legend_font_size` tinyint(2) unsigned NOT NULL DEFAULT '10',
  `chart_legend_font_color` varchar(8) NOT NULL DEFAULT '#888888',
  `chart_legend_align` enum('left','center','right') NOT NULL DEFAULT 'center',
  `chart_legend_valign` enum('top','center','bottom') NOT NULL DEFAULT 'bottom',
  `chart_legend_display` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `chart_indexes_font_size` tinyint(2) unsigned NOT NULL DEFAULT '10',
  `chart_indexes_font_color` varchar(8) NOT NULL DEFAULT '#888888',
  `chart_indexes_display` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chart_indexes_format` varchar(255) NOT NULL DEFAULT '{y}',
  `chart_indexes_rotation` int(3) unsigned NOT NULL DEFAULT '0',
  `chart_indexes_x` int(3) NOT NULL DEFAULT '0',
  `chart_indexes_y` int(3) NOT NULL DEFAULT '0',
  `chart_interlaced_x_color` varchar(8) NOT NULL DEFAULT ' #FFFFFF',
  `chart_interlaced_y_color` varchar(8) NOT NULL DEFAULT '#F0F0F0',
  `chart_interlaced_display` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chart_grid_color` varchar(8) NOT NULL DEFAULT '#D0D0D0',
  `chart_grid_x_thickness` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `chart_grid_y_thickness` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `chart_value_x_prefix` varchar(32) NOT NULL,
  `chart_value_x_suffix` varchar(32) NOT NULL,
  `chart_value_x_format` varchar(32) NOT NULL,
  `chart_value_y_prefix` varchar(32) NOT NULL,
  `chart_value_y_suffix` varchar(32) NOT NULL,
  `chart_value_y_format` varchar(32) NOT NULL,
  `chart_sort_x` enum('asc','desc','asc_val','desc_val') NOT NULL DEFAULT 'asc',
  `chart_sort_y` enum('asc','desc','asc_val','desc_val') NOT NULL DEFAULT 'asc',
  `chart_limit_x` tinyint(3) NOT NULL DEFAULT '0',
  `chart_limit_y` tinyint(3) NOT NULL DEFAULT '0',
  `chart_line_thickness` tinyint(2) unsigned NOT NULL DEFAULT '2',
  `chart_animation` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `chart_tooltip_format` varchar(255) NOT NULL DEFAULT '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned DEFAULT '0',
  `id_workspace` int(10) unsigned DEFAULT '0',
  `id_module` int(10) unsigned DEFAULT '0',
  `timestp_update` bigint(14) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_module` (`id_module`),
  KEY `locked` (`locked`),
  KEY `pivot_x` (`pivot_x`),
  KEY `pivot_y` (`pivot_y`),
  KEY `chart_x` (`chart_x`),
  KEY `chart_y` (`chart_y`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_queryfield`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_queryfield` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tablename` varchar(100) NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL DEFAULT '0',
  `fieldname` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `function` varchar(255) NOT NULL,
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort` varchar(20) NOT NULL,
  `criteria` varchar(100) NOT NULL,
  `type_criteria` varchar(20) NOT NULL,
  `or` varchar(100) NOT NULL,
  `type_or` varchar(20) NOT NULL,
  `intervals` varchar(255) NOT NULL,
  `operation` varchar(16) NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `series` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `id_query` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_query` (`id_query`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_queryrelation`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_queryrelation` (
  `id_query` int(10) unsigned NOT NULL DEFAULT '0',
  `tablename_src` varchar(100) NOT NULL,
  `fieldname_src` varchar(100) NOT NULL,
  `tablename_dest` varchar(100) NOT NULL,
  `fieldname_dest` varchar(100) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_query`,`tablename_src`,`fieldname_src`,`tablename_dest`,`fieldname_dest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_querytable`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_querytable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tablename` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `id_module_type` int(10) unsigned NOT NULL DEFAULT '0',
  `id_query` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tablename` (`tablename`,`alias`,`id_query`),
  KEY `id_module_type` (`id_module_type`),
  KEY `id_query` (`id_query`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ploopi_mod_dbreport_query_module_type`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_dbreport_query_module_type` (
  `id_query` int(10) unsigned NOT NULL DEFAULT '0',
  `id_module_type` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_query`,`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `ploopi_mod_dbreport_query` ADD `rowlimit` INT( 10 ) UNSIGNED NOT NULL DEFAULT '10000' AFTER `ws_ip`;
ALTER TABLE `ploopi_mod_dbreport_query` CHANGE `chart_tooltip_format` `chart_tooltip_format` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.0f}</b> ({point.percentage:.0f}%)<br/>';
ALTER TABLE `ploopi_mod_dbreport_queryfield` ADD `function_group` VARCHAR( 255 ) NOT NULL AFTER `function`;

ALTER TABLE  `ploopi_mod_dbreport_queryfield` CHANGE  `criteria`  `criteria` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE  `or`  `or` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE  `intervals`  `intervals` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

ALTER TABLE `ploopi_mod_dbreport_queryrelation` ADD `type_join` VARCHAR(16) NOT NULL DEFAULT 'inner' AFTER `fieldname_dest`;
