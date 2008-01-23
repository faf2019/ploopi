RENAME TABLE `dims_mod_directory_contact` TO `ploopi_mod_directory_contact`;
RENAME TABLE `dims_mod_directory_favorites` TO `ploopi_mod_directory_favorites`;
ALTER TABLE `ploopi_mod_directory_favorites` CHANGE `id_dims_user` `id_ploopi_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `name` `lastname` VARCHAR( 255 ) NULL DEFAULT NULL;
ALTER TABLE `ploopi_mod_directory_contact` CHANGE `commentary` `comments` LONGTEXT NULL DEFAULT NULL;
