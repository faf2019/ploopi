DROP TABLE IF EXISTS `ploopi_mod_newsletter_letter`;
CREATE TABLE `ploopi_mod_newsletter_letter` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(16) NOT NULL,
  `content` longtext NOT NULL,
  `template` varchar(255) NOT NULL,
  `banniere` varchar(255) NOT NULL,
  `banniere_id` varchar(255) NOT NULL,
  `background_color` varchar(7) NOT NULL,
  `content_color` varchar(7) NOT NULL,
  `text_color` varchar(7) NOT NULL,
  `timestp` bigint(14) unsigned NOT NULL,
  `id_author` int(10) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `lastupdate_timestp` bigint(14) unsigned NOT NULL,
  `lastupdate_id_user` int(10) unsigned NOT NULL,
  `lastupdate_user` varchar(255) NOT NULL,
  `validated_timestp` bigint(14) unsigned NOT NULL,
  `validated_id_user` int(10) unsigned NOT NULL,
  `validated_user` varchar(255) NOT NULL,
  `send_timestp` bigint(14) unsigned NOT NULL,
  `send_id_user` int(10) unsigned NOT NULL,
  `send_user` varchar(255) NOT NULL,
  `id_module` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  `id_workspace` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `id_module` (`id_module`),
  KEY `id_user` (`id_user`),
  KEY `id_workspace` (`id_workspace`)
) TYPE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_newsletter_param`;
CREATE TABLE `ploopi_mod_newsletter_param` (
  `param` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `id_module` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id_module`,`param`)
) TYPE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_newsletter_send`;
CREATE TABLE `ploopi_mod_newsletter_send` (
  `email_subscriber` varchar(255) NOT NULL,
  `id_letter` int(10) unsigned NOT NULL,
  `timestp_send` bigint(14) unsigned NOT NULL,
  KEY `email_subscriber` (`email_subscriber`),
  KEY `id_letter` (`id_letter`)
) TYPE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_newsletter_subscriber`;
CREATE TABLE `ploopi_mod_newsletter_subscriber` (
  `email` varchar(255) NOT NULL,
  `timestp_subscribe` bigint(14) unsigned NOT NULL,
  `ip` varchar(39) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `id_module` int(10) unsigned NOT NULL,
  KEY `active` (`active`),
  KEY `id_module` (`id_module`),
  KEY `email` (`email`)
) TYPE=MyISAM DEFAULT CHARSET=latin1;