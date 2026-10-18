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
 * Suite 26 - module dbreport : a quelles requetes un utilisateur accede-t-il ?
 *
 * La liste des requetes ne montre que celles de l'instance courante et des espaces
 * visibles, et ne propose la modification qu'a qui gere les requetes (10) et, pour une
 * requete verrouillee, detient aussi l'action de verrouillage (99). La suite appelle les
 * operations directement avec l'identifiant d'une requete et mesure la base.
 *
 * @package dbreport
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/dbreport';
$booDeploye = false;

define('DBR_LOGIN', 'dbreport_lecteur');
define('DBR_LOGIN_GEST', 'dbreport_gestionnaire');
define('DBR_MDP', 'Dbr3port!Test2026');
define('DBR_QA', 7601);        // requete de l'instance A
define('DBR_QA_VERROU', 7602); // requete verrouillee de l'instance A
define('DBR_QB', 7603);        // requete de l'instance B

/**
 * Retire le module, ses donnees, le role et les utilisateurs d'essai.
 */
function dbr_nettoyer()
{
    global $strModule, $booDeploye;

    $strUsers = "SELECT id FROM ploopi_user WHERE login IN ('".DBR_LOGIN."', '".DBR_LOGIN_GEST."')";
    $strRoles = "SELECT id FROM ploopi_role WHERE label = 'dbreport_tests'";
    $arrRequetes = array(
        "DELETE FROM ploopi_role_action WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_workspace_user_role WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_role WHERE label = 'dbreport_tests'",
        "DELETE FROM ploopi_workspace_user WHERE id_user IN ({$strUsers})",
        "DELETE FROM ploopi_user WHERE login IN ('".DBR_LOGIN."', '".DBR_LOGIN_GEST."')",
        "DROP TABLE IF EXISTS ploopi_mod_dbreport_query, ploopi_mod_dbreport_queryfield, ploopi_mod_dbreport_queryrelation,
                              ploopi_mod_dbreport_querytable, ploopi_mod_dbreport_query_module_type",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'dbreport%')",
        "DELETE FROM ploopi_module WHERE label LIKE 'dbreport%'",
        "DELETE FROM ploopi_module_type WHERE label = 'dbreport'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('dbr_nettoyer');
dbr_nettoyer();

T::section('Deploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/dbreport/files').' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}

foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/dbreport/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('dbreport', 0, 'Requetes', '2.1.1', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;

/**
 * @param string $strLabel libelle
 * @return int instance rattachee a l'espace 2
 */
function dbr_instance($strLabel)
{
    global $intType;

    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible) VALUES ('{$strLabel}', {$intType}, 2, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule}, 2, 1, '')");

    return $intModule;
}

$intModA = dbr_instance('dbreport');
$intModB = dbr_instance('dbreport_autre');
T::vrai('deux instances du module', is_dir($strModule.'/include') && $intModA > 0 && $intModB > 0);

/**
 * Cree un utilisateur sans role, rattache a l'espace 2.
 *
 * @param string $strLogin identifiant
 * @return int identifiant
 */
function dbr_utilisateur($strLogin)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Essai', 'Dbreport', 'dbreport@example.test', '%s', 0)",
        $strLogin, db()->real_escape_string(ploopi\user::generate_hash(DBR_MDP, $strLogin)), date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

    return $intUser;
}

dbr_utilisateur(DBR_LOGIN);
$intGest = dbr_utilisateur(DBR_LOGIN_GEST);

// Le gestionnaire gere les requetes de l'instance A, sans l'action de verrouillage
db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description) VALUES ({$intModA}, 2, 'dbreport_tests', '')");
$intRole = db()->insert_id;
db()->query("INSERT INTO ploopi_role_action (id_role, id_action, id_module_type) VALUES ({$intRole}, 10, {$intType})");
db()->query("INSERT INTO ploopi_workspace_user_role (id_user, id_workspace, id_role) VALUES ({$intGest}, 2, {$intRole})");

/**
 * Remet les trois requetes d'essai.
 */
function dbr_reinitialiser()
{
    global $intModA, $intModB;

    db()->query('DELETE FROM ploopi_mod_dbreport_query');
    db()->query('DELETE FROM ploopi_mod_dbreport_queryfield');
    db()->query('DELETE FROM ploopi_mod_dbreport_querytable');
    foreach (array(array(DBR_QA, 'REQUETEA', 0, $intModA), array(DBR_QA_VERROU, 'REQUETEVERROU', 1, $intModA), array(DBR_QB, 'REQUETEB', 0, $intModB)) as $arrQ)
    {
        db()->query("INSERT INTO ploopi_mod_dbreport_query (id, label, ws_id, ws_code, ws_ip, chart_title, chart_subtitle, chart_font,
                                                            chart_value_x_prefix, chart_value_x_suffix, chart_value_x_format,
                                                            chart_value_y_prefix, chart_value_y_suffix, chart_value_y_format,
                                                            locked, id_user, id_workspace, id_module)
                     VALUES ({$arrQ[0]}, '{$arrQ[1]}', '', '', '', '', '', '', '', '', '', '', '', '', {$arrQ[2]}, 2, 2, {$arrQ[3]})");

        // Une table et un champ connus de la metabase : la requete produit du SQL
        db()->query("INSERT INTO ploopi_mod_dbreport_querytable (id, tablename, alias, id_module_type, id_query) VALUES ({$arrQ[0]}, 'ploopi_user', '', 1, {$arrQ[0]})");
        db()->query("INSERT INTO ploopi_mod_dbreport_queryfield (tablename, id_module_type, fieldname, label, function, function_group, visible, sort,
                                                                 criteria, type_criteria, `or`, type_or, intervals, operation, position, series, id_query)
                     VALUES ('ploopi_user', 1, 'login', 'champ{$arrQ[0]}', '', '', 1, '', '', '', '', '', '', '', 1, 0, {$arrQ[0]})");
    }
}

/**
 * @param int $intId requete
 * @return string|null libelle
 */
function dbr_libelle($intId)
{
    $arrLigne = db()->query('SELECT label FROM ploopi_mod_dbreport_query WHERE id = '.(int)$intId)->fetch_row();

    return $arrLigne ? $arrLigne[0] : null;
}

/**
 * Se connecte et entre dans une instance du module (cf. suite 18).
 *
 * @param string $strLogin identifiant
 * @param int $intModule instance
 * @return string environnement
 */
function dbr_connecter($strLogin, $intModule)
{
    H::reset();
    H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode(DBR_MDP));

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
function dbr_op($strEnv, $strOp, $strQuery = '', $mixPost = null)
{
    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

T::section('Lecture d\'une requete');
dbr_reinitialiser();
$strEnv = dbr_connecter(DBR_LOGIN, $intModA);
$arrRep = dbr_op($strEnv, 'dbreport_query_export', '', 'dbreport_query_id='.DBR_QA);
T::contient('temoin : un utilisateur exporte une requete de son instance', 'Choix du format', $arrRep['body']);

$arrRep = dbr_op($strEnv, 'dbreport_query_export', '', 'dbreport_query_id='.DBR_QB);
T::contientpas('pas une requete d\'une autre instance', 'Choix du format', $arrRep['body']);

$arrRep = dbr_op($strEnv, 'dbreport_query_exec', '&dbreport_query_id='.DBR_QA.'&dbreport_format=sql');
T::contient('temoin : il execute une requete de son instance', 'champ'.DBR_QA, $arrRep['body']);

$arrRep = dbr_op($strEnv, 'dbreport_query_exec', '&dbreport_query_id='.DBR_QB.'&dbreport_format=sql');
T::contientpas('ni n\'execute une requete d\'une autre instance', 'champ'.DBR_QB, $arrRep['body']);

T::section('Suppression d\'une requete');
dbr_reinitialiser();
$strEnv = dbr_connecter(DBR_LOGIN_GEST, $intModA);
dbr_op($strEnv, 'dbreport_query_delete', '&dbreport_query_id='.DBR_QB);
T::vrai('le gestionnaire de A ne supprime pas une requete de B', dbr_libelle(DBR_QB) !== null);

dbr_op($strEnv, 'dbreport_query_delete', '&dbreport_query_id='.DBR_QA_VERROU);
T::vrai('ni une requete verrouillee, sans l\'action de verrouillage', dbr_libelle(DBR_QA_VERROU) !== null);

dbr_op($strEnv, 'dbreport_query_delete', '&dbreport_query_id='.DBR_QA);
T::vrai('temoin : il supprime une requete de son instance', dbr_libelle(DBR_QA) === null);

T::section('Modification d\'une requete');
dbr_reinitialiser();
$strEnv = dbr_connecter(DBR_LOGIN_GEST, $intModA);
dbr_op($strEnv, 'dbreport_query_save', '&dbreport_query_id='.DBR_QB, 'dbreport_query_label=DETOURNEE');
T::eq('le gestionnaire de A ne renomme pas une requete de B', 'REQUETEB', dbr_libelle(DBR_QB));

dbr_op($strEnv, 'dbreport_query_save', '&dbreport_query_id='.DBR_QA_VERROU, 'dbreport_query_label=DETOURNEE');
T::eq('ni une requete verrouillee', 'REQUETEVERROU', dbr_libelle(DBR_QA_VERROU));

dbr_op($strEnv, 'dbreport_query_save', '&dbreport_query_id='.DBR_QA, 'dbreport_query_label=RENOMMEE');
T::eq('temoin : il renomme une requete de son instance', 'RENOMMEE', dbr_libelle(DBR_QA));

T::section('Accents des graphiques et des exports');
/**
 * Les libelles des graphiques passaient par l'equivalent d'utf8_encode() sur des
 * chaines deja en UTF-8 (« RÃ©partition »), et l'export HTML s'annoncait en
 * ISO-8859-1 pour des donnees UTF-8.
 */
dbr_reinitialiser();
$intChamp = (int)db()->query('SELECT id FROM ploopi_mod_dbreport_queryfield WHERE id_query = '.DBR_QA)->fetch_row()[0];
db()->query("UPDATE ploopi_mod_dbreport_queryfield SET label = 'Identité' WHERE id = {$intChamp}");
db()->query("INSERT INTO ploopi_mod_dbreport_queryfield (tablename, id_module_type, fieldname, label, function, function_group, visible, sort,
                                                         criteria, type_criteria, `or`, type_or, intervals, operation, position, series, id_query)
             VALUES ('ploopi_user', 1, 'id', 'Valeur', '', '', 1, '', '', '', '', '', '', '', 2, 0, ".DBR_QA.")");
$intValeur = (int)db()->insert_id;
db()->query("UPDATE ploopi_mod_dbreport_query SET chart = 'pie', chart_x = {$intChamp}, chart_val = {$intValeur}, chart_title = 'Répartition des comptes' WHERE id = ".DBR_QA);
$strEnv = dbr_connecter(DBR_LOGIN, $intModA);

$arrRep = dbr_op($strEnv, 'dbreport_chart_generate', '&dbreport_query_id='.DBR_QA);
T::contient('temoin : le graphique est rendu par Chart.js', 'dbreport_chart.render(', $arrRep['body']);
T::contient('la page charge Chart.js', 'lib/chartjs/chart.umd.js', $arrRep['body']);
T::contientpas('et plus Highcharts, dont la licence n\'est pas libre', 'highcharts.js', strtolower($arrRep['body']));
T::contient('le titre accentue est intact', 'Répartition des comptes', $arrRep['body']);
T::contientpas('sans double encodage', 'RÃ©partition', $arrRep['body']);
T::contientpas('et plus Prototype, retiré du socle', 'protoaculous', $arrRep['body']);
T::vrai('Chart.js est livré avec sa licence', is_file(PLOOPI_ROOT.'/install/dbreport/files/lib/chartjs/LICENSE.md') && !is_dir(PLOOPI_ROOT.'/install/dbreport/files/lib/highcharts'));

// Un libellé saisi ne sort pas du <script> : la configuration est un JSON aux balises neutralisées
db()->query("UPDATE ploopi_mod_dbreport_query SET chart_title = 'x</script><b id=\"evade\">' WHERE id = ".DBR_QA);
$arrRep = dbr_op($strEnv, 'dbreport_chart_generate', '&dbreport_query_id='.DBR_QA);
T::contient('temoin : le titre piégé est transmis', 'x\u003C\/script\u003E', $arrRep['body']);
T::contientpas('il ne ferme pas le script', '<b id="evade">', $arrRep['body']);
db()->query("UPDATE ploopi_mod_dbreport_query SET chart_title = 'Répartition des comptes' WHERE id = ".DBR_QA);

$arrRep = dbr_op($strEnv, 'dbreport_query_exec', '&dbreport_query_id='.DBR_QA.'&dbreport_format=html');
T::contient('temoin : l\'export HTML est rendu', '<th>Identit', $arrRep['body']);
T::vrai('il s\'annonce en UTF-8', stripos($arrRep['headers'], 'charset=utf-8') !== false && stripos($arrRep['body'], 'charset=UTF-8') !== false, $arrRep['headers']);

$arrRep = dbr_op($strEnv, 'dbreport_query_exec', '&dbreport_query_id='.DBR_QA.'&dbreport_format=pdf');
T::vrai('l\'export PDF (Dompdf) produit un PDF', substr($arrRep['body'], 0, 5) === '%PDF-', substr($arrRep['body'], 0, 120));

T::section('Création et clonage d\'une requête (SQL strict)');
// Une requête, un champ ou une relation créés n'avaient que les colonnes saisies : en SQL
// strict, l'INSERT échouait sur la première colonne NOT NULL sans défaut (ws_id,
// function_group…), sans message ; le clonage omettait aussi le type de jointure
dbr_reinitialiser();
db()->query('DELETE FROM ploopi_mod_dbreport_queryrelation');
$strEnv = dbr_connecter(DBR_LOGIN_GEST, $intModA);
dbr_op($strEnv, 'dbreport_query_save', '', 'dbreport_query_label=CREEE&dbreport_query_rowlimit=100');
$arrCree = db()->query("SELECT id, id_module FROM ploopi_mod_dbreport_query WHERE label = 'CREEE'")->fetch_assoc();
T::vrai('une requête se crée', !empty($arrCree) && (int)$arrCree['id_module'] === $intModA);

db()->query("INSERT INTO ploopi_mod_dbreport_querytable (tablename, alias, id_module_type, id_query) VALUES ('ploopi_workspace', '', 1, ".DBR_QA.")");
db()->query("INSERT INTO ploopi_mod_dbreport_queryrelation (id_query, tablename_src, fieldname_src, tablename_dest, fieldname_dest, type_join, active)
             VALUES (".DBR_QA.", 'ploopi_user', 'id', 'ploopi_workspace', 'id', 'left', 1)");
dbr_op($strEnv, 'dbreport_query_clone', '&dbreport_query_id='.DBR_QA);
$arrClone = db()->query("SELECT q.id,
                                (SELECT COUNT(*) FROM ploopi_mod_dbreport_querytable t WHERE t.id_query = q.id) AS tables,
                                (SELECT COUNT(*) FROM ploopi_mod_dbreport_queryfield f WHERE f.id_query = q.id) AS champs,
                                (SELECT GROUP_CONCAT(r.type_join) FROM ploopi_mod_dbreport_queryrelation r WHERE r.id_query = q.id) AS jointures
                         FROM ploopi_mod_dbreport_query q WHERE q.label = 'Clone de REQUETEA'")->fetch_assoc();
T::vrai('le clone existe', !empty($arrClone));
T::eq('avec ses tables', 2, $arrClone ? (int)$arrClone['tables'] : 0);
T::eq('avec ses champs', 1, $arrClone ? (int)$arrClone['champs'] : 0);
T::eq('et ses relations, type de jointure compris', 'left', $arrClone ? $arrClone['jointures'] : null);
db()->query('DELETE FROM ploopi_mod_dbreport_queryrelation');

T::section('Graphique sur un champ texte');
// Valeurs prises dans un champ texte : la génération s'arrêtait (TypeError en PHP 8)
dbr_reinitialiser();
$intTexte = (int)db()->query('SELECT id FROM ploopi_mod_dbreport_queryfield WHERE id_query = '.DBR_QA)->fetch_row()[0];
db()->query("UPDATE ploopi_mod_dbreport_query SET chart = 'pie', chart_x = {$intTexte}, chart_val = {$intTexte} WHERE id = ".DBR_QA);
$strEnv = dbr_connecter(DBR_LOGIN, $intModA);
$arrRep = dbr_op($strEnv, 'dbreport_chart_generate', '&dbreport_query_id='.DBR_QA);
T::contient('le graphique est rendu', 'dbreport_chart.render(', $arrRep['body']);
T::contientpas('sans page d\'erreur', 'Une erreur est survenue', $arrRep['body']);

exit(T::bilan('26_module_dbreport'));
