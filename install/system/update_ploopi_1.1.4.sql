ALTER TABLE `ploopi_module` ADD `visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `active`;
UPDATE `ploopi_module` SET `visible` = `active`;

UPDATE `ploopi_module_type` SET `version` = '1.1.4', `author` = 'Ovensia', `date` = '20081027000000' WHERE `ploopi_module_type`.`id` = 1;