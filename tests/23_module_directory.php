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
 * Suite 23 — module directory : qui peut lire et modifier quoi ?
 *
 * L'interface applique trois règles :
 * - une rubrique (et ses contacts partagés) se gère par ses gestionnaires — validateurs
 *   de la rubrique ou, à défaut, d'une rubrique parente — et l'administrateur ;
 * - une rubrique racine ne se crée que par l'administrateur ;
 * - un contact personnel (hors rubrique) et une liste de favoris n'appartiennent qu'à
 *   leur propriétaire.
 *
 * La suite appelle chaque opération directement, avec le jeton de l'utilisateur, et
 * mesure la base. Un utilisateur sans rôle, un gestionnaire d'une seule rubrique,
 * l'administrateur. Les données sont remises en état avant chaque section.
 *
 * @package directory
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/directory';
$booDeploye = false;
$strWebedit = PLOOPI_ROOT.'/modules/webedit';
$booWebeditDeploye = false;

define('DIR_LOGIN', 'dir_sansdroit');
define('DIR_LOGIN_GEST', 'dir_gestionnaire');
define('DIR_MDP', 'D1rectory!Test2026');

// Identifiants fixes des données d'essai
define('DIR_RUB', 9101);          // rubrique gérée par le gestionnaire
define('DIR_RUB_FILLE', 9102);    // sous-rubrique de la précédente
define('DIR_RUB_AUTRE', 9103);    // rubrique sans gestionnaire
define('DIR_CONTACT', 9201);      // contact partagé de DIR_RUB
define('DIR_PERSO', 9202);        // contact personnel de l'administrateur
define('DIR_LISTE', 9301);        // liste de favoris de l'administrateur

/**
 * Retire le module, ses données et les utilisateurs d'essai. Appelé quoi qu'il arrive.
 */
function dir_nettoyer()
{
    global $strModule, $booDeploye, $strWebedit, $booWebeditDeploye;

    $strUsers = "SELECT id FROM ploopi_user WHERE login IN ('".DIR_LOGIN."', '".DIR_LOGIN_GEST."')";
    $arrRequetes = array(
        "DELETE FROM ploopi_validation WHERE id_module IN (SELECT id FROM ploopi_module WHERE label = 'directory')",
        "DELETE FROM ploopi_workspace_user WHERE id_user IN ({$strUsers})",
        "DELETE FROM ploopi_user WHERE login IN ('".DIR_LOGIN."', '".DIR_LOGIN_GEST."')",
        "DROP TABLE IF EXISTS ploopi_mod_directory_contact, ploopi_mod_directory_favorites, ploopi_mod_directory_heading,
                              ploopi_mod_directory_list, ploopi_mod_directory_speeddialing",
        "DELETE FROM `ploopi_mod_webedit_heading`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label IN ('directory', 'webedit'))",
        "DELETE FROM ploopi_module WHERE label IN ('directory', 'webedit')",
        "DELETE FROM ploopi_module_type WHERE label IN ('directory', 'webedit')"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
    if ($booWebeditDeploye && is_dir($strWebedit)) exec('rm -rf '.escapeshellarg($strWebedit));
}

register_shutdown_function('dir_nettoyer');
dir_nettoyer();

T::section('Déploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/directory/files').' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}

foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/directory/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('directory', 0, 'Annuaire', '2.5.4', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
             VALUES ('directory', {$intType}, 2, 1, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intModule}, 2, 1, '')");
T::vrai('directory déployé et instancié', is_dir($strModule.'/include') && $intModule > 0, "instance={$intModule}");

/**
 * Crée un utilisateur sans rôle, rattaché à l'espace 2.
 *
 * @param string $strLogin identifiant
 * @return int identifiant
 */
function dir_utilisateur($strLogin)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Essai', 'Annuaire', 'annuaire@example.test', '%s', 0)",
        $strLogin, db()->real_escape_string(ploopi\user::generate_hash(DIR_MDP, $strLogin)), date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

    return $intUser;
}

$intSansDroit = dir_utilisateur(DIR_LOGIN);
$intGest = dir_utilisateur(DIR_LOGIN_GEST);

// Le gestionnaire est validateur de DIR_RUB, donc de sa sous-rubrique
db()->query("INSERT INTO ploopi_validation (id, id_module, id_record, id_object, type_validation, id_validation, id_module_type)
             VALUES (1, {$intModule}, '".DIR_RUB."', 1, 'user', {$intGest}, {$intType})");

/**
 * Remet les données d'essai dans leur état initial.
 */
function dir_reinitialiser()
{
    db()->query('DELETE FROM ploopi_mod_directory_heading');
    db()->query('DELETE FROM ploopi_mod_directory_contact');
    db()->query('DELETE FROM ploopi_mod_directory_list');

    foreach (array(array(DIR_RUB, 'RUBRIQUEGEREE', 0, 1), array(DIR_RUB_FILLE, 'RUBRIQUEFILLE', DIR_RUB, 1), array(DIR_RUB_AUTRE, 'RUBRIQUEAUTRE', 0, 2)) as $arrRub)
    {
        db()->query(sprintf(
            "INSERT INTO ploopi_mod_directory_heading (id, label, description, position, phone, fax, address, postalcode, city, country, id_heading, id_user, id_workspace)
             VALUES (%d, '%s', '', %d, '', '', '', '', '', '', %d, 2, 2)",
            $arrRub[0], $arrRub[1], $arrRub[3], $arrRub[2]
        ));
    }

    foreach (array(array(DIR_CONTACT, 'CONTACTPARTAGE', DIR_RUB), array(DIR_PERSO, 'CONTACTPERSONNEL', 0)) as $arrContact)
    {
        db()->query(sprintf(
            "INSERT INTO ploopi_mod_directory_contact (id, lastname, firstname, service, function, phone, mobile, fax, email, address, postalcode, city, country,
                                                       comments, id_user, id_workspace, id_module, number, building, floor, office, civility, id_heading, rank, position)
             VALUES (%d, '%s', 'Prenom', '', '', '0102030405', '', '', '', '', '', '', '', '', 2, 2, %d, '', '', '', '', '', %d, '', 1)",
            $arrContact[0], $arrContact[1], $GLOBALS['intModule'], $arrContact[2]
        ));
    }

    db()->query("INSERT INTO ploopi_mod_directory_list (id, label, id_user, id_workspace, id_module) VALUES (".DIR_LISTE.", 'LISTEADMIN', 2, 2, {$GLOBALS['intModule']})");
}

/**
 * @param int $intId rubrique
 * @return array|null ligne de la rubrique
 */
function dir_rubrique($intId)
{
    return db()->query("SELECT * FROM ploopi_mod_directory_heading WHERE id = ".(int)$intId)->fetch_assoc();
}

/**
 * @param int $intId contact
 * @return array|null ligne du contact
 */
function dir_contact($intId)
{
    return db()->query("SELECT * FROM ploopi_mod_directory_contact WHERE id = ".(int)$intId)->fetch_assoc();
}

/**
 * Se connecte et entre dans l'instance du module (cf. suite 18).
 *
 * @param string $strLogin identifiant
 * @param string $strMdp mot de passe
 * @return string environnement
 */
function dir_connecter($strLogin, $strMdp)
{
    global $intModule;

    H::reset();
    H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode($strMdp));

    $arrEnv = explode('/', H::env());
    if (sizeof($arrEnv) !== 5) return '';
    $arrEnv[1] = 2;
    $arrEnv[2] = (int)$intModule;
    $arrEnv[3] = 'public';

    $strEnv = implode('/', $arrEnv);
    H::req('/admin.php?ploopi_env='.urlencode($strEnv));

    return $strEnv;
}

/**
 * Appelle une opération du module avec le jeton de l'utilisateur courant.
 *
 * @param string $strEnv environnement
 * @param string $strOp opération
 * @param string $strQuery paramètres supplémentaires
 * @param mixed $mixPost corps POST
 * @return array réponse
 */
function dir_op($strEnv, $strOp, $strQuery = '', $mixPost = null)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

$strEnvAdmin = dir_connecter(cfg('admin_login'), cfg('admin_password'));
$strEnvGest = dir_connecter(DIR_LOGIN_GEST, DIR_MDP);
$strEnvSans = dir_connecter(DIR_LOGIN, DIR_MDP);
T::vrai('trois sessions ouvertes', $strEnvAdmin !== '' && $strEnvGest !== '' && $strEnvSans !== '');

/**
 * Chaque session vit dans son propre bocal à cookies : on bascule de l'une à l'autre en
 * se reconnectant, ce qui renouvelle aussi le jeton.
 *
 * @param string $strQui admin, gest ou sans
 * @return string environnement
 */
function dir_session($strQui)
{
    switch ($strQui)
    {
        case 'admin': return dir_connecter(cfg('admin_login'), cfg('admin_password'));
        case 'gest': return dir_connecter(DIR_LOGIN_GEST, DIR_MDP);
        default: return dir_connecter(DIR_LOGIN, DIR_MDP);
    }
}

T::section('Suppression d\'une rubrique');
dir_reinitialiser();
dir_op(dir_session('gest'), 'directory_heading_delete', '&directory_heading_id='.DIR_RUB_FILLE);
T::vrai('témoin : le gestionnaire supprime une sous-rubrique de sa rubrique', dir_rubrique(DIR_RUB_FILLE) === null);

dir_reinitialiser();
dir_op(dir_session('sans'), 'directory_heading_delete', '&directory_heading_id='.DIR_RUB);
T::vrai('l\'utilisateur sans droit ne supprime pas une rubrique', dir_rubrique(DIR_RUB) !== null);

dir_op(dir_session('gest'), 'directory_heading_delete', '&directory_heading_id='.DIR_RUB_AUTRE);
T::vrai('le gestionnaire ne supprime pas une rubrique qu\'il ne gère pas', dir_rubrique(DIR_RUB_AUTRE) !== null);

T::section('Modification d\'une rubrique');
dir_reinitialiser();
dir_op(dir_session('gest'), 'directory_heading_save', '&directory_heading_id='.DIR_RUB, 'directory_heading_label=RENOMMEPARGEST');
T::eq('témoin : le gestionnaire renomme sa rubrique', 'RENOMMEPARGEST', dir_rubrique(DIR_RUB)['label'] ?? null);

dir_reinitialiser();
dir_op(dir_session('sans'), 'directory_heading_save', '&directory_heading_id='.DIR_RUB, 'directory_heading_label=RENOMMEPARINTRUS');
T::eq('l\'utilisateur sans droit ne renomme pas une rubrique', 'RUBRIQUEGEREE', dir_rubrique(DIR_RUB)['label'] ?? null);

dir_op(dir_session('gest'), 'directory_heading_save', '&directory_heading_id='.DIR_RUB, 'directory_heading_id_heading='.DIR_RUB_AUTRE);
T::eq('le gestionnaire ne déplace pas sa rubrique sous une rubrique qu\'il ne gère pas', 0, (int)(dir_rubrique(DIR_RUB)['id_heading'] ?? -1));

T::section('Création de rubriques');
/**
 * @return int nombre de rubriques
 */
function dir_nb_rubriques() { return (int)db()->query('SELECT COUNT(*) FROM ploopi_mod_directory_heading')->fetch_row()[0]; }

dir_reinitialiser();
dir_op(dir_session('gest'), 'directory_heading_add', '&directory_heading_id_heading='.DIR_RUB);
T::eq('témoin : le gestionnaire ajoute une sous-rubrique', 4, dir_nb_rubriques());

dir_reinitialiser();
dir_op(dir_session('sans'), 'directory_heading_add', '&directory_heading_id_heading='.DIR_RUB);
T::eq('l\'utilisateur sans droit n\'ajoute pas de sous-rubrique', 3, dir_nb_rubriques());

dir_reinitialiser();
dir_op(dir_session('admin'), 'directory_heading_add', '&directory_heading_id_heading=0');
T::eq('témoin : l\'administrateur crée une racine', 4, dir_nb_rubriques());

dir_reinitialiser();
dir_op(dir_session('gest'), 'directory_heading_add', '&directory_heading_id_heading=0');
T::eq('le gestionnaire ne crée pas de racine', 3, dir_nb_rubriques());

T::section('Contacts partagés');
dir_reinitialiser();
dir_op(dir_session('gest'), 'directory_contact_save', '&directory_contact_id='.DIR_CONTACT, 'directory_contact_lastname=MODIFIEPARGEST');
T::eq('témoin : le gestionnaire modifie un contact de sa rubrique', 'MODIFIEPARGEST', dir_contact(DIR_CONTACT)['lastname'] ?? null);

dir_reinitialiser();
$strEnv = dir_session('sans');
dir_op($strEnv, 'directory_contact_save', '&directory_contact_id='.DIR_CONTACT, 'directory_contact_lastname=MODIFIEPARINTRUS');
T::eq('l\'utilisateur sans droit ne modifie pas un contact partagé', 'CONTACTPARTAGE', dir_contact(DIR_CONTACT)['lastname'] ?? null);

dir_op($strEnv, 'directory_contact_delete', '&directory_contact_id='.DIR_CONTACT);
T::vrai('ni ne le supprime', dir_contact(DIR_CONTACT) !== null);

dir_op($strEnv, 'directory_contact_save', '', 'directory_contact_lastname=INTRUSINJECTE&directory_contact_id_heading='.DIR_RUB);
$intInjecte = (int)db()->query("SELECT COUNT(*) FROM ploopi_mod_directory_contact WHERE lastname = 'INTRUSINJECTE' AND id_heading = ".DIR_RUB)->fetch_row()[0];
T::eq('ni n\'en crée un dans la rubrique en forçant id_heading', 0, $intInjecte);

$strEnv = dir_session('gest');
dir_op($strEnv, 'directory_contact_save', '&directory_contact_id='.DIR_CONTACT, 'directory_contact_id_heading='.DIR_RUB_AUTRE);
T::eq('le gestionnaire ne déplace pas un contact vers une rubrique qu\'il ne gère pas', DIR_RUB, (int)(dir_contact(DIR_CONTACT)['id_heading'] ?? 0));

dir_op($strEnv, 'directory_contact_delete', '&directory_contact_id='.DIR_CONTACT);
T::vrai('témoin : le gestionnaire supprime un contact de sa rubrique', dir_contact(DIR_CONTACT) === null);

T::section('Contacts personnels');
dir_reinitialiser();
$strEnv = dir_session('admin');
$arrRep = dir_op($strEnv, 'directory_view', '&directory_id_contact='.DIR_PERSO);
T::contient('témoin : le propriétaire consulte son contact personnel', 'CONTACTPERSONNEL', $arrRep['body']);

$strEnv = dir_session('sans');
$arrRep = dir_op($strEnv, 'directory_view', '&directory_id_contact='.DIR_PERSO);
T::contientpas('un autre utilisateur ne le consulte pas', 'CONTACTPERSONNEL', $arrRep['body']);

$arrRep = dir_op($strEnv, 'directory_modify', '&directory_id_contact='.DIR_PERSO);
T::contientpas('ni par le formulaire de modification', 'CONTACTPERSONNEL', $arrRep['body']);

$arrRep = dir_op($strEnv, 'directory_view', '&directory_id_contact='.DIR_CONTACT);
T::contient('témoin : il consulte un contact partagé', 'CONTACTPARTAGE', $arrRep['body']);

dir_op($strEnv, 'directory_contact_save', '&directory_contact_id='.DIR_PERSO, 'directory_contact_lastname=MODIFIEPARINTRUS');
T::eq('il ne modifie pas le contact personnel d\'autrui', 'CONTACTPERSONNEL', dir_contact(DIR_PERSO)['lastname'] ?? null);

dir_op($strEnv, 'directory_contact_delete', '&directory_contact_id='.DIR_PERSO);
T::vrai('ni ne le supprime', dir_contact(DIR_PERSO) !== null);

dir_op($strEnv, 'directory_contact_save', '', 'directory_contact_lastname=PERSODUSANSDROIT');
T::eq('témoin : il crée son propre contact personnel', 1, (int)db()->query("SELECT COUNT(*) FROM ploopi_mod_directory_contact WHERE lastname = 'PERSODUSANSDROIT' AND id_heading = 0 AND id_user = {$intSansDroit}")->fetch_row()[0]);

T::section('Contacts personnels : formulaire de nouveau contact');
/**
 * Le formulaire de nouveau contact (masqué sous « Ajouter un nouveau contact ») ouvrait la
 * GED du contact, qui n'a pas encore d'identifiant : un dossier racine était créé pour
 * l'enregistrement null. En SQL strict (MariaDB de l'image Docker), l'insertion échouait
 * et la requête suivante (« WHERE id_folder = ») était invalide ; sinon, tous les nouveaux
 * contacts partageaient ce dossier. Les documents se joignent une fois le contact créé.
 */
$strEnv = dir_session('admin');
db()->query("DELETE FROM ploopi_documents_folder WHERE id_module = ".(int)$intModule." AND id_record = ''");
$arrRep = H::req('/admin.php?directoryTabItem=tabMycontacts&ploopi_env='.urlencode($strEnv));
T::contient('témoin : formulaire de nouveau contact présent', 'Ajouter un nouveau contact', $arrRep['body']);
T::contientpas('pas de documents pour un contact non enregistré', 'Documents liés au contact', $arrRep['body']);
T::eq('aucun dossier créé pour un contact sans identifiant', 0, (int)db()->query("SELECT COUNT(*) FROM ploopi_documents_folder WHERE id_module = ".(int)$intModule." AND id_record = ''")->fetch_row()[0]);
// Fenêtre de modification, appelée comme par le navigateur (admin-light charge la langue)
$arrRep = H::req('/admin-light.php?ploopi_op=directory_modify&directory_id_contact='.DIR_PERSO.'&ploopi_env='.urlencode($strEnv));
T::contient('témoin : documents d\'un contact existant toujours proposés', 'Documents liés au contact', $arrRep['body']);

T::section('Listes de favoris');
dir_reinitialiser();
dir_op(dir_session('sans'), 'directory_list_delete', '&directory_favorites_id_list='.DIR_LISTE);
T::eq('un utilisateur ne supprime pas la liste d\'autrui', 1, (int)db()->query('SELECT COUNT(*) FROM ploopi_mod_directory_list WHERE id = '.DIR_LISTE)->fetch_row()[0]);

dir_reinitialiser();
dir_op(dir_session('sans'), 'directory_list_save', '', 'directory_favorites_id_list='.DIR_LISTE.'&directory_list_label=LISTEDETOURNEE');
T::eq('ni ne la renomme', 'LISTEADMIN', db()->query('SELECT label FROM ploopi_mod_directory_list WHERE id = '.DIR_LISTE)->fetch_row()[0] ?? null);

dir_reinitialiser();
dir_op(dir_session('admin'), 'directory_list_delete', '&directory_favorites_id_list='.DIR_LISTE);
T::eq('témoin : le propriétaire supprime sa liste', 0, (int)db()->query('SELECT COUNT(*) FROM ploopi_mod_directory_list WHERE id = '.DIR_LISTE)->fetch_row()[0]);

T::section('Frontoffice : fiche et recherche');
/**
 * L'annuaire s'affiche en frontoffice comme contenu d'une page webedit
 * (template_moduleid), avec le gabarit « corporate » : un visiteur anonyme y
 * consulte les contacts partagés, jamais les contacts personnels.
 */
if (!is_dir($strWebedit))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/webedit/files').' '.escapeshellarg($strWebedit), $arrSortie, $intRc);
    $booWebeditDeploye = ($intRc === 0);
}
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/webedit/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}
db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('webedit', 0, 'webedit', '2.8.5', 'Ovensia', '".date('YmdHis')."')");
$intTypeWebedit = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
             VALUES ('webedit', {$intTypeWebedit}, 2, 1, 1, 1)");
$intWebedit = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intWebedit}, 2, 0, '')");
db()->query("INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                                     content_type, visible, private, private_visible, sortmode,
                                                     id_module, id_user, id_workspace)
             VALUES ('Annuaire', '', 'corporate', 0, '', 0, 1, 'articles', 1, 0, 0, 0, {$intWebedit}, 2, 2)");
$intRubWeb = db()->insert_id;

$arrEtatFo = db()->query('SELECT frontoffice, frontoffice_domainlist FROM ploopi_workspace WHERE id = 2')->fetch_assoc();
db()->query("UPDATE ploopi_workspace SET frontoffice = 1, frontoffice_domainlist = '*' WHERE id = 2");
register_shutdown_function(function() use ($arrEtatFo) {
    if (empty($arrEtatFo)) return;
    db()->query(sprintf(
        "UPDATE ploopi_workspace SET frontoffice = %d, frontoffice_domainlist = '%s' WHERE id = 2",
        (int)$arrEtatFo['frontoffice'], db()->real_escape_string($arrEtatFo['frontoffice_domainlist'])
    ));
});

dir_reinitialiser();
$strPage = "/index.php?headingid={$intRubWeb}&template_moduleid={$intModule}";

H::reset();
$arrRep = H::req($strPage.'&op=contact&directory_contact_id='.DIR_CONTACT);
T::contient('témoin : un visiteur consulte la fiche d\'un contact partagé', 'CONTACTPARTAGE', $arrRep['body']);

H::reset();
$arrRep = H::req($strPage.'&op=contact&directory_contact_id='.DIR_PERSO);
T::contientpas('pas celle d\'un contact personnel', 'CONTACTPERSONNEL', $arrRep['body']);

H::reset();
/**
 * Charge sans balise : le filtre d'entrée du socle retire les balises, pas les guillemets
 * qui ferment l'attribut value du champ.
 */
$strCharge = '" onfocus="alert(1)" autofocus="';
$arrRep = H::req($strPage.'&op=search', 'directory_lastname=CONTACT&directory_heading='.urlencode($strCharge).'&directory_comments='.urlencode($strCharge));
T::contient('témoin : le formulaire de recherche est rendu', 'name="directory_heading"', $arrRep['body']);
T::contientpas('les critères de recherche sont échappés', $strCharge, $arrRep['body']);

T::section('Frontoffice : photos des contacts');
/**
 * La fiche d'un contact partagé affiche sa photo par une URL index-light.php sans jeton :
 * l'opération doit être publique, et ne servir que les photos de contacts partagés.
 */
$strDossierPhotos = _PLOOPI_PATHDATA._PLOOPI_SEP.'directory';
if (!is_dir($strDossierPhotos)) mkdir($strDossierPhotos, 0777, true);
$objImage = imagecreatetruecolor(4, 4);
foreach (array(DIR_CONTACT, DIR_PERSO) as $intId) imagepng($objImage, $strDossierPhotos._PLOOPI_SEP.$intId.'.png');
register_shutdown_function(function() use ($strDossierPhotos) {
    foreach (array(DIR_CONTACT, DIR_PERSO) as $intId) @unlink($strDossierPhotos._PLOOPI_SEP.$intId.'.png');
});

H::reset();
$arrRep = H::req($strPage.'&op=contact&directory_contact_id='.DIR_CONTACT);
preg_match('/<img title="Photo de[^"]*" src="([^"]*)"/', $arrRep['body'], $arrMatches);
$strPhoto = isset($arrMatches[1]) ? html_entity_decode($arrMatches[1]) : '';
T::vrai('témoin : la fiche porte l\'URL de la photo', strpos($strPhoto, 'ploopi_url') !== false || strpos($strPhoto, 'directory_contact_getphoto') !== false, 'src='.$strPhoto);
$arrRep = H::req('/'.ltrim(preg_replace('#^\./#', '', $strPhoto), '/'));
T::eq('le visiteur reçoit la photo d\'un contact partagé', "\x89PNG", substr($arrRep['body'], 0, 4));

H::reset();
$arrRep = H::req('/index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id='.DIR_PERSO);
T::neq('pas celle d\'un contact personnel', "\x89PNG", substr($arrRep['body'], 0, 4));

exit(T::bilan('23_module_directory'));
