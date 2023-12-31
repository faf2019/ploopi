UPDATE `ploopi_mod_webedit_article_backup` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article_backup` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_docfile` SET `id_docfile` = 0  WHERE ISNULL(`id_docfile`);
ALTER TABLE `ploopi_mod_webedit_docfile` CHANGE `id_docfile` `id_docfile` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_docfile` SET `md5id_docfile` = '' WHERE ISNULL(`md5id_docfile`);
ALTER TABLE `ploopi_mod_webedit_docfile` CHANGE `md5id_docfile` `md5id_docfile` char(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `label` = '' WHERE ISNULL(`label`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `label` `label` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `description` = '' WHERE ISNULL(`description`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `description` `description` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `color` = '' WHERE ISNULL(`color`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `color` `color` varchar(32) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `url` = '' WHERE ISNULL(`url`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `url` `url` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `sortmode` = '' WHERE ISNULL(`sortmode`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `sortmode` `sortmode` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `free1` = '' WHERE ISNULL(`free1`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `free1` `free1` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `free2` = '' WHERE ISNULL(`free2`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `free2` `free2` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_heading` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `title` = '' WHERE ISNULL(`title`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metakeywords` = '' WHERE ISNULL(`metakeywords`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metakeywords` `metakeywords` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `content_cleaned` = '' WHERE ISNULL(`content_cleaned`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `content_cleaned` `content_cleaned` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metadescription` = '' WHERE ISNULL(`metadescription`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metadescription` `metadescription` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `metatitle` = '' WHERE ISNULL(`metatitle`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `metatitle` `metatitle` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `author` = '' WHERE ISNULL(`author`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `author` `author` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_heading` = 0  WHERE ISNULL(`id_heading`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_heading` `id_heading` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `tags` = '' WHERE ISNULL(`tags`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `tags` `tags` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article` SET `headcontent` = '' WHERE ISNULL(`headcontent`);
ALTER TABLE `ploopi_mod_webedit_article` CHANGE `headcontent` `headcontent` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_heading_subscriber` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_webedit_heading_subscriber` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_article` = 0  WHERE ISNULL(`id_article`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_article` `id_article` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_wce_object` = 0  WHERE ISNULL(`id_wce_object`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_wce_object` `id_wce_object` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_object` SET `id_record` = '' WHERE ISNULL(`id_record`);
ALTER TABLE `ploopi_mod_webedit_article_object` CHANGE `id_record` `id_record` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_tag` SET `tag` = '' WHERE ISNULL(`tag`);
ALTER TABLE `ploopi_mod_webedit_tag` CHANGE `tag` `tag` varchar(64) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_article` = 0  WHERE ISNULL(`id_article`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_article` `id_article` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `comment` `comment` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `email` = '' WHERE ISNULL(`email`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `email` `email` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `nickname` = '' WHERE ISNULL(`nickname`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `nickname` `nickname` varchar(50) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `timestp` = 0  WHERE ISNULL(`timestp`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `timestp` `timestp` bigint(14) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_comment` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article_comment` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `title` = '' WHERE ISNULL(`title`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `title` `title` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metakeywords` = '' WHERE ISNULL(`metakeywords`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metakeywords` `metakeywords` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `content` = '' WHERE ISNULL(`content`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `content` `content` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `content_cleaned` = '' WHERE ISNULL(`content_cleaned`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `content_cleaned` `content_cleaned` longtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metadescription` = '' WHERE ISNULL(`metadescription`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metadescription` `metadescription` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `metatitle` = '' WHERE ISNULL(`metatitle`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `metatitle` `metatitle` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `author` = '' WHERE ISNULL(`author`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `author` `author` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_heading` = 0  WHERE ISNULL(`id_heading`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_heading` `id_heading` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_module` = 0  WHERE ISNULL(`id_module`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_module` `id_module` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_user` = 0  WHERE ISNULL(`id_user`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_user` `id_user` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `id_workspace` = 0  WHERE ISNULL(`id_workspace`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `id_workspace` `id_workspace` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `tags` = '' WHERE ISNULL(`tags`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `tags` `tags` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_webedit_article_draft` SET `headcontent` = '' WHERE ISNULL(`headcontent`);
ALTER TABLE `ploopi_mod_webedit_article_draft` CHANGE `headcontent` `headcontent` longtext NOT NULL DEFAULT ''  COMMENT '' ;
