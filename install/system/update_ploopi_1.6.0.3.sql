ALTER TABLE `ploopi_user` CHANGE `service` `service` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `function` `function` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `city` `city` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `country` `country` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `floor` `floor` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `office` `office` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `rank` `rank` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `ploopi_serializedvar` DROP PRIMARY KEY;
ALTER TABLE `ploopi_serializedvar` ADD PRIMARY KEY ( `id` , `id_session` ) ;

UPDATE `ploopi_module_type` SET `version` = '1.6.0.3', `author` = 'Ovensia', `date` = '20091001000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;