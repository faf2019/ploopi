ALTER TABLE `ploopi_log` ADD INDEX ( `ts` );
UPDATE `ploopi_module_type` SET `version` = '1.0RC1c', `author` = 'Ovensia', `date` = '20080328000000' WHERE `ploopi_module_type`.`id` = 1;
