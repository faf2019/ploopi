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
 * Atomicité SQL et transport RabbitMQ réel (option --events du runner Docker).
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
require __DIR__.'/bootstrap.php';
exiger_instance();
amorcer_socle();

$strEventsSource = 'urn:ploopi:test:'.bin2hex(random_bytes(8));
$strEventsQueue = 'ploopi.test.'.bin2hex(random_bytes(8));
$strEventsExchange = $strEventsQueue;
$arrEventsPreviousEnv = array();
foreach (array('PLOOPI_EVENTS_ENABLED', 'PLOOPI_EVENTS_SOURCE', 'PLOOPI_RABBITMQ_QUEUE', 'PLOOPI_RABBITMQ_EXCHANGE', 'PLOOPI_RABBITMQ_PORT') as $strName) $arrEventsPreviousEnv[$strName] = getenv($strName);
putenv('PLOOPI_EVENTS_SOURCE='.$strEventsSource);
putenv('PLOOPI_RABBITMQ_QUEUE='.$strEventsQueue);
putenv('PLOOPI_RABBITMQ_EXCHANGE='.$strEventsExchange);
$objEventsTransport = null;
$intEventsUser = 0;
$arrEventsDeployed = array();
$arrEventsModuleIds = array();
function events_test_cleanup()
{
    global $strEventsSource, $strEventsQueue, $strEventsExchange, $arrEventsPreviousEnv, $objEventsTransport, $intEventsUser, $arrEventsDeployed, $arrEventsModuleIds;
    putenv('PLOOPI_EVENTS_ENABLED=0');
    if ($objEventsTransport)
    {
        foreach (array('', '.retry', '.dead') as $strSuffix)
        {
            try { $objEventsTransport->channel()->queue_delete($strEventsQueue.$strSuffix); } catch (Throwable $objError) { }
        }
        foreach (array('', '.retry', '.dead') as $strSuffix)
        {
            try { $objEventsTransport->channel()->exchange_delete($strEventsExchange.$strSuffix); } catch (Throwable $objError) { }
        }
        $objEventsTransport->close();
    }
    db()->query("DELETE FROM ploopi_event_outbox WHERE payload LIKE '%".db()->real_escape_string($strEventsSource)."%'");
    db()->query("DELETE FROM ploopi_event_inbox WHERE consumer = '".db()->real_escape_string($strEventsQueue)."'");
    foreach ($arrEventsModuleIds as $strType => $intModule)
    {
        if ($strType === 'wiki')
        {
            db()->query('DELETE FROM ploopi_mod_wiki_page WHERE id_module = '.$intModule);
            db()->query('DELETE FROM ploopi_mod_wiki_page_history WHERE id_module = '.$intModule);
        }
        else db()->query('DELETE FROM ploopi_mod_news2_entry WHERE id_module = '.$intModule);
        ploopi\search_index::remove_index_module($intModule);
        db()->query('DELETE FROM ploopi_module WHERE id = '.$intModule);
    }
    foreach ($arrEventsDeployed as $strDir) ploopi\fs::deletedir($strDir);
    if ($intEventsUser) db()->query('DELETE FROM ploopi_user WHERE id = '.(int) $intEventsUser);
    db()->query('DROP TABLE IF EXISTS events_test_record');
    db()->query('DROP TABLE IF EXISTS events_test_effect');
    foreach ($arrEventsPreviousEnv as $strName => $mixValue) putenv($mixValue === false ? $strName : $strName.'='.$mixValue);
}
register_shutdown_function('events_test_cleanup');

class events_test_entity extends ploopi\data_object
{
    protected $strEventPrefix = 'test.record';
    protected $arrEventFields = array('label');
    public function __construct() { parent::__construct('events_test_record'); }
}
function events_test_record($strLabel)
{
    $objRecord = new events_test_entity();
    $objRecord->init_description();
    $objRecord->fields['label'] = $strLabel;
    $objRecord->save();
    return $objRecord;
}
function events_test_count()
{
    global $strEventsSource;
    return (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_outbox WHERE payload LIKE '%".db()->real_escape_string($strEventsSource)."%'")->fetch_assoc()['n'];
}
function events_test_clear_outbox()
{
    global $strEventsSource;
    db()->query("DELETE FROM ploopi_event_outbox WHERE payload LIKE '%".db()->real_escape_string($strEventsSource)."%'");
}
function events_test_payload($strId)
{
    return db()->query("SELECT payload FROM ploopi_event_outbox WHERE id = '".db()->real_escape_string($strId)."'")->fetch_assoc()['payload'];
}
function events_test_envelope($strType = 'test.delivery.v1')
{
    global $strEventsSource;
    return array('specversion' => '1.0', 'source' => $strEventsSource, 'id' => bin2hex(random_bytes(16)), 'type' => $strType, 'data' => array('id' => 42));
}
function events_test_send($arrEvent, $arrHeaders = array())
{
    global $objEventsTransport;
    $objEventsTransport->publish(json_encode($arrEvent, JSON_THROW_ON_ERROR), $arrEvent['type'], $arrEvent['id'], null, null, $arrHeaders);
}
function events_test_wait(callable $fnTest, $intSeconds = 10)
{
    $floatDeadline = microtime(true) + $intSeconds;
    do { if ($fnTest()) return true; usleep(100000); } while (microtime(true) < $floatDeadline);
    return false;
}

T::section('Migration et activation explicite');
ploopi\schema::upgrade(PLOOPI_ROOT.'/install/system/update_ploopi_2.2.0.sql', '2.2.0');
ploopi\schema::upgrade(PLOOPI_ROOT.'/install/system/update_ploopi_2.2.0.sql', '2.2.0');
T::eq('migration rejouable vers 2.2.0', '2.2.0', db()->query('SELECT version FROM ploopi_module_type WHERE id = 1')->fetch_assoc()['version']);
db()->query('CREATE TABLE events_test_record (id int unsigned PRIMARY KEY AUTO_INCREMENT, label varchar(100) UNIQUE, technical int NOT NULL DEFAULT 0) ENGINE=InnoDB');
db()->query('CREATE TABLE events_test_effect (id varchar(128) PRIMARY KEY, n int NOT NULL DEFAULT 0) ENGINE=InnoDB');
putenv('PLOOPI_EVENTS_ENABLED=0');
events_test_record('disabled');
T::eq('désactivé : aucune capture implicite', 0, events_test_count());
T::eq('désactivé : record() ne produit rien', null, ploopi\events::record('test.disabled.v1', array()));
putenv('PLOOPI_EVENTS_ENABLED=1');

T::section('Enregistrement et transaction appelante');
$objDb = ploopi\db::get();
$objDb->begin_transaction();
$objRollback = events_test_record('rolled-back');
T::eq('une autre connexion ne voit pas l’événement avant commit', 0, events_test_count());
$objDb->rollback();
T::eq('rollback : écriture métier annulée', 0, (int) db()->query("SELECT COUNT(*) AS n FROM events_test_record WHERE label = 'rolled-back'")->fetch_assoc()['n']);
T::eq('rollback : événement annulé', 0, events_test_count());
$objRecord = events_test_record('committed');
T::eq('écriture validée et événement ensemble', 1, events_test_count());
$arrCreated = db()->query("SELECT * FROM ploopi_event_outbox WHERE type = 'test.record.created.v1' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
$arrEnvelope = json_decode($arrCreated['payload'], true);
T::eq('CloudEvents 1.0', '1.0', $arrEnvelope['specversion']);
T::eq('identifiant du message stable en base', $arrCreated['id'], $arrEnvelope['id']);
T::eq('identifiant métier', (string) $objRecord->fields['id'], (string) $arrEnvelope['data']['id']);
$objRecord->save();
T::eq('save sans changement ne produit rien', 1, events_test_count());
$objRecord->fields['technical'] = 1;
$objRecord->save();
T::eq('champ technique ignoré par le contrat', 1, events_test_count());
$objRecord->fields['label'] = 'modified';
$objRecord->save();
T::eq('modification métier capturée', 2, events_test_count());
$objRecord->delete();
T::eq('suppression capturée', 3, events_test_count());
$objRecord->delete();
T::eq('suppression inexistante sans faux événement', 3, events_test_count());

T::section('Savepoints et échecs SQL');
$objDb->transaction(function () use ($objDb)
{
    events_test_record('outer');
    try { $objDb->transaction(function () { events_test_record('inner-aborted'); throw new RuntimeException('annulation interne'); }); }
    catch (RuntimeException $objError) { }
    events_test_record('outer-after');
});
T::eq('écriture externe préservée après rollback interne', 2, (int) db()->query("SELECT COUNT(*) AS n FROM events_test_record WHERE label IN ('outer','outer-after')")->fetch_assoc()['n']);
T::eq('écriture interne annulée', 0, (int) db()->query("SELECT COUNT(*) AS n FROM events_test_record WHERE label = 'inner-aborted'")->fetch_assoc()['n']);
$intBefore = events_test_count();
try { events_test_record('outer'); T::vrai('erreur SQL transactionnelle propagée', false); }
catch (RuntimeException $objError) { T::vrai('erreur SQL transactionnelle propagée', true); }
T::eq('écriture refusée sans événement', $intBefore, events_test_count());
$objNew = events_test_record('after-error');
T::vrai('connexion réutilisable après rollback', (int) $objNew->fields['id'] > 0);
try
{
    $objDb->transaction(function ()
    {
        ploopi\db::get()->query("INSERT INTO events_test_record (label) VALUES ('bad-json')");
        ploopi\events::record('test.invalid.v1', array('value' => "\xB1"));
    });
    T::vrai('échec de sérialisation propagé', false);
}
catch (JsonException $objError) { T::vrai('échec de sérialisation propagé', true); }
T::eq('échec de sérialisation annule l’écriture', 0, (int) db()->query("SELECT COUNT(*) AS n FROM events_test_record WHERE label = 'bad-json'")->fetch_assoc()['n']);

T::section('Producteur du socle et confidentialité');
$objUser = new ploopi\user();
$objUser->init_description();
$objUser->fields['login'] = 'events_fixture_'.getmypid();
$objUser->fields['password'] = ploopi\user::generate_hash('Secret!Events2026', $objUser->fields['login']);
$objUser->save();
$intEventsUser = (int) $objUser->fields['id'];
$arrUserEvent = db()->query("SELECT payload FROM ploopi_event_outbox WHERE type = 'user.created.v1' ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
T::vrai('création utilisateur réellement capturée', (bool) $arrUserEvent);
T::contientpas('aucun mot de passe dans l’événement', 'password', $arrUserEvent['payload']);
T::contientpas('aucune valeur personnelle dans l’événement', 'events_fixture_', $arrUserEvent['payload']);
$intBefore = events_test_count();
$objUser->fields['last_connection'] = date('YmdHis');
$objUser->save();
T::eq('connexion technique sans événement utilisateur', $intBefore, events_test_count());

T::section('Producteurs Wiki et Actualités réels');
foreach (array('wiki', 'news2') as $strType)
{
    $strDir = PLOOPI_ROOT.'/modules/'.$strType;
    if (!is_dir($strDir))
    {
        ploopi\fs::copydir(PLOOPI_ROOT.'/install/'.$strType.'/files', $strDir);
        $arrEventsDeployed[] = $strDir;
    }
    $strTable = $strType === 'wiki' ? 'ploopi_mod_wiki_page' : 'ploopi_mod_news2_entry';
    if (!db()->query("SHOW TABLES LIKE '{$strTable}'")->num_rows)
        foreach (ploopi\db::split_sql(file_get_contents(PLOOPI_ROOT.'/install/'.$strType.'/structure.sql')) as $strQuery) if (trim($strQuery) !== '') db()->query($strQuery);
    $objModule = new ploopi\data_object('ploopi_module');
    $objModule->init_description();
    $objModule->fields['label'] = 'events_fixture_'.$strType;
    $objModule->fields['id_module_type'] = 1;
    $objModule->fields['id_workspace'] = 2;
    $objModule->save();
    $arrEventsModuleIds[$strType] = (int) $objModule->fields['id'];
}
$_SESSION['ploopi'] = array('userid' => $intEventsUser, 'workspaceid' => 2, 'moduleid' => $arrEventsModuleIds['wiki'], 'modules' => array(), 'moduletypes' => array());
ploopi\module::init('wiki', false, false, false);
require_once PLOOPI_ROOT.'/modules/wiki/classes/class_wiki_page.php';
$objPage = new wiki_page();
$objPage->init_description();
$objPage->fields['id'] = 'events-fixture-page';
$objPage->fields['content'] = 'Texte de test';
$objPage->save();
T::eq('création wiki capturée par la classe réelle', 1, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_outbox WHERE type = 'wiki.page.created.v1' AND payload LIKE '%".db()->real_escape_string($strEventsSource)."%'")->fetch_assoc()['n']);
$objPage->open('events-fixture-page', $arrEventsModuleIds['wiki']);
$objPage->fields['content'] = 'Texte modifié';
$objPage->save();
T::eq('modification wiki capturée avec son historique', 1, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_mod_wiki_page_history WHERE id_module = ".$arrEventsModuleIds['wiki'])->fetch_assoc()['n']);
$arrWikiEvent = json_decode(db()->query("SELECT payload FROM ploopi_event_outbox WHERE type = 'wiki.page.updated.v1' AND payload LIKE '%".db()->real_escape_string($strEventsSource)."%' LIMIT 1")->fetch_assoc()['payload'], true);
T::eq('clé composite documentée', 'events-fixture-page', $arrWikiEvent['data']['id']['id']);
$objPage->delete();
T::eq('suppression wiki capturée', 1, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_outbox WHERE type = 'wiki.page.deleted.v1' AND payload LIKE '%".db()->real_escape_string($strEventsSource)."%'")->fetch_assoc()['n']);
$objNews = new ploopi\news2\news2();
$objNews->init_description();
$objNews->fields['title'] = 'Actualité test';
$objNews->fields['id_module'] = $arrEventsModuleIds['news2'];
$objNews->fields['id_workspace'] = 2;
$objNews->save();
$objNews->fields['published'] = 1;
$objNews->save();
$objNews->delete();
T::eq('cycle complet des actualités capturé', 3, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_outbox WHERE type LIKE 'news.article.%%' AND payload LIKE '%".db()->real_escape_string($strEventsSource)."%'")->fetch_assoc()['n']);
$objDb->begin_transaction();
try { (new ploopi\event_publisher())->flush(); T::vrai('publisher interdit dans une transaction métier', false); }
catch (LogicException $objError) { T::vrai('publisher interdit dans une transaction métier', true); }
$objDb->rollback();

T::section('Bail, tentative bornée et reprise manuelle');
events_test_clear_outbox();
$strEventId = ploopi\events::record('test.exhausted.v1', array('id' => 1));
db()->query("UPDATE ploopi_event_outbox SET attempts = 10, status = 'publishing', locked_until = 0 WHERE id = '{$strEventId}'");
$arrStats = (new ploopi\event_publisher())->flush(1);
T::eq('dix tentatives finissent en échec visible', 'failed', db()->query("SELECT status FROM ploopi_event_outbox WHERE id = '{$strEventId}'")->fetch_assoc()['status']);
T::vrai('reprise explicite possible', ploopi\events::retry($strEventId));
T::eq('la reprise remet le compteur à zéro', 0, (int) db()->query("SELECT attempts FROM ploopi_event_outbox WHERE id = '{$strEventId}'")->fetch_assoc()['attempts']);
T::vrai('pas de reprise d’un événement déjà en attente', !ploopi\events::retry($strEventId));
events_test_clear_outbox();

T::section('Administration système et CLI');
H::reset(); H::connexion();
$arrEnv = explode('/', H::env());
$arrEnv[0] = _PLOOPI_MENU_WORKSPACES; $arrEnv[1] = 2; $arrEnv[2] = 1; $arrEnv[3] = 'admin';
$strDashboard = '/admin.php?system_level=system&sysToolbarItem=events&ploopi_env='.urlencode(implode('/', $arrEnv));
$arrRep = H::req($strDashboard);
T::eq('écran système accessible à l’admin', 200, $arrRep['code']);
T::contient('suivi d’outbox rendu', 'Derniers événements', html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
// Gabarit historique : blocs et tableau du skin, sans composant de ploopi4
$strEventsTemplate = (string) db()->query('SELECT template FROM ploopi_workspace WHERE id = 2')->fetch_row()[0];
db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
H::reset(); H::connexion();
$arrEnv = explode('/', H::env());
$arrEnv[0] = _PLOOPI_MENU_WORKSPACES; $arrEnv[1] = 2; $arrEnv[2] = 1; $arrEnv[3] = 'admin';
$arrRep = H::req('/admin.php?system_level=system&sysToolbarItem=events&ploopi_env='.urlencode(implode('/', $arrEnv)));
T::eq('ploopi3 : écran système accessible', 200, $arrRep['code']);
T::contient('ploopi3 : suivi d’outbox rendu', 'Derniers événements', html_entity_decode($arrRep['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
T::contientpas('ploopi3 : pas de composant ploopi4', 'class="p4-stats', $arrRep['body']);
db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strEventsTemplate)."' WHERE id = 2");
H::reset(); H::connexion();
$arrEnv = explode('/', H::env());
$arrEnv[0] = _PLOOPI_MENU_WORKSPACES; $arrEnv[1] = 2; $arrEnv[2] = 1; $arrEnv[3] = 'admin';
$strDashboard = '/admin.php?system_level=system&sysToolbarItem=events&ploopi_env='.urlencode(implode('/', $arrEnv));
$strRetryId = ploopi\events::record('test.ui.v1', array());
db()->query("UPDATE ploopi_event_outbox SET status = 'failed', attempts = 10 WHERE id = '{$strRetryId}'");
$arrRep = H::req($strDashboard);
preg_match('@<form method="post" action="([^"]+)"@', $arrRep['body'], $arrForm);
T::vrai('formulaire de reprise disponible', !empty($arrForm[1]));
H::req('/admin.php?system_level=system&sysToolbarItem=events', 'event_retry='.$strRetryId);
T::eq('reprise sans CSRF refusée', 'failed', db()->query("SELECT status FROM ploopi_event_outbox WHERE id = '{$strRetryId}'")->fetch_assoc()['status']);
if (!empty($arrForm[1]))
{
    $strForm = '/'.ltrim(html_entity_decode($arrForm[1], ENT_QUOTES, 'UTF-8'), '/');
    H::req($strForm, 'event_retry='.$strRetryId);
    T::eq('reprise par le tableau de bord', 'pending', db()->query("SELECT status FROM ploopi_event_outbox WHERE id = '{$strRetryId}'")->fetch_assoc()['status']);
}
events_test_clear_outbox();

exec(PHP_BINARY.' '.escapeshellarg(PLOOPI_ROOT.'/bin/events.php').' status', $arrCliOutput, $intCliCode);
T::eq('commande CLI de diagnostic', 0, $intCliCode);
T::vrai('diagnostic CLI JSON', is_array(json_decode(implode("\n", $arrCliOutput), true)));

if (!getenv('PLOOPI_TEST_RABBITMQ_HOST'))
{
    T::skip('RabbitMQ réel : publication, redélivrance, retries et concurrence', 'lancer tests/docker.sh 8.5 --events');
    exit(T::bilan('46_events'));
}
T::section('RabbitMQ réel : panne, conservation et confirmation');
$strEventId = ploopi\events::record('test.delivery.v1', array('id' => 42));
$strPayload = events_test_payload($strEventId);
putenv('PLOOPI_RABBITMQ_PORT=1');
$arrStats = (new ploopi\event_publisher())->flush(1);
$arrPending = db()->query("SELECT * FROM ploopi_event_outbox WHERE id = '{$strEventId}'")->fetch_assoc();
T::eq('connexion impossible : message conservé', 'pending', $arrPending['status']);
T::eq('connexion impossible : aucun faux succès', 0, $arrStats['published']);
T::eq('charge utile inchangée après panne', $strPayload, $arrPending['payload']);
T::vrai('reprise différée avec backoff', (int) $arrPending['next_attempt_at'] > time());
putenv('PLOOPI_RABBITMQ_PORT='.($arrEventsPreviousEnv['PLOOPI_RABBITMQ_PORT'] ?: '5672'));
db()->query("UPDATE ploopi_event_outbox SET next_attempt_at = 0, status = 'publishing', locked_until = 1, locked_by = 'worker-interrompu' WHERE id = '{$strEventId}'");
$arrStats = (new ploopi\event_publisher())->flush(1);
T::eq('bail expiré repris et publication confirmée', 1, $arrStats['published']);
T::eq('état publié après confirmation', 'published', db()->query("SELECT status FROM ploopi_event_outbox WHERE id = '{$strEventId}'")->fetch_assoc()['status']);
$objEventsTransport = new ploopi\rabbitmq();
$objMessage = $objEventsTransport->get();
T::vrai('message reçu depuis le broker réel', (bool) $objMessage);
if (!$objMessage) exit(T::bilan('46_events'));
T::eq('identifiant préservé dans AMQP', $strEventId, $objMessage->get('message_id'));
T::eq('message persistant', 2, $objMessage->get('delivery_mode'));
T::eq('charge CloudEvents transmise sans modification', $strPayload, $objMessage->getBody());
$objEventsTransport->channel()->basic_nack($objMessage->getDeliveryTag(), false, true);

$objDb->begin_transaction();
try
{
    (new ploopi\event_consumer($objEventsTransport, array()))->once();
    T::vrai('consommateur interdit dans une transaction englobante', false);
}
catch (LogicException $objError) { T::vrai('consommateur interdit dans une transaction englobante', true); }
$objDb->rollback();

T::section('Déduplication après redélivrance et effet transactionnel');
$fnEffect = static function ($arrEvent)
{
    $strId = ploopi\db::get()->addslashes($arrEvent['id']);
    ploopi\db::get()->query("INSERT INTO events_test_effect (id, n) VALUES ('{$strId}', 1) ON DUPLICATE KEY UPDATE n = n + 1");
};
$objConsumer = new ploopi\event_consumer($objEventsTransport, array('test.delivery.v1' => array($fnEffect)));
T::vrai('redélivrance traitée', events_test_wait(function () use ($objConsumer) { return $objConsumer->once(); }));
$objEventsTransport->publish($strPayload, 'test.delivery.v1', $strEventId);
T::vrai('copie doublonnée consommée', events_test_wait(function () use ($objConsumer) { return $objConsumer->once(); }));
T::eq('effet métier appliqué une seule fois', 1, (int) db()->query("SELECT n FROM events_test_effect WHERE id = '{$strEventId}'")->fetch_assoc()['n']);
T::eq('une entrée inbox par source/id/consommateur', 1, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_inbox WHERE event_id = '{$strEventId}'")->fetch_assoc()['n']);

T::section('Reprises différées et file d’échec');
$arrPoison = events_test_envelope('test.poison.v1');
events_test_send($arrPoison);
$intHandlerCalls = 0;
$objPoisonConsumer = new ploopi\event_consumer($objEventsTransport, array('test.poison.v1' => array(static function ($arrEvent) use ($fnEffect, &$intHandlerCalls)
{
    $intHandlerCalls++;
    $fnEffect($arrEvent);
    throw new RuntimeException('échec de traitement simulé');
})));
T::vrai('trois reprises puis rejet, sans boucle immédiate', events_test_wait(function () use ($objPoisonConsumer, &$intHandlerCalls)
{
    $objPoisonConsumer->once();
    return $intHandlerCalls === 4;
}, 25));
T::eq('écriture du gestionnaire annulée après chaque échec', 0, (int) db()->query("SELECT COUNT(*) AS n FROM events_test_effect WHERE id = '{$arrPoison['id']}'")->fetch_assoc()['n']);
T::eq('échec sans faux acquittement inbox', 0, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_inbox WHERE event_id = '{$arrPoison['id']}'")->fetch_assoc()['n']);
$objDead = null;
T::vrai('message conservé dans la file d’échec', events_test_wait(function () use (&$objDead, $objEventsTransport, $strEventsQueue)
{
    $objDead = $objEventsTransport->channel()->basic_get($strEventsQueue.'.dead', true);
    return (bool) $objDead;
}));
if ($objDead) T::eq('identité conservée dans la file d’échec', $arrPoison['id'], $objDead->get('message_id'));
$objEventsTransport->publish('not-json', 'test.invalid.v1', bin2hex(random_bytes(16)));
T::vrai('message invalide rejeté proprement', events_test_wait(function () use ($objConsumer) { return $objConsumer->once(); }));

T::section('Deux publishers concurrents');
events_test_clear_outbox();
$objEventsTransport->channel()->queue_purge($strEventsQueue);
for ($intIndex = 0; $intIndex < 12; $intIndex++) ploopi\events::record('test.concurrent.v1', array('id' => $intIndex));
$arrProcesses = array();
for ($intIndex = 0; $intIndex < 2; $intIndex++)
{
    $arrPipes = array();
    $resProcess = proc_open(array(PHP_BINARY, PLOOPI_ROOT.'/bin/events.php', 'publish', '--limit=12'), array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $arrPipes);
    fclose($arrPipes[0]);
    $arrProcesses[] = array($resProcess, $arrPipes);
}
foreach ($arrProcesses as $intIndex => $arrProcess)
{
    stream_get_contents($arrProcess[1][1]); stream_get_contents($arrProcess[1][2]);
    fclose($arrProcess[1][1]); fclose($arrProcess[1][2]);
    T::eq('publisher concurrent '.($intIndex + 1).' terminé', 0, proc_close($arrProcess[0]));
}
T::eq('douze événements confirmés', 12, (int) db()->query("SELECT COUNT(*) AS n FROM ploopi_event_outbox WHERE status = 'published' AND payload LIKE '%".db()->real_escape_string($strEventsSource)."%'")->fetch_assoc()['n']);
$arrReceivedIds = array();
while ($objMessage = $objEventsTransport->get())
{
    $arrReceivedIds[] = $objMessage->get('message_id');
    $objEventsTransport->acknowledge($objMessage);
}
T::eq('aucune perte avec deux publishers', 12, count(array_unique($arrReceivedIds)));
T::eq('aucune double réservation dans ce passage', 12, count($arrReceivedIds));

T::section('Routage obligatoire');
putenv('PLOOPI_RABBITMQ_EXCHANGE='.$strEventsExchange.'.unrouted');
$objUnrouted = new ploopi\rabbitmq($strEventsQueue.'.unrouted', array('never.v1'));
try { $objUnrouted->publish('{}', 'test.unrouted.v1', bin2hex(random_bytes(16))); T::vrai('message sans route refusé malgré confirmation AMQP', false); }
catch (RuntimeException $objError) { T::vrai('message sans route refusé malgré confirmation AMQP', true); }
foreach (array('', '.retry', '.dead') as $strSuffix) $objUnrouted->channel()->queue_delete($strEventsQueue.'.unrouted'.$strSuffix);
foreach (array('', '.retry', '.dead') as $strSuffix) $objUnrouted->channel()->exchange_delete($strEventsExchange.'.unrouted'.$strSuffix);
$objUnrouted->close();
putenv('PLOOPI_RABBITMQ_EXCHANGE='.$strEventsExchange);

exit(T::bilan('46_events'));
