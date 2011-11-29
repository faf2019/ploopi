ALTER TABLE `ploopi_user` ADD `service2` VARCHAR( 255 ) NOT NULL AFTER `service`;
UPDATE `ploopi_module_type` SET `version` = '1.8.9.5', `author` = 'Ovensia', `date` = '20111129000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
