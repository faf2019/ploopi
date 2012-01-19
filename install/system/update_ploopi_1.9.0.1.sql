ALTER TABLE `ploopi_documents_file` ADD `md5id` VARCHAR( 32 ) NOT NULL AFTER `id` , ADD INDEX ( `md5id` );
ALTER TABLE `ploopi_documents_folder` ADD `md5id` VARCHAR( 32 ) NOT NULL AFTER `id` , ADD INDEX ( `md5id` ); 
UPDATE `ploopi_documents_file` SET `md5id` = MD5(CONCAT(id, '_', timestp_create));
UPDATE `ploopi_documents_folder` SET `md5id` = MD5(CONCAT(id, '_', timestp_create));
UPDATE `ploopi_module_type` SET `version` = '1.9.0.1', `author` = 'Ovensia', `date` = '20120113000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;