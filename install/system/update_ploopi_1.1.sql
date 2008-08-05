ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_action` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_record` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `timestp` );
ALTER TABLE `ploopi_user_action_log` CHANGE `timestp` `timestp` BIGINT( 14 ) NOT NULL DEFAULT '0';

UPDATE `ploopi_module_type` SET `version` = '1.1', `author` = 'Ovensia', `date` = '20080805000000' WHERE `ploopi_module_type`.`id` = 1;
