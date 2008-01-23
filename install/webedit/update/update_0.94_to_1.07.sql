ALTER TABLE `dims_mod_wce_article` ADD `timestp_published` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp` ,
ADD `timestp_unpublished` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp_published` ;

ALTER TABLE `dims_mod_wce_article_draft` ADD `timestp_published` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp` ,
ADD `timestp_unpublished` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp_published` ;

ALTER TABLE `dims_mod_wce_article_draft` CHANGE `timestp` `timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_wce_article` CHANGE `timestp` `timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `dims_mod_wce_article` ADD `visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `version` ;
ALTER TABLE `dims_mod_wce_article_draft` ADD `visible` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `version` ;

ALTER TABLE `dims_mod_wce_heading` ADD `free1` VARCHAR( 255 ) NOT NULL AFTER `linkedpage` ,
ADD `free2` VARCHAR( 255 ) NOT NULL AFTER `free1` ;

ALTER TABLE `dims_mod_wce_heading` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `dims_mod_wce_heading` ADD `url` VARCHAR( 255 ) NOT NULL AFTER `linkedpage` ;
ALTER TABLE `dims_mod_wce_heading` ADD `url_window` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `url` ;

ALTER TABLE `dims_mod_wce_article` ADD `lastupdate_timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp_unpublished` ,
ADD `lastupdate_id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `lastupdate_timestp` ;
ALTER TABLE `dims_mod_wce_article_draft` ADD `lastupdate_timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `timestp_unpublished` ,
ADD `lastupdate_id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `lastupdate_timestp` ;
ALTER TABLE `dims_mod_wce_article` ADD INDEX ( `lastupdate_timestp` );

ALTER TABLE `dims_mod_wce_article_draft` CHANGE `author` `author` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article_draft` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article_draft` ADD `keywords` MEDIUMTEXT NOT NULL AFTER `title`;
ALTER TABLE `dims_mod_wce_article` CHANGE `author` `author` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article` CHANGE `title` `title` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `dims_mod_wce_article` ADD `keywords` MEDIUMTEXT NOT NULL AFTER `title`;
