ALTER TABLE `ploopi_workspace` DROP INDEX `code` ;
ALTER TABLE `ploopi_workspace` CHANGE `code` `code` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;

UPDATE `ploopi_module_type` SET `version` = '1.4.0.1', `author` = 'Ovensia', `date` = '20090504000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;