ALTER TABLE `ploopi_mod_dbreport_query` ADD `rowlimit` INT( 10 ) UNSIGNED NOT NULL DEFAULT '10000' AFTER `ws_ip`;
ALTER TABLE `ploopi_mod_dbreport_query` CHANGE `chart_tooltip_format` `chart_tooltip_format` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '<span style="color:{series.color}">{series.name}</span>: <b>{point.y:.0f}</b> ({point.percentage:.0f}%)<br/>';
ALTER TABLE `ploopi_mod_dbreport_queryfield` ADD `function_group` VARCHAR( 255 ) NOT NULL AFTER `function`;
