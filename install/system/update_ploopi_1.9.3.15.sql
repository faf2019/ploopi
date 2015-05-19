ALTER TABLE `ploopi_user` ADD `disabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', ADD INDEX ( `disabled` );
UPDATE `ploopi_module_type` SET `version` = '1.9.3.15', `author` = 'Ovensia', `date` = '20150518000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
