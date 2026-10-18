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
 * Prépare l'instance de test au parcours navigateur (tests/navigateur/parcours.js).
 *
 * Par les procédures du module system, comme le ferait l'administrateur : site de
 * démonstration (webedit et cinq modules avec des données fictives,
 * ploopi\system\demo), puis chaque autre paquet de install/, installé
 * (ploopi\system\moduleinstaller) et instancié dans le même espace.
 *
 * Écrit sur la sortie standard, en JSON, ce dont le parcours a besoin : l'espace et,
 * pour chaque module, son instance.
 *
 *   php tests/navigateur/preparer.php
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

chdir(dirname(__DIR__, 2));
define('_PLOOPI_DIRNAME', getcwd());
include_once './include/classes/loader.php';
ploopi\loader::boot_cli();
ploopi\module::init('system', false, false, false);

$db = ploopi\db::get();
$arrAdmin = $db->fetchrow($db->query('SELECT id FROM ploopi_user ORDER BY id LIMIT 1'));
$_SESSION['ploopi']['userid'] = (int) $arrAdmin['id'];
$_SESSION['ploopi']['workspaceid'] = _PLOOPI_SYSTEMGROUP;
$_SESSION['ploopi']['moduleid'] = _PLOOPI_MODULE_SYSTEM;

$arrErreurs = array();

if (ploopi\system\demo::isavailable())
{
    ploopi\system\demo::generate($arrRapport);
    foreach ($arrRapport as $arrLigne) if (!$arrLigne['res']) $arrErreurs[] = "démonstration : {$arrLigne['operation']} : {$arrLigne['detail']}";
}

$intEspace = ploopi\system\demo::getworkspace();

$arrInstalles = array();
$rs = $db->query('SELECT id, label FROM ploopi_module_type');
while ($row = $db->fetchrow($rs)) $arrInstalles[$row['label']] = (int) $row['id'];

foreach (glob('./install/*/description.xml') as $strDescription)
{
    $strModule = basename(dirname($strDescription));
    if (isset($arrInstalles[$strModule])) continue;

    $intType = ploopi\system\moduleinstaller::install($strModule, $arrRapport);
    if ($intType <= 0)
    {
        foreach ($arrRapport as $arrLigne) if (!$arrLigne['res']) $arrErreurs[] = "{$strModule} : {$arrLigne['operation']} : {$arrLigne['detail']}";
        continue;
    }

    // Comme ploopi\system\demo::instance() : instance active, visible et publique
    $objModuleType = new ploopi\module_type();
    $objModuleType->open($intType);
    $objModule = $objModuleType->createinstance($intEspace);
    $objModule->fields['label'] = ucfirst($strModule);
    $objModule->fields['active'] = 1;
    $objModule->fields['visible'] = 1;
    $objModule->fields['public'] = 1;
    $intModule = $objModule->save();

    $objModuleWorkspace = new ploopi\module_workspace();
    $objModuleWorkspace->fields['id_module'] = $intModule;
    $objModuleWorkspace->fields['id_workspace'] = $intEspace;
    $objModuleWorkspace->save();
}

$arrModules = array();
$rs = $db->query("
    SELECT m.id, t.label FROM ploopi_module m
    INNER JOIN ploopi_module_type t ON t.id = m.id_module_type
    INNER JOIN ploopi_module_workspace mw ON mw.id_module = m.id AND mw.id_workspace = {$intEspace}
    WHERE t.label <> 'system' ORDER BY t.label, m.id");
while ($row = $db->fetchrow($rs)) if (!isset($arrModules[$row['label']])) $arrModules[$row['label']] = (int) $row['id'];

echo json_encode(array('espace' => $intEspace, 'modules' => $arrModules, 'erreurs' => $arrErreurs), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
