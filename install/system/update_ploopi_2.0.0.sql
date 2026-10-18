-- Ploopi 2.0.0 : changement de numérotation (versions à trois nombres), sans changement de schéma.
UPDATE `ploopi_module_type` SET `version` = '2.0.0', `author` = 'Ovensia', `date` = '20261011000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
