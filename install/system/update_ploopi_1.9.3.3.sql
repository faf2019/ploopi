ALTER TABLE `ploopi_role_action` ADD INDEX ( `id_role` );
ALTER TABLE `ploopi_role_action` ADD INDEX ( `id_action` );
ALTER TABLE `ploopi_role_action` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_workspace_group_role` ADD INDEX ( `id_group` );
ALTER TABLE `ploopi_workspace_user_role` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_workspace_group` ADD INDEX ( `id_group` );
ALTER TABLE `ploopi_workspace` ADD INDEX ( `system` );
ALTER TABLE `ploopi_param_workspace` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_param_workspace` ADD INDEX ( `name` );
ALTER TABLE `ploopi_param_workspace` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_param_user` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_param_user` ADD INDEX ( `name` );
ALTER TABLE `ploopi_param_user` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_param_type` ADD INDEX ( `name` );
ALTER TABLE `ploopi_param_choice` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_group_user` ADD INDEX ( `id_group` );

UPDATE `ploopi_module_type` SET `version` = '1.9.3.3', `author` = 'Ovensia', `date` = '20130604000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
