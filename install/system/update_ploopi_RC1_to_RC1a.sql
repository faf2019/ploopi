ALTER TABLE `ploopi_log` ADD `ts` BIGINT( 14 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `system` ;
UPDATE `ploopi_log` SET ts = CONCAT(`date_year`,`date_month`,`date_day`,`date_hour`,`date_minute`,`date_second`);
UPDATE `ploopi_log` SET ts = RPAD(ts,14,'0');
ALTER TABLE `ploopi_log`
  DROP `date_year`,
  DROP `date_month`,
  DROP `date_day`,
  DROP `date_hour`,
  DROP `date_minute`,
  DROP `date_second`;
  
