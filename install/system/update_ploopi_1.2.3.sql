ALTER TABLE `ploopi_documents_file` CHANGE `ref` `ref` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
UPDATE `ploopi_module_type` SET `version` = '1.2.3', `author` = 'Ovensia', `date` = '20090109000000', `description` = 'Noyau du syst�me' WHERE `ploopi_module_type`.`id` = 1;