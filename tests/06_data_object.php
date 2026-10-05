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
 * Tests de l'ORM : ploopi\data_object et ploopi\data_object_collection.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();
exiger_bdd('06_data_object');

use ploopi\db;

$objDb = db::get();
$strTable = _PLOOPI_TESTS_PREFIXE.'article';
$strTableCle = _PLOOPI_TESTS_PREFIXE.'liaison';

/**
 * Objet métier de test, sur le modèle décrit dans CLAUDE.md §7.2.
 */
class article extends ploopi\data_object
{
    public function __construct() { parent::__construct(_PLOOPI_TESTS_PREFIXE.'article'); }
}

/**
 * Objet métier de test à clé composite.
 */
class liaison extends ploopi\data_object
{
    public function __construct() { parent::__construct(_PLOOPI_TESTS_PREFIXE.'liaison', array('id_a', 'id_b')); }
}

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query(
    "CREATE TABLE `{$strTable}` (
        `id` int(10) unsigned NOT NULL auto_increment,
        `titre` varchar(255) NOT NULL default '',
        `contenu` text,
        `note` int(10) unsigned default '7',
        `id_module` int(10) unsigned default '0',
        `id_workspace` int(10) unsigned default '0',
        `id_user` int(10) unsigned default '0',
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
);

$objDb->query("DROP TABLE IF EXISTS `{$strTableCle}`");
$objDb->query(
    "CREATE TABLE `{$strTableCle}` (
        `id_a` int(10) unsigned NOT NULL default '0',
        `id_b` int(10) unsigned NOT NULL default '0',
        `valeur` varchar(50) default '',
        PRIMARY KEY (`id_a`, `id_b`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
);

T::section('construction');
$objArticle = new article();
T::eq('un objet neuf est marqué comme nouveau', true, $objArticle->isnew());
T::eq('nom de table', _PLOOPI_TESTS_PREFIXE.'article', $objArticle->gettablename());

T::section('init_description');
$objArticle->init_description();
T::eq('les valeurs par défaut de la table sont reprises', '7', $objArticle->fields['note']);
T::eq('un champ sans défaut vaut la chaîne vide', '', $objArticle->fields['titre']);
/**
 * Piège à connaître : init_description() pré-remplit AUSSI la clé primaire avec ''.
 * L'insertion fonctionne quand même (MySQL génère la valeur auto-incrémentée pour 0),
 * mais l'objet porte une clé vide tant que save() n'a pas été appelé.
 */
T::eq('la clé primaire est pré-remplie à vide', '', $objArticle->fields['id']);

T::section('insertion après init_description : conflit avec le mode SQL strict');
/**
 * DÉFAUT CONSTATÉ, pas un choix de conception.
 *
 * init_description() pré-remplit la clé primaire avec '' ; save() produit donc
 * « INSERT ... SET `id` = '' ». Depuis MySQL 5.7 / MariaDB 10.2, sql_mode vaut par
 * défaut STRICT_TRANS_TABLES et le serveur REJETTE cette insertion
 * (« Incorrect integer value: '' for column id »).
 *
 * Autrement dit, l'enchaînement recommandé init_description() puis save() ne
 * fonctionne, sur une table à clé auto-incrémentée, que si le serveur tourne en
 * mode permissif. Le contournement côté appelant tient en une ligne :
 * unset($objet->fields['id']) avant save(). Le test épingle les deux cas pour que
 * la correction éventuelle du socle soit signalée.
 */
$arrModeSql = $objDb->fetchrow($objDb->query('SELECT @@sql_mode AS mode'));
$booStrict = strpos($arrModeSql['mode'], 'STRICT_') !== false;

$objStrict = new article();
$objStrict->init_description();
$objStrict->fields['titre'] = 'Insertion avec clé vide';
$mixCleStricte = @$objStrict->save();

if ($booStrict)
{
    T::eq('en mode strict, l\'insertion avec une clé vide échoue', '', $mixCleStricte);
    T::contient('le serveur signale la clé vide', "Incorrect integer value: ''", $objDb->get_last_error());
}
else
{
    T::skip('en mode strict, l\'insertion avec une clé vide échoue', 'sql_mode non strict ('.$arrModeSql['mode'].')');
    T::chk('en mode permissif, l\'insertion aboutit', intval($mixCleStricte) > 0, 'clé obtenue : '.var_export($mixCleStricte, true));
    $objDb->query("DELETE FROM `{$strTable}`");
}

T::section('insertion');
$objArticle = new article();
$objArticle->init_description();
unset($objArticle->fields['id']);   // contournement du défaut ci-dessus
$objArticle->fields['titre'] = "L'été \"chaud\"";
$objArticle->fields['contenu'] = 'Texte de test';
$intId = $objArticle->save();
T::chk('save() retourne la clé générée', intval($intId) > 0, 'clé obtenue : '.var_export($intId, true));
T::eq('l\'objet n\'est plus neuf', false, $objArticle->isnew());

T::section('relecture');
$objRelu = new article();
T::eq('open() retourne true', true, $objRelu->open($intId));
T::eq('les apostrophes et guillemets sont restitués', "L'été \"chaud\"", $objRelu->fields['titre']);
T::eq('la valeur par défaut a été persistée', '7', $objRelu->fields['note']);
T::eq('l\'objet relu n\'est pas neuf', false, $objRelu->isnew());

$objAbsent = new article();
T::eq('open() sur une clé inexistante retourne false', false, $objAbsent->open(999999));
/**
 * Piège : après un open() en échec, fields n'est PAS vide — fetchrow() a bien
 * renvoyé false, mais open() réaffecte ensuite les champs de clé. L'objet porte donc
 * uniquement la clé demandée, sans aucune des colonnes de la table.
 * CLAUDE.md §17 décrit ce cas comme « fields reste à false » : c'est inexact pour un
 * objet à clé simple. La règle pratique reste la même — toujours tester le retour de
 * open() avant de lire fields.
 */
T::eq('après un open() en échec, fields ne contient que la clé', array('id' => 999999), $objAbsent->fields);
T::eq('aucune colonne de la table n\'est présente', false, isset($objAbsent->fields['titre']));

T::section('mise à jour');
$objRelu->fields['titre'] = 'Titre modifié';
$objRelu->save();
$objVerif = new article();
$objVerif->open($intId);
T::eq('la modification est persistée', 'Titre modifié', $objVerif->fields['titre']);
$objCompte = $objDb->query("SELECT COUNT(*) AS nb FROM `{$strTable}`");
$arrCompte = $objDb->fetchrow($objCompte);
T::eq('aucun doublon créé par la mise à jour', '1', $arrCompte['nb']);

T::section('setvalues (dépréfixage)');
$objSaisie = new article();
$objSaisie->init_description();
unset($objSaisie->fields['id']);
$objSaisie->setvalues(array('art_titre' => 'Depuis le formulaire', 'art_note' => 3, 'autre' => 'ignoré'), 'art_');
T::eq('le préfixe est retiré', 'Depuis le formulaire', $objSaisie->fields['titre']);
T::eq('les autres champs préfixés sont repris', 3, $objSaisie->fields['note']);
T::eq('un champ hors préfixe n\'est pas repris', false, isset($objSaisie->fields['autre']));

T::section('setuwm');
$_SESSION['ploopi'] = array('userid' => 12, 'workspaceid' => 34, 'moduleid' => 56);
$objSaisie->setuwm();
T::eq('id_user', 12, $objSaisie->fields['id_user']);
T::eq('id_workspace', 34, $objSaisie->fields['id_workspace']);
T::eq('id_module', 56, $objSaisie->fields['id_module']);
$objSaisie->save();
unset($_SESSION['ploopi']);

T::section('collection');
$objCollection = article::get_collection();
$objCollection->add_where('id_workspace = %d', 34);
$arrObjets = $objCollection->get_objects();
T::eq('un seul objet dans l\'espace 34', 1, sizeof($arrObjets));
$objPremier = reset($arrObjets);
T::chk('la collection rend des objets de la bonne classe', $objPremier instanceof article);
T::eq('contenu de l\'objet', 'Depuis le formulaire', $objPremier->fields['titre']);
T::eq('un objet issu d\'une collection n\'est pas neuf', false, $objPremier->isnew());

$objCollection = article::get_collection();
$objCollection->add_where('id_workspace = %d', 99999);
T::eq('collection vide sur un critère sans résultat', 0, sizeof($objCollection->get_objects()));

T::section('clé composite');
$objLiaison = new liaison();
$objLiaison->fields['id_a'] = 1;
$objLiaison->fields['id_b'] = 2;
$objLiaison->fields['valeur'] = 'couple';
$mixCle = $objLiaison->save();
T::eq('save() retourne le couple de clés', array(1, 2), $mixCle);

$objLiaisonRelue = new liaison();
T::eq('open() avec les deux clés', true, $objLiaisonRelue->open(1, 2));
T::eq('valeur relue', 'couple', $objLiaisonRelue->fields['valeur']);
T::eq('open() avec une seule clé échoue', false, (new liaison())->open(1));

T::section('open_row');
$objRs = $objDb->query("SELECT * FROM `{$strTable}` WHERE `id` = '".intval($intId)."'");
$objDepuisLigne = new article();
$objDepuisLigne->open_row($objDb->fetchrow($objRs));
T::eq('hydratation sans requête supplémentaire', 'Titre modifié', $objDepuisLigne->fields['titre']);
T::eq('l\'objet hydraté n\'est pas neuf', false, $objDepuisLigne->isnew());

T::section('suppression');
$objDepuisLigne->delete();
T::eq('l\'enregistrement a disparu', false, (new article())->open($intId));

T::section('injection par les valeurs');
$objPiege = new article();
$objPiege->init_description();
unset($objPiege->fields['id']);
$objPiege->fields['titre'] = "x'); DROP TABLE `{$strTable}`; --";
$objPiege->save();
$arrCompte = $objDb->fetchrow($objDb->query("SELECT COUNT(*) AS nb FROM `{$strTable}`"));
T::chk('la table a survécu à la charge d\'injection', $arrCompte !== false && $arrCompte['nb'] > 0, 'la table a disparu');

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("DROP TABLE IF EXISTS `{$strTableCle}`");

T::bilan('06_data_object');
