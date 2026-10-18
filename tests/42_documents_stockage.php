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
 * Suite 42 — stockage des documents de la GED du socle (ploopi\documents), local ou S3.
 *
 * Les gestes de l'interface, rejoués en HTTP sur la fiche d'un utilisateur : dépôt par
 * le formulaire (envoi multipart), téléchargement, archive zip, remplacement du fichier
 * par un fichier d'une autre extension, renommage avec changement d'extension,
 * suppression. Après chaque geste, le stockage est lu directement : dossier de
 * _PLOOPI_PATHDATA, ou dépôt S3 par le SDK, sans passer par le code de Ploopi.
 *
 * Le stockage est celui de l'instance : S3 si _PLOOPI_S3_ACTIVATED (tests/instance.sh
 * le branche quand PLOOPI_TEST_S3_ENDPOINT est fourni, cf. tests/README.md), local
 * sinon. Le dépôt S3 est créé s'il n'existe pas.
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

define('S42_USER', 2);  // administrateur de l'instance de test, dont la fiche porte une GED

$booS3 = defined('_PLOOPI_S3_ACTIVATED') && _PLOOPI_S3_ACTIVATED;
$objS3 = null;

/**
 * Fichiers ou objets « <id>.* » d'un document, lus directement dans le stockage
 *
 * @param int $intId document
 * @return array noms (« id.extension ») => contenu
 */
function s42_stockage($intId)
{
    global $booS3, $objS3;

    $arrFichiers = array();
    if ($booS3)
    {
        foreach ($objS3->listObjects(array('Bucket' => _PLOOPI_S3_BUCKET, 'Prefix' => "{$intId}."))['Contents'] ?? array() as $arrObjet)
            $arrFichiers[$arrObjet['Key']] = (string) $objS3->getObject(array('Bucket' => _PLOOPI_S3_BUCKET, 'Key' => $arrObjet['Key']))['Body'];
    }
    else
    {
        foreach (glob(_PLOOPI_PATHDATA."/documents/*/{$intId}.*") as $strFichier) $arrFichiers[basename($strFichier)] = file_get_contents($strFichier);
    }
    ksort($arrFichiers);

    return $arrFichiers;
}

/**
 * Page de la fiche de l'utilisateur, avec son navigateur de documents
 *
 * @return string HTML
 */
function s42_fiche()
{
    return H::req('/admin.php?system_level=work&workspaceid=2&wspToolbarItem=tabUsers&op=modify_user&user_id='.S42_USER.'&ploopi_env='.urlencode(H::env()))['body'];
}

/**
 * Requête d'une action du navigateur de documents, retrouvée sur la page par son
 * contenu déchiffré (les URL internes sont chiffrées)
 *
 * @param string $strHtml page
 * @param string $strAction action (openfile, deletefile…)
 * @param string $strContient fragment de la requête déchiffrée (identifiant du document)
 * @return string requête chiffrée (« ploopi_url=… »), '' si absente
 */
function s42_action($strHtml, $strAction, $strContient)
{
    preg_match_all('/<button[^>]*data-ploopi-documents="'.$strAction.'"[^>]*>/', $strHtml, $arrBoutons);
    foreach ($arrBoutons[0] as $strBouton)
    {
        if (!preg_match('/data-query="ploopi_url=([^"]+)"/', $strBouton, $arrQuery)) continue;
        if (strpos(ploopi\cipher::singleton()->decrypt(html_entity_decode($arrQuery[1])), $strContient) !== false) return 'ploopi_url='.html_entity_decode($arrQuery[1]);
    }

    return '';
}

/**
 * Envoie le formulaire d'un fichier (opération documents_openfile), comme le fait
 * js/documents.js
 *
 * @param string $strQuery requête de l'action openfile
 * @param array $arrChamps champs du formulaire, fichiers compris (CURLFile)
 * @return array réponse décodée (champs du document), null si l'envoi échoue
 */
function s42_envoyer($strQuery, $arrChamps)
{
    $strForm = H::req('/admin-light.php?'.$strQuery)['body'];
    if (!preg_match('/<form[^>]*data-ploopi-documents-form[^>]*>/', $strForm, $arrForm) || !preg_match('/action="([^"]+)"/', $arrForm[0], $arrAction)) return null;

    return json_decode(H::req('/'.html_entity_decode($arrAction[1]), $arrChamps)['body'], true);
}

/**
 * Fichier temporaire, au nom choisi (le nom envoyé est celui du document)
 *
 * @param string $strNom nom
 * @param string $strContenu contenu
 * @return CURLFile
 */
function s42_fichier($strNom, $strContenu)
{
    $strChemin = sys_get_temp_dir().'/s42_'.$strNom;
    file_put_contents($strChemin, $strContenu);

    return new CURLFile($strChemin, 'application/octet-stream', $strNom);
}

T::section($booS3 ? 'Stockage S3 ('._PLOOPI_S3_ENDPOINT_URL.', dépôt '._PLOOPI_S3_BUCKET.')' : 'Stockage local (_PLOOPI_PATHDATA)');

if ($booS3)
{
    $objS3 = new Aws\S3\S3Client(array(
        'version' => 'latest',
        'region' => _PLOOPI_S3_REGION,
        'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
        'use_path_style_endpoint' => true,
        'http' => array('verify' => !defined('_PLOOPI_S3_VERIFY_SSL') || _PLOOPI_S3_VERIFY_SSL),
        'credentials' => array('key' => _PLOOPI_S3_AUTH_KEY, 'secret' => _PLOOPI_S3_AUTH_SECRET),
    ));
    try { if (!$objS3->doesBucketExist(_PLOOPI_S3_BUCKET)) $objS3->createBucket(array('Bucket' => _PLOOPI_S3_BUCKET)); }
    catch (Throwable $objErreur) { T::skip('suite entière', 'dépôt S3 injoignable : '.$objErreur->getMessage()); exit(T::bilan('42_documents_stockage')); }
}

H::connexion();
$strFiche = s42_fiche();
$strNouveau = s42_action($strFiche, 'openfile', 'documentsfile_id=&');
T::vrai('témoin : la fiche porte le navigateur de documents', $strNouveau !== '');

$arrDoc = s42_envoyer($strNouveau, array('documentsfile_label' => 'Essai stockage', 'documentsfile_file0' => s42_fichier('essai-stockage.txt', 'premier contenu')));
$intId = empty($arrDoc['id']) ? 0 : (int) $arrDoc['id'];
register_shutdown_function(function() use (&$intId) {
    if (!$intId) return;
    $objDoc = new ploopi\documentsfile();
    if ($objDoc->open($intId)) $objDoc->delete();
});

T::section('Dépôt par le formulaire');
T::vrai('le document est créé', $intId > 0, var_export($arrDoc, true));
if (!$intId) exit(T::bilan('42_documents_stockage'));

$arrLigne = db()->query("SELECT md5id, extension, s3_bucket FROM ploopi_documents_file WHERE id = {$intId}")->fetch_assoc();
T::eq($booS3 ? 'il est rangé dans le dépôt S3' : 'il est rangé localement', $booS3 ? _PLOOPI_S3_BUCKET : '', $arrLigne['s3_bucket']);
T::eq('le stockage porte le fichier, et lui seul', array("{$intId}.txt" => 'premier contenu'), s42_stockage($intId));

T::section('Téléchargement');
$strTelechargement = '/admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id='.$arrLigne['md5id'].'&ploopi_env='.urlencode(H::env());
$arrRep = H::req($strTelechargement);
T::eq('le contenu est servi', 'premier contenu', $arrRep['body']);
T::vrai('en pièce jointe, sous son nom', (bool) preg_match('/content-disposition: attachment; filename="essai-stockage.txt"/i', $arrRep['headers']), $arrRep['headers']);
T::vrai('avec nosniff', stripos($arrRep['headers'], 'x-content-type-options: nosniff') !== false);
T::eq('sans copie temporaire laissée sur le serveur', array(), glob(_PLOOPI_PATHDATA.'/tmp/s3_*') ?: array());

$arrRep = H::req('/admin-light.php?ploopi_op=documents_downloadfile_zip&documentsfile_id='.$arrLigne['md5id'].'&ploopi_env='.urlencode(H::env()));
$strZip = tempnam(sys_get_temp_dir(), 's42');
file_put_contents($strZip, $arrRep['body']);
$objZip = new ZipArchive();
T::vrai('l\'archive zip contient le document', $objZip->open($strZip) === true && $objZip->getFromName('essai-stockage.txt') === 'premier contenu');
@unlink($strZip);

T::section('Remplacement par un fichier d\'une autre extension');
// L'ancien fichier, ou l'ancien objet du dépôt, restait orphelin, même après la suppression
$strModif = s42_action(s42_fiche(), 'openfile', 'documentsfile_id='.$arrLigne['md5id']);
T::vrai('témoin : le document est modifiable depuis la fiche', $strModif !== '');
s42_envoyer($strModif, array('documentsfile_label' => 'Essai stockage', 'documentsfile_file' => s42_fichier('essai-stockage.md', '# second contenu')));
T::eq('le stockage ne porte que le nouveau fichier', array("{$intId}.md" => '# second contenu'), s42_stockage($intId));
T::eq('il est servi', '# second contenu', H::req($strTelechargement)['body']);

T::section('Renommage avec changement d\'extension');
s42_envoyer($strModif, array('documentsfile_label' => 'Essai stockage', 'documentsfile_name' => 'essai-stockage.csv'));
T::eq('le fichier suit son nouveau nom', array("{$intId}.csv" => '# second contenu'), s42_stockage($intId));
T::eq('il est servi', '# second contenu', H::req($strTelechargement)['body']);

T::section('Suppression');
$strSuppr = s42_action(s42_fiche(), 'deletefile', 'documentsfile_id='.$arrLigne['md5id']);
T::vrai('témoin : le document est supprimable depuis la fiche', $strSuppr !== '');
H::req('/admin-light.php?'.$strSuppr);
T::eq('l\'enregistrement est supprimé', 0, (int) db()->query("SELECT COUNT(*) FROM ploopi_documents_file WHERE id = {$intId}")->fetch_row()[0]);
T::eq('le stockage ne porte plus rien', array(), s42_stockage($intId));

exit(T::bilan('42_documents_stockage'));
