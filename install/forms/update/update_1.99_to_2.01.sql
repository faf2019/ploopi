ALTER TABLE `dims_mod_forms_reply` ADD `id_record` VARCHAR( 255 ) NOT NULL ,
ADD `id_object` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

RENAME TABLE `dims_mod_forms` TO `ploopi_mod_forms`;
RENAME TABLE `dims_mod_forms_field` TO `ploopi_mod_forms_field`;
RENAME TABLE `dims_mod_forms_reply` TO `ploopi_mod_forms_reply`;
RENAME TABLE `dims_mod_forms_reply_field` TO `ploopi_mod_forms_reply_field`;

RENAME TABLE `ploopi_mod_forms` TO `ploopi_mod_forms_form` ;
ALTER TABLE `ploopi_mod_forms_field` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_forms_reply` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ploopi_mod_forms_reply_field` CHANGE `id_forms` `id_form` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
