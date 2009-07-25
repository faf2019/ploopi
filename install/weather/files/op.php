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
 * Traitement des OP de weather
 *
 * @package weather
 * @subpackage op
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

ploopi_init_module('weather');

switch ($ploopi_op)
{
  case 'weather_get_city':
    include_once './modules/weather/include/functions.php';

    ob_start();

    $listCity = get_city($_POST['value']);
    if(!is_array($listCity))
      echo $listCity;
    else
    {
      foreach($listCity as $code_city => $name_city)
      {
        ?>
        <li style="list-style: none; cursor: pointer;" onclick="javascript:weather_transfert_city('<?php echo $_POST['id_code'].'\',\''.$_POST['id_city']; ?>','<?php echo $code_city; ?>','<?php echo $name_city; ?>');ploopi_hidepopup('popup_weather_codecity')"><?php echo $name_city; ?></li>
        <?php
      }
    }
    $content = ob_get_contents();
    ob_end_clean();
    echo $skin->create_popup('Rechercher',$content,'popup_weather_codecity');
    ploopi_die();
  break;

  default:
  break;
}
?>