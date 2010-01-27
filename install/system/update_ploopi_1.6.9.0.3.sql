INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES (1, 'system_jodwebservice', '', 0, '', 'URL du webservice JODConverter');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES (1, 'system_jodwebservice', '', 1);

UPDATE `ploopi_module_type` SET `version` = '1.6.9.0.3', `author` = 'Ovensia', `date` = '20100127000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;