ALTER TABLE `ploopi_workspace` CHANGE `code` `code` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_workspace` ADD INDEX ( `code` );
UPDATE `ploopi_module_type` SET `version` = '1.9.5.5', `author` = 'Ovensia', `date` = '20151202000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
