ALTER TABLE  `ploopi_user` ADD  `last_connection` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT  '0', ADD INDEX (  `last_connection` );

UPDATE `ploopi_module_type` SET `version` = '1.9.6.1', `author` = 'Ovensia', `date` = '20160912000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
