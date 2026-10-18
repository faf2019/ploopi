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
 * Suite 04 — dépôt réel d'un fichier, puis installation neuve déroulée par l'assistant.
 *
 * ATTENTION : cette suite est DESTRUCTIVE. Elle retire temporairement config/config.php,
 * fait créer une base par l'assistant puis la supprime. Elle ne s'exécute que si
 * allow_destructive vaut true dans tests/config.php, et jamais sur la base de travail.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();

T::section('Dépôt réel d\'un fichier par documents::savefile()');
amorcer_socle();
$strSource = sys_get_temp_dir().'/ploopi_tests_depot_'.getmypid().'.php';
file_put_contents($strSource, "<?php echo 'charge'; ?>");
$arrAvant = glob(PLOOPI_ROOT.'/data/documents/*/*.php');
db()->query("DELETE FROM ploopi_documents_file WHERE name = 'depot-verif.PHP'");
ploopi\documents::savefile(1, '1', $strSource, 'depot-verif.PHP', 0, 'dépôt', '', '', 2, 2, 1);
@unlink($strSource);

$arrLigne = db()->query("SELECT id, name, extension, timestp_create FROM ploopi_documents_file
                         WHERE name = 'depot-verif.PHP' ORDER BY id DESC LIMIT 1")->fetch_assoc();
T::chk('ligne insérée en base',                     !empty($arrLigne));
if ($arrLigne)
{
    $strChemin = PLOOPI_ROOT.'/data/documents/'.substr($arrLigne['timestp_create'], 0, 8).'/'.$arrLigne['id'].'.'.$arrLigne['extension'];
    T::chk('nom d\'affichage conservé',              $arrLigne['name'] === 'depot-verif.PHP');
    T::chk('extension stockée neutralisée',         $arrLigne['extension'] === 'bin', $arrLigne['extension']);
    // Documents rangés dans un dépôt S3 : la suite 42 éprouve le stockage
    if (defined('_PLOOPI_S3_ACTIVATED') && _PLOOPI_S3_ACTIVATED) T::skip('fichier présent sur disque', 'documents rangés dans le dépôt S3 (suite 42)');
    else
    {
        T::chk('fichier présent sur disque',            file_exists($strChemin), basename($strChemin));
        T::chk('identifiant présent dans le nom',       basename($strChemin) === $arrLigne['id'].'.bin');
    }
    $objDoc = new ploopi\documentsfile();
    if ($objDoc->open($arrLigne['id'])) $objDoc->delete();
    @unlink($strChemin);
    db()->query('DELETE FROM ploopi_documents_file WHERE id = '.(int)$arrLigne['id']);
}
T::chk('aucun .php ajouté dans data/documents',     glob(PLOOPI_ROOT.'/data/documents/*/*.php') === $arrAvant);

T::section('Installation neuve déroulée par l\'assistant');

if (!cfg('allow_destructive'))
{
    T::skip('installation neuve de bout en bout',   'allow_destructive est à false dans tests/config.php');
    exit(T::bilan());
}

if (cfg('install_db') === cfg('db_name'))
{
    fwrite(STDERR, "install_db est identique à db_name : refus d'exécuter, la base de travail serait détruite.\n");
    exit(2);
}

$strConfig = PLOOPI_ROOT.'/config/config.php';
$strSauvegarde = sys_get_temp_dir().'/ploopi_tests_config_'.getmypid().'.php';

if (!copy($strConfig, $strSauvegarde))
{
    fwrite(STDERR, "Sauvegarde de config.php impossible : refus d'exécuter.\n");
    exit(2);
}

// À partir d'ici, config.php doit être restauré quoi qu'il arrive
register_shutdown_function(function() use ($strConfig, $strSauvegarde) {
    if (file_exists($strSauvegarde)) { copy($strSauvegarde, $strConfig); unlink($strSauvegarde); }
});

unlink($strConfig);
clearstatcache();
T::chk('config.php retiré : l\'assistant prend la main', !file_exists($strConfig));

// La clé secrète proposée par le formulaire est celle que l'assistant génère : c'est elle
// que l'on veut éprouver, on la relit donc plutôt que d'en fournir une.
$arrForm = H::req('/');
T::chk('formulaire d\'installation servi',          strpos($arrForm['body'], 'name="SECRETKEY"') !== false);
preg_match('/name="SECRETKEY" value="([^"]*)"/', $arrForm['body'], $arrMatches);
$strCleProposee = $arrMatches[1] ?? '';
T::chk('clé proposée d\'au moins 43 caractères',    strlen($strCleProposee) >= 43, strlen($strCleProposee).' caractères');
$intPos = strpos($arrForm['body'], 'name="HASH_ALGO"');
preg_match_all('/<option value="([^"]*)"/', substr($arrForm['body'], $intPos, 600), $arrOptions);
T::chk('un seul algorithme de hachage proposé',     count($arrOptions[1]) === 1, implode(', ', $arrOptions[1]));
T::chk('et c\'est bcrypt',                          ($arrOptions[1][0] ?? '') === 'bcrypt');

// Présentation de ploopi4 : feuilles et sprite du gabarit, aucun script ni gestionnaire en ligne
T::chk('assistant habillé par ploopi4',             strpos($arrForm['body'], 'templates/backoffice/ploopi4/css/styles.pack.css') !== false && strpos($arrForm['body'], 'class="p4-install') !== false);
T::chk('aucun script en ligne',                     !preg_match('/<script(?![^>]*\bsrc=)[^>]*>/i', $arrForm['body']));
T::chk('aucun gestionnaire d\'évènement en ligne', !preg_match('/\son[a-z]+\s*=/i', $arrForm['body']));
T::chk('prérequis affichés : dossier de configuration accessible', (bool)preg_match('/<li class="p4-ok"[^>]*>(?:(?!<\/li>).)*Dossier de configuration/s', $arrForm['body']));

// Une saisie refusée : rien n'est installé, l'assistant signale les champs en cause
$arrRep = H::req('/', http_build_query(array(
    'ADMIN_LOGIN' => '', 'ADMIN_PASSWORD' => 'x', 'ADMIN_MAIL' => '',
    'DB_SERVER' => cfg('db_host'), 'DB_LOGIN' => cfg('db_user'), 'DB_PASSWORD' => cfg('db_pass'),
    'DB_DATABASE' => '1mauvais-nom', 'DATAPATH' => PLOOPI_ROOT.'/data', 'SYS_MAIL' => '',
    'CIPHER' => 'AES-128-CBC', 'HASH_ALGO' => 'bcrypt', 'SECRETKEY' => $strCleProposee,
)));
clearstatcache();
T::chk('saisie refusée : redirection, pas de config.php', $arrRep['code'] === 302 && !file_exists($strConfig), "HTTP {$arrRep['code']}");
$arrForm = H::req('/');
T::chk('saisie refusée : message d\'échec',         strpos($arrForm['body'], 'L\'installation n\'a pas abouti') !== false);
T::chk('saisie refusée : nom de base signalé',      (bool)preg_match('/class="[^"]*\bp4-err\b[^"]*" data-field="DB_DATABASE"/', $arrForm['body']));
T::chk('saisie refusée : identifiant vide signalé', (bool)preg_match('/class="[^"]*\bp4-err\b[^"]*" data-field="ADMIN_LOGIN"/', $arrForm['body']));
T::chk('saisie refusée : serveur accepté',          (bool)preg_match('/class="[^"]*\bp4-ok\b[^"]*" data-field="DB_SERVER"/', $arrForm['body']));
T::chk('saisie refusée : valeurs conservées',       strpos($arrForm['body'], 'value="1mauvais-nom"') !== false);

$strLoginTest = 'verif_admin';
$strMdpTest   = 'V3rif!Mdp2026';
$arrRep = H::req('/', http_build_query(array(
    'ADMIN_LOGIN' => $strLoginTest, 'ADMIN_PASSWORD' => $strMdpTest, 'ADMIN_MAIL' => 'verif@example.test',
    'DB_SERVER' => cfg('db_host'), 'DB_LOGIN' => cfg('db_user'), 'DB_PASSWORD' => cfg('db_pass'),
    'DB_DATABASE' => cfg('install_db'), 'DATAPATH' => PLOOPI_ROOT.'/data',
    'SYS_MAIL' => 'sys@example.test',
    'CIPHER' => 'AES-128-CBC', 'HASH_ALGO' => 'bcrypt', 'SECRETKEY' => $strCleProposee,
)));
T::chk('assistant répond',                          in_array($arrRep['code'], array(200, 302)), "HTTP {$arrRep['code']}");
clearstatcache();
T::chk('config.php généré',                         file_exists($strConfig));

if (file_exists($strConfig))
{
    $strGenere = file_get_contents($strConfig);
    exec('php -l '.escapeshellarg($strConfig).' 2>&1', $arrSortie, $intRc);
    T::chk('config.php généré : syntaxe valide',    $intRc === 0, implode(' ', $arrSortie));
    preg_match_all('/<[A-Z_]+>/', $strGenere, $arrMatches);
    T::chk('aucun paramètre non substitué',         empty(array_unique($arrMatches[0])), implode(', ', array_unique($arrMatches[0])));
    preg_match("/_PLOOPI_SECRETKEY', *'([^']*)'/", $strGenere, $arrMatches);
    $strCleEcrite = $arrMatches[1] ?? '';
    T::chk('clé écrite d\'au moins 43 caractères',  strlen($strCleEcrite) >= 43, strlen($strCleEcrite).' caractères');
    T::chk('clé écrite conforme à la clé proposée', $strCleEcrite === $strCleProposee);
    preg_match("/_PLOOPI_HASH_ALGO', *'([^']*)'/", $strGenere, $arrMatches);
    T::chk('algorithme retenu : bcrypt',            ($arrMatches[1] ?? '') === 'bcrypt');

    $objNeuve = new mysqli(cfg('db_host'), cfg('db_user'), cfg('db_pass'), cfg('install_db'), 3306);
    T::chk('base neuve créée',                      !$objNeuve->connect_errno);

    if (!$objNeuve->connect_errno)
    {
        $intTables = (int)$objNeuve->query("SELECT COUNT(*) c FROM information_schema.tables
                                            WHERE table_schema = '".$objNeuve->real_escape_string(cfg('install_db'))."'")->fetch_assoc()['c'];
        T::chk('schéma importé',                    $intTables > 40, "{$intTables} tables");
        $arrUser = $objNeuve->query('SELECT login, password, email FROM ploopi_user')->fetch_assoc();
        T::chk('compte administrateur renommé',     ($arrUser['login'] ?? '') === $strLoginTest, $arrUser['login'] ?? '');
        T::chk('adresse enregistrée',               ($arrUser['email'] ?? '') === 'verif@example.test');
        T::chk('empreinte au format bcrypt',        (bool)preg_match('/^\$2y\$/', $arrUser['password'] ?? ''));
        // entrée de bcrypt calculée comme le socle (user::bcrypt_input), clé de l'instance installée
        T::chk('mot de passe vérifiable',           password_verify(ploopi\user::bcrypt_input($strMdpTest, $strLoginTest, $strCleEcrite), $arrUser['password'] ?? ''));
        $objNeuve->query('DROP DATABASE `'.str_replace('`', '', cfg('install_db')).'`');
        $objNeuve->close();
    }
}

// Remise en place de l'instance de travail
copy($strSauvegarde, $strConfig);
unlink($strSauvegarde);
clearstatcache();
T::chk('configuration d\'origine restaurée',        file_exists($strConfig) && strpos(file_get_contents($strConfig), cfg('install_db')) === false);
H::reset();
$arrRep = H::connexion();
T::chk('instance de travail de nouveau fonctionnelle', $arrRep['code'] === 302 && stripos($arrRep['headers'], 'Set-Cookie') !== false);

exit(T::bilan());
