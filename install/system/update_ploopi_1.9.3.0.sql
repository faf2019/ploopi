ALTER TABLE `ploopi_module_workspace` ADD INDEX ( `id_module` ) ;
ALTER TABLE `ploopi_module_workspace` ADD INDEX ( `id_workspace` ) ;
ALTER TABLE `ploopi_module_type` ADD INDEX ( `label` ) ;
ALTER TABLE `ploopi_group` ADD INDEX ( `parents` ) ;
ALTER TABLE `ploopi_workspace_group` ADD INDEX ( `id_group` ) ;
ALTER TABLE `ploopi_ticket_dest` ADD INDEX ( `id_user` ) ;
ALTER TABLE `ploopi_ticket_watch` ADD INDEX ( `id_ticket` ) ;
ALTER TABLE `ploopi_ticket_watch` ADD INDEX ( `id_user` ) ;
ALTER TABLE `ploopi_ticket_watch` ADD INDEX ( `notify` ) ;

INSERT INTO `ploopi_mimetype` (`ext`, `mimetype`, `filetype`, `group`) VALUES
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

UPDATE `ploopi_mimetype` SET `mimetype` = 'video/mp4' WHERE `ploopi_mimetype`.`ext` = 'm4v';

UPDATE `ploopi_module_type` SET `version` = '1.9.3.0', `author` = 'Ovensia', `date` = '20130318000000', `description` = 'Noyau du système' WHERE `ploopi_module_type`.`id` = 1;
