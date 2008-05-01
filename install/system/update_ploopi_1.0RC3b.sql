ALTER TABLE `ploopi_user` CHANGE `timezone` `timezone` VARCHAR( 64 ) NOT NULL;
ALTER TABLE `ploopi_user` ADD `servertimezone` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `ticketsbyemail` ;
UPDATE `ploopi_module_type` SET `version` = '1.0RC3c', `author` = 'Ovensia', `date` = '20080428000000' WHERE `ploopi_module_type`.`id` = 1;
