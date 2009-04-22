ALTER TABLE `ploopi_module` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT;

INSERT INTO `ploopi_module` ( `id` , `label` , `id_module_type` , `id_workspace` , `active` , `visible` , `public` , `shared` , `herited` , `adminrestricted` , `viewmode` , `transverseview` , `autoconnect` )
VALUES ('-1', 'Recherche', '1', '0', '1', '1', '0', '0', '0', '0', '1', '0', '0');

UPDATE `ploopi_module_type` SET `version` = '1.4.0', `author` = 'Ovensia', `date` = '20090419000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;