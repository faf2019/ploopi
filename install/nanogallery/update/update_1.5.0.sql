CREATE TABLE IF NOT EXISTS `ploopi_mod_nanogallery_img` (
  `id` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

