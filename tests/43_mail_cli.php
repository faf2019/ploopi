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
*/

/**
 * Suite 43 — envoi de courriel depuis la ligne de commande (ploopi\mail).
 *
 * Une tâche cron ou cli qui envoie un courriel (publication programmée, alerte
 * d'exploitation d'un module) s'exécute sans requête HTTP ni session : mail::send()
 * lisait $_SERVER['SERVER_NAME'] et $_SESSION sans les tester, d'où quatre
 * avertissements par courriel (« Undefined array key "SERVER_NAME" »,
 * « Undefined global variable $_SESSION »…), journalisés et envoyés par courriel à
 * l'administrateur si _PLOOPI_MAIL_ERRORS est actif.
 *
 * La suite se relance en sous-processus PHP en ligne de commande, sans $_SERVER web
 * ni session, avec tests/sendmail.sh pour sendmail : aucune instance requise.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

// Sous-processus : envoi d'un courriel dans un contexte CLI pur
if (getenv('PLOOPI_TEST_MAIL_CLI') === '1')
{
    amorcer_socle();
    unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME'], $_SESSION);
    $booEnvoye = ploopi\mail::send(
        array(array('name' => 'Ploopi', 'address' => 'expediteur@example.test')),
        array(array('name' => 'Exploitation', 'address' => 'destinataire@example.test')),
        'Alerte de test',
        '<p>Courriel envoyé depuis la ligne de commande.</p>'
    );
    echo $booEnvoye ? 'ENVOYE' : 'ECHEC';
    exit(0);
}

T::section('mail::send en ligne de commande');

$strCourriels = tempnam(sys_get_temp_dir(), 'ploopi_mail_cli_');
$arrTuyaux = array();
$resProc = proc_open(
    array(PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'error_reporting='.E_ALL, '-d', 'log_errors=0',
        '-d', 'sendmail_path=sh '.PLOOPI_TESTS_ROOT.'/sendmail.sh '.$strCourriels, __FILE__),
    array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
    $arrTuyaux,
    PLOOPI_ROOT,
    array_merge(getenv(), array('PLOOPI_TEST_MAIL_CLI' => '1'))
);
$strSortie = stream_get_contents($arrTuyaux[1]);
$strErreurs = stream_get_contents($arrTuyaux[2]);
proc_close($resProc);
$strCourriel = (string) file_get_contents($strCourriels);
@unlink($strCourriels);

T::eq('le courriel est envoyé', 'ENVOYE', trim($strSortie));
T::eq('aucun avertissement ni dépréciation', '', trim($strErreurs));
T::contient('le courriel parvient à sendmail', 'destinataire@example.test', $strCourriel);
T::vrai('X-Sender : nom de la machine à défaut d\'hôte HTTP', preg_match('/^X-Sender: <[^<>\s]+>/m', $strCourriel) === 1, 'X-Sender absent ou vide');
T::contientpas('pas d\'en-tête Organization vide', "Organization: \r", $strCourriel);

exit(T::bilan('43_mail_cli'));
