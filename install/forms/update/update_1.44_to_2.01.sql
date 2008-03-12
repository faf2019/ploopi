ALTER TABLE `dims_mod_forms_field` DROP `option_cmsgroupby`, DROP `option_cmsorderby`, DROP `option_cmsdisplaylabel`, DROP `option_cmsshowfilter`;
ALTER TABLE `dims_mod_forms_field` ADD `option_wceview` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_exportview` ;
ALTER TABLE `dims_mod_forms` CHANGE `pubdate_end` `pubdate_end` BIGINT( 14 ) NULL DEFAULT NULL;
ALTER TABLE `dims_mod_forms` CHANGE `pubdate_start` `pubdate_start` BIGINT( 14 ) NULL DEFAULT NULL;
ALTER TABLE `dims_mod_forms_reply` DROP INDEX `id_2`;
ALTER TABLE `dims_mod_forms_reply` DROP INDEX `id`;
ALTER TABLE `dims_mod_forms_reply` ADD INDEX ( `id_forms` );
ALTER TABLE `dims_mod_forms_reply` ADD INDEX ( `id_user` );
ALTER TABLE `dims_mod_forms_reply` ADD INDEX ( `id_workspace` );
ALTER TABLE `dims_mod_forms_reply` ADD INDEX ( `id_module` );
ALTER TABLE `dims_mod_forms_field` DROP INDEX `id_2`;
ALTER TABLE `dims_mod_forms_field` DROP INDEX `id` ;
ALTER TABLE `dims_mod_forms_field` ADD INDEX ( `id_forms` );

ALTER TABLE `dims_mod_forms` ADD `autobackup_date` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `autobackup` ;
ALTER TABLE `dims_mod_forms_reply` ADD `id_record` VARCHAR( 255 ) NOT NULL ,
ADD `id_object` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

RENAME TABLE `dims_mod_forms` TO `ploopi_mod_forms`;
RENAME TABLE `dims_mod_forms_field` TO `ploopi_mod_forms_field`;
RENAME TABLE `dims_mod_forms_reply` TO `ploopi_mod_forms_reply`;
RENAME TABLE `dims_mod_forms_reply_field` TO `ploopi_mod_forms_reply_field`;

RENAME TABLE `ploopi_mod_forms` TO `ploopi_mod_forms_form` ;
ALTER TABLE `ploopi_mod_forms_field` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_forms_reply` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_forms_reply_field` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
