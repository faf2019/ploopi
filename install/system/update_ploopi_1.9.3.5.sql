INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_password_force_update', '0', '0', NULL, 'Forcer le changement de mot de passe lors de la prochaine connexion');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_password_force_update', '0', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_password_force_update', '0', 'non'), (1, 'system_password_force_update', '1', 'oui');

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_password_validity', '0', '0', NULL, 'Durée de validité du mot de passe en jours');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_password_validity', '0', '1');

ALTER TABLE `ploopi_user` ADD `password_force_update` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0', ADD `password_validity` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_user` ADD `password_last_update` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0', ADD INDEX ( `password_last_update` );
UPDATE `ploopi_user` SET `password_last_update` = NOW() + 0;

UPDATE `ploopi_module_type` SET `version` = '1.9.3.5', `author` = 'Ovensia', `date` = '20131121000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
