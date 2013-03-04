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

/**
 * Fonction de base pour le traitement des dates, des timestamps MYSQL et des fuseaux horaires.
 * Conversion de formats, conversion de fuseaux, calculs...
 *
 * @package ploopi
 * @subpackage date
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Retourne la date du serveur au format local (_PLOOPI_DATEFORMAT)
 *
 * @return string date au format local
 */

function ploopi_getdate() {return date(_PLOOPI_DATEFORMAT);}

/**
 * Retourne l'heure au format local (_PLOOPI_TIMEFORMAT)
 *
 * @return string heure au format local
 */

function ploopi_gettime() {return date(_PLOOPI_TIMEFORMAT);}

/**
 * Vérifie le format de la date en fonction du format local
 *
 * @param string $mydate date à vérifier
 * @return boolean true si le format de la date est valide
 */

function ploopi_dateverify($mydate)
{
    switch(_PLOOPI_DATEFORMAT)
    {
        case _PLOOPI_DATEFORMAT_FR:
            return preg_match(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $regs) === 1;
        break;

        case _PLOOPI_DATEFORMAT_US:
            return !preg_match(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $regs) === 1;
        break;

        default:
            return false;
        break;
    }
}

/**
 * Vérifie le format de l'heure en fonction du format local
 *
 * @param string $mytime heure à vérifier
 * @return boolean true si le format de l'heure est valide
 */

function ploopi_timeverify($mytime) { return preg_match(_PLOOPI_TIMEFORMAT_EREG, $mytime, $regs) === 1; }

/**
 * Renvoie le détail d'un timestamp au format MYSQL (AAAAMMJJhhmmss) sous forme d'un tableau
 * $regs[_PLOOPI_DATE_YEAR] => Year
 * $regs[_PLOOPI_DATE_MONTH] => Month
 * $regs[_PLOOPI_DATE_DAY] => Day
 * $regs[_PLOOPI_DATE_HOUR] => Hour
 * $regs[_PLOOPI_DATE_MINUTE] => Minute
 * $regs[_PLOOPI_DATE_SECOND] => Second
 *
 * @param string $mytimestamp
 * @return array tableau contenant le détail de la date
 */

function ploopi_gettimestampdetail($mytimestamp)
{
    preg_match(_PLOOPI_TIMESTAMPFORMAT_MYSQL_EREG, sprintf("%-014s", $mytimestamp), $regs);
    return $regs;
}

/**
 * Crée un timestamp de la date du serveur au format MYSQL (AAAAMMJJhhmmss)
 *
 * @return string
 */

function ploopi_createtimestamp() { return date(_PLOOPI_TIMESTAMPFORMAT_MYSQL); }

/**
 * Crée un datetime de la date du serveur au format MYSQL (AAAA-MM-JJ hh:mm:ss)
 *
 * @return string
 */

function ploopi_createdatetime() { return date(_PLOOPI_DATETIMEFORMAT_MYSQL); }

/**
 * Renvoie un timestamp UNIX au format local (_PLOOPI_TIMEFORMAT)
 *
 * @param int $mytimestamp timestamp UNIX
 * @return string date au format local
 */

function ploopi_unixtimestamp2local($mytimestamp) { return(date(_PLOOPI_DATEFORMAT,$mytimestamp).' '.date(_PLOOPI_TIMEFORMAT,$mytimestamp)); }

/**
 * Convertit un timestamp UNIX en timestamp MYSQL (AAAAMMJJhhmmss)
 *
 * @param int $mytimestamp timestamp UNIX
 * @return string timestamp MYSQL
 */

function ploopi_unixtimestamp2timestamp($mytimestamp) { return(date(_PLOOPI_TIMESTAMPFORMAT_MYSQL,$mytimestamp)); }

/**
 * Convertit un timestamp UNIX en datetime MYSQL (AAAA-MM-JJ hh:mm:ss)
 *
 * @param int $mytimestamp timestamp UNIX
 * @return string datetime MYSQL
 */

function ploopi_unixtimestamp2datetime($mytimestamp) { return(date(_PLOOPI_DATETIMEFORMAT_MYSQL,$mytimestamp)); }


/**
 * Convertit un timestamp MYSQL (AAAAMMJJhhmmss) en timestamp UNIX
 *
 * @param string $mytimestamp timestamp MYSQL
 * @return int timestamp UNIX
 */

function ploopi_timestamp2unixtimestamp($mytimestamp)
{
    $timestp_array = ploopi_gettimestampdetail($mytimestamp);

    return(
        mktime(
            $timestp_array[_PLOOPI_DATE_HOUR],
            $timestp_array[_PLOOPI_DATE_MINUTE],
            $timestp_array[_PLOOPI_DATE_SECOND],
            $timestp_array[_PLOOPI_DATE_MONTH],
            $timestp_array[_PLOOPI_DATE_DAY],
            $timestp_array[_PLOOPI_DATE_YEAR]
        )
    );
}

/**
 * Convertit un datetime MYSQL (AAAA-MM-JJ hh:mm:ss) en timestamp MySQL (AAAAMMJJhhmmss)
 *
 * @param string $mytimestamp datetime MYSQL
 * @return int timestamp UNIX
 */

function ploopi_datetime2timestamp($mydatetime)
{
    return str_replace(array('-', ':', ' '), '', $mydatetime);
}

/**
 * Convertit un datetime MYSQL (AAAA-MM-JJ hh:mm:ss) en timestamp UNIX
 *
 * @param string $mytimestamp datetime MYSQL
 * @return int timestamp UNIX
 */

function ploopi_datetime2unixtimestamp($mydatetime)
{
    return ploopi_timestamp2unixtimestamp(ploopi_datetime2timestamp($mydatetime));
}


/**
 * Convertit un datetime MYSQL (AAAA-MM-JJ hh:mm:ss) au format local (date+heure)
 *
 * @param string $mydatetime
 * @return array tableau associatif contenant la date et l'heure : Array('date' => '', 'time' => '');
 */

function ploopi_datetime2local($mydatetime)
{
    return ploopi_timestamp2local(ploopi_datetime2timestamp($mydatetime));
}


/**
 * Convertit un timestamp MYSQL (AAAAMMJJhhmmss) au format local (date+heure)
 *
 * @param string $mytimestamp
 * @return array tableau associatif contenant la date et l'heure : Array('date' => '', 'time' => '');
 */

function ploopi_timestamp2local($mytimestamp)
{
    // Output array declaration
    $mydate = array('date' => '', 'time' => '');

    // Trimming
    $mytimestamp = trim($mytimestamp);

    if (empty($mytimestamp)) return $mydate;

    // Exploding MySQL timestamp into human readable values
    $timestamparray = ploopi_gettimestampdetail($mytimestamp);
    if (is_array($timestamparray) && sizeof($timestamparray) == 7)
    {
        $year = $timestamparray[_PLOOPI_DATE_YEAR];
        $month = $timestamparray[_PLOOPI_DATE_MONTH];
        $day = $timestamparray[_PLOOPI_DATE_DAY];
        $hour = $timestamparray[_PLOOPI_DATE_HOUR];
        $minute = $timestamparray[_PLOOPI_DATE_MINUTE];
        $second = $timestamparray[_PLOOPI_DATE_SECOND];

        // Re-constucting date depending on the "_PLOOPI_DATEFORMAT"
        switch (_PLOOPI_DATEFORMAT)
        {
            case _PLOOPI_DATEFORMAT_FR:
                $localedate = $day . '/' .$month . '/' . $year;
            break;

            case _PLOOPI_DATEFORMAT_FR:
                $localedate = $year . '/' . $month . '/' . $day;
            break;
        }

        $localetime = $hour . ':' . $minute . ':' . $second;

        // Constructing output array
        $mydate['date'] = $localedate;
        $mydate['time'] = $localetime;
    }

    // returning the output array
    return $mydate;
}


/**
 * Convertit un timestamp MYSQL (AAAAMMJJhhmmss) au format XLS
 *
 * @param int $intTs timestamp MySQL
 * @return int date/heure XLS
 */

function ploopi_timestamp2xls($intTs)
{
    $intTs = ploopi_timestamp2unixtimestamp($intTs);

    return empty($intTs) ? '' : $intTs / 86400 + 25569 + 1/12;
}


/**
 * Convertit un date locale au format timestamp MYSQL (AAAAMMJJhhmmss)
 *
 * @param string $mydate date au format local
 * @param string $mytime heure au format local (optionnel, par défaut '00:00:00')
 * @return string timestamp MYSQL
 */

function ploopi_local2timestamp($mydate, $mytime = '00:00:00')
{
    // verify local format
    if (ploopi_dateverify($mydate))// && ploopi_timeverify($mytime))
    {
        preg_match(_PLOOPI_TIMEFORMAT_EREG, $mytime, $timeregs);
        switch(_PLOOPI_DATEFORMAT)
        {
            case _PLOOPI_DATEFORMAT_FR:
                preg_match(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $dateregs);
                if ($dateregs[3]<100) $dateregs[3]+=2000;
                $mydatetime = date(_PLOOPI_TIMESTAMPFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[1],$dateregs[3]));
            break;
            case _PLOOPI_DATEFORMAT_US:
                preg_match(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $dateregs);
                if ($dateregs[1]<100) $dateregs[1]+=2000;
                $mydatetime = date(_PLOOPI_TIMESTAMPFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[3],$dateregs[1]));
            break;
        }

        return $mydatetime;
    }
    else return false;
}



/**
 * Convertit un date locale au format datetime MYSQL (AAAA-MM-JJ hh:mm:ss)
 *
 * @param string $mydate date au format local
 * @param string $mytime heure au format local (optionnel, par défaut '00:00:00')
 * @return string timestamp MYSQL
 */

function ploopi_local2datetime($mydate, $mytime = '00:00:00')
{
    // verify local format
    if (ploopi_dateverify($mydate))// && ploopi_timeverify($mytime))
    {
        preg_match(_PLOOPI_TIMEFORMAT_EREG, $mytime, $timeregs);
        switch(_PLOOPI_DATEFORMAT)
        {
            case _PLOOPI_DATEFORMAT_FR:
                preg_match(_PLOOPI_DATEFORMAT_EREG_FR, $mydate, $dateregs);
                if ($dateregs[3]<100) $dateregs[3]+=2000;
                $mydatetime = date(_PLOOPI_DATETIMEFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[1],$dateregs[3]));
            break;
            case _PLOOPI_DATEFORMAT_US:
                preg_match(_PLOOPI_DATEFORMAT_EREG_US, $mydate, $dateregs);
                if ($dateregs[1]<100) $dateregs[1]+=2000;
                $mydatetime = date(_PLOOPI_DATETIMEFORMAT_MYSQL, mktime($timeregs[1],$timeregs[2],$timeregs[3],$dateregs[2],$dateregs[3],$dateregs[1]));
            break;
        }

        return $mydatetime;
    }
    else return false;
}

/**
 * Ajoute un durée (positive ou négative) à un timestamp MYSQL (AAAAMMJJhhmmss)
 *
 * @param string $timestp timestamp MYSQL
 * @param int $h nombre d'heures à ajouter
 * @param int $mn nombre de minutes à ajouter
 * @param int $s nombre de secondes à ajouter
 * @param int $m nombre de mois à ajouter
 * @param int $d nombre de jours à ajouter
 * @param int $y nombre d'année à ajouter
 * @return string timestamp MYSQL mis à jour
 */

function ploopi_timestamp_add($timestp, $h=0, $mn=0, $s=0, $m=0, $d=0, $y=0)
{
    $timestp_array = ploopi_gettimestampdetail($timestp);

    return
        date(
            _PLOOPI_TIMESTAMPFORMAT_MYSQL,
            mktime(
                $timestp_array[_PLOOPI_DATE_HOUR]+$h,
                $timestp_array[_PLOOPI_DATE_MINUTE]+$mn,
                $timestp_array[_PLOOPI_DATE_SECOND]+$s,
                $timestp_array[_PLOOPI_DATE_MONTH]+$m,
                $timestp_array[_PLOOPI_DATE_DAY]+$d,
                $timestp_array[_PLOOPI_DATE_YEAR]+$y
            )
        );
}

/**
 * Ajoute un durée (positive ou négative) à un datetime MYSQL (AAAA-MM-JJ hh:mm:ss)
 *
 * @param string $datetime datetime MYSQL
 * @param int $h nombre d'heures à ajouter
 * @param int $mn nombre de minutes à ajouter
 * @param int $s nombre de secondes à ajouter
 * @param int $m nombre de mois à ajouter
 * @param int $d nombre de jours à ajouter
 * @param int $y nombre d'année à ajouter
 * @return string datetime MYSQL mis à jour
 */

function ploopi_datetime_add($datetime, $h=0, $mn=0, $s=0, $m=0, $d=0, $y=0)
{
    $timestp_array = ploopi_gettimestampdetail(ploopi_datetime2timestamp($datetime));

    return
        date(
            _PLOOPI_DATETIMEFORMAT_MYSQL,
            mktime(
                $timestp_array[_PLOOPI_DATE_HOUR]+$h,
                $timestp_array[_PLOOPI_DATE_MINUTE]+$mn,
                $timestp_array[_PLOOPI_DATE_SECOND]+$s,
                $timestp_array[_PLOOPI_DATE_MONTH]+$m,
                $timestp_array[_PLOOPI_DATE_DAY]+$d,
                $timestp_array[_PLOOPI_DATE_YEAR]+$y
            )
        );
}


/**
 * Retourne un timestamp unix de la date du 1er jour d'une semaine
 *
 * @param int $intNumWeek Numéro de la semaine dans l'année
 * @param int $intYear Année
 * @return int timestamp unix de la date du premier jour de la semaine
 */
function ploopi_numweek2unixtimestamp($intNumWeek, $intYear)
{
    // On va chercher quand commence la semaine 1 de l'année en cours dans le but de connaître le 1er jour de n'importe quelle semaine de l'année
    // 1. On se positionne qques jours avant la fin de l'année (la semaine 1 commence au plus tôt le 29/12 : cf ISO-8601)
    $date_firstweek = mktime(0, 0, 0, 12, 29, $intYear - 1);
    $d = -1;
    do
    {
        $d++;
        $date_firstweek = mktime(0, 0, 0, 12, 29 + $d, $intYear - 1);

    } while (date('W', $date_firstweek) != 1); // Tant qu'on n'est pas sur la semaine 1

    // $date_firstweek contient le 1er jour de la semaine 1

    // 2. On ajoute ($intSelWeek-1)*7 jours pour se positionner sur le 1er jour de la semaine $intSelWeek
    return(mktime(0, 0, 0, 12, 29 + $d + (($intNumWeek - 1) * 7), $intYear - 1));
}


/**
 * Retourne un booléen indiquant si une date correspond à un jour férié
 *
 * @copyright Olravet (http://olravet.fr/)
 *
 * @param int $timestamp date au format timestamp unix
 * @return boolean true si le jour est férié
 */

function ploopi_holiday($timestamp)
{
    $intDay = date("d", $timestamp);
    $intMonth = date("m", $timestamp);
    $intYear = date("Y", $timestamp);

    $booIsHoliday = false;

    // dates fériées fixes
    if($intDay == 1 && $intMonth == 1) $booIsHoliday = true; // 1er janvier
    elseif($intDay == 1 && $intMonth == 5) $booIsHoliday = true; // 1er mai
    elseif($intDay == 8 && $intMonth == 5) $booIsHoliday = true; // 8 mai
    elseif($intDay == 14 && $intMonth == 7) $booIsHoliday = true; // 14 juillet
    elseif($intDay == 15 && $intMonth == 8) $booIsHoliday = true; // 15 aout
    elseif($intDay == 1 && $intMonth == 11) $booIsHoliday = true; // 1 novembre
    elseif($intDay == 11 && $intMonth == 11) $booIsHoliday = true; // 11 novembre
    elseif($intDay == 25 && $intMonth == 12) $booIsHoliday = true; // 25 décembre

    // fêtes religieuses mobiles

    $pak = easter_date($intYear);
    $jp = date("d", $pak);
    $mp = date("m", $pak);
    if($jp == $intDay && $mp == $intMonth) { $booIsHoliday = true;} // Pâques

    $lpk = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) +1, date("Y", $pak) );
    $jp = date("d", $lpk);
    $mp = date("m", $lpk);
    if($jp == $intDay && $mp == $intMonth){ $booIsHoliday = true; } // Lundi de Pâques

    $asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 39, date("Y", $pak) );
    $jp = date("d", $asc);
    $mp = date("m", $asc);
    if($jp == $intDay && $mp == $intMonth){ $booIsHoliday = true;} //ascension

    $pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 49, date("Y", $pak) );
    $jp = date("d", $pe);
    $mp = date("m", $pe);
    if($jp == $intDay && $mp == $intMonth) {$booIsHoliday = true;} // Pentecôte

    $lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 50, date("Y", $pak) );
    $jp = date("d", $lp);
    $mp = date("m", $lp);
    if($jp == $intDay && $mp == $intMonth) {$booIsHoliday = true;} // lundi Pentecôte

    return $booIsHoliday;
}

/**
 * Crée le timestamp MYSQL (AAAAMMJJhhmmss) de la date actuelle pour un fuseau horaire donné (par défaut UTC+0)
 *
 * @param string $timezone_name identifiant du fuseau horaire (par défaut 'UTC') ou 'user' ou 'server'
 * @return timestamp timestamp au format MYSQL
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @link http://fr.php.net/timezones
 * @see timezone_identifiers_list
 */

function ploopi_tz_createtimestamp($timezone_name = 'UTC')
{
    switch($timezone_name)
    {
        case 'user':
            $timezone_name = $_SESSION['ploopi']['user']['timezone'];
        break;

        case 'server':
            $timezone_name = $_SESSION['ploopi']['timezone'];
        break;
    }

    return(date_format(date_create(null, timezone_open($timezone_name)), _PLOOPI_TIMESTAMPFORMAT_MYSQL));
}

/**
 * Convertit un timestamp MYSQL (AAAAMMJJhhmmss) d'un fuseau à un autre
 *
 * @param string $ts timestamp au format MYSQL
 * @param unknown_type identifiant du fuseau horaire d'origine ou 'user' ou 'server'
 * @param unknown_type identifiant du fuseau horaire de destination ou 'user' ou 'server'
 * @return string timestamp au format MYSQL AAAAMMJJHHMMSS
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @link http://fr.php.net/timezones
 * @see timezone_identifiers_list
 */

function ploopi_tz_timestamp2timestamp($ts, $timezone_name_src = 'UTC', $timezone_name_dst = 'UTC')
{
    if (empty($ts)) return $ts;

    switch($timezone_name_src)
    {
        case 'user':
            $timezone_name_src = $_SESSION['ploopi']['user']['timezone'];
        break;

        case 'server':
            $timezone_name_src = $_SESSION['ploopi']['timezone'];
        break;
    }

    switch($timezone_name_dst)
    {
        case 'user':
            $timezone_name_dst = $_SESSION['ploopi']['user']['timezone'];
        break;

        case 'server':
            $timezone_name_dst = $_SESSION['ploopi']['timezone'];
        break;
    }

    $default_tz = date_default_timezone_get();

    // on cherche les 2 fuseaux
    ploopi_unset_error_handler();
    $tz_dst = timezone_open($timezone_name_dst);
    ploopi_set_error_handler();


    // on parse le timestamp 'mysql' pour créer un timestamp unix
    if (!preg_match(_PLOOPI_TIMESTAMPFORMAT_MYSQL_EREG, $ts, $tsregs)) $tsregs = array(0,0,0,0,0,0,0);

    // on crée l'objet date sur le fuseau source
    date_default_timezone_set($timezone_name_src);
    $date = date_create('@'.mktime($tsregs[4], $tsregs[5], $tsregs[6], $tsregs[2], $tsregs[3], $tsregs[1]));
    date_default_timezone_set($default_tz);

    /* BUG ?? ne fonctionne pas
     * $date = date_create('@'.mktime($tsregs[4], $tsregs[5], $tsregs[6], $tsregs[2], $tsregs[3], $tsregs[1]), $tz_src);
     */

    if ($tz_dst !== false)
    {
        // changement de fuseau horaire (dest)
        date_timezone_set($date, $tz_dst);

        // on renvoie la date formatée timestamp mysql
        return(date_format($date, _PLOOPI_TIMESTAMPFORMAT_MYSQL));
    }
    else return false;
}

/**
 * Renvoie une chaine indiquant le décalage d'un fuseau par rapport à l'heure UTC. Ex : UTC +02:00
 *
 * @param string identifiant du fuseau horaire ou 'user' ou 'server'
 * @return string chaine UTC formatée
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @link http://fr.php.net/timezones
 * @see timezone_identifiers_list
 */

function ploopi_tz_getutc($timezone_name = 'UTC')
{
    switch($timezone_name)
    {
        case 'user':
            $timezone_name = $_SESSION['ploopi']['user']['timezone'];
        break;

        case 'server':
            $timezone_name = $_SESSION['ploopi']['timezone'];
        break;
    }

    ploopi_unset_error_handler();
    $objDateTimeZone = timezone_open($timezone_name);
    ploopi_set_error_handler();

    if ($objDateTimeZone !== false) return('UTC '.date_format(date_create(null, $objDateTimeZone), "P"));
    else return(false);
}

/**
 * Affiche une petite image de calendrier qui permet d'ouvrir le calendrier/popup de choix d'une date
 *
 * @param string $strInputFieldId identifiant du champ input associé
 */
function ploopi_open_calendar($strInputFieldId, $booEcho = true, $strClass = null, $strStyle = null)
{
    $strScript = $_SESSION['ploopi']['mode'] == 'backoffice' ? 'admin' : 'index';

    $strClass = $strClass == null ? '' : " class=\"{$strClass}\"";
    $strStyle = $strStyle == null ? '' : " style=\"{$strStyle}\"";

    $strEcho = "
        <a href=\"javascript:void(0);\" onclick=\"javascript:ploopi_xmlhttprequest_topopup(192, event, 'ploopi_popup_calendar', '{$strScript}-light.php?ploopi_op=calendar_open', 'selected_date='+$('{$strInputFieldId}').value+'&inputfield_id={$strInputFieldId}', 'POST', true);\" {$strClass}{$strStyle}>
        <img src=\"./img/calendar/calendar.gif\" />
        </a>
    ";

    if ($booEcho) echo $strEcho;
    else return $strEcho;
}

?>
