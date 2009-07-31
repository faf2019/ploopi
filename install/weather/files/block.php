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
 * Affichage du bloc de menu
 *
 * @package weather
 * @subpackage block
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('weather', true, true, false);

include_once './modules/weather/class/class_weather.php';

$objWeather = new weather($menu_moduleid);

$objWeather->get_xmlweather();

$arrData = $objWeather->get_prevision();

$htmlBlockWeather = '';
if(isset($arrData) && is_array($arrData))
{
  $htmlBlockWeather .= '<div id="weather_block">';
  foreach($arrData as $key => $data)
  {
    if($key === 'now') //Maintenant
    {
      $htmlBlockWeather .= '
        <div class="weather_title" onclick="javascript:weather_change_info(\'now\');">'.$data['lieu'].'</div>
        <div id="weather_info_now" class="weather_block_detail">
          <p class="weather_info">'._WEATHER_BLOC_MAJ.': '.$data['maj'].'</p>
          <div style="clear: both; text-align:center; cursor: pointer;" onclick="javascript:weather_change_info(\'now\');"><img src="./modules/weather/img/93/'.$data['icon'].'.png" style="padding:0; margin: 0;"></div>
          <div style="clear: both;"><p class="weather_tendance">'.$data['text'].'</p></div>
          <div style="clear: both; text-align: left; padding: 2px 0 0 4px;">
            <label>'._WEATHER_BLOC_TEMPER.' :</label> '.$data['t'].' ('._WEATHER_BLOC_FEEL.' '.$data['t_ressentie'].')<br/>
            <label>'._WEATHER_BLOC_WIND.':</label> '.$data['vent']['vitesse'].' '.$data['vent']['direction_texte'].'<br/>
            <label>'._WEATHER_BLOC_HUMID.':</label> '.$data['humidite'].'
            <div id="weather_detail_now" style="display: none;">';
              if(isset($arrData[0]) && !empty($arrData[0]))
              {
                $htmlBlockWeather .= '
                <label>'._WEATHER_BLOC_SUNUP.':</label> '.$arrData[0]['soleil_leve'].'<br/>
                <label>'._WEATHER_BLOC_SUNDOWN.':</label> '.$arrData[0]['soleil_couche'].'<br/>';
              }
              $htmlBlockWeather .= '
              <label>'._WEATHER_BLOC_VISIBILITY.':</label> '.$data['visibilite'].'<br/>
              <label>'._WEATHER_BLOC_UV.':</label> '.$data['UV']['indice'].' ('.$data['UV']['text'].')<br/>
              <label>'._WEATHER_BLOC_PRESURE.':</label> '.$data['pression']['val'].'<br/>('.$data['pression']['direction'].')
              <div class="weather_subtitle">'._WEATHER_BLOC_THIS_NIGHT.'</div>
              <div style="clear: both; text-align:center; cursor: pointer;" onclick="javascript:weather_change_info(\'now\');"><img src="./modules/weather/img/93/'.$data['lune']['icon'].'.png" style="padding:0; margin: 0;"></div>
              <div style="clear: both;"><p class="weather_tendance">'.$data['lune']['text'].'</p></div>';
              if(isset($arrData[0]) && !empty($arrData[0]))
              {
                $htmlBlockWeather .= '
                  <label>'._WEATHER_BLOC_TENDANCE.':</label> '.$arrData[0]['nuit']['text_AM'].'<br/>
                  <label>'._WEATHER_BLOC_WIND.':</label> '.$arrData[0]['nuit']['vent']['vitesse'].' '.$arrData[0]['nuit']['vent']['direction_texte'].'<br/>
                  <label>'._WEATHER_BLOC_HUMID.':</label> '.$arrData[0]['nuit']['humidite'].'<br/>';
              }
            $htmlBlockWeather .= '
            </div>
            <div class="weather_info" style="cursor: pointer;" onclick="javascript:weather_change_weather();">'._WEATHER_BLOC_SOURCE.' : weather.com</div>
          </div>
        </div>
        <div id="weather_detail_weather" class="weather_block_detail" style="display: none; text-align: center;">
          <div style="padding: 4px; margin: 0; clear: both;">'._WEATHER_BLOC_TEXT_WEATHER_COM.' :<a href="http://www.weather.com"><img src="./modules/weather/img/TWClogo_61px.png" style="padding:0; margin: 5px 0;"></a></div>
          <div style="padding: 4px; margin: 0; clear: both;"><a href="javascript:weather_change_weather();">'._WEATHER_BLOC_RETURN.'</a></div>
        </div>';
    }
    else
    {
      // Si c'est aujourd'hui et qu'il plus de midi on donne la prévision pour la nuit
      if($key === 0 && time() > mktime('12'))
      {
        $htmlBlockWeather .= '
        <div class="weather_title" style="cursor: default;">'._WEATHER_BLOC_THIS_NIGHT.'</div>
        <div class="weather_block_detail">
          <div style="margin: 0; padding: 0 2px; width:31px; float:left;"><img src="./modules/weather/img/31/'.$data['nuit']['icon'].'.png"></div>
          <div style="margin: 0; padding: 7px 0 0 0; float:left; text-align: left;">
            <label style="padding: 0; margin: 0; font-weight: bold;">'._WEATHER_BLOC_TEMPER.':</label> '.$data['t_basse'].'
          </div>
        </div>';
      }
      elseif($key === 0) // Il n'est pas midi on donne pour la journée
      {
        $htmlBlockWeather .= '
        <div class="weather_title" style="cursor: default;">'._WEATHER_BLOC_THIS_DAY.'</div>
        <div class="weather_block_detail">
          <div style="margin: 0; padding: 0 2px; width:31px; float:left;"><img src="./modules/weather/img/31/'.$data['jour']['icon'].'.png"></div>
          <div style="margin: 0; padding: 4px 0 0 0;">
            <label>'._WEATHER_BLOC_TEMPER.':</label> '.$data['t_basse'].' / '.$data['t_haute'].'<br/>
            <label>'._WEATHER_BLOC_RISQ_RAIN.':</label> '.$data['jour']['risque_pluie'].'<br/>
          </div>
        </div>';
      }
      else
      {
        $htmlBlockWeather .= '
        <div class="weather_title" onclick="javascript:weather_change_info('.$key.');">'.$data['nomjour'].' '.$data['date'].'</div>
        <div class="weather_block_detail">
          <div style="margin: 0; padding: 0 2px; width:31px; float:left; overflow: auto;"><img src="./modules/weather/img/31/'.$data['jour']['icon'].'.png"></div>
          <div style="margin: 0; padding: 4px 0 0 0;">
            <label>'._WEATHER_BLOC_TEMPER.':</label> '.$data['t_basse'].' / '.$data['t_haute'].'<br/>
            <label>'._WEATHER_BLOC_RISQ_RAIN.':</label> '.$data['jour']['risque_pluie'].'<br/>
          </div>
        </div>
        <div id="weather_detail_'.$key.'" style="display: none; text-align: left; margin: 0; padding: 4px;">
          <label>'._WEATHER_BLOC_SUN.':</label> '.$data['soleil_leve'].' - '.$data['soleil_couche'].'<br/>
          <label>'._WEATHER_BLOC_AM.':</label> '.$data['jour']['text_AM'].'<br/>
          <label>'._WEATHER_BLOC_PM.':</label> '.$data['jour']['text_PM'].'<br/>
          <label>'._WEATHER_BLOC_WIND.':</label> '.$data['jour']['vent']['vitesse'].' - '.$data['jour']['vent']['direction_texte'].'<br/>
        </div>';
      }
    }
  }
  $htmlBlockWeather .= '</div>';

  $block->addcontent($htmlBlockWeather);
}

if (ploopi_isactionallowed(-1,$_SESSION['ploopi']['workspaceid'],$menu_moduleid))
{
  $block->addmenu('<strong>'._WEATHER_ADMIN.'</strong>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=admin"));
}
?>