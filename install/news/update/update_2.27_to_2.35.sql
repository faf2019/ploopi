RENAME TABLE `dims_mod_news` TO `ploopi_mod_news`;
RENAME TABLE `dims_mod_newscat` TO `ploopi_mod_newscat`;
RENAME TABLE `ploopi_mod_newscat` TO `ploopi_mod_news_cat` ;
RENAME TABLE `ploopi_mod_news` TO `ploopi_mod_news_entry` ;
ALTER TABLE `ploopi_mod_news_entry` CHANGE `id_newscat` `id_cat` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
