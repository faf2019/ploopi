ALTER TABLE `ploopi_mod_dbreport_query` ADD `locked` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `ws_ip` ;
ALTER TABLE `ploopi_mod_dbreport_query` ADD INDEX ( `locked` ) ;