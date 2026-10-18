INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES
(1, 'system_theme', 'auto', 1, 'Gabarits qui le gèrent (ploopi4) : thème clair, sombre, ou celui du système d''exploitation', 'Thème d''affichage'),
(1, 'system_density', 'comfortable', 1, 'Gabarits qui la gèrent (ploopi4) : espacements et taille de texte', 'Densité d''affichage');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES
(1, 'system_theme', 'auto', 1),
(1, 'system_density', 'comfortable', 1);
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES
(1, 'system_theme', 'auto', 'selon le système'),
(1, 'system_theme', 'light', 'clair'),
(1, 'system_theme', 'dark', 'sombre'),
(1, 'system_density', 'comfortable', 'confortable'),
(1, 'system_density', 'compact', 'compacte');
UPDATE `ploopi_module_type` SET `version` = '1.10.3.0', `author` = 'Ovensia', `date` = '20261011000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
