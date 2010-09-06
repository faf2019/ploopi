--
-- Structure de la table `ploopi_mb_table`
--

DROP TABLE IF EXISTS `ploopi_mb_table`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_table` (
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `visible` tinyint(1) unsigned default '1',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`name`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_table`
--

INSERT INTO `ploopi_mb_table` (`name`, `label`, `visible`, `id_module_type`) VALUES
('ploopi_group', 'group', 1, 1),
('ploopi_workspace', 'workspace', 1, 1),
('ploopi_user', 'user', 1, 1),
('ploopi_module', 'module', 1, 1);

--
-- Structure de la table `ploopi_mb_field`
--

DROP TABLE IF EXISTS `ploopi_mb_field`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_field` (
  `tablename` varchar(100) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `label` varchar(255) default NULL,
  `type` varchar(50) default NULL,
  `visible` tinyint(1) unsigned default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tablename`,`name`),
  KEY `visible` (`visible`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_field`
--

INSERT INTO `ploopi_mb_field` (`tablename`, `name`, `label`, `type`, `visible`, `id_module_type`) VALUES
('ploopi_group', 'shared', 'shared', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'depth', 'depth', 'int(10) unsigned', 1, 1),
('ploopi_group', 'id_workspace', 'id_workspace', 'int(10) unsigned', 0, 1),
('ploopi_group', 'protected', 'protected', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'parents', 'parents', 'varchar(100)', 1, 1),
('ploopi_group', 'system', 'system', 'tinyint(1) unsigned', 1, 1),
('ploopi_group', 'label', 'label', 'varchar(255)', 1, 1),
('ploopi_group', 'id_group', 'id_group', 'int(10) unsigned', 0, 1),
('ploopi_group', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_workspace', 'frontoffice_domainlist', 'frontoffice_domainlist', 'longtext', 1, 1),
('ploopi_workspace', 'meta_robots', 'meta_robots', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_copyright', 'meta_copyright', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_author', 'meta_author', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_keywords', 'meta_keywords', 'longtext', 1, 1),
('ploopi_workspace', 'frontoffice', 'frontoffice', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'backoffice_domainlist', 'backoffice_domainlist', 'longtext', 1, 1),
('ploopi_workspace', 'title', 'title', 'varchar(255)', 1, 1),
('ploopi_workspace', 'meta_description', 'meta_description', 'longtext', 1, 1),
('ploopi_workspace', 'backoffice', 'backoffice', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'mustdefinerule', 'mustdefinerule', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'depth', 'depth', 'int(10)', 1, 1),
('ploopi_workspace', 'template', 'template', 'varchar(255)', 1, 1),
('ploopi_workspace', 'macrules', 'macrules', 'text', 1, 1),
('ploopi_workspace', 'iprules', 'iprules', 'text', 1, 1),
('ploopi_workspace', 'code', 'code', 'text', 1, 1),
('ploopi_workspace', 'system', 'system', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'protected', 'protected', 'tinyint(1) unsigned', 1, 1),
('ploopi_workspace', 'parents', 'parents', 'varchar(255)', 1, 1),
('ploopi_workspace', 'label', 'label', 'varchar(255)', 1, 1),
('ploopi_workspace', 'id_workspace', 'id_workspace', 'int(10) unsigned', 0, 1),
('ploopi_workspace', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_user', 'rank', 'rank', 'varchar(255)', 1, 1),
('ploopi_user', 'civility', 'civility', 'varchar(16)', 1, 1),
('ploopi_user', 'office', 'office', 'varchar(255)', 1, 1),
('ploopi_user', 'floor', 'floor', 'varchar(255)', 1, 1),
('ploopi_user', 'building', 'building', 'varchar(255)', 1, 1),
('ploopi_user', 'timezone', 'timezone', 'varchar(64)', 1, 1),
('ploopi_user', 'color', 'color', 'varchar(16)', 1, 1),
('ploopi_user', 'servertimezone', 'servertimezone', 'tinyint(1) unsigned', 1, 1),
('ploopi_user', 'ticketsbyemail', 'ticketsbyemail', 'tinyint(1) unsigned', 1, 1),
('ploopi_user', 'country', 'country', 'varchar(255)', 1, 1),
('ploopi_user', 'city', 'city', 'varchar(255)', 1, 1),
('ploopi_user', 'service', 'service', 'varchar(255)', 1, 1),
('ploopi_user', 'function', 'function', 'varchar(255)', 1, 1),
('ploopi_user', 'number', 'number', 'varchar(255)', 1, 1),
('ploopi_user', 'postalcode', 'postalcode', 'varchar(16)', 1, 1),
('ploopi_user', 'mobile', 'mobile', 'varchar(32)', 1, 1),
('ploopi_user', 'address', 'address', 'text', 1, 1),
('ploopi_user', 'comments', 'comments', 'text', 1, 1),
('ploopi_user', 'fax', 'fax', 'varchar(32)', 1, 1),
('ploopi_user', 'phone', 'phone', 'varchar(32)', 1, 1),
('ploopi_user', 'email', 'email', 'varchar(255)', 1, 1),
('ploopi_user', 'date_expire', 'date_expire', 'bigint(14)', 1, 1),
('ploopi_user', 'login', 'login', 'varchar(32)', 1, 1),
('ploopi_user', 'date_creation', 'date_creation', 'bigint(14)', 1, 1),
('ploopi_user', 'firstname', 'firstname', 'varchar(100)', 1, 1),
('ploopi_user', 'lastname', 'lastname', 'varchar(100)', 1, 1),
('ploopi_user', 'id', 'id', 'int(10) unsigned', 0, 1),
('ploopi_module', 'autoconnect', 'autoconnect', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'transverseview', 'transverseview', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'viewmode', 'viewmode', 'int(10) unsigned', 1, 1),
('ploopi_module', 'adminrestricted', 'adminrestricted', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'herited', 'herited', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'public', 'public', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'shared', 'shared', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'visible', 'visible', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'active', 'active', 'tinyint(1) unsigned', 1, 1),
('ploopi_module', 'id_workspace', 'id_workspace', 'int(10)', 0, 1),
('ploopi_module', 'id_module_type', 'id_module_type', 'int(10) unsigned', 0, 1),
('ploopi_module', 'label', 'label', 'varchar(100)', 1, 1),
('ploopi_module', 'id', 'id', 'int(10)', 0, 1);

--
-- Structure de la table `ploopi_mb_relation`
--

DROP TABLE IF EXISTS `ploopi_mb_relation`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_relation` (
  `tablesrc` varchar(100) default NULL,
  `fieldsrc` varchar(100) default NULL,
  `tabledest` varchar(100) default NULL,
  `fielddest` varchar(100) default NULL,
  `id_module_type` int(10) unsigned NOT NULL default '0',
  KEY `tablesrc` (`tablesrc`),
  KEY `fieldsrc` (`fieldsrc`),
  KEY `tabledest` (`tabledest`),
  KEY `fielddest` (`fielddest`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_relation`
--

INSERT INTO `ploopi_mb_relation` (`tablesrc`, `fieldsrc`, `tabledest`, `fielddest`, `id_module_type`) VALUES
('ploopi_group', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_group', 'id_group', 'ploopi_group', 'id', 1),
('ploopi_workspace', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_module', 'id_workspace', 'ploopi_workspace', 'id', 1);

--
-- Structure de la table `ploopi_mb_schema`
--

DROP TABLE IF EXISTS `ploopi_mb_schema`;
CREATE TABLE IF NOT EXISTS `ploopi_mb_schema` (
  `tablesrc` varchar(100) NOT NULL default '',
  `tabledest` varchar(100) NOT NULL default '',
  `id_module_type` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tabledest`,`tablesrc`),
  KEY `tablesrc` (`tablesrc`),
  KEY `id_module_type` (`id_module_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `ploopi_mb_schema`
--

INSERT INTO `ploopi_mb_schema` (`tablesrc`, `tabledest`, `id_module_type`) VALUES
('ploopi_group', 'ploopi_workspace', 1),
('ploopi_workspace', 'ploopi_workspace', 1),
('ploopi_group', 'ploopi_group', 1),
('ploopi_module', 'ploopi_workspace', 1);

