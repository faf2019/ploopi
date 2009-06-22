DROP TABLE IF EXISTS `ploopi_serializedvar`;
CREATE TABLE IF NOT EXISTS `ploopi_serializedvar` (
  `id` char(32) NOT NULL,
  `id_session` char(32) NOT NULL,
  `data` longtext,
  PRIMARY KEY  (`id`),
  KEY `id_session` (`id_session`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


UPDATE `ploopi_module_type` SET `version` = '1.5.0.1', `author` = 'Ovensia', `date` = '20090622000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;