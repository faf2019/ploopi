ALTER TABLE `ploopi_mod_newsletter_letter` ADD `banniere` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `template` ,
ADD `banniere_id` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `banniere` ,
ADD `background_color` VARCHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `banniere_id` ,
ADD `content_color` VARCHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `background_color` ,
ADD `text_color` VARCHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL AFTER `content_color` ;