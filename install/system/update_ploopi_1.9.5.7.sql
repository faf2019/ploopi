INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_trombi_maxlines', '25', '0', NULL, 'Nombre de réponses maxi dans la recherche (sinon index alphabétique)');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_trombi_maxlines', '25', '1');

UPDATE `ploopi_module_type` SET `version` = '1.9.5.7', `author` = 'Ovensia', `date` = '20160215000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
