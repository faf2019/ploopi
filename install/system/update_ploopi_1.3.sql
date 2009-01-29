INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES (1, 'system_focus_popup', '0', 0, '', 'Activer le Focus sur les Popups');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_focus_popup', '0', 'non'), (1, 'system_focus_popup', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES (1, 'system_focus_popup', '0', 1);

UPDATE `ploopi_module_type` SET `version` = '1.3', `author` = 'Ovensia', `date` = '20090129000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;