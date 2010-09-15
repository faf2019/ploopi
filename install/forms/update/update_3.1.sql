ALTER TABLE `ploopi_mod_forms_field` CHANGE `style` `style_field` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_mod_forms_field` ADD `style_form` VARCHAR( 255 ) NOT NULL AFTER `style_field`;
ALTER TABLE `ploopi_mod_forms_form` CHANGE `width` `style` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
UPDATE `ploopi_mod_forms_form` SET `style` = '';
ALTER TABLE `ploopi_mod_forms_field` ADD `formula` VARCHAR( 255 ) NOT NULL AFTER `values`;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_adminonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_wceview` ;
ALTER TABLE `ploopi_mod_forms_form` ADD `option_adminonly` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_displayip` ;
ALTER TABLE `ploopi_mod_forms_field` ADD `option_formview` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `option_needed` ;
UPDATE `ploopi_mod_forms_field` SET `option_formview` = 1;

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_font` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `param_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.2',
ADD `param_fill_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_margin_left` INT( 10 ) UNSIGNED NOT NULL DEFAULT '40',
ADD `param_margin_right` INT( 10 ) UNSIGNED NOT NULL DEFAULT '20',
ADD `param_margin_top` INT( 10 ) UNSIGNED NOT NULL DEFAULT '120',
ADD `param_margin_bottom` INT( 10 ) UNSIGNED NOT NULL DEFAULT '60',
ADD `param_center_x` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_center_y` DOUBLE UNSIGNED NOT NULL DEFAULT '0.5',
ADD `param_shadow_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.8',
ADD `param_label_angle` DOUBLE UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_font_size_title` INT( 10 ) UNSIGNED NOT NULL DEFAULT '15',
ADD `param_font_size_legend` INT( 10 ) UNSIGNED NOT NULL DEFAULT '8',
ADD `param_font_size_data` INT( 10 ) UNSIGNED NOT NULL DEFAULT '10';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_mark_type` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `param_mark_transparency` DOUBLE UNSIGNED NOT NULL DEFAULT '0.3';

ALTER TABLE `ploopi_mod_forms_graphic` ADD `param_mark_width` INT( 10 ) UNSIGNED NOT NULL DEFAULT '3';

UPDATE `ploopi_mod_forms_graphic` SET `param_font` = 18;
UPDATE `ploopi_mod_forms_graphic` SET `param_mark_type` = 1;