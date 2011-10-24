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
 * Classe d'acces au données de weather
 *
 * @package weather
 * @subpackage class
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_weather
 *
 * @package weather
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class weather extends data_object
{
  private $strUrl = 'http://xoap.weather.com/weather/local/{CITY}?cc=*&unit={SI}&dayf={NBDAY}&link=xoap&prod=xoap&par={PARTNER_ID}&key={PARTNER_KEY}&local=fr';

  private $strPartnerID;// = '1085685494';
  private $strPartnerKey;// = 'ec326f768fb13eca';
  private $strCity;// = 'FRXX0115';
  private $strSI;// = 'm'; // 's'
  private $intNdDays;// = 5;

  private $error;
  private $error_msg = '';
  private $charset = 'UTF-8';
  private $header = array();
  private $arrTranslate = array();

  private $arrData = 'old';
  private $strDateUpdate = '';
  private $strTextCity = '';

  /**
   * Contructeur de la classe
   *
   * @return weather
   */
  function weather($moduleid = -1)
  {
    global $db;

    $this->error = true;

    parent::data_object('ploopi_mod_weather', 'id');

    if($moduleid > 0)
    {
      $groups = ploopi_viewworkspaces($moduleid,'weather');

      $reqOpen = $db->query("SELECT id FROM ploopi_mod_weather WHERE id_module = '{$moduleid}' AND id_workspace IN ($groups)");

      if($db->numrows($reqOpen))
      {
        $fields = $db->fetchrow($reqOpen);
        if($this->open($fields['id']))
        {
          $this->error = false;
          //Passage des params
          $this->set_partnerID($this->fields['partnerid']);
          $this->set_partnerKey($this->fields['partnerkey']);
          $this->set_city($this->fields['codecity']);
          $this->set_SI($this->fields['si']);
          $this->set_nbDays($this->fields['nbDays']);
        }
      }

      if(defined('_WEATHER_TRANSLATE'))
      {
        $arrList = explode(';',_WEATHER_TRANSLATE);
        foreach($arrList as $tmpdata)
        {
          $arrWord = explode(':',trim($tmpdata));
          $this->arrTranslate[strtolower($arrWord[0])] = $arrWord[1];
        }
      }
    }
  }

  /*
   * SET
   */
  private function set_url($myUrl) { if(!empty($myUrl)) $this->strUrl = $myUrl; }

  private function set_partnerID($myId) { if(!empty($myId)) $this->strPartnerID = $myId; }
  private function set_partnerKey($myKey) { if(!empty($myKey)) $this->strPartnerKey = $myKey; }
  private function set_city($myCity) { if(!empty($myCity)) $this->strCity = $myCity; }
  private function set_SI($mySI) { if(strtolower($mySI) == 's' || strtolower($mySI) == 'm') $this->strSI = strtolower($mySI); }
  private function set_nbDays($myNdDays) { if(is_numeric($myNdDays) && $myNdDays >= 0) $this->intNdDays = $myNdDays; }
  private function set_textCity($myTextCity) { if(!empty($myTextCity)) $this->strTextCity = $myTextCity; }
  private function set_dateUpdate($myDateUpdate)
  {
    // reçu : 12/15/08 7:00 AM Local Time
    if(preg_match("/^([0-1]{0,1}[0-9])\/([0-3]{0,1}[0-9])\/([0-9]{2})[[:space:]]([0-9]{1,2}):([0-9]{2})[[:space:]]([[:alpha:]]{2})/",$myDateUpdate,$arrRegExp))
    {
      if($arrRegExp[6] == 'PM') $arrRegExp[4] = $arrRegExp[4]+12;
      $this->strDateUpdate = date("YmdHis", mktime($arrRegExp[4],$arrRegExp[5],0,$arrRegExp[1],$arrRegExp[2],$arrRegExp[3]));
    }
    else
    {
      $this->strDateUpdate =  $myDateUpdate;
    }
  }

  /*
   * GET
   */
  public function get_url() { return $this->strUrl; }
  public function get_partnerID() { return $this->strPartnerID; }
  public function get_partnerKey() { return $this->strPartnerKey; }
  public function get_city() { return $this->strCity; }
  public function get_SI() { return $this->strSI; }
  public function get_nbDays() { return $this->intNdDays; }

  public function get_textCity() { return $this->strTextCity; }
  public function get_dateUpdate() { return $this->strDateUpdate; }

  /**
   * Surcharge de la méthode de sauvegarde. Ajout des param id_module, id_workspace, id_user
   *
   */
  public function save($withUWM = false)
  {
    if($withUWM) $this->setuwm();

    parent::save();
  }

  /**
   * Convertit une chaîne UTF8 en ISO-8859-1//TRANSLIT.
   * Méthode notamment utile pour traiter le contenu des flux RSS.
   * Une conversion "classique" UTF8 => ISO-8859-1 ne suffit pas.
   *
   * @param string $str chaîne UTF8 à convertir
   * @return string chaîne ISO-8859-1
   */
  function _convertstr($str)
  {
    $str = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
    return($str);
  }

  /**
   * Récupère le contenu du flux (via proxy si nécessaire) et le parse
   *
   * @param int $moduleid identifiant du module
   * @param string $srcenc codage source (flux)
   * @param string $tgtenc codage destination (ce qu'on veut)
   * @return xmlrss
   */
  function get_xmlweather()
  {
    if(!$this->error) // pas chargé on vire
    {
      //On va recupérer le xml si : on a changé de ville d'info, on a pas rcup depuis N min, on a pas d'info en cache
      //
      $test = false; //($_SERVER['SERVER_ADDR'] == '127.0.0.1');
      if(!$test && (ploopi_timestamp_add($this->fields['datetime_update'], 0, 30) <= ploopi_createtimestamp() || empty($this->fields['data'])))
      {
        ploopi_unset_error_handler();
        require_once 'HTTP/Request.php';
        ploopi_set_error_handler();
        require_once './include/classes/xml2array.php';

        $arrStrFind = array('{CITY}', '{SI}', '{NBDAY}', '{PARTNER_ID}', '{PARTNER_KEY}');
        $arrStrRepl = array($this->get_city(), $this->get_SI(), $this->get_nbDays(), $this->get_partnerID(), $this->get_partnerKey());

        $url = str_replace($arrStrFind,$arrStrRepl,$this->strUrl);

        if ($url == '')
        {
          $this->error = true;
          $this->error_msg = sprintf("Erreur url vide");
        }

        if (!$this->error)
        {
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
            $this->header = $request->getResponseHeader();

            // Détection de l'encoding dans le header HTTP
            foreach(explode(';',$this->header['content-type']) as $sp)
            {
                $detail = explode('=',$sp);
                if (!empty($detail[0]) && !empty($detail[1]) && strtolower(trim($detail[0])) == 'charset') $this->charset = strtoupper($detail[1]);
            }

            if ($request->getResponseCode() != '200' && $request->getResponseCode() != false)
            {
                $this->error = true;
                $this->error_msg = sprintf("Erreur HTTP %s",$request->getResponseCode());
            }
            else
            {
              $this->content = $request->getResponseBody();

              // Détection de l'encoding dans le source XML
              if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $this->content, $m)) $this->charset = strtoupper($m[1]);

              $this->objXML_R = new XMLReader();
              $this->objXML_R->XML($this->content);

              $this->arrData = array();
              $this->get_part($this->arrData);

              $this->set_dateUpdate($this->arrData['cc']['lsup']);
              $this->set_textCity($this->arrData['cc']['obst']);

              $this->fields['datetime_update'] = ploopi_createtimestamp();
              $this->fields['city'] = $this->get_textCity();
              $this->save();
            }
          }
          else
          {
            $this->error = true;
            $this->error_msg = sprintf("Erreur HTTP %s : %s",$res->getCode(),$res->getMessage());
          }
        }
      }
      elseif($test) // Test avec un fichier
      {
        $this->content = file_get_contents('./modules/weather/weather.xml');

        $this->objXML_R = new XMLReader();
        $this->objXML_R->XML($this->content);
        $this->arrData = array();
        $this->get_part($this->arrData);

        $this->set_dateUpdate($this->arrData['cc']['lsup']);
        $this->set_textCity($this->arrData['cc']['obst']);

        $this->fields['datetime_update'] = ploopi_createtimestamp();
        $this->fields['city'] = $this->get_textCity();
        $this->save();
      }
      else
      {
        $this->arrData = 'old';
      }
    }
    //echo htmlentities($this->content);
  }

  public function get_prevision()
  {
    $arrPrevisions = false;

    if(!$this->error)
    {
      if($this->arrData !== 'old')
      {
        $arrGlb = $this->arrData['weather'];
        $arrDateUpdate = ploopi_timestamp2local($this->get_dateUpdate());
        //Current condition
        $arrdata = $this->arrData['cc'];
        $arrPrevisions['now'] = array(
          'lieu' => $arrdata['obst'],
          'maj' => $arrDateUpdate['date'].' '.$arrDateUpdate['time'],
          't' => $arrdata['tmp'].'°'.$arrGlb['head']['ut'],
          't_ressentie' => $arrdata['flik'].'°'.$arrGlb['head']['ut'],
          't_point_de_rosee' => $arrdata['dewp'].'°'.$arrGlb['head']['ut'],
          'text' => $this->translate($arrdata['t']),
          'icon' => sprintf("%02d", $arrdata['icon']),
          'visibilite' => $arrdata['vis'].$arrGlb['head']['ud'],
          'humidite' => $arrdata['hmid'].'%',
          'pression' => array(
            'val' => $arrdata['bar']['r'].$arrGlb['head']['up'],
            'direction' => $this->translate($arrdata['bar']['d'])
          ),
          'vent' => array(
            'vitesse' => $arrdata['wind']['s'].$arrGlb['head']['us'],
            'rafale' => $this->translate($arrdata['wind']['gust']),
            'direction_degres' => $arrdata['wind']['d'].'°',
            'direction_texte' => $this->translate($arrdata['wind']['t'])
          ),
          'UV' => array(
            'indice' => $arrdata['uv']['i'],
            'text' => $this->translate($arrdata['uv']['t'])
          ),
          'lune' => array(
            'icon' => sprintf("%02d", $arrdata['moon']['icon']),
            'text' => $this->translate($arrdata['moon']['t'])
          )
        );

        // Next
        $i = 0;
        while(isset($this->arrData['dayf']['day'.$i]) && is_array($this->arrData['dayf']['day'.$i]))
        {
          $arrdata = $this->arrData['dayf']['day'.$i];

          //Découpe la date (reçu : Dec 15)
          $arrDate = explode(' ',$arrdata['date']);

          // Découpe le text de la tendance
          $arrTextJour = $this->translate_tendance($arrdata['jour']['t']);
          $arrTextNuit = $this->translate_tendance($arrdata['nuit']['t']);

          $arrPrevisions[] = array(
            'nomjour' => $this->translate($arrdata['nomjour']),
            'date' => $arrDate[1].' '.$this->translate($arrDate[0]),
            't_haute' => $arrdata['hi'].'°'.$arrGlb['head']['ut'],
            't_basse' => $arrdata['low'].'°'.$arrGlb['head']['ut'],
            'soleil_leve' => $this->translate_time($arrdata['sunr']),
            'soleil_couche' => $this->translate_time($arrdata['suns']),
            'jour' => array(
              'icon' => sprintf("%02d", $arrdata['jour']['icon']),
              'text_AM' => $arrTextJour[0],
              'text_PM' => $arrTextJour[1],
              'risque_pluie' => $arrdata['jour']['ppcp'].'%',
              'humidite' => $arrdata['jour']['hmid'].'%',
              'vent' => array(
                'vitesse' => $arrdata['jour']['wind']['s'].$arrGlb['head']['us'],
                'rafale' => $this->translate($arrdata['jour']['wind']['gust']),
                'direction_degres' => $arrdata['jour']['wind']['d'].'°',
                'direction_texte' => $this->translate($arrdata['jour']['wind']['t'])
              ),
            ),
            'nuit' => array(
              'icon' => sprintf("%02d", $arrdata['nuit']['icon']),
              'text_AM' => $arrTextNuit[0],
              'text_PM' => $arrTextNuit[1],
              'risque_pluie' => $arrdata['nuit']['ppcp'].'%',
              'humidite' => $arrdata['nuit']['hmid'].'%',
              'vent' => array(
                'vitesse' => $arrdata['nuit']['wind']['s'].' '.$arrGlb['head']['us'],
                'rafale' => $this->translate($arrdata['nuit']['wind']['gust']),
                'direction_degres' => $arrdata['nuit']['wind']['d'].'°',
                'direction_texte' => $this->translate($arrdata['nuit']['wind']['t'])
              ),
            ),
          );
          $i++;
        }
        $this->fields['data'] = serialize($arrPrevisions);

        $this->save();
      }
      else
      {
        if(!empty($this->fields['data'])) $arrPrevisions = unserialize($this->fields['data']);
      }
    }
    return $arrPrevisions;
  }

  private function get_part(&$arrMyData)
  {
    while($this->objXML_R->read())
    {
      switch ($this->objXML_R->nodeType)
      {
        case XMLReader::ELEMENT:
          if($this->objXML_R->name != 'lnks') // Vire la pub
          {
            $init = '';
            $addNode = '';

            // jour de prévision
            if($this->objXML_R->name == 'day')
              $init = array('nomjour' => $this->objXML_R->getAttribute("t"), 'date' => $this->objXML_R->getAttribute("dt"));

            if($this->objXML_R->getAttribute("p") == 'd') // days (dans la journée)
              $node = 'jour';
            elseif($this->objXML_R->getAttribute("p") == 'n') // night (dans la nuit)
              $node = 'nuit';
            else
              $node = str_replace('XML-NUM-','',$this->objXML_R->name).$this->objXML_R->getAttribute("d");

            $arrMyData[$node] = $init;
            $this->Get_part($arrMyData[$node]);
          }
        break;
        case XMLReader::TEXT:
          $arrMyData = $this->objXML_R->value;
        break;
        case XMLReader::END_ELEMENT:
          return null;
        break;
        default:
        break;
      }
    }
  }

  private function translate($myStr)
  {
    $myStr = strtolower($myStr);
    return (array_key_exists($myStr,$this->arrTranslate)) ? $this->arrTranslate[$myStr] : $myStr;
  }

  private function translate_time($myHour)
  {
    if(preg_match("/^([0-9]{1,2}):([0-9]{2})[[:space:]]([[:alpha:]]{2})/",$myHour,$arrRegExp))
    {
      if($arrRegExp[3] == 'PM') $arrRegExp[1] = $arrRegExp[1]+12;
      $myHour = $arrRegExp[1].'h'.$arrRegExp[2];
    }
    return $myHour;
  }

  private function translate_tendance($myText)
  {
    $arrText = array('N/A','N/A');
    if(strpos($myText, 'AM ') !== false && strpos($myText, ' / PM ') !== false)
    {
      $arrTextTmp = explode(' / PM ',$myText);
      $arrText[0] = $this->translate(substr($arrTextTmp[0],3));
      $arrText[1] = $this->translate($arrTextTmp[1]);
      unset($arrTextTmp);
    }
    elseif(substr($myText,0,3) === 'AM ')
      $arrText[0] = $this->translate(substr($myText,3));
    elseif(substr($myText,0,3) === 'PM ')
      $arrText[1] = $this->translate(substr($myText,3));
    else
      $arrText[0] = $arrText[1] = $this->translate($this->translate($myText));

    return $arrText;
  }
}
?>
