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
 * Suite 02 — contrôles unitaires sur les classes réelles du socle, chargées en CLI.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();

use ploopi\cipher;
use ploopi\crypt;
use ploopi\security;
use ploopi\user;
use ploopi\fs;
use ploopi\output;
use ploopi\inputfilter;
use ploopi\documents;
use ploopi\documentsfile;

T::section('Chiffrement des URL');
$objCipher = new cipher();
$strMsg = 'entity=admin&action=op_delete&id=42';
$strA = $objCipher->crypt($strMsg);
$strB = $objCipher->crypt($strMsg);
T::chk('aller-retour',                              $objCipher->decrypt($strA) === $strMsg);
T::chk('deux chiffrements du même message diffèrent', $strA !== $strB, '(IV aléatoire)');
T::chk('les deux se déchiffrent',                   $objCipher->decrypt($strB) === $strMsg);
$strX = crypt::base64_decode($objCipher->crypt('entity=admin&action=a'));
$strY = crypt::base64_decode($objCipher->crypt('entity=admin&action=b'));
$intCommun = 0;
while ($intCommun < min(strlen($strX), strlen($strY)) && $strX[$intCommun] === $strY[$intCommun]) $intCommun++;
T::chk('préfixe binaire commun : aucun octet',      $intCommun === 0, "{$intCommun} octet(s)");
T::chk('IV altéré : rejeté',                        $objCipher->decrypt(crypt::base64_encode(substr($strX, 0, 4).chr(ord($strX[4]) ^ 1).substr($strX, 5))) === false);
$intDernier = strlen($strX) - 1;
T::chk('cryptogramme altéré : rejeté',              $objCipher->decrypt(crypt::base64_encode(substr($strX, 0, $intDernier).chr(ord($strX[$intDernier]) ^ 1))) === false);
T::chk('message vide : rejeté',                     $objCipher->decrypt('') === false);
T::chk('message tronqué : rejeté',                  $objCipher->decrypt(crypt::base64_encode('court')) === false);
$strIvFixe = substr(_PLOOPI_CIPHER_IV, 0, openssl_cipher_iv_length(_PLOOPI_CIPHER));
$strRaw = openssl_encrypt($strMsg, _PLOOPI_CIPHER, _PLOOPI_SECRETKEY, OPENSSL_RAW_DATA, $strIvFixe);
$strLegacy = crypt::base64_encode($strIvFixe.hash_hmac('sha256', $strRaw, _PLOOPI_SECRETKEY, true).$strRaw);
// Format précédent : IV non authentifié, donc premier bloc altérable — refusé
T::chk('URL au format précédent : refusée',         $objCipher->decrypt($strLegacy) === false);
$strLegacyAltere = crypt::base64_encode(($strIvFixe ^ str_repeat(chr(1), strlen($strIvFixe))).hash_hmac('sha256', $strRaw, _PLOOPI_SECRETKEY, true).$strRaw);
T::chk('format précédent, IV altéré : refusé',      $objCipher->decrypt($strLegacyAltere) === false);
$strUrl = crypt::urlencode('admin.php?entity=admin&action=write&id=7');
parse_str(parse_url($strUrl, PHP_URL_QUERY), $arrQuery);
parse_str(cipher::singleton()->decrypt($arrQuery['ploopi_url']), $arrParams);
T::chk('URL complète : aller-retour',               ($arrParams['entity'] ?? '') === 'admin' && ($arrParams['id'] ?? '') === '7');

T::section('Désérialisation : aucun objet ne doit sortir');
/**
 * Classe témoin : si la désérialisation l'instancie, son __wakeup laisse une trace.
 * C'est le mécanisme exact d'une injection d'objet PHP.
 */
class TemoinGadget { public $charge = 'x'; public function __wakeup() { $GLOBALS['gadget_execute'] = true; } }

$GLOBALS['gadget_execute'] = false;
$mixRes = crypt::unserialize(crypt::serialize(new TemoinGadget()));
T::chk('__wakeup du gadget non exécuté',            $GLOBALS['gadget_execute'] === false, '(injection d\'objet fermée)');
T::chk('aucune instance produite',                  !($mixRes instanceof TemoinGadget));
$GLOBALS['gadget_execute'] = false;
$arrRes = crypt::unserialize(crypt::serialize(array('liste' => array(1, 2), 'piege' => new TemoinGadget())));
T::chk('objet imbriqué : __wakeup non exécuté',     $GLOBALS['gadget_execute'] === false);
T::chk('objet imbriqué : aucune instance',          !($arrRes['piege'] instanceof TemoinGadget));
T::chk('le reste du tableau est intact',            $arrRes['liste'] === array(1, 2));
$arrOrigine = array('a' => 1, 'b' => array('x', 'y'), 'c' => 'texte accentué é');
T::chk('aller-retour d\'un tableau de scalaires',   crypt::unserialize(crypt::serialize($arrOrigine)) === $arrOrigine);
T::chk('chaîne illisible : pas de plantage',        in_array(crypt::unserialize('nimportequoi'), array(false, null), true));

// L'expression employée par les cookies de filtre des modules booking et planning
$funcCookie = function($strCookie) {
    return @unserialize(@gzuncompress(@base64_decode($strCookie), 1048576), array('allowed_classes' => false));
};
$GLOBALS['gadget_execute'] = false;
$arrRes = $funcCookie(base64_encode(gzcompress(serialize(array('piege' => new TemoinGadget())), 9)));
T::chk('cookie de module : __wakeup non exécuté',   $GLOBALS['gadget_execute'] === false);
T::chk('cookie de module : tableau rendu',          is_array($arrRes));
$arrLegitime = array('booking_resources' => array(3 => 3));
T::chk('cookie légitime : toujours lisible',        $funcCookie(base64_encode(gzcompress(serialize($arrLegitime), 9))) === $arrLegitime);
T::chk('cookie hors borne de décompression : refusé', $funcCookie(base64_encode(gzcompress(str_repeat('A', 8 * 1024 * 1024), 9))) === false,
    '(archive-bombe)');

T::section('Générateurs aléatoires');
T::chk('jeton sur 32 caractères hexadécimaux',      (bool)preg_match('/^[0-9a-f]{32}$/', security::generatetoken()));
T::chk('jeton de 16 caractères pour 8 octets',      strlen(security::generatetoken(8)) === 16);
T::chk('jeton : plancher à 8 octets',               strlen(security::generatetoken(1)) === 16);
$arrJetons = array();
for ($i = 0; $i < 2000; $i++) $arrJetons[security::generatetoken()] = 1;
T::chk('jeton : aucune collision sur 2000 tirages', count($arrJetons) === 2000);
$booLongueur = true; $booComposition = true; $arrMdp = array();
for ($i = 0; $i < 2000; $i++)
{
    $strMdp = security::generatepassword(12);
    $arrMdp[$strMdp] = 1;
    if (strlen($strMdp) !== 12) $booLongueur = false;
    if (!preg_match('/[a-z]/', $strMdp) || !preg_match('/[A-Z]/', $strMdp)
        || !preg_match('/[0-9]/', $strMdp) || !preg_match('/[:?!@#$%&*]/', $strMdp)) $booComposition = false;
}
T::chk('mot de passe : longueur respectée',         $booLongueur);
T::chk('mot de passe : composition garantie',       $booComposition);
T::chk('mot de passe : aucune collision sur 2000 tirages', count($arrMdp) === 2000);

T::section('Vérification des mots de passe');
$strHash = user::generate_hash('S3cret!', 'jean');
T::chk('bcrypt : bon mot de passe accepté',         user::password_verify('S3cret!', 'jean', $strHash) === true);
T::chk('bcrypt : mauvais mot de passe rejeté',      user::password_verify('autre', 'jean', $strHash) === false);
T::chk('bcrypt : mauvais login rejeté',             user::password_verify('S3cret!', 'paul', $strHash) === false);
T::chk('bcrypt : empreinte vide rejetée',           user::password_verify('S3cret!', 'jean', '') === false);
T::chk('algorithme retenu : bcrypt',                _PLOOPI_HASH_ALGO === 'bcrypt', _PLOOPI_HASH_ALGO);

/**
 * bcrypt ne lit que les 72 premiers octets de son entrée. L'empreinte portait sur
 * « clé secrète/identifiant/mot de passe » : avec la clé de 43 caractères des
 * installations récentes (PR #18), un identifiant de 27 caractères ou plus ne
 * laissait plus aucun caractère du mot de passe dans l'empreinte — n'importe quel
 * mot de passe était accepté. Les identifiants courts n'en gardaient que 16 à 22.
 */
$strLoginLong = str_repeat('l', 80 - strlen(_PLOOPI_SECRETKEY));
$strHash = user::generate_hash('Correct!2026', $strLoginLong);
T::chk('identifiant long : bon mot de passe accepté',       user::password_verify('Correct!2026', $strLoginLong, $strHash) === true);
T::chk('identifiant long : mauvais mot de passe rejeté',    user::password_verify('nimportequoi', $strLoginLong, $strHash) === false);
$strMdpLong = str_repeat('m', 70).'A';
$strHash = user::generate_hash($strMdpLong, 'jean');
T::chk('mot de passe long : le dernier caractère compte',   user::password_verify(str_repeat('m', 70).'B', 'jean', $strHash) === false);

/**
 * Empreintes de l'ancienne forme : reconnues tant que le mot de passe y figure assez
 * (8 caractères au moins, ou en entier), puis recalculées à la connexion. Au-delà,
 * elles ne prouvent rien et sont refusées.
 */
$strAncienne = password_hash(_PLOOPI_SECRETKEY.'/jean/S3cret!', PASSWORD_BCRYPT);
T::chk('ancienne forme : reconnue',                         user::password_verify('S3cret!', 'jean', $strAncienne) === true);
T::chk('ancienne forme : mauvais mot de passe rejeté',      user::password_verify('autre', 'jean', $strAncienne) === false);
T::chk('ancienne forme : à recalculer',                     user::password_needs_rehash('S3cret!', 'jean', $strAncienne) === true);
T::chk('nouvelle forme : rien à recalculer',                user::password_needs_rehash('S3cret!', 'jean', user::generate_hash('S3cret!', 'jean')) === false);
$strAncienne = password_hash(_PLOOPI_SECRETKEY."/{$strLoginLong}/Correct!2026", PASSWORD_BCRYPT);
T::chk('ancienne forme sans mot de passe utile : refusée',  user::password_verify('Correct!2026', $strLoginLong, $strAncienne) === false);

T::section('crypt::htpasswd');
/**
 * Régression surveillée.
 *
 * L'implémentation précédente appelait crypt(trim($pass), CRYPT_STD_DES) en passant
 * la CONSTANTE CRYPT_STD_DES — qui vaut 1 — comme SEL et non comme algorithme. Le sel
 * « 1 » étant invalide, crypt() retournait sa valeur d'échec « *0 » quel que soit le
 * mot de passe : tous les comptes écrits dans le .htpasswd par
 * system_generate_htpasswd() portaient la même empreinte, à laquelle aucun mot de
 * passe ne peut correspondre.
 */
$strEmpreinte = crypt::htpasswd('S3cret!');
T::neq('ce n\'est plus la valeur d\'échec de crypt()', '*0', $strEmpreinte);
T::vrai('format bcrypt, reconnu par Apache depuis la 2.4.4', strpos($strEmpreinte, '$2y$') === 0, $strEmpreinte);
T::chk('le bon mot de passe est reconnu',           password_verify('S3cret!', $strEmpreinte) === true);
T::chk('un autre mot de passe est rejeté',          password_verify('autre', $strEmpreinte) === false);
T::chk('deux empreintes du même mot de passe diffèrent', crypt::htpasswd('S3cret!') !== $strEmpreinte, '(sel aléatoire)');
T::chk('les deux se vérifient',                     password_verify('S3cret!', crypt::htpasswd('S3cret!')) === true);
T::eq('mot de passe vide : empreinte vide', '', crypt::htpasswd(''));
T::vrai(
    'le générateur de .htpasswd s\'appuie bien dessus',
    strpos(file_get_contents(PLOOPI_ROOT.'/modules/system/include/functions.php'), 'crypt::htpasswd($pass)') !== false,
    'system_generate_htpasswd() n\'appelle plus crypt::htpasswd() : revoir ce contrôle'
);

T::section('URL internes et externes');
// Cette section rejoue le contexte web à partir de l'URL de l'instance : sans
// tests/config.php, il n'y a pas d'hôte de référence à comparer.
if (!config_fournie()) T::skip('URL internes et externes', 'aucune instance configurée');
else {
// En CLI _PLOOPI_BASEPATH vaut '' : on renseigne HTTP_HOST, sur lequel la fonction se rabat,
// pour rejouer le contexte web — seul contexte où une redirection a un sens.
$strHote = parse_url(cfg('base_url'), PHP_URL_HOST).(parse_url(cfg('base_url'), PHP_URL_PORT) ? ':'.parse_url(cfg('base_url'), PHP_URL_PORT) : '');
$_SERVER['HTTP_HOST'] = $strHote;
foreach (array('', 'admin.php', '/admin.php', 'admin.php?x=1#a', 'http://'.$strHote.'/admin.php', 'https://'.$strHote.'/x') as $strU)
    T::chk('interne : '.var_export($strU, true),    output::isinternalurl($strU) === true);
unset($_SERVER['HTTP_HOST']);
T::chk('hôte inconnu : URL absolue refusée',        output::isinternalurl('http://'.$strHote.'/x') === false, '(échec fermé)');
T::chk('hôte inconnu : URL relative acceptée',      output::isinternalurl('admin.php') === true);
$_SERVER['HTTP_HOST'] = $strHote;
foreach (array('http://evil.test/', 'https://evil.test/x', '//evil.test/x', '\\\\evil.test/x', '/\\evil.test',
               'javascript:alert(1)', 'data:text/html,<script>alert(1)</script>',
               'http://'.$strHote.'.evil.test/', 'http://evil.test@'.$strHote.'/', "http://evil.test/\r\nX: y") as $strU)
    T::chk('externe : '.str_replace(array("\r", "\n"), array('\r', '\n'), var_export($strU, true)), output::isinternalurl($strU) === false);
}

T::section('Extensions et entêtes de téléchargement');
foreach (array('php', 'PHP', 'phtml', 'phar', 'htaccess', 'sh', 'jsp', 'cgi', 'asp') as $strExt)
    T::chk("extension {$strExt} neutralisée",       fs::safe_extension($strExt) === 'bin');
foreach (array('pdf' => 'pdf', 'PNG' => 'png', 'docx' => 'docx', '' => '') as $strExt => $strAttendu)
    T::chk('extension '.var_export($strExt, true).' conservée', fs::safe_extension($strExt) === $strAttendu);
$booSansPoint = true;
foreach (array('php', 'php.txt', 'a.php', 'x.PhP.', '.php', 'tar.gz', 'p h p') as $strExt)
    if (strpos(fs::safe_extension($strExt), '.') !== false) $booSansPoint = false;
T::chk('aucune extension stockée ne contient de point', $booSansPoint);
T::chk('nom simple',                                fs::getdispositionfilename('rapport.pdf') === 'filename="rapport.pdf"; filename*=UTF-8\'\'rapport.pdf');
T::chk('guillemet neutralisé',                      strpos(fs::getdispositionfilename('a".html'), 'filename="a_.html"') === 0);
T::chk('retours chariot retirés',                   strpos(fs::getdispositionfilename("a\r\nX: y.txt"), "\r") === false);
T::chk('chemin retiré',                             strpos(fs::getdispositionfilename('/etc/passwd'), 'filename="passwd"') === 0);

T::section('Filtre d\'entrée');
T::chk('balise brute retirée',                      inputfilter::process('<script>alert(1)</script>') === 'alert(1)');
T::chk('entité décimale',                           inputfilter::process('&#60;script&#62;x&#60;/script&#62;') === 'x');
T::chk('entité hexadécimale',                       inputfilter::process('&#x3c;script&#x3e;x&#x3c;/script&#x3e;') === 'x');
T::chk('accents UTF-8 préservés',                   inputfilter::process('Café à Noël') === 'Café à Noël');
T::chk('entité nommée accentuée',                   inputfilter::process('&eacute;t&eacute;') === 'été');
T::chk('aucun octet NUL injecté',                   strpos(inputfilter::process('a&#x41;b&#x1f;c'), "\0") === false);
T::chk('entité hexadécimale réellement décodée',    strpos(inputfilter::process('a&#x41;b'), 'A') !== false);
foreach (array(null, 1712345678, 3.5, true) as $mixVal)
    T::chk('type préservé : '.gettype($mixVal),     inputfilter::process($mixVal) === $mixVal);
T::chk('_PLOOPI_FILTER_VARS définie',               defined('_PLOOPI_FILTER_VARS'));

T::section('Règles d\'IP des espaces (ip::isvalid)');
/**
 * ip::isvalid() appelait ploopi_getip(), fonction disparue avec les espaces de
 * noms : tout espace muni de règles d'IP finissait la connexion en erreur fatale.
 * Depuis le lot 12, ip::get() renvoie aussi l'IPv6, que des règles IPv4 ne
 * doivent jamais admettre.
 */
$ip_valide = function($strAdresse, $strRegles) {
    putenv('HTTP_CLIENT_IP'); putenv('HTTP_X_FORWARDED_FOR');
    putenv('REMOTE_ADDR='.$strAdresse);
    try { return ploopi\ip::isvalid(ploopi\ip::getrules($strRegles)); }
    catch (\Throwable $e) { return get_class($e).' : '.$e->getMessage(); }
};
T::eq('adresse dans une plage joker : admise',     true,  $ip_valide('192.168.1.10', '192.168.1.*'));
T::eq('adresse hors des plages : refusée',         false, $ip_valide('192.168.1.10', '10.0.0.1-10.0.0.5;172.16.0.*'));
T::eq('adresse dans la seconde plage : admise',    true,  $ip_valide('10.0.0.3', '192.168.1.*;10.0.0.1-10.0.0.5'));
T::eq('IPv6 face à des règles IPv4 : refusée',     false, $ip_valide('2001:db8::1', '192.168.1.*'));
T::eq('règle illisible : refus, pas ouverture',    false, $ip_valide('192.168.1.10', 'n_importe_quoi'));
T::eq('sans règle : admise',                       true,  $ip_valide('2001:db8::1', ''));
putenv('REMOTE_ADDR');

T::section('Hôte de confiance pour les liens envoyés par courriel');
// Les listes de domaines des espaces : seule une déclaration en clair vaut confiance
$arrListes = array("*\r\n", 'intranet.exemple.fr, www.exemple.fr:8443', '*.exemple.fr', '');
T::chk('hôte déclaré : reconnu',                    ploopi\system::trustedhost('www.exemple.fr', $arrListes) === true);
T::chk('casse et port ignorés',                     ploopi\system::trustedhost('WWW.Exemple.fr:443', $arrListes) === true);
T::chk('hôte inconnu : refusé',                     ploopi\system::trustedhost('evil.example', $arrListes) === false);
T::chk('joker « * » : ne vaut pas déclaration',    ploopi\system::trustedhost('evil.example', array('*')) === false);
T::chk('joker de sous-domaine : ignoré',            ploopi\system::trustedhost('a.exemple.fr', $arrListes) === false);
T::chk('hôte vide : refusé',                        ploopi\system::trustedhost('', $arrListes) === false);

T::section('Stockage S3 (fs_s3)');
/**
 * Aucun dépôt S3 n'est joignable ici : le client de fs_s3 est remplacé par un client
 * du SDK AWS branché sur un MockHandler, qui enregistre les commandes reçues.
 * upload() appelait getObject (lecture) au lieu de putObject : aucun document n'était
 * déposé. Le certificat du dépôt n'était jamais vérifié ('verify' => false).
 */
if (!class_exists('Aws\\MockHandler'))
{
    T::skip('fs_s3', 'SDK AWS absent de vendor/');
}
else
{
    foreach (array('_PLOOPI_S3_REGION' => 'eu-west-1', '_PLOOPI_S3_ENDPOINT_URL' => 'https://s3.example.test', '_PLOOPI_S3_AUTH_KEY' => 'cle', '_PLOOPI_S3_AUTH_SECRET' => 'secret') as $strConstante => $strValeur)
        if (!defined($strConstante)) define($strConstante, $strValeur);

    $objReflexion = new ReflectionClass('ploopi\\fs_s3');
    $objPropriete = $objReflexion->getProperty('objClient');
    $objPropriete->setAccessible(true);

    // options du client réel : certificat vérifié par défaut
    $objMethode = $objReflexion->getMethod('options');
    $objMethode->setAccessible(true);
    $arrOptions = $objMethode->invoke(null);
    T::chk('certificat du dépôt vérifié par défaut', (!defined('_PLOOPI_S3_VERIFY_SSL') || _PLOOPI_S3_VERIFY_SSL) ? $arrOptions['http']['verify'] === true : true, json_encode($arrOptions['http']));

    // le client factice
    $objMock = new Aws\MockHandler();
    $objMock->append(new Aws\Result(array()));
    $objPropriete->setValue(null, new Aws\S3\S3Client(array(
        'version' => 'latest', 'region' => 'eu-west-1', 'use_path_style_endpoint' => true,
        'endpoint' => 'https://s3.example.test', 'credentials' => array('key' => 'cle', 'secret' => 'secret'),
        'handler' => $objMock,
    )));
    $strSource = tempnam(sys_get_temp_dir(), 'ploopi_s3_');
    file_put_contents($strSource, 'contenu');
    T::chk('upload() réussit', ploopi\fs_s3::upload('depot', '12.pdf', $strSource) === true);
    $objCommande = $objMock->getLastCommand();
    T::eq('upload() envoie PutObject', 'PutObject', $objCommande ? $objCommande->getName() : '');
    T::eq('upload() vise la clé demandée', '12.pdf', $objCommande ? $objCommande['Key'] : '');
    @unlink($strSource);

    $objPropriete->setValue(null, null);
}

T::section('ip::get : adresses IPv6');
$ip_obtenue = function($strAdresse, $booWan) {
    putenv('HTTP_CLIENT_IP'); putenv('HTTP_X_FORWARDED_FOR'); putenv('REMOTE_ADDR='.$strAdresse);
    $arrIp = ploopi\ip::get($booWan);
    putenv('REMOTE_ADDR');
    return $arrIp;
};
T::eq('IPv6 publique reconnue',                     array('2a01:cb00::1'), $ip_obtenue('2a01:cb00::1', true));
T::eq('IPv6 privée (fc00::/7) écartée en WAN',      array(), $ip_obtenue('fd12:3456::1', true));
T::eq('IPv6 lien local (fe80::/10) écartée en WAN', array(), $ip_obtenue('fe80::1', true));
T::eq('IPv6 privée conservée hors WAN',             array('fd12:3456::1'), $ip_obtenue('fd12:3456::1', false));
T::eq('adresse invalide rejetée',                   array(), $ip_obtenue('2001:zz::1', false));

T::section('forms : nom des fichiers joints');
// Nom fourni par le visiteur, gardé tel quel jusqu'ici sous _PLOOPI_PATHDATA
// La source inclut './modules/forms/classes/…' : si le module est déployé, prendre la copie
// déployée (sinon formsField serait déclarée deux fois, par deux chemins différents)
include_once is_file(PLOOPI_ROOT.'/modules/forms/classes/formsField.php') ? PLOOPI_ROOT.'/modules/forms/classes/formsField.php' : PLOOPI_ROOT.'/install/forms/files/classes/formsField.php';
T::eq('nom ordinaire conservé',                     'rapport_2026.pdf', formsField::safeFileName('rapport 2026.pdf'));
T::eq('accents translittérés',                      'ete.odt', formsField::safeFileName('été.odt'));
T::eq('« .htaccess » neutralisé',                   'fichier.'.ploopi\fs::NEUTRAL_EXTENSION, formsField::safeFileName('.htaccess'));
T::eq('extension exécutable neutralisée',           'shell.'.ploopi\fs::NEUTRAL_EXTENSION, formsField::safeFileName('shell.php'));
T::eq('chemin retiré',                              'passwd', formsField::safeFileName('../../etc/passwd'));

T::section('Exception non interceptée');
/**
 * Les erreurs fatales du socle (base injoignable, type de champ inconnu...) sont
 * des exceptions depuis la PR #62. Sans gestionnaire d'exceptions, elles
 * échappaient à error::handler() : erreur fatale PHP brute, rien au journal.
 * Un sous-processus, car le gestionnaire met fin à l'exécution.
 */
$strScript = '<?php require '.var_export(__DIR__.'/bootstrap.php', true).'; amorcer_socle();'
    .' if (!defined("_PLOOPI_LOG_ERRORS")) define("_PLOOPI_LOG_ERRORS", false);'
    .' ploopi\\error::set_handler(); throw new \\ErrorException("Exception de test");';
$strFichierScript = tempnam(sys_get_temp_dir(), 'ploopi_exc_');
file_put_contents($strFichierScript, $strScript);
$strSortie = (string)shell_exec(escapeshellarg(PHP_BINARY).' -d display_errors=1 '.escapeshellarg($strFichierScript).' 2>&1');
@unlink($strFichierScript);
T::chk('traitée par error::handler (page d\'erreur)', strpos($strSortie, 'Une erreur est survenue') !== false || strpos($strSortie, '=== ') === 0, $strSortie);
T::chk('pas d\'erreur fatale PHP brute',             strpos($strSortie, 'Uncaught') === false, $strSortie);

T::section('Lieu d\'une erreur au journal');
/**
 * Le journal ne portait que la pile d'appels, sans le fichier ni la ligne de l'erreur
 * ($errfile, $errline) : une erreur de compilation (obsolescence « case X; » sous
 * PHP 8.5…) y apparaissait au site de l'include, pas dans le fichier fautif.
 */
$arrSauve = array(ploopi\error::$msg, ploopi\error::$nb, ploopi\error::$level);
ploopi\error::$msg = '';
ob_start();
ploopi\error::handler(E_DEPRECATED, 'Obsolescence de test', '/chemin/du/fichier_fautif.php', 7);
ob_end_clean();
$strJournal = ploopi\error::$msg;
list(ploopi\error::$msg, ploopi\error::$nb, ploopi\error::$level) = $arrSauve;
T::contient('fichier et ligne de l\'erreur, après le message', "Message : Obsolescence de test\nLieu : /chemin/du/fichier_fautif.php:7\n", $strJournal);

T::section('Annotations d\'un enregistrement sans identifiant');
/**
 * Le formulaire d'un nouvel enregistrement (événement du planning…) affichait les
 * annotations de l'identifiant null : addslashes(null) (obsolète en PHP 8), et un bloc
 * partagé par tous les nouveaux enregistrements. Rien n'est affiché tant que
 * l'enregistrement n'existe pas.
 */
$_SESSION['ploopi']['moduleid'] = 1;
$_SESSION['ploopi']['userid'] = 1;
unset($_SESSION['annotation']);
foreach (array('null' => null, 'vide' => '') as $strCas => $mixRecord)
{
    ob_start();
    ploopi\annotation::display(1, $mixRecord, 'Nouvel enregistrement');
    $strSortie = ob_get_clean();
    T::chk("identifiant {$strCas} : aucun bloc", trim($strSortie) === '' && empty($_SESSION['annotation']), substr(trim($strSortie), 0, 80));
}

T::section('Champ richtext des formulaires (form_richtext)');
/**
 * Le champ est un textarea déclaré à l'éditeur du socle (js/editor.js) : sa valeur part
 * avec le formulaire (le CKEditor d'origine remplaçait un <span> sans nom) et le contenu
 * est échappé, sans quoi un « </textarea> » saisi sortait du champ.
 */
$objRichtext = new ploopi\form_richtext('Texte', 'A</textarea><b id="x">B', 'champ_texte', 'champ_texte', array('profile' => 'complet', 'height' => 300, 'link' => 'admin-light.php?a=1&b=2'));
$strRendu = $objRichtext->render(4);
T::chk('textarea nommé, déclaré à l\'éditeur',     (bool)preg_match('/<textarea id="champ_texte" name="champ_texte"[^>]*data-ploopi-editor="complet"/', $strRendu), $strRendu);
T::chk('hauteur et fenêtre de choix transmises',    strpos($strRendu, 'data-ploopi-editor-height="300"') !== false && strpos($strRendu, 'data-ploopi-editor-link="admin-light.php?a=1&amp;b=2"') !== false);
T::chk('contenu échappé dans le champ',             strpos($strRendu, '>A&lt;/textarea&gt;&lt;b id=&quot;x&quot;&gt;B</textarea>') !== false);
T::chk('le contenu ne referme pas le champ',        strpos($strRendu, '<b id="x">') === false);
T::chk('options absentes : pas d\'attribut vide',   strpos($strRendu, 'data-ploopi-editor-image') === false && strpos($strRendu, 'data-ploopi-editor-css') === false);

T::section('documents::isallowed');
// documentsfile etend data_object, dont le constructeur ouvre la connexion : sans
// base joignable, la section est declaree non evaluee plutot que fatale.
if (!bdd_disponible())
{
    T::skip('documents::isallowed', 'aucune base de donnees joignable');
    exit(T::bilan());
}
$funcDoc = function($intModule, $intWorkspace) {
    $objDoc = new documentsfile();
    $objDoc->fields = array('id' => 1, 'id_module' => $intModule, 'id_workspace' => $intWorkspace, 'id_object' => 1, 'id_record' => '55');
    return $objDoc;
};
$_SESSION['documents'] = array();
$_SESSION['ploopi']['workspaces'] = array(3 => array('modules' => array(4, 7), 'adminlevel' => 10));
T::chk('module de l\'espace : autorisé',            documents::isallowed($funcDoc(7, 3)) === true);
T::chk('module hors espace : refusé',               documents::isallowed($funcDoc(42, 3)) === false);
T::chk('espace non accessible : refusé',            documents::isallowed($funcDoc(7, 99)) === false);
/**
 * Documents du module system : ceux d'un profil utilisateur (_SYSTEM_OBJECT_USER = 3,
 * id_record = utilisateur). Ouverts jusqu'ici à tout membre de l'espace ; désormais au
 * titulaire et aux gestionnaires de l'espace.
 */
$funcDocProfil = function($intTitulaire) {
    $objDoc = new documentsfile();
    $objDoc->fields = array('id' => 1, 'id_module' => _PLOOPI_MODULE_SYSTEM, 'id_workspace' => 3, 'id_object' => 3, 'id_record' => (string)$intTitulaire);
    return $objDoc;
};
$_SESSION['ploopi']['userid'] = 40;
T::chk('profil, titulaire : autorisé',              documents::isallowed($funcDocProfil(40)) === true);
T::chk('profil d\'un autre membre : refusé',        documents::isallowed($funcDocProfil(41)) === false);
$_SESSION['ploopi']['workspaces'][3]['adminlevel'] = _PLOOPI_ID_LEVEL_GROUPMANAGER;
T::chk('profil, gestionnaire de l\'espace : autorisé', documents::isallowed($funcDocProfil(41)) === true);
$_SESSION['ploopi']['workspaces'][3]['adminlevel'] = 10;
$_SESSION['ploopi']['workspaces'] = array(3 => array('modules' => array(), 'adminlevel' => 99));
T::chk('administrateur système : autorisé',         documents::isallowed($funcDoc(7, 3)) === true);
$_SESSION['ploopi']['workspaces'] = array(3 => array('modules' => array(), 'adminlevel' => 10), 8 => array('modules' => array(7), 'adminlevel' => 10));
T::chk('module partagé par un autre espace : autorisé', documents::isallowed($funcDoc(7, 3)) === true);
$_SESSION['documents'] = array('ctx' => array('id_module' => 77, 'id_object' => 1, 'id_record' => '55'));
T::chk('contexte GED ouvert : autorisé',            documents::isallowed($funcDoc(77, 99)) === true);
$_SESSION['documents'] = array();
T::chk('enregistrement non ouvert : refusé',        documents::isallowed(new documentsfile()) === false);

/**
 * Sous Docker, le dépôt est monté tel quel dans le conteneur : une installation hors
 * Docker du même dépôt y laissait son config/config.php, que le conteneur lisait
 * (base « localhost » injoignable, assistant masqué) jusqu'à ce qu'on le renomme à la
 * main. La variable d'environnement PLOOPI_CONFIG désigne un autre fichier, lu par
 * le socle et écrit par l'assistant.
 */
T::section('Fichier de configuration (PLOOPI_CONFIG)');
$strAvant = getenv('PLOOPI_CONFIG');
putenv('PLOOPI_CONFIG');
T::eq('par défaut : config/config.php du dépôt', './config/config.php', ploopi\loader::configpath());
putenv('PLOOPI_CONFIG=/var/www/ploopi-config/config.php');
T::eq('PLOOPI_CONFIG renseignée : ce fichier', '/var/www/ploopi-config/config.php', ploopi\loader::configpath());
putenv('PLOOPI_CONFIG=');
T::eq('PLOOPI_CONFIG vide : config/config.php du dépôt', './config/config.php', ploopi\loader::configpath());
putenv($strAvant === false ? 'PLOOPI_CONFIG' : 'PLOOPI_CONFIG='.$strAvant);

exit(T::bilan());
