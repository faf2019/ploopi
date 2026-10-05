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
 * Tests des mécanismes de sécurité : filtrage des entrées, chiffrement d'URL,
 * sérialisation et mots de passe.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

use ploopi\security;
use ploopi\inputfilter;
use ploopi\crypt;
use ploopi\cipher;

T::section('inputfilter::process');
/**
 * inputfilter est appliqué à $_GET / $_POST / $_REQUEST / $_COOKIE / $_SERVER par
 * loader::_importgpr(). Sa configuration par défaut retire TOUTES les balises :
 * c'est un nettoyage d'entrée, pas un échappement de sortie. L'échappement reste
 * à la charge de l'affichage (str::htmlentities / str::htmlpurifier, CLAUDE.md §8.2).
 */
T::eq('balise script retirée', 'alert(1)', inputfilter::process('<script>alert(1)</script>'));
T::eq('balise script imbriquée retirée', 'alert(1)', inputfilter::process('<scr<script>ipt>alert(1)</script>'));
T::eq('attribut onerror retiré avec sa balise', '', inputfilter::process('<img src=x onerror=alert(1)>'));
T::eq('attribut onmouseover retiré avec sa balise', '', inputfilter::process('<img src="x" onmouseover="alert(1)">'));
T::eq('balise svg retirée', '', inputfilter::process('<svg/onload=alert(1)>'));
T::eq('iframe retirée', '', inputfilter::process('<iframe src=//evil></iframe>'));
T::eq('lien javascript: réduit à son libellé', 'clic', inputfilter::process('<a href="javascript:alert(1)">clic</a>'));
T::eq('balise de mise en forme retirée elle aussi', 'gras', inputfilter::process('<b>gras</b>'));
T::eq('texte sans balise inchangé', 'texte normal & co', inputfilter::process('texte normal & co'));

T::section('security::filtervar');
T::eq('chaîne filtrée', 'bonjour', security::filtervar('<b>bonjour</b>'));
T::eq('tableau filtré récursivement', array('a' => 'x', 'b' => 'ok'), security::filtervar(array('a' => '<i>x</i>', 'b' => 'ok')));
T::eq(
    'tableau imbriqué filtré',
    array('n' => array('p' => 'y')),
    security::filtervar(array('n' => array('p' => '<u>y</u>')))
);
/**
 * Les variables préfixées fck_ échappent au filtrage (contenus de l'éditeur riche).
 */
T::eq('les variables fck_ ne sont pas filtrées', '<b>gras</b>', security::filtervar('<b>gras</b>', 'fck_contenu'));
T::eq('le nom de variable est propagé aux clés d\'un tableau', '<b>gras</b>', security::filtervar(array('fck_x' => '<b>gras</b>'))['fck_x']);

T::section('security::checkpasswordvalidity');
T::eq('mot de passe complet accepté', true, (bool)security::checkpasswordvalidity('Abcdef1!', 8, 20));
T::eq('sans majuscule refusé', false, (bool)security::checkpasswordvalidity('abcdef1!', 8, 20));
T::eq('sans minuscule refusé', false, (bool)security::checkpasswordvalidity('ABCDEF1!', 8, 20));
T::eq('sans chiffre refusé', false, (bool)security::checkpasswordvalidity('Abcdefg!', 8, 20));
T::eq('sans caractère spécial refusé', false, (bool)security::checkpasswordvalidity('Abcdefg1', 8, 20));
T::eq('trop court refusé', false, (bool)security::checkpasswordvalidity('Ab1!', 8, 20));
T::eq('trop long refusé', false, (bool)security::checkpasswordvalidity(str_repeat('Ab1!', 10), 8, 20));
T::eq('les accents sont translittérés avant contrôle', true, (bool)security::checkpasswordvalidity('Abcdéf1!', 8, 20));

T::section('security::generatepassword');
for($i = 0; $i < 20; $i++)
{
    $strMotDePasse = security::generatepassword(12);
    if (strlen($strMotDePasse) != 12) { T::chk('longueur demandée respectée', false, 'obtenu '.strlen($strMotDePasse)); break; }
    if (!preg_match('/[A-Z]/', $strMotDePasse)) { T::chk('au moins une majuscule', false, $strMotDePasse); break; }
    if (!preg_match('/[a-z]/', $strMotDePasse)) { T::chk('au moins une minuscule', false, $strMotDePasse); break; }
    if (!preg_match('/[0-9]/', $strMotDePasse)) { T::chk('au moins un chiffre', false, $strMotDePasse); break; }
    if (!preg_match('/[:?!@#$%&*]/', $strMotDePasse)) { T::chk('au moins un caractère de ponctuation', false, $strMotDePasse); break; }
}
if ($i == 20) T::chk('20 mots de passe générés respectent toutes les contraintes', true);

T::eq('longueur minimale forcée à 4', 4, strlen(security::generatepassword(1)));
T::eq('sans majuscule ni chiffre ni ponctuation', 0, preg_match('/[A-Z0-9:?!@#$%&*]/', security::generatepassword(10, false, false, false)));

$arrGeneres = array();
for($i = 0; $i < 50; $i++) $arrGeneres[] = security::generatepassword(12);
T::chk('50 générations donnent 50 valeurs distinctes', sizeof(array_unique($arrGeneres)) == 50, sizeof(array_unique($arrGeneres)).' valeurs distinctes');

T::section('crypt : base64 compatible URL');
T::eq('aller-retour', 'données ?&=', crypt::base64_decode(crypt::base64_encode('données ?&=')));
$strEncode = crypt::base64_encode("\xfb\xff\xfe");
T::eq('aucun caractère + / ou = dans la sortie', 0, preg_match('@[+/=]@', $strEncode));
T::eq('aller-retour sur des octets binaires', "\xfb\xff\xfe", crypt::base64_decode($strEncode));

T::section('crypt : sérialisation compressée');
$arrDonnees = array('a' => 1, 'b' => array('c' => "texte avec ' et \""));
T::eq('aller-retour', $arrDonnees, crypt::unserialize(crypt::serialize($arrDonnees)));
$arrRepetitif = array_fill(0, 200, 'valeur répétée');
T::chk(
    'la sérialisation compresse les contenus répétitifs',
    strlen(crypt::serialize($arrRepetitif)) < strlen(serialize($arrRepetitif)) / 4,
    'compressé : '.strlen(crypt::serialize($arrRepetitif)).' octets, brut : '.strlen(serialize($arrRepetitif))
);
T::eq('aller-retour sur un gros tableau', $arrRepetitif, crypt::unserialize(crypt::serialize($arrRepetitif)));

T::section('cipher : chiffrement des URL');
$strClair = 'entity=admin&action=write&id=42';
$strChiffre = cipher::singleton()->crypt($strClair);
T::eq('aller-retour', $strClair, cipher::singleton()->decrypt($strChiffre));
T::contientpas('le clair n\'apparaît pas dans le chiffré', 'entity=admin', $strChiffre);
T::eq('aucun caractère + / ou = dans la sortie (URL)', 0, preg_match('@[+/=]@', $strChiffre));
T::eq('un chiffré altéré ne se déchiffre pas en clair d\'origine', false, cipher::singleton()->decrypt($strChiffre.'xyz') === $strClair);
T::eq('une valeur quelconque ne se déchiffre pas', false, cipher::singleton()->decrypt('nimportequoi') === $strClair);
/**
 * Constat : le vecteur d'initialisation vient de la configuration (_PLOOPI_CIPHER_IV)
 * et ne change pas d'un message à l'autre. Deux clairs identiques produisent donc
 * exactement le même chiffré, ce qui laisse fuiter l'égalité de deux URL.
 */
T::eq('le chiffrement est déterministe (IV fixe issu de la configuration)', $strChiffre, cipher::singleton()->crypt($strClair));

T::section('crypt::htpasswd');
/**
 * DÉFAUT CONSTATÉ. crypt(trim($pass), CRYPT_STD_DES) passe la CONSTANTE CRYPT_STD_DES
 * (valeur 1) comme SEL, et non comme algorithme. Le sel « 1 » étant invalide, crypt()
 * retourne l'échec « *0 » quel que soit le mot de passe.
 *
 * Conséquence : le .htpasswd produit par system_generate_htpasswd()
 * (modules/system/include/functions.php) contient « *0 » pour tous les comptes. Le
 * fichier n'ouvre donc aucun accès — il interdit tout le monde, ce qui est le bon
 * côté de l'erreur, mais la fonctionnalité ne marche pas.
 */
T::eq('retourne la valeur d\'échec de crypt()', '*0', crypt::htpasswd('motdepasse'));
T::eq('deux mots de passe différents donnent le même résultat', crypt::htpasswd('abc'), crypt::htpasswd('xyz'));

T::bilan('07_securite');
