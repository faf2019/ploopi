INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES (1, 'system_unoconv', '', 0, '', 'Chemin vers UNOCONV');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES (1, 'system_unoconv', '/usr/bin/unoconv', 1);

UPDATE `ploopi_module_type` SET `version` = '1.8.9.4', `author` = 'Ovensia', `date` = '20111023000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;