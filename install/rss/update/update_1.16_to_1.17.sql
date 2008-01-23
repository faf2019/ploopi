ALTER TABLE `dims_mod_rssfeed` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_rsscat` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_rssrequest` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';
