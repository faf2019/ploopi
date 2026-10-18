<?php
/*    Copyright (c) 2007-2018 Ovensia    Contributors hold Copyright (c) to their code submissions.
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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  0211-1301  USA*/

/**
 * Suite 35 — mot de passe oublié (ploopi_lostpassword, ploopi_lostpassword_confirm).
 *
 * Deux défauts, relevés par la revue du 9 octobre :
 * - le lien de confirmation envoyé par courriel était bâti sur _PLOOPI_BASEPATH,
 *   dérivée de l'entête Host : une demande forgée avec « Host: evil.example »
 *   envoyait au titulaire du compte un lien vers evil.example, code compris ;
 * - le code de confirmation n'expirait jamais.
 *
 * Le courriel n'est pas observable ici : on mesure le code de confirmation en base
 * (créé ou non) et le mot de passe du compte (changé ou non).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

exiger_instance();
amorcer_socle();

$strConfig = PLOOPI_ROOT.'/config/config.php';
$strOrigine = (string)file_get_contents($strConfig);
$strDomaines = db()->query('SELECT backoffice_domainlist d FROM ploopi_workspace WHERE id = 2')->fetch_assoc()['d'];

function mdpo_nettoyer()
{
    global $strConfig, $strOrigine, $strDomaines;
    file_put_contents($strConfig, $strOrigine);
    db()->query("UPDATE ploopi_workspace SET backoffice_domainlist = '".db()->real_escape_string($strDomaines)."' WHERE id = 2");
    db()->query("DELETE FROM ploopi_confirmation_code WHERE action LIKE 'ploopi_lostpassword%'");
    db()->query("DELETE FROM ploopi_workspace_user WHERE id_user IN (SELECT id FROM ploopi_user WHERE login = 'mdp_oublie')");
    db()->query("DELETE FROM ploopi_user WHERE login = 'mdp_oublie'");
}
register_shutdown_function('mdpo_nettoyer');
mdpo_nettoyer();

db()->query("INSERT INTO ploopi_user (login, password, lastname, firstname, email, date_creation)
             VALUES ('mdp_oublie', '".db()->real_escape_string(ploopi\user::generate_hash('Initial!2026', 'mdp_oublie'))."', 'Oublie', 'Mdp', 'mdpo@example.test', '".date('YmdHis')."')");
$intUser = db()->insert_id;
db()->query("INSERT INTO ploopi_workspace_user (id_user, id_workspace, id_profile, adminlevel) VALUES ({$intUser}, 2, 1, 10)");

$lire_code = function () use ($intUser) {
    $arrLigne = db()->query("SELECT code, timestp FROM ploopi_confirmation_code WHERE action = 'ploopi_lostpassword{$intUser}'")->fetch_assoc();
    return $arrLigne ? $arrLigne : null;
};
$lire_hash = function () use ($intUser) {
    return db()->query("SELECT password FROM ploopi_user WHERE id = {$intUser}")->fetch_assoc()['password'];
};
$demander = function ($arrEntetes = array()) {
    H::reset();
    H::req('/admin.php?ploopi_op=ploopi_lostpassword&ploopi_lostpassword_login=mdp_oublie', null, $arrEntetes);
};
$confirmer = function ($strCode) {
    H::reset();
    H::req('/admin.php?ploopi_op=ploopi_lostpassword_confirm&ploopi_lostpassword_login=mdp_oublie&ploopi_lostpassword_confirmcode='.urlencode($strCode));
};

T::section('Expiration du code de confirmation');

$demander();
$arrCode = $lire_code();
T::vrai('témoin : la demande crée un code', $arrCode !== null);

// code vieilli de deux heures : refusé et supprimé
db()->query("UPDATE ploopi_confirmation_code SET timestp = '".date('YmdHis', time() - 7200)."' WHERE action = 'ploopi_lostpassword{$intUser}'");
$strAvant = $lire_hash();
$confirmer($arrCode['code']);
T::eq('code périmé : mot de passe inchangé', $strAvant, $lire_hash());
T::vrai('code périmé : supprimé', $lire_code() === null);

// témoin : un code frais aboutit
$demander();
$arrCode = $lire_code();
$confirmer($arrCode ? $arrCode['code'] : '');
T::neq('témoin : code frais, mot de passe renouvelé', $strAvant, $lire_hash());

T::section('Lien envoyé : jamais vers l\'hôte de la requête');

// sans _PLOOPI_PUBLIC_URL, l'hôte doit être déclaré dans les domaines d'un espace
$strBascule = preg_replace("/define *\\( *'_PLOOPI_PUBLIC_URL' *, *'[^']*' *\\)/", "define ('_PLOOPI_PUBLIC_URL', '')", $strOrigine, -1, $intRemplacements);
T::vrai('la configuration porte _PLOOPI_PUBLIC_URL (assistant)', $intRemplacements > 0);
file_put_contents($strConfig, $strBascule);
db()->query("UPDATE ploopi_workspace SET backoffice_domainlist = '*' WHERE id = 2");
db()->query("DELETE FROM ploopi_confirmation_code WHERE action = 'ploopi_lostpassword{$intUser}'");
// l'opcache du serveur revalide les fichiers toutes les 2 s
sleep(3);

$demander(array('Host: evil.example'));
T::vrai('hôte forgé, non déclaré : aucun code émis', $lire_code() === null);

$strHote = parse_url(cfg('base_url'), PHP_URL_HOST);
db()->query("UPDATE ploopi_workspace SET backoffice_domainlist = '".db()->real_escape_string($strHote)."' WHERE id = 2");
$demander();
T::vrai('témoin : hôte déclaré en clair, code émis', $lire_code() !== null);

file_put_contents($strConfig, $strOrigine);
sleep(3);

exit(T::bilan('35_motdepasse_oublie'));
