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
 * Interface d'administration du module.
 *
 * @package weather
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('weather');


$op = (empty($_GET['op'])) ? '' : $_GET['op'];
$action = 'admin.php?op=weather_save';

include_once './modules/weather/class/class_weather.php';
$objWeather = new weather();

switch($op)
{
  case 'weather_save':
    if(!empty($_POST['weather_city'])
      && !empty($_POST['weather_codecity'])
      && (isset($_POST['weather_nbDays']) && is_numeric($_POST['weather_nbDays']))
      && !empty($_POST['weather_partnerid'])
      && !empty($_POST['weather_partnerkey']))
    {
      if(isset($_GET['id_weather']) && $_GET['id_weather'] > 0)
      {
        $objWeather->open($_GET['id_weather']);
        if($objWeather->fields['nbDays'] != $_POST['weather_nbDays'] ||
          $objWeather->fields['partnerid'] != $_POST['weather_partnerid'] ||
          $objWeather->fields['partnerkey'] != $_POST['weather_partnerkey'] ||
          $objWeather->fields['codecity'] != $_POST['weather_codecity'] ||
          $objWeather->fields['si'] != $_POST['weather_si'])
        {
          $objWeather->fields['datetime_update'] = 0;
        }
      }
      else
      {
        $objWeather->fields['datetime_update'] = 0;
      }
      $objWeather->setvalues($_POST,'weather_');
      $objWeather->save();
      break; // Break dans le {} comme ça si ça coince on fait default
    }
  default:
    $sql =  "
      SELECT  id
      FROM    ploopi_mod_weather
      WHERE   id_module = '{$_SESSION['ploopi']['moduleid']}'
      AND     id_workspace IN (".ploopi_viewworkspaces($_SESSION['ploopi']['moduleid']).")";
    $reqWeather = $db->query($sql);

    if($db->numrows($reqWeather))
    {
      $rowWeather = $db->fetchrow($reqWeather);
      $objWeather->open($rowWeather['id']);
      unset($rowWeather);
    }
    else
    {
      $objWeather->init_description();
    }
  break;
}

if(isset($objWeather->fields['id']) && $objWeather->fields['id'] > 0)
  $action .= '&id_weather='.$objWeather->fields['id'];

echo $skin->create_pagetitle('ADMINISTRATION DU MODULE METEO');
echo $skin->open_simplebloc(_WEATHER_PAGE_TITLE);
echo '<div style="padding: 5px 10px;">'._WEATHER_TEXT_EXPLAIN.'</div>';
echo $skin->close_simplebloc();

echo $skin->open_simplebloc(_WEATHER_PAGE_TITLE);

?>
<form name="form_weather" action="<? echo ploopi_urlencode($action); ?>" method="post" onsubmit="return weather_validate(this);">
<div class="ploopi_form">
  <div style="padding:2px;">
    <p>
      <label><? echo 'Ville'; ?>:</label>
      <input class="text" type="text" id="weather_city" name="weather_city" style="width:300px;" value="<? echo htmlentities($objWeather->fields['city']); ?>" tabindex="100" />
      <input type="button" class="button" value="<? echo _WEATHER_ADMIN_SEARCH_CITY; ?>" onclick="javascript:weather_search_city('weather_codecity','weather_city')" tabindex="101" />
    </p>
    <p>
      <label><? echo 'Code Ville'; ?>:</label>
      <input class="text" type="text" id="weather_codecity" name="weather_codecity" style="width:300px;" value="<? echo htmlentities($objWeather->fields['codecity']); ?>" tabindex="102" />
    </p>
    <p>
      <label><? echo 'Prévision sur'; ?>:</label>
      <select class="select" name="weather_nbDays" style="width:50px;" tabindex="103">
        <?
        for($intNbJour=0; $intNbJour<=5; $intNbJour++)
        {
          ?>
          <option value="<? echo $intNbJour; ?>" <? if ($objWeather->fields['nbDays'] == $intNbJour) echo 'selected="selected"'; ?>><? echo $intNbJour; ?></option>
          <?
        }
        ?>
      </select>&nbsp;Jour(s)
    </p>
    <p>
      <label><? echo 'Identifiant Partenaire'; ?>:</label>
      <input class="text" type="text" id="weather_partnerid" name="weather_partnerid" style="width:300px;" value="<? echo htmlentities($objWeather->fields['partnerid']); ?>" tabindex="104" />
    </p>
    <p>
      <label><? echo 'Clé Partenaire'; ?>:</label>
      <input class="text" type="text" id="weather_partnerkey" name="weather_partnerkey" style="width:300px;" value="<? echo htmlentities($objWeather->fields['partnerkey']); ?>" tabindex="105" />
    </p>
    <p>
      <label><? echo 'Système Unitaire'; ?>:</label>
      <select class="select" name="weather_si" style="width:150px;" tabindex="106">
          <option <? if ($objWeather->fields['si'] == 's') echo 'selected="selected"'; ?> value="s"><? echo 'Impérial'; ?></option>
          <option <? if ($objWeather->fields['si'] == 'm') echo 'selected="selected"'; ?> value="m"><? echo 'Métrique'; ?></option>
      </select>
    </p>
  </div>
</div>
<div style="padding:2px;text-align:right;">
  <input type="button" class="button" value="<? echo _PLOOPI_CANCEL; ?>" onclick="javascript:document.location.href='<? echo ploopi_urlencode('admin.php'); ?>';" tabindex="108" />
  <input type="reset" class="button" value="<? echo _PLOOPI_RESET; ?>" tabindex="109" />
  <input type="submit" class="button" value="<? echo _PLOOPI_SAVE; ?>" tabindex="107" />
</div>
</form>
<? echo $skin->close_simplebloc(); ?>