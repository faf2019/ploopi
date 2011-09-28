ALTER TABLE `ploopi_workspace` ADD `priority` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0', ADD INDEX ( `priority` );
UPDATE `ploopi_workspace` SET priority = 1 * POW(10, depth-2);

UPDATE `ploopi_module_type` SET `version` = '1.8.9.3', `author` = 'Ovensia', `date` = '20110927000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
