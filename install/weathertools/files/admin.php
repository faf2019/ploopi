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
 * Interface d'administration
 *
 * @package weathertools
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

ploopi_init_module('weathertools');

echo $skin->create_pagetitle("{$_SESSION['ploopi']['modulelabel']} - Administration");
echo $skin->open_simplebloc();
?>
<div style="padding:4px;">
    <?
    echo $skin->open_simplebloc('Import des stations météo');

    // URL par défaut du fichier contenant les stations météo (US National weather service / NOAA)
    $strUrlStations = empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_station_list_url']) ? "http://weather.noaa.gov/data/nsd_cccc.txt" : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_station_list_url'];
    if (!empty($_REQUEST['weather_urlstations'])) $strUrlStations = $_REQUEST['weather_urlstations'];

    if (!isset($_GET['error']) && isset($_GET['weather_station_import']))
    {
        ?>
        <div style="padding:4px;font-weight:bold;">
            Import du fichier effectué. <? echo $_GET['weather_station_import']; ?> station(s) traitées.
        </div>
        <?
    }
    else
    {
        $rs = $db->query('SELECT count(*) as c FROM ploopi_mod_weathertools_station');
        if ($db->numrows())
        {
            ?>
            <div style="padding:4px;font-weight:bold;">
                La base de données contient <? echo current($db->getarray($rs, true)); ?> station(s).
            </div>
            <?
        }
    }
    ?>

    <form action="<? echo ploopi_urlencode("admin-light.php?ploopi_op=weathertools_stations_import"); ?>" method="post">
    <div class=ploopi_form>
        <p>
            <label>Url du fichier (weather.noaa.gov) : </label>
            <input type="text" class="text" name="weather_urlstations" value="<? echo htmlentities($strUrlStations); ?>" />
        </p>
    </div>
    <div style="padding:4px;text-align:right;">
        <input type="reset" class="button" value="Réinitialiser" />
        <input type="submit" class="button" value="Importer" />
    </div>
    </form>
    <?
    echo $skin->close_simplebloc();


    echo $skin->open_simplebloc('Import des données GEOIP');

    // URL par défaut du fichier contenant les données GEOIP
    $strUrlGeoIP = empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_geoip_litecity_url']) ? "http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz" : $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_geoip_litecity_url'];
    if (!empty($_REQUEST['weather_urlgeoip'])) $strUrlGeoIP = $_REQUEST['weather_urlgeoip'];

    if (!isset($_GET['error']) && isset($_GET['weather_geoip_import']))
    {
        ?>
        <div style="padding:4px;font-weight:bold;">
            Import du fichier effectué.
        </div>
        <?
    }
    else
    {
        if (file_exists(WEATHERTOOLS_FILE))
        {
            ?>
            <div style="padding:4px;font-weight:bold;">
                La base de données GEOIP est présente et date du <? echo date('d/m/Y', filemtime(WEATHERTOOLS_FILE)); ?>. Le fichier pèse <? echo number_format(filesize(WEATHERTOOLS_FILE)/1024,0,'.',' '); ?> ko.
            </div>
            <?
        }
        else
        {
            ?>
            <div style="padding:4px;font-weight:bold;" class="error">
                La base de données GEOIP est absente.
            </div>
            <?
        }
    }
    ?>

    <form action="<? echo ploopi_urlencode("admin-light.php?ploopi_op=weathertools_geoip_import"); ?>" method="post">
    <div class=ploopi_form>
        <p>
            <label>Url du fichier GEOIP : </label>
            <input type="text" class="text" name="weather_urlgeoip" value="<? echo htmlentities($strUrlGeoIP); ?>" />
        </p>
    </div>
    <div style="padding:4px;text-align:right;">
        <input type="reset" class="button" value="Réinitialiser" />
        <input type="submit" class="button" value="Importer" />
    </div>
    </form>
    <?
    echo $skin->close_simplebloc();

    echo $skin->open_simplebloc('Recherche d\'une station météo');

    $strWeatherCountryName = empty($_REQUEST['weather_country_name']) ? '' : $_REQUEST['weather_country_name'];
    $strWeatherPlaceName = empty($_REQUEST['weather_place_name']) ? '' : $_REQUEST['weather_place_name'];
    ?>
    <form action="<? echo ploopi_urlencode("admin.php?op=recherche"); ?>" method="post">
    <div class=ploopi_form>
        <p>
            <label>Pays : </label>
            <input type="text" class="text" name="weather_country_name" value="<? echo htmlentities($strWeatherCountryName); ?>" />
        </p>
        <p>
            <label>Nom de station : </label>
            <input type="text" class="text" name="weather_place_name" value="<? echo htmlentities($strWeatherPlaceName); ?>" />
        </p>
    </div>
    <div style="padding:4px;text-align:right;">
        <input type="submit" class="button" value="Chercher" />
    </div>
    </form>
    <?
    if (!empty($strWeatherCountryName) || !empty($strWeatherPlaceName))
    {
        // Préparation du tableau d'affichage
        $arrResult =
            array(
                'columns' => array(),
                'rows' => array()
            );


        $arrResult['columns']['left']['icao'] =
            array(
                'label' => 'ICAO',
                'width' => 60,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['left']['country_name'] =
            array(
                'label' => 'Pays',
                'width' => 150,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['auto']['place_name'] =
            array(
                'label' => 'Station',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['latitude'] =
            array(
                'label' => 'Lat.',
                'width' => 70,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['longitude'] =
            array(
                'label' => 'Long.',
                'width' => 70,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['altitude'] =
            array(
                'label' => 'Alt.',
                'width' => 60,
                'options' => array('sort' => true)
            );

        $arrWhere = array();

        if (!empty($strWeatherCountryName)) $arrWhere[] = "country_name LIKE '%".$db->addslashes($strWeatherCountryName)."%'";
        if (!empty($strWeatherPlaceName)) $arrWhere[] = "place_name LIKE '%".$db->addslashes($strWeatherPlaceName)."%'";

        // Affectation des données dans le tableau
        $rs = $db->query("SELECT * FROM ploopi_mod_weathertools_station WHERE ".implode(' AND ', $arrWhere));

        while ($row = $db->fetchrow($rs))
        {
            $arrResult['rows'][] =
                array(
                    'values' =>
                        array(
                            'icao' => array('label' => $row['icao']),
                            'country_name' => array('label' => $row['country_name']),
                            'place_name' => array('label' => $row['place_name']),
                            'latitude' => array('label' => number_format($row['station_latitude_wgs84'], 3, ',', ' ')." °", 'sort_label' => sprintf("%08.3f", $row['station_latitude_wgs84']), 'style' => 'text-align:right;'),
                            'longitude' => array('label' => number_format($row['station_longitude_wgs84'], 3, ',', ' ')." °", 'sort_label' => sprintf("%08.3f", $row['station_longitude_wgs84']),  'style' => 'text-align:right;'),
                            'altitude' => array('label' => number_format($row['station_elevation'], 0, ',', ' ')." m", 'sort_label' => sprintf("%05d", $row['station_elevation']), 'style' => 'text-align:right;')
                        ),
                    'description' => "Afficher la météo pour '".$row['place_name']."'",
                    'link' => 'javascript:void(0);',
                    'onclick' => "weathertools_open_bulletin('{$row['icao']}', event);"
                );
        }


        ?>
        <div style="margin:2px;border:1px solid #a0a0a0;">
            <?
            // Affichage du tableau
            $skin->display_array(
                $arrResult['columns'],
                $arrResult['rows'],
                'weathertools_stations',
                array(
                    'sortable' => true,
                    'orderby_default' => 'place_name',
                    'sort_default' => 'ASC'
                )
            );
            ?>
        </div>
        <?
    }

    echo $skin->close_simplebloc();
    ?>
</div>

<? echo $skin->close_simplebloc(); ?>
