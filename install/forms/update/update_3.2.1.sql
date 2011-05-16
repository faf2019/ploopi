ALTER TABLE `ploopi_mod_forms_form` ADD `option_multidisplaysave` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_adminonly` ,
ADD `option_multidisplaypages` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_multidisplaysave` ;

ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_mod_forms_form` ADD INDEX ( `id_user` );