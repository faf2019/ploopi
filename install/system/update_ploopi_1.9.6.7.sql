ALTER TABLE `ploopi_workspace` ADD `mail_model` TEXT NOT NULL ;

UPDATE `ploopi_workspace` SET mail_model = 'Bonjour {firstname} {lastname},

Veuillez trouver ci-dessous vos identifiants de connexion pour le site {url} :

Identifiant: {login}
Mot de passe: {password}

Gardez précieusement votre mot de passe et ne le communiquez à personne.
Vos identifiants sont strictement personnels.

Cordialement,

Ce message a été envoyé automatiquement. Nous vous remercions de ne pas répondre.';

UPDATE `ploopi_module_type` SET `version` = '1.9.6.7', `author` = 'Ovensia', `date` = '20170821000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
