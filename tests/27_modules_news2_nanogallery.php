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
 * Suite 27 - modules news2 et nanogallery : cloisonnement des instances.
 *
 * Ces deux modules suivent l'architecture contrôleur/actions. Leurs operations op_*
 * controlent bien les actions declarees, mais ouvrent l'enregistrement vise par son seul
 * identifiant : un gestionnaire d'une instance agissait sur une autre. La fiche publique
 * d'une news ne verifiait ni sa publication ni son instance.
 *
 * Les op_* sont appelees par admin-light.php, ou _header.php n'est pas inclus : chaque
 * action doit se proteger elle-meme.
 *
 * @package news2
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

define('MOD_LOGIN', 'modernes_lecteur');
define('MOD_LOGIN_GEST', 'modernes_gestionnaire');
define('MOD_MDP', 'M0dernes!Test2026');
define('NW_A', 7701);          // news publiee de l'instance A
define('NW_A_BROUILLON', 7702); // news non publiee de l'instance A
define('NW_B', 7703);          // news publiee de l'instance B
define('NG_A', 7711);          // galerie de l'instance A
define('NG_B', 7712);          // galerie de l'instance B

$arrDeploye = array();

/**
 * Retire les modules, leurs donnees, les roles et les utilisateurs d'essai.
 */
function mod_nettoyer()
{
    global $arrDeploye;

    $strUsers = "SELECT id FROM ploopi_user WHERE login IN ('".MOD_LOGIN."', '".MOD_LOGIN_GEST."')";
    $strRoles = "SELECT id FROM ploopi_role WHERE label = 'modernes_tests'";
    $arrRequetes = array(
        "DELETE FROM ploopi_role_action WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_workspace_user_role WHERE id_role IN ({$strRoles})",
        "DELETE FROM ploopi_role WHERE label = 'modernes_tests'",
        "DELETE FROM ploopi_workspace_user WHERE id_user IN ({$strUsers})",
        "DELETE FROM ploopi_user WHERE login IN ('".MOD_LOGIN."', '".MOD_LOGIN_GEST."')",
        "DROP TABLE IF EXISTS ploopi_mod_news2_cat, ploopi_mod_news2_entry, ploopi_mod_nanogallery, ploopi_mod_nanogallery_img",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label LIKE 'news2%' OR label LIKE 'nanogallery%')",
        "DELETE FROM ploopi_module WHERE label LIKE 'news2%' OR label LIKE 'nanogallery%'",
        "DELETE FROM ploopi_module_type WHERE label IN ('news2', 'nanogallery')"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    foreach ($arrDeploye as $strDossier) if (is_dir($strDossier)) exec('rm -rf '.escapeshellarg($strDossier));
}

register_shutdown_function('mod_nettoyer');
mod_nettoyer();

T::section('Deploiement des modules');

/**
 * Deploie un module, cree deux instances dans l'espace 2.
 *
 * @param string $strModule nom du module
 * @param string $strVersion version declaree
 * @return array [type, instance A, instance B]
 */
function mod_deployer($strModule, $strVersion)
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

    $arrInstances = array();
    foreach (array($strModule, "{$strModule}_autre") as $strLabel)
    {
        db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible) VALUES ('{$strLabel}', {$intType}, 2, 1, 1)");
        $intModule = db()->insert_id;
        db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition) VALUES ({$intModule}, 2, 1, '')");
        $arrInstances[] = $intModule;
    }

    return array($intType, $arrInstances[0], $arrInstances[1]);
}

list($intTypeNews, $intNewsA, $intNewsB) = mod_deployer('news2', '3.3.2');
list($intTypeGal, $intGalA, $intGalB) = mod_deployer('nanogallery', '1.6.2');
T::vrai('news2 et nanogallery deployes, deux instances chacun', $intNewsB > 0 && $intGalB > 0);

/**
 * @param string $strLogin identifiant
 * @return int utilisateur sans role, rattache a l'espace 2
 */
function mod_utilisateur($strLogin)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_user (login, password, firstname, lastname, email, date_creation, disabled)
         VALUES ('%s', '%s', 'Essai', 'Modernes', 'modernes@example.test', '%s', 0)",
        $strLogin, db()->real_escape_string(ploopi\user::generate_hash(MOD_MDP, $strLogin)), date('YmdHis')
    ));
    $intUser = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

    return $intUser;
}

mod_utilisateur(MOD_LOGIN);
$intGest = mod_utilisateur(MOD_LOGIN_GEST);

// Le gestionnaire detient toutes les actions, sur les instances A seulement
foreach (array(array($intNewsA, $intTypeNews, array(1, 2, 3, 4, 5)), array($intGalA, $intTypeGal, array(10, 20, 30, 40))) as $arrRole)
{
    db()->query("INSERT INTO ploopi_role (id_module, id_workspace, label, description) VALUES ({$arrRole[0]}, 2, 'modernes_tests', '')");
    $intRole = db()->insert_id;
    foreach ($arrRole[2] as $intAction) db()->query("INSERT INTO ploopi_role_action (id_role, id_action, id_module_type) VALUES ({$intRole}, {$intAction}, {$arrRole[1]})");
    db()->query("INSERT INTO ploopi_workspace_user_role (id_user, id_workspace, id_role) VALUES ({$intGest}, 2, {$intRole})");
}

/**
 * Remet les news et galeries d'essai.
 */
function mod_reinitialiser()
{
    global $intNewsA, $intNewsB, $intGalA, $intGalB;

    db()->query('DELETE FROM ploopi_mod_news2_entry');
    foreach (array(array(NW_A, 'NEWSPUBLIEEA', 1, $intNewsA), array(NW_A_BROUILLON, 'NEWSBROUILLONA', 0, $intNewsA), array(NW_B, 'NEWSPUBLIEEB', 1, $intNewsB)) as $arrN)
    {
        db()->query("INSERT INTO ploopi_mod_news2_entry (id, id_cat, title, resume, content, published, id_module, id_user, date_publish, id_workspace)
                     VALUES ({$arrN[0]}, 0, '{$arrN[1]}', '', '<p>{$arrN[1]}</p>', {$arrN[2]}, {$arrN[3]}, 2, '".date('YmdHis')."', 2)");
    }

    db()->query('DELETE FROM ploopi_mod_nanogallery');
    foreach (array(array(NG_A, 'GALERIEA', $intGalA), array(NG_B, 'GALERIEB', $intGalB)) as $arrG)
    {
        db()->query("INSERT INTO ploopi_mod_nanogallery (id, id_user, id_workspace, id_module, label) VALUES ({$arrG[0]}, 2, 2, {$arrG[2]}, '{$arrG[1]}')");
    }
}

/**
 * @param string $strTable table
 * @param int $intId identifiant
 * @return boolean l'enregistrement existe
 */
function mod_existe($strTable, $intId)
{
    return (int)db()->query("SELECT COUNT(*) FROM {$strTable} WHERE id = ".(int)$intId)->fetch_row()[0] === 1;
}

/**
 * Se connecte et entre dans une instance (cf. suite 18).
 *
 * @param string $strLogin identifiant
 * @param int $intModule instance
 * @return string environnement
 */
function mod_connecter($strLogin, $intModule)
{
    H::reset();
    H::req('/admin.php', 'ploopi_login='.urlencode($strLogin).'&ploopi_password='.urlencode(MOD_MDP));

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
 * Appelle une action du controleur.
 *
 * @param string $strEnv environnement
 * @param string $strScript admin ou admin-light
 * @param string $strQuery entity, action et parametres
 * @param mixed $mixPost corps POST
 * @return array reponse
 */
function mod_action($strEnv, $strScript, $strQuery, $mixPost = null)
{
    return H::req("/{$strScript}.php?{$strQuery}&ploopi_env=".urlencode($strEnv), $mixPost);
}

T::section('news2 : fiche publique');
/**
 * Le titre d'une news publiée figure aussi dans le bloc de menu de son instance, affiché
 * dans toutes les pages de l'espace : on cherche son contenu, que seule la fiche rend.
 */
mod_reinitialiser();
$strEnv = mod_connecter(MOD_LOGIN, $intNewsA);
$arrRep = mod_action($strEnv, 'admin', 'entity=public&action=one&id='.NW_A);
T::contient('temoin : un utilisateur lit une news publiee de son instance', '<p>NEWSPUBLIEEA</p>', $arrRep['body']);

$arrRep = mod_action($strEnv, 'admin', 'entity=public&action=one&id='.NW_A_BROUILLON);
T::contientpas('pas une news non publiee', '<p>NEWSBROUILLONA</p>', $arrRep['body']);

$arrRep = mod_action($strEnv, 'admin', 'entity=public&action=one&id='.NW_B);
T::contientpas('ni une news d\'une autre instance', '<p>NEWSPUBLIEEB</p>', $arrRep['body']);

T::section('news2 : suppression et publication');
mod_reinitialiser();
$strEnv = mod_connecter(MOD_LOGIN_GEST, $intNewsA);
mod_action($strEnv, 'admin-light', 'entity=admin&action=op_delete&id='.NW_B);
T::vrai('le gestionnaire de A ne supprime pas une news de B', mod_existe('ploopi_mod_news2_entry', NW_B));

mod_action($strEnv, 'admin-light', 'entity=admin&action=op_delete&id='.NW_A);
T::vrai('temoin : il supprime une news de son instance', !mod_existe('ploopi_mod_news2_entry', NW_A));

mod_reinitialiser();
db()->query('UPDATE ploopi_mod_news2_entry SET published = 0 WHERE id = '.NW_B);
mod_action($strEnv, 'admin-light', 'entity=admin&action=op_publish&id='.NW_B);
T::eq('ni ne publie une news de B', '0', db()->query('SELECT published FROM ploopi_mod_news2_entry WHERE id = '.NW_B)->fetch_row()[0]);

mod_action($strEnv, 'admin-light', 'entity=admin&action=op_publish&id='.NW_A_BROUILLON);
T::eq('temoin : il publie une news de son instance', '1', db()->query('SELECT published FROM ploopi_mod_news2_entry WHERE id = '.NW_A_BROUILLON)->fetch_row()[0]);

T::section('news2 : liste des news');
/**
 * La liste échappait le contenu HTML (balises affichées en clair) et rendait une image
 * sans source pour une news sans illustration. Le contenu, saisi dans l'éditeur et non
 * filtré à l'enregistrement, passe désormais par HTMLPurifier ; les liens aux schémas
 * exécutables sont écartés.
 */
mod_reinitialiser();
db()->query("UPDATE ploopi_mod_news2_entry SET content = '<p>NEWSPUBLIEEA</p><script>var temoin_news2 = 1;</script>', url = 'javascript:void(0)', urltitle = 'LIENNEWS', background = '' WHERE id = ".NW_A);
$strEnv = mod_connecter(MOD_LOGIN, $intNewsA);
foreach (array('liste' => 'entity=public&action=all', 'fiche' => 'entity=public&action=one&id='.NW_A) as $strVue => $strQuery)
{
    $arrRep = mod_action($strEnv, 'admin', $strQuery);
    T::contient("témoin ({$strVue}) : le contenu de la news est rendu en HTML", '<p>NEWSPUBLIEEA</p>', $arrRep['body']);
    T::contientpas("({$strVue}) son balisage n'est pas affiché en clair", '&lt;p&gt;NEWSPUBLIEEA', $arrRep['body']);
    T::contientpas("({$strVue}) un script du contenu est retiré", 'temoin_news2', $arrRep['body']);
    T::contientpas("({$strVue}) pas d'image sans source", '<img src="./"', $arrRep['body']);
    T::contientpas("({$strVue}) un lien javascript: est écarté", 'javascript:void(0)"', $arrRep['body']);
}

T::section('news2 : arbre de redirection vers une page du site');
/**
 * Le titre d'une rubrique ou d'un article de webedit était passé à la fonction de choix
 * par addslashes, dans un attribut onclick que l'arbre écrit tel quel : un guillemet
 * refermait l'attribut. Le choix passe désormais par data-ploopi-action et ses arguments
 * (data-ploopi-args), que l'arbre encode en JSON et échappe pour l'attribut.
 */
if (!is_file(PLOOPI_ROOT.'/modules/webedit/class_heading.php')) T::skip('titres transmis par l\'arbre', 'module webedit non déployé');
else
{
    chdir(PLOOPI_ROOT);
    $arrArbre = ploopi\news2\tools::news2_gettreeview(
        array('list' => array(5 => array('id' => 5, 'id_heading' => 0, 'parents' => '0', 'label' => 'Rub "x" <i>', 'description' => ''))),
        array('list' => array(9 => array('id' => 9, 'id_heading' => 5, 'title' => 'Art "y"', 'status' => '', 'date_ok' => 1, 'metadescription' => '', 'new_version' => 0)))
    );
    $arrRubrique = $arrArbre['list']['hr5']['attrs'] ?? array();
    $arrArticle = $arrArbre['list']['ar9']['attrs'] ?? array();
    T::vrai('rubrique : aucun gestionnaire en ligne', empty($arrArbre['list']['hr5']['onclick']));
    T::eq('rubrique : choix par l\'action news2_select_article_or_heading', 'news2_select_article_or_heading', $arrRubrique['data-ploopi-action'] ?? '');
    T::eq('rubrique : titre transmis tel quel', array('articles/rub-x-i-h5.html', 'Rub "x" <i>'), $arrRubrique['data-ploopi-args'] ?? null);
    T::eq('article : titre transmis tel quel', 'Art "y"', $arrArticle['data-ploopi-args'][1] ?? null);
    $strAttrs = ploopi\skin::treeview_attrs($arrRubrique);
    T::vrai('rubrique : attribut rendu sans guillemet double brut', strpos(preg_replace('/="[^"]*"/', '', $strAttrs), '"') === false, $strAttrs);
}

T::section('nanogallery : sans le module spcolor');
/**
 * spcolor (sélecteur de couleurs) n'est pas livré avec le dépôt : le module ne
 * présentait que « Le module système spcolor doit être installé ». Il est facultatif.
 */
mod_reinitialiser();
$strEnv = mod_connecter(MOD_LOGIN_GEST, $intGalA);
$arrRep = mod_action($strEnv, 'admin', 'entity=home');
T::contientpas('l\'accueil du module ne réclame plus spcolor', 'spcolor', $arrRep['body']);
$arrRep = mod_action($strEnv, 'admin', 'entity=public&action=list');
T::contient('la liste des galeries est servie', 'GALERIEA', $arrRep['body']);

/**
 * L'onglet « Liste des galeries photo » pointait vers une entité inexistante
 * (entity=publi) : « Une erreur est survenue ».
 */
preg_match('/<a [^>]*href="(admin\.php\?ploopi_url=[^"]+)"[^>]*>(?:\s*<[^>]+>)*\s*Liste des galeries photo/', $arrRep['body'], $arrOnglet);
T::vrai('onglet « Liste des galeries photo » présent', !empty($arrOnglet[1]));
$arrRep = H::req('/'.html_entity_decode($arrOnglet[1] ?? ''));
T::contient('l\'onglet ramène à la liste des galeries', 'GALERIEA', $arrRep['body']);
T::contientpas('l\'onglet ne mène pas à la page d\'erreur', 'Une erreur est survenue', $arrRep['body']);

/**
 * Les colonnes useAlbums et flatAlbums, lues et écrites par le module, n'étaient créées
 * ni par structure.sql ni par une mise à jour : l'enregistrement échouait.
 */
mod_action($strEnv, 'admin-light', 'entity=admin&action=op_save&tab=gallery', 'nano_label=GALERIENOUVELLE&nano_useAlbums=1&nano_flatAlbums=0&nano_id_folder=0');
T::eq('une galerie s\'enregistre (colonnes des albums)', '1', (string)db()->query("SELECT useAlbums FROM ploopi_mod_nanogallery WHERE label = 'GALERIENOUVELLE'")->fetch_row()[0]);

/**
 * Le backoffice ne publie plus de variable globale $template_body : l'affichage d'une
 * galerie s'arrêtait en erreur fatale.
 */
$arrRep = mod_action($strEnv, 'admin', 'entity=public&action=display&id='.NG_A);
T::contient('une galerie s\'affiche au backoffice', 'nanogallery_'.NG_A, $arrRep['body']);
T::contientpas('sans erreur', 'Une erreur est survenue', $arrRep['body']);

T::section('nanogallery : suppression');
mod_reinitialiser();
$strEnv = mod_connecter(MOD_LOGIN_GEST, $intGalA);
mod_action($strEnv, 'admin-light', 'entity=admin&action=op_delete&id='.NG_B);
T::vrai('le gestionnaire de A ne supprime pas une galerie de B', mod_existe('ploopi_mod_nanogallery', NG_B));

mod_action($strEnv, 'admin-light', 'entity=admin&action=op_delete&id='.NG_A);
T::vrai('temoin : il supprime une galerie de son instance', !mod_existe('ploopi_mod_nanogallery', NG_A));

T::section('nanogallery : titres et descriptions des images');
/**
 * op_saveimg enregistrait le titre et la description de tout identifiant posté, quelle
 * que soit sa galerie. Une image par galerie, dans le dossier de la GED de chacune ;
 * la table des fichiers de la GED est créée si le module doc n'est pas installé.
 */
define('NG_IMG_A', str_repeat('a', 32));
define('NG_IMG_B', str_repeat('b', 32));

$booTableDoc = (bool)db()->query("SHOW TABLES LIKE 'ploopi_mod_doc_file'")->num_rows;
if (!$booTableDoc)
{
    $strSql = file_get_contents(PLOOPI_ROOT.'/install/doc/structure.sql');
    preg_match('/CREATE TABLE IF NOT EXISTS `ploopi_mod_doc_file` \(.*?;/s', $strSql, $arrMatches);
    db()->query($arrMatches[0]);
}
register_shutdown_function(function() use ($booTableDoc) {
    try {
        if ($booTableDoc) db()->query("DELETE FROM ploopi_mod_doc_file WHERE md5id IN ('".NG_IMG_A."', '".NG_IMG_B."')");
        else db()->query('DROP TABLE IF EXISTS ploopi_mod_doc_file');
    } catch (Throwable $objErreur) { }
});

mod_reinitialiser();
db()->query('UPDATE ploopi_mod_nanogallery SET id_folder = 88801 WHERE id = '.NG_A);
db()->query('UPDATE ploopi_mod_nanogallery SET id_folder = 88802 WHERE id = '.NG_B);
db()->query("DELETE FROM ploopi_mod_doc_file WHERE md5id IN ('".NG_IMG_A."', '".NG_IMG_B."')");
foreach (array(array(NG_IMG_A, 88801), array(NG_IMG_B, 88802)) as $arrImg)
{
    db()->query("INSERT INTO ploopi_mod_doc_file (md5id, name, extension, content, metadata, words_overall, words_indexed, id_folder)
                 VALUES ('{$arrImg[0]}', 'photo.jpg', 'jpg', '', '', 0, 0, {$arrImg[1]})");
}
db()->query('DELETE FROM ploopi_mod_nanogallery_img');

$strEnv = mod_connecter(MOD_LOGIN_GEST, $intGalA);
mod_action($strEnv, 'admin-light', 'entity=admin&action=op_saveimg&id='.NG_A,
    'title_'.NG_IMG_A.'=TITREA&desc_'.NG_IMG_A.'=DESCA&title_'.NG_IMG_B.'=TITREINTRUS&desc_'.NG_IMG_B.'=DESCINTRUS');

$arrTitres = array();
$objRs = db()->query('SELECT id, title FROM ploopi_mod_nanogallery_img');
while ($arrLigne = $objRs->fetch_assoc()) $arrTitres[$arrLigne['id']] = $arrLigne['title'];
T::eq('temoin : le titre d\'une image de la galerie est enregistre', 'TITREA', isset($arrTitres[NG_IMG_A]) ? $arrTitres[NG_IMG_A] : null);
T::vrai('pas celui d\'une image d\'une autre galerie', !isset($arrTitres[NG_IMG_B]), print_r($arrTitres, true));

exit(T::bilan('27_modules_news2_nanogallery'));
