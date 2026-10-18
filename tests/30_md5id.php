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
 * Suite 30 — identifiants md5id imprévisibles (lot 7, décision D4).
 *
 * md5id valait md5(horodatage_identifiant) : l'identifiant étant séquentiel et
 * l'horodatage à la seconde, l'espace à parcourir pour deviner l'empreinte d'un
 * document est minuscule. Là où le md5id sert d'identifiant d'URL — téléchargement
 * d'un document d'un dossier public, vignette — il ne doit pas être calculable.
 *
 * La suite crée des enregistrements par les classes réelles (GED du socle et
 * module doc déployé) et vérifie que :
 * - l'empreinte en base n'est pas celle que donnerait la formule publique ;
 * - deux enregistrements créés dans la même seconde ne partagent pas leur
 *   empreinte (sans aléa, la seule variation est l'identifiant séquentiel) ;
 * - l'empreinte garde le format attendu (32 caractères hexadécimaux) pour ne
 *   rien casser des URL existantes ;
 * - l'empreinte reste unique en base ;
 * - openmd5() retrouve toujours l'enregistrement par son empreinte.
 *
 * Barrières inchangées : le md5id n'est jamais la seule barrière (contrôle
 * d'accès de documents::isallowed() côté socle, doc_ispublicfolder() côté doc),
 * ce que les suites 01 et 15 mesurent déjà ; aucune migration des empreintes
 * existantes n'est requise, conformément à la décision D4.
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

/**
 * Supprime les enregistrements et fichiers créés par la suite.
 */
function md5id_nettoyer()
{
    // documents de la GED du socle : repérés par leur nom
    $objRs = db()->query("SELECT id, timestp_create, extension FROM ploopi_documents_file WHERE name LIKE 'md5id-test-%'");
    $arrDocs = $objRs->fetch_all(MYSQLI_ASSOC);
    foreach ($arrDocs as $arrDoc)
    {
        $strChemin = PLOOPI_ROOT.'/data/documents/'.substr($arrDoc['timestp_create'], 0, 8)."/{$arrDoc['id']}.{$arrDoc['extension']}";
        @unlink($strChemin);
    }
    db()->query("DELETE FROM ploopi_documents_file WHERE name LIKE 'md5id-test-%'");
    db()->query("DELETE FROM ploopi_documents_folder WHERE name LIKE 'md5id-test-%'");

    md5id_nettoyer_doc();
}

/**
 * Supprime les enregistrements du module doc. Les tables n'existent qu'après le
 * déploiement de la section dédiée : sans elles, n'avoir que le retrait du module.
 */
function md5id_nettoyer_doc()
{
    $objRs = db()->query("SHOW TABLES LIKE 'ploopi_mod_doc_file'");
    if ($objRs->num_rows > 0)
    {
        $objRs = db()->query("SELECT f.id, f.timestp_create, f.extension, f.version, f.id_module FROM ploopi_mod_doc_file f WHERE f.name LIKE 'md5id-test-%'");
        $arrDocs = $objRs->fetch_all(MYSQLI_ASSOC);
        foreach ($arrDocs as $arrDoc)
        {
            $strChemin = PLOOPI_ROOT.'/data/doc-'.(int)$arrDoc['id_module'].'/'.substr($arrDoc['timestp_create'], 0, 8)."/{$arrDoc['id']}_{$arrDoc['version']}.{$arrDoc['extension']}";
            @unlink($strChemin);
        }
        $objRs = db()->query("SELECT d.id, d.timestp_create, d.extension, d.id_module FROM ploopi_mod_doc_file_draft d WHERE d.name LIKE 'md5id-test-%'");
        $arrDrafts = $objRs->fetch_all(MYSQLI_ASSOC);
        foreach ($arrDrafts as $arrDraft)
        {
            $strChemin = PLOOPI_ROOT.'/data/doc-'.(int)$arrDraft['id_module'].'/'.substr($arrDraft['timestp_create'], 0, 8)."/{$arrDraft['id']}.{$arrDraft['extension']}";
            @unlink($strChemin);
        }
        db()->query("DELETE FROM ploopi_mod_doc_file WHERE name LIKE 'md5id-test-%'");
        db()->query("DELETE FROM ploopi_mod_doc_file_draft WHERE name LIKE 'md5id-test-%'");
        db()->query("DELETE FROM ploopi_mod_doc_folder WHERE name LIKE 'md5id-test-%'");
    }
    // Type de module et instance enregistrés par la suite : l'instance doit rester vierge (suite 41)
    global $intMd5idType;
    if (!empty($intMd5idType))
    {
        db()->query("DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE id_module_type = ".(int)$intMd5idType.")");
        db()->query("DELETE FROM ploopi_module WHERE id_module_type = ".(int)$intMd5idType);
        db()->query("DELETE FROM ploopi_module_type WHERE id = ".(int)$intMd5idType);
    }
    if (is_dir(PLOOPI_ROOT.'/modules/doc')) exec('rm -rf '.escapeshellarg(PLOOPI_ROOT.'/modules/doc'));
}

register_shutdown_function('md5id_nettoyer');
md5id_nettoyer();

/**
 * Crée un document de la GED du socle par la classe réelle.
 *
 * @param string $strSuffixe suffixe du nom, pour différencier les essais
 * @return array id, md5id, timestp_create
 */
function md5id_document_socle($strSuffixe)
{
    $strSource = tempnam(sys_get_temp_dir(), 'md5id');
    file_put_contents($strSource, 'contenu md5id '.$strSuffixe);

    $objDoc = new ploopi\documentsfile();
    $objDoc->fields['name'] = "md5id-test-{$strSuffixe}.txt";
    $objDoc->fields['label'] = "md5id test {$strSuffixe}";
    $objDoc->fields['id_user'] = 2;
    $objDoc->fields['id_workspace'] = 2;
    $objDoc->fields['id_module'] = 1;
    $objDoc->fields['id_object'] = 1;
    $objDoc->fields['id_record'] = 1;
    $objDoc->setfile($strSource);
    $intErreur = $objDoc->save();
    @unlink($strSource);

    // relire en base : l'identifiant attribué n'est pas renvoyé par save()
    $objRs = db()->query("SELECT id, md5id, timestp_create FROM ploopi_documents_file WHERE name = '".db()->real_escape_string($objDoc->fields['name'])."' ORDER BY id DESC LIMIT 1");
    $arrLigne = $objRs->fetch_assoc();

    return array('erreur' => (int)$intErreur, 'id' => (int)$arrLigne['id'], 'md5id' => $arrLigne['md5id'], 'ts' => $arrLigne['timestp_create']);
}

/**
 * Crée un dossier de la GED du socle par la classe réelle.
 *
 * @param string $strSuffixe suffixe du libellé
 * @return array id, md5id, timestp_create
 */
function md5id_dossier_socle($strSuffixe)
{
    $objDossier = new ploopi\documentsfolder();
    $objDossier->fields['name'] = "md5id-test-{$strSuffixe}";
    $objDossier->fields['id_user'] = 2;
    $objDossier->fields['id_workspace'] = 2;
    $objDossier->fields['id_module'] = 1;
    $objDossier->fields['id_object'] = 1;
    $objDossier->fields['id_record'] = 1;
    $objDossier->save();

    // relire l'identifiant attribué et l'empreinte en base
    $objRs = db()->query("SELECT id, md5id, timestp_create FROM ploopi_documents_folder WHERE name LIKE 'md5id-test-%' ORDER BY id DESC LIMIT 1");
    $arrLigne = $objRs->fetch_assoc();

    return array('id' => (int)$arrLigne['id'], 'md5id' => $arrLigne['md5id'], 'ts' => $arrLigne['timestp_create']);
}

T::section('GED du socle — documents');

$arrA = md5id_document_socle('a');
$arrB = md5id_document_socle('b');

T::eq('témoin : le premier document est enregistré sans erreur', 0, $arrA['erreur']);
T::eq('témoin : le second document est enregistré sans erreur', 0, $arrB['erreur']);
T::vrai('témoin : les empreintes sont renseignées', $arrA['md5id'] !== '' && $arrB['md5id'] !== '');
T::vrai('format conservé : 32 caractères hexadécimaux',
    preg_match('/^[0-9a-f]{32}$/', $arrA['md5id']) === 1 && preg_match('/^[0-9a-f]{32}$/', $arrB['md5id']) === 1);

T::contientpas('l\'empreinte n\'est pas la formule publique md5(horodatage_identifiant)',
    md5(sprintf("%s_%d", $arrA['ts'], $arrA['id'])), $arrA['md5id']);
T::contientpas('l\'empreinte du second document non plus',
    md5(sprintf("%s_%d", $arrB['ts'], $arrB['id'])), $arrB['md5id']);

T::vrai('deux documents dans la même seconde ne partagent pas leur empreinte',
    $arrA['md5id'] !== $arrB['md5id'],
    'sans aléa, seuls les identifiants séquentiels les distinguent');

// l'empreinte doit rester unique en base
$objRs = db()->query("SELECT COUNT(*) c FROM ploopi_documents_file WHERE md5id = '".db()->real_escape_string($arrA['md5id'])."'");
T::eq('l\'empreinte est unique en base', 1, (int)$objRs->fetch_assoc()['c']);

// openmd5 doit toujours retrouver l'enregistrement
$objDoc = new ploopi\documentsfile();
$objRs = db()->query("SELECT id FROM ploopi_documents_file WHERE md5id = '".db()->real_escape_string($arrA['md5id'])."'");
T::vrai('témoin : openmd5 retrouve le document par son empreinte', $objDoc->openmd5($arrA['md5id']) && (int)$objDoc->fields['id'] === (int)$objRs->fetch_assoc()['id']);

T::section('GED du socle — dossiers');

$arrD = md5id_dossier_socle('d');
$arrE = md5id_dossier_socle('e');

T::vrai('deux dossiers créés', $arrD['id'] > 0 && $arrE['id'] > 0);
T::contientpas('l\'empreinte du dossier n\'est pas la formule publique',
    md5(sprintf("%s_%d", $arrD['ts'], $arrD['id'])), $arrD['md5id']);
T::vrai('deux dossiers dans la même seconde ne partagent pas leur empreinte', $arrD['md5id'] !== $arrE['md5id']);

$objDossier = new ploopi\documentsfolder();
T::vrai('témoin : openmd5 retrouve le dossier par son empreinte', $objDossier->openmd5($arrD['md5id']) && (int)$objDossier->fields['id'] === $arrD['id']);

T::section('Module doc — fichiers et brouillons');

// déployer le module doc, comme la suite 15
$strModule = PLOOPI_ROOT.'/modules/doc';
$strSource = PLOOPI_ROOT.'/install/doc/files';
if (!is_dir($strModule)) exec('cp -r '.escapeshellarg($strSource).' '.escapeshellarg($strModule));
T::vrai('témoin : modules/doc déployé', is_dir($strModule.'/include'));

// structure et instance, comme la suite 15
$arrErreurs = array();
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/doc/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { $arrErreurs[] = $objErreur->getMessage(); }
}
T::vrai('témoin : structure.sql exécuté', empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 2)));

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date) VALUES ('doc', 0, 'GED', '4.10.8', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
$intMd5idType = $intType;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible) VALUES ('doc', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule}, 2, 1, '')");
T::vrai('témoin : instance du module enregistrée', $intModule > 0, "id={$intModule}");

/**
 * Crée un fichier du module doc par la classe réelle.
 *
 * @param string $strSuffixe suffixe du nom
 * @return array id, md5id, timestp_create, version
 */
function md5id_fichier_doc($strSuffixe)
{
    global $intModule;

    $strSource = tempnam(sys_get_temp_dir(), 'md5id');
    file_put_contents($strSource, 'contenu doc '.$strSuffixe);

    $objDoc = new docfile();
    $objDoc->fields['name'] = "md5id-test-{$strSuffixe}.txt";
    $objDoc->fields['description'] = '';
    $objDoc->fields['extension'] = 'txt';
    $objDoc->fields['size'] = 14;
    $objDoc->fields['version'] = 1;
    $objDoc->fields['parents'] = '0';
    $objDoc->fields['timestp_create'] = ploopi\date::createtimestamp();
    $objDoc->fields['timestp_modify'] = $objDoc->fields['timestp_create'];
    $objDoc->fields['id_folder'] = 0;
    $objDoc->fields['id_user'] = 2;
    $objDoc->fields['id_workspace'] = 2;
    $objDoc->fields['id_module'] = $intModule;
    $objDoc->sharedfile = $strSource;
    $objDoc->save();
    @unlink($strSource);

    $objRs = db()->query("SELECT id, md5id, timestp_create, version FROM ploopi_mod_doc_file WHERE name = '".db()->real_escape_string($objDoc->fields['name'])."' ORDER BY id DESC LIMIT 1");
    $arrLigne = $objRs->fetch_assoc();

    return array('id' => (int)$arrLigne['id'], 'md5id' => $arrLigne['md5id'], 'ts' => $arrLigne['timestp_create'], 'version' => (int)$arrLigne['version']);
}

/**
 * Crée un brouillon du module doc par la classe réelle.
 *
 * @param string $strSuffixe suffixe du nom
 * @return array id, md5id, timestp_create
 */
function md5id_brouillon_doc($strSuffixe)
{
    global $intModule;

    $strSource = tempnam(sys_get_temp_dir(), 'md5id');
    file_put_contents($strSource, 'contenu brouillon '.$strSuffixe);

    $objBrouillon = new docfiledraft();
    $objBrouillon->fields['name'] = "md5id-test-{$strSuffixe}.txt";
    $objBrouillon->fields['extension'] = 'txt';
    $objBrouillon->fields['timestp_create'] = ploopi\date::createtimestamp();
    $objBrouillon->fields['id_user'] = 2;
    $objBrouillon->fields['id_workspace'] = 2;
    $objBrouillon->fields['id_module'] = $intModule;
    $objBrouillon->sharedfile = $strSource;
    $objBrouillon->save();
    @unlink($strSource);

    $objRs = db()->query("SELECT id, md5id, timestp_create FROM ploopi_mod_doc_file_draft WHERE name = '".db()->real_escape_string($objBrouillon->fields['name'])."' ORDER BY id DESC LIMIT 1");
    $arrLigne = $objRs->fetch_assoc();

    return array('id' => (int)$arrLigne['id'], 'md5id' => $arrLigne['md5id'], 'ts' => $arrLigne['timestp_create']);
}

require_once $strModule.'/include/global.php';
require_once $strModule.'/class_docfile.php';
require_once $strModule.'/class_docfiledraft.php';

$arrF = md5id_fichier_doc('f');
$arrG = md5id_fichier_doc('g');

T::vrai('deux fichiers créés', $arrF['id'] > 0 && $arrG['id'] > 0);
T::contientpas('l\'empreinte du fichier doc n\'est pas la formule publique',
    md5(sprintf("%s_%d_%d", $arrF['ts'], $arrF['id'], $arrF['version'])), $arrF['md5id']);
T::vrai('deux fichiers dans la même seconde ne partagent pas leur empreinte', $arrF['md5id'] !== $arrG['md5id']);

$objDoc = new docfile();
T::vrai('témoin : openmd5 retrouve le fichier doc par son empreinte', $objDoc->openmd5($arrF['md5id']) && (int)$objDoc->fields['id'] === $arrF['id']);

$arrH = md5id_brouillon_doc('h');
$arrI = md5id_brouillon_doc('i');

T::vrai('deux brouillons créés', $arrH['id'] > 0 && $arrI['id'] > 0);
T::contientpas('l\'empreinte du brouillon n\'est pas la formule publique',
    md5(sprintf("%s_%d_1", $arrH['ts'], $arrH['id'])), $arrH['md5id']);
T::vrai('deux brouillons dans la même seconde ne partagent pas leur empreinte', $arrH['md5id'] !== $arrI['md5id']);

$objBrouillon = new docfiledraft();
T::vrai('témoin : openmd5 retrouve le brouillon par son empreinte', $objBrouillon->openmd5($arrH['md5id']) && (int)$objBrouillon->fields['id'] === $arrH['id']);

md5id_nettoyer();

exit(T::bilan());
