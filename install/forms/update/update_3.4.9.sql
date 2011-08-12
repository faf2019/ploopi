ALTER TABLE `ploopi_mod_forms_field` ADD `html` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `separator`;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_disablexhtmlfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_pagebreak`;
ALTER TABLE `ploopi_mod_forms_field` ADD `xhtmlcontent` LONGTEXT NOT NULL AFTER `description`;
ALTER TABLE `ploopi_mod_forms_field` ADD `xhtmlcontent_cleaned` LONGTEXT NOT NULL AFTER `xhtmlcontent`;
ALTER TABLE `ploopi_mod_forms_field` ADD `export_width` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_forms_form` ADD `export_landscape` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `cms_link` ,
ADD `export_fitpage_width` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `export_landscape` ,
ADD `export_fitpage_height` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `export_fitpage_width` ,
ADD `export_border` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `export_fitpage_height`;


CREATE TABLE `ploopi_mod_forms_group` (
`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`label` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM ;


ALTER TABLE `ploopi_mod_forms_field` ADD `id_group` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_form`;

ALTER TABLE `ploopi_mod_forms_group` ADD `conditions` LONGTEXT NOT NULL ,
ADD `formula` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `ploopi_mod_forms_group` ADD `description` LONGTEXT NOT NULL AFTER `label`;
