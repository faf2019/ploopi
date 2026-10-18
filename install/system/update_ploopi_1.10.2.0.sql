ALTER TABLE `ploopi_user_action_log` CHANGE `ip` `ip` varchar(45) NOT NULL DEFAULT ''  COMMENT '' ;
ALTER TABLE `ploopi_connecteduser` CHANGE `ip` `ip` varchar(45) DEFAULT NULL  COMMENT '' ;
UPDATE `ploopi_module_type` SET `version` = '1.10.2.0', `author` = 'Ovensia', `date` = '20261006000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
