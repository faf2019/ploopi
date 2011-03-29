INSERT INTO `ploopi_param_type` (`id_module_type` ,`name` ,`default_value` ,`public` ,`description` ,`label`) VALUES ('1', 'system_submenu_display', '1', '0', NULL , 'Afficher les sous-menus de (Mon Espace)');
INSERT INTO `ploopi_param_default` (`id_module` ,`name` ,`value` ,`id_module_type`) VALUES ('1', 'system_submenu_display', '1', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type` ,`name` ,`value` ,`displayed_value`) VALUES ('1', 'system_submenu_display', '1', 'oui'), ('1', 'system_submenu_display', '0', 'non');

UPDATE `ploopi_module_type` SET `version` = '1.8.9.1', `author` = 'Ovensia', `date` = '20110329000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
