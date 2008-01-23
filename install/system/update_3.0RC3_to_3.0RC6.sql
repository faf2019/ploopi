ALTER TABLE `dims_workspace` DROP `typegroup`;
ALTER TABLE `dims_documents_folder` ADD `system` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `nbelements` ;
ALTER TABLE `dims_documents_file` ADD `label` VARCHAR( 255 ) NOT NULL AFTER `name` ;

INSERT INTO `dims_mb_action` (`id_module_type` ,`id_action` ,`label` ,`description` ,`id_workspace` ,`id_object`) VALUES ('1', '32', 'Mettre à jour un module', NULL , '0', '0');

ALTER TABLE `dims_documents_file` ADD `ref` VARCHAR( 32 ) NOT NULL AFTER `description` ,
ADD `timestp_file` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ref` ;
UPDATE `dims_module_type` SET `version` = '2.99l' WHERE `dims_module_type`.`id` =1;
