INSERT INTO `ploopi_mb_wce_object` ( `id` , `label` , `id_module_type` , `script` , `select_id` , `select_label` , `select_table` )
VALUES ('1', 'Affichage Trombinscope', '1', '?object=''display''', NULL , NULL , NULL);

UPDATE `ploopi_module_type` SET `version` = '1.3.0.1', `author` = 'Ovensia', `date` = '20090204000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;