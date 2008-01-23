RENAME TABLE `dims_mb_cms_object` TO `dims_mb_wce_object`;


-- ------------------------------------
--                                   --
-- Script de mise à jour de la base  --
--                                   --
-- ------------------------------------


-- ------------------------
-- Creation des tables   --
-- ------------------------

-- table DIMS_WORKSPACE --


CREATE TABLE dims_workspace (
	id INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	id_workspace INTEGER(10) UNSIGNED NULL DEFAULT '0',
	label VARCHAR(255) NOT NULL DEFAULT 'NULL',
	code VARCHAR(64) NULL DEFAULT 'NULL',
	system TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	protected TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	parents VARCHAR(255) NULL DEFAULT 'NULL',
	iprules TEXT NULL,
	macrules TEXT NULL,
	admin_template VARCHAR(255) NULL DEFAULT 'NULL',
	web_template VARCHAR(255) NOT NULL,
	depth INT(10) NOT NULL DEFAULT '0',
	mustdefinerule TINYINT(1) UNSIGNED NULL DEFAULT '0',
	typegroup VARCHAR(4) NULL DEFAULT 'org',
	admin TINYINT(1) UNSIGNED NULL DEFAULT '1',
	public TINYINT(1) UNSIGNED NULL DEFAULT '0',
	web TINYINT(1) UNSIGNED NULL DEFAULT '0',
	admin_domainlist LONGTEXT NULL,
	title VARCHAR(255) NOT NULL,
	meta_description LONGTEXT NOT NULL,
	meta_keywords LONGTEXT NOT NULL,
	meta_author VARCHAR(255) NOT NULL,
	meta_copyright VARCHAR(255) NOT NULL,
	meta_robots VARCHAR(255) NOT NULL DEFAULT 'index, follow, all',
	web_domainlist LONGTEXT NULL,
	PRIMARY KEY(id)
)TYPE=MyISAM;

-- table DIMS_WORKSPACE_USER --

CREATE TABLE dims_workspace_user (
	id_user INTEGER( 10 ) UNSIGNED NOT NULL ,
	id_workspace INTEGER( 10 ) UNSIGNED NOT NULL ,
	id_profile INTEGER( 10 ) UNSIGNED NOT NULL ,
	adminlevel TINYINT( 3 ) UNSIGNED DEFAULT 0,
	PRIMARY KEY ( id_user, id_workspace )
)TYPE=MyISAM;



-- ---------------------------
-- Modification des tables  --
-- ---------------------------

-- suppression des champs inutiles --

ALTER TABLE dims_mb_field DROP id_table;

ALTER TABLE dims_mb_table DROP id;

ALTER TABLE dims_mb_schema DROP id_tablesrc;
ALTER TABLE dims_mb_schema DROP id_tabledest;

ALTER TABLE dims_mb_relation DROP id_tablesrc;
ALTER TABLE dims_mb_relation DROP id_tabledest;

ALTER TABLE dims_ticket DROP id_group;


-- renommer les tables --

RENAME TABLE
	dims_group_org TO dims_workspace_group,
	dims_group_user_role TO dims_workspace_user_role,
	dims_group_org_role TO dims_workspace_group_role,
	dims_module_group TO dims_module_workspace;

-- ajouts des clés étrangères nécessaires --

ALTER TABLE dims_annotation ADD id_module_type INTEGER(10) DEFAULT 0;
ALTER TABLE dims_ticket ADD id_module_type INTEGER(10) DEFAULT 0;
ALTER TABLE dims_workflow ADD id_module_type INTEGER(10) DEFAULT 0;
ALTER TABLE dims_share ADD id_module_type INTEGER(10) DEFAULT 0;

-- renommer les champs --

ALTER TABLE dims_workspace_group CHANGE id_group id_workspace INTEGER(10);
ALTER TABLE dims_workspace_group CHANGE id_org id_group INTEGER(10);

ALTER TABLE dims_workspace_user_role CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_workspace_group_role CHANGE id_group id_workspace INTEGER(10);
ALTER TABLE dims_workspace_group_role CHANGE id_org id_group INTEGER(10);

ALTER TABLE dims_module_workspace CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_module CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_annotation CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_mb_action CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_role CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_user_filter_rules CHANGE id_group id_workspace INTEGER(10);

ALTER TABLE dims_log CHANGE dims_groupid dims_workspaceid INTEGER(10);

ALTER TABLE dims_connecteduser CHANGE group_id workspace_id INTEGER(10);

ALTER TABLE dims_profile CHANGE id_group id_workspace INTEGER(10);

-- copie et modifications des données --

INSERT INTO dims_workspace_user ( id_user, id_workspace, id_profile, adminlevel )
	SELECT dgu.id_user, dgu.id_group, dgu.id_profile, dgu.adminlevel
	FROM dims_group_user dgu, dims_group dg
	WHERE dgu.id_group = dg.id
	AND dg.typegroup = 'work';


DELETE dims_group_user.*
	FROM dims_group_user, dims_group
	WHERE dims_group_user.id_group = dims_group.id
	AND dims_group.typegroup = 'work';


INSERT INTO dims_workspace (
							id,
							id_workspace,
							label,
							code,
							system,
							parents,
							iprules,
							macrules,
							admin_template,
							web_template,
							depth,
							mustdefinerule,
							typegroup,
							admin,
							public,
							web,
							admin_domainlist,
							title,
							meta_description,
							meta_keywords,
							meta_author,
							meta_copyright,
							meta_robots,
							web_domainlist
						)
	SELECT
							id,
							id_group,
							label,
							code,
							system,
							parents,
							iprules,
							macrules,
							admin_template,
							web_template,
							depth,
							mustdefinerule,
							typegroup,
							admin,
							public,
							web,
							admin_domainlist,
							title,
							meta_description,
							meta_keywords,
							meta_author,
							meta_copyright,
							meta_robots,
							web_domainlist
	FROM dims_group
	WHERE typegroup !=  'org';


DELETE FROM dims_group WHERE typegroup = 'work';



/***** MAJ de la table dims_annotation *****/

CREATE TABLE  tmp_annotation (
	id int(10) unsigned NOT NULL auto_increment,
	title varchar(255) NOT NULL default '',
	content longtext,
	object_label varchar(255) NOT NULL default '',
	type_annotation varchar(16) default NULL,
	date_annotation varchar(14) default NULL,
	private tinyint(1) unsigned NOT NULL default '1',
	id_record varchar(255) default NULL,
	id_object int(10) unsigned default '0',
	id_user int(10) unsigned default '0',
	id_workspace int(10) default NULL,
	id_module int(10) unsigned default '0',
	id_module_type int(10) default '0',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `id` (`id`),
	KEY id_2 (`id`)
) TYPE=MyISAM ;

INSERT INTO tmp_annotation (
							id,
							title,
							content,
							object_label,
							type_annotation,
							date_annotation,
							private,
							id_record,
							id_object,
							id_user,
							id_module,
							id_module_type
						)
SELECT DISTINCT
			da.id,
			da.title,
			da.content,
			da.object_label,
			da.type_annotation,
			da.date_annotation,
			da.private,
			da.id_record,
			da.id_object,
			da.id_user,
			da.id_module,
			dmbo.id_module_type
	FROM dims_mb_object dmbo, dims_annotation da, dims_module dm, dims_module_type dmt
	WHERE da.id_object != 0
	AND da.id_module = dm.id
	AND dm.id_module_type = dmt.id
	AND dmt.id != 0
	AND dmbo.id_module_type = dmt.id;

DROP TABLE dims_annotation;

RENAME TABLE tmp_annotation TO dims_annotation;


/***** MAJ de la table dims_ticket *****/

CREATE TABLE tmp_ticket (
	id int(10) unsigned NOT NULL auto_increment,
	title varchar(255) default NULL,
	message longtext,
	needed_validation tinyint(1) unsigned NOT NULL default '0',
	delivery_notification tinyint(1) unsigned NOT NULL default '0',
	status int(10) unsigned NOT NULL default '0',
	object_label varchar(255) NOT NULL default '',
	timestp varchar(14) default NULL,
	lastreply_timestp varchar(14) NOT NULL default '',
	count_read int(10) unsigned NOT NULL default '0',
	count_replies int(10) unsigned NOT NULL default '0',
	id_object int(10) default '0',
	id_module int(10) unsigned default '0',
	id_record varchar(255) default NULL,
	id_user int(10) unsigned default '0',
	parent_id int(10) unsigned NOT NULL default '0',
	root_id int(10) unsigned NOT NULL default '0',
	deleted tinyint(1) NOT NULL default '0',
	id_module_type int(10) default '0',
	PRIMARY KEY  (`id`),
	KEY id_user (`id_user`)
) TYPE=MyISAM ;

/* on met à jour les tickets en relation avec un objet*/
INSERT INTO tmp_ticket (
							id,
							title,
							message,
							needed_validation,
							delivery_notification,
							status,
							object_label,
							timestp,
							lastreply_timestp,
							count_read,
							count_replies,
							id_object,
							id_module,
							id_record,
							id_user,
							parent_id,
							root_id,
							deleted,
							id_module_type
						)
SELECT DISTINCT
				dt.id,
				dt.title,
				dt.message,
				dt.needed_validation,
				dt.delivery_notification,
				dt.status,
				dt.object_label,
				dt.timestp,
				dt.lastreply_timestp,
				dt.count_read,
				dt.count_replies,
				dt.id_object,
				dt.id_module,
				dt.id_record,
				dt.id_user,
				dt.parent_id,
				dt.root_id,
				dt.deleted,
				dmbo.id_module_type
	FROM dims_mb_object dmbo, dims_ticket dt, dims_module dm, dims_module_type dmt
	WHERE dt.id_object != 0
	AND dt.id_module = dm.id
	AND dm.id_module_type = dmt.id
	AND dmt.id != 0
	AND dmbo.id_module_type = dmt.id;


/* on garde les tickets sans objets*/
INSERT INTO tmp_ticket (
							id,
							title,
							message,
							needed_validation,
							delivery_notification,
							status,
							object_label,
							timestp,
							lastreply_timestp,
							count_read,
							count_replies,
							id_object,
							id_module,
							id_record,
							id_user,
							parent_id,
							root_id,
							deleted,
							id_module_type
						)
SELECT DISTINCT
				dt.id,
				dt.title,
				dt.message,
				dt.needed_validation,
				dt.delivery_notification,
				dt.status,
				dt.object_label,
				dt.timestp,
				dt.lastreply_timestp,
				dt.count_read,
				dt.count_replies,
				dt.id_object,
				dt.id_module,
				dt.id_record,
				dt.id_user,
				dt.parent_id,
				dt.root_id,
				dt.deleted,
				dt.id_module_type
	FROM dims_ticket dt
	WHERE dt.id_object = 0;


DROP TABLE dims_ticket;

RENAME TABLE tmp_ticket TO dims_ticket;


/***** MAJ de la table dims_workflow *****/

CREATE TABLE tmp_workflow (
	id int(10) unsigned NOT NULL auto_increment,
	id_module int(10) unsigned NOT NULL default '0',
	id_record int(10) unsigned NOT NULL default '0',
	id_object int(10) unsigned NOT NULL default '0',
	type_workflow varchar(16) default '0',
	id_workflow int(10) unsigned default '0',
	id_module_type int(10) default '0',
	PRIMARY KEY  (`id`),
	KEY search (`id_module`,`id_object`,`id_record`)
) TYPE=MyISAM;

INSERT INTO tmp_workflow (
							id,
							id_module,
							id_record,
							id_object,
							type_workflow,
							id_workflow,
							id_module_type
						)
SELECT DISTINCT
				dw.id,
				dw.id_module,
				dw.id_record,
				dw.id_object,
				dw.type_workflow,
				dw.id_workflow,
				dmbo.id_module_type
	FROM dims_mb_object dmbo, dims_workflow dw, dims_module dm, dims_module_type dmt
	WHERE dw.id_object != 0
	AND dw.id_module = dm.id
	AND dm.id_module_type = dmt.id
	AND dmt.id != 0
	AND dmbo.id_module_type = dmt.id;


DROP TABLE dims_workflow;

RENAME TABLE tmp_workflow TO dims_workflow;



/***** MAJ de la table dims_share *****/


CREATE TABLE tmp_share (
	id int(10) unsigned NOT NULL auto_increment,
	id_module int(10) unsigned NOT NULL default '0',
	id_record int(10) unsigned NOT NULL default '0',
	id_object int(10) unsigned NOT NULL default '0',
	type_share varchar(16) default '0',
	id_share int(10) unsigned default '0',
	id_module_type int(10) default '0',
	PRIMARY KEY  (`id`),
	KEY search (`id_module`,`id_object`,`id_record`)
) TYPE=MyISAM;

INSERT INTO tmp_share (
							id,
							id_module,
							id_record,
							id_object,
							type_share,
							id_share,
							id_module_type
						)
SELECT DISTINCT
				ds.id,
				ds.id_module,
				ds.id_record,
				ds.id_object,
				ds.type_share,
				ds.id_share,
				dmbo.id_module_type
	FROM dims_mb_object dmbo, dims_share ds, dims_module dm, dims_module_type dmt
	WHERE ds.id_object != 0
	AND ds.id_module = dm.id
	AND dm.id_module_type = dmt.id
	AND dmt.id != 0
	AND dmbo.id_module_type = dmt.id;


DROP TABLE dims_share;

RENAME TABLE tmp_share TO dims_share;

-- ------------------------------
-- MAJ de la table dims_group --
-- ------------------------------

ALTER TABLE `dims_group`
  DROP `code`,
  DROP `iprules`,
  DROP `macrules`,
  DROP `admin_template`,
  DROP `web_template`,
  DROP `mustdefinerule`,
  DROP `admin`,
  DROP `public`,
  DROP `web`,
  DROP `admin_domainlist`,
  DROP `title`,
  DROP `meta_description`,
  DROP `meta_keywords`,
  DROP `meta_author`,
  DROP `meta_copyright`,
  DROP `meta_robots`,
  DROP `web_domainlist`;


-- ----------------------------------
-- MAJ de la table dims_group_user --
-- ----------------------------------

ALTER TABLE dims_group_user DROP id_profile;
ALTER TABLE dims_group_user DROP adminlevel;



-- -------
-- FIN  --
-- -------
