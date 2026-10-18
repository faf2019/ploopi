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
 * Suite 31 — _PLOOPI_PATHDATA hors de la racine web (lot 10, décision D5).
 *
 * Un document déposé dans la GED vit sous _PLOOPI_PATHDATA. Lorsque ce dossier
 * se trouve sous la racine web (valeur historique ./data), la seule barrière
 * entre le fichier et un visiteur est la règle .htaccess : si elle saute —
 * migration, réécriture, serveur autre qu'Apache — le fichier est servi.
 *
 * L'assistant propose désormais un dossier hors racine par défaut. Cette suite
 * mesure le comportement avec l'instance de test, dont PATHDATA est encore sous
 * la racine (le cas à risque) :
 * - un fichier déposé n'est pas servi par son chemin direct : le serveur doit
 *   refuser, comme le fait la règle .htaccess que le routeur de test reproduit ;
 * - l'assistant propose bien une valeur par défaut hors racine (contrôle du
 *   formulaire d'installation) ;
 * - le dépôt continue de fonctionner (témoin).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();
amorcer_socle();

// Documents rangés dans un dépôt S3 : rien sous PATHDATA (la suite 42 éprouve le stockage)
if (defined('_PLOOPI_S3_ACTIVATED') && _PLOOPI_S3_ACTIVATED) { T::skip('suite entière', 'documents rangés dans le dépôt S3 (suite 42)'); exit(T::bilan()); }

/**
 * Nom de fichier unique pour la suite.
 */
define('LOT10_NOM', 'lot10-direct.bin');

T::section('Dépôt d\'un fichier sous PATHDATA');

$strSource = tempnam(sys_get_temp_dir(), 'lot10');
file_put_contents($strSource, 'contenu lot 10');

db()->query("DELETE FROM ploopi_documents_file WHERE name = '".LOT10_NOM."'");
ploopi\documents::savefile(1, '1', $strSource, LOT10_NOM, 0, 'dépôt lot 10', '', '', 2, 2, 1);
@unlink($strSource);

$arrLigne = db()->query("SELECT id, timestp_create, extension FROM ploopi_documents_file
                          WHERE name = '".LOT10_NOM."' ORDER BY id DESC LIMIT 1")->fetch_assoc();
T::vrai('témoin : le dépôt aboutit', !empty($arrLigne));

if (!$arrLigne)
{
    db()->query("DELETE FROM ploopi_documents_file WHERE name = '".LOT10_NOM."'");
    exit(T::bilan());
}

$strCheminRelatif = '/data/documents/'.substr($arrLigne['timestp_create'], 0, 8).'/'.$arrLigne['id'].'.'.$arrLigne['extension'];
$strCheminPhysique = PLOOPI_ROOT.$strCheminRelatif;
T::vrai('témoin : le fichier est sur disque sous la racine web', file_exists($strCheminPhysique), $strCheminRelatif);

T::section('Aucun fichier déposé n\'est servi directement');

$arrRep = H::req($strCheminRelatif);
T::vrai('le chemin direct du dépôt est refusé', $arrRep['code'] === 403, "HTTP {$arrRep['code']}");
T::vrai('aucun contenu du fichier n\'est renvoyé',
    strpos($arrRep['body'], 'contenu lot 10') === false,
    strlen($arrRep['body']).' octets');

$arrRep = H::req('/data/');
T::vrai('le dossier data lui-même est refusé', $arrRep['code'] === 403, "HTTP {$arrRep['code']}");

T::section('L\'assistant propose un dossier hors racine par défaut');

/**
 * La valeur proposée par le formulaire d'installation est définie dans
 * config/install/install.php (clé DATAPATH du tableau arrVariables). On vérifie
 * qu'elle ne pointe pas à l'intérieur du dossier servi.
 */
$strAssistant = file_get_contents(PLOOPI_ROOT.'/config/install/install.php');
$intOcc = preg_match("/'DATAPATH'\\s*=>\\s*\\[[^]]*'([^']*)'\\s*\\]/s", $strAssistant, $arrM);
T::vrai('valeur par défaut lisible dans l\'assistant', $intOcc === 1);
if ($intOcc === 1)
{
    $strDefaut = trim($arrM[1]);
    T::vrai('la valeur proposée est hors de la racine web', strpos($strDefaut, './') !== 0 && strpos($strDefaut, 'data') === false, $strDefaut);
}

db()->query("DELETE FROM ploopi_documents_file WHERE name = '".LOT10_NOM."'");
@unlink($strCheminPhysique);

exit(T::bilan());
