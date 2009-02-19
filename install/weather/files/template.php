<?php
/*
    Copyright (c) 2008 HeXad
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
 * Gestion des variables insérables dans le template frontoffice
 *
 * @package weather
 * @subpackage template
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('weather');

include_once './modules/weather/class/class_weather.php';

$objWeather = new weather($template_moduleid);

$objWeather->get_xmlweather();

$arrData = $objWeather->get_prevision();

if(isset($arrData) && is_array($arrData))
{
  foreach($arrData as $key => $data)
  {
    if($key === 'now') //Maintenant
    {
      $template_body->assign_block_vars('weather' , array(
        'LOGO_WEATHER_COM' => './modules/weather/img/TWClogo_61px.png',
        'CITY'        => $data['lieu'],
        'UPDATE'      => $data['maj'],
        'LARGE_PICT'  => './modules/weather/img/93/'.$data['icon'].'.png',
        'MEDIUM_PICT' => './modules/weather/img/61/'.$data['icon'].'.png',
        'SMALL_PICT'  => './modules/weather/img/31/'.$data['icon'].'.png',
        'TENDANCE'    => $data['text'],
        'TEMP'        => $data['t'],
        'FEEL'        => $data['t_ressentie'],
        'WIND'        => $data['vent']['vitesse'],
        'WIND_DIRECTION'=> $data['vent']['direction_texte'],
        'HUMIDITY'    => $data['humidite'],
        'VISIBILITY'  => $data['visibilite'],
        'UV'          => $data['UV']['indice'],
        'UV_TEXT'     => $data['UV']['text'],
        'PRESSURE'    => $data['pression']['val'],
        'PRESSURE_TENDANCE' => $data['pression']['direction'],
        'MOON_LARGE_PICT' => './modules/weather/img/93/'.$data['lune']['icon'].'.png',
        'MOON_MEDIUM_PICT'=> './modules/weather/img/61/'.$data['lune']['icon'].'.png',
        'MOON_SMALL_PICT' => './modules/weather/img/31/'.$data['lune']['icon'].'.png',
        'MOON_TEXT'       => $data['lune']['text'],

        'LIB_WEATHER_COM' => _WEATHER_TPL_WEATHER_COM,
        'LIB_CITY'        => _WEATHER_TPL_CITY,
        'LIB_UPDATE'      => _WEATHER_TPL_UPDATE,
        'LIB_TENDANCE'    => _WEATHER_TPL_TENDANCE,
        'LIB_TEMP'        => _WEATHER_TPL_TEMP,
        'LIB_TEMP_SHORT'  => _WEATHER_TPL_TEMP_SHORT,
        'LIB_FEEL'        => _WEATHER_TPL_FEEL,
        'LIB_WIND'        => _WEATHER_TPL_WIND,
        'LIB_WIND_DIRECTION'=> _WEATHER_TPL_WIND_DIRECT,
        'LIB_HUMIDITY'    => _WEATHER_TPL_HUMIDITY,
        'LIB_VISIBILITY'  => _WEATHER_TPL_VISIBILITY,
        'LIB_UV'          => _WEATHER_TPL_UV,
        'LIB_PRESSURE'    => _WEATHER_TPL_PRESSURE,
        'LIB_PRESSURE_TENDANCE'=> _WEATHER_TPL_PRESSURE_TENDANCE,
        'LIB_MOON_TEXT'   => _WEATHER_TPL_MOON,
        'LIB_DAWN'        => _WEATHER_TPL_DAWN,
        'LIB_DUSK'        => _WEATHER_TPL_DUSK,
        'LIB_MAXI'        => _WEATHER_TPL_MAXI,
        'LIB_MINI'        => _WEATHER_TPL_MINI,
        'LIB_RAIN_PROBAB' => _WEATHER_TPL_RAIN_PROB,
        'LIB_TEND_AM'     => _WEATHER_TPL_TEND_AM,
        'LIB_TEND_PM'     => _WEATHER_TPL_TEND_PM,
        'LIB_TEND_AM_NIGHT' => _WEATHER_TPL_TEND_AM_NIGHT,
        'LIB_TEND_PM_NIGHT' => _WEATHER_TPL_TEND_PM_NIGHT,
        )
      );

      if(isset($arrData[0]) && !empty($arrData[0]))
      {
        $template_body->assign_block_vars('weather.sun' , array(
          'DAWN'  => $arrData[0]['soleil_leve'],
          'DUSK'  => $arrData[0]['soleil_couche']
          )
        );
        $template_body->assign_block_vars('weather.night' , array(
          'TENDANCE'  => $arrData[0]['nuit']['text_AM'],
          'WIND'      => $arrData[0]['nuit']['vent']['vitesse'],
          'WIND_DIRECTION'  => $arrData[0]['nuit']['vent']['direction_texte'],
          'HUMIDITY'    => $arrData[0]['nuit']['humidite']
          )
        );

      }
    }
    else
    {
      // Si c'est aujourd'hui et qu'il plus de midi on donne la prévision pour la nuit
      if($key === 0 && time() > mktime('12'))
      {
        $template_body->assign_block_vars('weather.this_night' , array(
          'LARGE_PICT'  => './modules/weather/img/93/'.$data['nuit']['icon'].'.png',
          'MEDIUM_PICT' => './modules/weather/img/61/'.$data['nuit']['icon'].'.png',
          'SMALL_PICT'  => './modules/weather/img/31/'.$data['nuit']['icon'].'.png',
          'TEMP'        => $data['t_basse']
          )
        );
      }
      elseif($key === 0) // Il n'est pas midi on donne pour la journée
      {
        $template_body->assign_block_vars('weather.this_PM' , array(
            'LARGE_PICT'  => './modules/weather/img/93/'.$data['jour']['icon'].'.png',
            'MEDIUM_PICT' => './modules/weather/img/61/'.$data['jour']['icon'].'.png',
            'SMALL_PICT'  => './modules/weather/img/31/'.$data['jour']['icon'].'.png',
            'TEMP_DOWN'   => $data['t_basse'],
            'TEMP_UP'     => $data['t_haute'],
            'RAIN_PROBAB' => $data['jour']['risque_pluie']
          )
        );
      }
      else
      {
        $template_body->assign_block_vars('weather.preview' , array(
          'NAME_DAY' => $data['nomjour'],
          'DATE'     => $data['date'],
          'TEMP_UP'     => $data['t_haute'],
          'TEMP_DOWN'   => $data['t_basse'],
          'DAWN'  => $data['soleil_leve'],
          'DUSK'  => $data['soleil_couche']
          )
        );
        $template_body->assign_block_vars('weather.preview.days' , array(
          'LARGE_PICT'  => './modules/weather/img/93/'.$data['jour']['icon'].'.png',
          'MEDIUM_PICT' => './modules/weather/img/61/'.$data['jour']['icon'].'.png',
          'SMALL_PICT'  => './modules/weather/img/31/'.$data['jour']['icon'].'.png',
          'TENDANCE_AM' => $data['jour']['text_AM'],
          'TENDANCE_PM' => $data['jour']['text_PM'],
          'RAIN_PROBAB' => $data['jour']['risque_pluie'],
          'HUMIDITY'    => $data['jour']['humidite'],
          'WIND'      => $data['jour']['vent']['vitesse'],
          'WIND_DIRECTION'  => $data['jour']['vent']['direction_texte']
          )
        );
        $template_body->assign_block_vars('weather.preview.night' , array(
          'LARGE_PICT'  => './modules/weather/img/93/'.$data['nuit']['icon'].'.png',
          'MEDIUM_PICT' => './modules/weather/img/61/'.$data['nuit']['icon'].'.png',
          'SMALL_PICT'  => './modules/weather/img/31/'.$data['nuit']['icon'].'.png',
          'TENDANCE_AM' => $data['nuit']['text_AM'],
          'TENDANCE_PM' => $data['nuit']['text_PM'],
          'RAIN_PROBAB' => $data['nuit']['risque_pluie'],
          'HUMIDITY'    => $data['nuit']['humidite'],
          'WIND'      => $data['nuit']['vent']['vitesse'],
          'WIND_DIRECTION'  => $data['nuit']['vent']['direction_texte']
          )
        );
      }
    }
  }
}
?>

