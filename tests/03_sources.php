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
 * Suite 03 — contrôles sur les sources et sur les échappements, sans effet de bord.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

$strInstalleur = file_get_contents(PLOOPI_ROOT.'/config/install/install.php');

T::section('Le modèle de configuration produit un fichier valide');
$arrValeurs = array('ADMIN_MAIL' => 'a@b.test', 'CIPHER' => 'AES-128-CBC', 'DATAPATH' => '/tmp',
    'DB_DATABASE' => 'd', 'DB_LOGIN' => 'l', 'DB_PASSWORD' => 'p', 'DB_SERVER' => '127.0.0.1',
    'HASH_ALGO' => 'bcrypt', 'NBCORE' => 1, 'PUBLIC_URL' => 'https://www.exemple.test', 'SECRETKEY' => 'k', 'SYS_MAIL' => 's@b.test');
$arrTags = $arrRemplacements = array();
foreach ($arrValeurs as $strCle => $strVal) { $arrTags[] = "<{$strCle}>"; $arrRemplacements[] = $strVal; }
$arrTags[] = '<CIPHER_IV>'; $arrRemplacements[] = substr(base64_encode(random_bytes(16)), 0, 16);
$strRendu = str_replace($arrTags, $arrRemplacements, file_get_contents(PLOOPI_ROOT.'/config/config.php.model'));
$strFichier = tempnam(sys_get_temp_dir(), 'ploopi_cfg').'.php';
file_put_contents($strFichier, $strRendu);
preg_match_all('/<[A-Z_]+>/', $strRendu, $arrMatches);
T::chk('aucun paramètre non substitué',             empty(array_unique($arrMatches[0])), implode(', ', array_unique($arrMatches[0])));
exec('php -l '.escapeshellarg($strFichier).' 2>&1', $arrSortie, $intRc);
T::chk('configuration rendue : syntaxe valide',     $intRc === 0, implode(' ', $arrSortie));
T::chk('stockage S3 désactivé par défaut',          strpos($strRendu, "_PLOOPI_S3_ACTIVATED', false") !== false);
@unlink($strFichier);

T::section('Clé secrète et algorithme proposés par l\'assistant');
preg_match("/\\\$S\['secretkey'\] = ([^;]+);/", $strInstalleur, $arrMatches);
T::chk('expression de génération trouvée',          !empty($arrMatches[1]), trim($arrMatches[1] ?? ''));
T::chk('clé issue de random_bytes',                 strpos($arrMatches[1] ?? '', 'random_bytes') !== false);
if (!empty($arrMatches[1]))
{
    $strCle = eval('return '.$arrMatches[1].';');
    T::chk('clé d\'au moins 43 caractères',         strlen($strCle) >= 43, strlen($strCle).' caractères');
}
preg_match("/'HASH_ALGO' => \[(.*?)\n        \],/s", $strInstalleur, $arrMatches);
$strBloc = $arrMatches[1] ?? '';
T::chk('bcrypt proposé',                            strpos($strBloc, "'bcrypt'") !== false);
foreach (array('md5', 'sha1', 'sha256', 'whirlpool') as $strAlgo)
    T::chk("{$strAlgo} retiré des choix",           strpos($strBloc, "'{$strAlgo}' =>") === false);

T::section('Assistant : arrêt après chaque redirection');
// Sans exit, une connexion refusée poursuivait l'import (requêtes sur une connexion
// fermée, config.php supprimé deux fois) avant que le navigateur ne suive la redirection
preg_match_all("/header\('Location: \.'\);\s*(\S+)/", $strInstalleur, $arrMatches);
T::chk('redirections trouvées',                     count($arrMatches[1]) >= 4, count($arrMatches[1]).' redirections');
T::chk('chacune suivie d\'un exit',                 count(array_filter($arrMatches[1], function($s) { return $s !== 'exit;'; })) === 0, implode(' | ', $arrMatches[1]));
T::chk('script de l\'assistant hors de la page (jQuery inutile)', strpos($strInstalleur, 'templates/install/js/install.js') !== false && strpos($strInstalleur, 'jquery') === false);

T::section('Échappement SQL de l\'assistant');
T::chk('login administrateur échappé',              strpos($strInstalleur, "real_escape_string(\$S['saved']['ADMIN_LOGIN'])") !== false);
T::chk('adresse administrateur échappée',           strpos($strInstalleur, "real_escape_string(\$S['saved']['ADMIN_MAIL'])") !== false);
T::chk('nom de base contraint avant interpolation', strpos($strInstalleur, "preg_match('@^[A-Za-z0-9_]{1,64}\$@', \$S['saved']['DB_DATABASE'])") !== false);
// Exécution réelle : la charge utile doit rester une donnée
if (!bdd_disponible())
{
    T::skip('échappement SQL éprouvé en base', 'aucune base de données joignable');
    exit(T::bilan());
}
db()->query('DROP TABLE IF EXISTS ploopi_tests_sqli');
db()->query('CREATE TABLE ploopi_tests_sqli (login VARCHAR(190), email VARCHAR(190))');
db()->query('INSERT INTO ploopi_tests_sqli VALUES ("admin","a@a"),("bob","b@b"),("eve","e@e")');
$strCharge = 'x", `email`="pwn" WHERE 1=1 -- ';
db()->query('UPDATE ploopi_tests_sqli SET `login` = "'.db()->real_escape_string($strCharge).'", `email` = "'
    .db()->real_escape_string('o"neil@example.test').'" WHERE `login` = "admin"');
$arrLignes = db()->query('SELECT login, email FROM ploopi_tests_sqli')->fetch_all(MYSQLI_ASSOC);
T::chk('seule la ligne visée est modifiée',         count(array_filter($arrLignes, function($r) { return $r['email'] === 'o"neil@example.test'; })) === 1);
T::chk('les autres lignes sont intactes',           count(array_filter($arrLignes, function($r) { return in_array($r['email'], array('b@b', 'e@e')); })) === 2);
T::chk('aucune ligne marquée pwn',                  !array_filter($arrLignes, function($r) { return $r['email'] === 'pwn'; }));
T::chk('charge utile stockée comme donnée',         (bool)array_filter($arrLignes, function($r) use ($strCharge) { return $r['login'] === $strCharge; }));
db()->query('DROP TABLE ploopi_tests_sqli');
foreach (array('ploopi' => true, 'ma_base_2' => true, 'a`; DROP DATABASE x; --' => false,
               'base-tiret' => false, '' => false, str_repeat('a', 65) => false) as $strNom => $booAttendu)
    T::chk('nom de base '.var_export(substr($strNom, 0, 22), true), (bool)preg_match('@^[A-Za-z0-9_]{1,64}$@', $strNom) === $booAttendu);

T::section('Descriptions de la GED attachée (include/op/documents.php)');
/**
 * Les descriptions de dossier et de fichier sont saisies dans l'éditeur du socle : écrites
 * brutes dans le textarea, un « </textarea> » enregistré sortait du champ ; enregistrées
 * sans filtre, elles portaient un script affiché ensuite dans la GED.
 */
$strDocuments = file_get_contents(PLOOPI_ROOT.'/include/op/documents.php');
preg_match_all('@data-ploopi-editor="[a-z]+">(.*?)</textarea>@s', $strDocuments, $arrChamps);
T::chk('champs de description déclarés à l\'éditeur', count($arrChamps[1]) === 2, count($arrChamps[1]).' champ(s)');
T::chk('contenu échappé dans chaque champ', count(array_filter($arrChamps[1], function($s) { return strpos($s, 'ploopi\\str::htmlentities(') === false; })) === 0);
preg_match_all('@->fields\[\'description\'\] = ([^;]+);@', $strDocuments, $arrAffectations);
T::chk('descriptions enregistrées trouvées', count($arrAffectations[1]) === 4, count($arrAffectations[1]).' affectation(s)');
T::chk('chacune filtrée par HTMLPurifier', count(array_filter($arrAffectations[1], function($s) { return strpos($s, 'ploopi\\str::htmlpurifier($_POST[') !== 0; })) === 0, implode(' | ', $arrAffectations[1]));

T::section('Garde sur la désinstallation de module');
$strUninstall = file_get_contents(PLOOPI_ROOT.'/modules/system/admin_system_installmodules_uninstallproc.php');
T::chk('contrôle is_numeric présent',               strpos($strUninstall, "!is_numeric(\$_GET['uninstallidmoduletype'])") !== false);
T::chk('identifiant repris de l\'enregistrement',   strpos($strUninstall, "intval(\$module_type->fields['id'])") !== false);
T::chk('plus d\'interpolation de superglobale en SQL', !preg_match('/id_module_type = \{\$_GET/', $strUninstall));

T::section('Échappement des arguments passés au shell');
$arrCibles = array(
    'modules/system/op.php'             => array('escapeshellarg($strUnoconvPath)', 'escapeshellarg($strTypeDoc)', 'escapeshellarg($strTmpFile)'),
    'include/classes/uno_converter.php' => array('escapeshellarg($outputType)', 'escapeshellarg($srcFile)', 'escapeshellarg($dstFile)'),
    'include/classes/mimethumb.php'     => array('escapeshellarg($this->strPathFile)', 'escapeshellarg($fileTempo)'),
);
foreach ($arrCibles as $strFichier => $arrAttendus)
{
    $strContenu = file_get_contents(PLOOPI_ROOT.'/'.$strFichier);
    foreach ($arrAttendus as $strAttendu)
        T::chk(basename($strFichier).' : '.$strAttendu, strpos($strContenu, $strAttendu) !== false);
}
T::chk('plus d\'interpolation brute dans passthru', !preg_match('/passthru\(\$cmd = "/', file_get_contents(PLOOPI_ROOT.'/modules/system/op.php')));
$strTemoin = sys_get_temp_dir().'/ploopi_tests_temoin_'.getmypid();
@unlink($strTemoin);
exec('echo '.escapeshellarg('x$(touch '.$strTemoin.').txt'), $arrSortie2);
T::chk('substitution de commande non exécutée',     !file_exists($strTemoin));
T::chk('valeur transmise telle quelle',             implode('', $arrSortie2) === 'x$(touch '.$strTemoin.').txt');
@unlink($strTemoin);

exit(T::bilan());
