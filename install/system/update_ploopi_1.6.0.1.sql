DELETE FROM `ploopi_param_default` WHERE name = 'system_set_cache';
DELETE FROM `ploopi_param_type` WHERE name = 'system_set_cache';
DELETE FROM `ploopi_param_choice` WHERE name = 'system_set_cache';


UPDATE `ploopi_module_type` SET `version` = '1.6.0.1', `author` = 'Ovensia', `date` = '20090929000000', `description` = 'Noyau du syst�me' WHERE `ploopi_module_type`.`id` = 1;