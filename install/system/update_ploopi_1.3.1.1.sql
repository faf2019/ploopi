ALTER TABLE `ploopi_user` ADD `building` VARCHAR( 255 ) NOT NULL AFTER `timezone` , ADD `floor` VARCHAR( 32 ) NOT NULL AFTER `building` ;
ALTER TABLE `ploopi_user` ADD `rank` VARCHAR( 32 ) NOT NULL ;

UPDATE `ploopi_module_type` SET `version` = '1.3.1.1', `author` = 'Ovensia', `date` = '20090216000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;