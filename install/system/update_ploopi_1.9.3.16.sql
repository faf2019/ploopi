ALTER TABLE `ploopi_user` ADD `failed_attemps` INT( 0 ) UNSIGNED NOT NULL DEFAULT '0', ADD INDEX ( `failed_attemps` );
ALTER TABLE `ploopi_user` ADD `jailed_since` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_user` ADD `entity` VARCHAR( 255 ) NOT NULL AFTER `mobile`;

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_profile_edit_allowed', '1', '0', NULL, 'L''utilisateur peut modifier son profil');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_profile_edit_allowed', '1', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_profile_edit_allowed', '0', 'non'), (1, 'system_profile_edit_allowed', '1', 'oui');

UPDATE `ploopi_module_type` SET `version` = '1.9.3.16', `author` = 'Ovensia', `date` = '20150529000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
