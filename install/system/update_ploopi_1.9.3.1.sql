ALTER TABLE `ploopi_index_keyword` ADD `phonetic` CHAR( 20 ) NOT NULL AFTER `length` , ADD INDEX ( `phonetic` );

UPDATE `ploopi_module_type` SET `version` = '1.9.3.1', `author` = 'Ovensia', `date` = '20130327000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
