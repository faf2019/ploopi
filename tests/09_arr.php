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

require __DIR__.'/bootstrap.php';

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
 * bout, cf. doc/reference.md §1), ce qui produit une double conversion.
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
T::vrai('le résultat est du XML valide', simplexml_load_string($strXml) !== false);

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

T::section('arr::toxls (PhpSpreadsheet, qui remplace Spreadsheet_Excel_Writer)');

$strXls = tempnam(sys_get_temp_dir(), 'ploopi_xls_');
arr::toxls(
    array(
        array('nom' => 'Dupont é €', 'montant' => 1234.5, 'jour' => 45000),
        array('nom' => array('value' => 'Martin', 'bgcolor' => 'red'), 'montant' => array('value' => -3, 'bgcolor' => array(0, 128, 255)), 'jour' => '')
    ),
    true, $strXls, 'Feuille',
    array('nom' => array('title' => 'Nom', 'width' => 30), 'montant' => array('type' => 'float_euro'), 'jour' => array('type' => 'date')),
    array('tofile' => true, 'header' => array(array('Export du jour'))),
    'Commentaire é'
);
$objLu = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls')->load($strXls);
$objFeuille = $objLu->getSheet(0);
T::eq('fichier XLS (Excel 97) relu', 'Feuille', $objFeuille->getTitle());
T::eq('ligne d\'en-tête libre', 'Export du jour', $objFeuille->getCell('A1')->getValue());
T::eq('titre de colonne', 'Nom', $objFeuille->getCell('A2')->getValue());
T::eq('titre par défaut : la clé', 'montant', $objFeuille->getCell('B2')->getValue());
T::eq('texte UTF-8 conservé', 'Dupont é €', $objFeuille->getCell('A3')->getValue());
T::eq('nombre écrit comme nombre', 1234.5, $objFeuille->getCell('B3')->getValue());
T::contient('format monétaire en euros', '€', $objFeuille->getStyle('B3')->getNumberFormat()->getFormatCode());
T::eq('format de date', 'DD/MM/YYYY', $objFeuille->getStyle('C3')->getNumberFormat()->getFormatCode());
T::eq('cellule { value, bgcolor } : valeur', 'Martin', $objFeuille->getCell('A4')->getValue());
T::eq('couleur nommée', 'FF0000', $objFeuille->getStyle('A4')->getFill()->getStartColor()->getRGB());
T::eq('couleur RVB', '0080FF', $objFeuille->getStyle('B4')->getFill()->getStartColor()->getRGB());
T::vrai('date vide laissée vide', $objFeuille->getCell('C4')->getValue() === null);
T::eq('largeur de colonne', 30, (int) round($objFeuille->getColumnDimension('A')->getWidth()));
T::eq('feuille de commentaire', 'Commentaire é', $objLu->getSheetByName('comment')->getCell('A1')->getValue());
@unlink($strXls);
T::vrai('Spreadsheet_Excel_Writer n\'est plus une dépendance', strpos(file_get_contents(PLOOPI_ROOT.'/composer.json'), 'spreadsheet_excel_writer') === false);

T::section('export PDF (Dompdf, qui remplace mPDF sous GPL v2 seule)');

$strPdf = arr::toexcel(array(array('nom' => 'Dupont é', 'n' => 3), array('nom' => 'Martin', 'n' => 12)), true, 'x.pdf', 'Feuille', null, array('writer' => 'pdf'));
T::vrai('un PDF est produit', substr($strPdf, 0, 5) === '%PDF-' && strlen($strPdf) > 500, strlen($strPdf).' octets');
T::vrai('le PDF est complet (marque de fin)', strpos($strPdf, '%%EOF') !== false);
T::contientpas('mPDF n\'est plus une dépendance', 'mpdf/mpdf', file_get_contents(PLOOPI_ROOT.'/composer.json'));
T::contient('Dompdf (LGPL) l\'est', 'dompdf/dompdf', file_get_contents(PLOOPI_ROOT.'/composer.json'));

/**
 * Deux paquets déclarent la classe PEAR : pear/pear-core-minimal (la vraie) et une coquille
 * de compatibilité de horde/exception (raiseError et isError seulement). Si l'autoload
 * charge la coquille, le require_once 'PEAR.php' des paquets PEAR (Mail, Mail_mime…)
 * s'arrête sur « Cannot declare class PEAR ». composer.json écarte la coquille
 * (exclude-from-classmap) : la classe chargée la première doit être la vraie.
 */
T::section('classe PEAR : celle de pear/pear-core-minimal');

class_exists('PEAR');   // autoload d'abord, comme un PEAR::isError() précoce
T::contient('PEAR chargée par l\'autoload depuis pear-core-minimal', 'pear/pear-core-minimal/', (new ReflectionClass('PEAR'))->getFileName());
$objMime = new Mail_mime();   // require_once 'PEAR.php' : sans erreur fatale
$objMime->setTXTBody('Bonjour');
T::contient('un message MIME est produit après un chargement précoce de PEAR', 'Bonjour', $objMime->get());

exit(T::bilan('09_arr'));
