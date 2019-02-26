ALTER TABLE `ploopi_mod_dbreport_queryfield` ADD `raw_criteria` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `type_criteria`;
ALTER TABLE `ploopi_mod_dbreport_queryfield` ADD `raw_or` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `type_or`;
