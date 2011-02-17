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
 * Fonctions, constantes, variables globales
 *
 * @package weathertools
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constates
 */

define ('WEATHERTOOLS_ACTION_ADMIN', 1);
define ('WEATHERTOOLS_ACTION_METEOCONSULT', 2);

define ('WEATHERTOOLS_PATHDATA', _PLOOPI_PATHDATA._PLOOPI_SEP.'weathertools');
define ('WEATHERTOOLS_FILE', WEATHERTOOLS_PATHDATA._PLOOPI_SEP.'GeoLiteCity.dat');

/**
 * Télécharge, gère la mise en cache et renvoie une carte de vigilance (format image)
 *
 * @param string $type_map type de la carte demandée
 * @param int $moduleid identifiant du module
 * @param int $cache_length durée de mise en cache en secondes
 * @return boolean true si l'image a pu être envoyée
 */

function weathertools_getmap($type_map, $moduleid = null, $cache_length = 1800)
{
	require_once 'HTTP/Request.php'; // PEAR
	include_once './include/classes/cache.php';

	$strUrl = '';
	
	if (!isset($moduleid)) $moduleid = $_SESSION['ploopi']['moduleid'];
	
	switch($type_map)
	{
		case 'vigicrue':
			$strUrl = $_SESSION['ploopi']['modules'][$moduleid]['weathertools_cartevigicrue_url'];
		break;
		
		default:
		case 'vigilance':
			$strUrl = $_SESSION['ploopi']['modules'][$moduleid]['weathertools_cartevigilance_url'];
		break;
	}
	
	$strCachePath = _PLOOPI_PATHDATA._PLOOPI_SEP."weathertools-{$moduleid}"._PLOOPI_SEP."cache";
	$strCacheFile = $strCachePath.$type_map;
	
	$intFileTs = filemtime("{$strCacheFile}.raw");
	
    $booCached = true;
    
	if ((!file_exists("{$strCacheFile}.raw") || (mktime() - $intFileTs > $cache_length)) && (!isset($_SESSION['weathertools'][$strCacheFile]) || mktime() >= $_SESSION['weathertools'][$strCacheFile]))
	{
	    $booCached = false;
	    
		$objRequest = new HTTP_Request("{$strUrl}?".ploopi_createtimestamp());
		
		if (_PLOOPI_INTERNETPROXY_HOST != '')
		{
			$objRequest->setProxy( 
				_PLOOPI_INTERNETPROXY_HOST,
				_PLOOPI_INTERNETPROXY_PORT,
				_PLOOPI_INTERNETPROXY_USER,
				_PLOOPI_INTERNETPROXY_PASS
			);
		}
        
		ploopi_unset_error_handler();
		$objPearError = $objRequest->sendRequest();
        ploopi_set_error_handler();
		
		if (!PEAR::isError($objPearError)) 
	    {
			if ($objRequest->getResponseCode() != '200' && $objRequest->getResponseCode() != '') return false;
			else 
			{ 
				ploopi_makedir($strCachePath);

				$ptrFd = fopen("{$strCacheFile}.raw", 'wb');
		 		fwrite($ptrFd, $objRequest->getResponseBody());
				fclose($ptrFd);
				
				$intFileTs = filemtime("{$strCacheFile}.raw");
			}
			
			unset($_SESSION['weathertools'][$strCacheFile]);
		}
		else // Problème de lecture du fichier
		{
		    $_SESSION['weathertools'][$strCacheFile] = mktime() + $cache_length;
		}
	}
	
	ploopi_ob_clean();
    
	ploopi_unset_error_handler();
	
    if (file_exists("{$strCacheFile}.raw"))
	{
    	weathertools_modifyimage(
    		"{$strCacheFile}.raw", 
    		"{$strCacheFile}.png", 
    		array(
                'label' => "{$type_map}: ".date('d/m/y H:i', $intFileTs),
    		    'fontsize' => 8.2,
    		),
    		array(
                'padding_left' => 0,
                'padding_right' => 0,
                'padding_top' => 0,
                'padding_bottom' => 20,
                'width' => isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_carte'.$type_map.'_size']) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_carte'.$type_map.'_size'] : 130,
                'height' => isset($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_carte'.$type_map.'_size']) ? $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['weathertools_carte'.$type_map.'_size']+20 : 150,
    		)
    	);
	}
	if (file_exists("{$strCacheFile}.png"))
	{
        header("Content-Type: image/png");
	    readfile("{$strCacheFile}.png");
	}
    
	ploopi_set_error_handler();
	
	return true;
}

/**
 * Met en forme une (mini)carte de vigilance
 *
 * @param string $input_file chemin vers le fichier image brut
 * @param string $output_file chemin vers le fichier image modifié
 * @param array $arrLabel paramètres du libellé
 * @param array $arrOptions options d'affichage
 * @return boolean true si l'image a pu être envoyée
 */

function weathertools_modifyimage($input_file, $output_file, $arrLabel = null, $arrOptions = null)
{
	if (!empty($arrLabel))
	{
		$label = empty($arrLabel['label']) ? '' : $arrLabel['label'];
		$vertical_align = empty($arrLabel['vertical_align']) ? 'bottom' : $arrLabel['vertical_align'];
		$horizontal_align = empty($arrLabel['horizontal_align']) ? 'right' : $arrLabel['horizontal_align'];
		$margin_left = empty($arrLabel['margin_left']) ? 0 : $arrLabel['margin_left'];
		$margin_right = empty($arrLabel['margin_right']) ? 0 : $arrLabel['margin_right'];
		$fontsize = empty($arrLabel['fontsize']) ? 8 : $arrLabel['fontsize'];
		$fontfile = empty($arrLabel['fontfile']) ? './modules/weathertools/TAHOMA.TTF' : $arrLabel['fontfile'];
		$text_bgcolor = ploopi_color_hex2rgb(empty($arrLabel['text_bgcolor']) ? '#ffffff' : $arrLabel['text_bgcolor']);
		$text_fgcolor = ploopi_color_hex2rgb(empty($arrLabel['text_fgcolor']) ? '#000000' : $arrLabel['text_fgcolor']);
	}
	
    $padding_left = $padding_right = empty($arrOptions['horizontal_padding']) ? 0 : $arrOptions['horizontal_padding'];
    $padding_top = $padding_bottom = empty($arrOptions['vertical_padding']) ? 0 : $arrOptions['vertical_padding'];
	
    $padding_left = empty($arrOptions['padding_left']) ? $padding_left : $arrOptions['padding_left'];
    $padding_right = empty($arrOptions['padding_right']) ? $padding_right : $arrOptions['padding_right'];
    $padding_top = empty($arrOptions['padding_top']) ? $padding_top : $arrOptions['padding_top'];
    $padding_bottom = empty($arrOptions['padding_bottom']) ? $padding_bottom : $arrOptions['padding_bottom'];
    
    $width = empty($arrOptions['width']) ? 0 : $arrOptions['width'];
	$height = empty($arrOptions['height']) ? 0 : $arrOptions['height'];

    $input_extension = ploopi_file_getextension($input_file);
    
    // si l'extension du fichier n'est pas "parlante", on tente de récupérer le format dans les infos du fichier
    if (!in_array($input_extension, array('jpg', 'jpeg', 'png', 'gif')))
    {
    	$arrImageInfo = @getimagesize($input_file);
    	if (isset($arrImageInfo['mime']))
    	{
    		if (strstr($arrImageInfo['mime'], 'gif') !== false) $input_extension = 'gif';
    		elseif (strstr($arrImageInfo['mime'], 'png') !== false) $input_extension = 'png';
    		elseif (strstr($arrImageInfo['mime'], 'jpg') !== false) $input_extension = 'jpg';
    		elseif (strstr($arrImageInfo['mime'], 'jpeg') !== false) $input_extension = 'jpeg';
    	}
	}

	// Ouverture de l'image source
    switch($input_extension)
    {
        case 'jpg':
        case 'jpeg':
          $imgsrc = ImageCreateFromJPEG($input_file);
        break;

        case 'png':
          $imgsrc = ImageCreateFromPng($input_file);
        break;

        case 'gif':
          $imgsrc = imagecreatefromgif($input_file);
        break;

        default: // format en entrée non supporté
          return false;
        break;
    }

	// Taille de l'image source
    $w = imagesx($imgsrc);
    $h = imagesy($imgsrc);

	
	// Couleur de fond de l'image source
	imagecolortransparent($imgsrc, imagecolorat($imgsrc, 1, 1));
	$arrBgColor = imagecolorsforindex($imgsrc, imagecolorat($imgsrc, 1, 1));

	if (empty($width)) $width = $w;
	if (empty($height)) $height = $h;

	$imgdest = imagecreatetruecolor($width, $height);
	imagealphablending($imgdest, false);
	imagesavealpha($imgdest, true);

    // On applique une couleur de fond (imagefill ne fonctionne pas avec une couche alpha)
	imagefilledrectangle($imgdest, 0, 0, imagesx($imgdest), imagesy($imgdest), imagecolorallocatealpha($imgdest, $arrBgColor['red'], $arrBgColor['green'], $arrBgColor['blue'], $arrBgColor['alpha']));
    // Copie/redimension de l'image
	imagecopyresampled  ($imgdest, $imgsrc, $padding_left, $padding_top, 0, 0, $width - ($padding_left+$padding_right), $height - ($padding_top+$padding_bottom), $w ,$h);
	
    // Ecriture du texte sur l'image
    $textcolor1 = imagecolorallocate($imgdest, $text_bgcolor[0], $text_bgcolor[1], $text_bgcolor[2]);
    $textcolor2 = imagecolorallocate($imgdest, $text_fgcolor[0], $text_fgcolor[1], $text_fgcolor[2]);
	
    $label_array = ploopi_image_wordwrap($label, $width - $margin_left - $margin_right, $fontsize, $fontfile);
	
	$text_h = $label_array['lineheight'] * count($label_array['lines']);
	$text_w = $label_array['textwidth'];


	switch($vertical_align)
	{
        case 'top':
        		$posY_font = 0;
        break;
        case 'bottom':
        		$posY_font = $height - $text_h;
        break;
        case 'center':
        		$posY_font = ($height - $text_h) / 2;
        break;
	}

	foreach($label_array['lines'] as $line)
	{
        $text = $line['text'];
        switch($horizontal_align)
        {
            case 'left':
            		$posX_font = $margin_left;
            break;
            case 'right':
            		$posX_font = $width - $line['width'] - $margin_right;
            break;
            case 'center':
            		$posX_font = $margin_left + ($width - $margin_left - $margin_right - $line['width']) / 2;
            break;
        }
        
        $posY_font += $fontsize+3;
        imagettftext($imgdest, $fontsize, 0, $posX_font-1, $posY_font-1, $textcolor1, $fontfile, $text );
        imagettftext($imgdest, $fontsize, 0, $posX_font, $posY_font, $textcolor2, $fontfile, $text );
	}
	
	// On rend le fond transparent (pixel en 1,1)
	imagecolortransparent($imgdest, imagecolorat($imgdest, 1, 1));

	// Ecriture de l'image finale
    $output_extension = ploopi_file_getextension($output_file);
    
	$output_path = dirname($output_file);
	$exists = file_exists($output_file);
	if (is_writable($output_path) && (!$exists || ($exists && is_writable($output_file))))
	{
		switch($output_extension)
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($imgdest, $output_file);
			break;

			case 'png':
				imagepng($imgdest, $output_file);
			break;

			case 'gif':
				imagepng($imgdest, $output_file);
			break;

			default:
				return false;
			break;
		}
	}
	else return false;

}

/**
 * Convertit une coordonnée du type NAD83 (30-24-07S) en WGS84 (-30,401944444)
 *
 * @param string $nad83 coordonnées de type NAD83
 * @return string coordonnées de type WGS84
 */ 
function weathertools_nad83_to_wgs84($nad83)
{
	if ($nad83 == '') return '';
	
	// Extraction des composantes
	$arrNad83 = preg_split('/-/', substr($nad83, 0, -1));
	
	// Conversion en wgs84
	return ( intval($arrNad83[0], 10) + (intval($arrNad83[1], 10) * 60 + ( isset($arrNad83[2]) ?  intval($arrNad83[2], 10) : 0)) / 3600 ) * ( in_array(substr($nad83, -1), array('S', 'W')) ? -1 : 1 );
}


/**
 * Retourne la(les) station(s) la(les) plus proche(s) d'un point donné
 *
 * @param float $latitude latitude en degré (WGS84) du point à considérer
 * @param float $longitude longitude en degré (WGS84) du point à considérer
 * @param int $nb nombre de stations à renvoyer
 * @param float $degree_range périmètre de recherche en degré
 * @return array tableau contenant une liste de stations (dont code ICAO)
 */

function weathertools_get_closest_station($latitude, $longitude, $nb = 1, $degree_range = 10)
{
	global $db;
	
	$v1 = cos(deg2rad($latitude));
	$v2 = deg2rad($longitude);
	$v3 = sin(deg2rad($latitude));
	
	// recherche des stations les plus proches (calcul de distance
	$db->query("
		SELECT 		*, 
					6366*ACOS({$v1}*COS(RADIANS(station_latitude_wgs84))*COS(RADIANS(station_longitude_wgs84)-{$v2})+{$v3}*SIN(RADIANS(station_latitude_wgs84))) as distance
		
		FROM 		ploopi_mod_weathertools_station 
		WHERE		ABS(station_latitude_wgs84 - {$latitude}) < {$degree_range}
		AND			ABS(station_longitude_wgs84 - {$longitude}) < {$degree_range}
		
		ORDER BY 	distance ASC 

		LIMIT 0, {$nb}
	"); 

	return $db->getarray();
}

/**
 * Retourne la distance en kilomètre entre 2 points
 *
 * @param float $lat_a latitude du point A
 * @param float $long_a longitude du point A
 * @param float $lat_b latitude du point B
 * @param float $long_b longitude du point B
 * @return float distance en km
 */

function weathertools_get_distance($lat_a, $long_a, $lat_b, $long_b)
{
    return 6366*acos(cos(deg2rad($lat_a))*cos(deg2rad($lat_b))*cos(deg2rad($long_b)-deg2rad($long_a))+sin(deg2rad($lat_a))*sin(deg2rad($lat_b)));
}

/**
 * Retourne le bulletin météo en français à partir des données METAR d'une station météo
 *
 * @param string $url adresse du serveur proposant les fichiers METAR
 * @param string $icao code ICAO de la station météo (4 caractères)
 * @param boolean $force true si l'on souhaite forcer la lecture des données sur le serveur (sans passer par la mise en cache)
 * @param boolean $debug true si l'on souhaite afficher des données de debug
 * @return string bulletin météo
 */
function weathertools_get_metar_bulletin($url, $icao, $force = false, $debug = false)
{
	if (($arrMetarData = weathertools_extract_metar_data(weathertools_get_metar_file($url, $icao, $force))) !== false) 
	{
		// Affichage des données décodées non formatées
		if ($debug) ploopi_print_r($arrMetarData);
		
		// Retourne le bulletin météo en français
		return ploopi_nl2br(weathertools_get_bulletin($arrMetarData));
	}	
	else return "Problème de lecture des données";
}

/**
 * Télécharge, gère la mise en cache (BDD) et renvoit les données METAR d'une station avec son code icao.
 *
 * @param string $url adresse du serveur proposant les fichiers METAR
 * @param string $icao code ICAO de la station météo (4 caractères)
 * @param boolean $force true si l'on souhaite forcer la lecture des données sur le serveur (sans passer par la mise en cache)
 * @return array contenu brut du fichier METAR
 */

function weathertools_get_metar_file($url, $icao, $force = false)
{
	require_once 'HTTP/Request.php'; // PEAR
	include_once './modules/weathertools/classes/class_weathertools_cache.php';
	
	if (!ploopi_is_url($url)) return "Erreur URL non valide";

	$objWeatherCache = new weathertools_cache();

	if (!$objWeatherCache->open($icao) || (ploopi_createtimestamp() - $objWeatherCache->fields['timestp'] > 1800) || $force) // cache pas à jour (ou rechargement forcé)
	{
		$icao = strtoupper($icao);
		$objRequest = new HTTP_Request("{$url}/{$icao}.TXT");
		
		if (_PLOOPI_INTERNETPROXY_HOST != '')
		{
			$objRequest->setProxy( 
				_PLOOPI_INTERNETPROXY_HOST,
				_PLOOPI_INTERNETPROXY_PORT,
				_PLOOPI_INTERNETPROXY_USER,
				_PLOOPI_INTERNETPROXY_PASS
			);
		}
							
		$intResult = $objRequest->sendRequest();

		if ($intResult == 1) 
		{
			if ($objRequest->getResponseCode() != '200' && $objRequest->getResponseCode() != '') return sprintf("Erreur HTTP %s", $objRequest->getResponseCode());
			else 
			{
				$objWeatherCache->fields['zoneid'] = $icao;
				$objWeatherCache->fields['rawcontent'] = $objRequest->getResponseBody();
				$objWeatherCache->save();
				return preg_split("/\n/", $objWeatherCache->fields['rawcontent']);
			}
		}	
	}
	else
	{
		return preg_split("/\n/", $objWeatherCache->fields['rawcontent']);
	}
		
	return false;
}

/**
 * Analyse, décode les données METAR et retourne un tableau humainement lisible
 * 
 * @param array $arrMetarContent contenu d'un fichier METAR
 * @return $array données décodées (lisibles)
 */

function weathertools_extract_metar_data($arrMetarContent)
{
	include_once './modules/weathertools/classes/class_metar.php';
	include_once './modules/weathertools/classes/class_weathertools_station.php';
		
	if (sizeof($arrMetarContent) == 1) return false;
	
	// Création d'un buffer pour échapper aux notices de la classe PEAR (...)
	$objMetar = new metar();

	$objMetar->setUnitsFormat("metric");
	$objMetar->setDateTimeFormat('d/n/Y', 'H:i');

	// parse les données METAR
	$arrData = $objMetar->_parseWeatherData($arrMetarContent);
	
	$strIcaoCode = $arrData['station'];
	
	// Convertion des données METAR
	$objMetar->_convertReturn(
		&$arrData, 
		array(
			'wind' => 'kmh',
			'vis' => 'km',
			'height' => 'm',
			'temp' => 'c',
			'pres' => 'mb',
			'rain' => 'mm'
		), 
		' '
		);
	
	$objWeatherStation = new weathertools_station();
	if ($objWeatherStation->open($strIcaoCode)) $arrData['station'] = $objWeatherStation->fields;
		
	return($arrData);	
}

/**
 * Traduit les données METAR décodées en un bulletin écrit
 *
 * @param array $arrMetarData données METAR décodées
 * @return string bulletin météo
 */

function weathertools_get_bulletin($arrMetarData)
{
    $arrWeatherBulletin = array();
    
    $arrWeatherBulletin['station'] = 
        sprintf(
            "Station : %s - %s", 
            $arrMetarData['station']['place_name'], 
            $arrMetarData['station']['country_name']
        ); 
        
    $arrWeatherBulletin['localisation'] = 
        sprintf(
            "Localisation : latitude: %.2f ° longitude: %.2f ° altitude: %s m", 
            $arrMetarData['station']['station_latitude_wgs84'], 
            $arrMetarData['station']['station_longitude_wgs84'],
            $arrMetarData['station']['station_elevation']
        ); 

    // Timestamp UTC
    $strUtcTs = str_replace(array(':', '/', ' '), array('', '', ''), $arrMetarData['updateRaw']).'00';
    $arrUtcDate = ploopi_timestamp2local($strUtcTs);
    
    // Timestamp local (utilisateur)
    $strUserTs = ploopi_tz_timestamp2timestamp($strUtcTs, 'UTC', 'user');
    
    // Date/heure locale
    $arrLocalDate = ploopi_timestamp2local($strUserTs);
    
    $arrWeatherBulletin['date'] = 
        sprintf(
            "Date/Heure : %s %s (UTC) - heure locale (utilisateur) : %s (%s)", 
            $arrUtcDate['date'],
            substr($arrUtcDate['time'], 0, 5),
            substr($arrLocalDate['time'], 0, 5),
            ploopi_tz_getutc('user')
        ); 

    $arrWeatherBulletin['température'] = 
        sprintf(
            "Température : %s °C (ressentie : %s °C) - Point de rosée : %s °C", 
            isset($arrMetarData['temperature']) ? $arrMetarData['temperature'] : '-',
            isset($arrMetarData['feltTemperature']) ? $arrMetarData['feltTemperature'] : '-',
            isset($arrMetarData['dewPoint']) ? $arrMetarData['dewPoint'] : '-'
        ); 

    $arrWeatherBulletin['pression'] = 
        sprintf(
            "Pression athmosphérique : %s mb - Hygrométrie : %s %%", 
            isset($arrMetarData['pressure']) ? $arrMetarData['pressure'] : '-',
            isset($arrMetarData['humidity']) ? $arrMetarData['humidity'] : '-'
        ); 

    $arrWeatherBulletin['vent'] = 
        sprintf(
            "Vent : %s km/h (Orientation : %s%s)%s", 
            isset($arrMetarData['wind']) ? $arrMetarData['wind'] : '-',
            isset($arrMetarData['windDirection']) ? ($arrMetarData['windDirection'] == 'Variable' ? 'variable' : str_replace(array('N', 'E', 'S', 'O'), array('Nord', 'Est', 'Sud', 'Ouest'), substr(chunk_split($arrMetarData['windDirection'], 1, '-'), 0, -1))) : '-',
            isset($arrMetarData['windDegrees']) ? (($arrMetarData['windDegrees'] == 'Variable') ? '' : " / {$arrMetarData['windDegrees']} °") : '-',
            isset($arrMetarData['windGust']) ? " - Rafales : {$arrMetarData['windGust']} km/h" : ''
        ); 

    $arrWeatherBulletin['visibilité'] = 
        sprintf(
            "Visbilité : %s %s km", 
            isset($arrMetarData['visQualifier']) ? $arrMetarData['visQualifier'] : '-',
            isset($arrMetarData['visibility']) ? $arrMetarData['visibility'] : '-'
        ); 

    $arrWeatherBulletin['nuages'] = array();
    if (isset($arrMetarData['clouds']))
    {
        foreach($arrMetarData['clouds'] as $cloudDesc)
        {
            $arrWeatherBulletin['nuages'][] = 
                sprintf(
                    "%s%s%s", 
                    isset($cloudDesc['height']) ? "à {$cloudDesc['height']} m " : '', 
                    $cloudDesc['amount'], 
                    isset($cloudDesc['type']) ? " ({$cloudDesc['type']})" : ''
                );
        }
    }
    
    $arrWeatherBulletin['nuages'] = "Nuages : ".(empty($arrWeatherBulletin['nuages']) ? '-' : implode(', ',     $arrWeatherBulletin['nuages']));

    $arrWeatherBulletin['condition'] = "Conditions : ".(isset($arrMetarData['condition']) ? $arrMetarData['condition'] : '-');
    
    return implode("\n", $arrWeatherBulletin);  
}

/**
 * Géocodage d'IP
 *
 * @param string $ip adresse IP 
 * @return geoip_object
 */

function weathertools_iptolocation($ip = null)
{
    // Installation des données GEOIP :
    // cd ./modules/weathertools/geoip/
    // wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
    // gunzip GeoLiteCity.dat.gz
    
    $record = null;
    // On vérifie l'existence de la BDD
    if (file_exists(_PLOOPI_PATHDATA.'/weathertools/GeoLiteCity.dat'))
    {
        include("./modules/weathertools/geoip/geoipcity.inc.php");
        include("./modules/weathertools/geoip/geoipregionvars.php");
        $gi = geoip_open(_PLOOPI_PATHDATA.'/weathertools/GeoLiteCity.dat', GEOIP_STANDARD);
        
        $record = geoip_record_by_addr($gi, empty($ip) ? $_SESSION['ploopi']['remote_ip'][0] : $ip);
        geoip_close($gi);
    }
    
    return($record);
}
?>
