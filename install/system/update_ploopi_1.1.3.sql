ALTER TABLE `ploopi_validation` CHANGE `id_record` `id_record` VARCHAR( 255 ) NOT NULL;

CREATE TABLE `ploopi_confirmation_code` (
`action` VARCHAR( 255 ) NOT NULL ,
`timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0',
`code` VARCHAR( 32 ) NOT NULL ,
PRIMARY KEY ( `action` )
) ENGINE = MYISAM ;

UPDATE `ploopi_module_type` SET `version` = '1.1.3', `author` = 'Ovensia', `date` = '20081002000000' WHERE `ploopi_module_type`.`id` = 1;
UPDATE `ploopi_module` SET `visible` = `active`;
