<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Construit le sprite d'icônes du gabarit ploopi4.
 *
 * Lit la liste tools/icones/ploopi4.txt (un nom de glyphe Tabler par ligne) et
 * écrit templates/backoffice/ploopi4/img/icons.svg : un <symbol id="i-<nom>"> par
 * glyphe, repris de tools/icones/tabler/outline/<nom>.svg sans son cadre
 * transparent. Le trait (couleur, épaisseur) est donné par la feuille du gabarit
 * (.p4-icon) : l'icône prend la couleur du texte.
 *
 * Usage, depuis la racine du dépôt :
 *   php tools/ploopi4_icones.php          régénère icons.svg
 *   php tools/ploopi4_icones.php --check  échoue si icons.svg n'est pas à jour
 *
 * @package ploopi
 * @subpackage tools
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

$strRacine = dirname(__DIR__);
$strListe = "{$strRacine}/tools/icones/ploopi4.txt";
$strGlyphes = "{$strRacine}/tools/icones/tabler/outline";
$strCible = "{$strRacine}/templates/backoffice/ploopi4/img/icons.svg";

$arrNoms = array();
foreach(file($strListe, FILE_IGNORE_NEW_LINES) as $strLigne)
{
    $strLigne = trim($strLigne);
    if ($strLigne == '' || $strLigne[0] == '#') continue;
    if (!preg_match('/^[a-z0-9-]+$/', $strLigne)) { fwrite(STDERR, "Nom de glyphe invalide : {$strLigne}\n"); exit(1); }
    $arrNoms[$strLigne] = true;
}

$strSprite = "<svg xmlns=\"http://www.w3.org/2000/svg\">\n"
    ."<!-- Tabler Icons (licence MIT, https://tabler.io/icons) : généré par tools/ploopi4_icones.php, ne pas modifier -->\n";

foreach(array_keys($arrNoms) as $strNom)
{
    $strFichier = "{$strGlyphes}/{$strNom}.svg";
    if (!file_exists($strFichier)) { fwrite(STDERR, "Glyphe absent : {$strFichier}\n"); exit(1); }

    $strSvg = file_get_contents($strFichier);
    if (!preg_match('@<svg[^>]*>(.*)</svg>@s', $strSvg, $arrMatches)) { fwrite(STDERR, "Glyphe illisible : {$strFichier}\n"); exit(1); }

    // Contenu sans le cadre transparent de 24x24, une forme par ligne
    $strContenu = preg_replace('@<path stroke="none" d="M0 0h24v24H0z" fill="none"\s*/>@', '', $arrMatches[1]);
    $strContenu = trim(preg_replace('/\s*\n\s*/', '', $strContenu));

    $strSprite .= "<symbol id=\"i-{$strNom}\" viewBox=\"0 0 24 24\">{$strContenu}</symbol>\n";
}

$strSprite .= "</svg>\n";

if (in_array('--check', $argv))
{
    if (!file_exists($strCible) || file_get_contents($strCible) !== $strSprite)
    {
        fwrite(STDERR, "{$strCible} n'est pas à jour : php tools/ploopi4_icones.php\n");
        exit(1);
    }
    echo "icons.svg à jour (".count($arrNoms)." glyphes)\n";
    exit(0);
}

file_put_contents($strCible, $strSprite);
echo "{$strCible} : ".count($arrNoms)." glyphes\n";
