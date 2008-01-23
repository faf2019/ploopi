ALTER TABLE `dims_user` ADD `timezone` DOUBLE NOT NULL ;

UPDATE `dims_module_type` SET `version` = '2.99e' WHERE `dims_module_type`.`id` =1;
