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
 * Suite 28 - module booking en frontoffice : le planning public se navigue-t-il ?
 *
 * Le planning s'insere dans un article webedit comme objet WCE. Sa navigation (jour,
 * semaine, mois, choix des ressources) recharge le planning par les operations
 * booking_refresh et booking_setresources, appelees en AJAX sur index-light.php. Aucun
 * jeton n'est emis hors backoffice : sans declaration dans publicops.php, le
 * repartiteur les ignore.
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

$arrDeploye = array();

/**
 * Retire les deux modules et leurs donnees.
 */
function fobk_nettoyer()
{
    global $arrDeploye;

    $arrRequetes = array(
        "DROP TABLE IF EXISTS ploopi_mod_booking_event, ploopi_mod_booking_event_detail, ploopi_mod_booking_resource,
                              ploopi_mod_booking_resource_workspace, ploopi_mod_booking_resourcetype,
                              ploopi_mod_booking_subresource, ploopi_mod_booking_event_subresource",
        "DELETE FROM `ploopi_mod_webedit_article`",
        "DELETE FROM `ploopi_mod_webedit_heading`",
        "DELETE FROM ploopi_user WHERE login = 'fobk_user'",
        "DELETE FROM ploopi_mb_wce_object WHERE id_module_type IN (SELECT id FROM ploopi_module_type WHERE label = 'booking')",
        "DELETE FROM ploopi_role_action WHERE id_role IN (SELECT id FROM ploopi_role WHERE label = 'fobk_role')",
        "DELETE FROM ploopi_workspace_user_role WHERE id_role IN (SELECT id FROM ploopi_role WHERE label = 'fobk_role')",
        "DELETE FROM ploopi_role WHERE label = 'fobk_role'",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label IN ('booking', 'webedit'))",
        "DELETE FROM ploopi_module WHERE label IN ('booking', 'webedit')",
        "DELETE FROM ploopi_module_type WHERE label IN ('booking', 'webedit')"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    foreach ($arrDeploye as $strDossier) if (is_dir($strDossier)) exec('rm -rf '.escapeshellarg($strDossier));
}

register_shutdown_function('fobk_nettoyer');
fobk_nettoyer();

T::section('Deploiement des modules');

/**
 * Deploie un module et l'instancie dans l'espace 2 (cf. suite 19).
 *
 * @param string $strModule nom du module
 * @param string $strVersion version declaree
 * @return array [type, instance]
 */
function fobk_deployer($strModule, $strVersion)
{
    global $arrDeploye;

    $strCible = PLOOPI_ROOT."/modules/{$strModule}";
    if (!is_dir($strCible))
    {
        exec('cp -r '.escapeshellarg(PLOOPI_ROOT."/install/{$strModule}/files").' '.escapeshellarg($strCible), $arrSortie, $intRc);
        if ($intRc === 0) $arrDeploye[] = $strCible;
    }

    foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT."/install/{$strModule}/structure.sql")) as $strRequete)
    {
        if (trim($strRequete) === '') continue;
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
                 VALUES ('{$strModule}', 0, '{$strModule}', '{$strVersion}', 'Ovensia', '".date('YmdHis')."')");
    $intType = db()->insert_id;
    db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible, public)
                 VALUES ('{$strModule}', {$intType}, 2, 1, 1, 1)");
    $intModule = db()->insert_id;
    db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
                 VALUES ({$intModule}, 2, 1, '')");

    return array($intType, $intModule);
}

list($intTypeWebedit, $intWebedit) = fobk_deployer('webedit', '2.8.5');
list($intTypeBooking, $intBooking) = fobk_deployer('booking', '1.3.7');
T::vrai('webedit et booking deployes', $intWebedit > 0 && $intBooking > 0);

// L'objet WCE « Affichage Calendrier » declare par install/booking/mb.xml
db()->query("INSERT INTO ploopi_mb_wce_object (id, label, id_module_type, script, select_id, select_label, select_table)
             VALUES (1, 'Affichage Calendrier', {$intTypeBooking}, '?object=\\'display\\'', '', '', '')");

// Une ressource active, rattachee a l'espace : le planning a quelque chose a montrer
db()->query("INSERT INTO ploopi_mod_booking_resourcetype (id, name, active, id_user, id_workspace, id_module) VALUES (1, 'Salles', 1, 2, 2, {$intBooking})");
db()->query("INSERT INTO ploopi_mod_booking_resource (id, name, reference, color, active, id_resourcetype, id_user, id_workspace, id_module)
             VALUES (1, 'SALLEPUBLIQUE', '', '#ccc', 1, 1, 2, 2, {$intBooking})");
db()->query("INSERT INTO ploopi_mod_booking_resource_workspace (id_resource, id_workspace) VALUES (1, 2)");

/**
 * Une seconde ressource active, rattachee a aucun espace gestionnaire : cas legitime
 * (ressource creee, pas encore affectee), qui faisait echouer booking_get_resources()
 * en PHP 8 (in_array() sur null), donc tout le planning.
 */
db()->query("INSERT INTO ploopi_mod_booking_resource (id, name, reference, color, active, id_resourcetype, id_user, id_workspace, id_module)
             VALUES (2, 'SALLESANSESPACE', '', '#ccc', 1, 1, 2, 2, {$intBooking})");

$arrEtatFo = db()->query('SELECT frontoffice, frontoffice_domainlist FROM ploopi_workspace WHERE id = 2')->fetch_assoc();
db()->query("UPDATE ploopi_workspace SET frontoffice = 1, frontoffice_domainlist = '*' WHERE id = 2");
register_shutdown_function(function() use ($arrEtatFo) {
    if (empty($arrEtatFo)) return;
    db()->query(sprintf(
        "UPDATE ploopi_workspace SET frontoffice = %d, frontoffice_domainlist = '%s' WHERE id = 2",
        (int)$arrEtatFo['frontoffice'], db()->real_escape_string($arrEtatFo['frontoffice_domainlist'])
    ));
});

db()->query("INSERT INTO ploopi_mod_webedit_heading (label, description, template, id_heading, parents, depth, position,
                                                     content_type, visible, private, private_visible, sortmode,
                                                     id_module, id_user, id_workspace)
             VALUES ('Reservations', '', 'corporate', 0, '', 0, 1, 'articles', 1, 0, 0, 0, {$intWebedit}, 2, 2)");
$intRubrique = db()->insert_id;
$strContenu = "<p>PLANNING CI-DESSOUS</p>[[1,{$intBooking}]]";
db()->query(sprintf(
    "INSERT INTO ploopi_mod_webedit_article (reference, title, metakeywords, content, content_cleaned,
                                             metadescription, metatitle, author, version, visible,
                                             comments_allowed, timestp, timestp_published, timestp_unpublished,
                                             lastupdate_timestp, lastupdate_id_user, id_heading, id_module,
                                             id_user, id_workspace, position, status, tags)
     VALUES ('', 'Reservations', '', '%s', '%s', '', '', '', 1, 1, 0, '%s', '%s', 0, '%s', 2, %d, %d, 2, 2, 1, 1, '')",
    db()->real_escape_string($strContenu), db()->real_escape_string($strContenu),
    date('YmdHis'), date('YmdHis', strtotime('-10 days')), date('YmdHis'), $intRubrique, $intWebedit
));
$intArticle = db()->insert_id;

T::section('Planning public : rendu puis navigation');
H::reset();
$arrRep = H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");
T::contient('temoin : l\'article est servi', 'PLANNING CI-DESSOUS', $arrRep['body']);
T::contient('temoin : le planning est rendu dans l\'article', 'Affichage :', $arrRep['body']);

/**
 * Le lien « Mensuel », tel que le module le rend : attributs data-ploopi-todiv (CSP stricte),
 * requête envoyée à index-light.php
 */
$strRequete = '';
if (preg_match('/title="Mois" data-ploopi-todiv="booking_main" data-ploopi-script="index-light\.php" data-ploopi-query="([^"]*)"/', $arrRep['body'], $arrMatches)) $strRequete = html_entity_decode($arrMatches[1]);
T::vrai('le lien de navigation est rendu', $strRequete !== '', 'requete absente');

$arrRep = H::req('/index-light.php?'.$strRequete);
/**
 * Une operation ignoree laisse index-light.php rendre la page entiere, article compris,
 * qui contient aussi le planning : la reponse attendue est le fragment seul.
 */
T::contient('la navigation recharge le planning pour un visiteur', 'Affichage :', $arrRep['body']);
T::contientpas('sous forme de fragment, pas de la page entiere', 'PLANNING CI-DESSOUS', $arrRep['body']);

T::section('Planning public : identifiants de ressources');
/**
 * booking_refresh est desormais publique : ses parametres viennent d'un visiteur. Un
 * identifiant de ressource non numerique doit etre ecarte, pas transmis a la base.
 */
H::reset();
H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");

/**
 * L'erreur SQL est avalee et le planning rendu vide : seule la journalisation des erreurs
 * de l'instance (_PLOOPI_LOG_ERRORS_FILE) revele la valeur transmise a la base.
 */
$strJournal = defined('_PLOOPI_LOG_ERRORS_FILE') ? _PLOOPI_LOG_ERRORS_FILE : '/tmp/ploopi_error.log';
clearstatcache();
$intTailleAvant = file_exists($strJournal) ? filesize($strJournal) : 0;

$arrRep = H::req('/index-light.php?'.$strRequete.'&booking_resources[]=1&booking_resources[]=abc');
T::contient('temoin : le planning est rendu', 'Affichage :', $arrRep['body']);

clearstatcache();
$strAjout = file_exists($strJournal) ? (string)file_get_contents($strJournal, false, null, $intTailleAvant) : '';
T::contientpas('un identifiant non numerique n\'atteint pas la base', 'id_resource IN (1,abc)', $strAjout);

/**
 * Demande de reservation depuis le frontoffice (lot 8, decision D3).
 *
 * booking_event_save ecrit avec l\'identite de la session : sans jeton frontoffice,
 * elle ne pouvait etre ni protegee ni declaree publique — faire une demande depuis
 * le site etait impossible. Le frontoffice d\'une session connectee porte desormais
 * un jeton : la demande aboutit pour lui, est refusee sans jeton (le scenario CSRF),
 * et l\'anonyme n\'obtient rien non plus.
 */
T::section('Demande de reservation : sessions');

define('FOBK_MDP', 'Frontoffice!2026');

// utilisateur connecte, avec l action de demande de reservation sur l instance
db()->query(sprintf(
    "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
     VALUES ('fobk_user', '%s', 'Front', 'Office', 'fobk@example.test', '%s', 0)",
    db()->real_escape_string(ploopi\user::generate_hash(FOBK_MDP, 'fobk_user')), date('YmdHis')
));
$intUser = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");
db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description) VALUES ({$intBooking}, 2, 'fobk_role', '')");
$intRole = db()->insert_id;
db()->query("INSERT INTO ploopi_role_action (id_role, id_action, id_module_type) VALUES ({$intRole}, 30, {$intTypeBooking})");
db()->query("INSERT INTO ploopi_workspace_user_role (id_user, id_workspace, id_role) VALUES ({$intUser}, 2, {$intRole})");
T::vrai('utilisateur connecte cree avec l action de demande', $intUser > 0 && $intRole > 0);

$intEvenements = function () { return (int)db()->query('SELECT COUNT(*) c FROM ploopi_mod_booking_event')->fetch_assoc()['c']; };

/**
 * Les parametres que le formulaire public poste : dates du jour, ressource 1.
 *
 * @param int $intModule instance du module
 * @param string $strEnv environnement (jeton compris), vide pour l omission
 * @return string corps POST
 */
$corps_demande = function ($intModule, $strEnv = '') {
    $strTs = date('d/m/Y');
    $strCorps = 'booking_event_id=0'
        .'&booking_moduleid='.$intModule
        .'&booking_event_id_resource=1'
        .'&booking_event_object=Demande frontoffice'
        .'&_booking_event_timestp_begin_d='.$strTs
        .'&_booking_event_timestp_begin_h=10'
        .'&_booking_event_timestp_begin_i=00'
        .'&_booking_event_timestp_end_d='.$strTs
        .'&_booking_event_timestp_end_h=11'
        .'&_booking_event_timestp_end_i=00'
        .'&booking_event_comment=';
    if ($strEnv !== '') $strCorps .= '&ploopi_env='.urlencode($strEnv);
    return $strCorps;
};

// anonyme : la page article d abord (session frontoffice), puis la demande
H::reset();
H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");
$intAvant = $intEvenements();
H::req('/index-light.php?ploopi_op=booking_event_save&booking_moduleid='.$intBooking, $corps_demande($intBooking));
T::eq('anonyme : aucune demande enregistree', $intAvant, $intEvenements());

// session connectee : le frontoffice porte un jeton
H::reset();
$arrRep = H::req('/admin.php', 'ploopi_login=fobk_user&ploopi_password='.urlencode(FOBK_MDP));
T::vrai('connexion acceptee', $arrRep['code'] === 302);
H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");
$arrRep = H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");
preg_match('/_PLOOPI_ENV *= *\'([^\']*)\'/', $arrRep['body'], $arrMatches);
$strEnv = isset($arrMatches[1]) ? $arrMatches[1] : '';
T::vrai('le frontoffice connecte porte un jeton', preg_match('/^\/?[0-9]*\/2\/[^\/]*\/[a-z]+\/[0-9a-f]{32}$/', $strEnv) === 1, $strEnv);

$intAvant = $intEvenements();
H::req('/index-light.php?ploopi_op=booking_event_save&booking_moduleid='.$intBooking, $corps_demande($intBooking, $strEnv));
T::vrai('session connectee : la demande est enregistree', $intEvenements() === $intAvant + 1, 'jeton='.$strEnv);

// sans jeton : le scenario CSRF — requete forgee depuis un site tiers
$intAvant = $intEvenements();
H::req('/index-light.php?ploopi_op=booking_event_save&booking_moduleid='.$intBooking, $corps_demande($intBooking));
T::eq('sans jeton : aucune demande enregistree (le scenario CSRF)', $intAvant, $intEvenements());

/**
 * Peremption des jetons du frontoffice.
 *
 * Un jeton est emis a chaque page. Seule la purge (_PLOOPI_TOKENTIME,
 * _PLOOPI_TOKENMAX) les fait expirer : checktoken() ne regarde que leur presence
 * en session. Cette purge n'etait faite qu'en backoffice : en frontoffice, les
 * jetons s'accumulaient en session et restaient valides indefiniment. On vieillit
 * les jetons de la session dans son fichier, on charge une page : le jeton perime
 * doit avoir disparu et ne plus rien permettre.
 */
T::section('Demande de reservation : peremption des jetons');

$strFichierSession = chemin_session(H::sid());
if ($strFichierSession === false)
{
    T::skip('le jeton perime est purge', 'dossier de sessions introuvable, voir session_path');
}
else
{
    /**
     * Jetons d'un fichier de session (format du gestionnaire php), vieillis au
     * passage si demande. session_decode()/session_encode() exigent une session
     * active, impossible a ouvrir apres la premiere sortie de la suite : un
     * sous-processus PHP s'en charge, dans un dossier de sessions temporaire.
     *
     * @param string $strFichier fichier de session du serveur
     * @param bool $booVieillir true pour dater tous les jetons de 1970 et reecrire le fichier
     * @return array jetons => date d'emission
     */
    $jetons_session = function ($strFichier, $booVieillir = false) {
        $strCode = <<<'PHP'
session_save_path(sys_get_temp_dir());
session_start();
session_decode((string)file_get_contents($argv[1]));
$arrJetons = isset($_SESSION['ploopi']['tokens']) ? $_SESSION['ploopi']['tokens'] : array();
if ($argv[2] === '1')
{
    foreach ($arrJetons as $strCle => $intDate) $_SESSION['ploopi']['tokens'][$strCle] = $arrJetons[$strCle] = 0;
    file_put_contents($argv[1], session_encode());
}
echo json_encode($arrJetons);
session_destroy();
PHP;
        $strSortie = shell_exec(escapeshellarg(PHP_BINARY).' -r '.escapeshellarg($strCode).' '.escapeshellarg($strFichier).' '.($booVieillir ? '1' : '0').' 2>/dev/null');
        $arrJetons = json_decode((string)$strSortie, true);
        return is_array($arrJetons) ? $arrJetons : array();
    };

    $strJetonPerime = (string)(explode('/', $strEnv)[4] ?? '');
    $arrJetons = $jetons_session($strFichierSession, true);
    T::vrai('le jeton de la page figure en session', $strJetonPerime !== '' && isset($arrJetons[$strJetonPerime]), count($arrJetons).' jeton(s) en session');

    // tous les jetons de la session datent desormais de 1970 : une page les purge
    $arrRep = H::req("/articles/reservations-h{$intRubrique}a{$intArticle}.html");
    preg_match('/_PLOOPI_ENV *= *\'([^\']*)\'/', $arrRep['body'], $arrMatches);
    $strEnvNeuf = isset($arrMatches[1]) ? $arrMatches[1] : '';

    $arrJetons = $jetons_session($strFichierSession);
    T::vrai('le jeton perime est purge de la session', $arrJetons !== array() && !isset($arrJetons[$strJetonPerime]), count($arrJetons).' jeton(s) en session');

    $intAvant = $intEvenements();
    H::req('/index-light.php?ploopi_op=booking_event_save&booking_moduleid='.$intBooking, $corps_demande($intBooking, $strEnv));
    T::eq('jeton perime : aucune demande enregistree', $intAvant, $intEvenements());

    // temoin : le jeton de la nouvelle page est accepte
    $intAvant = $intEvenements();
    H::req('/index-light.php?ploopi_op=booking_event_save&booking_moduleid='.$intBooking, $corps_demande($intBooking, $strEnvNeuf));
    T::vrai('temoin : le jeton de la nouvelle page est accepte', $intEvenements() === $intAvant + 1, 'jeton='.$strEnvNeuf);
}

exit(T::bilan('28_module_booking_frontoffice'));
