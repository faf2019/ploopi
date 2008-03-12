ALTER TABLE `dims_mod_rssfeed` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_rsscat` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_rssrequest` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NULL DEFAULT '0';

RENAME TABLE `dims_mod_rsscache` TO `ploopi_mod_rsscache`;
RENAME TABLE `dims_mod_rsscat` TO `ploopi_mod_rsscat`;
RENAME TABLE `dims_mod_rssfeed` TO `ploopi_mod_rssfeed`;
RENAME TABLE `dims_mod_rsspref` TO `ploopi_mod_rsspref`;
RENAME TABLE `dims_mod_rssrequest` TO `ploopi_mod_rssrequest`;

ALTER TABLE `ploopi_mod_rsscat` CHANGE `timestamp` `timestamp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';
RENAME TABLE `ploopi_mod_rsscat`  TO `ploopi_mod_rss_cat` ;
RENAME TABLE `ploopi_mod_rsscache`  TO `ploopi_mod_rss_cache`;
ALTER TABLE `ploopi_mod_rssrequest` DROP INDEX `id_2`;
ALTER TABLE `ploopi_mod_rssrequest` DROP INDEX `id`;
RENAME TABLE `ploopi_mod_rssrequest`  TO `ploopi_mod_rss_request`;
RENAME TABLE `ploopi_mod_rssfeed`  TO `ploopi_mod_rss_feed`;
ALTER TABLE `ploopi_mod_rsspref` ADD PRIMARY KEY ( `id_module` , `id_user` , `id_feed` ) ;
RENAME TABLE `ploopi_mod_rsspref` TO `ploopi_mod_rss_pref`;
ALTER TABLE `ploopi_mod_rss_feed` DROP `country` , DROP `language`;
ALTER TABLE `ploopi_mod_rss_feed` CHANGE `description` `subtitle` MEDIUMTEXT NOT NULL;
ALTER TABLE `ploopi_mod_rss_feed` ADD `author` VARCHAR( 255 ) NOT NULL AFTER `subtitle`;
ALTER TABLE `ploopi_mod_rss_feed` ADD `updated` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `author` ;
RENAME TABLE `ploopidev`.`ploopi_mod_rss_cache`  TO `ploopidev`.`ploopi_mod_rss_entry` ;
ALTER TABLE `ploopi_mod_rss_entry` CHANGE `id` `id` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `ploopi_mod_rss_entry` CHANGE `subject` `subtitle` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `description` `category` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `ploopi_mod_rss_entry` ADD `published` VARCHAR( 14 ) NOT NULL ;

ALTER TABLE `ploopi_mod_rss_feed` CHANGE `id_rsscat` `id_cat` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_rss_entry` CHANGE `id_rssfeed` `id_feed` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_rss_request` CHANGE `id_rsscat` `id_cat` INT( 10 ) UNSIGNED NULL DEFAULT '0';
UPDATE `ploopi_mod_rss_entry` SET id = md5( id );
ALTER TABLE `ploopi_mod_rss_entry` CHANGE `id` `id` CHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_mod_rss_entry` ADD `id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
