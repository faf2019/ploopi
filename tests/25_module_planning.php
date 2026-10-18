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
 * Suite 25 - module planning : droits des operations sur les evenements.
 *
 * Seule action declaree : ajouter un evenement (10). L'interface n'affiche la creation,
 * la modification et la suppression qu'a qui la detient. La suite appelle les operations
 * directement et mesure la base : un utilisateur sans role, l'administrateur, et une
 * seconde instance du module pour le cloisonnement.
 *
 * @package planning
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/planning';
$booDeploye = false;

define('PL_LOGIN', 'planning_sansdroit');
define('PL_MDP', 'Pl4nning!Test2026');
define('PL_EVENT', 7501);
define('PL_DETAIL', 7502);
define('PL_EVENT_B', 7503);
define('PL_DETAIL_B', 7504);

/**
 * Retire le module, ses donnees et l'utilisateur d'essai.
 */
function pl_nettoyer()
{
    global $strModule, $booDeploye;

    $arrRequetes = array(
        "DELETE FROM ploopi_workspace_user WHERE id_user IN (SELECT id FROM ploopi_user WHERE login = '".PL_LOGIN."')",
        "DELETE FROM ploopi_user WHERE login = '".PL_LOGIN."'",
        "DROP TABLE IF EXISTS ploopi_mod_planning_event, ploopi_mod_planning_event_detail, ploopi_mod_planning_event_detail_resource",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'planning%')",
        "DELETE FROM ploopi_module WHERE label LIKE 'planning%'",
        "DELETE FROM ploopi_module_type WHERE label = 'planning'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('pl_nettoyer');
pl_nettoyer();

T::section('Deploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/planning/files').' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}

foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/planning/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('planning', 0, 'Planning', '1.0.4', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;

/**
 * @param string $strLabel libelle
 * @return int instance rattachee a l'espace 2
 */
function pl_instance($strLabel)
{
    global $intType;

    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible) VALUES ('{$strLabel}', {$intType}, 2, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule}, 2, 1, '')");

    return $intModule;
}

$intModA = pl_instance('planning');
$intModB = pl_instance('planning_autre');
T::vrai('deux instances du module', is_dir($strModule.'/include') && $intModA > 0 && $intModB > 0);

db()->query(sprintf(
    "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
     VALUES ('%s', '%s', 'Essai', 'Planning', 'planning@example.test', '%s', 0)",
    PL_LOGIN, db()->real_escape_string(ploopi\user::generate_hash(PL_MDP, PL_LOGIN)), date('YmdHis')
));
$intSansDroit = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intSansDroit}, 2, 1, 10)");

/**
 * Remet les evenements d'essai : un dans chaque instance, crees par l'administrateur.
 */
function pl_reinitialiser()
{
    global $intModA, $intModB;

    db()->query('DELETE FROM ploopi_mod_planning_event');
    db()->query('DELETE FROM ploopi_mod_planning_event_detail');
    db()->query('DELETE FROM ploopi_mod_planning_event_detail_resource');

    foreach (array(array(PL_EVENT, PL_DETAIL, 'EVENEMENTA', $intModA), array(PL_EVENT_B, PL_DETAIL_B, 'EVENEMENTB', $intModB)) as $arrEv)
    {
        db()->query("INSERT INTO ploopi_mod_planning_event (id, object, periodicity, comment, managed, timestp_request, id_user, id_workspace, id_module)
                     VALUES ({$arrEv[0]}, '{$arrEv[2]}', '', '', 0, ".date('YmdHis').", 2, 2, {$arrEv[3]})");
        db()->query("INSERT INTO ploopi_mod_planning_event_detail (id, timestp_begin, timestp_end, validated, canceled, id_event)
                     VALUES ({$arrEv[1]}, ".date('Ymd')."100000, ".date('Ymd')."110000, 0, 0, {$arrEv[0]})");
    }
}

/**
 * @param int $intId evenement
 * @return string|null objet de l'evenement
 */
function pl_objet($intId)
{
    $arrLigne = db()->query('SELECT object FROM ploopi_mod_planning_event WHERE id = '.(int)$intId)->fetch_row();

    return $arrLigne ? $arrLigne[0] : null;
}

/**
 * @param int $intId detail
 * @return array|null ligne du detail
 */
function pl_detail($intId)
{
    return db()->query('SELECT * FROM ploopi_mod_planning_event_detail WHERE id = '.(int)$intId)->fetch_assoc();
}

/**
 * Se connecte et entre dans une instance du module (cf. suite 18).
 *
 * @param string $strLogin identifiant
 * @param string $strMdp mot de passe
 * @param int $intModule instance
 * @return string environnement
 */
function pl_connecter($strLogin, $strMdp, $intModule)
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
 * @param string $strEnv environnement
 * @param string $strOp operation
 * @param string $strQuery parametres
 * @param mixed $mixPost corps POST
 * @return array reponse
 */
function pl_op($strEnv, $strOp, $strQuery = '', $mixPost = null)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

/**
 * Corps POST d'une modification d'evenement : nouvel objet, meme creneau, une ressource.
 *
 * @param string $strObjet nouvel objet
 * @return string corps POST
 */
function pl_post_modification($strObjet)
{
    return 'planning_event_object='.urlencode($strObjet)
        .'&_planning_eventresource_id[user][]=2'
        .'&_planning_event_timestp_begin_d='.urlencode(date('d/m/Y'))
        .'&_planning_event_timestp_begin_h=10&_planning_event_timestp_begin_m=0'
        .'&_planning_event_timestp_end_h=11&_planning_event_timestp_end_m=0';
}

T::section('Modification d\'un evenement');
pl_reinitialiser();
pl_op(pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA), 'planning_event_save', '&planning_event_detail_id='.PL_DETAIL, pl_post_modification('MODIFIEPARADMIN'));
T::eq('temoin : l\'administrateur modifie un evenement de l\'instance courante', 'MODIFIEPARADMIN', pl_objet(PL_EVENT));

pl_reinitialiser();
pl_op(pl_connecter(PL_LOGIN, PL_MDP, $intModA), 'planning_event_save', '&planning_event_detail_id='.PL_DETAIL, pl_post_modification('MODIFIEPARINTRUS'));
T::eq('l\'utilisateur sans droit ne le modifie pas', 'EVENEMENTA', pl_objet(PL_EVENT));

pl_op(pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA), 'planning_event_save', '&planning_event_detail_id='.PL_DETAIL_B, pl_post_modification('MODIFIEDEPUISA'));
T::eq('depuis l\'instance A, pas d\'evenement de l\'instance B', 'EVENEMENTB', pl_objet(PL_EVENT_B));

T::section('Deplacement rapide d\'un evenement');
pl_reinitialiser();
pl_op(pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA), 'planning_event_detail_quicksave', '&planning_event_detail_id='.PL_DETAIL.'&calendar_event_date='.date('Ymd').'&calendar_event_hour=14');
T::eq('temoin : l\'administrateur deplace un evenement', date('Ymd').'140000', (string)(pl_detail(PL_DETAIL)['timestp_begin'] ?? ''));

pl_reinitialiser();
pl_op(pl_connecter(PL_LOGIN, PL_MDP, $intModA), 'planning_event_detail_quicksave', '&planning_event_detail_id='.PL_DETAIL.'&calendar_event_date='.date('Ymd').'&calendar_event_hour=14');
T::eq('l\'utilisateur sans droit ne le deplace pas', date('Ymd').'100000', (string)(pl_detail(PL_DETAIL)['timestp_begin'] ?? ''));

T::section('Suppression d\'un evenement');
pl_reinitialiser();
pl_op(pl_connecter(PL_LOGIN, PL_MDP, $intModA), 'planning_event_detail_delete', '&planning_event_detail_id='.PL_DETAIL);
T::vrai('l\'utilisateur sans droit ne supprime pas un evenement', pl_detail(PL_DETAIL) !== null);

$strEnv = pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA);
pl_op($strEnv, 'planning_event_detail_delete', '&planning_event_detail_id='.PL_DETAIL_B);
T::vrai('depuis l\'instance A, pas celui de l\'instance B', pl_detail(PL_DETAIL_B) !== null);

pl_op($strEnv, 'planning_event_detail_delete', '&planning_event_detail_id='.PL_DETAIL);
T::vrai('temoin : l\'administrateur supprime un evenement de l\'instance courante', pl_detail(PL_DETAIL) === null);

T::section('Recherche d\'evenements');
/**
 * planning_search ne filtrait pas sur l'instance : depuis A, la recherche listait les
 * evenements de B. Les deux evenements ont pour ressource l'administrateur, que le
 * cookie de selection designe.
 */
pl_reinitialiser();
foreach (array(PL_DETAIL, PL_DETAIL_B) as $intDetail)
{
    db()->query("INSERT INTO ploopi_mod_planning_event_detail_resource (id_event_detail, id_resource, type_resource) VALUES ({$intDetail}, 2, 'user')");
}
$strEnv = pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA);
$strCookie = 'planning_request'.$intModA.'='.rawurlencode(base64_encode(gzcompress(serialize(array('planning_resources' => array('user' => array(2 => 2), 'group' => array()))), 9)));
$arrRep = H::req('/admin-light.php?ploopi_op=planning_search&ploopi_env='.urlencode($strEnv), 'query_string=EVENEMENT', array('Cookie: '.$strCookie));
T::contient('temoin : la recherche trouve l\'evenement de l\'instance courante', 'EVENEMENTA', $arrRep['body']);
T::contientpas('pas celui d\'une autre instance', 'EVENEMENTB', $arrRep['body']);

T::section('Taille du calendrier');
/**
 * planning_size() : une des tailles proposées ou « auto » (largeur mesurée par le
 * navigateur, bornée) ; une taille inconnue revient au défaut, « auto » sous un gabarit
 * de génération 4 et la première taille proposée sinon. La taille demandée entrait
 * auparavant telle quelle dans le calendrier.
 */
$funcVue = function($strEnv, $strQuery = '') {
    $arrRep = H::req('/admin-light.php?ploopi_op=planning_refresh&ploopi_env='.urlencode($strEnv).$strQuery);
    preg_match('/<option value="([^"]+)" selected="selected">[^<]*<\/option>/', preg_replace('/<select[^>]*name="planning_(?!size)[\s\S]*?<\/select>/', '', $arrRep['body']), $arrSel);
    preg_match('/data-autowidth="(\d+)" data-autowidth-op="planning_refresh"/', $arrRep['body'], $arrAuto);
    return array(isset($arrSel[1]) ? $arrSel[1] : '', isset($arrAuto[1]) ? (int) $arrAuto[1] : 0, $arrRep['body']);
};

$strTemplate = (string) db()->query("SELECT template FROM ploopi_workspace WHERE id = 2")->fetch_row()[0];
register_shutdown_function(function() use ($strTemplate) { db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strTemplate)."' WHERE id = 2"); });
db()->query("UPDATE ploopi_workspace SET template = 'ploopi3' WHERE id = 2");
$strEnv = pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA);
list($strTaille, $intAuto) = $funcVue($strEnv);
T::eq('ploopi3 : taille par défaut inchangée (800x650)', array('800x650', 0), array($strTaille, $intAuto));
list($strTaille, $intAuto) = $funcVue($strEnv, '&planning_size=99999x99999');
T::eq('taille inconnue refusée : retour au défaut', array('800x650', 0), array($strTaille, $intAuto));
list($strTaille, $intAuto) = $funcVue($strEnv, '&planning_size=auto&planning_autowidth=500');
T::eq('« auto » : largeur mesurée par le navigateur', array('auto', 500), array($strTaille, $intAuto));
list(, $intAuto) = $funcVue($strEnv, '&planning_autowidth=99999');
T::eq('« auto » : largeur bornée (2400)', 2400, $intAuto);
list(, $intAuto, $strCorps) = $funcVue($strEnv, '&planning_display_type=week&planning_autowidth=10');
T::eq('« auto » : largeur bornée (280)', 280, $intAuto);
T::vrai('colonnes étroites : intitulés de jour abrégés, intitulé complet en infobulle', (bool) preg_match('/<div class="day_header_label" title="Lundi \d\d\/\d\d">L \d\d<\/div>/', $strCorps));
list(, , $strCorps) = $funcVue($strEnv, '&planning_size=800x650');
T::vrai('colonnes larges : intitulés complets, sans infobulle', (bool) preg_match('/<div class="day_header_label">Lundi \d\d\/\d\d<\/div>/', $strCorps));

db()->query("UPDATE ploopi_workspace SET template = 'ploopi4' WHERE id = 2");
$strEnv = pl_connecter(cfg('admin_login'), cfg('admin_password'), $intModA);
list($strTaille, $intAuto) = $funcVue($strEnv);
T::eq('ploopi4 : « auto » par défaut (800 avant la première mesure)', array('auto', 800), array($strTaille, $intAuto));
db()->query("UPDATE ploopi_workspace SET template = '".db()->real_escape_string($strTemplate)."' WHERE id = 2");

exit(T::bilan('25_module_planning'));
