ALTER TABLE `ploopi_mod_dbreport_queryrelation` ADD `fieldname_src` VARCHAR( 100 ) NOT NULL AFTER `tablename_src`;
ALTER TABLE `ploopi_mod_dbreport_queryrelation` ADD `fieldname_dest` VARCHAR( 100 ) NOT NULL AFTER `tablename_dest`;
ALTER TABLE `ploopi_mod_dbreport_queryrelation` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mod_dbreport_queryrelation` ADD PRIMARY KEY ( `id_query` , `tablename_src` , `fieldname_src` , `tablename_dest` , `fieldname_dest` );
ALTER TABLE `ploopi_mod_dbreport_querytable` ADD `alias` VARCHAR( 100 ) NOT NULL AFTER `tablename`;
ALTER TABLE `ploopi_mod_dbreport_querytable` ADD UNIQUE (`tablename` ,`alias` ,`id_query`);
DELETE FROM `ploopi_mod_dbreport_querytable` WHERE ISNULL(`tablename`);
UPDATE `ploopi_mod_dbreport_querytable` SET `alias` = `tablename`;
ALTER TABLE `ploopi_mod_dbreport_querytable` CHANGE `tablename` `tablename` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `ploopi_mod_dbreport_queryfield`
    CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    CHANGE `tablename` `tablename` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `id_module_type` `id_module_type` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE `fieldname` `fieldname` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `label` `label` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `function` `function` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `visible` `visible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE `sort` `sort` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `criteria` `criteria` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `type_criteria` `type_criteria` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `or` `or` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `type_or` `type_or` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `intervals` `intervals` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `operation` `operation` VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
    CHANGE `position` `position` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE `series` `series` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    CHANGE `id_query` `id_query` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_dbreport_query` ADD `transformation` ENUM( '', 'pivot_table' ) NOT NULL DEFAULT '' AFTER `ws_ip`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `pivot_x` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `transformation` , ADD INDEX ( `pivot_x` );
ALTER TABLE `ploopi_mod_dbreport_query` ADD `pivot_y` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `pivot_x` , ADD INDEX ( `pivot_y` );
ALTER TABLE `ploopi_mod_dbreport_query` ADD `pivot_val` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `pivot_y`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart` ENUM( '','bar','stackedBar','stackedBar100','column','stackedColumn','stackedColumn100','stepLine','line','spline','area','splineArea','pie','doughnut' ) NOT NULL DEFAULT 'line' AFTER `pivot_val`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_x` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart` , ADD INDEX ( `chart_x` );
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_y` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_x` , ADD INDEX ( `chart_y` );
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_val` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_y`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_width` INT( 10 ) UNSIGNED NOT NULL DEFAULT '500' AFTER `chart_val` ,
ADD `chart_height` INT( 10 ) UNSIGNED NOT NULL DEFAULT '300' AFTER `chart_width` ,
ADD `chart_background` VARCHAR( 8 ) NOT NULL DEFAULT '#FFFFFF' AFTER `chart_height` ,
ADD `chart_colorset` VARCHAR( 16 ) NOT NULL DEFAULT 'default' AFTER `chart_background`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_color` VARCHAR( 8 ) NOT NULL DEFAULT '#FF0000' AFTER `chart_colorset`;


ALTER TABLE `ploopi_mod_dbreport_query`
    ADD `chart_font` VARCHAR(32) NOT NULL AFTER `chart_color`,
    ADD `chart_title_font_size` TINYINT(2) UNSIGNED NOT NULL DEFAULT '16' AFTER `chart_font`,
    ADD `chart_title_font_color` VARCHAR(8) NOT NULL DEFAULT '#000000' AFTER `chart_title_font_size`,
    ADD `chart_axis_font_size` TINYINT(2) UNSIGNED NOT NULL DEFAULT '10' AFTER `chart_title_font_color`,
    ADD `chart_axis_font_color` VARCHAR(8) NOT NULL DEFAULT '#888888' AFTER `chart_axis_font_size`,
    ADD `chart_legend_font_size` TINYINT(2) UNSIGNED NOT NULL DEFAULT '10' AFTER `chart_axis_font_color`,
    ADD `chart_legend_font_color` VARCHAR(8) NOT NULL DEFAULT '#888888' AFTER `chart_legend_font_size`,
    ADD `chart_legend_align` ENUM('left','center','right') NOT NULL DEFAULT 'center' AFTER `chart_legend_font_color`,
    ADD `chart_legend_valign` ENUM('top','center','bottom') NOT NULL DEFAULT 'bottom' AFTER `chart_legend_align`,
    ADD `chart_indexes_font_size` TINYINT(2) UNSIGNED NOT NULL DEFAULT '10' AFTER `chart_legend_valign`,
    ADD `chart_indexes_font_color` VARCHAR(8) NOT NULL DEFAULT '#888888' AFTER `chart_indexes_font_size`,
    ADD `chart_interlaced_x_color` VARCHAR(8) NOT NULL DEFAULT ' #FFFFFF' AFTER `chart_indexes_font_color`,
    ADD `chart_interlaced_y_color` VARCHAR(8) NOT NULL DEFAULT '#F0F0F0' AFTER `chart_interlaced_x_color`,
    ADD `chart_marker` ENUM('','circle','square','triangle','cross') NOT NULL AFTER `chart_interlaced_y_color`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_marker_size` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '10' AFTER `chart_marker`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_grid_color` VARCHAR( 8 ) NOT NULL DEFAULT '#D0D0D0' AFTER `chart_marker_size`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_grid_x_thickness` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_grid_color`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_grid_y_thickness` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `chart_grid_x_thickness`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_value_x_prefix` VARCHAR( 32 ) NOT NULL AFTER `chart_grid_y_thickness` ,
    ADD `chart_value_x_suffix` VARCHAR( 32 ) NOT NULL AFTER `chart_value_x_prefix` ,
    ADD `chart_value_x_format` VARCHAR( 32 ) NOT NULL AFTER `chart_value_x_suffix` ,
    ADD `chart_value_y_prefix` VARCHAR( 32 ) NOT NULL AFTER `chart_value_x_format` ,
    ADD `chart_value_y_suffix` VARCHAR( 32 ) NOT NULL AFTER `chart_value_y_prefix` ,
    ADD `chart_value_y_format` VARCHAR( 32 ) NOT NULL AFTER `chart_value_y_suffix`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_indexes_display` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_indexes_font_color`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_legend_display` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `chart_legend_valign`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_marker_display` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_marker_size`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_interlaced_display` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_interlaced_y_color`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_display_zero` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_value_y_format`;
ALTER TABLE `ploopi_mod_dbreport_query`
    ADD `chart_sort_x` ENUM( 'asc','desc','asc_val','desc_val' ) NOT NULL DEFAULT 'asc' AFTER `chart_display_zero` ,
    ADD `chart_sort_y` ENUM( 'asc','desc','asc_val','desc_val' ) NOT NULL DEFAULT 'asc' AFTER `chart_sort_x`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_show_percent` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_display_zero`;
ALTER TABLE `ploopi_mod_dbreport_query`
    ADD `chart_limit_x` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `chart_sort_y` ,
    ADD `chart_limit_y` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `chart_limit_x`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_legend_marker` ENUM( '', 'circle', 'square', 'triangle', 'cross' ) NOT NULL DEFAULT 'circle' AFTER `chart_legend_display`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_line_thickness` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '2' AFTER `chart_limit_y`;

ALTER TABLE `ploopi_mod_dbreport_query`
    DROP `chart_legend_marker`,
    DROP `chart_marker`,
    DROP `chart_marker_size`,
    DROP `chart_marker_display`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_indexes_rotation` INT( 3 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_indexes_display`;
ALTER TABLE `ploopi_mod_dbreport_query` DROP `chart_display_zero`;
ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_grid_display` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_grid_y_thickness`;
ALTER TABLE `ploopi_mod_dbreport_query`
    ADD `chart_title` VARCHAR( 255 ) NOT NULL AFTER `chart` ,
    ADD `chart_subtitle` VARCHAR( 255 ) NOT NULL AFTER `chart_title`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_axis_x_thickness` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `chart_axis_font_color` ,
ADD `chart_axis_y_thickness` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `chart_axis_x_thickness` ,
ADD `chart_axis_color` VARCHAR( 8 ) NOT NULL DEFAULT '#C0C0C0' AFTER `chart_axis_y_thickness`;

ALTER TABLE `ploopi_mod_dbreport_query` DROP `chart_grid_display`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_indexes_x` INT( 3 ) NOT NULL DEFAULT '0' AFTER `chart_indexes_rotation` ,
ADD `chart_indexes_y` INT( 3 ) NOT NULL DEFAULT '0' AFTER `chart_indexes_x`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_animation` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `chart_line_thickness`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_border_width` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `chart_font` ,
ADD `chart_border_color` VARCHAR( 8 ) NOT NULL DEFAULT '#A0A0A0' AFTER `chart_border_width`;

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_tooltip_format` VARCHAR( 255 ) NOT NULL DEFAULT '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>' AFTER `chart_animation` ;

ALTER TABLE `ploopi_mod_dbreport_query` CHANGE `chart` `chart` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'line';

ALTER TABLE `ploopi_mod_dbreport_query` ADD `chart_indexes_format` VARCHAR( 255 ) NOT NULL DEFAULT '{y}' AFTER `chart_indexes_display`;

ALTER TABLE `ploopi_mod_dbreport_query` DROP `chart_show_percent`;
