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
