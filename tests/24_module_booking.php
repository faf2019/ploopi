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
 * Suite 24 - module booking : droits des operations, et planning public.
 *
 * Actions declarees : gerer les types de ressources (10), gerer les ressources (11),
 * gerer les validations (20), faire une demande (30). L'interface n'affiche chaque
 * fonction qu'a qui detient l'action ; la suite appelle les operations directement, avec
 * le jeton d'un utilisateur sans role, et mesure la base.
 *
 * @package booking
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/booking';
$booDeploye = false;

define('BK_LOGIN', 'booking_sansdroit');
define('BK_MDP', 'B00king!Test2026');
define('BK_TYPE', 7401);
define('BK_RES', 7402);
define('BK_SUBRES', 7403);
define('BK_EVENT', 7404);
define('BK_EVENT_DETAIL', 7405);

/**
 * Retire le module, ses donnees et l'utilisateur d'essai. Appele quoi qu'il arrive.
 */
function bk_nettoyer()
{
    global $strModule, $booDeploye;

    $strUsers = "SELECT id FROM ploopi_user WHERE login = '".BK_LOGIN."'";
    $arrRequetes = array(
        "DELETE FROM ploopi_workspace_user WHERE id_user IN ({$strUsers})",
        "DELETE FROM ploopi_user WHERE login = '".BK_LOGIN."'",
        "DROP TABLE IF EXISTS ploopi_mod_booking_event, ploopi_mod_booking_event_detail, ploopi_mod_booking_resource,
                              ploopi_mod_booking_resource_workspace, ploopi_mod_booking_resourcetype,
                              ploopi_mod_booking_subresource, ploopi_mod_booking_event_subresource",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label = 'booking')",
        "DELETE FROM ploopi_module WHERE label = 'booking'",
        "DELETE FROM ploopi_module_type WHERE label = 'booking'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('bk_nettoyer');
bk_nettoyer();

T::section('Deploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/booking/files').' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}

foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/booking/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('booking', 0, 'Reservation', '1.3.6', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
             VALUES ('booking', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intModule}, 2, 1, '')");
T::vrai('booking deploye et instancie', is_dir($strModule.'/include') && $intModule > 0, "instance={$intModule}");

/**
 * Cree un utilisateur sans role, rattache a l'espace 2.
 *
 * @param string $strLogin identifiant
 * @return int identifiant
 */
function bk_utilisateur($strLogin)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Essai', 'Booking', 'booking@example.test', '%s', 0)",
        $strLogin, db()->real_escape_string(ploopi\user::generate_hash(BK_MDP, $strLogin)), date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

    return $intUser;
}

$intSansDroit = bk_utilisateur(BK_LOGIN);

/**
 * Remet les donnees d'essai dans leur etat initial : un type, une ressource, une
 * sous-ressource, une demande d'un autre utilisateur (l'administrateur) et son detail.
 */
function bk_reinitialiser()
{
    global $intModule;

    db()->query('DELETE FROM ploopi_mod_booking_resourcetype');
    db()->query('DELETE FROM ploopi_mod_booking_resource');
    db()->query('DELETE FROM ploopi_mod_booking_subresource');
    db()->query('DELETE FROM ploopi_mod_booking_event');
    db()->query('DELETE FROM ploopi_mod_booking_event_detail');

    db()->query("INSERT INTO ploopi_mod_booking_resourcetype (id, name, active, id_user, id_workspace, id_module)
                 VALUES (".BK_TYPE.", 'TYPETEMOIN', 1, 2, 2, {$intModule})");
    db()->query("INSERT INTO ploopi_mod_booking_resource (id, name, reference, color, active, id_resourcetype, id_user, id_workspace, id_module)
                 VALUES (".BK_RES.", 'RESSOURCETEMOIN', '', '#ccc', 1, ".BK_TYPE.", 2, 2, {$intModule})");
    db()->query("INSERT INTO ploopi_mod_booking_subresource (id, name, reference, active, id_resource, id_user, id_workspace, id_module)
                 VALUES (".BK_SUBRES.", 'SOUSRESSOURCETEMOIN', '', 1, ".BK_RES.", 2, 2, {$intModule})");
    db()->query("INSERT INTO ploopi_mod_booking_event (id, id_resource, object, periodicity, comment, managed, timestp_request, id_user, id_workspace, id_module)
                 VALUES (".BK_EVENT.", ".BK_RES.", 'DEMANDEADMIN', 'none', '', 0, ".date('YmdHis').", 2, 2, {$intModule})");
    db()->query("INSERT INTO ploopi_mod_booking_event_detail (id, timestp_begin, timestp_end, validated, canceled, cancelreason, message, emails, id_event)
                 VALUES (".BK_EVENT_DETAIL.", ".date('YmdHis').", ".date('YmdHis').", 0, 0, '', '', '', ".BK_EVENT.")");
}

/**
 * @param string $strTable table (sans prefixe)
 * @param int $intId identifiant
 * @return array|null ligne
 */
function bk_ligne($strTable, $intId)
{
    return db()->query("SELECT * FROM ploopi_mod_booking_{$strTable} WHERE id = ".(int)$intId)->fetch_assoc();
}

/**
 * @param string $strTable table (sans prefixe)
 * @return int nombre de lignes
 */
function bk_nb($strTable)
{
    return (int)db()->query("SELECT COUNT(*) FROM ploopi_mod_booking_{$strTable}")->fetch_row()[0];
}

/**
 * Se connecte et entre dans l'instance du module (cf. suite 18).
 *
 * @param string $strLogin identifiant
 * @param string $strMdp mot de passe
 * @return string environnement
 */
function bk_connecter($strLogin, $strMdp)
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
 * Appelle une operation du module avec le jeton de l'utilisateur courant.
 *
 * @param string $strEnv environnement
 * @param string $strOp operation
 * @param string $strQuery parametres supplementaires
 * @param mixed $mixPost corps POST
 * @return array reponse
 */
function bk_op($strEnv, $strOp, $strQuery = '', $mixPost = null)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

/**
 * Bascule de session en se reconnectant (chaque session a son bocal a cookies).
 *
 * @param string $strQui admin ou sans
 * @return string environnement
 */
function bk_session($strQui)
{
    return $strQui === 'admin'
        ? bk_connecter(cfg('admin_login'), cfg('admin_password'))
        : bk_connecter(BK_LOGIN, BK_MDP);
}

$strEnvAdmin = bk_session('admin');
$strEnvSans = bk_session('sans');
T::vrai('deux sessions ouvertes', $strEnvAdmin !== '' && $strEnvSans !== '');

T::section('Gestion des types de ressources');
bk_reinitialiser();
bk_op(bk_session('admin'), 'booking_resourcetype_save', '&booking_resourcetype_id='.BK_TYPE, 'booking_resourcetype_name=TYPEPARADMIN');
T::eq('temoin : l\'administrateur renomme un type', 'TYPEPARADMIN', bk_ligne('resourcetype', BK_TYPE)['name'] ?? null);

bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_resourcetype_save', '&booking_resourcetype_id='.BK_TYPE, 'booking_resourcetype_name=TYPEPARINTRUS');
T::eq('l\'utilisateur sans droit ne renomme pas un type', 'TYPETEMOIN', bk_ligne('resourcetype', BK_TYPE)['name'] ?? null);

bk_op(bk_session('sans'), 'booking_resourcetype_delete', '&booking_element_list='.BK_TYPE);
T::vrai('ni ne le supprime', bk_ligne('resourcetype', BK_TYPE) !== null);

bk_op(bk_session('sans'), 'booking_resourcetype_save', '', 'booking_resourcetype_name=TYPECREEPARINTRUS&booking_resourcetype_active=1');
T::eq('ni n\'en cree', 0, (int)db()->query("SELECT COUNT(*) FROM ploopi_mod_booking_resourcetype WHERE name = 'TYPECREEPARINTRUS'")->fetch_row()[0]);

T::section('Gestion des ressources');
bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_resource_save', '&booking_resource_id='.BK_RES, 'booking_resource_name=RESPARINTRUS');
T::eq('l\'utilisateur sans droit ne renomme pas une ressource', 'RESSOURCETEMOIN', bk_ligne('resource', BK_RES)['name'] ?? null);

bk_op(bk_session('sans'), 'booking_resource_delete', '&booking_element_list='.BK_RES);
T::vrai('ni ne la supprime', bk_ligne('resource', BK_RES) !== null);

bk_reinitialiser();
bk_op(bk_session('admin'), 'booking_resource_save', '&booking_resource_id='.BK_RES, 'booking_resource_name=RESPARADMIN');
T::eq('temoin : l\'administrateur la renomme', 'RESPARADMIN', bk_ligne('resource', BK_RES)['name'] ?? null);

T::section('Gestion des sous-ressources');
bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_subresource_save', '&booking_subresource_id='.BK_SUBRES, 'booking_subresource_name=SRPARINTRUS');
T::eq('l\'utilisateur sans droit ne renomme pas une sous-ressource', 'SOUSRESSOURCETEMOIN', bk_ligne('subresource', BK_SUBRES)['name'] ?? null);

bk_op(bk_session('sans'), 'booking_subresource_delete', '&booking_element_list='.BK_SUBRES);
T::vrai('ni ne la supprime', bk_ligne('subresource', BK_SUBRES) !== null);

bk_reinitialiser();
bk_op(bk_session('admin'), 'booking_subresource_save', '&booking_subresource_id='.BK_SUBRES, 'booking_subresource_name=SRPARADMIN');
T::eq('temoin : l\'administrateur la renomme', 'SRPARADMIN', bk_ligne('subresource', BK_SUBRES)['name'] ?? null);

T::section('Demandes de reservation d\'autrui');
bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_event_delete', '&booking_element_list='.BK_EVENT);
T::vrai('l\'utilisateur sans droit ne supprime pas la demande d\'autrui', bk_ligne('event', BK_EVENT) !== null);

bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_event_planning_delete', '&booking_event_id='.BK_EVENT);
T::vrai('ni par booking_event_planning_delete', bk_ligne('event', BK_EVENT) !== null);

bk_reinitialiser();
bk_op(bk_session('sans'), 'booking_event_unlock', '&booking_element_id='.BK_EVENT.','.BK_EVENT_DETAIL);
T::eq('un utilisateur sans droit ne deverrouille pas la demande d\'autrui', 0, (int)(bk_ligne('event', BK_EVENT)['managed'] ?? -1));
db()->query('UPDATE ploopi_mod_booking_event SET managed = 1 WHERE id = '.BK_EVENT);
bk_op(bk_session('sans'), 'booking_event_unlock', '&booking_element_id='.BK_EVENT.','.BK_EVENT_DETAIL);
T::eq('temoin : la demande verrouillee reste verrouillee pour lui', 1, (int)(bk_ligne('event', BK_EVENT)['managed'] ?? -1));

bk_reinitialiser();
bk_op(bk_session('admin'), 'booking_event_delete', '&booking_element_list='.BK_EVENT);
T::vrai('temoin : l\'administrateur supprime une demande', bk_ligne('event', BK_EVENT) === null);

T::section('Taille du planning des réservations');
/**
 * booking_size() : une des tailles proposées ou « auto » (largeur mesurée par le
 * navigateur, bornée) ; une taille inconnue revient au défaut, « auto » sous un gabarit
 * de génération 4 et la première taille proposée sinon.
 */
$funcVue = function($strEnv, $strQuery = '') {
    $arrRep = H::req('/admin-light.php?ploopi_op=booking_refresh&ploopi_env='.urlencode($strEnv).$strQuery);
    preg_match('/<select[^>]*name="booking_size"[\s\S]*?<\/select>/', $arrRep['body'], $arrSelect);
    preg_match('/<option value="([^"]+)" selected="selected">/', isset($arrSelect[0]) ? $arrSelect[0] : '', $arrSel);
    preg_match('/data-autowidth="(\d+)" data-autowidth-op="booking_refresh"/', $arrRep['body'], $arrAuto);
    return array(isset($arrSel[1]) ? $arrSel[1] : '', isset($arrAuto[1]) ? (int) $arrAuto[1] : 0);
};

$strTemplate = (string) db()->query("SELECT template FROM ploopi_workspace WHERE id = 2")->fetch_row()[0];
register_shutdown_function(function() use ($strTemplate) { db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strTemplate)."' WHERE id = 2"); });
db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
$strEnv = bk_session('admin');
T::eq('ploopi3 : taille par défaut inchangée (800x500)', array('800x500', 0), $funcVue($strEnv));
T::eq('taille inconnue refusée : retour au défaut', array('800x500', 0), $funcVue($strEnv, '&booking_size=99999x99999'));
T::eq('« auto » : largeur mesurée par le navigateur', array('auto', 600), $funcVue($strEnv, '&booking_size=auto&booking_autowidth=600'));
T::eq('« auto » : largeur bornée (2400)', array('auto', 2400), $funcVue($strEnv, '&booking_autowidth=99999'));

db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");
$strEnv = bk_session('admin');
T::eq('ploopi4 : « auto » par défaut (800 avant la première mesure)', array('auto', 800), $funcVue($strEnv));

exit(T::bilan('24_module_booking'));
