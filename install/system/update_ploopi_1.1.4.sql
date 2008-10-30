ALTER TABLE `ploopi_module` ADD `visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `active`;
UPDATE `ploopi_module` SET `visible` = `active`;

ALTER TABLE `ploopi_user_action_log` CHANGE `ip` `ip` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL; 

UPDATE `ploopi_module_type` SET `version` = '1.1.4', `author` = 'Ovensia', `date` = '20081029000000' WHERE `ploopi_module_type`.`id` = 1;