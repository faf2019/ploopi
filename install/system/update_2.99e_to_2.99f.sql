INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_same_login', '0', '0', NULL , 'Utiliser des logins identiques (fortement déconseillé)');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_same_login', '0', '1');
INSERT INTO `dims_param_choice` ( `id_module_type` , `name` , `value` , `displayed_value` ) VALUES ('1', 'system_same_login', '1', 'oui');
INSERT INTO `dims_param_choice` ( `id_module_type` , `name` , `value` , `displayed_value` ) VALUES ('1', 'system_same_login', '0', 'non');

UPDATE `dims_module_type` SET `version` = '2.99f' WHERE `dims_module_type`.`id` =1;
