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
 * Interface publique
 *
 * @package weathertools
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

ploopi_init_module('weathertools');

echo $skin->create_pagetitle("{$_SESSION['ploopi']['modulelabel']} - Consultation des données météo (METAR)");
echo $skin->open_simplebloc('Recherche d\'une station météo');

$record = weathertools_iptolocation();

?>
<div style="padding:4px;">
    <?
    $strWeatherCountryName = empty($_REQUEST['weather_country_name']) ? '' : $_REQUEST['weather_country_name'];
    $strWeatherPlaceName = empty($_REQUEST['weather_place_name']) ? '' : $_REQUEST['weather_place_name'];
    ?>
    <form action="<? echo ploopi_urlencode("admin.php?op=recherche"); ?>" method="post">
    <div class=ploopi_form>
        <p>
            <label>Pays : </label>
            <?
            $rs = $db->query("SELECT distinct(country_name) as country_name FROM ploopi_mod_weathertools_station WHERE country_name != '' AND country_name != '0' ORDER BY country_name");
            ?>
            <select class="select" name="weather_country_name">
                <?
                // On propose la fonction "stations les plus proches" si GEOIP retourne quelquechose
                if (!empty($record->latitude))
                {
                    ?>
                    <option value="@" <? if ($strWeatherCountryName == '@') echo 'selected="selected"'; ?>>Stations les plus proches de <? printf("%s (%s)", $record->city, $record->country_name); ?></option>
                    <?
                }
                ?>
                <option value="#" <? if ($strWeatherCountryName == '#') echo 'selected="selected"'; ?>>(Tous les pays)</option>
                <?
                while ($row = $db->fetchrow($rs))
                {
                    ?>
                    <option <? if ($strWeatherCountryName == $row['country_name']) echo 'selected="selected"'; ?>><? echo $row['country_name']; ?></option>
                    <?
                }
                ?>
            </select>
        </p>
        <p>
            <label>Nom de station : </label>
            <input type="text" class="text" name="weather_place_name" value="<? echo ploopi_htmlentities($strWeatherPlaceName); ?>" />
        </p>
    </div>
    <div style="padding:4px;text-align:right;">
        <input type="submit" class="button" value="Chercher" />
    </div>
    </form>
    <?
    $arrStations = array();
    $booAutodetect = false;

    if ($strWeatherCountryName == '@' || (empty($strWeatherCountryName) && empty($strWeatherPlaceName))) // Autodetection
    {
        if (!empty($record))
        {
            $booAutodetect = true;
            $arrStations = weathertools_get_closest_station($record->latitude, $record->longitude, 20);
        }
    }
    elseif (!empty($strWeatherCountryName) || !empty($strWeatherPlaceName))
    {

        if ($strWeatherCountryName == '#') $strWeatherCountryName = '';

        if (!empty($strWeatherCountryName) || !empty($strWeatherPlaceName))
        {
            $arrWhere = array();

            if (!empty($strWeatherCountryName)) $arrWhere[] = "country_name LIKE '%".$db->addslashes($strWeatherCountryName)."%'";
            if (!empty($strWeatherPlaceName)) $arrWhere[] = "place_name LIKE '%".$db->addslashes($strWeatherPlaceName)."%'";

            // Affectation des données dans le tableau
            $rs = $db->query("SELECT * FROM ploopi_mod_weathertools_station WHERE ".implode(' AND ', $arrWhere));

            $arrStations = $db->getarray();
        }
    }

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

    $arrResult['columns']['right']['distance'] =
        array(
            'label' => 'Dist.',
            'width' => 70,
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


    foreach($arrStations as $row)
    {
        $intDistance = (empty($record->latitude)) ? 0 : ceil(weathertools_get_distance($record->latitude, $record->longitude, $row['station_latitude_wgs84'], $row['station_longitude_wgs84']));

        $arrResult['rows'][] =
            array(
                'values' =>
                    array(
                        'icao' => array('label' => $row['icao']),
                        'country_name' => array('label' => $row['country_name']),
                        'place_name' => array('label' => $row['place_name']),
                        'latitude' => array('label' => number_format($row['station_latitude_wgs84'], 3, ',', ' ')." °", 'sort_label' => sprintf("%08.3f", $row['station_latitude_wgs84']), 'style' => 'text-align:right;'),
                        'longitude' => array('label' => number_format($row['station_longitude_wgs84'], 3, ',', ' ')." °", 'sort_label' => sprintf("%08.3f", $row['station_longitude_wgs84']),  'style' => 'text-align:right;'),
                        'altitude' => array('label' => number_format($row['station_elevation'], 0, ',', ' ')." m", 'sort_label' => sprintf("%05d", $row['station_elevation']), 'style' => 'text-align:right;'),
                        'distance' => array('label' => empty($record->latitude) ? '-' : $intDistance.' km', 'sort_label' => sprintf("%05d", $intDistance), 'style' => 'text-align:right;')
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
                'orderby_default' => $booAutodetect ? 'distance' : 'place_name',
                'sort_default' => 'ASC'
            )
        );
        ?>
    </div>
</div>

<? echo $skin->close_simplebloc();

/*
ploopi_init_module('weathertools');

$strUrlMetarFiles = empty($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_metar_data_url']) ? '' : 'http://weather.noaa.gov/pub/data/observations/metar/stations';

$arrStations = weathertools_get_closest_station(48, 5, 5);

foreach($arrStations as $arrDetailStation)
{
    echo '<div style="padding:4px;"><strong>à '.floor($arrDetailStation['distance']).' km</strong></div>';
    echo '<div style="padding:4px;">'.weathertools_get_metar_bulletin($strUrlMetarFiles, $arrDetailStation['icao']).'</div>';
}
*/
?>
