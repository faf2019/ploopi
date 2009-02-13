INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displaymodule', '0', 0, '', 'Afficher la colonne "Module" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displaymodule', '0', 'non'), (1, 'system_search_displaymodule', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displaymodule', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayindexed', '0', 0, '', 'Afficher la colonne "Indexé le" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayindexed', '0', 'non'), (1, 'system_search_displayindexed', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayindexed', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayworkspace', '0', 0, '', 'Afficher la colonne "Espace" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayworkspace', '0', 'non'), (1, 'system_search_displayworkspace', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayworkspace', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayuser', '0', 0, '', 'Afficher la colonne "Utilisateur" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayuser', '0', 'non'), (1, 'system_search_displayuser', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayuser', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displaydatetime', '0', 0, '', 'Afficher la colonne "Ajouté le" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displaydatetime', '0', 'non'), (1, 'system_search_displaydatetime', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displaydatetime', '1', 1);

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`)  VALUES (1, 'system_search_displayobjecttype', '0', 0, '', 'Afficher la colonne "Type d\'Objet" dans la recherche');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_search_displayobjecttype', '0', 'non'), (1, 'system_search_displayobjecttype', '1', 'oui');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES  (1, 'system_search_displayobjecttype', '1', 1);

UPDATE `ploopi_module_type` SET `version` = '1.3.1', `author` = 'Ovensia', `date` = '20090213000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;