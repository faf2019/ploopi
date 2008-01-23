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
