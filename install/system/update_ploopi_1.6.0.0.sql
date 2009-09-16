ALTER TABLE `ploopi_mb_table` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mb_table` ADD PRIMARY KEY ( `name` );
ALTER TABLE `ploopi_mb_field` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mb_field` ADD PRIMARY KEY ( `tablename` , `name` );

ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `tablesrc` ); 
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `fieldsrc` ); 
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `tabledest` ); 
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `fielddest` );
ALTER TABLE `ploopi_mb_field` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_mb_schema` ADD INDEX ( `tablesrc` );
ALTER TABLE `ploopi_mb_table` ADD INDEX ( `id_module_type` );

UPDATE `ploopi_module_type` SET `version` = '1.6.0.0', `author` = 'Ovensia', `date` = '20090916000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;