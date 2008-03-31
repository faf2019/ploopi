ALTER TABLE `ploopi_mb_action` ADD `role_enabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1';

UPDATE `ploopi_module_type` SET `version` = '1.0RC1b', `author` = 'Ovensia', `date` = '20080328000000' WHERE `ploopi_module_type`.`id` = 1;
