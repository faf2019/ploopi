ALTER TABLE `ploopi_user` CHANGE `password` `password` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

UPDATE `ploopi_module_type` SET `version` = '1.9.4.9', `author` = 'Ovensia', `date` = '20160916000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
