DROP TABLE IF EXISTS `dims_mod_rsspref`;
CREATE TABLE `dims_mod_rsspref` (
  `id_module` int(10) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_feed` int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;

ALTER TABLE `dims_mod_rsscache` CHANGE `date` `timestp` VARCHAR(14)  DEFAULT "00000000000000" NOT NULL;
ALTER TABLE `dims_mod_rsscache` DROP `time`;
ALTER TABLE `dims_mod_rsscache` ADD INDEX title (title);
ALTER TABLE `dims_mod_rsscache` ADD INDEX link (link);
ALTER TABLE `dims_mod_rsscache` ADD `content` LONGTEXT;

ALTER TABLE `dims_mod_rssfeed` ADD `error` TINYINT(1)  UNSIGNED DEFAULT "0" NOT NULL AFTER `updating_cache`;
