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
