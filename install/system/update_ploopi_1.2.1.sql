INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) 
VALUES (1, 'system_set_cache', '0', 0, '', 'Activer le Cache');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES
(1, 'system_set_cache', '0', 'non'),
(1, 'system_set_cache', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES 
(1, 'system_set_cache', '0', 1);

UPDATE `ploopi_module_type` SET `version` = '1.2.1', `author` = 'Ovensia', `date` = '20081208000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;