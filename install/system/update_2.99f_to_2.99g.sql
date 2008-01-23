ALTER TABLE `dims_ticket` ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `id_user` ;
UPDATE `dims_module_type` SET `version` = '2.99g' WHERE `dims_module_type`.`id` =1;
