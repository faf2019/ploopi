ALTER TABLE `ploopi_mod_directory_contact` DROP `level` ,
DROP `date_create` ,
DROP `date_modify`,
DROP `id_dims_user`;

ALTER TABLE `ploopi_mod_directory_contact` ADD `number` VARCHAR( 255 ) NOT NULL ,
ADD `office` VARCHAR( 255 ) NOT NULL ;

ALTER TABLE `ploopi_mod_directory_contact` DROP INDEX `FT`;

ALTER TABLE `ploopi_mod_directory_contact` ADD FULLTEXT KEY `FT` (
`lastname` ,
`firstname` ,
`city` ,
`country` ,
`service` ,
`function` ,
`number` ,
`office`,
`comments`
);

ALTER TABLE `ploopi_mod_directory_contact` 
CHANGE `lastname` `lastname` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `firstname` `firstname` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `service` `service` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `function` `function` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `phone` `phone` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `mobile` `mobile` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `fax` `fax` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `email` `email` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `address` `address` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `postalcode` `postalcode` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `city` `city` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `country` `country` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `comments` `comments` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL, 
CHANGE `id_user` `id_user` INT(10) UNSIGNED NOT NULL DEFAULT '0', 
CHANGE `id_workspace` `id_workspace` INT(10) UNSIGNED NOT NULL DEFAULT '0', 
CHANGE `id_module` `id_module` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_directory_contact` ADD `civility` VARCHAR( 16 ) NOT NULL ;

DROP TABLE IF EXISTS `ploopi_mod_directory_heading`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_directory_heading` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `position` int(10) unsigned NOT NULL default '0',
  `id_heading` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_rubrique` (`id_heading`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `ploopi_mod_directory_heading` (`id`, `label`, `description`, `position`, `id_heading`, `id_user`, `id_workspace`, `id_module`) VALUES 
(1, 'Racine', 'Rubrique créée par défaut', 1, 0, 0, 0, 0);

ALTER TABLE `ploopi_mod_directory_contact` ADD `id_heading` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';