ALTER TABLE `ploopi_user` CHANGE `password` `password` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_session` CHANGE `data` `data` LONGBLOB NULL DEFAULT NULL;
ALTER TABLE `ploopi_serializedvar` CHANGE `data` `data` LONGBLOB NULL DEFAULT NULL;

ALTER TABLE  `ploopi_user` ADD  `last_connection` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT  '0', ADD INDEX (  `last_connection` );

UPDATE `ploopi_module_type` SET `version` = '1.9.7.0', `author` = 'Ovensia', `date` = '20160602000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
