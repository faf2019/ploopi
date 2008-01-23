ALTER TABLE `dims_mod_forms_reply_field` ADD INDEX ( `id_reply` );
ALTER TABLE `dims_mod_forms_reply_field` ADD INDEX ( `id_forms` );
ALTER TABLE `dims_mod_forms_reply_field` ADD INDEX ( `id_field` );

ALTER TABLE `dims_mod_forms` ADD `option_modify` VARCHAR( 16 ) NOT NULL DEFAULT 'nobody' AFTER `typeform` ;
 ALTER TABLE `dims_mod_forms` ADD `option_view` VARCHAR( 16 ) NOT NULL DEFAULT 'global' AFTER `option_modify` ;

ALTER TABLE `dims_mod_forms` DROP `option_userview` ,
DROP `option_usermodify` ,
DROP `option_groupview` ,
DROP `option_groupmodify` ,
DROP `option_allview` ,
DROP `option_allmodify` ;

ALTER TABLE `dims_mod_forms_field` ADD `interline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_forms` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `dims_mod_forms_reply` CHANGE `id_group` `id_workspace` TINYINT( 3 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `dims_mod_forms` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `dims_mod_forms_reply` CHANGE `id_group` `id_workspace` TINYINT( 3 ) UNSIGNED NULL DEFAULT '0';
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
