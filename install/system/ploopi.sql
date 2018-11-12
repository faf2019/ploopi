DROP TABLE IF EXISTS ploopi_annotation;
CREATE TABLE IF NOT EXISTS ploopi_annotation (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL DEFAULT '',
  content longtext,
  object_label varchar(255) NOT NULL DEFAULT '',
  type_annotation varchar(16) DEFAULT NULL,
  date_annotation varchar(14) DEFAULT NULL,
  private tinyint(1) unsigned NOT NULL DEFAULT '1',
  id_record varchar(255) DEFAULT NULL,
  id_object int(10) unsigned DEFAULT '0',
  id_user int(10) unsigned DEFAULT '0',
  id_workspace int(10) DEFAULT NULL,
  id_element char(32) NOT NULL DEFAULT '0',
  id_module int(10) unsigned DEFAULT '0',
  id_module_type int(10) DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_record (id_record),
  KEY id_object (id_object),
  KEY id_user (id_user),
  KEY id_workspace (id_workspace),
  KEY id_module (id_module),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS ploopi_annotation_tag;
CREATE TABLE IF NOT EXISTS ploopi_annotation_tag (
  id_annotation int(10) unsigned NOT NULL DEFAULT '0',
  id_tag int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_annotation,id_tag),
  KEY id_tag (id_tag)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_captcha;
CREATE TABLE IF NOT EXISTS ploopi_captcha (
  id varchar(255) NOT NULL,
  cptuse int(10) unsigned NOT NULL,
  codesound varchar(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `time` int(20) unsigned NOT NULL,
  KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_confirmation_code;
CREATE TABLE IF NOT EXISTS ploopi_confirmation_code (
  `action` varchar(255) NOT NULL,
  timestp bigint(14) unsigned NOT NULL DEFAULT '0',
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_connecteduser;
CREATE TABLE IF NOT EXISTS ploopi_connecteduser (
  sid char(32) NOT NULL DEFAULT '0',
  ip char(15) DEFAULT NULL,
  domain varchar(255) NOT NULL,
  user_id int(10) unsigned DEFAULT '0',
  workspace_id int(10) DEFAULT NULL,
  module_id int(10) unsigned DEFAULT '0',
  timestp bigint(14) DEFAULT '0',
  PRIMARY KEY (sid),
  KEY workspace_id (workspace_id),
  KEY user_id (user_id),
  KEY module_id (module_id),
  KEY timestp (timestp)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_documents_ext;
CREATE TABLE IF NOT EXISTS ploopi_documents_ext (
  ext varchar(10) NOT NULL DEFAULT '',
  filetype varchar(16) DEFAULT NULL,
  PRIMARY KEY (ext),
  KEY filetype (filetype)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_documents_ext (ext, filetype) VALUES
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

DROP TABLE IF EXISTS ploopi_documents_file;
CREATE TABLE IF NOT EXISTS ploopi_documents_file (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  md5id varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  label varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  ref varchar(255) NOT NULL,
  timestp_file bigint(14) unsigned NOT NULL,
  timestp_create bigint(14) DEFAULT NULL,
  timestp_modify bigint(14) DEFAULT NULL,
  size int(10) unsigned DEFAULT '0',
  extension varchar(20) DEFAULT NULL,
  parents varchar(255) DEFAULT NULL,
  content longtext NOT NULL,
  nbclick int(10) unsigned DEFAULT '0',
  id_folder int(10) unsigned DEFAULT '0',
  id_user_modify int(10) unsigned DEFAULT '0',
  id_user int(10) unsigned DEFAULT '0',
  id_workspace int(10) unsigned DEFAULT '0',
  id_module int(10) unsigned DEFAULT '0',
  id_record varchar(255) NOT NULL,
  id_object int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_user (id_user),
  KEY id_group (id_workspace),
  KEY id_module (id_module),
  KEY `name` (`name`),
  KEY id_folder (id_folder),
  KEY extension (extension),
  KEY md5id (md5id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_documents_folder;
CREATE TABLE IF NOT EXISTS ploopi_documents_folder (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  md5id varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  parents varchar(255) DEFAULT '0',
  timestp_create bigint(14) DEFAULT NULL,
  timestp_modify bigint(14) DEFAULT NULL,
  nbelements int(10) unsigned NOT NULL DEFAULT '0',
  system tinyint(1) unsigned NOT NULL DEFAULT '0',
  id_folder int(10) unsigned DEFAULT '0',
  id_user_modify int(10) unsigned DEFAULT '0',
  id_user int(10) unsigned DEFAULT '0',
  id_workspace int(10) unsigned DEFAULT '0',
  id_module int(10) unsigned DEFAULT '0',
  id_record varchar(255) NOT NULL,
  id_object int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_user (id_user),
  KEY id_group (id_workspace),
  KEY id_module (id_module),
  KEY id_folder (id_folder),
  KEY md5id (md5id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_group;
CREATE TABLE IF NOT EXISTS ploopi_group (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_group int(10) unsigned DEFAULT '0',
  label varchar(255) NOT NULL DEFAULT '',
  system tinyint(1) unsigned NOT NULL DEFAULT '0',
  protected tinyint(1) unsigned DEFAULT '0',
  parents varchar(100) DEFAULT NULL,
  depth int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) unsigned NOT NULL DEFAULT '0',
  shared tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_workspace (id_workspace),
  KEY shared (shared),
  KEY system (system),
  KEY protected (protected),
  KEY parents (parents)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO ploopi_group (id, id_group, `label`, system, protected, parents, depth, id_workspace, shared) VALUES
(1, 0, 'system', 1, 1, '0', 1, 0, 0),
(3, 1, 'Groupe Principal', 0, 1, '0;1', 2, 1, 1);

DROP TABLE IF EXISTS ploopi_group_user;
CREATE TABLE IF NOT EXISTS ploopi_group_user (
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  id_group int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_group,id_user),
  KEY id_user (id_user),
  KEY id_group (id_group)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_group_user (id_user, id_group) VALUES
(2, 3);

DROP TABLE IF EXISTS ploopi_index_element;
CREATE TABLE IF NOT EXISTS ploopi_index_element (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_record char(64) NOT NULL,
  id_object smallint(5) unsigned NOT NULL DEFAULT '0',
  label char(128) NOT NULL,
  timestp_create bigint(14) unsigned NOT NULL DEFAULT '0',
  timestp_modify bigint(14) unsigned NOT NULL DEFAULT '0',
  timestp_lastindex bigint(14) unsigned NOT NULL DEFAULT '0',
  id_user smallint(5) unsigned NOT NULL DEFAULT '0',
  id_workspace smallint(5) unsigned NOT NULL DEFAULT '0',
  id_module smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_module (id_module),
  KEY id_workspace (id_workspace),
  KEY id_user (id_user),
  KEY id_record (id_record),
  KEY id_object (id_object),
  KEY timestp_create (timestp_create),
  KEY timestp_modify (timestp_modify),
  KEY timestp_lastindex (timestp_lastindex)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_index_keyword_element;
CREATE TABLE IF NOT EXISTS ploopi_index_keyword_element (
  id_element int(10) unsigned NOT NULL,
  keyword char(20) NOT NULL,
  weight mediumint(10) unsigned NOT NULL DEFAULT '0',
  ratio float unsigned NOT NULL DEFAULT '0',
  relevance tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY id_element (id_element),
  KEY keyword (keyword)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;


DROP TABLE IF EXISTS ploopi_index_phonetic_element;
CREATE TABLE IF NOT EXISTS ploopi_index_phonetic_element (
  id_element int(10) unsigned NOT NULL,
  phonetic char(20) NOT NULL,
  weight mediumint(10) unsigned NOT NULL DEFAULT '0',
  ratio float unsigned NOT NULL DEFAULT '0',
  relevance tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY id_element (id_element),
  KEY phonetic (phonetic)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;


DROP TABLE IF EXISTS ploopi_index_stem_element;
CREATE TABLE IF NOT EXISTS ploopi_index_stem_element (
  id_element int(10) unsigned NOT NULL,
  stem char(20) NOT NULL,
  weight mediumint(10) unsigned NOT NULL DEFAULT '0',
  ratio float unsigned NOT NULL DEFAULT '0',
  relevance tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY id_element (id_element),
  KEY stem (stem)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 PACK_KEYS=1;


DROP TABLE IF EXISTS ploopi_log;
CREATE TABLE IF NOT EXISTS ploopi_log (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  request_method varchar(255) DEFAULT NULL,
  query_string varchar(255) DEFAULT NULL,
  remote_addr varchar(64) DEFAULT NULL,
  remote_port int(10) unsigned DEFAULT NULL,
  script_filename varchar(255) DEFAULT NULL,
  path_translated varchar(255) DEFAULT NULL,
  script_name varchar(255) DEFAULT NULL,
  request_uri varchar(255) DEFAULT NULL,
  ploopi_userid int(10) unsigned NOT NULL DEFAULT '0',
  ploopi_workspaceid int(10) unsigned NOT NULL DEFAULT '0',
  ploopi_moduleid int(10) unsigned NOT NULL DEFAULT '0',
  browser varchar(64) DEFAULT NULL,
  system varchar(64) DEFAULT NULL,
  ts bigint(14) unsigned NOT NULL DEFAULT '0',
  total_exec_time int(10) unsigned DEFAULT '0',
  sql_exec_time int(10) unsigned DEFAULT '0',
  sql_percent_time int(10) unsigned DEFAULT '0',
  php_percent_time int(10) unsigned DEFAULT '0',
  numqueries int(10) unsigned DEFAULT '0',
  page_size int(10) unsigned DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_mb_action;
CREATE TABLE IF NOT EXISTS ploopi_mb_action (
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  id_action int(10) unsigned NOT NULL DEFAULT '0',
  label varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  id_workspace int(10) DEFAULT NULL,
  id_object int(10) unsigned NOT NULL DEFAULT '0',
  role_enabled tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (id_action,id_module_type),
  KEY id_workspace (id_workspace),
  KEY id_object (id_object),
  KEY role_enabled (role_enabled)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_action (id_module_type, id_action, `label`, description, id_workspace, id_object, role_enabled) VALUES
(1, 1, 'Installer un Module', NULL, 0, 0, 1),
(1, 2, 'Désinstaller un Module', NULL, 0, 0, 1),
(1, 3, 'Modifier les Paramètres d''un Module', NULL, 0, 0, 1),
(1, 4, 'Instancier / Utiliser un Module', NULL, 0, 0, 1),
(1, 5, 'Modifier les Propriétés d''un Module', NULL, 0, 0, 1),
(1, 6, 'Modifier la Page d''Accueil', NULL, 0, 0, 1),
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

DROP TABLE IF EXISTS ploopi_mb_field;
CREATE TABLE IF NOT EXISTS ploopi_mb_field (
  tablename varchar(100) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  label varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  visible tinyint(1) unsigned DEFAULT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tablename,`name`),
  KEY visible (visible),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_field (tablename, `name`, `label`, `type`, visible, id_module_type) VALUES
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

DROP TABLE IF EXISTS ploopi_mb_object;
CREATE TABLE IF NOT EXISTS ploopi_mb_object (
  id int(10) unsigned NOT NULL DEFAULT '0',
  label varchar(255) DEFAULT NULL,
  script varchar(255) DEFAULT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id,id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_object (id, `label`, script, id_module_type) VALUES
(2, 'Groupe d''Utilisateur', 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=org&groupid=<IDRECORD>', 1),
(1, 'Espace de Travail', 'ploopi_workspaceid=<IDWORKSPACE>&ploopi_moduleid=1&ploopi_action=admin&system_level=work&workspaceid=<IDRECORD>', 1);

DROP TABLE IF EXISTS ploopi_mb_relation;
CREATE TABLE IF NOT EXISTS ploopi_mb_relation (
  tablesrc varchar(100) DEFAULT NULL,
  fieldsrc varchar(100) DEFAULT NULL,
  tabledest varchar(100) DEFAULT NULL,
  fielddest varchar(100) DEFAULT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  KEY tablesrc (tablesrc),
  KEY fieldsrc (fieldsrc),
  KEY tabledest (tabledest),
  KEY fielddest (fielddest),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_relation (tablesrc, fieldsrc, tabledest, fielddest, id_module_type) VALUES
('ploopi_group', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_group', 'id_group', 'ploopi_group', 'id', 1),
('ploopi_workspace', 'id_workspace', 'ploopi_workspace', 'id', 1),
('ploopi_module', 'id_workspace', 'ploopi_workspace', 'id', 1);

DROP TABLE IF EXISTS ploopi_mb_schema;
CREATE TABLE IF NOT EXISTS ploopi_mb_schema (
  tablesrc varchar(100) NOT NULL DEFAULT '',
  tabledest varchar(100) NOT NULL DEFAULT '',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tabledest,tablesrc),
  KEY tablesrc (tablesrc),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_schema (tablesrc, tabledest, id_module_type) VALUES
('ploopi_group', 'ploopi_workspace', 1),
('ploopi_workspace', 'ploopi_workspace', 1),
('ploopi_group', 'ploopi_group', 1),
('ploopi_module', 'ploopi_workspace', 1);

DROP TABLE IF EXISTS ploopi_mb_table;
CREATE TABLE IF NOT EXISTS ploopi_mb_table (
  `name` varchar(100) NOT NULL DEFAULT '',
  label varchar(255) DEFAULT NULL,
  visible tinyint(1) unsigned DEFAULT '1',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_table (`name`, `label`, visible, id_module_type) VALUES
('ploopi_group', 'group', 1, 1),
('ploopi_workspace', 'workspace', 1, 1),
('ploopi_user', 'user', 1, 1),
('ploopi_module', 'module', 1, 1);

DROP TABLE IF EXISTS ploopi_mb_wce_object;
CREATE TABLE IF NOT EXISTS ploopi_mb_wce_object (
  id int(11) unsigned NOT NULL,
  label varchar(255) DEFAULT NULL,
  id_module_type int(10) NOT NULL DEFAULT '0',
  script varchar(255) DEFAULT NULL,
  select_id varchar(64) DEFAULT NULL,
  select_label varchar(64) DEFAULT NULL,
  select_table varchar(64) DEFAULT NULL,
  PRIMARY KEY (id,id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mb_wce_object (id, `label`, id_module_type, script, select_id, select_label, select_table) VALUES
(1, 'Affichage Trombinscope', 1, '?object=''display''', NULL, NULL, NULL);

DROP TABLE IF EXISTS ploopi_mimetype;
CREATE TABLE IF NOT EXISTS ploopi_mimetype (
  ext varchar(10) NOT NULL,
  mimetype varchar(255) NOT NULL,
  filetype varchar(50) NOT NULL,
  `group` varchar(30) NOT NULL,
  PRIMARY KEY (ext)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_mimetype (ext, mimetype, filetype, `group`) VALUES
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

DROP TABLE IF EXISTS ploopi_module;
CREATE TABLE IF NOT EXISTS ploopi_module (
  id int(10) NOT NULL AUTO_INCREMENT,
  label varchar(100) NOT NULL DEFAULT '',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) DEFAULT NULL,
  active tinyint(1) unsigned NOT NULL DEFAULT '0',
  visible tinyint(1) unsigned NOT NULL DEFAULT '0',
  public tinyint(1) unsigned DEFAULT '0',
  shared tinyint(1) unsigned DEFAULT '0',
  herited tinyint(1) unsigned DEFAULT '0',
  adminrestricted tinyint(1) unsigned DEFAULT '0',
  viewmode int(10) unsigned DEFAULT '1',
  transverseview tinyint(1) unsigned DEFAULT '0',
  autoconnect tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_module_type (id_module_type),
  KEY id_workspace (id_workspace),
  KEY active (active),
  KEY shared (shared),
  KEY herited (herited)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO ploopi_module (id, `label`, id_module_type, id_workspace, active, visible, public, shared, herited, adminrestricted, viewmode, transverseview, autoconnect) VALUES
(1, 'Système', 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0),
(-1, 'Recherche', 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0);

DROP TABLE IF EXISTS ploopi_module_type;
CREATE TABLE IF NOT EXISTS ploopi_module_type (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  label varchar(100) DEFAULT NULL,
  system tinyint(1) unsigned NOT NULL DEFAULT '0',
  publicparam tinyint(1) unsigned DEFAULT '0',
  description longtext,
  version varchar(32) DEFAULT NULL,
  author varchar(255) DEFAULT NULL,
  `date` varchar(14) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY label (label)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO ploopi_module_type (id, `label`, system, publicparam, description, version, author, `date`) VALUES
(1, 'system', 1, 0, 'Noyau du système', '1.9.5.1', 'Ovensia', '20150616000000');

DROP TABLE IF EXISTS ploopi_module_workspace;
CREATE TABLE IF NOT EXISTS ploopi_module_workspace (
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) NOT NULL DEFAULT '0',
  position tinyint(2) NOT NULL DEFAULT '0',
  blockposition char(10) NOT NULL DEFAULT 'left',
  PRIMARY KEY (id_workspace,id_module),
  KEY id_module (id_module),
  KEY id_workspace (id_workspace)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_param_choice;
CREATE TABLE IF NOT EXISTS ploopi_param_choice (
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  displayed_value varchar(100) DEFAULT NULL,
  PRIMARY KEY (id_module_type,`name`,`value`),
  KEY `name` (`name`),
  KEY `value` (`value`),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_param_choice (id_module_type, `name`, `value`, displayed_value) VALUES
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

DROP TABLE IF EXISTS ploopi_param_default;
CREATE TABLE IF NOT EXISTS ploopi_param_default (
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_module,`name`),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_param_default (id_module, `name`, `value`, id_module_type) VALUES
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
(1, 'system_profile_edit_allowed', '1', 1);

DROP TABLE IF EXISTS ploopi_param_type;
CREATE TABLE IF NOT EXISTS ploopi_param_type (
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  default_value text NOT NULL,
  public tinyint(1) unsigned NOT NULL DEFAULT '0',
  description longtext,
  label varchar(100) DEFAULT NULL,
  PRIMARY KEY (id_module_type,`name`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_param_type (id_module_type, `name`, default_value, public, description, `label`) VALUES
(1, 'system_generate_htpasswd', '1', 0, '', 'Générer un fichier htpasswd'),
(1, 'system_language', '', 1, '', 'Langue du système'),
(1, 'system_jodwebservice', '', 0, '', 'URL du webservice JODConverter'),
(1, 'system_focus_popup', '0', 0, '', 'Activer le Focus sur les Popups'),
(1, 'system_search_displaymodule', '0', 0, '', 'Afficher la colonne "Module" dans la recherche'),
(1, 'system_search_displayindexed', '0', 0, '', 'Afficher la colonne "Indexé le" dans la recherche'),
(1, 'system_search_displayworkspace', '0', 0, '', 'Afficher la colonne "Espace" dans la recherche'),
(1, 'system_search_displayuser', '0', 0, '', 'Afficher la colonne "Utilisateur" dans la recherche'),
(1, 'system_search_displaydatetime', '0', 0, '', 'Afficher la colonne "Ajouté le" dans la recherche'),
(1, 'system_search_displayobjecttype', '0', 0, '', 'Afficher la colonne "Type d''Objet" dans la recherche'),
(1, 'system_submenu_display', '1', 0, NULL, 'Afficher les sous-menus de (Mon Espace)'),
(1, 'system_unoconv', '', 0, '', 'Chemin vers UNOCONV'),
(1, 'system_user_required_fields', 'email,phone,service,function,city', 0, NULL, 'Champs requis dans le profil utilisateur'),
(1, 'system_password_force_update', '0', 0, NULL, 'Forcer le changement de mot de passe lors de la prochaine connexion'),
(1, 'system_password_validity', '0', 0, NULL, 'Durée de validité du mot de passe en jours'),
(1, 'system_profile_edit_allowed', '1', 0, NULL, 'L''utilisateur peut modifier son profil');

DROP TABLE IF EXISTS ploopi_param_user;
CREATE TABLE IF NOT EXISTS ploopi_param_user (
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_module,`name`,id_user),
  KEY id_module_type (id_module_type),
  KEY id_user (id_user),
  KEY `name` (`name`),
  KEY id_module (id_module)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_param_workspace;
CREATE TABLE IF NOT EXISTS ploopi_param_workspace (
  id_module int(10) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL DEFAULT '',
  id_workspace int(10) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_module,`name`,id_workspace),
  KEY id_module_type (id_module_type),
  KEY id_module (id_module),
  KEY `name` (`name`),
  KEY id_workspace (id_workspace)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_role;
CREATE TABLE IF NOT EXISTS ploopi_role (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_module int(10) unsigned DEFAULT '0',
  id_workspace int(10) DEFAULT NULL,
  label varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  def tinyint(1) unsigned NOT NULL DEFAULT '0',
  shared tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_module (id_module),
  KEY id_workspace (id_workspace),
  KEY shared (shared)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_role_action;
CREATE TABLE IF NOT EXISTS ploopi_role_action (
  id_role int(10) unsigned NOT NULL DEFAULT '0',
  id_action int(10) unsigned NOT NULL DEFAULT '0',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_action,id_module_type,id_role),
  KEY id_role (id_role),
  KEY id_action (id_action),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_serializedvar;
CREATE TABLE IF NOT EXISTS ploopi_serializedvar (
  id char(32) NOT NULL,
  id_session char(32) NOT NULL,
  `data` longtext,
  PRIMARY KEY (id,id_session),
  KEY id_session (id_session)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_session;
CREATE TABLE IF NOT EXISTS ploopi_session (
  id char(32) NOT NULL,
  access int(10) unsigned DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_share;
CREATE TABLE IF NOT EXISTS ploopi_share (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  id_record int(10) unsigned NOT NULL DEFAULT '0',
  id_object int(10) unsigned NOT NULL DEFAULT '0',
  type_share varchar(16) DEFAULT '0',
  id_share int(10) unsigned DEFAULT '0',
  id_module_type int(10) DEFAULT '0',
  PRIMARY KEY (id),
  KEY search (id_module,id_object,id_record),
  KEY id_module_type (id_module_type),
  KEY id_share (id_share),
  KEY type_share (type_share)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_subscription;
CREATE TABLE IF NOT EXISTS ploopi_subscription (
  id char(32) NOT NULL,
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  id_object int(10) NOT NULL DEFAULT '0',
  id_record varchar(255) NOT NULL,
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  allactions tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_module (id_module),
  KEY id_object (id_object),
  KEY id_user (id_user),
  KEY id_action (allactions)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_subscription_action;
CREATE TABLE IF NOT EXISTS ploopi_subscription_action (
  id_subscription char(32) NOT NULL,
  id_action int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_subscription,id_action),
  KEY id_action (id_action)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_tag;
CREATE TABLE IF NOT EXISTS ploopi_tag (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  tag char(32) NOT NULL,
  tag_clean char(32) NOT NULL,
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_user (id_user),
  KEY tag (tag)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_ticket;
CREATE TABLE IF NOT EXISTS ploopi_ticket (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) DEFAULT NULL,
  message longtext,
  needed_validation tinyint(1) unsigned NOT NULL DEFAULT '0',
  delivery_notification tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  object_label varchar(255) NOT NULL DEFAULT '',
  timestp bigint(14) unsigned NOT NULL DEFAULT '0',
  lastreply_timestp bigint(14) unsigned NOT NULL DEFAULT '0',
  lastedit_timestp bigint(14) unsigned NOT NULL DEFAULT '0',
  count_read int(10) unsigned NOT NULL DEFAULT '0',
  count_replies int(10) unsigned NOT NULL DEFAULT '0',
  id_object int(10) DEFAULT '0',
  id_module int(10) unsigned DEFAULT '0',
  id_record varchar(255) DEFAULT NULL,
  id_user int(10) unsigned DEFAULT '0',
  id_workspace int(10) unsigned NOT NULL DEFAULT '0',
  parent_id int(10) unsigned NOT NULL DEFAULT '0',
  root_id int(10) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_user (id_user),
  KEY id_module_type (id_module_type),
  KEY id_object (id_object),
  KEY id_module (id_module),
  KEY id_workspace (id_workspace),
  KEY parent_id (parent_id),
  KEY root_id (root_id),
  KEY deleted (deleted)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_ticket_dest;
CREATE TABLE IF NOT EXISTS ploopi_ticket_dest (
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  id_ticket int(10) unsigned NOT NULL DEFAULT '0',
  deleted tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id_user,id_ticket),
  KEY id_ticket (id_ticket),
  KEY id_user (id_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_ticket_status;
CREATE TABLE IF NOT EXISTS ploopi_ticket_status (
  id_ticket int(10) unsigned NOT NULL DEFAULT '0',
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  timestp bigint(14) NOT NULL,
  KEY id_ticket (id_ticket),
  KEY id_user (id_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_ticket_watch;
CREATE TABLE IF NOT EXISTS ploopi_ticket_watch (
  id_ticket int(10) unsigned NOT NULL DEFAULT '0',
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  notify tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_ticket,id_user),
  KEY id_ticket (id_ticket),
  KEY id_user (id_user),
  KEY notify (notify)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_user;
CREATE TABLE IF NOT EXISTS ploopi_user (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  lastname varchar(100) NOT NULL,
  firstname varchar(100) NOT NULL,
  login varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  date_creation bigint(14) NOT NULL DEFAULT '0',
  date_expire bigint(14) NOT NULL DEFAULT '0',
  email varchar(255) NOT NULL,
  phone varchar(32) NOT NULL,
  fax varchar(32) NOT NULL,
  comments text NOT NULL,
  address text NOT NULL,
  mobile varchar(32) NOT NULL,
  entity varchar(255) NOT NULL,
  service varchar(255) NOT NULL,
  service2 varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  number varchar(255) NOT NULL,
  postalcode varchar(16) NOT NULL,
  city varchar(255) NOT NULL,
  country varchar(255) NOT NULL,
  ticketsbyemail tinyint(1) unsigned NOT NULL DEFAULT '0',
  servertimezone tinyint(1) unsigned NOT NULL DEFAULT '1',
  color varchar(16) NOT NULL DEFAULT '',
  timezone varchar(64) NOT NULL,
  building varchar(255) NOT NULL,
  floor varchar(255) NOT NULL,
  office varchar(255) NOT NULL,
  civility varchar(16) NOT NULL,
  rank varchar(255) NOT NULL,
  password_force_update tinyint(1) unsigned NOT NULL DEFAULT '0',
  password_validity int(10) unsigned NOT NULL DEFAULT '0',
  password_last_update bigint(14) unsigned NOT NULL DEFAULT '0',
  disabled tinyint(1) unsigned NOT NULL DEFAULT '0',
  failed_attemps int(10) unsigned NOT NULL DEFAULT '0',
  jailed_since bigint(14) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY login_unique (login),
  KEY lastname (lastname),
  KEY firstname (firstname),
  KEY password_last_update (password_last_update),
  KEY disabled (disabled),
  KEY failed_attemps (failed_attemps),
  FULLTEXT KEY ft (lastname,firstname,email,comments,service,service2,`function`,city,building,floor,office,rank)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO ploopi_user (id, lastname, firstname, login, `password`, date_creation, date_expire, email, phone, fax, comments, address, mobile, entity, service, service2, `function`, number, postalcode, city, country, ticketsbyemail, servertimezone, color, timezone, building, floor, office, civility, rank, password_force_update, password_validity, password_last_update, disabled, failed_attemps, jailed_since) VALUES
(2, 'Administrateur', '', 'admin', 'feee4f3ca6345d6562972e7c3a9dad9b', 20150608225254, 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0, 1, '', '0', '', '', '', '', '', 0, 0, 20150608225254, 0, 0, 0);

DROP TABLE IF EXISTS ploopi_user_action_log;
CREATE TABLE IF NOT EXISTS ploopi_user_action_log (
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) unsigned NOT NULL DEFAULT '0',
  id_action int(10) unsigned NOT NULL DEFAULT '0',
  id_module_type int(10) unsigned NOT NULL DEFAULT '0',
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  id_record char(255) NOT NULL,
  ip char(16) NOT NULL,
  timestp bigint(14) NOT NULL DEFAULT '0',
  `user` char(100) NOT NULL,
  workspace char(100) NOT NULL,
  `action` char(100) NOT NULL,
  module_type char(100) NOT NULL,
  module char(100) NOT NULL,
  KEY `user` (`user`),
  KEY workspace (workspace),
  KEY `action` (`action`),
  KEY module_type (module_type),
  KEY module (module),
  KEY id_user (id_user),
  KEY id_workspace (id_workspace),
  KEY id_action (id_action),
  KEY id_module_type (id_module_type),
  KEY id_module (id_module),
  KEY id_record (id_record)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_validation;
CREATE TABLE IF NOT EXISTS ploopi_validation (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_module int(10) unsigned NOT NULL DEFAULT '0',
  id_record varchar(255) NOT NULL,
  id_object int(10) unsigned NOT NULL DEFAULT '0',
  type_validation varchar(16) DEFAULT '0',
  id_validation int(10) unsigned DEFAULT '0',
  id_module_type int(10) DEFAULT '0',
  PRIMARY KEY (id),
  KEY search (id_module,id_object,id_record),
  KEY type_workflow (type_validation),
  KEY id_workflow (id_validation),
  KEY id_module_type (id_module_type)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_workspace;
CREATE TABLE IF NOT EXISTS ploopi_workspace (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_workspace int(10) unsigned DEFAULT '0',
  label varchar(255) NOT NULL DEFAULT 'NULL',
  `code` text,
  system tinyint(1) unsigned NOT NULL DEFAULT '0',
  protected tinyint(1) unsigned NOT NULL DEFAULT '0',
  parents varchar(255) DEFAULT 'NULL',
  iprules text,
  macrules text,
  template varchar(255) DEFAULT NULL,
  depth int(10) NOT NULL DEFAULT '0',
  mustdefinerule tinyint(1) unsigned DEFAULT '0',
  backoffice tinyint(1) unsigned DEFAULT '1',
  frontoffice tinyint(1) unsigned DEFAULT '0',
  backoffice_domainlist longtext,
  title varchar(255) NOT NULL DEFAULT '',
  meta_description longtext NOT NULL,
  meta_keywords longtext NOT NULL,
  meta_author varchar(255) NOT NULL DEFAULT '',
  meta_copyright varchar(255) NOT NULL DEFAULT '',
  meta_robots varchar(255) NOT NULL DEFAULT 'index, follow, all',
  frontoffice_domainlist longtext,
  priority int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY id_workspace (id_workspace),
  KEY priority (priority),
  KEY system (system)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO ploopi_workspace (id, id_workspace, `label`, code, system, protected, parents, iprules, macrules, template, depth, mustdefinerule, backoffice, frontoffice, backoffice_domainlist, title, meta_description, meta_keywords, meta_author, meta_copyright, meta_robots, frontoffice_domainlist, priority) VALUES
(2, 1, 'Espace Principal', '', 0, 0, '0;1', '', '', 'dims', 2, 0, 1, 0, '*\r\n', '', '', '', '', '', '', '*', 1),
(1, 0, 'system', NULL, 1, 0, '0', NULL, NULL, 'dims', 1, 0, 1, 0, NULL, '', '', '', '', '', 'index, follow, all', '', 0);

DROP TABLE IF EXISTS ploopi_workspace_group;
CREATE TABLE IF NOT EXISTS ploopi_workspace_group (
  id_group int(10) NOT NULL DEFAULT '0',
  id_workspace int(10) NOT NULL DEFAULT '0',
  adminlevel tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (id_group,id_workspace),
  KEY id_workspace (id_workspace),
  KEY id_group (id_group),
  KEY id_group_2 (id_group)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_workspace_group_role;
CREATE TABLE IF NOT EXISTS ploopi_workspace_group_role (
  id_group int(10) NOT NULL DEFAULT '0',
  id_workspace int(10) NOT NULL DEFAULT '0',
  id_role int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_group,id_workspace,id_role),
  KEY id_workspace (id_workspace),
  KEY id_role (id_role),
  KEY id_group (id_group)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS ploopi_workspace_user;
CREATE TABLE IF NOT EXISTS ploopi_workspace_user (
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) unsigned NOT NULL DEFAULT '0',
  id_profile int(10) unsigned NOT NULL DEFAULT '0',
  adminlevel tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (id_user,id_workspace),
  KEY id_workspace (id_workspace)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES
(2, 2, 1, 99);

DROP TABLE IF EXISTS ploopi_workspace_user_role;
CREATE TABLE IF NOT EXISTS ploopi_workspace_user_role (
  id_user int(10) unsigned NOT NULL DEFAULT '0',
  id_workspace int(10) NOT NULL DEFAULT '0',
  id_role int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id_user,id_workspace,id_role),
  KEY id_workspace (id_workspace),
  KEY id_role (id_role),
  KEY id_user (id_user)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ploopi_workspace` CHANGE `code` `code` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `ploopi_workspace` ADD INDEX ( `code` );

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_trombi_maxlines', '25', '0', NULL, 'Nombre de réponses maxi dans la recherche (sinon index alphabétique)');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_trombi_maxlines', '25', '1');

ALTER TABLE  `ploopi_user` ADD  `last_connection` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT  '0', ADD INDEX (  `last_connection` );

ALTER TABLE `ploopi_user` CHANGE `password` `password` VARCHAR( 128 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `ploopi_workspace` ADD `mail_model` TEXT NOT NULL ;

UPDATE `ploopi_workspace` SET mail_model = 'Bonjour {firstname} {lastname},

Veuillez trouver ci-dessous vos identifiants de connexion pour le site {url} :

Identifiant: {login}
Mot de passe: {password}

Gardez précieusement votre mot de passe et ne le communiquez à personne.
Vos identifiants sont strictement personnels.

Cordialement,

Ce message a été envoyé automatiquement. Nous vous remercions de ne pas répondre.';

INSERT INTO `ploopi_param_type` (`id_module_type`, `name`, `default_value`, `public`, `description`, `label`) VALUES ('1', 'system_new_user_mail', '25', '0', NULL, 'Envoyer un courriel à la création d''un compte utilisateur');
INSERT INTO `ploopi_param_default` (`id_module`, `name`, `value`, `id_module_type`) VALUES ('1', 'system_new_user_mail', '0', '1');
INSERT INTO `ploopi_param_choice` (`id_module_type`, `name`, `value`, `displayed_value`) VALUES (1, 'system_new_user_mail', '0', 'non'), (1, 'system_new_user_mail', '1', 'oui');

UPDATE `ploopi_module_type` SET `version` = '1.9.6.11', `author` = 'Ovensia', `date` = '20181112000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;

