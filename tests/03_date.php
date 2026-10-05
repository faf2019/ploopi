<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Tests unitaires de ploopi\date.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

use ploopi\date;

T::section('format pivot');
T::eq('le format local par défaut est le format français', 'd/m/Y', _PLOOPI_DATEFORMAT);
T::eq('createtimestamp produit 14 caractères (YmdHis)', 14, strlen(date::createtimestamp()));
T::eq('createtimestamp ne produit que des chiffres', 1, preg_match('/^[0-9]{14}$/', date::createtimestamp()));

T::section('date::gettimestampdetail');
$arrDetail = date::gettimestampdetail('20240513142530');
T::eq('année', '2024', $arrDetail[_PLOOPI_DATE_YEAR]);
T::eq('mois', '05', $arrDetail[_PLOOPI_DATE_MONTH]);
T::eq('jour', '13', $arrDetail[_PLOOPI_DATE_DAY]);
T::eq('heure', '14', $arrDetail[_PLOOPI_DATE_HOUR]);
T::eq('minute', '25', $arrDetail[_PLOOPI_DATE_MINUTE]);
T::eq('seconde', '30', $arrDetail[_PLOOPI_DATE_SECOND]);

T::section('date::timestamp2local / local2timestamp');
$arrLocal = date::timestamp2local('20240513142530');
T::eq('date au format français', '13/05/2024', $arrLocal['date']);
T::eq('heure', '14:25:30', $arrLocal['time']);
T::eq('conversion inverse', '20240513142530', date::local2timestamp('13/05/2024', '14:25:30'));
T::eq(
    'aller-retour sur une date quelconque',
    '20051231235959',
    date::local2timestamp(date::timestamp2local('20051231235959')['date'], date::timestamp2local('20051231235959')['time'])
);
T::eq('heure omise : minuit', '20240513000000', date::local2timestamp('13/05/2024'));

$arrLocalUs = date::timestamp2local('20240513142530', _PLOOPI_DATEFORMAT_US);
T::eq('format US = ISO (Y-m-d)', '2024-05-13', $arrLocalUs['date']);
T::eq('conversion inverse en format US', '20240513142530', date::local2timestamp('2024-05-13', '14:25:30', _PLOOPI_DATEFORMAT_US));

T::section('valeurs vides');
$arrVide = date::timestamp2local('');
T::eq('timestamp vide : date vide', '', $arrVide['date']);
T::eq('timestamp vide : heure vide', '', $arrVide['time']);
T::eq('date vide : false', false, date::local2timestamp('', ''));

T::section('date::dateverify / timeverify');
T::eq('date française valide', true, date::dateverify('13/05/2024'));
T::eq('heure valide', true, date::timeverify('14:25:30'));
T::eq('chaîne sans chiffres refusée', false, date::dateverify('pas une date'));
T::eq('heure sans séparateur refusée', false, date::timeverify('142530'));
/**
 * Comportement à connaître : dateverify() ne contrôle que la FORME (regex non ancrée),
 * pas la validité calendaire. Un appelant qui a besoin d'une vraie validation doit
 * passer par checkdate() après gettimestampdetail().
 */
T::eq('32/13/2024 passe le contrôle de forme (pas de contrôle calendaire)', true, date::dateverify('32/13/2024'));

T::section('date::timestamp_add');
T::eq('ajout d\'un jour', '20240514142530', date::timestamp_add('20240513142530', 0, 0, 0, 0, 1, 0));
T::eq('ajout d\'une heure', '20240513152530', date::timestamp_add('20240513142530', 1, 0, 0, 0, 0, 0));
T::eq('retrait d\'un jour (franchit le mois)', '20240229120000', date::timestamp_add('20240301120000', 0, 0, 0, 0, -1, 0));
T::eq('passage d\'année', '20250101000000', date::timestamp_add('20241231235959', 0, 0, 1, 0, 0, 0));
/**
 * Comportement à connaître : l'ajout de mois s'appuie sur mktime(), qui déborde sur le
 * mois suivant quand le jour n'existe pas (31 janvier + 1 mois = 2 mars en 2024).
 */
T::eq('31 janvier + 1 mois déborde sur mars', '20240302120000', date::timestamp_add('20240131120000', 0, 0, 0, 1, 0, 0));

T::section('timestamps unix');
$intUnix = date::timestamp2unixtimestamp('20240513142530');
T::eq('aller-retour unix', '20240513142530', date::unixtimestamp2timestamp($intUnix));
T::eq('un timestamp unix est un entier', true, is_int($intUnix));

T::section('format MySQL DATETIME');
T::eq('datetime vers timestamp', '20240513142530', date::datetime2timestamp('2024-05-13 14:25:30'));
T::eq('timestamp vers datetime', '2024-05-13 14:25:30', date::local2datetime('13/05/2024', '14:25:30'));

T::section('date::holiday');
T::eq('1er mai est férié', true, date::holiday(mktime(0, 0, 0, 5, 1, 2024)));
T::eq('25 décembre est férié', true, date::holiday(mktime(0, 0, 0, 12, 25, 2024)));
T::eq('lundi de Pâques 2024 est férié', true, date::holiday(mktime(0, 0, 0, 4, 1, 2024)));
T::eq('2 mai 2024 n\'est pas férié', false, date::holiday(mktime(0, 0, 0, 5, 2, 2024)));

T::section('date::tz_timestamp2timestamp');
/**
 * Hors session utilisateur, la conversion de fuseau est inopérante et retourne
 * le timestamp inchangé (cf. date::tz_timestamp2timestamp()).
 */
T::eq('sans session : timestamp inchangé', '20240513120000', date::tz_timestamp2timestamp('20240513120000', 'UTC', 'Europe/Paris'));

$_SESSION['ploopi']['user'] = array('timezone' => 'Europe/Paris');
$_SESSION['ploopi']['timezone'] = 'UTC';
T::eq('UTC vers Paris en été (+2h)', '20240513140000', date::tz_timestamp2timestamp('20240513120000', 'UTC', 'Europe/Paris'));
T::eq('UTC vers Paris en hiver (+1h)', '20240113130000', date::tz_timestamp2timestamp('20240113120000', 'UTC', 'Europe/Paris'));
T::eq('alias "user" résolu depuis la session', '20240513140000', date::tz_timestamp2timestamp('20240513120000', 'UTC', 'user'));
T::eq('conversion retour', '20240513120000', date::tz_timestamp2timestamp('20240513140000', 'Europe/Paris', 'UTC'));
unset($_SESSION['ploopi']);

T::bilan('03_date');
