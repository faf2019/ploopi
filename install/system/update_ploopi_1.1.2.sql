RENAME TABLE `ploopi_workflow` TO `ploopi_validation`;
ALTER TABLE `ploopi_validation` CHANGE `type_workflow` `type_validation` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '0',
CHANGE `id_workflow` `id_validation` INT( 10 ) UNSIGNED NULL DEFAULT '0';