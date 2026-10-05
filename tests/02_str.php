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
 * Tests unitaires de ploopi\str.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

use ploopi\str;

T::section('str::cut');
T::eq('chaîne plus courte que la limite : inchangée', 'abc', str::cut('abc', 5));
T::eq('chaîne à la limite exacte : inchangée', 'abcde', str::cut('abcde', 5));
T::eq('coupe à gauche avec ellipse', 'abcde...', str::cut('abcdefghij', 5));
T::eq('coupe au milieu', 'abc...lmn', str::cut('abcdefghijklmn', 9, 'middle'));
T::eq('mode inconnu : chaîne inchangée', 'abcdefghij', str::cut('abcdefghij', 5, 'inconnu'));

T::section('str::convertaccents');
T::eq('accents minuscules', 'eeaiou', str::convertaccents('éèàîöù'));
T::eq('casse préservée', 'EeAaCc', str::convertaccents('ÉeÀaÇc'));
T::eq('ligature et eszett (translittération simple)', 'As', str::convertaccents('Æß'));
T::eq('chaîne sans accent inchangée', 'Bonjour 42', str::convertaccents('Bonjour 42'));

T::section('str::nl2br');
T::eq('les trois styles de fin de ligne', 'a<br />b<br />c<br />d', str::nl2br("a\r\nb\nc\rd"));

T::section('str::tourl');
T::eq('accents, espaces et ponctuation', 'ete-de-l-ours', str::tourl("  Été  de l'Ôurs !"));
T::eq('tirets multiples réduits', 'a-b', str::tourl('a --- b'));
T::contientpas('pas de tiret final', '-', substr(str::tourl('Bonjour !'), -1));

T::section('str::rawurlencode');
T::eq('la barre oblique est préservée', 'a/b%20c', str::rawurlencode('a/b c'));

T::section('str::htmlentities');
T::eq(
    'chevrons, guillemets et esperluette échappés',
    '&lt;a href=&quot;x&quot;&gt;&amp;&lt;/a&gt;',
    str::htmlentities('<a href="x">&</a>')
);
T::eq('apostrophe échappée (ENT_QUOTES)', '&#039;', str::htmlentities("'"));
T::eq('suppression des balises sur demande', 'x', str::htmlentities('<b>x</b>', null, null, true));
T::contientpas(
    'une charge XSS ne ressort pas exécutable',
    '<script',
    str::htmlentities('<script>alert(1)</script>')
);

T::section('str::xmlentities');
T::eq('les 5 entités XML', '&lt;a&gt;&amp;&quot;&apos;', str::xmlentities('<a>&"\''));

T::section('str::html_entity_decode');
T::eq('décodage des entités nommées', '<b>', str::html_entity_decode('&lt;b&gt;'));

T::section('str::clean_filename');
$strNom = str::clean_filename("Été/../rapport final*.txt");
T::contientpas('pas de séparateur de chemin', '/', $strNom);
T::contientpas('pas de remontée de répertoire', '..', $strNom);
T::contientpas('pas de joker', '*', $strNom);
T::contientpas('pas d\'accent', 'é', $strNom);
T::contientpas('pas d\'espace', ' ', $strNom);

T::section('str::is_url');
T::eq('URL http valide', true, str::is_url('http://ovensia.fr/x?y=1'));
T::eq('URL https valide', true, str::is_url('https://ovensia.fr'));
T::eq('javascript: refusé', false, str::is_url('javascript:alert(1)'));
T::eq('chaîne quelconque refusée', false, str::is_url('pas une url'));

T::section('str::color_hex2rgb');
T::eq('couleur hexadécimale avec dièse', array(255, 128, 0), str::color_hex2rgb('#ff8000'));
T::eq('couleur hexadécimale sans dièse', array(0, 0, 0), str::color_hex2rgb('000000'));

T::section('str::str_split');
T::eq('découpage multi-octets', array('é', 'a', '€'), str::str_split('éa€'));

T::section('str::make_links');
T::contient(
    'une URL nue devient un lien',
    '<a href="http://a.fr/b"',
    str::make_links('voir http://a.fr/b fin')
);

T::section('str::getwords');
list($arrMots, $intTotal, $intDistincts) = str::getwords('Le petit chat boit du lait', false);
T::eq('tous les mots sont comptés', 6, $intTotal);
T::eq('les mots sont indexés en minuscules', true, isset($arrMots['chat']));
T::eq('occurrence unitaire', 1, $arrMots['chat']);

T::section('str::highlight');
$strTexte = str_repeat('Lorem ipsum dolor sit amet consectetur. ', 10).'Le petit chat boit du lait. '.str_repeat('Fin de texte quelconque. ', 5);
$strSurligne = str::highlight($strTexte, array('chat'));
T::contient('le mot cherché est encadré', '<span class="ploopi_highlight">chat</span>', $strSurligne);
// Comportement à connaître : la recherche d'extraits n'opère que si le contenu est
// plus long que la taille d'extrait demandée (150 caractères par défaut).
T::eq('contenu plus court que l\'extrait : résultat vide', '', str::highlight('Le petit chat boit', array('chat')));
T::eq('mot absent du contenu : résultat vide', '', str::highlight($strTexte, array('hippopotame')));

T::section('str::htmlpurifier');
if (!vendor_disponible()) T::skip('nettoyage HTML riche', 'vendor/ absent (composer install)');
else
{
    $strPropre = str::htmlpurifier('<p onclick="alert(1)">gras <b>ok</b><script>alert(1)</script></p>');
    T::contient('le balisage légitime est conservé', '<b>ok</b>', $strPropre);
    T::contientpas('la balise script est supprimée', '<script', $strPropre);
    T::contientpas('le gestionnaire d\'évènement est supprimé', 'onclick', $strPropre);
}

T::bilan('02_str');
