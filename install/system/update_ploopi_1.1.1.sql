ALTER TABLE `ploopi_group` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_group` ADD INDEX ( `shared` );
ALTER TABLE `ploopi_group` ADD INDEX ( `system` );
ALTER TABLE `ploopi_group` ADD INDEX ( `protected` );
ALTER TABLE `ploopi_mb_action` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_mb_action` ADD INDEX ( `id_object` );
ALTER TABLE `ploopi_mb_action` ADD INDEX ( `role_enabled` );
ALTER TABLE `ploopi_mb_field` ADD INDEX ( `visible` );
ALTER TABLE `ploopi_module` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_module` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_module` ADD INDEX ( `active` );
ALTER TABLE `ploopi_module` ADD INDEX ( `shared` );
ALTER TABLE `ploopi_module` ADD INDEX ( `herited` );
ALTER TABLE `ploopi_role` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_role` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_role` ADD INDEX ( `shared` );
ALTER TABLE `ploopi_session` CHANGE `id` `id` CHAR( 32 ) NOT NULL;
ALTER TABLE `ploopi_share` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_share` ADD INDEX ( `id_share` );
ALTER TABLE `ploopi_share` ADD INDEX ( `type_share` );
ALTER TABLE `ploopi_ticket_status` CHANGE `timestp` `timestp` BIGINT( 14 ) NOT NULL;
ALTER TABLE `ploopi_user` ADD INDEX ( `lastname` );
ALTER TABLE `ploopi_user` ADD INDEX ( `firstname` );
ALTER TABLE `ploopi_workflow` ADD INDEX ( `type_workflow` );
ALTER TABLE `ploopi_workflow` ADD INDEX ( `id_workflow` );
ALTER TABLE `ploopi_workflow` ADD INDEX ( `id_module_type` );

UPDATE `ploopi_module_type` SET `version` = '1.1.1', `author` = 'Ovensia', `date` = '20080807000000' WHERE `ploopi_module_type`.`id` = 1;

