<?php
/*    Copyright (c) 2007-2018 Ovensia    Contributors hold Copyright (c) to their code submissions.
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  0211-1301  USA*/

/**
 * Suite 34 — gestionnaires de session internes (_PLOOPI_SESSION_HANDLER).
 *
 * Hors du gestionnaire « php », loader::boot() enregistre ploopi\session_handler
 * (SessionHandlerInterface, PR #63). La classe avait été écrite sans namespace :
 * « new session_handler() » dans loader.php cherchait ploopi\session_handler et
 * toute page finissait en erreur fatale. L'instance de test tourne en « php » :
 * rien ne le signalait.
 *
 * La suite bascule l'instance sur le gestionnaire « db » le temps de quelques
 * requêtes, puis restaure config/config.php.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();

$strConfig = PLOOPI_ROOT.'/config/config.php';
$strOrigine = (string)file_get_contents($strConfig);

T::section('Gestionnaire de session « db »');

$strBascule = preg_replace("/define *\\( *'_PLOOPI_SESSION_HANDLER' *, *'[a-z]+' *\\)/", "define ('_PLOOPI_SESSION_HANDLER', 'db')", $strOrigine, -1, $intRemplacements);
T::vrai('la configuration se bascule sur « db »', $intRemplacements > 0);

if ($intRemplacements > 0)
{
    // restauration quoi qu'il arrive, même sur une erreur de la suite
    register_shutdown_function(function () use ($strConfig, $strOrigine) { file_put_contents($strConfig, $strOrigine); });
    file_put_contents($strConfig, $strBascule);
    // l'opcache du serveur revalide les fichiers toutes les 2 s (revalidate_freq)
    sleep(3);

    H::reset();
    $arrRep = H::req('/admin.php');
    T::vrai('la page de connexion est servie', $arrRep['code'] === 200 && stripos($arrRep['body'], 'Fatal error') === false, "HTTP {$arrRep['code']}");

    $arrRep = H::connexion();
    $arrRep = H::req('/admin.php');
    T::chk('connexion établie', stripos($arrRep['headers'], 'Ploopi-Connected: 1') !== false);

    // le gestionnaire interne nomme la session « ploopi<sha256 du chemin> », pas PHPSESSID
    preg_match('/\tploopi[0-9a-f]{64}\t([0-9a-zA-Z,-]+)/', (string)@file_get_contents(H::jar()), $arrMatches);
    $strSid = isset($arrMatches[1]) ? $arrMatches[1] : '';
    $intLignes = $strSid === '' ? 0 : (int)db()->query("SELECT COUNT(*) c FROM ploopi_session WHERE id = '".db()->real_escape_string($strSid)."'")->fetch_assoc()['c'];
    T::vrai('la session est stockée en base', $intLignes === 1, 'sid='.$strSid);

    file_put_contents($strConfig, $strOrigine);
    sleep(3);
    H::reset();
}

exit(T::bilan('34_session_handler'));
