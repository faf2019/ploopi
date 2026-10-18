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
 * Suite 06 — jeton anti-CSRF sur les opérations des modules.
 *
 * Provisionne un module jetable portant deux opérations, l'une déclarée publique et l'autre
 * non, éprouve la grille, puis retire tout ce qu'elle a créé. Aucun module livré n'est
 * touché : c'est le seul moyen d'éprouver le mécanisme sans dépendre des modules installés.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

$strMod = 'verifcsrf';
$strDir = PLOOPI_ROOT.'/modules/'.$strMod;
$strTemoin = sys_get_temp_dir().'/verifcsrf_';

// ── Module jetable : deux opérations, une déclarée publique, l'autre non
@mkdir($strDir.'/include', 0777, true);
file_put_contents($strDir.'/op.php', '<?php
switch ($ploopi_op) {
    case "verifcsrf_public": file_put_contents("'.$strTemoin.'public", "1"); break;
    case "verifcsrf_prive":  file_put_contents("'.$strTemoin.'prive",  "1"); break;
}
');
file_put_contents($strDir.'/include/publicops.php', '<?php return array("verifcsrf_public");');

// ── Enregistrement en base
db()->query("DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label='verifcsrf')");
db()->query("DELETE FROM ploopi_module WHERE label='verifcsrf'");
db()->query("DELETE FROM ploopi_module_type WHERE label='verifcsrf'");
db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('verifcsrf', 0, 'module de verification', '1.0', 'tests', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
             VALUES ('verifcsrf', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule}, 2, 1, '')");

// ── Session rechargée pour que le module apparaisse
H::reset();
H::connexion();
H::req('/admin.php?reloadsession=1');
$strEnv = H::env();

$funcAppel = function($strOp, $booJeton) use ($strEnv) {
    @unlink(sys_get_temp_dir().'/verifcsrf_public'); @unlink(sys_get_temp_dir().'/verifcsrf_prive');
    H::req("/index-quick.php?ploopi_op={$strOp}".($booJeton ? '&ploopi_env='.urlencode($strEnv) : ''));
    return file_exists(sys_get_temp_dir().'/verifcsrf_'.($strOp === 'verifcsrf_public' ? 'public' : 'prive'));
};

T::section('Jeton anti-CSRF sur les opérations de module');
T::chk('opération non déclarée, avec jeton : exécutée',   $funcAppel('verifcsrf_prive', true));
T::chk('opération non déclarée, sans jeton : refusée',    !$funcAppel('verifcsrf_prive', false), '(le scénario CSRF)');
T::chk('opération déclarée publique, sans jeton : exécutée', $funcAppel('verifcsrf_public', false), '(vignettes et liens publics préservés)');
T::chk('opération déclarée publique, avec jeton : exécutée', $funcAppel('verifcsrf_public', true));

// ── Nettoyage
db()->query("DELETE FROM ploopi_module_workspace WHERE id_module = {$intModule}");
db()->query("DELETE FROM ploopi_module WHERE id = {$intModule}");
db()->query("DELETE FROM ploopi_module_type WHERE id = {$intType}");
@unlink($strDir.'/include/publicops.php'); @unlink($strDir.'/op.php');
@rmdir($strDir.'/include'); @rmdir($strDir);
@unlink(sys_get_temp_dir().'/verifcsrf_public'); @unlink(sys_get_temp_dir().'/verifcsrf_prive');
T::chk('module jetable retiré', !is_dir($strDir));

/**
 * Le compteur de messages de la barre supérieure se rafraîchit toutes les 30 s par
 * index-quick.php?ploopi_op=tickets_getnum (ploopi.tickets.refresh, js/tickets.js).
 * Depuis que index-quick.php exige un jeton pour les opérations du socle, la requête
 * partait sans ploopi_env : opération ignorée (« Jeton absent » au journal toutes les
 * 30 s), et le résultat aurait de toute façon été écrit dans un élément inexistant
 * (#tpl_ploopi.tickets.create). Le compteur ne bougeait jamais sans recharger la page.
 */
T::section('Compteur de messages rafraîchi (tickets_getnum)');
H::reset();
H::connexion();
$strEnv = H::env();
$arrSans = H::req('/index-quick.php?ploopi_op=tickets_getnum');
$arrAvec = H::req('/index-quick.php?ploopi_op=tickets_getnum&ploopi_env='.urlencode($strEnv));
T::chk('session connectée signalée (Ploopi-Connected)', preg_match('/^Ploopi-Connected: 1/mi', $arrAvec['headers']) === 1);
T::chk('témoin : sans jeton, l\'opération est ignorée', !preg_match('/^\d+,\d+$/', trim($arrSans['body'])), '(la requête de l\'ancien script)');
T::chk('avec le jeton de la page : « non lus,dernier »', preg_match('/^\d+,\d+$/', trim($arrAvec['body'])) === 1, trim(substr($arrAvec['body'], 0, 60)));

// Le script servi aux navigateurs, et non ses sources
$strPack = (string)file_get_contents(PLOOPI_ROOT.'/js/functions.pack.js');
preg_match('/ploopi\.tickets\.refresh = function.*?\n\};/s', $strPack, $arrMatches);
$strRefresh = $arrMatches[0] ?? '';
T::chk('ploopi.tickets.refresh trouvé dans functions.pack.js', $strRefresh !== '');
T::chk('la requête transporte le jeton de la page (_PLOOPI_ENV)', strpos($strRefresh, '_PLOOPI_ENV') !== false);
preg_match_all("/jQuery\('#([A-Za-z0-9_-]+)'\)\.html\(/", $strRefresh, $arrCibles);
T::chk('le compteur est écrit dans un élément désigné', !empty($arrCibles[1]));
foreach(suivis(glob(PLOOPI_ROOT.'/templates/backoffice/*/index.tpl')) as $strGabarit)
{
    $strTpl = file_get_contents($strGabarit);
    foreach($arrCibles[1] as $strId)
        T::chk('élément #'.$strId.' présent dans '.basename(dirname($strGabarit)).'/index.tpl', strpos($strTpl, 'id="'.$strId.'"') !== false);
}

exit(T::bilan());
