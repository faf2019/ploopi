INSERT INTO dims_param_type (id, id_module_type, label, default_value, public, description, displayed_label) SELECT NULL, id, 'directory_label_myfavorites', '', 0, '', 'Libellé "Mes Favoris"' from dims_module_type where label = 'directory';
INSERT INTO dims_param_type (id, id_module_type, label, default_value, public, description, displayed_label) SELECT NULL, id, 'directory_label_mycontacts', '', 0, '', 'Libellé "Mes Contacts"' from dims_module_type where label = 'directory';
INSERT INTO dims_param_type (id, id_module_type, label, default_value, public, description, displayed_label) SELECT NULL, id, 'directory_label_mygroup', '', 0, '', 'Libellé "Mon Groupe"' from dims_module_type where label = 'directory';
INSERT INTO dims_param_type (id, id_module_type, label, default_value, public, description, displayed_label) SELECT NULL, id, 'directory_label_users', '', 0, '', 'Libellé "Utilisateurs"' from dims_module_type where label = 'directory';
INSERT INTO dims_param_type (id, id_module_type, label, default_value, public, description, displayed_label) SELECT NULL, id, 'directory_label_search', '', 0, '', 'Libellé "Recherche"' from dims_module_type where label = 'directory';

DELETE dims_action FROM dims_action, dims_module_type WHERE dims_module_type.id = dims_action.id_module_type AND dims_module_type.label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '1', 'Rubrique "Favoris"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '2', 'Rubrique "Mes Contacts"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '3', 'Rubrique "Mon Groupe"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '4', 'Rubrique "Utilisateurs"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '5', 'Rubrique "Recherche"', '' from dims_module_type where label = 'directory';

ALTER TABLE `dims_mod_directory_contact` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';

DELETE dims_mod_directory_contact
FROM dims_mod_directory_contact
LEFT JOIN dims_user u ON u.id = dims_mod_directory_contact.id_user 
WHERE isnull(u.login);
