ALTER TABLE `ploopi_mod_webedit_heading` ADD `content_type` CHAR( 16 ) NOT NULL DEFAULT 'article_first' AFTER `position` ;
UPDATE ploopi_mod_webedit_heading SET content_type = 'article_redirect' WHERE linkedpage > 0;
UPDATE ploopi_mod_webedit_heading SET content_type = 'url_redirect' WHERE url <> '';
