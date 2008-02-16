ALTER TABLE `dims_workspace_user` ADD INDEX ( `id_workspace` );
INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_proxy_host', '', '0', '', 'Adresse du proxy pour les requêtes sortantes');
INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_proxy_port', '', '0', '', 'Port du proxy pour les requêtes sortantes');
INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_proxy_user', '', '0', '', 'Utilisateur du proxy pour les requêtes sortantes');
INSERT INTO `dims_param_type` ( `id_module_type` , `name` , `default_value` , `public` , `description` , `label` ) VALUES ('1', 'system_proxy_pass', '', '0', '', 'Mot de Passe du proxy pour les requêtes sortantes');
/*
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_proxy_host', '', '1');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_proxy_port', '', '1');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_proxy_user', '', '1');
INSERT INTO `dims_param_default` ( `id_module` , `name` , `value` , `id_module_type` ) VALUES ('1', 'system_proxy_pass', '', '1');
*/

UPDATE dims_user SET password = md5(concat('ploopi/', login, '/', password));

ALTER TABLE `dims_role` CHANGE `description` `description` VARCHAR( 255 ) NULL DEFAULT NULL;

RENAME TABLE `dims_annotation` TO `ploopi_annotation`;
RENAME TABLE `dims_annotation_tag` TO `ploopi_annotation_tag` ;
RENAME TABLE `dims_connecteduser` TO `ploopi_connecteduser`;
RENAME TABLE `dims_documents_ext` TO `ploopi_documents_ext`;
RENAME TABLE `dims_documents_file` TO `ploopi_documents_file`;
RENAME TABLE `dims_documents_folder` TO `ploopi_documents_folder`;
RENAME TABLE `dims_group` TO `ploopi_group`;
RENAME TABLE `dims_group_user` TO `ploopi_group_user`;
RENAME TABLE `dims_log` TO `ploopi_log`;
RENAME TABLE `dims_mb_action` TO `ploopi_mb_action`;
RENAME TABLE `dims_mb_field` TO `ploopi_mb_field`;
RENAME TABLE `dims_mb_object` TO `ploopi_mb_object`;
RENAME TABLE `dims_mb_relation` TO `ploopi_mb_relation`;
RENAME TABLE `dims_mb_schema` TO `ploopi_mb_schema`;
RENAME TABLE `dims_mb_table` TO `ploopi_mb_table`;
RENAME TABLE `dims_mb_wce_object` TO `ploopi_mb_wce_object`;
RENAME TABLE `dims_module` TO `ploopi_module`;
RENAME TABLE `dims_module_type` TO `ploopi_module_type`;
RENAME TABLE `dims_module_workspace` TO `ploopi_module_workspace`;
RENAME TABLE `dims_param_choice` TO `ploopi_param_choice`;
RENAME TABLE `dims_param_default` TO `ploopi_param_default`;
RENAME TABLE `dims_param_group` TO `ploopi_param_group`;
RENAME TABLE `dims_param_type` TO `ploopi_param_type`;
RENAME TABLE `dims_param_user` TO `ploopi_param_user`;
RENAME TABLE `dims_profile` TO `ploopi_profile`;
RENAME TABLE `dims_role` TO `ploopi_role`;
RENAME TABLE `dims_role_action` TO `ploopi_role_action`;
RENAME TABLE `dims_role_profile` TO `ploopi_role_profile`;
RENAME TABLE `dims_share` TO `ploopi_share`;
RENAME TABLE `dims_tag` TO `ploopi_tag`;
RENAME TABLE `dims_ticket` TO `ploopi_ticket`;
RENAME TABLE `dims_ticket_dest` TO `ploopi_ticket_dest`;
RENAME TABLE `dims_ticket_status` TO `ploopi_ticket_status`;
RENAME TABLE `dims_ticket_watch` TO `ploopi_ticket_watch`;
RENAME TABLE `dims_user` TO `ploopi_user`;
RENAME TABLE `dims_user_action_log` TO `ploopi_user_action_log`;
RENAME TABLE `dims_user_filter_rules` TO `ploopi_user_filter_rules`;
RENAME TABLE `dims_user_type` TO `ploopi_user_type`;
RENAME TABLE `dims_user_type_fields` TO `ploopi_user_type_fields`;
RENAME TABLE `dims_workflow` TO `ploopi_workflow`;
RENAME TABLE `dims_workspace` TO `ploopi_workspace`;
RENAME TABLE `dims_workspace_group` TO `ploopi_workspace_group`;
RENAME TABLE `dims_workspace_group_role` TO `ploopi_workspace_group_role`;
RENAME TABLE `dims_workspace_user` TO `ploopi_workspace_user`;
RENAME TABLE `dims_workspace_user_role` TO `ploopi_workspace_user_role`;

DROP TABLE IF EXISTS `ploopi_session`;
CREATE TABLE IF NOT EXISTS `ploopi_session` (
  `id` varchar(32) NOT NULL,
  `access` int(10) unsigned default NULL,
  `data` longtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

UPDATE `ploopi_module_type` SET `version` = '1.0 Alpha 1' WHERE `ploopi_module_type`.`id` = 1;

ALTER TABLE `ploopi_ticket` CHANGE `timestp` `timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `lastreply_timestp` `lastreply_timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `deleted` `deleted` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `id_module_type` `id_module_type` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_ticket` ADD `lastedit_timestp` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `lastreply_timestp` ;

ALTER TABLE `ploopi_annotation_tag` ADD INDEX ( `id_annotation` );
ALTER TABLE `ploopi_annotation_tag` ADD INDEX ( `id_tag` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_record` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_object` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_workspace` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_module` );
ALTER TABLE `ploopi_annotation` ADD INDEX ( `id_module_type` );

UPDATE `ploopi_mb_object` SET `script` = 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=org&groupid=<IDRECORD>' WHERE `ploopi_mb_object`.`id` =2 AND `ploopi_mb_object`.`id_module_type` =1 LIMIT 1 ;
UPDATE `ploopi_mb_object` SET `script` = 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=work&workspaceid=<IDRECORD>' WHERE `ploopi_mb_object`.`id` =1 AND `ploopi_mb_object`.`id_module_type` =1 LIMIT 1 ;

RENAME TABLE `ploopi_param_group`  TO `ploopi_param_workspace` ;
ALTER TABLE `ploopi_param_workspace` CHANGE `id_group` `id_workspace` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `ploopi_mb_wce_object` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `ploopi_mb_wce_object` DROP PRIMARY KEY
ALTER TABLE `ploopi_mb_wce_object` ADD PRIMARY KEY ( `id` , `id_module_type` ) ;

ALTER TABLE `ploopi_mb_table` DROP INDEX `nom_2`;
ALTER TABLE `ploopi_mb_table` DROP INDEX `nom` ;
ALTER TABLE `ploopi_mb_relation` ADD INDEX ( `id_module_type` );

CREATE TABLE `ploopi_index_element` (
  `id` char(32) NOT NULL,
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) NOT NULL,
  `timestp_create` bigint(14) unsigned NOT NULL default '0',
  `timestp_modify` bigint(14) unsigned NOT NULL default '0',
  `timestp_lastindex` bigint(14) unsigned NOT NULL default '0',
  `id_user` int(10) unsigned NOT NULL default '0',
  `id_workspace` int(10) unsigned NOT NULL default '0',
  `id_module` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id_module` (`id_module`),
  KEY `id_workspace` (`id_workspace`),
  KEY `id_user` (`id_user`),
  KEY `id_record` (`id_record`),
  KEY `id_object` (`id_object`),
  KEY `timestp_create` (`timestp_create`),
  KEY `timestp_modify` (`timestp_modify`),
  KEY `timestp_lastindex` (`timestp_lastindex`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_index_keyword` (
  `id` char(32) NOT NULL,
  `keyword` char(20) character set latin1 collate latin1_bin NOT NULL,
  `twoletters` char(2) character set latin1 collate latin1_bin NOT NULL,
  `length` tinyint(2) unsigned NOT NULL default '0',
  `id_stem` char(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `twoletter` (`twoletters`),
  KEY `length` (`length`),
  KEY `id_stem` (`id_stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_index_keyword_element` (
  `id_keyword` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` int(10) unsigned NOT NULL default '0',
  KEY `id_keyword` (`id_keyword`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_index_stem` (
  `id` char(32) NOT NULL default '0',
  `stem` char(20) character set latin1 collate latin1_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `stem` (`stem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `ploopi_index_stem_element` (
  `id_stem` char(32) NOT NULL default '0',
  `id_element` char(32) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  `ratio` float unsigned NOT NULL default '0',
  `relevance` int(10) unsigned NOT NULL default '0',
  KEY `id_stem` (`id_stem`),
  KEY `id_element` (`id_element`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `ploopi_tag` CHANGE `tag` `tag` CHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_tag` ADD INDEX ( `id_user` );
ALTER TABLE `ploopi_tag` ADD INDEX ( `tag` );
ALTER TABLE `ploopi_tag` ADD `tag_clean` CHAR( 32 ) NOT NULL AFTER `tag` ;
ALTER TABLE `ploopi_annotation` ADD `id_element` CHAR( 32 ) NOT NULL DEFAULT '0' AFTER `id_workspace` ;

UPDATE `ploopi_annotation` SET id_element = MD5(CONCAT(LPAD(id_module,4,'0'), LPAD(id_object,4,'0'), id_record));
UPDATE `ploopi_group` SET `shared` = '0' WHERE `ploopi_group`.`id` = 3 LIMIT 1;