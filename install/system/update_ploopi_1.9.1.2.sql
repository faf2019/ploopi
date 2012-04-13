INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_user_required_fields', 'email,phone,service,function,city', '0', NULL, 'Champs requis dans le profil utilisateur');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_user_required_fields', 'email,phone,service,function,city', '1');
UPDATE `ploopi_module_type` SET `version` = '1.9.1.2', `author` = 'Ovensia', `date` = '20120413000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
