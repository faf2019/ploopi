<?php
/*
    Copyright (c) 2008-2009 Ovensia
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
 * Opérations du module
 *
 * @package weathertools
 * @subpackage op
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

if (ploopi_ismoduleallowed('weathertools'))
{
    switch($ploopi_op)
    {
        case 'weathertools_open_bulletin':
            ob_start();
            if (empty($_GET['weathertools_icao'])) ploopi_die();

            ploopi_init_module('weathertools', false, false, false);

            $strUrlMetarFiles = empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_metar_data_url']) ? '' : 'http://weather.noaa.gov/pub/data/observations/metar/stations';

            echo '<div style="padding:4px;">'.weathertools_get_metar_bulletin($strUrlMetarFiles, $_GET['weathertools_icao']).'</div>';

            $strContent = ob_get_contents();
            ob_end_clean();

            echo $skin->create_popup("Données météo pour &laquo; {$_GET['weathertools_icao']} &raquo;", $strContent, 'popup_weathertools_bulletin');
            ploopi_die();
        break;

        case 'weathertools_geoip_import':
            // Le fichier à télécharger est assez gros et le serveur assez lent...
            // http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
            set_time_limit(0);

            ploopi_init_module('weathertools', false, false, false);

            require_once 'HTTP/Request2.php';

            if(!empty($_POST['weather_urlgeoip']))
            {
                $objRequest = new HTTP_Request2($_POST['weather_urlgeoip']);

                if (_PLOOPI_INTERNETPROXY_HOST != '')
                {
                    $arrConfig['proxy_host'] = _PLOOPI_INTERNETPROXY_HOST;
                    $arrConfig['proxy_port'] = _PLOOPI_INTERNETPROXY_PORT;
                    $arrConfig['proxy_user'] = _PLOOPI_INTERNETPROXY_USER;
                    $arrConfig['proxy_password'] = _PLOOPI_INTERNETPROXY_PASS;
                    $arrConfig['proxy_auth_scheme'] = HTTP_Request2::AUTH_BASIC;
                    $objRequest->setConfig($arrConfig);
                }

                $objResponse = $objRequest->send();

                $strStatus = $objResponse->getStatus();

                if ($strStatus == '200' || $strStatus != '')
                {
                    // Création du dossier si n'existe pas
                    ploopi_makedir(WEATHERTOOLS_PATHDATA);

                    // On récupère le contenu du fichier
                    $ptrFp = fopen(WEATHERTOOLS_FILE, 'wb');
                    // On le dézippe et on enregistre
                    fwrite($ptrFp, file_get_contents('compress.zlib://data:who/cares;base64,'. base64_encode($objResponse->getBody())));
                    fclose($ptrFp);

                    ploopi_redirect("admin.php?weather_urlgeoip={$_POST['weather_urlgeoip']}&weather_geoip_import=1");
                }
                else ploopi_redirect("admin.php?error{$strStatus}&weather_urlgeoip={$_POST['weather_urlgeoip']}");
            }

            ploopi_redirect("admin.php?error");

        break;

        case 'weathertools_stations_import':
            ploopi_init_module('weathertools', false, false, false);

            require_once 'HTTP/Request2.php'; // PEAR
            include_once './modules/weathertools/classes/class_weathertools_station.php';

            if(!empty($_POST['weather_urlstations']))
            {
                $objRequest = new HTTP_Request2($_POST['weather_urlstations']);

                if (_PLOOPI_INTERNETPROXY_HOST != '')
                {
                    $arrConfig['proxy_host'] = _PLOOPI_INTERNETPROXY_HOST;
                    $arrConfig['proxy_port'] = _PLOOPI_INTERNETPROXY_PORT;
                    $arrConfig['proxy_user'] = _PLOOPI_INTERNETPROXY_USER;
                    $arrConfig['proxy_password'] = _PLOOPI_INTERNETPROXY_PASS;
                    $arrConfig['proxy_auth_scheme'] = HTTP_Request2::AUTH_BASIC;
                }

                $objResponse = $objRequest->send();

                $strStatus = $objResponse->getStatus();

                if ($strStatus == '200' || $strStatus != '')
                {
                    $intImport = 0;

                    // on vide la table des stations
                    $db->query('TRUNCATE ploopi_mod_weathertools_station');

                    // on récupère le contenu du fichier
                    $strFileContent = $objResponse->getBody();

                    $arrStations = preg_split("/\n/", $strFileContent);

                    foreach($arrStations as $strStation)
                    {
                        $arrStationDetail = preg_split('/;/', $strStation);

                        $arrStationDetail = array_merge($arrStationDetail, array_fill(0, 14, 0));

                        if (sizeof($arrStationDetail) >= 14)
                        {
                            $weather_station = new weathertools_station();

                            $weather_station->fields['icao'] = $arrStationDetail[0];
                            $weather_station->fields['block_number'] = $arrStationDetail[1];
                            $weather_station->fields['station_number'] = $arrStationDetail[2];
                            $weather_station->fields['place_name'] = $arrStationDetail[3];
                            $weather_station->fields['us_state'] = $arrStationDetail[4];
                            $weather_station->fields['country_name'] = $arrStationDetail[5];
                            $weather_station->fields['wmo_region'] = $arrStationDetail[6];
                            $weather_station->fields['station_latitude'] = $arrStationDetail[7];
                            $weather_station->fields['station_longitude'] = $arrStationDetail[8];
                            $weather_station->fields['upper_air_latitude'] = $arrStationDetail[9];
                            $weather_station->fields['upper_air_longitude'] = $arrStationDetail[10];
                            $weather_station->fields['station_elevation'] = $arrStationDetail[11];
                            $weather_station->fields['upper_air_elevation'] = $arrStationDetail[12];
                            $weather_station->fields['rbsn_indicateur'] = $arrStationDetail[13];

                            $weather_station->fields['station_latitude_wgs84'] = weathertools_nad83_to_wgs84($arrStationDetail[7]);
                            $weather_station->fields['station_longitude_wgs84'] = weathertools_nad83_to_wgs84($arrStationDetail[8]);

                            $weather_station->save();

                            $intImport++;
                        }
                        else ploopi_print_r($arrStationDetail);
                    }


                    ploopi_redirect("admin.php?weather_urlstations={$_POST['weather_urlstations']}&weather_station_import={$intImport}");
                }
                else ploopi_redirect("admin.php?error={$strStatus}&weather_urlstations={$_POST['weather_urlstations']}");
            }

            ploopi_redirect("admin.php?error");
        break;
    }
}

switch($ploopi_op)
{

    case 'weathertools_getmap':
        ploopi_init_module('weathertools', false, false, false);

        // On récupère le moduleid
        $moduleid = (isset($_REQUEST['weathertools_moduleid']) && is_numeric($_REQUEST['weathertools_moduleid'])) ? $_REQUEST['weathertools_moduleid'] : $_SESSION['ploopi']['moduleid'];

        // On vérifie que le typemap est valide et on envoie la carte
        if (isset($_REQUEST['weathertools_typemap']) && in_array($_REQUEST['weathertools_typemap'], array('vigilance', 'vigicrue'))) weathertools_getmap($_REQUEST['weathertools_typemap'], $moduleid, 1800);

        ploopi_die();
    break;
}
