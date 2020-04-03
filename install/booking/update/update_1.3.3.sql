UPDATE `ploopi_mod_booking_event_detail` SET `cancelreason` = '' WHERE ISNULL(`cancelreason`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `cancelreason` `cancelreason` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event_detail` SET `message` = '' WHERE ISNULL(`message`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `message` `message` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event_detail` SET `emails` = '' WHERE ISNULL(`emails`);
ALTER TABLE `ploopi_mod_booking_event_detail` CHANGE `emails` `emails` text NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_subresource` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_subresource` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_subresource` SET `reference` = '' WHERE ISNULL(`reference`);
ALTER TABLE `ploopi_mod_booking_subresource` CHANGE `reference` `reference` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resourcetype` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_resourcetype` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `name` = '' WHERE ISNULL(`name`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `name` `name` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `reference` = '' WHERE ISNULL(`reference`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `reference` `reference` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_resource` SET `color` = '' WHERE ISNULL(`color`);
ALTER TABLE `ploopi_mod_booking_resource` CHANGE `color` `color` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `object` = '' WHERE ISNULL(`object`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `object` `object` varchar(255) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `periodicity` = '' WHERE ISNULL(`periodicity`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `periodicity` `periodicity` varchar(16) NOT NULL DEFAULT ''  COMMENT '' ;
UPDATE `ploopi_mod_booking_event` SET `comment` = '' WHERE ISNULL(`comment`);
ALTER TABLE `ploopi_mod_booking_event` CHANGE `comment` `comment` mediumtext NOT NULL DEFAULT ''  COMMENT '' ;
