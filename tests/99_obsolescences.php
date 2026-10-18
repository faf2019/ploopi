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
 * Suite 99 — obsolescences relevées pendant la suite complète.
 *
 * Passée en dernier : elle lit la partie du journal des erreurs de l'instance
 * (_PLOOPI_LOG_ERRORS_FILE) écrite depuis son montage (tests/instance.sh), installation
 * et toutes les suites comprises. Une obsolescence (E_DEPRECATED) levée par le code de
 * Ploopi, au lieu que consigne error::handler, fait échouer la suite : elle annonce une
 * erreur de la version suivante de PHP (PHP 8.5 : xml_parser_free(), imagedestroy(),
 * ord() sur plusieurs octets, null en clé de tableau…). Celles des dépendances (vendor/) sont listées sans faire échouer : elles
 * se règlent par leur mise à jour.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();

T::section('Obsolescences levées pendant la suite (PHP '.PHP_VERSION.' en ligne de commande)');

$strJournal = (string) cfg('journal_erreurs', '');
if ($strJournal === '' || !file_exists($strJournal))
{
    T::skip('obsolescences du code de Ploopi', 'journal des erreurs de l\'instance introuvable (instance montée par tests/instance.sh ?)');
    exit(T::bilan('99_obsolescences'));
}

clearstatcache();
$strContenu = (string) file_get_contents($strJournal, false, null, (int) cfg('journal_debut', 0));

$arrPloopi = $arrVendor = array();
foreach (preg_split("/\n(?=Type d'erreur : )/", $strContenu) as $strBloc)
{
    if (!preg_match("/^Type d'erreur : Deprecated\s*\nMessage : (.*)/", $strBloc, $arrM)) continue;
    // Lieu de l'erreur (error::handler) ; à défaut, dernier fichier de la pile, qui pour
    // une erreur de compilation est le site de l'include et non le fichier fautif
    if (preg_match('/\nLieu : (\S+):(\d+)\n/', $strBloc, $arrLieu)) { }
    elseif (preg_match_all('/Fichier : (\S+) \nLigne : (\d+)/', $strBloc, $arrF, PREG_SET_ORDER)) $arrLieu = end($arrF);
    else continue;
    $strFichier = preg_replace('@^'.preg_quote(PLOOPI_ROOT.'/', '@').'@', '', $arrLieu[1]);
    // Le harnais (tests/routeur.php) inclut les points d'entrée : il n'est jamais le lieu
    if (strpos($strFichier, '/vendor/') !== false || strpos($strFichier, 'vendor/') === 0)
    {
        preg_match('@vendor/([^/]+/[^/]+)/@', $strFichier, $arrPaquet);
        $arrVendor[$arrPaquet[1] ?? $strFichier] = true;
        continue;
    }
    $arrPloopi[trim($arrM[1]).' @ '.$strFichier.':'.$arrLieu[2]] = true;
}

T::vrai('aucune obsolescence levée par le code de Ploopi', empty($arrPloopi), count($arrPloopi)." lieu(x) :\n    ".implode("\n    ", array_slice(array_keys($arrPloopi), 0, 25)));
echo empty($arrVendor) ? "  (aucune dans vendor/)\n" : '  Dépendances concernées (non bloquant) : '.implode(', ', array_keys($arrVendor))."\n";

exit(T::bilan('99_obsolescences'));
