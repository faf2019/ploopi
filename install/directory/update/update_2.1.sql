ALTER TABLE `ploopi_mod_directory_contact` ADD `building` VARCHAR( 255 ) NOT NULL AFTER `number` ;
ALTER TABLE `ploopi_mod_directory_contact` ADD `floor` VARCHAR( 32 ) NOT NULL AFTER `building` ;
ALTER TABLE `ploopi_mod_directory_contact` ADD `rank` VARCHAR( 32 ) NOT NULL ;

ALTER TABLE `ploopi_mod_directory_contact` ADD `position` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_directory_heading` ADD `phone` VARCHAR( 32 ) NOT NULL AFTER `position` ,
ADD `fax` VARCHAR( 32 ) NOT NULL AFTER `phone` ,
ADD `address` VARCHAR( 255 ) NOT NULL AFTER `fax` ,
ADD `postalcode` VARCHAR( 32 ) NOT NULL AFTER `address` ,
ADD `city` VARCHAR( 64 ) NOT NULL AFTER `postalcode` ,
ADD `country` VARCHAR( 64 ) NOT NULL AFTER `city` ;