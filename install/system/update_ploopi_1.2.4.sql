ALTER TABLE `ploopi_user` ADD `number` VARCHAR( 255 ) NOT NULL AFTER `function`;

UPDATE `ploopi_module_type` SET `version` = '1.2.4', `author` = 'Ovensia', `date` = '20090116000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;