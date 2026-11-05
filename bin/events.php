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
 * Worker CLI d'événements (jamais accessible par HTTP).
 * @package ploopi
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__));
$strCommand = $argv[1] ?? 'help';
if ($strCommand === 'help' || $strCommand === '--help')
{
    echo "php bin/events.php publish|consume [--watch] [--limit=100] [--queue=nom] [--binding=type.#]\nphp bin/events.php status\nphp bin/events.php retry --id=identifiant\nphp bin/events.php prune --days=30\n";
    exit;
}
$arrOptions = array();
foreach (array_slice($argv, 2) as $strArg)
{
    if ($strArg === '--watch') $arrOptions['watch'] = true;
    elseif (preg_match('/^--(limit|queue|binding|id|days)=(.+)$/D', $strArg, $arrMatch)) $arrOptions[$arrMatch[1]] = $arrMatch[2];
    else { fwrite(STDERR, "Option invalide.\n"); exit(2); }
}
if (!defined('_PLOOPI_DIRNAME')) define('_PLOOPI_DIRNAME', getcwd());
require './include/classes/loader.php';
if (!is_file(ploopi\loader::configpath())) { fwrite(STDERR, "Ploopi doit être installé avant de démarrer les workers.\n"); exit(75); }
ploopi\loader::boot_cli();
try
{
    $resVersion = ploopi\db::get()->query('SELECT version FROM ploopi_module_type WHERE id = 1');
    $arrVersion = ploopi\db::get()->fetchrow($resVersion);
    if (!$arrVersion || version_compare($arrVersion['version'], '2.2.0', '<')) throw new RuntimeException('Mise à jour de la base 2.2.0 nécessaire.');
    $intLimit = filter_var($arrOptions['limit'] ?? 100, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 1000)));
    if ($intLimit === false) throw new InvalidArgumentException('Limite invalide.');
    if ($strCommand === 'status')
    {
        $resRows = ploopi\db::get()->query('SELECT status, COUNT(*) AS total FROM ploopi_event_outbox GROUP BY status');
        echo json_encode(ploopi\db::get()->getarray($resRows), JSON_THROW_ON_ERROR)."\n";
        exit;
    }
    if ($strCommand === 'retry')
    {
        if (!ploopi\events::retry($arrOptions['id'] ?? '')) throw new RuntimeException('Événement absent ou hors état failed.');
        echo "Événement remis en attente.\n";
        exit;
    }
    if ($strCommand === 'prune')
    {
        $intDays = filter_var($arrOptions['days'] ?? 30, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 3650)));
        if ($intDays === false) throw new InvalidArgumentException('Durée invalide.');
        ploopi\db::get()->query("DELETE FROM ploopi_event_outbox WHERE status = 'published' AND published_at < ".(time() - $intDays * 86400));
        echo 'Événements publiés supprimés : '.ploopi\db::get()->affectedrows()."\n";
        exit;
    }
    if (!in_array($strCommand, array('publish', 'consume'), true)) throw new InvalidArgumentException('Commande inconnue.');
    // Sans gestionnaire, chaque message irait en file d'échec : ne pas démarrer.
    if ($strCommand === 'consume' && !ploopi\events::listeners())
    {
        fwrite(STDERR, "Aucun gestionnaire déclaré (modules/<module>/include/events.php) : consommateur non démarré.\n");
        exit(78);
    }
    $booStop = false;
    if (function_exists('pcntl_async_signals'))
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, function () use (&$booStop) { $booStop = true; });
        pcntl_signal(SIGINT, function () use (&$booStop) { $booStop = true; });
    }
    $objTransport = null;
    $intExit = 0;
    do
    {
        try
        {
            if ($strCommand === 'publish')
            {
                $arrStats = (new ploopi\event_publisher())->flush($intLimit);
                $intExit = $arrStats['failed'] ? 1 : 0;
                if ($arrStats['published'] || $arrStats['failed'] || empty($arrOptions['watch'])) echo json_encode($arrStats)."\n";
            }
            else
            {
                if (!$objTransport) $objTransport = new ploopi\rabbitmq($arrOptions['queue'] ?? null, array($arrOptions['binding'] ?? '#'));
                $objConsumer = new ploopi\event_consumer($objTransport, ploopi\events::listeners());
                $intDeliveries = 0;
                while (!$booStop && $intDeliveries < $intLimit && $objConsumer->once()) $intDeliveries++;
                if ($intDeliveries || empty($arrOptions['watch'])) echo json_encode(array('deliveries' => $intDeliveries))."\n";
            }
        }
        catch (Throwable $objError)
        {
            if ($objTransport) $objTransport->close();
            $objTransport = null;
            fwrite(STDERR, 'Worker indisponible ('.get_class($objError)."). Vérifiez la configuration et les services.\n");
            if (empty($arrOptions['watch'])) exit(1);
            sleep(5);
        }
        if (!empty($arrOptions['watch']) && !$booStop) sleep(1);
    } while (!empty($arrOptions['watch']) && !$booStop);
    if ($objTransport) $objTransport->close();
    exit($intExit);
}
catch (Throwable $objError)
{
    fwrite(STDERR, 'Démarrage impossible ('.get_class($objError)."). Vérifiez la version, les arguments et la configuration.\n");
    exit(2);
}
