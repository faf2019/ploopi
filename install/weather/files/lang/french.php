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
 * Fichier de langue fran�ais
 *
 * @package weather
 * @subpackage lang
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * D�finition des constantes
 */

define ('_WEATHER_PAGE_TITLE', 'Gestion du Module �M�t�o�');
define ('_WEATHER_PAGE_EXPLAIN', 'Inscription aux donn�es weather.com');

define ('_WEATHER_TEXT_EXPLAIN',
'ATTENTION, pour b�n�ficier des informations m�t�o de weather.com vous devez vous inscrire sur le site weather.com et en accepter les conditions d\'utilisation.<br/>
<ul>Etapes � suivre pour l\'inscription (En anglais, aucune version Fr � ce jour) :
<li>Se rendre sur le site : <a href="https://registration.weather.com/ursa/xmloap/step1" target="_blank">www.weather.com</a></li>
<li>S\'inscrire en prenant soin de bien d�cocher les offres publicitaires de l\'�tape 3 de l\'inscription (� confirmer par la suite)</li>
<li>Se connecter � votre compte et se rendre � l\'adresse suivante (en �tant connect� !) <a href="https://registration.weather.com/ursa/profile" target="_blank">https://registration.weather.com/ursa/profile</a></li>
<li>Demander l\'activation du module "XML Data Feed" (N�cessite aussi de r�pondre a un questionnaire)</li>
<li>Dans les minutes qui suivent vous recevrez un email contenant votre Identifiant Partenaire et votre Cl� Partenaire</li>
<li>C \'est termin�</li>
</ul>');


define ('_WEATHER_ADMIN',  'Administration');

define ('_WEATHER_ADMIN_CITY',        'Ville');
define ('_WEATHER_ADMIN_CODE',        'Code Ville');
define ('_WEATHER_ADMIN_PREVIEW_DAY', 'Pr�vision sur');
define ('_WEATHER_ADMIN_PARTN_ID',    'Identifiant Partenaire');
define ('_WEATHER_ADMIN_PARTN_KEY',   'Cl� Partenaire');
define ('_WEATHER_ADMIN_SYSTEM_UNIT', 'Syst�me Unitaire');
define ('_WEATHER_ADMIN_UNIT_IMPERIAL', 'Imp�rial');
define ('_WEATHER_ADMIN_UNIT_METER',  'M�trique');


define ('_WEATHER_BLOC_MAJ', 'maj');
define ('_WEATHER_BLOC_FEEL', 'Ressent.');
define ('_WEATHER_BLOC_TEMPER', 'T�');
define ('_WEATHER_BLOC_WIND', 'Vent');
define ('_WEATHER_BLOC_HUMID', 'Humidit�');
define ('_WEATHER_BLOC_SUNUP', 'Aube');
define ('_WEATHER_BLOC_SUNDOWN', 'Cr�puscule');
define ('_WEATHER_BLOC_VISIBILITY', 'Visibilit�');
define ('_WEATHER_BLOC_UV', 'UV');
define ('_WEATHER_BLOC_PRESURE', 'Pression');
define ('_WEATHER_BLOC_THIS_NIGHT', 'Cette nuit');
define ('_WEATHER_BLOC_THIS_DAY', 'Aujourd\'hui');
define ('_WEATHER_BLOC_TENDANCE', 'Tendance');
define ('_WEATHER_BLOC_TEXT_WEATHER_COM', 'Ce module utilise les donn�es fournies par');
define ('_WEATHER_BLOC_RETURN', 'Retour');
define ('_WEATHER_BLOC_SOURCE', 'Source');
define ('_WEATHER_BLOC_RISQ_RAIN', 'Risq. Pluie');
define ('_WEATHER_BLOC_SUN', 'Soleil');
define ('_WEATHER_BLOC_AM', 'Matin');
define ('_WEATHER_BLOC_PM', 'A-Midi');

define ('_WEATHER_ADMIN_SEARCH_CITY', 'Rechercher cette ville');

define ('_WEATHER_TPL_WEATHER_COM','Donn�es fournies par');
define ('_WEATHER_TPL_CITY',      'Ville');
define ('_WEATHER_TPL_UPDATE',    'Mise � jour');
define ('_WEATHER_TPL_TENDANCE',  'Tendance');
define ('_WEATHER_TPL_TEMP',      'Temp�rature');
define ('_WEATHER_TPL_TEMP_SHORT','T�');
define ('_WEATHER_TPL_FEEL',      'Ressentie');
define ('_WEATHER_TPL_WIND',      'Vent');
define ('_WEATHER_TPL_WIND_DIRECT', 'Direction');
define ('_WEATHER_TPL_HUMIDITY',  'Humidit�');
define ('_WEATHER_TPL_VISIBILITY','Visibilit�');
define ('_WEATHER_TPL_UV',        'UV');
define ('_WEATHER_TPL_PRESSURE',  'Pression');
define ('_WEATHER_TPL_PRESSURE_TENDANCE','Tendance');
define ('_WEATHER_TPL_MOON',      'Lune');
define ('_WEATHER_TPL_DAWN',      'Aurore');
define ('_WEATHER_TPL_DUSK',      'Cr�puscule');
define ('_WEATHER_TPL_MAXI',      'Max');
define ('_WEATHER_TPL_MINI',      'Min');
define ('_WEATHER_TPL_RAIN_PROB', 'Risque de pluie');
define ('_WEATHER_TPL_TEND_AM',   'Tendance du matin');
define ('_WEATHER_TPL_TEND_PM',   'Tendance de l\'apr�s-midi');
define ('_WEATHER_TPL_TEND_AM_NIGHT',   'Tendance premiere partie de nuit');
define ('_WEATHER_TPL_TEND_PM_NIGHT',   'Tendance seconde partie de nuit');

/*
 * TRADUCTION !!!
 */
define ('_WEATHER_TRANSLATE',
'jan:Janvier
;feb:F�vrier
;mar:Mars
;apr:Avril
;may:Mai
;jun:Juin
;jul:Juillet
;aug:Aout
;sep:Septembre
;oct:Octobre
;nov:Novembre
;dec:D�cembre
;sunday:Dimanche
;monday:Lundi
;tuesday:Mardi
;wednesday:Mercredi
;thursday:Jeudi
;friday:Vendredi
;saturday:Samedi

;Current:Cond
;Temperature:Temp
;Pressure:Bar
;N/a:N/a
;N/A:N/A

;Wind:Vent
;Humidity:Hum
;Visibility:Visibilit�
;UV:Index:UV

;Sun:Soleil
;Sunshine:Lever
;Sunset:Coucher

;A Few Clouds:Quelques Nuages
;Blowing snow and windy:Neige avec vent
;calm:Calme
;Clear:Temps Clair
;Clear / wind:D�gag� / vent
;clouds:Nuages
;Cloudy:Nuageux
;Cloudy/Wind:Nuageux/Vent
;Cloudy / wind:Nuageux / Vent
;Cloudy and windy:Nuageux avec vents
;Drizzle:Bruine
;Drifting snow:Neige d�rivante
;Fair:Clair
;Fair and windy:Clair avec vent
;Few showers:Quelques Averses
;Few Showers/Wind:Quelques Averses/Vent
;Few showers / wind:Faible averses / vent
;Few snow showers:Quelques chutes de neige
;Few Snow Showers/Wind:Quelques Averses de Neige/Vent
;Flurries:Bourrasques
;Flurries/Wind:Bourrasques/Vent
;Flurries / wind:Rafales de vent / vent
;Fog:Brouillard
;Foggy:Brouillard
;gust:Rafale
;Haze:Brume
;Heavy rain:Forte pluie
;Heavy rain shower:Forte pluie
;Heavy Rain/Wind:Forte Pluie/Vent
;Heavy rain / wind:Forte pluie / vent
;Heavy showers:Fortes averses
;Heavy snow:Neige abondante
;Heavy snow showers:Chutes de neige abond.
;Heavy snow / wind:Neige abondante / vent
;Hvy Rain/Freezing Rain:Forte Pluie/Pluie Vergla�ante
;Isolated t-storms:Orages isol�s
;Iso T-Storms/Wind:Orages Isol�s/Vent
;Light drizzle:L�g�re bruine
;Light drizzle and windy:L�g�res bruines et vents
;Light freezing drizzle:Bruines l�g�res glac�es
;Light rain:L�g�re pluie
;Light rain Shower:L�g�res averses
;Light rain/Wind:Pluie L�g�re/Vent
;Light rain / freezing rain:L�g�re pluie vergla�ante
;Light rain / wind:Faible pluie / vent
;Light rain shower and windy:L�g�re averse et vents
;Light rain and windy:L�g�re pluie et vents
;Light rain/Ice:L�g�re pluie/Gr�le
;Light rain with Thunder:L�g�re pluie et Tonnerre
;Light showers:L�g�res averses
;Light snow:L�g�re neige
;Light snow showers:L�g�res chutes de neige
;Light snow and windy:Quelques flocons avec vents
;Light snow and sleet:Quelques flocons et verglas
;Light Snow Shower:L�g�res chutes de Neige
;Light Snow Shower/ Windy:L�g�res chutes de Neige/Vent
;Light Snow/Wind:Quelques flocons/vent
;Light wintry mix:L�ger Temps L�ger
;Mist:Brume
;Misty:Brumeux
;Mostly clear:Plut�t d�gag�
;Mostly cloudy:Plut�t nuageux
;Mostly Cloudy/Windy:Plut�t Nuageux/Vent
;Mostly cloudy / wind:Plut�t nuageux/Vent
;Mostly cloudy and windy:Plut�t nuageux, vents
;Mostly Sunny:Plut�t ensoleill�
;Mostly Sunny/Wind:Plut�t Ensoleill�/Vent
;Partly Cloudy:Parti. Nuageux
;Partly Cloudy/Windy:Parti. Nuageux/Vent
;Partly cloudy / wind:Parti. Nuageux/Vent
;Partly cloudy and windy:Parti. Nuageux/Vent
;Rain:Pluie
;Rain and snow:Pluie et neige
;Rain shower:Averses
;Rain to Snow:Pluie puis Neige
;Rain to Snow/Wind:Pluie puis Neige/Vent
;Rain / wind:Pluie / vent
;Rain / snow:Pluie / neige
;Rain / snow showers:Pluie / Chutes de neige
;Rain / snow / wind:Pluie / neige / vent
;Rain / snow showers / wind:Pluie / Chutes de neige / vent
;Rain/Freezing Rain:Pluie/Pluie verglassante
;Rain/Ice:Pluie/Gr�le
;Rain/Snow:Pluie/Neige
;Rain/Snow/Wind:Pluie/neige/vent
;Rain/Snow Showers:Averses Pluie/Neige
;Rain/Snow Showers/Wind:Averses Pluie/Neige / Vent
;Rain/Snow/Wind:Pluie/Neige/Vent
;Rain/Thunder:Pluie/Orage
;Rain / Thunder:Pluie/Orage
;Rain/Wind:Pluie/Vent
;Scattered showers:Averses �parses
;Scattered t-storms:Orages �pars
;Scattered Flurries:Fortes rafales
;Scattered Thunderstorms:Orages Violents
;Sct Flurries/Wind:fortes rafales/Vent
;Sct Strong Storms:Orages Violents
;Sct T-Storms/Wind:Orages Violents/Vent
;Sct Snow Showers:Fortes chutes de neige
;showers:Averses
;Showers / wind:Averses / vent
;Showers/Wind:Averses/Vent
;Showers in the Vicinity:Averses � proximit�
;Sleet:Neige fondue
;Smoke:Brouillard �pais
;Snow:Neige
;Snow / wind:Neige / vent
;Snow/Wind:Neige/Vent
;Snow and fog:Neige et brouillard
;Snow and Ice to Rain:Neige et Verglas puis Pluie
;Snow Shower:Chute de neige
;snow showers:Chutes de neige
;Snow Showers/Wind:Chutes de Neige/Vent
;Snow Showers/Windy:Chutes de Neige/Vent
;snow Showers / Wind:Chutes de Neige/vent
;Snow to Ice:Verglas
;Snow to Ice/Wind:Verglas/Vent
;Snow to Rain:Neige fondue
;Snow to Rain/Wind:Neige fondue/Vent
;Snow to Wintry Mix:Neige puis Temps Hivernal
;Sprinkles:Averses
;Strong Storms/Wind:Gros orages/Vent
;sun:Soleil
;Sunny:Ensoleill�
;Sunny/Windy:Ensoleill�/Vent
;Sunny intervals:Passages ensoleill�s
;T-storms:Orages
;T-Storms/Wind:Orages/Vent
;Thunderstorms:Orages
;Thundery showers:Averses orageuses
;Wintry Mix:Temps Hivernal
;Wintry Mix/Wind:Temps Hivernal/Vent
;Wintry Mix to Snow:Temps Hivernal puis Neige
;N:N
;NNE:NNE
;NE:NE
;E:E
;SE:SE
;SSE:SSE
;S:S
;SSW:SSO
;SW:SO
;W:O
;NW:NO
;NNW:NNO
;ENE:ENE
;ESE:ESE
;WSW:OSO
;WNW:ONO
;New Moon:Nouvelle Lune
;Waxing Crescent:Premier Croissant
;First Quarter:Premier Quartier
;Waxing Gibbous:Lune Gibbeuse Croissante
;Full Moon:Pleine Lune
;Full: Pleine Lune
;Waning Gibbous:Lune Gibbeuse d�croissante
;Last Quarter:Dernier Quartier
;Waning Crescent:Dernier Croissant
;Low:Minimal
;Moderate:Moyen
;Extreme:Maximal
;falling:En baisse
;steady:Stable
;rising:En hausse');
?>
