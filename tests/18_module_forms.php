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
 * Suite 18 — module forms : les droits déclarés sont-ils appliqués ?
 *
 * description.xml déclare saisir (2), exporter (3), supprimer (5), archiver (6) et
 * administrer (99). public_forms_viewreplies.php s'en sert pour afficher ou non les
 * boutons ; il faut vérifier que les traitements, appelables directement en HTTP, en
 * font autant.
 *
 * Deux angles :
 *
 * - un utilisateur SANS AUCUN RÔLE, rattaché à l'espace : un administrateur système
 *   passe tous les contrôles d'ACL et ne prouverait rien ;
 * - un GESTIONNAIRE de l'instance A, qui détient tous les droits sur A mais aucun sur
 *   l'instance B du même espace : ses droits ne doivent pas s'étendre aux formulaires
 *   de B, désignés par leur seul identifiant.
 *
 * @package forms
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/forms';
$strSource = PLOOPI_ROOT.'/install/forms/files';
$booDeploye = false;

define('FORMS_LOGIN', 'forms_sansdroit');
define('FORMS_LOGIN_GEST', 'forms_gestionnaire');
define('FORMS_MDP', 'F0rms!Test2026');
define('FORMS_VALEUR', 'VALEUR ORIGINALE');

/**
 * Retire le module, ses données, les rôles et les utilisateurs d'essai. Appelé quoi
 * qu'il arrive.
 */
function forms_nettoyer()
{
    global $strModule, $booDeploye;

    $objRs = db()->query("SHOW TABLES LIKE 'form\\_%'");
    while ($objRs && $arrLigne = $objRs->fetch_row()) db()->query("DROP TABLE IF EXISTS `{$arrLigne[0]}`");

    $strUsers = "SELECT id FROM ploopi_user WHERE login IN ('".FORMS_LOGIN."', '".FORMS_LOGIN_GEST."')";
    $strRoles = "SELECT id FROM ploopi_role WHERE label = 'forms_tests'";

    $arrRequetes = array(
        "DELETE FROM ploopi_role_action WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_workspace_user_role WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_role WHERE label = 'forms_tests'",
        "DELETE FROM ploopi_workspace_user WHERE id_user IN ({$strUsers})",
        "DELETE FROM ploopi_user WHERE login IN ('".FORMS_LOGIN."', '".FORMS_LOGIN_GEST."')",
        "DELETE FROM `ploopi_mod_forms_form`",
        "DELETE FROM `ploopi_mod_forms_field`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'forms%')",
        "DELETE FROM ploopi_module WHERE label LIKE 'forms%'",
        "DELETE FROM ploopi_module_type WHERE label = 'forms'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('forms_nettoyer');
forms_nettoyer();

T::section('Déploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg($strSource).' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}
T::vrai('modules/forms déployé depuis install/forms/files', is_dir($strModule.'/classes'));

$arrErreurs = array();
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/forms/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { if (!db()->query($strRequete)) $arrErreurs[] = db()->error; } catch (Throwable $objErreur) { $arrErreurs[] = $objErreur->getMessage(); }
}
T::vrai('structure.sql exécuté', empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 2)));

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('forms', 0, 'Formulaires', '3.8.1', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;

/**
 * Crée une instance du module dans l'espace 2.
 *
 * @param int $intType type de module
 * @param string $strLabel libellé
 * @return int identifiant de l'instance
 */
function forms_instance($intType, $strLabel)
{
    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, viewmode)
                 VALUES ('".db()->real_escape_string($strLabel)."', {$intType}, 2, 1, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
                 VALUES ({$intModule}, 2, 1, '')");

    return $intModule;
}

$intModA = forms_instance($intType, 'forms');
$intModB = forms_instance($intType, 'forms_autre');
T::vrai('deux instances du module créées', $intModA > 0 && $intModB > 0, "A={$intModA} B={$intModB}");

/**
 * Crée un formulaire, son unique champ texte et sa table de données, sans passer par
 * les classes du module : la suite mesure les traitements, pas leur préparation.
 *
 * @param string $strLabel libellé
 * @param int $intModule instance
 * @param string $strModify option_modify (nobody, user, group, all)
 * @return array [identifiant du formulaire, identifiant du champ]
 */
function forms_formulaire($strLabel, $intModule, $strModify = 'nobody')
{
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_forms_form (label, description, email_from, email, typeform, option_modify, option_view,
                                            nbline, autobackup, autobackup_date, id_user, id_workspace, id_module)
         VALUES ('%s', '', '', '', 'app', '%s', 'global', 25, 0, 0, 2, 2, %d)",
        db()->real_escape_string($strLabel), db()->real_escape_string($strModify), (int)$intModule
    ));
    $intForm = db()->insert_id;

    db()->query("INSERT INTO ploopi_mod_forms_field (id_form, name, fieldname, type, format, `values`, formula, description,
                                                     xhtmlcontent, xhtmlcontent_cleaned, position, style_field, style_form,
                                                     defaultvalue, option_formview, option_arrayview, option_exportview)
                 VALUES ({$intForm}, 'Champ', 'champ_1', 'text', 'string', '', '', '', '', '', 1, '', '', '', 1, 1, 1)");
    $intField = db()->insert_id;

    db()->query("CREATE TABLE `form_{$intModule}_{$intForm}` (
                    `#id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `date_validation` BIGINT(14) DEFAULT 0, `ip` VARCHAR(16) DEFAULT '',
                    `user_id` INT(10) UNSIGNED DEFAULT 0, `user_login` VARCHAR(255) DEFAULT '',
                    `user_firstname` VARCHAR(255) DEFAULT '', `user_lastname` VARCHAR(255) DEFAULT '',
                    `workspace_id` INT(10) UNSIGNED DEFAULT 0, `workspace_label` VARCHAR(255) DEFAULT '',
                    `workspace_code` VARCHAR(255) DEFAULT '', `champ_1` VARCHAR(255) DEFAULT NULL
                 ) ENGINE=MyISAM");

    return array($intForm, $intField);
}

/**
 * Remet un formulaire dans son état initial : une seule réponse, saisie par
 * l'administrateur, et l'option de modification demandée.
 *
 * @param int $intForm formulaire
 * @param int $intModule instance
 * @param string $strModify option_modify
 */
function forms_reinitialiser($intForm, $intModule, $strModify = 'nobody')
{
    db()->query("TRUNCATE TABLE `form_{$intModule}_{$intForm}`");
    db()->query("INSERT INTO `form_{$intModule}_{$intForm}` (`#id`, date_validation, user_id, user_login, workspace_id, champ_1)
                 VALUES (1, ".date('YmdHis').", 2, 'admin', 2, '".FORMS_VALEUR."')");
    db()->query("UPDATE ploopi_mod_forms_form SET option_modify = '{$strModify}', autobackup = 0 WHERE id = {$intForm}");
}

/**
 * @param int $intForm formulaire
 * @param int $intModule instance
 * @return array réponses du formulaire, indexées par #id
 */
function forms_reponses($intForm, $intModule)
{
    $arrReponses = array();
    $objRs = db()->query("SELECT * FROM `form_{$intModule}_{$intForm}`");
    while ($objRs && $arrLigne = $objRs->fetch_assoc()) $arrReponses[$arrLigne['#id']] = $arrLigne;

    return $arrReponses;
}

/**
 * @param int $intForm formulaire
 * @return array|null ligne du formulaire, null s'il n'existe plus
 */
function forms_lire($intForm)
{
    return db()->query("SELECT * FROM ploopi_mod_forms_form WHERE id = ".(int)$intForm)->fetch_assoc();
}

list($intFormA, $intFieldA) = forms_formulaire('Formulaire A', $intModA);
list($intFormB, $intFieldB) = forms_formulaire('Formulaire B', $intModB);
forms_reinitialiser($intFormA, $intModA);
forms_reinitialiser($intFormB, $intModB);
T::vrai('formulaires d\'essai créés, une réponse chacun',
    sizeof(forms_reponses($intFormA, $intModA)) === 1 && sizeof(forms_reponses($intFormB, $intModB)) === 1);

/**
 * Crée un utilisateur simple, rattaché à l'espace 2.
 *
 * @param string $strLogin identifiant
 * @return int identifiant de l'utilisateur
 */
function forms_utilisateur($strLogin)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Essai', 'Forms', 'forms@example.test', '%s', 0)",
        $strLogin, db()->real_escape_string(ploopi\user::generate_hash(FORMS_MDP, $strLogin)), date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel)
                 VALUES ({$intUser}, 2, 1, 10)");

    return $intUser;
}

/**
 * Se connecte au backoffice et entre dans une instance du module. Les opérations du
 * module ne sont atteintes que si le module COURANT de la session est un forms :
 * l'environnement porte donc l'instance visée.
 *
 * @param string $strLogin identifiant
 * @param int $intModule instance à activer
 * @return string environnement (mainmenu/espace/module/action/jeton)
 */
function forms_connecter($strLogin, $strMdp, $intModule)
{
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
 * Appelle une opération du module (ploopi_op) avec le jeton de l'utilisateur courant.
 *
 * @param string $strOp opération
 * @param string $strQuery paramètres supplémentaires
 * @param mixed $mixPost corps POST éventuel
 * @return array réponse
 */
function forms_op($strOp, $strQuery = '', $mixPost = null)
{
    global $strEnv;

    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

/**
 * Appelle une vue du module (public.php, aiguillée par « op »).
 *
 * @param string $strOp vue
 * @param string $strQuery paramètres supplémentaires
 * @param mixed $mixPost corps POST éventuel
 * @return array réponse
 */
function forms_vue($strOp, $strQuery = '', $mixPost = null)
{
    global $strEnv;

    return H::req("/admin.php?op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

T::section('Utilisateur sans aucun droit');
$intUser = forms_utilisateur(FORMS_LOGIN);
T::vrai('utilisateur créé', $intUser > 0, "id={$intUser}");

$strEnv = forms_connecter(FORMS_LOGIN, FORMS_MDP, $intModA);
T::vrai('environnement obtenu (jeton compris)', substr_count($strEnv, '/') === 4, $strEnv);

/**
 * Contrôle témoin : la consultation des réponses n'exige aucun droit particulier. Si
 * la réponse s'affiche, l'utilisateur atteint bien le module — sans quoi les refus
 * mesurés ensuite ne prouveraient rien.
 */
$arrRep = forms_vue('forms_viewreplies', "&forms_id={$intFormA}");
T::contient('témoin : le module répond à cet utilisateur', FORMS_VALEUR, $arrRep['body']);

T::section('Suppression des réponses sans le droit de supprimer');
forms_reinitialiser($intFormA, $intModA);
forms_vue('forms_deletedata', "&forms_id={$intFormA}");
T::eq('les réponses sont toujours là', 1, sizeof(forms_reponses($intFormA, $intModA)));

T::section('Paramétrage de l\'archivage sans le droit d\'archiver');
forms_reinitialiser($intFormA, $intModA);
forms_vue('forms_save', '', "forms_id={$intFormA}&forms_autobackup=7&forms_autobackup_date=");
T::eq('l\'archivage est inchangé', '0', forms_lire($intFormA)['autobackup'] ?? null);

T::section('Modification d\'une réponse sans aucun droit');
forms_reinitialiser($intFormA, $intModA);
forms_op('forms_reply_save', "&forms_id={$intFormA}&forms_record_id=1", "field_{$intFieldA}=".urlencode('MODIFIE SANS DROIT'));
$arrReponses = forms_reponses($intFormA, $intModA);
T::eq('la réponse est inchangée', FORMS_VALEUR, $arrReponses[1]['champ_1'] ?? null);

T::section('Saisie d\'une réponse sans le droit de saisir');
forms_reinitialiser($intFormA, $intModA);
forms_op('forms_reply_save', "&forms_id={$intFormA}", "field_{$intFieldA}=".urlencode('SAISIE SANS DROIT'));
T::eq('aucune réponse ajoutée', 1, sizeof(forms_reponses($intFormA, $intModA)));

T::section('Impression des données sans le droit d\'exporter');
forms_reinitialiser($intFormA, $intModA);
/**
 * forms_print_array produit le même document que forms_export, qui exige le droit
 * d'exporter : seul le second paramètre de formsForm::export() les distingue.
 */
$arrRep = forms_op('forms_print_array', "&forms_id={$intFormA}&forms_export_format=CSV");
T::contientpas('les données ne sont pas exportées', FORMS_VALEUR, $arrRep['body']);

T::section('Gestionnaire de l\'instance A face à l\'instance B');
$intGest = forms_utilisateur(FORMS_LOGIN_GEST);
db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description) VALUES ({$intModA}, 2, 'forms_tests', '')");
$intRole = db()->insert_id;
foreach (array(2, 3, 4, 5, 6, 7, 8, 99) as $intAction)
{
    db()->query("INSERT INTO ploopi_role_action (id_role, id_action, id_module_type) VALUES ({$intRole}, {$intAction}, {$intType})");
}
db()->query("INSERT INTO ploopi_workspace_user_role (id_user, id_workspace, id_role) VALUES ({$intGest}, 2, {$intRole})");
T::vrai('gestionnaire créé, tous les droits sur A seulement', $intGest > 0 && $intRole > 0);

$strEnv = forms_connecter(FORMS_LOGIN_GEST, FORMS_MDP, $intModA);
T::vrai('environnement obtenu, instance A courante', substr_count($strEnv, '/') === 4, $strEnv);

/**
 * Chaque refus ci-dessous a son témoin sur l'instance A : la même opération, avec le
 * même utilisateur, aboutit sur un formulaire de sa propre instance.
 */
forms_reinitialiser($intFormA, $intModA, 'all');
forms_reinitialiser($intFormB, $intModB, 'all');
forms_op('forms_reply_save', "&forms_id={$intFormA}&forms_record_id=1", "field_{$intFieldA}=".urlencode('MODIFIE PAR GESTIONNAIRE'));
$arrReponses = forms_reponses($intFormA, $intModA);
T::eq('témoin : il modifie une réponse de A', 'MODIFIE PAR GESTIONNAIRE', $arrReponses[1]['champ_1'] ?? null);

forms_op('forms_reply_save', "&forms_id={$intFormB}&forms_record_id=1", "field_{$intFieldB}=".urlencode('MODIFIE DEPUIS A'));
$arrReponses = forms_reponses($intFormB, $intModB);
T::eq('une réponse de B est inchangée', FORMS_VALEUR, $arrReponses[1]['champ_1'] ?? null);

forms_reinitialiser($intFormA, $intModA, 'all');
forms_reinitialiser($intFormB, $intModB, 'all');
forms_vue('forms_deletedata', "&forms_id={$intFormA}");
T::eq('témoin : il supprime les réponses de A', 0, sizeof(forms_reponses($intFormA, $intModA)));

forms_vue('forms_deletedata', "&forms_id={$intFormB}");
T::eq('les réponses de B sont toujours là', 1, sizeof(forms_reponses($intFormB, $intModB)));

forms_reinitialiser($intFormA, $intModA, 'all');
forms_reinitialiser($intFormB, $intModB, 'all');
forms_op('forms_reply_delete', "&forms_id={$intFormA}&record_id=1");
T::eq('témoin : il supprime une réponse de A', 0, sizeof(forms_reponses($intFormA, $intModA)));

forms_op('forms_reply_delete', "&forms_id={$intFormB}&record_id=1");
T::eq('une réponse de B n\'est pas supprimée une à une', 1, sizeof(forms_reponses($intFormB, $intModB)));

db()->query("INSERT INTO ploopi_mod_forms_field (id_form, name, fieldname, type, format, `values`, formula, description,
                                                 xhtmlcontent, xhtmlcontent_cleaned, position, style_field, style_form, defaultvalue)
             VALUES ({$intFormA}, 'Jetable', 'jetable', 'text', 'string', '', '', '', '', '', 2, '', '', '')");
$intFieldJetable = db()->insert_id;
// formsField::delete() retire aussi la colonne de la table de données
db()->query("ALTER TABLE `form_{$intModA}_{$intFormA}` ADD `jetable` VARCHAR(255) DEFAULT NULL");
forms_op('forms_field_delete', "&forms_id={$intFormA}&field_id={$intFieldJetable}");
T::vrai('témoin : il supprime un champ de A',
    db()->query("SELECT id FROM ploopi_mod_forms_field WHERE id = {$intFieldJetable}")->num_rows === 0);

forms_op('forms_field_delete', "&forms_id={$intFormB}&field_id={$intFieldB}");
T::vrai('un champ de B n\'est pas supprimé',
    db()->query("SELECT id FROM ploopi_mod_forms_field WHERE id = {$intFieldB}")->num_rows === 1);

list($intFormJetable) = forms_formulaire('Formulaire jetable', $intModA);
forms_op('forms_delete', "&forms_id={$intFormJetable}");
T::vrai('témoin : il supprime un formulaire de A', forms_lire($intFormJetable) === null);

forms_op('forms_delete', "&forms_id={$intFormB}");
T::vrai('le formulaire de B n\'est pas supprimé', forms_lire($intFormB) !== null);

/**
 * forms_tablelink_values lit les valeurs d'une liste liée : toute session connectée
 * lisait ainsi les réponses d'un champ de n'importe quel formulaire de la plateforme.
 */
forms_reinitialiser($intFormA, $intModA);
forms_reinitialiser($intFormB, $intModB);
$lier = function ($intForm, $intChampSource) {
    db()->query("INSERT INTO ploopi_mod_forms_field (id_form, name, fieldname, type, format, `values`, formula, description,
                                                     xhtmlcontent, xhtmlcontent_cleaned, position, style_field, style_form,
                                                     defaultvalue, option_formview, option_arrayview, option_exportview)
                 VALUES ({$intForm}, 'Liée', 'liee', 'tablelink', 'string', '{$intChampSource}', '', '', '', '', 9, '', '', '', 1, 0, 0)");
    return db()->insert_id;
};
$intLieA = $lier($intFormA, $intFieldA);
$intLieB = $lier($intFormB, $intFieldB);
$arrRep = forms_op('forms_tablelink_values', "&forms_fields=1&forms_params[0]=x&forms_requested={$intLieA}");
T::contient('témoin : il lit la liste liée d\'un formulaire de A', FORMS_VALEUR, $arrRep['body']);
$arrRep = forms_op('forms_tablelink_values', "&forms_fields=1&forms_params[0]=x&forms_requested={$intLieB}");
T::contientpas('la liste liée d\'un formulaire de B reste illisible', FORMS_VALEUR, $arrRep['body']);
db()->query("DELETE FROM ploopi_mod_forms_field WHERE id IN ({$intLieA}, {$intLieB})");

T::section('Contrôle témoin : l\'administrateur, lui, peut agir');
/**
 * Sans cette section, les refus de l'utilisateur sans droit pourraient venir
 * d'opérations inopérantes plutôt que d'un contrôle de droits.
 */
$strEnv = forms_connecter(cfg('admin_login'), cfg('admin_password'), $intModA);

forms_reinitialiser($intFormA, $intModA);
forms_op('forms_reply_save', "&forms_id={$intFormA}&forms_record_id=1", "field_{$intFieldA}=".urlencode('MODIFIE PAR ADMIN'));
$arrReponses = forms_reponses($intFormA, $intModA);
T::eq('l\'administrateur modifie une réponse', 'MODIFIE PAR ADMIN', $arrReponses[1]['champ_1'] ?? null);

forms_reinitialiser($intFormA, $intModA);
forms_op('forms_reply_save', "&forms_id={$intFormA}", "field_{$intFieldA}=".urlencode('SAISIE PAR ADMIN'));
T::eq('l\'administrateur saisit une réponse', 2, sizeof(forms_reponses($intFormA, $intModA)));

forms_reinitialiser($intFormA, $intModA);
$arrRep = forms_op('forms_print_array', "&forms_id={$intFormA}&forms_export_format=CSV");
T::contient('l\'administrateur imprime les données', FORMS_VALEUR, $arrRep['body']);


forms_reinitialiser($intFormA, $intModA);
forms_vue('forms_save', '', "forms_id={$intFormA}&forms_autobackup=7&forms_autobackup_date=");
T::eq('l\'administrateur paramètre l\'archivage', '7', forms_lire($intFormA)['autobackup'] ?? null);

forms_reinitialiser($intFormA, $intModA);
forms_vue('forms_deletedata', "&forms_id={$intFormA}");
T::eq('l\'administrateur supprime les réponses', 0, sizeof(forms_reponses($intFormA, $intModA)));

exit(T::bilan('18_module_forms'));
