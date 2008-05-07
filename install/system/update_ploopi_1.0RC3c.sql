ALTER TABLE `ploopi_workspace` ADD INDEX ( `code` );
ALTER TABLE `ploopi_workspace` ADD INDEX ( `id_workspace` );

UPDATE `ploopi_module_type` SET `version` = '1.0RC3c', `author` = 'Ovensia', `date` = '20080507000000' WHERE `ploopi_module_type`.`id` = 1;
