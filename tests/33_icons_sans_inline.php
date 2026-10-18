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
 * Suite 33 — les icônes de la barre d'outils ne portent plus de javascript inline.
 *
 * La CSP effective (script-src 'self') interdit les gestionnaires d'événement
 * inline : create_icon() doit écrire des attributs data-* relus par un
 * écouteur délégué du socle JS.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

$strSkin = file_get_contents(PLOOPI_ROOT.'/include/classes/skin.php');
$strUtilities = file_get_contents(PLOOPI_ROOT.'/js/utilities.js');

T::section('create_icon n\'écrit plus de onclick inline');

preg_match('/function create_icon\(.*?\n    \}/s', $strSkin, $arrMatches);
$strCorps = $arrMatches[0] ?? '';
T::chk('corps de create_icon trouvé',                $strCorps !== '');
T::chk('aucun attribut onclick dans create_icon',    strpos($strCorps, 'onclick') === false);
T::chk('l\'url est portée par un attribut data-url', strpos($strCorps, 'data-url') !== false);

T::section('la confirmation passe par data-confirm');

T::chk('message de confirmation porté par data-confirm',    strpos($strCorps, 'data-confirm') !== false);
T::chk('l\'URL reste encodée (crypt::urlencode)',           strpos($strCorps, 'crypt::urlencode') !== false);

/**
 * Le rendu, pas le texte source : la PR P2 construisait data-url entre
 * apostrophes simples, et chaque icône menait à la chaîne « {$strUrl} ».
 */
T::section('rendu d\'une icône');

amorcer_socle();
$objSkin = (new ReflectionClass('ploopi\\skin'))->newInstanceWithoutConstructor();
$strIcone = $objSkin->create_icon(array('title' => 'Supprimer', 'url' => 'admin.php?op=supprimer&id=3', 'confirm' => 'Supprimer "ceci" ?'), false, 'icone_test', false);
preg_match('/<a [^>]*class="skin_icon"[^>]*>/', $strIcone, $arrMatches);
$strBalise = $arrMatches[0] ?? '';
preg_match('/data-url="([^"]*)"/', $strBalise, $arrUrl);
preg_match('/data-confirm="([^"]*)"/', $strBalise, $arrConfirm);
T::chk('data-url porte l\'URL chiffrée de l\'icône', isset($arrUrl[1]) && strpos($arrUrl[1], 'admin.php?') === 0 && strpos($arrUrl[1], '{$') === false, $strBalise);
T::chk('data-confirm porte le message, échappé', isset($arrConfirm[1]) && html_entity_decode($arrConfirm[1], ENT_QUOTES, 'UTF-8') === 'Supprimer "ceci" ?', $strBalise);

T::section('le socle JS porte l\'écouteur délégué');

T::chk('les icônes portent une classe déléguée (skin_icon)', strpos($strSkin, 'skin_icon') !== false);
T::chk('écouteur délégué sur les icônes dans utilities.js',  strpos($strUtilities, 'skin_icon') !== false);
T::chk('l\'écouteur lit data-url',                           strpos($strUtilities, 'data(\'url\')') !== false || strpos($strUtilities, 'attr(\'data-url\')') !== false || strpos($strUtilities, 'data("url")') !== false || strpos($strUtilities, 'attr("data-url")') !== false);
T::chk('l\'écouteur applique la confirmation',               strpos($strUtilities, 'data-confirm') !== false || strpos($strUtilities, 'data(\'confirm\')') !== false || strpos($strUtilities, 'data("confirm")') !== false);
T::chk('l\'écouteur navigue vers l\'url encodée',            strpos($strUtilities, 'confirmlink') !== false || strpos($strUtilities, 'base64_decode') !== false);


T::section('les popups du socle ferment sans onclick inline');

T::chk('open_simplebloc : le bouton fermer porte une classe ploopi_popup_close', strpos($strSkin, 'ploopi_popup_close') !== false);
T::chk('open_popup idem', substr_count($strSkin, 'ploopi_popup_close') >= 2);
T::chk('l\'écouteur ferme via ploopi.popup.hide dans utilities.js', strpos($strUtilities, 'ploopi_popup_close') !== false && strpos($strUtilities, 'ploopi.popup.hide') !== false);

T::section('tri des colonnes et pagination sans onclick inline');

T::chk('les titres triables portent data-array et data-orderby', strpos($strSkin, 'data-orderby') !== false);
T::chk('la pagination porte data-array et data-page',           strpos($strSkin, 'data-page') !== false);
T::chk('le JS appelle array_refresh pour les deux familles',    substr_count($strUtilities, 'array_refresh') >= 2);
// une balise à deux attributs class ne garde que le premier : l'en-tête perdait ses classes
$booClasseDouble = false;
foreach (explode("\n", $strSkin) as $strLigne) if (strpos($strLigne, 'sortdata') !== false && preg_match('/<a [^\n]*class="[^\n]*class="/', $strLigne)) $booClasseDouble = true;
T::chk('les en-têtes triables n\'ont qu\'un attribut class', !$booClasseDouble);
T::chk('plus aucun onclick javascript: dans skin.php pour le tri et la pagination', strpos($strSkin, "array_refresh('") === false || strpos($strSkin, 'onclick="javascript:ploopi.skin.array_refresh') === false);


T::section('zones cliquables des checkbox sans onclick inline');

$intZones = 0;
foreach (glob(PLOOPI_ROOT.'/modules/system/*.php') as $strFichier) {
    $strC = file_get_contents($strFichier);
    $intZones += preg_match_all('/onclick=\\"javascript:ploopi\.checkbox_click\(/', $strC);
}
$intZones += preg_match_all('/onclick=\\"javascript:ploopi\.checkbox_click\(/', file_get_contents(PLOOPI_ROOT.'/include/op/tickets.php'));
T::chk('aucune zone checkbox avec onclick inline dans le socle et system', $intZones == 0, $intZones.' sites restants');
T::chk('les zones portent data-checkbox', strpos(file_get_contents(PLOOPI_ROOT.'/modules/system/admin_index_modules.php'), 'data-checkbox') !== false);
T::chk('l\'écouteur JS appelle ploopi.checkbox_click', strpos($strUtilities, 'checkbox_click') !== false);

/**
 * Les navigateurs ne lisent pas utilities.js : ils reçoivent js/functions.pack.js
 * et, derrière Apache, js/functions.pack.js.gz — le .htaccess sert la version
 * compressée dès qu'elle existe. Un .gz resté à l'ancienne version prive le
 * backoffice des écouteurs délégués alors que le HTML n'a plus d'onclick.
 */
T::section('le JS servi aux navigateurs porte les écouteurs');

$arrSources = array_filter(suivis(glob(PLOOPI_ROOT.'/js/*.js')), function($strFichier) { return strpos(basename($strFichier), 'functions') !== 0; });
sort($arrSources, SORT_STRING);
$normaliser = function($strJs) { return preg_replace('/\n+/', "\n", $strJs); };
$strConcat = '';
foreach ($arrSources as $strFichier) $strConcat .= file_get_contents($strFichier)."\n";
$strPack = (string)file_get_contents(PLOOPI_ROOT.'/js/functions.pack.js');
T::chk('functions.pack.js reprend les sources de js/ (tools/compress.sh)', $normaliser($strConcat) === $normaliser($strPack."\n"));

$strGz = PLOOPI_ROOT.'/js/functions.pack.js.gz';
if (file_exists($strGz))
{
    T::chk('functions.pack.js.gz est la version compressée de functions.pack.js', gzdecode((string)file_get_contents($strGz)) === $strPack);
}
else T::chk('pas de functions.pack.js.gz : le .htaccess sert functions.pack.js', true);

/**
 * Même règle pour tous les .gz du dépôt (gabarits, lib/) : le .htaccess les sert à la
 * place de leur source. 224 d'entre eux étaient désaccordés (minification de 2025
 * jamais refaite, un tronqué), dont la feuille de style du backoffice.
 */
$arrDesaccordes = array();
$intGz = 0;
foreach (array('js', 'templates', 'lib', 'modules/system', 'install') as $strRacine)
{
    if (!is_dir(PLOOPI_ROOT.'/'.$strRacine)) continue;
    $objIterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PLOOPI_ROOT.'/'.$strRacine, FilesystemIterator::SKIP_DOTS));
    foreach ($objIterateur as $objFichier)
    {
        if (substr($objFichier->getFilename(), -3) !== '.gz' || !suivi($objFichier->getPathname())) continue;
        $strSource = substr($objFichier->getPathname(), 0, -3);
        if (!file_exists($strSource)) continue;
        $intGz++;
        if (@gzdecode((string)file_get_contents($objFichier->getPathname())) !== file_get_contents($strSource))
            $arrDesaccordes[] = substr($objFichier->getPathname(), strlen(PLOOPI_ROOT) + 1);
    }
}
T::vrai('chaque .gz est la version compressée de sa source', $intGz > 0 && empty($arrDesaccordes), count($arrDesaccordes)."/{$intGz} désaccordé(s) : ".implode(', ', array_slice($arrDesaccordes, 0, 4)));

exit(T::bilan());
