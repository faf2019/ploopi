SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `ploopi_annotation` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` longtext,
  `object_label` varchar(255) NOT NULL DEFAULT '',
  `type_annotation` varchar(16) DEFAULT NULL,
  `date_annotation` varchar(14) DEFAULT NULL,
  `private` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `id_record` varchar(255) DEFAULT NULL,
  `id_object` int(10) UNSIGNED DEFAULT '0',
  `id_user` int(10) UNSIGNED DEFAULT '0',
  `id_workspace` int(10) DEFAULT NULL,
  `id_element` char(32) NOT NULL DEFAULT '0',
  `id_module` int(10) UNSIGNED DEFAULT '0',
  `id_module_type` int(10) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_annotation_tag` (
  `id_annotation` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_tag` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_captcha` (
  `id` varchar(255) NOT NULL,
  `cptuse` int(10) UNSIGNED NOT NULL,
  `codesound` varchar(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `time` int(20) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_confirmation_code` (
  `action` varchar(255) NOT NULL,
  `timestp` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `code` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_connecteduser` (
  `sid` char(32) NOT NULL DEFAULT '0',
  `ip` char(15) DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT '0',
  `workspace_id` int(10) DEFAULT NULL,
  `module_id` int(10) UNSIGNED DEFAULT '0',
  `timestp` bigint(14) DEFAULT '0'
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_documents_ext` (
  `ext` varchar(10) NOT NULL DEFAULT '',
  `filetype` varchar(16) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_documents_ext` (`ext`, `filetype`) VALUES
('odt', 'document'),
('doc', 'document'),
('xls', 'spreadsheet'),
('mp3', 'audio'),
('wav', 'audio'),
('ogg', 'audio'),
('jpg', 'image'),
('jpeg', 'image'),
('png', 'image'),
('gif', 'image'),
('psd', 'image'),
('xcf', 'image'),
('svg', 'image'),
('pdf', 'document'),
('avi', 'video'),
('wmv', 'video'),
('ogm', 'video'),
('mpg', 'video'),
('mpeg', 'video'),
('zip', 'archive'),
('tgz', 'archive'),
('gz', 'archive'),
('rar', 'archive'),
('bz2', 'archive'),
('ace', 'archive');

CREATE TABLE `ploopi_documents_file` (
  `id` int(10) UNSIGNED NOT NULL,
  `md5id` varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ref` varchar(255) NOT NULL,
  `timestp_file` bigint(14) UNSIGNED NOT NULL,
  `timestp_create` bigint(14) DEFAULT NULL,
  `timestp_modify` bigint(14) DEFAULT NULL,
  `size` int(10) UNSIGNED DEFAULT '0',
  `extension` varchar(20) DEFAULT NULL,
  `parents` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `nbclick` int(10) UNSIGNED DEFAULT '0',
  `id_folder` int(10) UNSIGNED DEFAULT '0',
  `id_user_modify` int(10) UNSIGNED DEFAULT '0',
  `id_user` int(10) UNSIGNED DEFAULT '0',
  `id_workspace` int(10) UNSIGNED DEFAULT '0',
  `id_module` int(10) UNSIGNED DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_documents_folder` (
  `id` int(10) UNSIGNED NOT NULL,
  `md5id` varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parents` varchar(255) DEFAULT '0',
  `timestp_create` bigint(14) DEFAULT NULL,
  `timestp_modify` bigint(14) DEFAULT NULL,
  `nbelements` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `id_folder` int(10) UNSIGNED DEFAULT '0',
  `id_user_modify` int(10) UNSIGNED DEFAULT '0',
  `id_user` int(10) UNSIGNED DEFAULT '0',
  `id_workspace` int(10) UNSIGNED DEFAULT '0',
  `id_module` int(10) UNSIGNED DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_group` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_group` int(10) UNSIGNED DEFAULT '0',
  `label` varchar(255) NOT NULL DEFAULT '',
  `system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `protected` tinyint(1) UNSIGNED DEFAULT '0',
  `parents` varchar(100) DEFAULT NULL,
  `depth` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `shared` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_group` (`id`, `id_group`, `label`, `system`, `protected`, `parents`, `depth`, `id_workspace`, `shared`) VALUES
(1, 0, 'system', 1, 1, '0', 1, 0, 0),
(3, 1, 'Groupe Principal', 0, 1, '0;1', 2, 1, 1);

CREATE TABLE `ploopi_group_user` (
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_group` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_group_user` (`id_user`, `id_group`) VALUES
(2, 3);

CREATE TABLE `ploopi_index_element` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_record` char(64) NOT NULL,
  `id_object` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `label` char(128) NOT NULL,
  `timestp_create` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `timestp_modify` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `timestp_lastindex` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `id_user` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `id_module` smallint(5) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_index_keyword_element` (
  `id_element` int(10) UNSIGNED NOT NULL,
  `keyword` char(20) NOT NULL,
  `weight` mediumint(10) UNSIGNED NOT NULL DEFAULT '0',
  `ratio` float UNSIGNED NOT NULL DEFAULT '0',
  `relevance` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;

CREATE TABLE `ploopi_index_phonetic_element` (
  `id_element` int(10) UNSIGNED NOT NULL,
  `phonetic` char(20) NOT NULL,
  `weight` mediumint(10) UNSIGNED NOT NULL DEFAULT '0',
  `ratio` float UNSIGNED NOT NULL DEFAULT '0',
  `relevance` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;

CREATE TABLE `ploopi_index_stem_element` (
  `id_element` int(10) UNSIGNED NOT NULL,
  `stem` char(20) NOT NULL,
  `weight` mediumint(10) UNSIGNED NOT NULL DEFAULT '0',
  `ratio` float UNSIGNED NOT NULL DEFAULT '0',
  `relevance` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;

CREATE TABLE `ploopi_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_method` varchar(255) DEFAULT NULL,
  `query_string` varchar(255) DEFAULT NULL,
  `remote_addr` varchar(64) DEFAULT NULL,
  `remote_port` int(10) UNSIGNED DEFAULT NULL,
  `script_filename` varchar(255) DEFAULT NULL,
  `path_translated` varchar(255) DEFAULT NULL,
  `script_name` varchar(255) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `ploopi_userid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ploopi_workspaceid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ploopi_moduleid` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `browser` varchar(64) DEFAULT NULL,
  `system` varchar(64) DEFAULT NULL,
  `ts` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `total_exec_time` int(10) UNSIGNED DEFAULT '0',
  `sql_exec_time` int(10) UNSIGNED DEFAULT '0',
  `sql_percent_time` int(10) UNSIGNED DEFAULT '0',
  `php_percent_time` int(10) UNSIGNED DEFAULT '0',
  `numqueries` int(10) UNSIGNED DEFAULT '0',
  `page_size` int(10) UNSIGNED DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_mb_action` (
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_action` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `id_workspace` int(10) DEFAULT NULL,
  `id_object` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `role_enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_action` (`id_module_type`, `id_action`, `label`, `description`, `id_workspace`, `id_object`, `role_enabled`) VALUES
(1, 1, 'Installer un Module', NULL, 0, 0, 1),
(1, 2, 'Désinstaller un Module', NULL, 0, 0, 1),
(1, 3, 'Modifier les Paramètres d\'un Module', NULL, 0, 0, 1),
(1, 4, 'Instancier / Utiliser un Module', NULL, 0, 0, 1),
(1, 5, 'Modifier les Propriétés d\'un Module', NULL, 0, 0, 1),
(1, 6, 'Modifier la Page d\'Accueil', NULL, 0, 0, 1),
(1, 7, 'Installer un Skin', NULL, 0, 0, 1),
(1, 8, 'Désinstaller un Skin', NULL, 0, 0, 1),
(1, 9, 'Créer un Groupe', NULL, 0, 0, 1),
(1, 10, 'Modifier un Groupe', NULL, 0, 0, 1),
(1, 11, 'Supprimer un Groupe', NULL, 0, 0, 1),
(1, 12, 'Cloner un Groupe', NULL, 0, 0, 1),
(1, 13, 'Créer un Rôle', NULL, 0, 0, 1),
(1, 14, 'Modifier un Rôle', NULL, 0, 0, 1),
(1, 15, 'Supprimer un Rôle', NULL, 0, 0, 1),
(1, 16, 'Créer un Profil', NULL, 0, 0, 1),
(1, 17, 'Modifier un Profil', NULL, 0, 0, 1),
(1, 18, 'Supprimer un Profil', NULL, 0, 0, 1),
(1, 19, 'Ajouter un Utilisateur', NULL, 0, 0, 1),
(1, 20, 'Modifier un Utilisateur', NULL, 0, 0, 1),
(1, 21, 'Supprimer un Utilisateur', NULL, 0, 0, 1),
(1, 22, 'Détacher un Module', NULL, 0, 0, 1),
(1, 23, 'Supprimer un Module', NULL, 0, 0, 1),
(1, 24, 'Mettre à jour la Métabase', NULL, 0, 0, 1),
(1, 25, 'Connexion Utilisateur', NULL, 0, 0, 1),
(1, 26, 'Erreur de Connexion', NULL, 0, 0, 1),
(1, 27, 'Déplacer un Utilisateur', NULL, 0, 0, 1),
(1, 28, 'Attacher un Utilisateur', NULL, 0, 0, 1),
(1, 29, 'Détacher un Utilisateur', NULL, 0, 0, 1),
(1, 32, 'Mettre à jour un module', NULL, 0, 0, 1),
(1, 39, 'Créer un Espace de Travail', NULL, 0, 0, 1),
(1, 40, 'Modifier un Espace de Travail', NULL, 0, 0, 1),
(1, 41, 'Supprimer un Espace de Travail', NULL, 0, 0, 1),
(1, 42, 'Clôner un Espace de Travail', NULL, 0, 0, 1);

CREATE TABLE `ploopi_mb_field` (
  `tablename` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `label` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `visible` tinyint(1) UNSIGNED DEFAULT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

CREATE TABLE `ploopi_mb_object` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(255) DEFAULT NULL,
  `script` varchar(255) DEFAULT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_object` (`id`, `label`, `script`, `id_module_type`) VALUES
(2, 'Groupe d\'Utilisateur', 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=org&groupid=<IDRECORD>', 1),
(1, 'Espace de Travail', 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=work&workspaceid=<IDRECORD>', 1);

CREATE TABLE `ploopi_mb_relation` (
  `tablesrc` varchar(100) DEFAULT NULL,
  `fieldsrc` varchar(100) DEFAULT NULL,
  `tabledest` varchar(100) DEFAULT NULL,
  `fielddest` varchar(100) DEFAULT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_relation` (`tablesrc`, `fieldsrc`, `tabledest`, `fielddest`, `id_module_type`) VALUES
('ploopi_group', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_group', 'id_group', 'ploopi_group', 'id', 1),
('ploopi_workspace', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_module', 'id_workspace', 'ploopi_workspace', 'id', 1);

CREATE TABLE `ploopi_mb_schema` (
  `tablesrc` varchar(100) NOT NULL DEFAULT '',
  `tabledest` varchar(100) NOT NULL DEFAULT '',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_schema` (`tablesrc`, `tabledest`, `id_module_type`) VALUES
('ploopi_group', 'ploopi_workspace', 1),
('ploopi_workspace', 'ploopi_workspace', 1),
('ploopi_group', 'ploopi_group', 1),
('ploopi_module', 'ploopi_workspace', 1);

CREATE TABLE `ploopi_mb_table` (
  `name` varchar(100) NOT NULL DEFAULT '',
  `label` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) UNSIGNED DEFAULT '1',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_table` (`name`, `label`, `visible`, `id_module_type`) VALUES
('ploopi_group', 'group', 1, 1),
('ploopi_workspace', 'workspace', 1, 1),
('ploopi_user', 'user', 1, 1),
('ploopi_module', 'module', 1, 1);

CREATE TABLE `ploopi_mb_wce_object` (
  `id` int(11) UNSIGNED NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `id_module_type` int(10) NOT NULL DEFAULT '0',
  `script` varchar(255) DEFAULT NULL,
  `select_id` varchar(64) DEFAULT NULL,
  `select_label` varchar(64) DEFAULT NULL,
  `select_table` varchar(64) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mb_wce_object` (`id`, `label`, `id_module_type`, `script`, `select_id`, `select_label`, `select_table`) VALUES
(1, 'Affichage Trombinscope', 1, '?object=\'display\'', NULL, NULL, NULL);

CREATE TABLE `ploopi_mimetype` (
  `ext` varchar(10) NOT NULL,
  `mimetype` varchar(255) NOT NULL,
  `filetype` varchar(50) NOT NULL,
  `group` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_mimetype` (`ext`, `mimetype`, `filetype`, `group`) VALUES
('txt', 'text/plain', 'text', 'text'),
('html', 'text/html', 'html', 'text'),
('htm', 'text/html', 'html', 'text'),
('shtml', 'text/html', 'html', 'text'),
('shtm', 'text/html', 'html', 'text'),
('xhtml', 'text/xhtml', 'html', 'text'),
('xhtm', 'text/xhtml', 'html', 'text'),
('css', 'text/css', 'html', 'text'),
('js', 'application/javascript', 'script', 'text'),
('latex', 'application/x-latex', 'text', 'text'),
('g', 'text/plain', 'text', 'text'),
('bas', 'text/plain', 'text', 'text'),
('h', 'text/plain', 'text', 'text'),
('c', 'text/plain', 'text', 'text'),
('cc', 'text/plain', 'text', 'text'),
('cpp', 'text/plain', 'text', 'text'),
('hpp', 'text/plain', 'text', 'text'),
('java', 'text/plain', 'text', 'text'),
('hh', 'text/plain', 'text', 'text'),
('m', 'text/plain', 'text', 'text'),
('f90', 'text/plain', 'text', 'text'),
('csv', 'text/csv', 'text', 'text'),
('tsv', 'text/tab-separated-values', 'text', 'text'),
('php', 'application/x-httpd-php', 'script', 'text'),
('php3', 'application/x-httpd-php', 'script', 'text'),
('php4', 'application/x-httpd-php', 'script', 'text'),
('phtml', 'application/x-httpd-php', 'script', 'text'),
('sql', 'text/x-sql', 'text', 'text'),
('323', 'text/h323', 'text', 'text'),
('tcl', 'application/x-tcl', 'text', 'text'),
('tex', 'application/x-tex', 'text', 'text'),
('ltx', 'application/x-tex', 'text', 'text'),
('texi', 'application/x-tex', 'text', 'text'),
('ctx', 'application/x-tex', 'text', 'text'),
('py', 'text/x-python', 'script', 'text'),
('pl', 'text/x-perl', 'script', 'text'),
('info', 'text/plain', 'text', 'text'),
('msl', 'text/plain', 'text', 'text'),
('sct', 'text/scriptlet', 'text', 'text'),
('tpl', 'text/css', 'html', 'text'),
('text', 'text/plain', 'text', 'text'),
('nfo', '', 'document', 'text'),
('png', 'image/png', 'image', 'image'),
('gif', 'image/gif', 'image', 'image'),
('jpg', 'image/jpeg', 'image', 'image'),
('jpeg', 'image/jpeg', 'image', 'image'),
('jpe', 'image/jpeg', 'image', 'image'),
('jfif', 'image/jpeg', 'image', 'image'),
('bmp', 'image/bmp', 'image', 'image'),
('pcx', 'image/pcx', 'image', 'image'),
('tif', 'image/tiff', 'image', 'image'),
('tiff', 'image/tiff', 'image', 'image'),
('pnm', 'image/x-portable-anymap', 'image', 'image'),
('pbm', 'image/x-portable-bitmap', 'image', 'image'),
('pgm', 'image/x-portable-graymap', 'image', 'image'),
('ppm', 'image/x-portable-pixmap', 'image', 'image'),
('xbm', 'image/x-xbitmap', 'image', 'image'),
('xpm', 'image/x-xpixmap', 'image', 'image'),
('ico', 'image/x-icon', 'image', 'image'),
('svg', 'image/svg+xml', 'image', 'image'),
('svgz', 'image/svg+xml', 'image', 'image'),
('rgb', 'image/x-rgb', 'image', 'image'),
('jng', 'image/x-jng', 'image', 'image'),
('cdr', 'image/x-coreldraw', 'image', 'image'),
('pat', 'image/x-coreldrawpattern', 'image', 'image'),
('cdt', 'image/x-coreldrawtemplate', 'image', 'image'),
('cpt', 'image/x-corelphotopaint', 'image', 'image'),
('gif87', 'image/gif', 'image', 'image'),
('pef', 'image/tiff', 'image', 'image'),
('png24', 'image/png', 'image', 'image'),
('png32', 'image/png', 'image', 'image'),
('png8', 'image/png', 'image', 'image'),
('ras', 'image/x-cmu-raster', 'image', 'image'),
('xwd', 'image/x-xwindowdump', 'image', 'image'),
('art', '', 'image', 'image'),
('arw', '', 'image', 'image'),
('bmp2', '', 'image', 'image'),
('bmp3', '', 'image', 'image'),
('cin', '', 'image', 'image'),
('cmyk', '', 'image', 'image'),
('cmyka', '', 'image', 'image'),
('cr2', '', 'image', 'image'),
('crw', '', 'image', 'image'),
('cur', '', 'image', 'image'),
('cut', '', 'image', 'image'),
('dcm', '', 'image', 'image'),
('dcx', '', 'image', 'image'),
('dds', '', 'image', 'image'),
('dfont', '', 'image', 'image'),
('djvu', '', 'image', 'image'),
('dng', '', 'image', 'image'),
('dpx', '', 'image', 'image'),
('exr', '', 'image', 'image'),
('fax', '', 'image', 'image'),
('fig', '', 'image', 'image'),
('fits', '', 'image', 'image'),
('gray', '', 'image', 'image'),
('icb', '', 'image', 'image'),
('icon', '', 'image', 'image'),
('ipl', '', 'image', 'image'),
('jp2', '', 'image', 'image'),
('jpc', '', 'image', 'image'),
('jpx', '', 'image', 'image'),
('k25', '', 'image', 'image'),
('kdc', '', 'image', 'image'),
('mat', '', 'image', 'image'),
('miff', '', 'image', 'image'),
('mono', '', 'image', 'image'),
('mrw', '', 'image', 'image'),
('mtv', '', 'image', 'image'),
('mvg', '', 'image', 'image'),
('nef', '', 'image', 'image'),
('orf', '', 'image', 'image'),
('otb', '', 'image', 'image'),
('pal', '', 'image', 'image'),
('palm', '', 'image', 'image'),
('pam', '', 'image', 'image'),
('pcd', '', 'image', 'image'),
('pcds', '', 'image', 'image'),
('pcl', '', 'image', 'image'),
('pct', '', 'image', 'image'),
('pfm', '', 'image', 'image'),
('picon', '', 'image', 'image'),
('pict', '', 'image', 'image'),
('pix', '', 'image', 'image'),
('pjpeg', '', 'image', 'image'),
('ptif', '', 'image', 'image'),
('pwp', '', 'image', 'image'),
('rad', '', 'image', 'image'),
('raf', '', 'image', 'image'),
('rgba', '', 'image', 'image'),
('rla', '', 'image', 'image'),
('rle', '', 'image', 'image'),
('sfw', '', 'image', 'image'),
('sgi', '', 'image', 'image'),
('sr2', '', 'image', 'image'),
('srf', '', 'image', 'image'),
('sun', '', 'image', 'image'),
('tga', '', 'image', 'image'),
('tiff64', '', 'image', 'image'),
('tim', '', 'image', 'image'),
('uil', '', 'image', 'image'),
('uyvy', '', 'image', 'image'),
('vicar', '', 'image', 'image'),
('viff', '', 'image', 'image'),
('vst', '', 'image', 'image'),
('wbmp', '', 'image', 'image'),
('wmz', '', 'image', 'image'),
('wpg', '', 'image', 'image'),
('x3f', '', 'image', 'image'),
('xc', '', 'image', 'image'),
('xcf', '', 'image', 'image'),
('ycbcr', '', 'image', 'image'),
('ycbcra', '', 'image', 'image'),
('yuv', '', 'image', 'image'),
('thm', 'application/vnd.eri.thm', 'image', 'thumbnails'),
('pdb', '', 'document', 'thumbnails'),
('man', 'application/x-troff-man', 'document', 'unix'),
('bz2', 'application/x-bzip', 'archive', 'archive'),
('gz', 'application/x-gzip', 'archive', 'archive'),
('tar', 'application/x-tar', 'archive', 'archive'),
('tgz', 'application/x-gzip', 'archive', 'archive'),
('zip', 'application/zip', 'archive', 'archive'),
('z', 'application/x-compress', 'archive', 'archive'),
('sit', 'application/x-stuffit', 'archive', 'archive'),
('sitx', 'application/x-stuffit', 'archive', 'archive'),
('lzh', 'application/lzh', 'archive', 'archive'),
('lhw', 'application/lzh', 'archive', 'archive'),
('lzs', 'application/lzh', 'archive', 'archive'),
('lzw', 'application/lzh', 'archive', 'archive'),
('ace', 'application/x-ace', 'archive', 'archive'),
('rar', 'application/x-rar', 'archive', 'archive'),
('arj', 'application/x-arj', 'archive', 'archive'),
('7z', 'application/x-7z-compressed', 'archive', 'archive'),
('rpm', 'application/x-redhat-package', 'package', 'package'),
('deb', 'application/x-debian-package', 'package', 'package'),
('udeb', 'application/x-debian-package', 'package', 'package'),
('aif', 'audio/aiff', 'audio', 'audio'),
('aiff', 'audio/aiff', 'audio', 'audio'),
('aifc', 'audio/aiff', 'audio', 'audio'),
('mid', 'audio/midi', 'audio', 'audio'),
('midi', 'audio/midi', 'audio', 'audio'),
('kar', 'audio/midi', 'audio', 'audio'),
('rmi', 'audio/midi', 'audio', 'audio'),
('mp3', 'audio/mpeg', 'audio', 'audio'),
('mp2', 'audio/mpeg', 'audio', 'audio'),
('mpa', 'audio/mpeg', 'audio', 'audio'),
('ogg', 'audio/ogg', 'audio', 'audio'),
('wav', 'audio/wav', 'audio', 'audio'),
('wma', 'audio/x-ms-wma', 'audio', 'audio'),
('au', 'audio/basic', 'audio', 'audio'),
('snd', 'audio/basic', 'audio', 'audio'),
('flac', 'audio/flac', 'audio', 'audio'),
('aac', 'audio/mp4', 'audio', 'audio'),
('m4a', 'audio/mp4', 'audio', 'audio'),
('mka', 'audio/x-matroska', 'audio', 'audio'),
('ac3', 'audio/ac3', 'audio', 'audio'),
('mpc', 'audio/x-musepack', 'audio', 'audio'),
('mod', 'audio/x-mod', 'audio', 'audio/tracker'),
('xm', 'audio/x-xm', 'audio', 'audio/tracker'),
('xi', 'audio/x-xi', 'audio', 'audio/tracker'),
('s3m', 'audio/x-s3m', 'audio', 'audio/tracker'),
('stm', 'audio/x-stm', 'audio', 'audio/tracker'),
('it', 'audio/x-it', 'audio', 'audio/tracker'),
('asf', 'video/x-ms-asf', 'video', 'video'),
('asx', 'video/x-ms-asf', 'video', 'video'),
('avi', 'video/avi', 'video', 'video'),
('mpg', 'video/mpeg', 'video', 'video'),
('mpeg', 'video/mpeg', 'video', 'video'),
('mpe', 'video/mpeg', 'video', 'video'),
('wmv', 'video/x-ms-wmv', 'video', 'video'),
('wmx', 'video/x-ms-wmx', 'video', 'video'),
('qt', 'video/quicktime', 'video', 'video'),
('mov', 'video/quicktime', 'video', 'video'),
('movie', 'video/x-sgi-movie', 'video', 'video'),
('mp4', 'audio/mp4', 'video', 'video'),
('flv', 'video/x-flv', 'video', 'video'),
('mkv', 'video/x-matroska', 'video', 'video'),
('3gp', 'video/3gpp', 'video', 'video'),
('dv', 'video/dv', 'video', 'video'),
('dif', 'video/dv', 'video', 'video'),
('dl', 'video/dl', 'video', 'video'),
('h264', 'video/h264', 'video', 'video'),
('viv', 'video/vivo', 'video', 'video'),
('vivo', 'video/vivo', 'video', 'video'),
('mng', 'video/x-mng', 'video', 'video'),
('gl', 'video/gl', 'video', 'video'),
('fli', 'video/fli', 'video', 'video'),
('ra', 'audio/vnd.rn-realaudio', 'video', 'real'),
('ram', 'audio/x-pn-realaudio', 'video', 'real'),
('rm', 'application/vnd.rn-realmedia', 'video', 'real'),
('rv', 'video/vnd.rn-realvideo', 'video', 'real'),
('rmvb', 'application/vnd.rn-realmedia-vbr', 'video', 'real'),
('smil', 'application/smil', 'video', 'real'),
('smi', 'application/smil', 'video', 'real'),
('avs', '', 'video', 'video'),
('dps', '', 'video', 'video'),
('m2v', '', 'video', 'video'),
('m4v', 'video/mp4', 'video', 'video'),
('ogm', '', 'video', 'video'),
('vid', '', 'video', 'video'),
('pls', 'audio/scpls', 'audio', 'playlist'),
('m3u', 'audio/x-mpegurl', 'audio', 'playlist'),
('mxu', 'video/vnd.mpegurl', 'video', 'playlist'),
('pla', 'audio/x-iriver-pla', 'audio', 'playlist'),
('xml', 'text/xml', 'text', 'xml'),
('xsl', 'text/xsl', 'text', 'xml'),
('sgml', 'text/x-sgml', 'text', 'xml'),
('sgm', 'text/x-sgml', 'text', 'xml'),
('flr', 'x-world/x-vrml', 'text', 'xml'),
('vrml', 'x-world/x-vrml', 'text', 'xml'),
('wrl', 'x-world/x-vrml', 'text', 'xml'),
('wrz', 'x-world/x-vrml', 'text', 'xml'),
('xaf', 'x-world/x-vrml', 'text', 'xml'),
('xof', 'x-world/x-vrml', 'text', 'xml'),
('rss', 'application/rss+xml', 'text', 'xml'),
('rdf', 'application/rdf+xml', 'text', 'xml'),
('atom', 'application/atom+xml', 'text', 'xml'),
('opml', 'application/opml+xml', 'text', 'xml'),
('xul', 'application/vnd.mozilla.xul+xml', 'text', 'xml'),
('abw', 'application/x-abiword', 'document', 'office'),
('gnumeric', 'application/x-gnumeric', 'document', 'office'),
('kwd', 'application/x-kword', 'document', 'office'),
('kwt', 'application/x-kword', 'document', 'office'),
('ksp', 'application/x-kspread', 'spreadsheet', 'office'),
('kpr', 'application/x-kpresenter', 'presentation', 'office'),
('kpt', 'application/x-kpresenter', 'presentation', 'office'),
('doc', 'application/msword', 'document', 'microsoft'),
('dot', 'application/msword', 'document', 'microsoft'),
('xls', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xla', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlc', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlm', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlt', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('xlw', 'application/vnd.ms-excel', 'spreadsheet', 'microsoft'),
('pps', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('ppt', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('ppz', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('pot', 'application/vnd.ms-powerpoint', 'presentation', 'microsoft'),
('hlp', 'application/mshelp', 'document', 'microsoft'),
('chm', 'application/mshelp', 'document', 'microsoft'),
('msg', 'application/vnd.ms-outlook', 'document', 'microsoft'),
('mpp', 'application/vnd.ms-project', 'document', 'microsoft'),
('wcm', 'application/vnd.ms-works', 'document', 'microsoft'),
('wdb', 'application/vnd.ms-works', 'document', 'microsoft'),
('wks', 'application/vnd.ms-works', 'document', 'microsoft'),
('wps', 'application/vnd.ms-works', 'document', 'microsoft'),
('mdb', 'application/x-msaccess', 'document', 'microsoft'),
('wmf', 'application/x-msmetafile', 'document', 'microsoft'),
('mny', 'application/x-msmoney', 'document', 'microsoft'),
('pub', 'application/x-mspublisher', 'presentation', 'microsoft'),
('scd', 'application/x-msschedule', 'document', 'microsoft'),
('trm', 'application/x-msterminal', 'document', 'microsoft'),
('wri', 'application/x-mswrite', 'document', 'microsoft'),
('vsd', 'application/vnd.visio', 'document', 'microsoft'),
('scr', '', 'exec', 'microsoft'),
('sxw', 'application/vnd.sun.xml.writer', 'document', 'open office'),
('stw', 'application/vnd.sun.xml.writer.template', 'document', 'open office'),
('sxg', 'application/vnd.sun.xml.writer.global', 'document', 'open office'),
('sxc', 'application/vnd.sun.xml.calc', 'spreadsheet', 'open office'),
('stc', 'application/vnd.sun.xml.calc.template', 'spreadsheet', 'open office'),
('sxi', 'application/vnd.sun.xml.impress', 'presentation', 'open office'),
('sti', 'application/vnd.sun.xml.impress.template', 'presentation', 'open office'),
('sxd', 'application/vnd.sun.xml.draw', 'image', 'open office'),
('std', 'application/vnd.sun.xml.draw.template', 'image', 'open office'),
('sxm', 'application/vnd.sun.xml.math', 'spreadsheet', 'open office'),
('odt', 'application/vnd.oasis.opendocument.text', 'document', 'open office'),
('otm', 'application/vnd.oasis.opendocument.text-master', 'document', 'open office'),
('ott', 'application/vnd.oasis.opendocument.text-template', 'document', 'open office'),
('odc', 'application/vnd.oasis.opendocument.chart', 'document', 'open office'),
('otc', 'application/vnd.oasis.opendocument.chart-template', 'document', 'open office'),
('odf', 'application/vnd.oasis.opendocument.formula', 'spreadsheet', 'open office'),
('odg', 'application/vnd.oasis.opendocument.graphics', 'image', 'open office'),
('otg', 'application/vnd.oasis.opendocument.graphics-template', 'image', 'open office'),
('odi', 'application/vnd.oasis.opendocument.image', 'image', 'open office'),
('oti', 'application/vnd.oasis.opendocument.image-template', 'image', 'open office'),
('odp', 'application/vnd.oasis.opendocument.presentation', 'presentation', 'open office'),
('otp', 'application/vnd.oasis.opendocument.presentation-template', 'presentation', 'open office'),
('ods', 'application/vnd.oasis.opendocument.spreadsheet', 'spreadsheet', 'open office'),
('ots', 'application/vnd.oasis.opendocument.spreadsheet-template', 'spreadsheet', 'open office'),
('oth', 'application/vnd.oasis.opendocument.text-web', 'html', 'open office'),
('rtf', 'text/rtf', 'document', 'rich text'),
('rtx', 'text/richtext', 'document', 'rich text'),
('pdf', 'application/pdf', 'document', 'adobe'),
('ai', 'application/postscript', 'image', 'adobe'),
('eps', 'application/postscript', 'document', 'adobe'),
('psd', 'image/psd', 'image', 'adobe'),
('ps', 'application/postscript', 'document', 'adobe'),
('dcr', 'application/x-director', 'document', 'adobe'),
('dir', 'application/x-director', 'document', 'adobe'),
('dxr', 'application/x-director', 'document', 'adobe'),
('swf', 'application/x-shockwave-flash', 'image', 'adobe'),
('swfl', 'application/x-shockwave-flash', 'image', 'adobe'),
('fla', 'application/x-shockwave-flash', 'image', 'adobe'),
('pdfa', '', 'document', 'adobe'),
('hqx', 'application/mac-binhex40', 'exec', 'binarie/executable'),
('exe', 'application/x-msdownload', 'exec', 'binarie/executable'),
('com', 'application/x-msdownload', 'exec', 'binarie/executable'),
('msi', 'application/x-msi', 'exec', 'binarie/executable'),
('class', 'application/x-java-class', 'exec', 'binarie/executable'),
('jar', 'application/java', 'exec', 'binarie/executable'),
('jad', 'text/vnd.sun.j2me.app-descriptor', 'exec', 'binarie/executable'),
('bin', '', 'exec', 'binarie/executable'),
('sh', 'application/x-sh', 'script', 'shell'),
('bat', 'application/x-msdownload', 'script', 'shell'),
('otf', 'font/opentype', 'document', 'fonts'),
('ttf', 'application/x-font-ttf', 'document', 'fonts'),
('ttc', 'application/x-font-ttf', 'document', 'fonts'),
('pfa', 'application/x-font-type1', 'document', 'fonts'),
('pfb', 'application/x-font-type1', 'document', 'fonts'),
('cer', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('crt', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('der', 'application/x-x509-ca-cert', 'certificate', 'encoding/certificat'),
('p12', 'application/x-pkcs12', 'certificate', 'encoding/certificat'),
('pfx', 'application/x-pkcs12', 'certificate', 'encoding/certificat'),
('p7b', 'application/x-pkcs7-certificates', 'certificate', 'encoding/certificat'),
('spc', 'application/x-pkcs7-certificates', 'certificate', 'encoding/certificat'),
('p7r', 'application/x-pkcs7-certreqresp', 'certificate', 'encoding/certificat'),
('p7c', 'application/x-pkcs7-mime', 'certificate', 'encoding/certificat'),
('p7m', 'application/x-pkcs7-mime', 'certificate', 'encoding/certificat'),
('p7s', 'application/x-pkcs7-signature', 'certificate', 'encoding/certificat'),
('iso', 'application/x-iso9660-image', 'cd', 'disk images'),
('nrg', 'application/x-extension-nrg', 'cd', 'disk images'),
('ccd', 'text/x-cdwizard', 'cd', 'disk images'),
('dmg', 'application/x-apple-diskimage', 'cd', 'disk images'),
('vcf', 'text/x-vcard', 'document', 'other'),
('vcs', 'text/x-vcalendar', 'calendar', 'other'),
('ics', 'text/calendar', 'calendar', 'other'),
('icz', 'text/calendar', 'calendar', 'other'),
('mht', 'message/rfc822', 'document', 'other'),
('mhtml', 'message/rfc822', 'document', 'other'),
('torrent', 'application/x-bittorrent', 'document', 'other'),
('docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'document', 'microsoft'),
('dotx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'document', 'microsoft'),
('xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'spreadsheet', 'microsoft'),
('xltx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'spreadsheet', 'microsoft'),
('xlam', 'application/vnd.ms-excel.addin.macroEnabled.12', 'spreadsheet', 'microsoft'),
('xlsb', 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'spreadsheet', 'microsoft'),
('potx', 'application/vnd.openxmlformats-officedocument.presentationml.template', 'presentation', 'microsoft'),
('ppsx', 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'presentation', 'microsoft'),
('pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'presentation', 'microsoft'),
('sldx', 'application/vnd.openxmlformats-officedocument.presentationml.slide', 'presentation', 'microsoft'),
('ogv', 'video/ogg', 'video', 'video'),
('oga', 'audio/ogg', 'audio', 'audio'),
('webm', 'video/webm', 'video', 'video');

CREATE TABLE `ploopi_module` (
  `id` int(10) NOT NULL,
  `label` varchar(100) NOT NULL DEFAULT '',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) DEFAULT NULL,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `public` tinyint(1) UNSIGNED DEFAULT '0',
  `shared` tinyint(1) UNSIGNED DEFAULT '0',
  `herited` tinyint(1) UNSIGNED DEFAULT '0',
  `adminrestricted` tinyint(1) UNSIGNED DEFAULT '0',
  `viewmode` int(10) UNSIGNED DEFAULT '1',
  `transverseview` tinyint(1) UNSIGNED DEFAULT '0',
  `autoconnect` tinyint(1) UNSIGNED DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_module` (`id`, `label`, `id_module_type`, `id_workspace`, `active`, `visible`, `public`, `shared`, `herited`, `adminrestricted`, `viewmode`, `transverseview`, `autoconnect`) VALUES
(1, 'Système', 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0),
(-1, 'Recherche', 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0);

CREATE TABLE `ploopi_module_type` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `publicparam` tinyint(1) UNSIGNED DEFAULT '0',
  `description` longtext,
  `version` varchar(32) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `date` varchar(14) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_module_type` (`id`, `label`, `system`, `publicparam`, `description`, `version`, `author`, `date`) VALUES
(1, 'system', 1, 0, 'Noyau du système', '1.9.7.1', 'Ovensia', '20190430000000');

CREATE TABLE `ploopi_module_workspace` (
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) NOT NULL DEFAULT '0',
  `position` tinyint(2) NOT NULL DEFAULT '0',
  `blockposition` char(10) NOT NULL DEFAULT 'left'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_param_choice` (
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `displayed_value` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES
(1, 'system_generate_htpasswd', '0', 'non'),
(1, 'system_generate_htpasswd', '1', 'oui'),
(1, 'system_submenu_display', '1', 'oui'),
(1, 'system_focus_popup', '0', 'non'),
(1, 'system_focus_popup', '1', 'oui'),
(1, 'system_search_displaymodule', '0', 'non'),
(1, 'system_search_displaymodule', '1', 'oui'),
(1, 'system_search_displayindexed', '0', 'non'),
(1, 'system_search_displayindexed', '1', 'oui'),
(1, 'system_search_displayworkspace', '0', 'non'),
(1, 'system_search_displayworkspace', '1', 'oui'),
(1, 'system_search_displayuser', '0', 'non'),
(1, 'system_search_displayuser', '1', 'oui'),
(1, 'system_search_displaydatetime', '0', 'non'),
(1, 'system_search_displaydatetime', '1', 'oui'),
(1, 'system_search_displayobjecttype', '0', 'non'),
(1, 'system_search_displayobjecttype', '1', 'oui'),
(1, 'system_submenu_display', '0', 'non'),
(1, 'system_password_force_update', '0', 'non'),
(1, 'system_password_force_update', '1', 'oui'),
(1, 'system_profile_edit_allowed', '0', 'non'),
(1, 'system_profile_edit_allowed', '1', 'oui');

CREATE TABLE `ploopi_param_default` (
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES
(1, 'system_generate_htpasswd', '0', 1),
(1, 'system_language', 'french', 1),
(1, 'system_jodwebservice', '', 1),
(1, 'system_focus_popup', '0', 1),
(1, 'system_search_displaymodule', '1', 1),
(1, 'system_search_displayindexed', '1', 1),
(1, 'system_search_displayworkspace', '1', 1),
(1, 'system_search_displayuser', '1', 1),
(1, 'system_search_displaydatetime', '1', 1),
(1, 'system_search_displayobjecttype', '1', 1),
(1, 'system_submenu_display', '1', 1),
(1, 'system_unoconv', '/usr/bin/unoconv', 1),
(1, 'system_user_required_fields', 'email,phone,service,function,city', 1),
(1, 'system_password_force_update', '0', 1),
(1, 'system_password_validity', '0', 1),
(1, 'system_profile_edit_allowed', '1', 1),
(1, 'system_trombi_maxlines', '25', 1);

CREATE TABLE `ploopi_param_type` (
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `default_value` text NOT NULL,
  `public` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `description` longtext,
  `label` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES
(1, 'system_generate_htpasswd', '1', 0, '', 'Générer un fichier htpasswd'),
(1, 'system_language', '', 1, '', 'Langue du système'),
(1, 'system_jodwebservice', '', 0, '', 'URL du webservice JODConverter'),
(1, 'system_focus_popup', '0', 0, '', 'Activer le Focus sur les Popups'),
(1, 'system_search_displaymodule', '0', 0, '', 'Afficher la colonne "Module" dans la recherche'),
(1, 'system_search_displayindexed', '0', 0, '', 'Afficher la colonne "Indexé le" dans la recherche'),
(1, 'system_search_displayworkspace', '0', 0, '', 'Afficher la colonne "Espace" dans la recherche'),
(1, 'system_search_displayuser', '0', 0, '', 'Afficher la colonne "Utilisateur" dans la recherche'),
(1, 'system_search_displaydatetime', '0', 0, '', 'Afficher la colonne "Ajouté le" dans la recherche'),
(1, 'system_search_displayobjecttype', '0', 0, '', 'Afficher la colonne "Type d\'Objet" dans la recherche'),
(1, 'system_submenu_display', '1', 0, NULL, 'Afficher les sous-menus de (Mon Espace)'),
(1, 'system_unoconv', '', 0, '', 'Chemin vers UNOCONV'),
(1, 'system_user_required_fields', 'email,phone,service,function,city', 0, NULL, 'Champs requis dans le profil utilisateur'),
(1, 'system_password_force_update', '0', 0, NULL, 'Forcer le changement de mot de passe lors de la prochaine connexion'),
(1, 'system_password_validity', '0', 0, NULL, 'Durée de validité du mot de passe en jours'),
(1, 'system_profile_edit_allowed', '1', 0, NULL, 'L\'utilisateur peut modifier son profil'),
(1, 'system_trombi_maxlines', '25', 0, NULL, 'Nombre de réponses maxi dans la recherche (sinon index alphabétique)');

CREATE TABLE `ploopi_param_user` (
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_param_workspace` (
  `id_module` int(10) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `id_workspace` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_role` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_module` int(10) UNSIGNED DEFAULT '0',
  `id_workspace` int(10) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `def` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `shared` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_role_action` (
  `id_role` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_action` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_serializedvar` (
  `id` char(32) NOT NULL,
  `id_session` char(32) NOT NULL,
  `data` longblob
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_session` (
  `id` char(32) NOT NULL,
  `access` int(10) UNSIGNED DEFAULT NULL,
  `data` longblob
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_share` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_record` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_object` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type_share` varchar(16) DEFAULT '0',
  `id_share` int(10) UNSIGNED DEFAULT '0',
  `id_module_type` int(10) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_subscription` (
  `id` char(32) NOT NULL,
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_object` int(10) NOT NULL DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `allactions` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_subscription_action` (
  `id_subscription` char(32) NOT NULL,
  `id_action` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_tag` (
  `id` int(10) UNSIGNED NOT NULL,
  `tag` char(32) NOT NULL,
  `tag_clean` char(32) NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_ticket` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` longtext,
  `needed_validation` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `delivery_notification` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `status` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `object_label` varchar(255) NOT NULL DEFAULT '',
  `timestp` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `lastreply_timestp` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `lastedit_timestp` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `count_read` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `count_replies` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_object` int(10) DEFAULT '0',
  `id_module` int(10) UNSIGNED DEFAULT '0',
  `id_record` varchar(255) DEFAULT NULL,
  `id_user` int(10) UNSIGNED DEFAULT '0',
  `id_workspace` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `root_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `deleted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_ticket_dest` (
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_ticket` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_ticket_status` (
  `id_ticket` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `timestp` bigint(14) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_ticket_watch` (
  `id_ticket` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `notify` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_user` (
  `id` int(10) UNSIGNED NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `login` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `date_creation` bigint(14) NOT NULL DEFAULT '0',
  `date_expire` bigint(14) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `comments` text NOT NULL,
  `address` text NOT NULL,
  `mobile` varchar(32) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `service` varchar(255) NOT NULL,
  `service2` varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `postalcode` varchar(16) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `ticketsbyemail` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `servertimezone` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `color` varchar(16) NOT NULL DEFAULT '',
  `timezone` varchar(64) NOT NULL,
  `building` varchar(255) NOT NULL,
  `floor` varchar(255) NOT NULL,
  `office` varchar(255) NOT NULL,
  `civility` varchar(16) NOT NULL,
  `rank` varchar(255) NOT NULL,
  `password_force_update` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `password_validity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `password_last_update` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `disabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `failed_attemps` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `jailed_since` bigint(14) UNSIGNED NOT NULL DEFAULT '0',
  `last_connection` bigint(14) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_user` (`id`, `lastname`, `firstname`, `login`, `password`, `date_creation`, `date_expire`, `email`, `phone`, `fax`, `comments`, `address`, `mobile`, `entity`, `service`, `service2`, `function`, `number`, `postalcode`, `city`, `country`, `ticketsbyemail`, `servertimezone`, `color`, `timezone`, `building`, `floor`, `office`, `civility`, `rank`, `password_force_update`, `password_validity`, `password_last_update`, `disabled`, `failed_attemps`, `jailed_since`, `last_connection`) VALUES
(2, 'Administrateur', '', 'admin', 'feee4f3ca6345d6562972e7c3a9dad9b', 20150608225254, 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 1, '', '0', '', '', '', '', '', 0, 0, 20150608225254, 0, 0, 0, 0);

CREATE TABLE `ploopi_user_action_log` (
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_action` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_module_type` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_record` char(255) NOT NULL,
  `ip` char(16) NOT NULL,
  `timestp` bigint(14) NOT NULL DEFAULT '0',
  `user` char(100) NOT NULL,
  `workspace` char(100) NOT NULL,
  `action` char(100) NOT NULL,
  `module_type` char(100) NOT NULL,
  `module` char(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_validation` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_module` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_record` varchar(255) NOT NULL,
  `id_object` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `type_validation` varchar(16) DEFAULT '0',
  `id_validation` int(10) UNSIGNED DEFAULT '0',
  `id_module_type` int(10) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_workspace` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_workspace` int(10) UNSIGNED DEFAULT '0',
  `label` varchar(255) NOT NULL DEFAULT 'NULL',
  `code` varchar(255) NOT NULL,
  `system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `protected` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `parents` varchar(255) DEFAULT 'NULL',
  `iprules` text,
  `macrules` text,
  `template` varchar(255) DEFAULT NULL,
  `depth` int(10) NOT NULL DEFAULT '0',
  `mustdefinerule` tinyint(1) UNSIGNED DEFAULT '0',
  `backoffice` tinyint(1) UNSIGNED DEFAULT '1',
  `frontoffice` tinyint(1) UNSIGNED DEFAULT '0',
  `backoffice_domainlist` longtext,
  `title` varchar(255) NOT NULL DEFAULT '',
  `meta_description` longtext NOT NULL,
  `meta_keywords` longtext NOT NULL,
  `meta_author` varchar(255) NOT NULL DEFAULT '',
  `meta_copyright` varchar(255) NOT NULL DEFAULT '',
  `meta_robots` varchar(255) NOT NULL DEFAULT 'index, follow, all',
  `frontoffice_domainlist` longtext,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_workspace` (`id`, `id_workspace`, `label`, `code`, `system`, `protected`, `parents`, `iprules`, `macrules`, `template`, `depth`, `mustdefinerule`, `backoffice`, `frontoffice`, `backoffice_domainlist`, `title`, `meta_description`, `meta_keywords`, `meta_author`, `meta_copyright`, `meta_robots`, `frontoffice_domainlist`, `priority`) VALUES
(2, 1, 'Espace Principal', '', 0, 0, '0;1', '', '', 'ploopi2', 2, 0, 1, 0, '*\r\n', '', '', '', '', '', '', '*', 1),
(1, 0, 'system', '', 1, 0, '0', NULL, NULL, 'ploopi2', 1, 0, 1, 0, NULL, '', '', '', '', '', 'index, follow, all', '', 0);

CREATE TABLE `ploopi_workspace_group` (
  `id_group` int(10) NOT NULL DEFAULT '0',
  `id_workspace` int(10) NOT NULL DEFAULT '0',
  `adminlevel` tinyint(3) UNSIGNED DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_workspace_group_role` (
  `id_group` int(10) NOT NULL DEFAULT '0',
  `id_workspace` int(10) NOT NULL DEFAULT '0',
  `id_role` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ploopi_workspace_user` (
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_profile` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `adminlevel` tinyint(3) UNSIGNED DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ploopi_workspace_user` (`id_user`, `id_workspace`, `id_profile`, `adminlevel`) VALUES
(2, 2, 1, 99);

CREATE TABLE `ploopi_workspace_user_role` (
  `id_user` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `id_workspace` int(10) NOT NULL DEFAULT '0',
  `id_role` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `ploopi_annotation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_record` (`id_record`),
  ADD KEY `id_object` (`id_object`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_annotation_tag`
  ADD PRIMARY KEY (`id_annotation`,`id_tag`),
  ADD KEY `id_tag` (`id_tag`);

ALTER TABLE `ploopi_captcha`
  ADD KEY `id` (`id`);

ALTER TABLE `ploopi_confirmation_code`
  ADD PRIMARY KEY (`action`);

ALTER TABLE `ploopi_connecteduser`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `workspace_id` (`workspace_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `timestp` (`timestp`);

ALTER TABLE `ploopi_documents_ext`
  ADD PRIMARY KEY (`ext`),
  ADD KEY `filetype` (`filetype`);

ALTER TABLE `ploopi_documents_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_group` (`id_workspace`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `name` (`name`),
  ADD KEY `id_folder` (`id_folder`),
  ADD KEY `extension` (`extension`),
  ADD KEY `md5id` (`md5id`);

ALTER TABLE `ploopi_documents_folder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_group` (`id_workspace`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_folder` (`id_folder`),
  ADD KEY `md5id` (`md5id`);

ALTER TABLE `ploopi_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `shared` (`shared`),
  ADD KEY `system` (`system`),
  ADD KEY `protected` (`protected`),
  ADD KEY `parents` (`parents`);

ALTER TABLE `ploopi_group_user`
  ADD PRIMARY KEY (`id_group`,`id_user`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_group` (`id_group`);

ALTER TABLE `ploopi_index_element`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_record` (`id_record`),
  ADD KEY `id_object` (`id_object`),
  ADD KEY `timestp_create` (`timestp_create`),
  ADD KEY `timestp_modify` (`timestp_modify`),
  ADD KEY `timestp_lastindex` (`timestp_lastindex`);

ALTER TABLE `ploopi_index_keyword_element`
  ADD KEY `id_element` (`id_element`),
  ADD KEY `keyword` (`keyword`);

ALTER TABLE `ploopi_index_phonetic_element`
  ADD KEY `id_element` (`id_element`),
  ADD KEY `phonetic` (`phonetic`);

ALTER TABLE `ploopi_index_stem_element`
  ADD KEY `id_element` (`id_element`),
  ADD KEY `stem` (`stem`);

ALTER TABLE `ploopi_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ploopi_mb_action`
  ADD PRIMARY KEY (`id_action`,`id_module_type`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_object` (`id_object`),
  ADD KEY `role_enabled` (`role_enabled`);

ALTER TABLE `ploopi_mb_field`
  ADD PRIMARY KEY (`tablename`,`name`),
  ADD KEY `visible` (`visible`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_mb_object`
  ADD PRIMARY KEY (`id`,`id_module_type`);

ALTER TABLE `ploopi_mb_relation`
  ADD KEY `tablesrc` (`tablesrc`),
  ADD KEY `fieldsrc` (`fieldsrc`),
  ADD KEY `tabledest` (`tabledest`),
  ADD KEY `fielddest` (`fielddest`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_mb_schema`
  ADD PRIMARY KEY (`tabledest`,`tablesrc`),
  ADD KEY `tablesrc` (`tablesrc`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_mb_table`
  ADD PRIMARY KEY (`name`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_mb_wce_object`
  ADD PRIMARY KEY (`id`,`id_module_type`);

ALTER TABLE `ploopi_mimetype`
  ADD PRIMARY KEY (`ext`);

ALTER TABLE `ploopi_module`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `active` (`active`),
  ADD KEY `shared` (`shared`),
  ADD KEY `herited` (`herited`);

ALTER TABLE `ploopi_module_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `label` (`label`);

ALTER TABLE `ploopi_module_workspace`
  ADD PRIMARY KEY (`id_workspace`,`id_module`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_workspace` (`id_workspace`);

ALTER TABLE `ploopi_param_choice`
  ADD PRIMARY KEY (`id_module_type`,`name`,`value`),
  ADD KEY `name` (`name`),
  ADD KEY `value` (`value`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_param_default`
  ADD PRIMARY KEY (`id_module`,`name`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_param_type`
  ADD PRIMARY KEY (`id_module_type`,`name`),
  ADD KEY `name` (`name`);

ALTER TABLE `ploopi_param_user`
  ADD PRIMARY KEY (`id_module`,`name`,`id_user`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `name` (`name`),
  ADD KEY `id_module` (`id_module`);

ALTER TABLE `ploopi_param_workspace`
  ADD PRIMARY KEY (`id_module`,`name`,`id_workspace`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `name` (`name`),
  ADD KEY `id_workspace` (`id_workspace`);

ALTER TABLE `ploopi_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `shared` (`shared`);

ALTER TABLE `ploopi_role_action`
  ADD PRIMARY KEY (`id_action`,`id_module_type`,`id_role`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `id_action` (`id_action`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_serializedvar`
  ADD PRIMARY KEY (`id`,`id_session`),
  ADD KEY `id_session` (`id_session`);

ALTER TABLE `ploopi_session`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ploopi_share`
  ADD PRIMARY KEY (`id`),
  ADD KEY `search` (`id_module`,`id_object`,`id_record`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_share` (`id_share`),
  ADD KEY `type_share` (`type_share`);

ALTER TABLE `ploopi_subscription`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_object` (`id_object`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_action` (`allactions`);

ALTER TABLE `ploopi_subscription_action`
  ADD PRIMARY KEY (`id_subscription`,`id_action`),
  ADD KEY `id_action` (`id_action`);

ALTER TABLE `ploopi_tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `tag` (`tag`);

ALTER TABLE `ploopi_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_object` (`id_object`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `root_id` (`root_id`),
  ADD KEY `deleted` (`deleted`);

ALTER TABLE `ploopi_ticket_dest`
  ADD PRIMARY KEY (`id_user`,`id_ticket`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_user` (`id_user`);

ALTER TABLE `ploopi_ticket_status`
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_user` (`id_user`);

ALTER TABLE `ploopi_ticket_watch`
  ADD PRIMARY KEY (`id_ticket`,`id_user`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `notify` (`notify`);

ALTER TABLE `ploopi_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_unique` (`login`),
  ADD KEY `lastname` (`lastname`),
  ADD KEY `firstname` (`firstname`),
  ADD KEY `password_last_update` (`password_last_update`),
  ADD KEY `disabled` (`disabled`),
  ADD KEY `failed_attemps` (`failed_attemps`),
  ADD KEY `last_connection` (`last_connection`);
ALTER TABLE `ploopi_user` ADD FULLTEXT KEY `ft` (`lastname`,`firstname`,`email`,`comments`,`service`,`service2`,`function`,`city`,`building`,`floor`,`office`,`rank`);

ALTER TABLE `ploopi_user_action_log`
  ADD KEY `user` (`user`),
  ADD KEY `workspace` (`workspace`),
  ADD KEY `action` (`action`),
  ADD KEY `module_type` (`module_type`),
  ADD KEY `module` (`module`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_action` (`id_action`),
  ADD KEY `id_module_type` (`id_module_type`),
  ADD KEY `id_module` (`id_module`),
  ADD KEY `id_record` (`id_record`);

ALTER TABLE `ploopi_validation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `search` (`id_module`,`id_object`,`id_record`),
  ADD KEY `type_workflow` (`type_validation`),
  ADD KEY `id_workflow` (`id_validation`),
  ADD KEY `id_module_type` (`id_module_type`);

ALTER TABLE `ploopi_workspace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `priority` (`priority`),
  ADD KEY `system` (`system`),
  ADD KEY `code` (`code`);

ALTER TABLE `ploopi_workspace_group`
  ADD PRIMARY KEY (`id_group`,`id_workspace`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_group` (`id_group`),
  ADD KEY `id_group_2` (`id_group`);

ALTER TABLE `ploopi_workspace_group_role`
  ADD PRIMARY KEY (`id_group`,`id_workspace`,`id_role`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `id_group` (`id_group`);

ALTER TABLE `ploopi_workspace_user`
  ADD PRIMARY KEY (`id_user`,`id_workspace`),
  ADD KEY `id_workspace` (`id_workspace`);

ALTER TABLE `ploopi_workspace_user_role`
  ADD PRIMARY KEY (`id_user`,`id_workspace`,`id_role`),
  ADD KEY `id_workspace` (`id_workspace`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `id_user` (`id_user`);


ALTER TABLE `ploopi_annotation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_documents_file`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_documents_folder`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_index_element`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_module`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_module_type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_role`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_share`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_tag`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_ticket`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_validation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `ploopi_workspace`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `ploopi_log` SET `request_method` = '' WHERE ISNULL(`request_method`);
ALTER TABLE `ploopi_log` CHANGE `request_method` `request_method` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `query_string` = '' WHERE ISNULL(`query_string`);
ALTER TABLE `ploopi_log` CHANGE `query_string` `query_string` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `remote_addr` = '' WHERE ISNULL(`remote_addr`);
ALTER TABLE `ploopi_log` CHANGE `remote_addr` `remote_addr` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `remote_port` = 0  WHERE ISNULL(`remote_port`);
ALTER TABLE `ploopi_log` CHANGE `remote_port` `remote_port` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `script_filename` = '' WHERE ISNULL(`script_filename`);
ALTER TABLE `ploopi_log` CHANGE `script_filename` `script_filename` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `path_translated` = '' WHERE ISNULL(`path_translated`);
ALTER TABLE `ploopi_log` CHANGE `path_translated` `path_translated` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `script_name` = '' WHERE ISNULL(`script_name`);
ALTER TABLE `ploopi_log` CHANGE `script_name` `script_name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `request_uri` = '' WHERE ISNULL(`request_uri`);
ALTER TABLE `ploopi_log` CHANGE `request_uri` `request_uri` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `browser` = '' WHERE ISNULL(`browser`);
ALTER TABLE `ploopi_log` CHANGE `browser` `browser` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `system` = '' WHERE ISNULL(`system`);
ALTER TABLE `ploopi_log` CHANGE `system` `system` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_log` SET `total_exec_time` = 0  WHERE ISNULL(`total_exec_time`);
ALTER TABLE `ploopi_log` CHANGE `total_exec_time` `total_exec_time` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `sql_exec_time` = 0  WHERE ISNULL(`sql_exec_time`);
ALTER TABLE `ploopi_log` CHANGE `sql_exec_time` `sql_exec_time` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `sql_percent_time` = 0  WHERE ISNULL(`sql_percent_time`);
ALTER TABLE `ploopi_log` CHANGE `sql_percent_time` `sql_percent_time` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `php_percent_time` = 0  WHERE ISNULL(`php_percent_time`);
ALTER TABLE `ploopi_log` CHANGE `php_percent_time` `php_percent_time` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `numqueries` = 0  WHERE ISNULL(`numqueries`);
ALTER TABLE `ploopi_log` CHANGE `numqueries` `numqueries` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_log` SET `page_size` = 0  WHERE ISNULL(`page_size`);
ALTER TABLE `ploopi_log` CHANGE `page_size` `page_size` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_tag` SET `tag` = '' WHERE ISNULL(`tag`);
ALTER TABLE `ploopi_tag` CHANGE `tag` `tag` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_tag` SET `tag_clean` = '' WHERE ISNULL(`tag_clean`);
ALTER TABLE `ploopi_tag` CHANGE `tag_clean` `tag_clean` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_validation` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_validation` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_validation` SET `type_validation` = '' WHERE ISNULL(`type_validation`);
ALTER TABLE `ploopi_validation` CHANGE `type_validation` `type_validation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_validation` SET `id_validation` = 0  WHERE ISNULL(`id_validation`);
ALTER TABLE `ploopi_validation` CHANGE `id_validation` `id_validation` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_validation` SET `id_module_type` = 0  WHERE ISNULL(`id_module_type`);
ALTER TABLE `ploopi_validation` CHANGE `id_module_type` `id_module_type` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_module` CHANGE `id_workspace` `id_workspace` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `public` = 0  WHERE ISNULL(`public`);
ALTER TABLE `ploopi_module` CHANGE `public` `public` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `shared` = 0  WHERE ISNULL(`shared`);
ALTER TABLE `ploopi_module` CHANGE `shared` `shared` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `herited` = 0  WHERE ISNULL(`herited`);
ALTER TABLE `ploopi_module` CHANGE `herited` `herited` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `adminrestricted` = 0  WHERE ISNULL(`adminrestricted`);
ALTER TABLE `ploopi_module` CHANGE `adminrestricted` `adminrestricted` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `viewmode` = 0  WHERE ISNULL(`viewmode`);
ALTER TABLE `ploopi_module` CHANGE `viewmode` `viewmode` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `transverseview` = 0  WHERE ISNULL(`transverseview`);
ALTER TABLE `ploopi_module` CHANGE `transverseview` `transverseview` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module` SET `autoconnect` = 0  WHERE ISNULL(`autoconnect`);
ALTER TABLE `ploopi_module` CHANGE `autoconnect` `autoconnect` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_index_keyword_element` SET `id_element` = 0  WHERE ISNULL(`id_element`);
ALTER TABLE `ploopi_index_keyword_element` CHANGE `id_element` `id_element` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_index_keyword_element` SET `keyword` = '' WHERE ISNULL(`keyword`);
ALTER TABLE `ploopi_index_keyword_element` CHANGE `keyword` `keyword` char(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_annotation` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_annotation` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_annotation` SET `type_annotation` = '' WHERE ISNULL(`type_annotation`);
ALTER TABLE `ploopi_annotation` CHANGE `type_annotation` `type_annotation` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_annotation` SET `date_annotation` = '' WHERE ISNULL(`date_annotation`);
ALTER TABLE `ploopi_annotation` CHANGE `date_annotation` `date_annotation` varchar(14) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_annotation` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_object` = 0  WHERE ISNULL(`id_object`);
ALTER TABLE `ploopi_annotation` CHANGE `id_object` `id_object` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_annotation` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_annotation` CHANGE `id_workspace` `id_workspace` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_annotation` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_annotation` SET `id_module_type` = 0  WHERE ISNULL(`id_module_type`);
ALTER TABLE `ploopi_annotation` CHANGE `id_module_type` `id_module_type` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_user_action_log` CHANGE `id_record` `id_record` char(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `ip` = '' WHERE ISNULL(`ip`);
ALTER TABLE `ploopi_user_action_log` CHANGE `ip` `ip` char(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `user` = '' WHERE ISNULL(`user`);
ALTER TABLE `ploopi_user_action_log` CHANGE `user` `user` char(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `workspace` = '' WHERE ISNULL(`workspace`);
ALTER TABLE `ploopi_user_action_log` CHANGE `workspace` `workspace` char(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `action` = '' WHERE ISNULL(`action`);
ALTER TABLE `ploopi_user_action_log` CHANGE `action` `action` char(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `module_type` = '' WHERE ISNULL(`module_type`);
ALTER TABLE `ploopi_user_action_log` CHANGE `module_type` `module_type` char(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user_action_log` SET `module` = '' WHERE ISNULL(`module`);
ALTER TABLE `ploopi_user_action_log` CHANGE `module` `module` char(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_workspace` SET `value` = '' WHERE ISNULL(`value`);
ALTER TABLE `ploopi_param_workspace` CHANGE `value` `value` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_index_phonetic_element` SET `id_element` = 0  WHERE ISNULL(`id_element`);
ALTER TABLE `ploopi_index_phonetic_element` CHANGE `id_element` `id_element` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_index_phonetic_element` SET `phonetic` = '' WHERE ISNULL(`phonetic`);
ALTER TABLE `ploopi_index_phonetic_element` CHANGE `phonetic` `phonetic` char(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_session` SET `id` = '' WHERE ISNULL(`id`);
ALTER TABLE `ploopi_session` CHANGE `id` `id` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_session` SET `access` = 0  WHERE ISNULL(`access`);
ALTER TABLE `ploopi_session` CHANGE `access` `access` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_session` SET `data` = '' WHERE ISNULL(`data`);
ALTER TABLE `ploopi_session` CHANGE `data` `data` longblob NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_choice` SET `displayed_value` = '' WHERE ISNULL(`displayed_value`);
ALTER TABLE `ploopi_param_choice` CHANGE `displayed_value` `displayed_value` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_workspace` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_workspace` SET `code` = '' WHERE ISNULL(`code`);
ALTER TABLE `ploopi_workspace` CHANGE `code` `code` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_workspace` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `iprules` = '' WHERE ISNULL(`iprules`);
ALTER TABLE `ploopi_workspace` CHANGE `iprules` `iprules` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `macrules` = '' WHERE ISNULL(`macrules`);
ALTER TABLE `ploopi_workspace` CHANGE `macrules` `macrules` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `template` = '' WHERE ISNULL(`template`);
ALTER TABLE `ploopi_workspace` CHANGE `template` `template` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `mustdefinerule` = 0  WHERE ISNULL(`mustdefinerule`);
ALTER TABLE `ploopi_workspace` CHANGE `mustdefinerule` `mustdefinerule` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_workspace` SET `backoffice` = 0  WHERE ISNULL(`backoffice`);
ALTER TABLE `ploopi_workspace` CHANGE `backoffice` `backoffice` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_workspace` SET `frontoffice` = 0  WHERE ISNULL(`frontoffice`);
ALTER TABLE `ploopi_workspace` CHANGE `frontoffice` `frontoffice` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_workspace` SET `backoffice_domainlist` = '' WHERE ISNULL(`backoffice_domainlist`);
ALTER TABLE `ploopi_workspace` CHANGE `backoffice_domainlist` `backoffice_domainlist` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `meta_description` = '' WHERE ISNULL(`meta_description`);
ALTER TABLE `ploopi_workspace` CHANGE `meta_description` `meta_description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `meta_keywords` = '' WHERE ISNULL(`meta_keywords`);
ALTER TABLE `ploopi_workspace` CHANGE `meta_keywords` `meta_keywords` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace` SET `frontoffice_domainlist` = '' WHERE ISNULL(`frontoffice_domainlist`);
ALTER TABLE `ploopi_workspace` CHANGE `frontoffice_domainlist` `frontoffice_domainlist` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_default` SET `value` = '' WHERE ISNULL(`value`);
ALTER TABLE `ploopi_param_default` CHANGE `value` `value` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_index_stem_element` SET `id_element` = 0  WHERE ISNULL(`id_element`);
ALTER TABLE `ploopi_index_stem_element` CHANGE `id_element` `id_element` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_index_stem_element` SET `stem` = '' WHERE ISNULL(`stem`);
ALTER TABLE `ploopi_index_stem_element` CHANGE `stem` `stem` char(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `ip` = '' WHERE ISNULL(`ip`);
ALTER TABLE `ploopi_connecteduser` CHANGE `ip` `ip` char(15) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `domain` = '' WHERE ISNULL(`domain`);
ALTER TABLE `ploopi_connecteduser` CHANGE `domain` `domain` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `user_id` = 0  WHERE ISNULL(`user_id`);
ALTER TABLE `ploopi_connecteduser` CHANGE `user_id` `user_id` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `workspace_id` = 0  WHERE ISNULL(`workspace_id`);
ALTER TABLE `ploopi_connecteduser` CHANGE `workspace_id` `workspace_id` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `module_id` = 0  WHERE ISNULL(`module_id`);
ALTER TABLE `ploopi_connecteduser` CHANGE `module_id` `module_id` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_connecteduser` SET `timestp` = 0  WHERE ISNULL(`timestp`);
ALTER TABLE `ploopi_connecteduser` CHANGE `timestp` `timestp` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mb_field` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mb_field` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_field` SET `type` = '' WHERE ISNULL(`type`);
ALTER TABLE `ploopi_mb_field` CHANGE `type` `type` varchar(50) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_field` SET `visible` = 0  WHERE ISNULL(`visible`);
ALTER TABLE `ploopi_mb_field` CHANGE `visible` `visible` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_param_user` SET `value` = '' WHERE ISNULL(`value`);
ALTER TABLE `ploopi_param_user` CHANGE `value` `value` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `md5id` = '' WHERE ISNULL(`md5id`);
ALTER TABLE `ploopi_documents_folder` CHANGE `md5id` `md5id` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_documents_folder` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_documents_folder` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_documents_folder` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_documents_folder` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `timestp_modify` = 0  WHERE ISNULL(`timestp_modify`);
ALTER TABLE `ploopi_documents_folder` CHANGE `timestp_modify` `timestp_modify` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_folder` = 0  WHERE ISNULL(`id_folder`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_folder` `id_folder` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_user_modify` = 0  WHERE ISNULL(`id_user_modify`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_user_modify` `id_user_modify` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_folder` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_documents_folder` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `lastname` = '' WHERE ISNULL(`lastname`);
ALTER TABLE `ploopi_user` CHANGE `lastname` `lastname` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `firstname` = '' WHERE ISNULL(`firstname`);
ALTER TABLE `ploopi_user` CHANGE `firstname` `firstname` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `login` = '' WHERE ISNULL(`login`);
ALTER TABLE `ploopi_user` CHANGE `login` `login` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `password` = '' WHERE ISNULL(`password`);
ALTER TABLE `ploopi_user` CHANGE `password` `password` varchar(128) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_user` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `phone` = '' WHERE ISNULL(`phone`);
ALTER TABLE `ploopi_user` CHANGE `phone` `phone` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `fax` = '' WHERE ISNULL(`fax`);
ALTER TABLE `ploopi_user` CHANGE `fax` `fax` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `comments` = '' WHERE ISNULL(`comments`);
ALTER TABLE `ploopi_user` CHANGE `comments` `comments` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `address` = '' WHERE ISNULL(`address`);
ALTER TABLE `ploopi_user` CHANGE `address` `address` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `mobile` = '' WHERE ISNULL(`mobile`);
ALTER TABLE `ploopi_user` CHANGE `mobile` `mobile` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `entity` = '' WHERE ISNULL(`entity`);
ALTER TABLE `ploopi_user` CHANGE `entity` `entity` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `service` = '' WHERE ISNULL(`service`);
ALTER TABLE `ploopi_user` CHANGE `service` `service` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `service2` = '' WHERE ISNULL(`service2`);
ALTER TABLE `ploopi_user` CHANGE `service2` `service2` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `function` = '' WHERE ISNULL(`function`);
ALTER TABLE `ploopi_user` CHANGE `function` `function` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `number` = '' WHERE ISNULL(`number`);
ALTER TABLE `ploopi_user` CHANGE `number` `number` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `postalcode` = '' WHERE ISNULL(`postalcode`);
ALTER TABLE `ploopi_user` CHANGE `postalcode` `postalcode` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `city` = '' WHERE ISNULL(`city`);
ALTER TABLE `ploopi_user` CHANGE `city` `city` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `country` = '' WHERE ISNULL(`country`);
ALTER TABLE `ploopi_user` CHANGE `country` `country` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `timezone` = '' WHERE ISNULL(`timezone`);
ALTER TABLE `ploopi_user` CHANGE `timezone` `timezone` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `building` = '' WHERE ISNULL(`building`);
ALTER TABLE `ploopi_user` CHANGE `building` `building` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `floor` = '' WHERE ISNULL(`floor`);
ALTER TABLE `ploopi_user` CHANGE `floor` `floor` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `office` = '' WHERE ISNULL(`office`);
ALTER TABLE `ploopi_user` CHANGE `office` `office` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `civility` = '' WHERE ISNULL(`civility`);
ALTER TABLE `ploopi_user` CHANGE `civility` `civility` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_user` SET `rank` = '' WHERE ISNULL(`rank`);
ALTER TABLE `ploopi_user` CHANGE `rank` `rank` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `id` = 0  WHERE ISNULL(`id`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `id` `id` int(11) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `script` = '' WHERE ISNULL(`script`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `script` `script` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `select_id` = '' WHERE ISNULL(`select_id`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `select_id` `select_id` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `select_label` = '' WHERE ISNULL(`select_label`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `select_label` `select_label` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_wce_object` SET `select_table` = '' WHERE ISNULL(`select_table`);
ALTER TABLE `ploopi_mb_wce_object` CHANGE `select_table` `select_table` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_table` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mb_table` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_table` SET `visible` = 0  WHERE ISNULL(`visible`);
ALTER TABLE `ploopi_mb_table` CHANGE `visible` `visible` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_subscription_action` SET `id_subscription` = '' WHERE ISNULL(`id_subscription`);
ALTER TABLE `ploopi_subscription_action` CHANGE `id_subscription` `id_subscription` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_ext` SET `filetype` = '' WHERE ISNULL(`filetype`);
ALTER TABLE `ploopi_documents_ext` CHANGE `filetype` `filetype` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_subscription` SET `id` = '' WHERE ISNULL(`id`);
ALTER TABLE `ploopi_subscription` CHANGE `id` `id` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_subscription` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_subscription` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_share` SET `type_share` = '' WHERE ISNULL(`type_share`);
ALTER TABLE `ploopi_share` CHANGE `type_share` `type_share` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_share` SET `id_share` = 0  WHERE ISNULL(`id_share`);
ALTER TABLE `ploopi_share` CHANGE `id_share` `id_share` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_share` SET `id_module_type` = 0  WHERE ISNULL(`id_module_type`);
ALTER TABLE `ploopi_share` CHANGE `id_module_type` `id_module_type` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `md5id` = '' WHERE ISNULL(`md5id`);
ALTER TABLE `ploopi_documents_file` CHANGE `md5id` `md5id` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_documents_file` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_documents_file` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_documents_file` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `ref` = '' WHERE ISNULL(`ref`);
ALTER TABLE `ploopi_documents_file` CHANGE `ref` `ref` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `timestp_file` = 0  WHERE ISNULL(`timestp_file`);
ALTER TABLE `ploopi_documents_file` CHANGE `timestp_file` `timestp_file` bigint(14) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `timestp_create` = 0  WHERE ISNULL(`timestp_create`);
ALTER TABLE `ploopi_documents_file` CHANGE `timestp_create` `timestp_create` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `timestp_modify` = 0  WHERE ISNULL(`timestp_modify`);
ALTER TABLE `ploopi_documents_file` CHANGE `timestp_modify` `timestp_modify` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `size` = 0  WHERE ISNULL(`size`);
ALTER TABLE `ploopi_documents_file` CHANGE `size` `size` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `extension` = '' WHERE ISNULL(`extension`);
ALTER TABLE `ploopi_documents_file` CHANGE `extension` `extension` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_documents_file` CHANGE `parents` `parents` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_documents_file` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `nbclick` = 0  WHERE ISNULL(`nbclick`);
ALTER TABLE `ploopi_documents_file` CHANGE `nbclick` `nbclick` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_folder` = 0  WHERE ISNULL(`id_folder`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_folder` `id_folder` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_user_modify` = 0  WHERE ISNULL(`id_user_modify`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_user_modify` `id_user_modify` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_documents_file` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_documents_file` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_ticket_status` SET `timestp` = 0  WHERE ISNULL(`timestp`);
ALTER TABLE `ploopi_ticket_status` CHANGE `timestp` `timestp` bigint(14) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_workspace_user` SET `adminlevel` = 0  WHERE ISNULL(`adminlevel`);
ALTER TABLE `ploopi_workspace_user` CHANGE `adminlevel` `adminlevel` tinyint(3) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_ticket` SET `title` = '' WHERE ISNULL(`title`);
ALTER TABLE `ploopi_ticket` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_ticket` SET `message` = '' WHERE ISNULL(`message`);
ALTER TABLE `ploopi_ticket` CHANGE `message` `message` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_ticket` SET `id_object` = 0  WHERE ISNULL(`id_object`);
ALTER TABLE `ploopi_ticket` CHANGE `id_object` `id_object` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_ticket` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_ticket` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_ticket` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_ticket` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_ticket` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_ticket` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_group` SET `id_group` = 0  WHERE ISNULL(`id_group`);
ALTER TABLE `ploopi_group` CHANGE `id_group` `id_group` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_group` SET `protected` = 0  WHERE ISNULL(`protected`);
ALTER TABLE `ploopi_group` CHANGE `protected` `protected` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_group` SET `parents` = '' WHERE ISNULL(`parents`);
ALTER TABLE `ploopi_group` CHANGE `parents` `parents` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_type` SET `default_value` = '' WHERE ISNULL(`default_value`);
ALTER TABLE `ploopi_param_type` CHANGE `default_value` `default_value` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_type` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_param_type` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_param_type` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_param_type` CHANGE `label` `label` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_object` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mb_object` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_object` SET `script` = '' WHERE ISNULL(`script`);
ALTER TABLE `ploopi_mb_object` CHANGE `script` `script` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_confirmation_code` SET `action` = '' WHERE ISNULL(`action`);
ALTER TABLE `ploopi_confirmation_code` CHANGE `action` `action` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_confirmation_code` SET `code` = '' WHERE ISNULL(`code`);
ALTER TABLE `ploopi_confirmation_code` CHANGE `code` `code` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_workspace_group` SET `adminlevel` = 0  WHERE ISNULL(`adminlevel`);
ALTER TABLE `ploopi_workspace_group` CHANGE `adminlevel` `adminlevel` tinyint(3) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mb_relation` SET `tablesrc` = '' WHERE ISNULL(`tablesrc`);
ALTER TABLE `ploopi_mb_relation` CHANGE `tablesrc` `tablesrc` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_relation` SET `fieldsrc` = '' WHERE ISNULL(`fieldsrc`);
ALTER TABLE `ploopi_mb_relation` CHANGE `fieldsrc` `fieldsrc` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_relation` SET `tabledest` = '' WHERE ISNULL(`tabledest`);
ALTER TABLE `ploopi_mb_relation` CHANGE `tabledest` `tabledest` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_relation` SET `fielddest` = '' WHERE ISNULL(`fielddest`);
ALTER TABLE `ploopi_mb_relation` CHANGE `fielddest` `fielddest` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_role` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_role` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_role` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_role` CHANGE `id_workspace` `id_workspace` int(10) NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_role` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_role` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_role` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_role` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_module_type` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_module_type` CHANGE `label` `label` varchar(100) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_module_type` SET `publicparam` = 0  WHERE ISNULL(`publicparam`);
ALTER TABLE `ploopi_module_type` CHANGE `publicparam` `publicparam` tinyint(1) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_module_type` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_module_type` CHANGE `description` `description` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_module_type` SET `version` = '' WHERE ISNULL(`version`);
ALTER TABLE `ploopi_module_type` CHANGE `version` `version` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_module_type` SET `author` = '' WHERE ISNULL(`author`);
ALTER TABLE `ploopi_module_type` CHANGE `author` `author` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_captcha` SET `id` = '' WHERE ISNULL(`id`);
ALTER TABLE `ploopi_captcha` CHANGE `id` `id` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_captcha` SET `cptuse` = 0  WHERE ISNULL(`cptuse`);
ALTER TABLE `ploopi_captcha` CHANGE `cptuse` `cptuse` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_captcha` SET `codesound` = '' WHERE ISNULL(`codesound`);
ALTER TABLE `ploopi_captcha` CHANGE `codesound` `codesound` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_captcha` SET `code` = '' WHERE ISNULL(`code`);
ALTER TABLE `ploopi_captcha` CHANGE `code` `code` varchar(20) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_captcha` SET `time` = 0  WHERE ISNULL(`time`);
ALTER TABLE `ploopi_captcha` CHANGE `time` `time` int(20) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_index_element` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_index_element` CHANGE `id_record` `id_record` char(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_index_element` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_index_element` CHANGE `label` `label` char(128) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mimetype` SET `ext` = '' WHERE ISNULL(`ext`);
ALTER TABLE `ploopi_mimetype` CHANGE `ext` `ext` varchar(10) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mimetype` SET `mimetype` = '' WHERE ISNULL(`mimetype`);
ALTER TABLE `ploopi_mimetype` CHANGE `mimetype` `mimetype` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mimetype` SET `filetype` = '' WHERE ISNULL(`filetype`);
ALTER TABLE `ploopi_mimetype` CHANGE `filetype` `filetype` varchar(50) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mimetype` SET `group` = '' WHERE ISNULL(`group`);
ALTER TABLE `ploopi_mimetype` CHANGE `group` `group` varchar(30) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_serializedvar` SET `id` = '' WHERE ISNULL(`id`);
ALTER TABLE `ploopi_serializedvar` CHANGE `id` `id` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_serializedvar` SET `id_session` = '' WHERE ISNULL(`id_session`);
ALTER TABLE `ploopi_serializedvar` CHANGE `id_session` `id_session` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_serializedvar` SET `data` = '' WHERE ISNULL(`data`);
ALTER TABLE `ploopi_serializedvar` CHANGE `data` `data` longblob NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_action` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mb_action` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_action` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mb_action` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mb_action` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mb_action` CHANGE `id_workspace` `id_workspace` int(10) NOT NULL DEFAULT 0  COMMENT '' ;

UPDATE `ploopi_module_type` SET `version` = '1.9.7.4', `author` = 'Ovensia', `date` = '20200311000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
