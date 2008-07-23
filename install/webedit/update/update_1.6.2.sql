ALTER TABLE `ploopi_mod_webedit_docfile` ADD `id_article` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' FIRST;
ALTER TABLE `ploopi_mod_webedit_docfile` DROP PRIMARY KEY;
ALTER TABLE `ploopi_mod_webedit_docfile` ADD PRIMARY KEY ( `id_article` , `id_docfile` );