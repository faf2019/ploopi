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

function weather_change_info(id)
{
  if($('weather_detail_'+id).style.display == 'none')
  {
    // if(id == 'now') $('weather_info_'+id).style.display = 'none';
    $('weather_detail_'+id).style.display = 'block';
  }
  else
  {
    $('weather_detail_'+id).style.display = 'none';
    // if(id == 'now') $('weather_info_'+id).style.display = 'block';
  }
}

function weather_change_weather()
{
  if($('weather_detail_weather').style.display == 'none')
  {
    $('weather_info_now').style.display = 'none';
    $('weather_detail_now').style.display = 'none';
    $('weather_detail_weather').style.display = 'block';
  }
  else
  {
    $('weather_detail_weather').style.display = 'none';
    $('weather_info_now').style.display = 'block';
  }
}

function weather_transfert_city(id_code,id_city,val_code,val_city)
{
  $(id_code).value = val_code;
  $(id_city).value = val_city;
}