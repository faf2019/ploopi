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
 * Contrôles sur les sources : syntaxe PHP et conventions de code (cf. CLAUDE.md §14).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

/**
 * Liste les fichiers PHP du dépôt, hors dépendances et dossiers techniques.
 *
 * @param array $arrRacines dossiers à parcourir, relatifs à la racine du dépôt
 * @return array chemins relatifs
 */
function lister_php($arrRacines)
{
    $arrFichiers = array();

    foreach($arrRacines as $strRacine)
    {
        $strChemin = _PLOOPI_TESTS_ROOT.'/'.$strRacine;
        if (!is_dir($strChemin)) continue;

        $objIterateur = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($strChemin, FilesystemIterator::SKIP_DOTS));

        foreach($objIterateur as $objFichier)
        {
            if (!$objFichier->isFile() || strtolower($objFichier->getExtension()) != 'php') continue;
            $arrFichiers[] = substr($objFichier->getPathname(), strlen(_PLOOPI_TESTS_ROOT) + 1);
        }
    }

    sort($arrFichiers);
    return $arrFichiers;
}

chdir(_PLOOPI_TESTS_ROOT);

$arrRacines = array('include', 'lib', 'config', 'modules', 'install', 'templates', 'tests');
$arrFichiers = lister_php($arrRacines);

T::section('syntaxe PHP');
T::chk('des fichiers PHP sont bien trouvés', sizeof($arrFichiers) > 100, sizeof($arrFichiers).' fichier(s)');

/**
 * Le dépôt utilise la syntaxe de balise courte dans quelques fichiers : le lint doit
 * être exécuté avec le même réglage que la production (cf. CLAUDE.md §17).
 */
$arrErreurs = array();
foreach($arrFichiers as $strFichier)
{
    $strSortie = array();
    $intCode = 0;
    exec('php -d short_open_tag=On -l '.escapeshellarg($strFichier).' 2>&1', $strSortie, $intCode);
    if ($intCode !== 0) $arrErreurs[$strFichier] = implode(' ', $strSortie);
}

T::chk(
    'aucune erreur de syntaxe sur '.sizeof($arrFichiers).' fichiers',
    empty($arrErreurs),
    sizeof($arrErreurs).' fichier(s) en erreur : '.implode(' | ', array_keys($arrErreurs))
);

T::section('conventions de code (include/classes)');
$arrClasses = lister_php(array('include/classes'));
T::chk('les classes du socle sont trouvées', sizeof($arrClasses) > 50, sizeof($arrClasses).' classe(s)');

/**
 * Écarts connus, constatés à la mise en place de la suite. Ils ne sont pas corrigés
 * ici (le correctif ne relève pas des tests) mais sont listés nommément pour que
 * TOUT NOUVEL écart fasse échouer le contrôle.
 */
$arrDerogations = array(
    // En-tête GPL absent.
    'entete' => array('include/classes/controller.php'),
    // Le fichier ne porte pas le nom de la classe qu'il déclare.
    // - form_selection_option.php déclare form_select_option (classe inatteignable par l'autoload)
    // - search_index_es.php déclare une seconde fois search_index (variante Elasticsearch, non référencée)
    'nommage' => array('include/classes/form_selection_option.php', 'include/classes/search_index_es.php')
);

$arrSansEntete = array();
$arrAvecTab = array();
$arrMalNommees = array();

foreach($arrClasses as $strFichier)
{
    $strSource = file_get_contents($strFichier);

    // En-tête GPL obligatoire (CLAUDE.md §14)
    if (strpos($strSource, 'GNU General Public License') === false && !in_array($strFichier, $arrDerogations['entete'], true)) $arrSansEntete[] = $strFichier;

    // Indentation : 4 espaces, jamais de tabulation (clean.sh)
    if (strpos($strSource, "\t") !== false) $arrAvecTab[] = $strFichier;

    // Un fichier = une classe, nom de fichier = nom de la classe (CLAUDE.md §7.1)
    $strAttendu = basename($strFichier, '.php');
    if (preg_match('/^\s*(?:abstract\s+|final\s+)*(?:class|interface|trait)\s+([a-zA-Z0-9_]+)/m', $strSource, $arrMatch))
    {
        if (strtolower($arrMatch[1]) !== strtolower($strAttendu) && !in_array($strFichier, $arrDerogations['nommage'], true)) $arrMalNommees[] = $strFichier.' (classe '.$arrMatch[1].')';
    }
}

T::chk('toutes les classes portent l\'en-tête GPL', empty($arrSansEntete), implode(', ', $arrSansEntete));
T::chk('aucune tabulation dans les classes du socle', empty($arrAvecTab), implode(', ', $arrAvecTab));
T::chk('nom de fichier = nom de la classe', empty($arrMalNommees), implode(', ', $arrMalNommees));

// Les écarts connus sont surveillés : s'ils disparaissent, la dérogation doit être retirée.
foreach($arrDerogations as $strType => $arrListe)
    foreach($arrListe as $strFichier)
        T::chk("dérogation '{$strType}' toujours justifiée : {$strFichier}", file_exists($strFichier), 'fichier disparu, retirer la dérogation');

T::section('autoload');
/**
 * La règle de nommage de loader::_classToFile() doit être vérifiable pour chaque
 * classe du socle : ploopi\<classe> -> include/classes/<classe>.php
 */
$arrNonChargeables = array();
foreach($arrClasses as $strFichier)
{
    if (in_array($strFichier, $arrDerogations['nommage'], true)) continue;
    $strClasse = 'ploopi\\'.basename($strFichier, '.php');
    if (!ploopi\loader::classExists($strClasse)) $arrNonChargeables[] = $strClasse;
}
amorcer_socle();
T::chk('chaque classe du socle est résolue par l\'autoload', empty($arrNonChargeables), implode(', ', $arrNonChargeables));

T::section('encodage');
$arrNonUtf8 = array();
foreach($arrClasses as $strFichier)
{
    if (!mb_check_encoding(file_get_contents($strFichier), 'UTF-8')) $arrNonUtf8[] = $strFichier;
}
T::chk('les classes du socle sont en UTF-8', empty($arrNonUtf8), implode(', ', $arrNonUtf8));

T::bilan('01_sources');
