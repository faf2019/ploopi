ALTER TABLE `ploopi_workspace` ADD `mail_model` TEXT NOT NULL ;

UPDATE `ploopi_workspace` SET mail_model = 'Bonjour {firstname} {lastname},

Veuillez trouver ci-dessous vos identifiants de connexion pour le site {url} :

Identifiant: {login}
Mot de passe: {password}

Gardez pr�cieusement votre mot de passe et ne le communiquez � personne.
Vos identifiants sont strictement personnels.

Cordialement,

Ce message a �t� envoy� automatiquement. Nous vous remercions de ne pas r�pondre.';

UPDATE `ploopi_module_type` SET `version` = '1.9.6.7', `author` = 'Ovensia', `date` = '20170821000000', `description` = 'Noyau du syst�me' WHERE `ploopi_module_type`.`id` = 1;
