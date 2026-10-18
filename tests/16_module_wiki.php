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
 * Suite 16 — module wiki : les droits déclarés sont-ils appliqués ?
 *
 * description.xml déclare cinq actions — modifier (10), renommer (11), supprimer (12),
 * verrouiller (20), déverrouiller (21) — et public_view.php s'en sert pour décider des
 * boutons à afficher. op.php, lui, exécute ces mêmes actions et n'est protégé que par
 * un acl::ismoduleallowed('wiki'), c'est-à-dire « le module est dans votre espace ».
 *
 * Or doc/reference.md §8.4 est explicite : le contrôle de droits va en tête de CHAQUE action,
 * y compris les op_*, « elles sont appelables directement en HTTP ».
 *
 * La suite agit donc avec un utilisateur sans aucun rôle : un administrateur système
 * passe tous les contrôles d'ACL et ne prouverait rien.
 *
 * @package wiki
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/wiki';
$strSource = PLOOPI_ROOT.'/install/wiki/files';
$booDeploye = false;

define('WIKI_LOGIN', 'wiki_sansdroit');
define('WIKI_MDP', 'W1ki!Test2026');

/**
 * Retire le module, ses données et l'utilisateur d'essai. Appelé quoi qu'il arrive.
 */
function wiki_nettoyer()
{
    global $strModule, $booDeploye;

    $arrRequetes = array(
        "DELETE FROM `ploopi_mod_wiki_page`",
        "DELETE FROM `ploopi_mod_wiki_page_history`",
        "DELETE FROM ploopi_workspace_user WHERE id_user IN (SELECT id FROM ploopi_user WHERE login = '".WIKI_LOGIN."')",
        "DELETE FROM ploopi_user WHERE login = '".WIKI_LOGIN."'",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label = 'wiki')",
        "DELETE FROM ploopi_module WHERE label = 'wiki'",
        "DELETE FROM ploopi_module_type WHERE label = 'wiki'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('wiki_nettoyer');
wiki_nettoyer();

T::section('Déploiement du module');

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg($strSource).' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}
T::vrai('modules/wiki déployé depuis install/wiki/files', is_dir($strModule.'/classes'));

$arrErreurs = array();
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/wiki/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    if (!db()->query($strRequete)) $arrErreurs[] = db()->error;
}
T::vrai('structure.sql exécuté', empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 2)));

db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('wiki', 0, 'Wiki', '1.4.3', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
             VALUES ('wiki', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intModule}, 2, 1, '')");
T::vrai('instance du module enregistrée', $intModule > 0, "id={$intModule}");

/**
 * Crée ou réinitialise une page du wiki.
 *
 * @param string $strId identifiant de la page
 * @param string $strContenu contenu
 * @param int $intVerrouillee 1 si la page est verrouillée
 * @param int $intModule instance de module
 */
function wiki_page_creer($strId, $strContenu, $intVerrouillee, $intModule)
{
    $strTs = date('YmdHis');

    db()->query(sprintf(
        "REPLACE INTO ploopi_mod_wiki_page (id, content, root, tags, revision, locked,
                                            ts_created, ts_modified, ts_validated, id_user, id_workspace, id_module)
         VALUES ('%s', '%s', 0, '', 1, %d, '%s', '%s', 0, 2, 2, %d)",
        db()->real_escape_string($strId), db()->real_escape_string($strContenu),
        (int)$intVerrouillee, $strTs, $strTs, (int)$intModule
    ));
}

/**
 * Relit une page.
 *
 * @param string $strId identifiant
 * @return array|null ligne, ou null si la page n'existe plus
 */
function wiki_page_lire($strId)
{
    $objRs = db()->query("SELECT * FROM ploopi_mod_wiki_page WHERE id = '".db()->real_escape_string($strId)."'");

    return $objRs->fetch_assoc();
}

wiki_page_creer('accueil', 'Contenu initial de la page.', 0, $intModule);
wiki_page_creer('protegee', 'Page verrouillée.', 1, $intModule);
T::vrai('pages d\'essai créées', wiki_page_lire('accueil') !== null && wiki_page_lire('protegee') !== null);

T::section('Utilisateur sans aucun droit');
$strHash = ploopi\user::generate_hash(WIKI_MDP, WIKI_LOGIN);
db()->query(sprintf(
    "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
     VALUES ('%s', '%s', 'Sans', 'Droit', 'sansdroit@example.test', '%s', 0)",
    WIKI_LOGIN, db()->real_escape_string($strHash), date('YmdHis')
));
$intUser = db()->insert_id;
// Rattaché à l'espace 2, simple utilisateur, sans aucun rôle : aucune action du wiki
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel)
             VALUES ({$intUser}, 2, 1, 10)");
T::vrai('utilisateur créé', $intUser > 0, "id={$intUser}");

H::reset();
$arrRep = H::req('/admin.php', 'ploopi_login='.urlencode(WIKI_LOGIN).'&ploopi_password='.urlencode(WIKI_MDP));
T::vrai('connexion acceptée', $arrRep['code'] === 302 && stripos($arrRep['headers'], 'Set-Cookie') !== false, "HTTP {$arrRep['code']}");

$strEnv = H::env();
T::vrai('environnement obtenu (jeton compris)', substr_count($strEnv, '/') === 4, $strEnv);


/**
 * Entrer dans le module. acl::ismoduleallowed('wiki') — le seul garde-fou de op.php —
 * compare le module COURANT de la session au type demandé : tant que la session est
 * sur le module system, aucune opération du wiki n'est atteinte et les refus mesurés
 * ensuite ne prouveraient rien. L'environnement porte l'instance visée.
 *
 * @param string $strEnv environnement courant (mainmenu/espace/module/action/jeton)
 * @param int $intModule instance de module à activer
 * @return string environnement modifié
 */
function wiki_entrer($strEnv, $intModule)
{
    $arrEnv = explode('/', $strEnv);
    if (sizeof($arrEnv) === 5) $arrEnv[2] = (int)$intModule;

    $strEnv = implode('/', $arrEnv);
    H::req('/admin.php?ploopi_env='.urlencode($strEnv));

    return $strEnv;
}

/**
 * Appelle une opération du wiki avec le jeton de l'utilisateur courant.
 *
 * @param string $strOp opération
 * @param string $strQuery paramètres supplémentaires
 * @param mixed $mixPost corps POST éventuel
 * @return array réponse
 */
function wiki_appel($strOp, $strQuery = '', $mixPost = null)
{
    global $strEnv;

    return H::req("/index-quick.php?ploopi_op={$strOp}&ploopi_env=".urlencode($strEnv).$strQuery, $mixPost);
}

/**
 * Contrôle témoin : wiki_page_print ne demande aucun droit particulier. S'il répond,
 * c'est que le répartiteur atteint bien op.php pour cet utilisateur — sans quoi tous
 * les refus mesurés ensuite ne prouveraient rien.
 */
$strEnv = wiki_entrer($strEnv, $intModule);
$arrRep = wiki_appel('wiki_page_print', '&wiki_page_id=accueil');
T::contient('témoin : le module répond à cet utilisateur', 'Contenu initial', $arrRep['body']);

/**
 * Chaque section repart du même état : une opération qui aboutit indûment ne doit pas
 * fausser la mesure de la suivante.
 */
$funcReinitialiser = function() use ($intModule) {
    db()->query("DELETE FROM ploopi_mod_wiki_page");
    wiki_page_creer('accueil', 'Contenu initial de la page.', 0, $intModule);
    wiki_page_creer('protegee', 'Page verrouillée.', 1, $intModule);
};

T::section('wiki_page_save sans le droit de modifier');
$funcReinitialiser();
/**
 * Marqueur sans accent, et comparaison au contenu d'origine plutôt que recherche du
 * marqueur : le contenu passe par iso8859_clean() puis htmlpurifier() à
 * l'enregistrement, un caractère accentué ne se retrouve donc pas tel quel en base et
 * un strpos() dessus conclurait à tort que rien n'a changé.
 */
wiki_appel('wiki_page_save', '&wiki_page_id=accueil', 'fck_wiki_page_content='.urlencode('CONTENU INJECTE SANS DROIT'));
$arrPage = wiki_page_lire('accueil');
T::eq('le contenu de la page est inchangé', 'Contenu initial de la page.', $arrPage['content'] ?? null);

T::section('wiki_page_save sur une page verrouillée');
$funcReinitialiser();
wiki_appel('wiki_page_save', '&wiki_page_id=protegee', 'fck_wiki_page_content='.urlencode('CONTENU ECRASE MALGRE LE VERROU'));
$arrPage = wiki_page_lire('protegee');
T::eq('une page verrouillée n\'est pas réécrite', 'Page verrouillée.', $arrPage['content'] ?? null);

T::section('wiki_page_delete sans le droit de supprimer');
$funcReinitialiser();
wiki_appel('wiki_page_delete', '&wiki_page_id=accueil');
T::vrai('la page existe toujours', wiki_page_lire('accueil') !== null);

T::section('wiki_page_rename sans le droit de renommer');
$funcReinitialiser();
wiki_appel('wiki_page_rename', '&wiki_page_id=accueil', 'wiki_page_newid=accueil_renomme');
T::vrai('la page conserve son identifiant', wiki_page_lire('accueil') !== null);
T::vrai('aucune page n\'a été créée sous le nouveau nom', wiki_page_lire('accueil_renomme') === null);

T::section('wiki_page_lock / unlock sans le droit');
$funcReinitialiser();
wiki_appel('wiki_page_unlock', '&wiki_page_id=protegee');
$arrPage = wiki_page_lire('protegee');
T::vrai('la page verrouillée le reste', $arrPage !== null && (int)$arrPage['locked'] === 1, 'locked='.($arrPage['locked'] ?? '?'));

/**
 * Contrôle témoin du déverrouillage : sans lui, le refus ci-dessus pourrait venir
 * d'un déverrouillage impossible plutôt que d'un contrôle de droits. C'était
 * précisément le cas avant le correctif de data_object::save() : lock(false)
 * écrivait « locked = '' », que le mode strict refuse.
 */
include_once PLOOPI_ROOT.'/modules/wiki/classes/class_wiki_page.php';
$_SESSION['ploopi'] = array('userid' => 2, 'workspaceid' => 2, 'moduleid' => $intModule);
$objVerrou = new wiki_page();
$objVerrou->open('protegee');
$objVerrou->lock(false);
unset($_SESSION['ploopi']);
$arrPage = wiki_page_lire('protegee');
T::vrai('témoin : le déverrouillage fonctionne par ailleurs', $arrPage !== null && (int)$arrPage['locked'] === 0, 'locked='.($arrPage['locked'] ?? '?'));

wiki_appel('wiki_page_lock', '&wiki_page_id=accueil');
$arrPage = wiki_page_lire('accueil');
T::vrai('une page libre n\'est pas verrouillée', $arrPage !== null && (int)$arrPage['locked'] === 0, 'locked='.($arrPage['locked'] ?? '?'));

T::section('Contrôle témoin : l\'administrateur, lui, peut agir');
$funcReinitialiser();
/**
 * Sans cette section, les refus ci-dessus pourraient venir d'opérations inopérantes
 * plutôt que d'un contrôle de droits.
 */
H::reset();
H::connexion();
H::req('/admin.php?reloadsession=1');
$strEnv = wiki_entrer(H::env(), $intModule);

wiki_appel('wiki_page_save', '&wiki_page_id=accueil', 'fck_wiki_page_content='.urlencode('CONTENU MODIFIE PAR ADMIN'));
$arrPage = wiki_page_lire('accueil');
T::contient('l\'administrateur modifie la page', 'CONTENU MODIFIE PAR ADMIN', $arrPage['content'] ?? '');

wiki_appel('wiki_page_delete', '&wiki_page_id=accueil');
T::vrai('l\'administrateur supprime la page', wiki_page_lire('accueil') === null);

exit(T::bilan('16_module_wiki'));
