ALTER TABLE `ploopi_documents_file` ADD `s3_bucket` TEXT NOT NULL DEFAULT '';

UPDATE `ploopi_module_type` SET `version` = '1.10.1.0', `author` = 'Ovensia', `date` = '20260205000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
