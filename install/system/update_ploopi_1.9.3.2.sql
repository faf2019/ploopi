ALTER TABLE ploopi_serializedvar DROP PRIMARY KEY;
TRUNCATE TABLE `ploopi_serializedvar`;
ALTER TABLE `ploopi_serializedvar` ADD PRIMARY KEY ( `id` , `id_session` );

UPDATE `ploopi_module_type` SET `version` = '1.9.3.2', `author` = 'Ovensia', `date` = '20130513000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
