ALTER TABLE `ploopi_user_action_log` ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_user` ;

ALTER TABLE `ploopi_user_action_log` ADD `user` VARCHAR( 100 ) NOT NULL ,
ADD `workspace` VARCHAR( 100 ) NOT NULL ,
ADD `action` VARCHAR( 100 ) NOT NULL ,
ADD `module_type` VARCHAR( 100 ) NOT NULL ,
ADD `module` VARCHAR( 100 ) NOT NULL ;

ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `user` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `workspace` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `action` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `module_type` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `module` );

INSERT INTO `ploopi_mb_action` ( `id_module_type` , `id_action` , `label` , `description` , `id_workspace` , `id_object` , `role_enabled` )
VALUES 
('1', '39', 'Créer un Espace de Travail', NULL , '0', '0', '1'), 
('1', '40', 'Modifier un Espace de Travail', NULL , '0', '0', '1'),
('1', '41', 'Supprimer un Espace de Travail', NULL , '0', '0', '1'),
('1', '42', 'Clôner un Espace de Travail', NULL , '0', '0', '1');

UPDATE `ploopi_module_type` SET `version` = '1.5.2.0', `author` = 'Ovensia', `date` = '20090812000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;