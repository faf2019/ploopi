ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `disabledfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_webedit_article` ADD `disabledfilter` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_webedit_article_draft` ADD `headcontent` LONGTEXT NOT NULL;
ALTER TABLE `ploopi_mod_webedit_article` ADD `headcontent` LONGTEXT NOT NULL;