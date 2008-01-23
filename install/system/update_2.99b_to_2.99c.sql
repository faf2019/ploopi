INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_use_profiles', '0', '0', NULL , 'Utiliser les Profils (Utilisateurs)');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_use_profiles', '0', '1');
INSERT INTO `dims_param_choice` ( `id_module_type` , `name` , `value` , `displayed_value` ) VALUES ('1', 'system_use_profiles', '1', 'oui');
INSERT INTO `dims_param_choice` ( `id_module_type` , `name` , `value` , `displayed_value` ) VALUES ('1', 'system_use_profiles', '0', 'non');

UPDATE `dims_param_type` SET `name` = 'system_language_default' WHERE `dims_param_type`.`id_module_type` =1 AND `dims_param_type`.`name` = 'site_language_default' LIMIT 1 ;
 
INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_language', NULL , '1', NULL , 'Langue du système');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_language', 'french', '1');
