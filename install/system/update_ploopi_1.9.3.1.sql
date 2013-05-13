ALTER TABLE `ploopi_index_keyword` ADD `phonetic` CHAR( 20 ) NOT NULL AFTER `length` , ADD INDEX ( `phonetic` );

ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_action` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_module_type` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_user_action_log` ADD INDEX ( `id_record` );

ALTER TABLE `ploopi_user_action_log`
CHANGE `id_user` `id_user` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_workspace` `id_workspace` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_action` `id_action` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_module_type` `id_module_type` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_module` `id_module` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_record` `id_record` CHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `ip` `ip` CHAR(16) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `timestp` `timestp` BIGINT(14) NOT NULL DEFAULT '0',
CHANGE `user` `user` CHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `workspace` `workspace` CHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `action` `action` CHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `module_type` `module_type` CHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
CHANGE `module` `module` CHAR(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

UPDATE `ploopi_module_type` SET `version` = '1.9.3.1', `author` = 'Ovensia', `date` = '20130327000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;

