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
 * Suite 01 — parcours HTTP réels sur une instance en fonctionnement.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
require __DIR__.'/fixtures.php';

exiger_instance();
$arrDocs = fixtures_documents();
H::reset();

$arrRep = H::connexion();

T::section('Cookie de session et entêtes HTTP');
T::chk('cookie HttpOnly',                           stripos($arrRep['headers'], 'HttpOnly') !== false);
T::chk('cookie SameSite=Lax',                       stripos($arrRep['headers'], 'SameSite=Lax') !== false);
T::chk('X-Content-Type-Options: nosniff',           stripos($arrRep['headers'], 'X-Content-Type-Options: nosniff') !== false);
T::chk('Referrer-Policy renseignée',                stripos($arrRep['headers'], 'Referrer-Policy: strict-origin-when-cross-origin') !== false);
$arrRep = H::req('/admin.php');
T::chk('connexion établie',                         stripos($arrRep['headers'], 'Ploopi-Connected: 1') !== false);

H::reset();
H::req('/admin.php');
$strSidAvant = H::sid();
H::connexion();
$strSidApres = H::sid();
T::chk('identifiant de session renouvelé à la connexion', $strSidAvant !== '' && $strSidApres !== '' && $strSidAvant !== $strSidApres);

$strEnv = H::env();
$strJeton = isset(explode('/', $strEnv)[4]) ? explode('/', $strEnv)[4] : '';

T::section('Secrets hors session, jeton imprévisible');
T::chk('jeton sur 32 caractères hexadécimaux',      (bool)preg_match('/^[0-9a-f]{32}$/', $strJeton), $strJeton);
$strFichierSession = chemin_session(H::sid());
if ($strFichierSession === false)
{
    T::skip('mot de passe absent du fichier de session', 'dossier de sessions introuvable, voir session_path');
    T::skip('champ password vide en session',            'dossier de sessions introuvable, voir session_path');
}
else
{
    $strSession = file_get_contents($strFichierSession);
    T::chk('mot de passe en clair absent de la session', strpos($strSession, cfg('admin_password')) === false);
    T::chk('champ password vide en session',             (bool)preg_match('/password[^;]*;s:0:""/', $strSession));
}
$arrRep = H::req('/admin.php');
T::chk('champ mot de passe non pré-rempli',         !preg_match('/id="ploopi_password"[^>]*value="[^"]+"/', $arrRep['body']));

T::section('Jeton anti-CSRF sur index-quick.php');
db()->query("DELETE FROM ploopi_user WHERE login = 'cible'");
db()->query("INSERT INTO ploopi_user (login, password, lastname, firstname, email, date_creation)
             VALUES ('cible', 'x', 'Cible', 'Cible', 'cible@example.test', '".date('YmdHis')."')");
$intCible = db()->insert_id;
$funcExiste = function() use ($intCible) {
    return (int)db()->query("SELECT COUNT(*) c FROM ploopi_user WHERE id = {$intCible}")->fetch_assoc()['c'] === 1;
};
T::chk('utilisateur cible créé',                    $funcExiste(), "id={$intCible}");
H::req("/index-quick.php?ploopi_op=system_delete_user&system_user_id={$intCible}");
T::chk('sans jeton : suppression refusée',          $funcExiste(), '(le scénario CSRF)');
H::req("/index-quick.php?ploopi_op=system_delete_user&system_user_id={$intCible}&ploopi_env=".urlencode('1/2/1/admin/deadbeef'));
T::chk('jeton invalide : suppression refusée',      $funcExiste());
H::req("/index-quick.php?ploopi_op=system_delete_user&system_user_id={$intCible}&ploopi_env=".urlencode($strEnv));
T::chk('jeton valide : suppression effectuée',      !$funcExiste(), '(le correctif reste ciblé)');

T::section('Contrôle d\'accès sur la GED');
$funcTelecharger = function($strMd5, $strExtra = '') use ($strEnv) {
    return H::req("/admin-light.php?ploopi_op=documents_downloadfile&documentsfile_id={$strMd5}{$strExtra}&ploopi_env=".urlencode($strEnv));
};
$arrRep = $funcTelecharger($arrDocs['etranger']['md5']);
T::chk('document d\'un espace non accessible : refusé', strpos($arrRep['body'], 'contenu etranger') === false, '(le scénario IDOR)');
$arrRep = $funcTelecharger($arrDocs['system']['md5']);
T::chk('document du module system : servi',         strpos($arrRep['body'], 'contenu system') !== false);
$arrRep = $funcTelecharger(str_repeat('0', 32));
T::chk('identifiant inexistant : refusé',           strpos($arrRep['body'], "Le fichier n'existe pas") !== false);

T::section('Rendu des fichiers déposés');
$arrRep = $funcTelecharger($arrDocs['html']['md5'], '&attachement=0');
T::chk('fichier HTML effectivement servi',          strpos($arrRep['body'], '<script>') !== false, '(sinon les contrôles suivants ne prouvent rien)');
T::chk('inline demandé, attachment imposé',         stripos($arrRep['headers'], 'Content-disposition: attachment') !== false);
T::chk('nom de fichier en RFC 5987',                stripos($arrRep['headers'], "filename*=UTF-8''xss.html") !== false);
T::chk('nosniff sur le téléchargement',             stripos($arrRep['headers'], 'X-Content-Type-Options: nosniff') !== false);
$arrRep = $funcTelecharger($arrDocs['image']['md5'], '&attachement=0');
T::chk('image PNG effectivement servie',            strpos($arrRep['body'], "\x89PNG") !== false);
T::chk('image en inline : reste en inline',         stripos($arrRep['headers'], 'Content-disposition: inline') !== false, '(pas de régression sur les aperçus)');

T::section('XSS stockée dans l\'annuaire');
$strCharge = 'a" onmouseover="alert(1)" x="@evil.test';
$strMailInitial = db()->query('SELECT email FROM ploopi_user WHERE id = 2')->fetch_assoc()['email'];
db()->query("UPDATE ploopi_user SET email = '".db()->real_escape_string($strCharge)."' WHERE id = 2");
$arrRep = H::req('/admin.php?system_level=system&sysToolbarItem=directory&ploopi_env='.urlencode($strEnv));
T::chk('page annuaire atteinte',                    $arrRep['code'] === 200, "HTTP {$arrRep['code']}");
T::chk('charge utile non réfléchie',                strpos($arrRep['body'], 'onmouseover="alert(1)"') === false);
T::chk('adresse non conforme : aucun lien mailto',  strpos($arrRep['body'], 'mailto:a"') === false);
db()->query("UPDATE ploopi_user SET email = '".db()->real_escape_string($strMailInitial)."' WHERE id = 2");

T::section('Redirections sortantes');
H::reset();
H::connexion();
$arrRep = H::req('/admin.php?ploopi_logout=1', null, array('Referer: http://evil.test/piege'));
preg_match('/^Location:\s*(.+)$/mi', $arrRep['headers'], $arrMatches);
$strLocation = isset($arrMatches[1]) ? trim($arrMatches[1]) : '';
T::chk('déconnexion : Referer externe ignoré',      strpos($strLocation, 'evil.test') === false, $strLocation);
T::chk('déconnexion : retour sur le site',          $strLocation !== '' && strpos($strLocation, parse_url(cfg('base_url'), PHP_URL_HOST)) !== false);

/**
 * requiretoken() redirige vers _PLOOPI_BASEPATH.'/admin.php', donc une URL absolue interne.
 * Si isinternalurl() la prenait pour externe, la destination serait remplacée par la racine
 * et toutes les redirections du backoffice seraient dégradées.
 */
H::reset();
H::connexion();
$arrRep = H::req('/admin.php?zz=1');
T::chk('jeton absent : requête interrompue',        strpos($arrRep['body'], 'Jeton absent') !== false);
// requiretoken() redirige avec un délai, donc par un entête Refresh et non Location
preg_match('/^Refresh:\s*\d+;\s*url=(.*)$/mi', $arrRep['headers'], $arrMatches);
$strRedir = isset($arrMatches[1]) ? trim($arrMatches[1]) : '';
T::chk('jeton absent : destination renseignée',     $strRedir !== '', $strRedir);
T::chk('jeton absent : redirection interne conservée', strpos($strRedir, 'admin.php') !== false,
    '(sinon isinternalurl aurait pris BASEPATH pour une URL externe)');

fixtures_nettoyer();
/**
 * Empreintes bcrypt de l'ancienne forme (« clé/identifiant/mot de passe »), que bcrypt
 * tronquait à 72 octets : reconnues à la connexion, puis recalculées sous la nouvelle
 * forme. Une empreinte de l'ancienne forme qui ne contient plus le mot de passe est
 * refusée : avant correctif, n'importe quel mot de passe y ouvrait la session.
 */
T::section('Empreintes de mots de passe de l\'ancienne forme');

amorcer_socle();
$strCle = defined('_PLOOPI_SECRETKEY') ? _PLOOPI_SECRETKEY : '';
// identifiant juste assez long pour que l'ancienne empreinte ne contienne plus le
// mot de passe (préfixe « clé/identifiant/ » de 72 octets), dans la limite de la
// colonne login (32) : 27 caractères avec une clé de 43
$intLongueur = max(1, 71 - strlen($strCle));
$strLoginLong = str_repeat('l', $intLongueur);
db()->query("DELETE FROM ploopi_user WHERE login IN ('ancienne_forme', '{$strLoginLong}')");
db()->query("INSERT INTO ploopi_user (login, password, lastname, firstname, email, date_creation)
             VALUES ('ancienne_forme', '".db()->real_escape_string(password_hash("{$strCle}/ancienne_forme/Ancienne!2026", PASSWORD_BCRYPT))."', 'Ancienne', 'Forme', 'af@example.test', '".date('YmdHis')."')");
$intUser = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

H::reset();
H::req('/admin.php', 'ploopi_login=ancienne_forme&ploopi_password='.urlencode('Ancienne!2026'));
$arrRep = H::req('/admin.php');
T::chk('ancienne forme : connexion acceptée', stripos($arrRep['headers'], 'Ploopi-Connected: 1') !== false);
$strHash = db()->query("SELECT password FROM ploopi_user WHERE id = {$intUser}")->fetch_assoc()['password'];
T::chk('ancienne forme : empreinte recalculée à la connexion', !password_verify("{$strCle}/ancienne_forme/Ancienne!2026", $strHash) && strpos($strHash, '$2y$') === 0);

H::reset();
H::req('/admin.php', 'ploopi_login=ancienne_forme&ploopi_password='.urlencode('Ancienne!2026'));
$arrRep = H::req('/admin.php');
T::chk('nouvelle forme : connexion acceptée', stripos($arrRep['headers'], 'Ploopi-Connected: 1') !== false);

$intUserLong = 0;
if ($intLongueur > 32)
{
    T::skip('identifiant long : un mot de passe quelconque est refusé', "clé de ".strlen($strCle)." caractères : aucun identifiant de 32 caractères n'atteint 72 octets");
}
else
{
    db()->query("INSERT INTO ploopi_user (login, password, lastname, firstname, email, date_creation)
                 VALUES ('{$strLoginLong}', '".db()->real_escape_string(password_hash("{$strCle}/{$strLoginLong}/Correct!2026", PASSWORD_BCRYPT))."', 'Long', 'Login', 'll@example.test', '".date('YmdHis')."')");
    $intUserLong = db()->insert_id;
    db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUserLong}, 2, 1, 10)");
    H::reset();
    H::req('/admin.php', "ploopi_login={$strLoginLong}&ploopi_password=nimportequoi");
    $arrRep = H::req('/admin.php');
    T::chk('identifiant long : un mot de passe quelconque est refusé', stripos($arrRep['headers'], 'Ploopi-Connected: 1') === false);
}

db()->query("DELETE FROM ploopi_workspace_user WHERE id_user IN ({$intUser}, {$intUserLong})");
db()->query("DELETE FROM ploopi_user WHERE id IN ({$intUser}, {$intUserLong})");
H::reset();

/**
 * Exemples, tests et documentation des bibliothèques (vendor/) : jamais utiles en
 * production, parfois vulnérables (ceux de CKEditor 4, retiré depuis, portaient une XSS,
 * CVE-2024-24816). .htaccess_modele les interdit ; le routeur de test reproduit la règle.
 */
T::section('Exemples des bibliothèques non servis');

$strModele = file_get_contents(PLOOPI_ROOT.'/.htaccess_modele');
T::chk('.htaccess_modele : exemples de vendor/ interdits', strpos($strModele, 'RewriteRule ^vendor/.+/(samples|examples|tests?|docs?)/ - [F]') !== false);
// Un fichier réel d'un tel dossier, quelle que soit la dépendance qui en fournit un
$arrExemples = array_filter((array)glob(PLOOPI_ROOT.'/vendor/*/*/{samples,examples,tests,test,docs,doc}/*.*', GLOB_BRACE), 'is_file');
if (empty($arrExemples)) T::skip('fichier d\'exemple d\'une bibliothèque : refusé', 'aucun dossier d\'exemples dans vendor/');
else
{
    $strExemple = substr(reset($arrExemples), strlen(PLOOPI_ROOT));
    $arrRep = H::req($strExemple);
    T::chk("fichier d'exemple d'une bibliothèque : refusé ({$strExemple})", $arrRep['code'] === 403, "HTTP {$arrRep['code']}");
}
$arrRep = H::req('/vendor/components/jquery/jquery.min.js');
T::chk('témoin : bibliothèque elle-même servie', $arrRep['code'] === 200, "HTTP {$arrRep['code']}");

exit(T::bilan());
