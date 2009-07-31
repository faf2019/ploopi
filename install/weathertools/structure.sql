DROP TABLE IF EXISTS `ploopi_mod_weathertools_cache`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_weathertools_cache` (
  `zoneid` varchar(16) NOT NULL,
  `rawcontent` text NOT NULL,
  `timestp` bigint(14) NOT NULL,
  PRIMARY KEY  (`zoneid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ploopi_mod_weathertools_station`;
CREATE TABLE IF NOT EXISTS `ploopi_mod_weathertools_station` (
  `icao` char(4) NOT NULL,
  `block_number` char(4) NOT NULL,
  `station_number` char(4) NOT NULL,
  `place_name` varchar(64) NOT NULL,
  `us_state` char(2) character set latin2 collate latin2_bin NOT NULL,
  `country_name` varchar(128) NOT NULL,
  `wmo_region` tinyint(1) unsigned NOT NULL,
  `station_latitude` varchar(32) NOT NULL,
  `station_longitude` varchar(32) NOT NULL,
  `upper_air_latitude` varchar(32) NOT NULL,
  `upper_air_longitude` varchar(32) NOT NULL,
  `station_elevation` smallint(5) unsigned NOT NULL,
  `upper_air_elevation` smallint(5) unsigned NOT NULL,
  `rbsn_indicateur` char(1) NOT NULL,
  `station_latitude_wgs84` double NOT NULL,
  `station_longitude_wgs84` double NOT NULL,
  PRIMARY KEY  (`icao`),
  KEY `block_number` (`block_number`),
  KEY `station_number` (`station_number`),
  KEY `place_name` (`place_name`),
  KEY `us_state` (`us_state`),
  KEY `country_name` (`country_name`),
  KEY `wmo_region` (`wmo_region`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
