ALTER TABLE `ploopi_user` ADD `office` VARCHAR( 64 ) NOT NULL , ADD `civility` VARCHAR( 16 ) NOT NULL ;

ALTER TABLE `ploopi_user` 
CHANGE `lastname` `lastname` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `firstname` `firstname` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `login` `login` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `password` `password` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `date_creation` `date_creation` BIGINT(14) NOT NULL DEFAULT '0', 
CHANGE `date_expire` `date_expire` BIGINT(14) NOT NULL DEFAULT '0', 
CHANGE `email` `email` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `phone` `phone` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `fax` `fax` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `comments` `comments` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `address` `address` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `mobile` `mobile` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `service` `service` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `function` `function` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `postalcode` `postalcode` VARCHAR(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `city` `city` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `country` `country` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `ploopi_user` DROP `id_type`, DROP `id_ldap`;

UPDATE `ploopi_module_type` SET `version` = '1.0RC4.1', `author` = 'Ovensia', `date` = '20080610000000' WHERE `ploopi_module_type`.`id` = 1;
