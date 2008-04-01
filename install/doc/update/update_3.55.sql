ALTER TABLE `dims_mod_doc_file_draft` ADD `readonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `size`;
ALTER TABLE `dims_mod_doc_file` ADD `readonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `size` ;
ALTER TABLE `dims_mod_doc_keyword_file` ADD `meta` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `dims_mod_doc_keyword_file` ADD INDEX ( `meta` );
UPDATE `dims_mod_doc_parser` SET `path` = 'catppt -s 8859-15 -d 8859-15 %f' WHERE `extension` = 'ppt';

ALTER TABLE `dims_mod_doc_keyword_file` ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `meta` ;
ALTER TABLE `dims_mod_doc_keyword_file` ADD INDEX ( `id_module` ) ;
ALTER TABLE `dims_mod_doc_keyword` ADD `id_module` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `twoletters` ;
ALTER TABLE `dims_mod_doc_keyword` ADD INDEX ( `id_module` ) ;

OPTIMIZE TABLE `dims_mod_doc_keyword_file`;
OPTIMIZE TABLE `dims_mod_doc_keyword`;
