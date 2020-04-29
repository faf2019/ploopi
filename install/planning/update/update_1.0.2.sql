UPDATE `ploopi_mod_planning_event_detail_resource` SET `type_resource` = '' WHERE ISNULL(`type_resource`);
ALTER TABLE `ploopi_mod_planning_event_detail_resource` CHANGE `type_resource` `type_resource` enum('group','user') NOT NULL DEFAULT 'user' COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `object` = '' WHERE ISNULL(`object`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `object` `object` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `periodicity` = '' WHERE ISNULL(`periodicity`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `periodicity` `periodicity` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_planning_event` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_planning_event` CHANGE `comment` `comment` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
