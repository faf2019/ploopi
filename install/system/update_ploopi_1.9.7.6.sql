ALTER TABLE `ploopi_workspace` ADD `mail_model` TEXT NOT NULL ;

UPDATE `ploopi_workspace` SET mail_model = 'Bonjour {firstname} {lastname},

Veuillez trouver ci-dessous vos identifiants de connexion pour le site {url} :

Identifiant: {login}
Mot de passe: {password}

Gardez précieusement votre mot de passe et ne le communiquez à personne.
Vos identifiants sont strictement personnels.

Cordialement,

Ce message a été envoyé automatiquement. Nous vous remercions de ne pas répondre.';

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_new_user_mail', '25', '0', '', 'Envoyer un courriel à la création d''un compte utilisateur');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_new_user_mail', '0', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_new_user_mail', '0', 'non'), (1, 'system_new_user_mail', '1', 'oui');

UPDATE `ploopi_module_type` SET `version` = '1.9.7.6', `author` = 'Ovensia', `date` = '20210920000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
