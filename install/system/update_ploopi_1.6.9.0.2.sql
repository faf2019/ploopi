CREATE TABLE `ploopi_captcha` (
`id` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`cptuse` INT( 10 ) UNSIGNED NOT NULL ,
`codesound` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`code` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
`time` INT( 20 ) UNSIGNED NOT NULL ,
INDEX ( `id` )
) ENGINE = MYISAM ;

UPDATE `ploopi_module_type` SET `version` = '1.6.9.0.2', `author` = 'Ovensia', `date` = '20100106000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;