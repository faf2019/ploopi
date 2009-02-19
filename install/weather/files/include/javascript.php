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
 * Fonctions javascript dynamiques
 *
 * @package weather
 * @subpackage javascript
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
?>

function weather_search_city(id_code,id_city)
{
    ploopi_showpopup('', 300, null, true, 'popup_weather_codecity');

    new Ajax.Updater(
      'popup_weather_codecity',
      '<?php echo ploopi_urlencode('admin-light.php?ploopi_op=weather_get_city'); ?>',
      {
        method: 'post',
        postBody: 'value='+$(id_city).value+'&id_code='+id_code+'&id_city='+id_city,
        onCreate: function() {
          ploopi_ajaxloader('popup_weather_codecity');
        },
        onSuccess : function() {
          new Draggable('popup_weather_codecity', { handle: 'handle_popup_weather_codecity'});
          new Draggable('popup_weather_codecity', { handle: 'handlebottom_popup_weather_codecity'});
          document.location.href='#anchor_popup_weather_codecity';
        }
      }
    );
}

