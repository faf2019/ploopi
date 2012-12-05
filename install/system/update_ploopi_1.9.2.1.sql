ALTER TABLE `ploopi_module_workspace` ADD INDEX ( `id_module` ) ;
ALTER TABLE `ploopi_module_workspace` ADD INDEX ( `id_workspace` ) ;
ALTER TABLE `ploopi_module_type` ADD INDEX ( `label` ) ;
ALTER TABLE `ploopi_group` ADD INDEX ( `parents` ) ;
ALTER TABLE `ploopi_workspace_group` ADD INDEX ( `id_group` ) ;

UPDATE `ploopi_module_type` SET `version` = '1.9.2.1', `author` = 'Ovensia', `date` = '20121205000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
