ALTER TABLE `ploopi_mod_webedit_article` ADD `content_cleaned` LONGTEXT NULL DEFAULT NULL AFTER `content`;
ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `content_cleaned` LONGTEXT NULL DEFAULT NULL AFTER `content`;
UPDATE `ploopi_mod_webedit_article` SET `content_cleaned` = `content`;