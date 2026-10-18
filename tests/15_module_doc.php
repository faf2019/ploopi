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
 * Suite 15 — module doc (GED) : les trois opérations dispensées de jeton anti-CSRF.
 *
 * doc_image_get, doc_file_view et doc_file_download sont déclarées dans
 * install/doc/files/include/publicops.php : elles sont joignables sans jeton, parce
 * qu'une vignette est une balise <img> et qu'un document lié depuis une page publique
 * l'est par une URL réécrite. Leur propre contrôle d'accès est donc la SEULE barrière.
 *
 * La suite déploie le module sur l'instance, y crée des dossiers et des documents, puis
 * interroge ces trois opérations en session administrateur et sans aucune session.
 *
 * @package doc
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

$strModule  = PLOOPI_ROOT.'/modules/doc';
$strSource  = PLOOPI_ROOT.'/install/doc/files';
$booDeploye = false;

/**
 * Retire le module et ses données. Appelé quoi qu'il arrive.
 */
function doc_nettoyer()
{
    global $strModule, $booDeploye;

    // mysqli signale ses erreurs par une exception depuis PHP 8.1 : une table encore
    // absente ne doit pas interrompre le nettoyage.
    $arrRequetes = array(
        "DELETE FROM `ploopi_mod_doc_file`",
        "DELETE FROM `ploopi_mod_doc_folder`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label = 'doc')",
        "DELETE FROM ploopi_module WHERE label = 'doc'",
        "DELETE FROM ploopi_module_type WHERE label = 'doc'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));

    foreach (glob(PLOOPI_ROOT.'/data/doc-*') as $strChemin) exec('rm -rf '.escapeshellarg($strChemin));
}

register_shutdown_function('doc_nettoyer');
doc_nettoyer();

T::section('Déploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg($strSource).' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}
T::vrai('modules/doc déployé depuis install/doc/files', is_dir($strModule.'/include'));

// Schéma du module
$strSql = file_get_contents(PLOOPI_ROOT.'/install/doc/structure.sql');
$arrErreurs = array();
foreach (preg_split('/;\s*\n/', $strSql) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    if (!db()->query($strRequete)) $arrErreurs[] = db()->error;
}
T::vrai('structure.sql exécuté', empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 2)));

// Enregistrement d'une instance du module dans l'espace de l'administrateur (2)
db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('doc', 0, 'GED', '4.10.8', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
             VALUES ('doc', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intModule}, 2, 1, '')");
T::vrai('instance du module enregistrée', $intModule > 0, "id={$intModule}");

/**
 * Crée un dossier de la GED.
 *
 * Le propriétaire compte : « id_user = moi » est l'une des branches de visibilité
 * (cf. class_docapi.php). Un dossier appartenant à l'utilisateur courant lui est
 * visible quel que soit son type ou son espace — ce qui est voulu. Les scénarios
 * d'accès indu se construisent donc avec un AUTRE propriétaire.
 *
 * @param string $strType foldertype : public, shared ou private
 * @param int $intWorkspace espace propriétaire
 * @param int $intModule instance de module
 * @param int $intProprietaire utilisateur propriétaire du dossier
 * @return int identifiant du dossier
 */
function doc_dossier($strType, $intWorkspace, $intModule, $intProprietaire = 2)
{
    $strTs = date('YmdHis');
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_doc_folder (name, description, foldertype, parents, timestp_create, timestp_modify,
                                            published, id_folder, id_user, id_workspace, id_module)
         VALUES ('dossier %s', '', '%s', '0', '%s', '%s', 1, 0, %d, %d, %d)",
        db()->real_escape_string($strType), db()->real_escape_string($strType), $strTs, $strTs,
        (int)$intProprietaire, (int)$intWorkspace, (int)$intModule
    ));

    return db()->insert_id;
}

/**
 * Dépose un document dans un dossier, en base et sur le disque.
 *
 * @param string $strNom nom du fichier
 * @param string $strExtension extension
 * @param string $strContenu contenu
 * @param int $intDossier dossier d'accueil
 * @param int $intWorkspace espace propriétaire
 * @param int $intModule instance de module
 * @return array id et md5id du document
 */
function doc_document($strNom, $strExtension, $strContenu, $intDossier, $intWorkspace, $intModule)
{
    $strTs = date('YmdHis');

    db()->query(sprintf(
        "INSERT INTO ploopi_mod_doc_file (md5id, name, description, extension, size, version, parents,
                                          timestp_create, timestp_modify, id_folder, id_user, id_workspace, id_module)
         VALUES ('', '%s', '', '%s', %d, 1, '0', '%s', '%s', %d, 2, %d, %d)",
        db()->real_escape_string($strNom),
        db()->real_escape_string($strExtension), strlen($strContenu), $strTs, $strTs,
        (int)$intDossier, (int)$intWorkspace, (int)$intModule
    ));

    $intId  = db()->insert_id;
    // empreinte de test : la génération réelle est aléatoire (suite 30)
    $strMd5 = bin2hex(random_bytes(16));
    db()->query("UPDATE ploopi_mod_doc_file SET md5id = '{$strMd5}' WHERE id = {$intId}");

    // Même disposition que docfile::getfilepath() : <pathdata>/doc-<module>/<AAAAMMJJ>/<id>_<version>.<ext>
    $strDossier = PLOOPI_ROOT.'/data/doc-'.(int)$intModule.'/'.substr($strTs, 0, 8);
    if (!is_dir($strDossier)) mkdir($strDossier, 0777, true);
    file_put_contents("{$strDossier}/{$intId}_1.{$strExtension}", $strContenu);

    return array('id' => $intId, 'md5' => $strMd5);
}

// 2 = l'administrateur qui exécute la suite ; 999 = un autre utilisateur.
$intDossierPublic    = doc_dossier('public',  2,  $intModule, 2);
$intDossierPrive     = doc_dossier('private', 2,  $intModule, 999);
$intDossierEtranger  = doc_dossier('public',  99, $intModule, 999);

$arrDocs = array(
    'public'   => doc_document('note-publique.txt', 'txt', 'contenu publie',    $intDossierPublic,   2,  $intModule),
    'prive'    => doc_document('note-privee.txt',   'txt', 'contenu prive',     $intDossierPrive,    2,  $intModule),
    'etranger' => doc_document('note-etrangere.txt','txt', 'contenu etranger',  $intDossierEtranger, 99, $intModule),
    'html'     => doc_document('page.html',         'html','<script>alert(1)</script>', $intDossierPublic, 2, $intModule),
);
T::vrai('jeu de documents créé', sizeof($arrDocs) === 4);

// Session administrateur, module visible, espace courant établi.
//
// Le répartiteur d'opérations de module (include/op.php) boucle sur
// $_SESSION['ploopi']['workspaces'][workspaceid]['modules'] : sans espace courant
// en session, AUCUNE opération de module n'est atteinte et tous les contrôles qui
// suivent passeraient à vide. L'espace est posé par une navigation portant
// ploopi_env, exactement comme le ferait un navigateur avant de charger une
// vignette ou un lien de document.
H::reset();
H::connexion();
H::req('/admin.php?reloadsession=1');
$strEnv = H::env();
H::req('/admin.php?ploopi_env='.urlencode($strEnv));

/**
 * Appelle une opération publique du module doc.
 *
 * @param string $strOp opération
 * @param string $strMd5 identifiant md5 du document
 * @return array réponse
 */
function doc_appel($strOp, $strMd5)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&docfile_md5id={$strMd5}");
}

T::section('doc_file_download — dossier public de l\'espace courant');
/**
 * Contrôle témoin : il établit que l'opération fonctionne SANS jeton. Sans lui, tous
 * les refus mesurés plus bas pourraient venir d'un appel qui n'aboutit jamais.
 */
$arrRep = doc_appel('doc_file_download', $arrDocs['public']['md5']);
T::vrai('le document est servi, sans jeton', strpos($arrRep['body'], 'contenu publie') !== false, "HTTP {$arrRep['code']}, ".strlen($arrRep['body']).' octets');
T::vrai('en pièce jointe', stripos($arrRep['headers'], 'Content-disposition: attachment') !== false);

T::section('doc_file_view / doc_file_download — dossier privé');
foreach (array('doc_file_view', 'doc_file_download') as $strOp)
{
    $arrRep = doc_appel($strOp, $arrDocs['prive']['md5']);
    T::vrai("{$strOp} : document d'un dossier privé refusé", strpos($arrRep['body'], 'contenu prive') === false);
}

T::section('Identifiant inexistant');
foreach (array('doc_file_view', 'doc_file_download', 'doc_image_get') as $strOp)
{
    $arrRep = doc_appel($strOp, str_repeat('0', 32));
    T::vrai("{$strOp} : md5 inconnu, aucun contenu servi", strlen(trim($arrRep['body'])) === 0, strlen($arrRep['body']).' octets');
}

T::section('Rendu d\'un HTML déposé dans un dossier public');
$arrRep = doc_appel('doc_file_view', $arrDocs['html']['md5']);
T::vrai('le fichier est bien servi', strpos($arrRep['body'], '<script>') !== false, '(sinon le contrôle suivant ne prouve rien)');
T::vrai('affichage en ligne refusé pour du HTML', stripos($arrRep['headers'], 'Content-disposition: attachment') !== false);
T::vrai('nosniff posé', stripos($arrRep['headers'], 'X-Content-Type-Options: nosniff') !== false);

T::section('Cloisonnement par espace');
/**
 * block.php et class_docapi.php n'exposent un dossier « public » que dans les espaces
 * visibles par l'utilisateur :
 *     (f.foldertype = 'public' AND f.id_workspace IN (viewworkspaces()))
 *
 * op.php, lui, ne contrôle QUE foldertype. Ce contrôle mesure l'écart : l'administrateur
 * est ici dans l'espace 2 et demande un document d'un dossier public de l'espace 99.
 */
$arrRep = doc_appel('doc_file_download', $arrDocs['etranger']['md5']);
T::vrai(
    'document d\'un dossier public d\'un espace étranger : refusé',
    strpos($arrRep['body'], 'contenu etranger') === false,
    strlen($arrRep['body']).' octets servis'
);

T::section('doc_image_get — vignette');
/**
 * Une vignette révèle le contenu du document : elle est soumise au même contrôle de
 * visibilité que le dossier qui le porte. L'opération n'en appliquait aucun.
 */
$arrRep = doc_appel('doc_image_get', $arrDocs['public']['md5']);
T::vrai('vignette servie pour un document public', strlen($arrRep['body']) > 0, '(contrôle témoin) '.strlen($arrRep['body']).' octets');

$arrRep = doc_appel('doc_image_get', $arrDocs['prive']['md5']);
T::vrai(
    'aucune vignette pour un document d\'un dossier privé d\'autrui',
    strlen($arrRep['body']) === 0,
    strlen($arrRep['body']).' octets servis'
);

$arrRep = doc_appel('doc_image_get', $arrDocs['etranger']['md5']);
T::vrai(
    'aucune vignette pour un document d\'un espace étranger',
    strlen($arrRep['body']) === 0,
    strlen($arrRep['body']).' octets servis'
);

T::section('Envoyer un document par message');
/**
 * Le lien « Envoyer » de la fiche d'un document appelait ploopi_tickets_new(), fonction
 * inexistante (rien ne se passait), et y plaçait le nom du document échappé pour le HTML
 * puis par addslashes() : un nom contenant une apostrophe sortait de la chaîne JavaScript
 * une fois l'attribut décodé par le navigateur.
 */
$strNomPiege = "pj');alert('xss.txt";
$arrPiege = doc_document($strNomPiege, 'txt', 'contenu piege', $intDossierPublic, 2, $intModule);
$arrEnvDoc = explode('/', $strEnv);
$arrEnvDoc[1] = 2;
$arrEnvDoc[2] = $intModule;
$arrEnvDoc[3] = 'public';
$arrRep = H::req('/admin.php?op=doc_fileform&docfile_md5id='.$arrPiege['md5'].'&ploopi_env='.urlencode(implode('/', $arrEnvDoc)));
preg_match('/<a [^>]*onclick="([^"]*)"[^>]*title="Envoyer en Pièce Jointe"/', $arrRep['body'], $arrLien);
$strJs = html_entity_decode($arrLien[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
T::vrai('lien « Envoyer » présent', $strJs !== '', 'lien introuvable');
T::vrai('ouvre la fenêtre de message (ploopi.tickets.create)', strpos($strJs, 'ploopi.tickets.create(') !== false, $strJs);
T::vrai('nom du document passé en chaîne JSON', strpos($strJs, json_encode($strNomPiege)) !== false, $strJs);

T::section('Flux RSS et Atom d\'un dossier public');
/**
 * Le flux incluait une bibliothèque disparue (lib/feedwriter, avec l'ancienne API
 * « new FeedWriter(RSS2) ») et include/start/functions.php : erreur fatale à chaque
 * appel. Il passe par ploopi\feed, et le contenu (déjà en UTF-8) n'est plus converti
 * depuis l'ISO-8859-1, ce qui le doublait.
 */
db()->query("UPDATE ploopi_mod_doc_folder SET allow_feeds = 1, name = 'Publications été', description = 'Notes & comptes rendus' WHERE id = {$intDossierPublic}");
$strXml = '';
foreach (array('rss' => '/rss/channel/item/title', 'atom' => '/a:feed/a:entry/a:title') as $strFormat => $strChemin)
{
    $arrRep = H::req("/backend.php?format={$strFormat}&ploopi_moduleid={$intModule}&id_folder={$intDossierPublic}");
    $objDom = new DOMDocument();
    $booXml = $arrRep['body'] !== '' && @$objDom->loadXML($arrRep['body']);
    T::vrai("{$strFormat} : flux XML bien formé servi", $booXml, $arrRep['code'].' '.substr($arrRep['body'], 0, 300));
    if (!$booXml) continue;
    $objXpath = new DOMXPath($objDom);
    $objXpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');
    $arrTitres = array();
    foreach ($objXpath->query($strChemin) as $objNoeud) $arrTitres[] = $objNoeud->textContent;
    T::vrai("{$strFormat} : le document du dossier y figure", (bool) preg_grep('/^note-publique\.txt/', $arrTitres), implode(' | ', $arrTitres));
    T::eq("{$strFormat} : titre du flux en UTF-8, sans double conversion", 'Publications été', $objXpath->evaluate($strFormat == 'rss' ? 'string(/rss/channel/title)' : 'string(/a:feed/a:title)'));
    T::vrai("{$strFormat} : pas de document d'un autre dossier", !preg_grep('/note-privee|note-etrangere/', $arrTitres), implode(' | ', $arrTitres));
}
db()->query("UPDATE ploopi_mod_doc_folder SET allow_feeds = 0 WHERE id = {$intDossierPublic}");
$arrRep = H::req("/backend.php?format=rss&ploopi_moduleid={$intModule}&id_folder={$intDossierPublic}");
T::vrai('flux refusé pour un dossier qui ne l\'autorise pas', strpos($arrRep['body'], '<rss') === false, $arrRep['code'].' '.substr($arrRep['body'], 0, 200));

T::section('Sans session établie');
/**
 * Le répartiteur d'opérations de module boucle sur les modules de l'espace courant en
 * session. Sans session, aucune opération de module n'est atteinte : les trois
 * opérations « publiques » sont dispensées du JETON, pas de la session.
 */
H::reset();
$arrRep = doc_appel('doc_file_download', $arrDocs['public']['md5']);
T::vrai('aucun document servi sans session', strpos($arrRep['body'], 'contenu publie') === false, strlen($arrRep['body']).' octets');

exit(T::bilan('15_module_doc'));
