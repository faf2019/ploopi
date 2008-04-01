ALTER TABLE `dims_mod_forms` ADD `autobackup_date` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `autobackup` ;
ALTER TABLE `dims_mod_forms_reply` ADD `id_record` VARCHAR( 255 ) NOT NULL ,
ADD `id_object` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
