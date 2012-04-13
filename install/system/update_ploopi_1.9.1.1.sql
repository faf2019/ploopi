ALTER TABLE `ploopi_user` DROP INDEX `FT`;
ALTER TABLE `ploopi_user` ADD FULLTEXT `ft` (`lastname` ,`firstname` ,`email` ,`comments` ,`service` ,`service2` ,`function` ,`city` ,`building` ,`floor` ,`office` ,`rank`);
UPDATE `ploopi_module_type` SET `version` = '1.9.1.1', `author` = 'Ovensia', `date` = '20120329000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
