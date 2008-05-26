ALTER TABLE `ploopi_workspace` CHANGE `admin_template` `template` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `ploopi_workspace` DROP `web_template`;
ALTER TABLE `ploopi_workspace` DROP `public`;
ALTER TABLE `ploopi_workspace` CHANGE `admin_domainlist` `backoffice_domainlist` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `ploopi_workspace` CHANGE `web_domainlist` `frontoffice_domainlist` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
ALTER TABLE `ploopi_workspace` CHANGE `admin` `backoffice` TINYINT( 1 ) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `ploopi_workspace` CHANGE `web` `frontoffice` TINYINT( 1 ) UNSIGNED NULL DEFAULT '0';

UPDATE `ploopi_module_type` SET `version` = '1.0RC4', `author` = 'Ovensia', `date` = '20080526000000' WHERE `ploopi_module_type`.`id` = 1;
