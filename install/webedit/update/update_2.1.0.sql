ALTER TABLE `ploopi_mod_webedit_heading` ADD `private` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `visible` ;
ALTER TABLE `ploopi_mod_webedit_heading` ADD `private_visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `private` ;