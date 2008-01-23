ALTER TABLE `dims_mod_webedit_article` CHANGE `keywords` `metakeywords` MEDIUMTEXT NOT NULL;
ALTER TABLE `dims_mod_webedit_article` CHANGE `description` `metadescription` MEDIUMTEXT NOT NULL;
ALTER TABLE `dims_mod_webedit_article` ADD `metatitle` MEDIUMTEXT NOT NULL AFTER `metadescription`;
ALTER TABLE `dims_mod_webedit_article` ADD `comments_allowed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `visible`;

ALTER TABLE `dims_mod_webedit_article_draft` CHANGE `keywords` `metakeywords` MEDIUMTEXT NOT NULL;
ALTER TABLE `dims_mod_webedit_article_draft` CHANGE `description` `metadescription` MEDIUMTEXT NOT NULL;
ALTER TABLE `dims_mod_webedit_article_draft` ADD `metatitle` MEDIUMTEXT NOT NULL AFTER `metadescription`;
ALTER TABLE `dims_mod_webedit_article_draft` ADD `comments_allowed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `visible`;

ALTER TABLE `dims_mod_webedit_article` ADD `tags` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `dims_mod_webedit_article_draft` ADD `tags` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `dims_mod_webedit_heading` ADD `sortmode` VARCHAR( 16 ) NOT NULL AFTER `url_window` ;

ALTER TABLE `dims_mod_webedit_article_backup` ADD `id_user` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

RENAME TABLE `dims_mod_webedit_article` TO `dims_mod_webedit_article`;
RENAME TABLE `dims_mod_webedit_article_backup` TO `dims_mod_webedit_article_backup`;
RENAME TABLE `dims_mod_webedit_article_draft` TO `dims_mod_webedit_article_draft`;
RENAME TABLE `dims_mod_webedit_heading` TO `dims_mod_webedit_heading`;
