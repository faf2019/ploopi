DELETE dims_action FROM dims_action, dims_module_type WHERE dims_module_type.id = dims_action.id_module_type AND dims_module_type.label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '1', 'Rubrique "Favoris"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '2', 'Rubrique "Mes Contacts"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '3', 'Rubrique "Mon Groupe"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '4', 'Rubrique "Utilisateurs"', '' from dims_module_type where label = 'directory';
INSERT INTO dims_action(id_module_type, id_action, label, description) SELECT id, '5', 'Rubrique "Recherche"', '' from dims_module_type where label = 'directory';

ALTER TABLE `dims_mod_directory_contact` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';

DELETE dims_mod_directory_contact
FROM dims_mod_directory_contact
LEFT JOIN dims_user u ON u.id = dims_mod_directory_contact.id_user 
WHERE isnull(u.login);

RENAME TABLE `dims_mod_directory_contact` TO `ploopi_mod_directory_contact`;
RENAME TABLE `dims_mod_directory_favorites` TO `ploopi_mod_directory_favorites`;
ALTER TABLE `ploopi_mod_directory_favorites` CHANGE `id_dims_user` `id_ploopi_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `name` `lastname` VARCHAR( 255 ) NULL DEFAULT NULL;
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `commentary` `comments` LONGTEXT NULL DEFAULT NULL;

DROP TABLE IF EXISTS `ploopi_mod_directory_list`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_list` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(100) NOT NULL,
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_mod_directory_favorites` ADD `id_list` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_directory_favorites` DROP PRIMARY KEY;

ALTER TABLE `ploopi_mod_directory_favorites` ADD PRIMARY KEY ( `id_contact` , `id_user` , `id_ploopi_user` , `id_list` ) ;

