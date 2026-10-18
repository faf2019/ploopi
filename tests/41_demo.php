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
    along with Ploopi.  If not, see <https://www.gnu.org/licenses/>.
*/


/**
 * Suite 41 — site de démonstration (Système › Modules, ploopi\system\demo).
 *
 * Sur une installation vierge, un clic installe webedit et publie un site fictif.
 * Ce qu'on vérifie : le bouton n'est proposé qu'à une installation sans autre module
 * que system ; la génération passe par la procédure d'installation habituelle et
 * publie un site servi par le gabarit « corporate », recherche comprise ; une fois
 * un module installé, le bouton disparaît et l'opération est refusée.
 *
 * Destructive : installe puis retire webedit. Ne s'exécute que sur une instance
 * vierge (aucun module installé hors system), et remet l'espace dans son état.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

if (!cfg('allow_destructive')) { T::skip('suite entière', 'allow_destructive à false'); exit(T::bilan('41_demo')); }

$arrAutres = db()->query("SELECT COUNT(*) AS nb FROM ploopi_module_type WHERE label <> 'system'")->fetch_assoc();
if ((int) $arrAutres['nb'] > 0) { T::skip('suite entière', 'instance non vierge : des modules sont installés'); exit(T::bilan('41_demo')); }

$intEspace = (int) db()->query('SELECT id FROM ploopi_workspace WHERE system = 0 ORDER BY depth, id LIMIT 1')->fetch_assoc()['id'];
$arrEspace = db()->query("SELECT frontoffice, frontoffice_domainlist, title, meta_description, meta_copyright FROM ploopi_workspace WHERE id = {$intEspace}")->fetch_assoc();
$arrDemoModules = array('webedit', 'directory', 'planning', 'booking', 'news2', 'dbreport');
$arrDeployes = array();
foreach ($arrDemoModules as $strModule) $arrDeployes[$strModule] = is_dir(PLOOPI_ROOT."/modules/{$strModule}");

/**
 * Retire tout ce que la démonstration a créé. Appelé quoi qu'il arrive.
 */
register_shutdown_function(function() use ($intEspace, $arrEspace, $arrDemoModules, $arrDeployes) {
    foreach ($arrDemoModules as $strModule)
    {
        $arrType = db()->query("SELECT id FROM ploopi_module_type WHERE label = '{$strModule}'")->fetch_assoc();
        if (!$arrType) continue;

        $intType = (int) $arrType['id'];
        $arrRequetes = array(
            "DELETE ke FROM ploopi_index_keyword_element ke INNER JOIN ploopi_index_element e ON e.id = ke.id_element WHERE e.id_module IN (SELECT id FROM ploopi_module WHERE id_module_type = {$intType})",
            "DELETE FROM ploopi_index_element WHERE id_module IN (SELECT id FROM ploopi_module WHERE id_module_type = {$intType})",
            "DELETE FROM ploopi_param_default WHERE id_module_type = {$intType}",
            "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE id_module_type = {$intType})",
            "DELETE FROM ploopi_module WHERE id_module_type = {$intType}",
        );
        foreach (array('ploopi_param_type', 'ploopi_param_choice', 'ploopi_mb_action', 'ploopi_mb_table', 'ploopi_mb_field', 'ploopi_mb_schema', 'ploopi_mb_relation', 'ploopi_mb_object', 'ploopi_mb_wce_object') as $strTable)
            $arrRequetes[] = "DELETE FROM {$strTable} WHERE id_module_type = {$intType}";
        // Données du module : toutes ses tables
        $rs = db()->query("SHOW TABLES LIKE 'ploopi\\_mod\\_{$strModule}\\_%'");
        while ($row = $rs->fetch_row()) $arrRequetes[] = "DELETE FROM `{$row[0]}`";
        $arrRequetes[] = "DELETE FROM ploopi_module_type WHERE id = {$intType}";

        foreach ($arrRequetes as $strRequete) { try { db()->query($strRequete); } catch (Throwable $objErreur) { } }

        if (!$arrDeployes[$strModule] && is_dir(PLOOPI_ROOT."/modules/{$strModule}")) exec('rm -rf '.escapeshellarg(PLOOPI_ROOT."/modules/{$strModule}"));
    }

    db()->query(sprintf(
        "UPDATE ploopi_workspace SET frontoffice = %d, frontoffice_domainlist = '%s', title = '%s', meta_description = '%s', meta_copyright = '%s' WHERE id = %d",
        (int) $arrEspace['frontoffice'], db()->real_escape_string((string) $arrEspace['frontoffice_domainlist']), db()->real_escape_string((string) $arrEspace['title']),
        db()->real_escape_string((string) $arrEspace['meta_description']), db()->real_escape_string((string) $arrEspace['meta_copyright']), $intEspace
    ));
});

/**
 * Écran Système › Modules, administrateur système connecté.
 *
 * @return array réponse
 */
function demo_ecran()
{
    return H::req('/admin.php?system_level=system&sysToolbarItem=install&ploopi_env='.urlencode(H::env()));
}

/**
 * Lien chiffré du bouton de démonstration, '' s'il n'est pas proposé.
 */
function demo_lien($strCorps)
{
    preg_match('/<a[^>]*data-p4-confirm="[^"]*"[^>]*href="([^"]*)"[^>]*>(?:(?!<\/a>).)*G&eacute;n&eacute;rer le site de d&eacute;monstration/s', $strCorps, $arrMatches);
    if (empty($arrMatches)) preg_match('/<a[^>]*href="([^"]*)"[^>]*data-p4-confirm="[^"]*"[^>]*>(?:(?!<\/a>).)*G&eacute;n&eacute;rer le site de d&eacute;monstration/s', $strCorps, $arrMatches);

    return isset($arrMatches[1]) ? '/'.ltrim(preg_replace('#^\./#', '', html_entity_decode($arrMatches[1])), '/') : '';
}

T::section('Installation vierge : le bouton est proposé');
H::reset();
H::connexion();
$arrRep = demo_ecran();
$strLien = demo_lien($arrRep['body']);
T::vrai('le bouton « Générer le site de démonstration » est proposé', $strLien !== '');
T::contient('avec une confirmation', 'data-p4-confirm="Installer les modules webedit', $arrRep['body']);

T::section('Génération');
$arrRep = H::req($strLien);
// Génération puis redirection vers le compte rendu, session rechargée
T::eq('la génération redirige vers le compte rendu', 302, $arrRep['code']);
preg_match('/^location:\s*(\S+)/mi', $arrRep['headers'], $arrLoc);
$arrRep = H::req('/'.ltrim(preg_replace('#^https?://[^/]+#', '', $arrLoc[1] ?? ''), '/'));
T::eq('l\'opération répond', 200, $arrRep['code']);
preg_match('/<nav class="p4-nav"[\s\S]*?<\/nav>/', $arrRep['body'], $arrMenu);
T::contient('la session est rechargée : le menu montre les nouveaux modules', 'Rapports', html_entity_decode($arrMenu[0] ?? '', ENT_QUOTES, 'UTF-8'));
T::contient('webedit est installé par la procédure habituelle', 'description.xml&#039; import&eacute;', $arrRep['body']);
T::contient('les contenus sont publiés', 'rubriques et 12 articles publi&eacute;s', $arrRep['body']);
T::contient('le site public est ouvert', 'Frontoffice activ&eacute;', $arrRep['body']);

$arrType = db()->query("SELECT id FROM ploopi_module_type WHERE label = 'webedit'")->fetch_assoc();
T::vrai('le type de module webedit existe', !empty($arrType));
$arrNb = db()->query('SELECT (SELECT COUNT(*) FROM ploopi_mod_webedit_article) AS articles, (SELECT COUNT(*) FROM ploopi_mod_webedit_article_draft) AS brouillons')->fetch_assoc();
T::vrai('chaque article publié a son brouillon (éditable dans le backoffice)', (int) $arrNb['articles'] > 0 && $arrNb['articles'] === $arrNb['brouillons'], "{$arrNb['articles']} / {$arrNb['brouillons']}");

T::section('Autres modules, avec leurs données');
foreach (array('directory' => 'Annuaire', 'planning' => 'Agenda', 'booking' => 'R&eacute;servation de salles', 'news2' => 'Actualit&eacute;s', 'dbreport' => 'Rapports') as $strModule => $strLabel)
{
    $arrInst = db()->query("SELECT m.id FROM ploopi_module m INNER JOIN ploopi_module_type t ON t.id = m.id_module_type INNER JOIN ploopi_module_workspace mw ON mw.id_module = m.id WHERE t.label = '{$strModule}' AND mw.id_workspace = {$intEspace}")->fetch_assoc();
    T::vrai("{$strModule} : une instance dans l'espace", !empty($arrInst));
    T::contient("{$strModule} : ses données sont annoncées", 'Données « '.html_entity_decode($strLabel).' »', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));
}
$arrNb = db()->query("SELECT
    (SELECT COUNT(*) FROM ploopi_mod_directory_contact) AS contacts,
    (SELECT COUNT(*) FROM ploopi_mod_planning_event_detail WHERE timestp_begin >= ".date('Ymd', strtotime('monday this week'))."000000) AS rdv,
    (SELECT COUNT(*) FROM ploopi_mod_booking_event_detail) AS reservations,
    (SELECT COUNT(*) FROM ploopi_mod_booking_event_detail WHERE validated = 0) AS attente,
    (SELECT COUNT(*) FROM ploopi_mod_news2_entry WHERE published = 1) AS nouvelles,
    (SELECT COUNT(*) FROM ploopi_mod_dbreport_query) AS requetes")->fetch_assoc();
T::eq('annuaire : 9 contacts', 9, (int) $arrNb['contacts']);
T::eq('agenda : 8 rendez-vous dans la semaine courante', 8, (int) $arrNb['rdv']);
T::eq('réservation : 7 réservations, dont 2 à valider', '7/2', $arrNb['reservations'].'/'.$arrNb['attente']);
T::eq('actualités : 4 nouvelles publiées', 4, (int) $arrNb['nouvelles']);
T::eq('rapports : 2 requêtes', 2, (int) $arrNb['requetes']);

T::section('Site public');
H::reset();
$arrRep = H::req('/index.php');
T::eq('l\'accueil est servi', 200, $arrRep['code']);
T::contient('par le gabarit corporate', 'templates/frontoffice/corporate/css/corporate.css', $arrRep['body']);
T::contient('avec le bandeau d\'accueil', 'class="c-hero"', $arrRep['body']);
T::contient('et le menu des rubriques', 'Expertises', $arrRep['body']);
$arrRep = H::req('/index.php', 'query_string=accessibilite');
T::contient('la recherche trouve un article (indexation faite)', 'class="c-result"', $arrRep['body']);

T::section('Une fois un module installé');
H::reset();
H::connexion();
$arrRep = demo_ecran();
T::eq('le bouton n\'est plus proposé', '', demo_lien($arrRep['body']));
$arrRep = H::req($strLien);
// Refusée par le jeton (lien d'une session précédente) ou par demo::generate() : rien n'est créé
T::contientpas('l\'opération rejouée ne génère rien', 'Instance du module', html_entity_decode($arrRep['body'], ENT_QUOTES, 'UTF-8'));
$arrNb2 = db()->query("SELECT COUNT(*) AS nb FROM ploopi_module_type WHERE label = 'webedit'")->fetch_assoc();
T::eq('et n\'installe rien de plus', 1, (int) $arrNb2['nb']);

exit(T::bilan('41_demo'));
