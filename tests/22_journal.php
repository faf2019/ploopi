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
 * Suite 22 — journal des actions utilisateur (user_action_log::record).
 *
 * Le journal est la trace des connexions, des échecs de connexion et des actions
 * métier. Une entrée refusée par la base est perdue sans bruit : record() ne renvoie
 * rien. MySQL et MariaDB sont en mode strict par défaut (STRICT_TRANS_TABLES) : une
 * chaîne vide dans une colonne entière, ou une valeur plus longue que sa colonne, y est
 * une erreur, plus une conversion silencieuse.
 *
 * La suite mesure la table, pas le retour de la fonction.
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

$strModeSql = db()->query('SELECT @@SESSION.sql_mode')->fetch_row()[0];
T::vrai('le serveur de test est en mode strict', strpos($strModeSql, 'STRICT_') !== false, $strModeSql);

/**
 * @param int $intAction identifiant d'action
 * @param string $strRecord identifiant d'enregistrement (préfixe)
 * @return array entrées du journal correspondantes, la plus récente en tête
 */
function journal_entrees($intAction, $strRecord)
{
    $arrEntrees = array();
    $objRs = db()->query(sprintf(
        "SELECT * FROM ploopi_user_action_log WHERE id_action = %d AND id_record LIKE '%s%%' ORDER BY timestp DESC",
        $intAction, db()->real_escape_string($strRecord)
    ));
    while ($objRs && $arrLigne = $objRs->fetch_assoc()) $arrEntrees[] = $arrLigne;

    return $arrEntrees;
}

/**
 * Les entrées de test portent un identifiant d'enregistrement reconnaissable.
 */
function journal_nettoyer()
{
    db()->query("DELETE FROM ploopi_user_action_log WHERE id_record LIKE 'journaltest%' OR id_record LIKE 'inconnu%'");
}
register_shutdown_function('journal_nettoyer');
journal_nettoyer();

T::section('Connexion réussie');
/**
 * À la connexion, la session n'a pas encore d'espace courant : l'identifiant d'espace
 * valait '' et l'entrée était refusée.
 */
db()->query("DELETE FROM ploopi_user_action_log WHERE id_action = "._SYSTEM_ACTION_LOGIN_OK." AND id_record = '".db()->real_escape_string(cfg('admin_login'))."'");
H::reset();
$arrRep = H::connexion();
T::vrai('témoin : la connexion aboutit', H::env() !== '', 'aucun ploopi_env après connexion');
$arrEntrees = journal_entrees(_SYSTEM_ACTION_LOGIN_OK, cfg('admin_login'));
T::eq('la connexion est journalisée', 1, sizeof($arrEntrees));
T::eq('par l\'utilisateur connecté', 2, (int)($arrEntrees[0]['id_user'] ?? 0));

T::section('Échec de connexion');
H::reset();
H::req('/admin.php', 'ploopi_login=inconnujournal&ploopi_password=mauvais');
T::eq('l\'échec de connexion est journalisé', 1, sizeof(journal_entrees(_SYSTEM_ACTION_LOGIN_ERR, 'inconnujournal')));

/**
 * L'identifiant saisi est journalisé dans id_record, char(255). Plus long, il était
 * refusé : il suffisait d'un identifiant de 256 caractères pour échouer sans trace.
 */
H::reset();
H::req('/admin.php', 'ploopi_login=inconnu'.str_repeat('x', 300).'&ploopi_password=mauvais');
$arrEntrees = journal_entrees(_SYSTEM_ACTION_LOGIN_ERR, 'inconnuxxx');
T::eq('un identifiant trop long est journalisé quand même', 1, sizeof($arrEntrees));
T::eq('tronqué à la largeur de la colonne', 255, mb_strlen($arrEntrees[0]['id_record'] ?? ''));

T::section('Valeurs reprises de la session');
/**
 * record($action, $record) sans module ni espace : ils sont pris dans la session.
 * Les variables locales étaient bien mises à jour, mais seul l'espace était recopié dans
 * l'entrée : le module et son type restaient à 0.
 */
$arrSessionAvant = $_SESSION ?? null;
$_SESSION = array('ploopi' => array(
    'moduletypeid'  => 1,
    'moduleid'      => _PLOOPI_MODULE_SYSTEM,
    'workspaceid'   => 2,
    'userid'        => 2,
    'user'          => array('firstname' => 'Prenom', 'lastname' => 'Nom', 'login' => 'journaltest'),
    'workspaces'    => array(2 => array('label' => 'Espace de test')),
    'modules'       => array(_PLOOPI_MODULE_SYSTEM => array('label' => 'Système', 'moduletype' => 'system')),
));
ploopi\user_action_log::record(_SYSTEM_ACTION_LOGIN_OK, 'journaltest_session');
$arrEntrees = journal_entrees(_SYSTEM_ACTION_LOGIN_OK, 'journaltest_session');
T::eq('témoin : l\'entrée est enregistrée', 1, sizeof($arrEntrees));
T::eq('l\'espace vient de la session', 2, (int)($arrEntrees[0]['id_workspace'] ?? -1));
T::eq('le module aussi', (int)_PLOOPI_MODULE_SYSTEM, (int)($arrEntrees[0]['id_module'] ?? -1));
T::eq('et son type', 1, (int)($arrEntrees[0]['id_module_type'] ?? -1));
T::eq('le libellé de l\'action est résolu', 'Connexion Utilisateur', $arrEntrees[0]['action'] ?? null);

T::section('Valeurs plus longues que leur colonne');
/**
 * ip est en varchar(45) depuis le lot 12 : une adresse IPv6 (39 caractères
 * au plus) y tient entière, de même qu'une courte chaîne de mandataires
 * (X-Forwarded-For). Libellés en char(100).
 */
$_SESSION['ploopi']['remote_ip'] = array('2001:db8:85a3:0:0:8a2e:370:7334');
$_SESSION['ploopi']['user']['lastname'] = str_repeat('N', 150);
ploopi\user_action_log::record(_SYSTEM_ACTION_LOGIN_OK, 'journaltest_long');
$arrEntrees = journal_entrees(_SYSTEM_ACTION_LOGIN_OK, 'journaltest_long');
T::eq('l\'entrée est enregistrée malgré une adresse IPv6', 1, sizeof($arrEntrees));
T::eq('adresse IPv6 conservée entière', '2001:db8:85a3:0:0:8a2e:370:7334', $arrEntrees[0]['ip'] ?? null);
T::eq('libellé utilisateur tronqué à 100 caractères', 100, mb_strlen($arrEntrees[0]['user'] ?? ''));

$_SESSION = $arrSessionAvant;

exit(T::bilan('22_journal'));
