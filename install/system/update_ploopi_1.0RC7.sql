ALTER TABLE `ploopi_connecteduser` ADD PRIMARY KEY ( `sid` );
ALTER TABLE `ploopi_connecteduser` ADD INDEX ( `workspace_id` );
ALTER TABLE `ploopi_connecteduser` ADD INDEX ( `user_id` );
ALTER TABLE `ploopi_connecteduser` ADD INDEX ( `module_id` );
ALTER TABLE `ploopi_connecteduser` CHANGE `timestp` `timestp` BIGINT( 14 ) NULL DEFAULT '00000000000000';
ALTER TABLE `ploopi_connecteduser` ADD INDEX ( `timestp` );
ALTER TABLE `ploopi_connecteduser` CHANGE `sid` `sid` CHAR( 32 ) NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_connecteduser` CHANGE `ip` `ip` CHAR( 15 ) NULL DEFAULT NULL; 
ALTER TABLE `ploopi_connecteduser` CHANGE `domain` `domain` VARCHAR( 255 ) NOT NULL; 
ALTER TABLE `ploopi_documents_ext` ADD PRIMARY KEY ( `ext` );
ALTER TABLE `ploopi_documents_ext` DROP INDEX `ext`;
ALTER TABLE `ploopi_param_choice` ADD PRIMARY KEY ( `id_module_type` , `name` , `value` );
ALTER TABLE `ploopi_param_choice` DROP INDEX `id_module_type` ;
ALTER TABLE `ploopi_param_choice` ADD INDEX ( `value` ) ;
ALTER TABLE `ploopi_ticket` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `id_object` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `parent_id` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `root_id` );
ALTER TABLE `ploopi_ticket` ADD INDEX ( `deleted` );

DELETE FROM `ploopi_ticket_dest` WHERE id_ticket = 0;
ALTER TABLE `ploopi_ticket_dest` ADD PRIMARY KEY ( `id_user` , `id_ticket` );
ALTER TABLE `ploopi_ticket_dest` DROP INDEX `id_user`;

ALTER TABLE `ploopi_workspace_group` ADD PRIMARY KEY ( `id_group` , `id_workspace` );
ALTER TABLE `ploopi_workspace_group` DROP `id_profile`;
ALTER TABLE `ploopi_workspace_group_role` ADD PRIMARY KEY ( `id_group` , `id_workspace` , `id_role` );

UPDATE `ploopi_module_type` SET `version` = '1.0 RC7', `author` = 'Ovensia', `date` = '20080723000000' WHERE `ploopi_module_type`.`id` = 1;
