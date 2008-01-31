<?php
/*
	Copyright (c) 2002-2007 Netlor
	Copyright (c) 2007-2008 Ovensia
	Contributors hold Copyright (c) to their code submissions.

	This file is part of Ploopi.

	Ploopi is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Ploopi is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Ploopi; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?
##############################################################################
#
# Date / Time functions
#
##############################################################################


/**
* ! description !
*
* @return string returns current DATE in localized format according to the _PLOOPI_DATEFORMAT constant
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_getdate() {return date(_PLOOPI_DATEFORMAT);}

/**
* ! description !
*
* @return string returns current TIME in localized format according to the _PLOOPI_DATEFORMAT constant
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_gettime() {return date(_PLOOPI_TIMEFORMAT);}

/**
* ! description !
*
* @return string returns current TIME in MySQL timestamp format
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_getdatetime() {return date(_PLOOPI_DATETIMEFORMAT_MYSQL);}

/**
* ! description !
*
* @param string date in localized format
* @return string returns the param date in localized format according to the _PLOOPI_DATEFORMAT constant
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_dateverify($mydate)
{
	switch(_PLOOPI_DATEFORMAT)
	{
		case _PLOOPI_DATEFORMAT_FR:
			return ereg(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $regs);
		break;
		case _PLOOPI_DATEFORMAT_US:
			return ereg(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $regs);
		break;
		default:
			return false;
		break;
	}
}

/**
* ! description !
*
* @param string time
* @return string returns the param time in localized format according to the _PLOOPI_DATEFORMAT constant
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_timeverify($mytime) {return ereg(_PLOOPI_TIMEFORMAT_EREG, $mytime, $regs);}

/**
* ! description !
*
* @param string date
* @param string time
* @return string returns param'd date and time in "DATETIME" format
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*
* @uses ploopi_dateverify()
*/
function ploopi_local2datetime($mydate,$mytime)
{
	// verify local format
	if (ploopi_dateverify($mydate) && ploopi_timeverify($mytime))
	{
		ereg(_PLOOPI_TIMEFORMAT_EREG, $mytime, $timeregs);
		switch(_PLOOPI_DATEFORMAT)
		{
			case _PLOOPI_DATEFORMAT_FR:
				ereg(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $dateregs);
				$mydatetime = date(_PLOOPI_DATETIMEFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[1],$dateregs[3]));
			break;
			case _PLOOPI_DATEFORMAT_US:
				ereg(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $dateregs);
				$mydatetime = date(_PLOOPI_DATETIMEFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[3],$dateregs[1]));
			break;
		}

		return($mydatetime);
	}
	else return(false);
}

/**
* ! description !
*
* @param string date
* @return string returns param'd "DATETIME" in localized human readable form
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_datetime2local($mydatetime)
{
	$mydate = Array();

	// verify mysql format
	if (ereg(_PLOOPI_DATETIMEFORMAT_MYSQL_EREG, $mydatetime, $regs))
	{
		$mydate['date'] = date(_PLOOPI_DATEFORMAT, mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]));
		$mydate['time'] = date(_PLOOPI_TIMEFORMAT, mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]));
		return($mydate);
	}
	else return(false);
}

//
/*
$regs[_PLOOPI_DATE_YEAR] => Year
$regs[_PLOOPI_DATE_MONTH] => Month
$regs[_PLOOPI_DATE_DAY] => Day
$regs[_PLOOPI_DATE_HOUR] => Hour
$regs[_PLOOPI_DATE_MINUTE] => Minute
$regs[_PLOOPI_DATE_SECOND] => Second
*/

/**
* Get detailled datetime in a tab
*
* @return array returns current date/time details in an array
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_getdatetimedetail()
{
	ereg(_PLOOPI_DATETIMEFORMAT_MYSQL_EREG, date(_PLOOPI_DATETIMEFORMAT_MYSQL), $regs);
	return $regs;
}

/**
* ! description !
*
* @param string timestamp
* @return array returns param'd timestamp details in an array
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_gettimestampdetail($mytimestamp)
{
	ereg(_PLOOPI_TIMESTAMPFORMAT_MYSQL_EREG, $mytimestamp, $regs);
	return $regs;
}

/**
* ! description !
*
* @return string returns current timestamp
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*/
function ploopi_createtimestamp()
{
	return date(_PLOOPI_TIMESTAMPFORMAT_MYSQL);
}

function ploopi_unixtimestamp2local($mytimestamp)
{
	return(date(_PLOOPI_DATEFORMAT,$mytimestamp).' '.date(_PLOOPI_TIMEFORMAT,$mytimestamp));
}

function ploopi_unixtimestamp2timestamp($mytimestamp)
{
	return(date(_PLOOPI_TIMESTAMPFORMAT_MYSQL,$mytimestamp));
}

/**
* ! description !
*
* @param string timestamp
* @return array returns a 2 dimensions array with param'd timestamp converted to localized date/time format
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*
* @uses ploopi_gettimestampdetail()
*/
function ploopi_timestamp2local($mytimestamp)
{
	// Output array declaration
	$mydate = array();

	// Trimming
	$mytimestamp = trim($mytimestamp);

	// Exploding MySQL timestamp into human readable values
	$timestamparray = ploopi_gettimestampdetail($mytimestamp);

	$year = $timestamparray[_PLOOPI_DATE_YEAR];
	$month = $timestamparray[_PLOOPI_DATE_MONTH];
	$day = $timestamparray[_PLOOPI_DATE_DAY];
	$hour = $timestamparray[_PLOOPI_DATE_HOUR];
	$minute = $timestamparray[_PLOOPI_DATE_MINUTE];
	$second = $timestamparray[_PLOOPI_DATE_SECOND];

	// Re-constucting date depending on the "_PLOOPI_DATEFORMAT"
	switch (_PLOOPI_DATEFORMAT)
	{
		CASE _PLOOPI_DATEFORMAT_FR:
		{
			$localedate = $day . '/' .$month . '/' . $year;
		}
		BREAK;

		CASE _PLOOPI_DATEFORMAT_FR:
		{
			$localedate = $year . '/' . $month . '/' . $day;
		}
		BREAK;
	}

	$localetime = $hour . ':' . $minute . ':' . $second;

	// Constructing output array
	$mydate['date'] = $localedate;
	$mydate['time'] = $localetime;

	// returning the output array
	return $mydate;
}

/**
* Convert local date & time to datetime mysql
*
* @param string date
* @param string time
* @return string returns param'd date and time in a MySQL datetime format
*
* @version 2.09
* @since 0.1
*
* @category date/time manipulations
*
* @uses ploopi_dateverify()
*/
function ploopi_local2timestamp($mydate,$mytime = '00:00:00')
{
	// verify local format

	if (ploopi_dateverify($mydate))// && ploopi_timeverify($mytime))
	{
		ereg(_PLOOPI_TIMEFORMAT_EREG, $mytime, $timeregs);
		switch(_PLOOPI_DATEFORMAT)
		{
			CASE _PLOOPI_DATEFORMAT_FR:
				ereg(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $dateregs);
				$mydatetime = date(_PLOOPI_TIMESTAMPFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[1],$dateregs[3]));
			BREAK;
			CASE _PLOOPI_DATEFORMAT_US:
				ereg(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $dateregs);
				$mydatetime = date(_PLOOPI_TIMESTAMPFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[3],$dateregs[1]));
			BREAK;
		}

		return($mydatetime);
	}
	else return(false);
}


function ploopi_timestamp_add($timestp, $h=0, $mn=0, $s=0, $m=0, $d=0, $y=0)
{
	$timestp_array = ploopi_gettimestampdetail($timestp);

	return date(_PLOOPI_TIMESTAMPFORMAT_MYSQL, mktime(	$timestp_array[_PLOOPI_DATE_HOUR]+$h,
														$timestp_array[_PLOOPI_DATE_MINUTE]+$mn,
														$timestp_array[_PLOOPI_DATE_SECOND]+$s,
														$timestp_array[_PLOOPI_DATE_MONTH]+$m,
														$timestp_array[_PLOOPI_DATE_DAY]+$d,
														$timestp_array[_PLOOPI_DATE_YEAR]+$y
													));
}



function ploopi_gmt_timestamp()
{
	// renvoie le timestamp de Greenwich
	return(ploopi_timestamp_add(ploopi_createtimestamp(), 0, 0, -date('Z'), 0, 0, 0));
}

// renvoie le timezone du serveur (par rapport à Greenwich)
function ploopi_tz()
{
	return(date('Z')/3600);
}

// renvoie le timestamp 'local' en fonction du timezone
function ploopi_tz_timestamp($timezone, $gmt_timestamp = 0)
{
	if ($timezone == 'server') $timezone = ploopi_tz();

	if (!$gmt_timestamp) $gmt_timestamp = ploopi_gmt_timestamp();
	return(ploopi_timestamp_add($gmt_timestamp, 0, 0, $timezone*3600, 0, 0, 0));
}

function ploopi_formatgmt($timezone)
{
	return(sprintf("%s%02d:%02d", ($timezone>=0) ? '+' : '-', floor(abs($timezone)),(fmod(abs($timezone),1)*60)));
}

function ploopi_tz_timestamp2local($ts, $timezone = 0, $light = false)
{
	if ($timezone === 'server') $timezone = ploopi_tz();
	if ($timezone === 0) $timezone = $_SESSION['ploopi']['user']['timezone'];

	$localdate = ploopi_timestamp2local(ploopi_tz_timestamp($timezone, $ts));

	if ($light) return(substr($localdate['date'],0,5).' '.substr($localdate['time'],0,5));
	else return($localdate['date'].' '.substr($localdate['time'],0,5).' (GMT '.ploopi_formatgmt($timezone).')');
}

function ploopi_tz_local2timestamp($date, $heure = '', $timezone = 0)
{
	if ($timezone === 'paris') $timezone = ploopi_tz();
	if ($timezone === 0) $timezone = $_SESSION['ploopi']['user']['timezone'];

	return(ploopi_tz_timestamp(-$timezone, ploopi_local2timestamp($date, $heure)));
}

?>
