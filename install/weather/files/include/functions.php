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
 * Fonction du module Weather
 *
 * @package weather
 * @subpackage functions
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

function get_city($nameCity)
{
  if(empty($nameCity)) return 'Pas de nom de ville à rechercher.';

  require_once 'HTTP/Request.php';
  require_once './include/classes/xml2array.php';

  $url = 'http://xoap.weather.com/search/search?where='.$nameCity;

  $request = new HTTP_Request($url, array('timeout' => 500));

  if (_PLOOPI_INTERNETPROXY_HOST != '')
  {
    $request->setProxy(
      _PLOOPI_INTERNETPROXY_HOST,
      _PLOOPI_INTERNETPROXY_PORT,
      _PLOOPI_INTERNETPROXY_USER,
      _PLOOPI_INTERNETPROXY_PASS
    );
  }

  $res = $request->sendRequest();

  if ($res == 1)
  {
    $header = $request->getResponseHeader();

    // Détection de l'encoding dans le header HTTP
    foreach(explode(';',$header['content-type']) as $sp)
    {
      $detail = explode('=',$sp);
      if (!empty($detail[0]) && !empty($detail[1]) && strtolower(trim($detail[0])) == 'charset') $charset = strtoupper($detail[1]);
    }

    if ($request->getResponseCode() != '200' && $request->getResponseCode() != false)
    {
      $content = sprintf("Erreur HTTP %s",$request->getResponseCode());
    }
    else
    {
      $content = $request->getResponseBody();
      // Détection de l'encoding dans le source XML
      if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $content, $m)) $charset = strtoupper($m[1]);

      $objXML_R = new XMLReader();
      $objXML_R->XML($content);
      get_part_city(&$objXML_R,&$arrResultTmp);
      if(is_array($arrResultTmp))
      {
        foreach($arrResultTmp as $data)
          $arrResult = $data;
      }

      if(empty($arrResult))
      {
        $arrResult = "Erreur. Pas de réponse pour cette recherche";
      }
    }
  }
  else
  {
    $arrResult = sprintf("Erreur HTTP %s : %s",$res->getCode(),$res->getMessage());
  }
  return $arrResult;
}

function get_part_city($objXML_R,$arrMyData)
{
  while($objXML_R->read())
  {
    switch ($objXML_R->nodeType)
    {
      case XMLReader::ELEMENT:
        if($objXML_R->name != 'lnks') // Vire la pub
        {
          $init = '';
          $node = $objXML_R->getAttribute("id");

          $arrMyData[$node] = $init;
          get_part_city(&$objXML_R,&$arrMyData[$node]);
        }
      break;
      case XMLReader::TEXT:
        $arrMyData = $objXML_R->value;
      break;
      case XMLReader::END_ELEMENT:
        return null;
      break;
      default:
      break;
    }
  }
}
?>