ALTER TABLE `dims_documents_file` ADD `ref` VARCHAR( 32 ) NOT NULL AFTER `description` ,
ADD `timestp_file` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ref` ;
UPDATE `dims_module_type` SET `version` = '3.0' WHERE `dims_module_type`.`id` =1;

