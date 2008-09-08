DROP TABLE `ploopi_mod_rss_request` ;

ALTER TABLE `ploopi_mod_rss_pref` CHANGE `id_feed` `id_feed_cat_filter` VARCHAR( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_rss_cat` ADD `limit` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `title` ;
ALTER TABLE `ploopi_mod_rss_cat` ADD `tpl_tag` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL AFTER `limit` ;
ALTER TABLE `ploopi_mod_rss_cat` ADD INDEX (`id_workspace`);
ALTER TABLE `ploopi_mod_rss_cat` ADD INDEX (`id_module`);

ALTER TABLE `ploopi_mod_rss_entry` CHANGE `published` `published` INT( 20 ) UNSIGNED NOT NULL DEFAULT '0';  
ALTER TABLE `ploopi_mod_rss_entry` ADD `published_day` INT( 20 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `published` ;
ALTER TABLE `ploopi_mod_rss_entry` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_mod_rss_entry` ADD INDEX ( `id_module` );
 
ALTER TABLE `ploopi_mod_rss_feed` DROP `default`;
ALTER TABLE `ploopi_mod_rss_feed` ADD `limit` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `author`;
ALTER TABLE `ploopi_mod_rss_feed` ADD `tpl_tag` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL AFTER `limit` ;
ALTER TABLE `ploopi_mod_rss_feed` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_mod_rss_feed` ADD INDEX ( `id_module` );

CREATE TABLE `ploopi_mod_rss_filter` (
	`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR( 255 ) NOT NULL,
	`condition` TINYINT( 1 ) UNSIGNED NULL DEFAULT '1',
	`limit` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0',
	`tpl_tag` VARCHAR( 255 ) NULL,
	`timestp` BIGINT( 14 ) UNSIGNED NOT NULL,
	`lastupdate_timestp` BIGINT( 14 ) UNSIGNED NOT NULL,
	`id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY ( `id` ),
	INDEX ( `id_workspace`),
	INDEX ( `id_module`)
) ENGINE = MYISAM;

CREATE TABLE `ploopi_mod_rss_filter_element` (
	`id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_filters` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`target` VARCHAR( 100 ) NOT NULL,
	`compare` VARCHAR( 100 ) NOT NULL,
	`value` VARCHAR( 100 ) NOT NULL,
	PRIMARY KEY ( `id` )
) ENGINE = MYISAM;

CREATE TABLE `ploopi_mod_rss_filter_cat` (
	`id_filter` INT( 10 ) UNSIGNED NOT NULL  DEFAULT '0',
	`id_cat` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY ( `id_filter` , `id_cat` ),
	INDEX ( `id_workspace`),
	INDEX ( `id_module`)
) ENGINE = MYISAM; 

 CREATE TABLE `ploopi_mod_rss_filter_feed` (
	`id_filter` INT( 10 ) UNSIGNED NOT NULL  DEFAULT '0',
	`id_feed` INT( 10 ) UNSIGNED NOT NULL  DEFAULT '0',
	`id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	`id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY ( `id_filter`, `id_feed` ),
	INDEX ( `id_workspace`),
	INDEX ( `id_module`)
) ENGINE = MYISAM;