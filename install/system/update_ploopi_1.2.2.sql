ALTER TABLE `ploopi_group_user` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_workspace_group` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_workspace_user_role` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_workspace_user_role` ADD INDEX ( `id_role` );
ALTER TABLE `ploopi_workspace_group_role` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_workspace_group_role` ADD INDEX ( `id_role` );

UPDATE `ploopi_module_type` SET `version` = '1.2.2', `author` = 'Ovensia', `date` = '20081210000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;