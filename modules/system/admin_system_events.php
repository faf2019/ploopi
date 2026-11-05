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
 * Système > Événements : suivi de l'outbox et reprise explicite des échecs.
 * @package system
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
use ploopi\system\ui;

// Rendu ploopi4 ou historique par les fonctions system_* (include/functions.php)
if (!function_exists('system_open_bloc')) include_once './modules/system/include/functions.php';

if (!ploopi\acl::isadmin()) return;
header('Cache-Control: no-store');
echo system_pagehead(_SYSTEM_MENU_EVENTS, 'Publication RabbitMQ et suivi des événements métier');
$objEventsDb = ploopi\db::get();
$resEventsVersion = $objEventsDb->query('SELECT version FROM ploopi_module_type WHERE id = 1');
$arrEventsVersion = $objEventsDb->fetchrow($resEventsVersion);
if (!$arrEventsVersion || version_compare($arrEventsVersion['version'], '2.2.0', '<'))
{
    echo system_message('warning', 'Effectuez la mise à jour de la base dans Système → Mise à jour.');
    return;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_retry']))
{
    if (!ploopi\loader::checktoken()) { http_response_code(403); echo system_message('danger', 'Jeton de sécurité invalide.'); }
    else
    {
        try
        {
            $booEventsRetried = ploopi\events::retry(is_string($_POST['event_retry']) ? $_POST['event_retry'] : '');
            echo system_message($booEventsRetried ? 'success' : 'warning', $booEventsRetried ? 'Événement remis en attente.' : 'Événement absent ou déjà repris.');
            if ($booEventsRetried) ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, 'Reprise événement '.$_POST['event_retry']);
        }
        catch (\InvalidArgumentException $objEventsError) { echo system_message('danger', ui::e($objEventsError->getMessage())); }
    }
}
echo system_message(ploopi\events::enabled() ? 'info' : 'warning', ploopi\events::enabled() ? 'Enregistrement des événements activé. Les workers assurent la publication et le traitement.' : 'Enregistrement désactivé. Activer PLOOPI_EVENTS_ENABLED après configuration et mise à jour de la base. Les événements déjà en attente restent conservés.');
$arrEventsCounts = array('pending' => 0, 'publishing' => 0, 'published' => 0, 'failed' => 0);
$resEventsCounts = $objEventsDb->query('SELECT status, COUNT(*) AS total FROM ploopi_event_outbox GROUP BY status');
while ($arrEventsCount = $objEventsDb->fetchrow($resEventsCounts)) $arrEventsCounts[$arrEventsCount['status']] = (int) $arrEventsCount['total'];
echo system_stats(array(array('En attente', $arrEventsCounts['pending'], 'clock'), array('En publication', $arrEventsCounts['publishing'], 'refresh'), array('Publiés', $arrEventsCounts['published'], 'check'), array('En échec', $arrEventsCounts['failed'], 'alert-triangle')));
echo system_open_bloc('Derniers événements', 'activity');
// Même déclaration pour les deux rendus : le type en largeur libre, colonnes à largeur fixe
// autour (groupe « right » : la première déclarée est la plus à droite)
$arrEventsColumns = array(
    'left' => array('id' => array('label' => 'Identifiant', 'width' => 280)),
    'auto' => array('type' => array('label' => 'Type')),
    'right' => array('action' => array('label' => 'Action', 'width' => 100), 'error' => array('label' => 'Dernier échec', 'width' => 200), 'created' => array('label' => 'Création', 'width' => 140), 'attempts' => array('label' => 'Tentatives', 'width' => 80), 'status' => array('label' => 'État', 'width' => 90)),
);
$arrEventsRows = array();
$resEventsRows = $objEventsDb->query('SELECT id, type, status, attempts, created_at, last_error FROM ploopi_event_outbox ORDER BY created_at DESC, id DESC LIMIT 100');
while ($arrEventsRow = $objEventsDb->fetchrow($resEventsRows))
{
    $strEventsAction = '';
    if ($arrEventsRow['status'] === 'failed') $strEventsAction = '<form method="post" action="'.ui::e(ploopi\crypt::urlencode('admin.php?system_level=system&sysToolbarItem=events')).'"><input type="hidden" name="event_retry" value="'.ui::e($arrEventsRow['id']).'" />'.system_button(array('label' => 'Reprendre', 'type' => 'submit', 'confirm' => 'Réessayer la publication de cet événement ?')).'</form>';
    $arrEventsValues = array('id' => ui::e($arrEventsRow['id']), 'type' => ui::e($arrEventsRow['type']), 'status' => ui::e($arrEventsRow['status']), 'attempts' => (int) $arrEventsRow['attempts'], 'created' => ui::e(date('d/m/Y H:i:s', $arrEventsRow['created_at'])), 'error' => ui::e($arrEventsRow['last_error']), 'action' => $strEventsAction);
    $arrEventsTableRow = array('values' => array());
    foreach ($arrEventsValues as $strEventsCol => $strEventsValue) $arrEventsTableRow['values'][$strEventsCol] = array('label' => $strEventsValue);
    $arrEventsRows[] = $arrEventsTableRow;
}
system_display_array($arrEventsColumns, $arrEventsRows, 'system_events', array('filter' => 'Filtrer les événements', 'limit' => 20, 'empty' => 'Aucun événement enregistré.'));
echo system_close_bloc();
echo system_open_bloc('Fonctionnement et contrat', 'code');
?>
<div<?php echo system_bloc_padding(); ?>>
<p>Les écritures SQL instrumentées et les événements sont écrits dans la même transaction InnoDB. Le worker attend la confirmation RabbitMQ avant de marquer un événement comme publié. Un état publié indique la confirmation du broker, pas la fin du traitement métier. Une panne du broker laisse les événements en attente ; un bail expiré permet la reprise après arrêt d’un worker.</p>
<p>Les événements suivent CloudEvents 1.0 : <code>id</code>, <code>source</code>, <code>type</code>, <code>time</code>, <code>data</code> et contexte Ploopi. Les producteurs livrés signalent les créations, modifications et suppressions des utilisateurs, pages wiki et actualités. Ils transmettent les identifiants et noms des champs modifiés, sans mots de passe ni contenu métier.</p>
<p>La livraison est au moins une fois : dédupliquer par <code>source + id</code>. Les consommateurs PHP du socle utilisent une inbox transactionnelle, un ACK après commit et trois reprises différées avant une file d’échec. Les traitements externes doivent appliquer leur propre idempotence.</p>
<pre><code>php bin/events.php publish --watch
php bin/events.php consume --watch --queue=ploopi.events
php bin/events.php status</code></pre>
<p>Chaque abonnement logique dispose d’une file durable ; plusieurs workers d’une même file se partagent le travail. Le consommateur interne est facultatif : déclarer les gestionnaires dans <code>install/&lt;module&gt;/files/include/events.php</code> avant de le démarrer. La configuration et le guide d’extension sont fournis dans <code>doc/events.md</code>.</p>
</div>
<?php
echo system_close_bloc();
echo ui::script();
