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
 * Tests unitaires de ploopi\arr (exports et pagination).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

use ploopi\arr;

$arrDonnees = array(
    array('id' => 1, 'nom' => 'Dupont; "X"', 'note' => 3.5),
    array('id' => 2, 'nom' => 'Durand',      'note' => 4)
);

T::section('arr::map');
T::eq('application récursive', array('a', array('b')), arr::map('trim', array(' a ', array(' b '))));
T::eq('valeur simple', 'a', arr::map('trim', ' a '));

T::section('arr::tojson');
T::eq(
    'export JSON sans forçage d\'encodage',
    json_encode(array(array('a' => 'é'))),
    arr::tojson(array(array('a' => 'é')), false)
);
/**
 * Piège à connaître : le second paramètre vaut true par défaut et applique un
 * utf8_encode() sur des chaînes DÉJÀ en UTF-8 (le projet est en UTF-8 de bout en
 * bout, cf. CLAUDE.md §1), ce qui produit une double conversion.
 * Passer false sur du contenu déjà UTF-8.
 */
T::eq(
    'le forçage UTF-8 par défaut double-encode un contenu déjà UTF-8',
    json_encode(array(array('a' => mb_convert_encoding('é', 'UTF-8', 'ISO-8859-1')))),
    arr::tojson(array(array('a' => 'é')))
);
T::eq('tableau vide', '[]', arr::tojson(array(), false));

T::section('arr::tocsv');
$strCsv = arr::tocsv($arrDonnees);
$arrLignes = array_values(array_filter(explode("\n", $strCsv), 'strlen'));
T::eq('ligne d\'en-tête issue des clés', '"id","nom","note"', $arrLignes[0]);
T::contient('les guillemets du contenu sont doublés', '"Dupont; ""X"""', $arrLignes[1]);
T::eq('une ligne par enregistrement', 3, sizeof($arrLignes));

T::section('arr::tohtml');
$strHtml = arr::tohtml($arrDonnees);
T::contient('en-tête de colonne', '<th>nom</th>', $strHtml);
T::contient('classe CSS par défaut', 'class="ploopi_array"', $strHtml);
T::contientpas('en-tête supprimé sur demande', '<th>', arr::tohtml($arrDonnees, false));
T::contient(
    'le contenu est échappé',
    '&lt;b&gt;&amp;x&lt;/b&gt;',
    arr::tohtml(array(array('nom' => '<b>&x</b>')))
);
T::contientpas(
    'aucune balise injectée depuis les données',
    '<b>',
    arr::tohtml(array(array('nom' => '<b>&x</b>')))
);

T::section('arr::toxml');
$strXml = arr::toxml($arrDonnees);
T::contient('déclaration XML', '<?xml version="1.0"?>', $strXml);
T::contient('un noeud par enregistrement', '<row><id>1</id>', $strXml);
T::contient(
    'le contenu est échappé',
    '&lt;b&gt;&amp;x&lt;/b&gt;',
    arr::toxml(array(array('nom' => '<b>&x</b>')))
);
T::chk('le résultat est du XML valide', simplexml_load_string($strXml) !== false);

T::section('arr::cleankeys');
T::eq(
    'espaces et accents normalisés dans les clés',
    array('a_b' => 1, 'e' => 2, 'ok_1' => 3),
    arr::cleankeys(array('a b' => 1, 'é' => 2, 'ok_1' => 3))
);

T::section('arr::getpages');
T::eq('une seule page : pas de pagination', '', arr::getpages(5, 10, '?page={p}', 1));
T::eq('pagination désactivée (maxlines = 0)', '', arr::getpages(100, 0, '?page={p}', 1));

$strPages = arr::getpages(100, 10, '?page={p}', 3);
T::contient('le marqueur {p} est substitué', '?page=4', $strPages);
T::contientpas('aucun marqueur résiduel', '{p}', $strPages);
T::contient('la page courante n\'est pas un lien', '<strong>3</strong>', $strPages);
T::contient('lien vers la page précédente', '>&laquo;</a>', $strPages);
T::contient('lien vers la page suivante', '>&raquo;</a>', $strPages);
T::contient('lien vers la dernière page', '?page=10', $strPages);

$strPremiere = arr::getpages(100, 10, '?page={p}', 1);
T::contientpas('pas de flèche précédente sur la première page', '&laquo;', $strPremiere);
$strDerniere = arr::getpages(100, 10, '?page={p}', 10);
T::contientpas('pas de flèche suivante sur la dernière page', '&raquo;', $strDerniere);

T::bilan('04_arr');
