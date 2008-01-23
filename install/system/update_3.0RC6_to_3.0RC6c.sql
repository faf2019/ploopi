ALTER TABLE `dims_param_type` CHANGE `default_value` `default_value` TEXT  NULL;
ALTER TABLE `dims_param_default` CHANGE `value` `value` TEXT  NULL;
ALTER TABLE `dims_param_group` CHANGE `value` `value` TEXT  NULL;
ALTER TABLE `dims_param_user` CHANGE `value` `value` TEXT  NULL;
ALTER TABLE `dims_documents_file` ADD `ref` VARCHAR( 32 ) NOT NULL AFTER `description` ,
ADD `timestp_file` BIGINT( 14 ) UNSIGNED NOT NULL AFTER `ref` ;
UPDATE dims_module_type SET label = 'webedit' WHERE label = 'wce';
UPDATE `dims_module_type` SET `version` = '3.0RC6c' WHERE `dims_module_type`.`id` = 1;
