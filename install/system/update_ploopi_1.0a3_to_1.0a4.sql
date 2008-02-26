ALTER TABLE `ploopi_log` CHANGE `dims_userid` `ploopi_userid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dims_workspaceid` `ploopi_workspaceid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dims_moduleid` `ploopi_moduleid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
