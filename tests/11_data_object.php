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

require __DIR__.'/bootstrap.php';

amorcer_socle();
exiger_bdd();

use ploopi\db;

$objDb = db::get();
$strTable = PLOOPI_TESTS_PREFIXE.'article';
$strTableCle = PLOOPI_TESTS_PREFIXE.'liaison';

/**
 * Objet métier de test, sur le modèle décrit dans doc/reference.md §7.2.
 */
class article extends ploopi\data_object
{
    public function __construct() { parent::__construct(PLOOPI_TESTS_PREFIXE.'article'); }
}

/**
 * Objet métier de test à clé composite.
 */
class liaison extends ploopi\data_object
{
    public function __construct() { parent::__construct(PLOOPI_TESTS_PREFIXE.'liaison', array('id_a', 'id_b')); }
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
);

$objDb->query("DROP TABLE IF EXISTS `{$strTableCle}`");
$objDb->query(
    "CREATE TABLE `{$strTableCle}` (
        `id_a` int(10) unsigned NOT NULL default '0',
        `id_b` int(10) unsigned NOT NULL default '0',
        `valeur` varchar(50) default '',
        PRIMARY KEY (`id_a`, `id_b`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
);

T::section('construction');
$objArticle = new article();
T::eq('un objet neuf est marqué comme nouveau', true, $objArticle->isnew());
T::eq('nom de table', PLOOPI_TESTS_PREFIXE.'article', $objArticle->gettablename());

T::section('init_description');
$objArticle->init_description();
T::eq('les valeurs par défaut de la table sont reprises', '7', $objArticle->fields['note']);
T::eq('un champ sans défaut vaut la chaîne vide', '', $objArticle->fields['titre']);
// La clé auto-incrémentée est laissée à null : c'est le serveur qui la renseigne.
T::eq('la clé primaire est laissée à null', null, $objArticle->fields['id']);

T::section('insertion après init_description, quel que soit le mode SQL');
/**
 * Régression surveillée.
 *
 * init_description() pré-remplissait toute colonne sans défaut déclaré avec '',
 * y compris la clé auto-incrémentée : save() émettait « INSERT ... SET `id` = '' »,
 * que tout serveur en mode strict — STRICT_TRANS_TABLES, défaut depuis MySQL 5.7 et
 * MariaDB 10.2 — rejette. L'enchaînement recommandé init_description() puis save()
 * était donc inopérant sur un serveur récent.
 *
 * Le contrôle s'exécute dans les deux modes : l'insertion doit aboutir sans que
 * l'appelant ait à toucher à la clé.
 */
$arrModeSql = $objDb->fetchrow($objDb->query('SELECT @@sql_mode AS mode'));
T::vrai('mode SQL du serveur relevé', !empty($arrModeSql), 'SELECT @@sql_mode sans résultat');
T::chk('mode strict actif', strpos($arrModeSql['mode'], 'STRICT_') !== false, $arrModeSql['mode']);

$objAuto = new article();
$objAuto->init_description();
T::eq('la clé auto-incrémentée est laissée à null', null, $objAuto->fields['id']);
T::eq('une colonne numérique garde son défaut déclaré', '7', $objAuto->fields['note']);
T::eq('une colonne texte sans défaut vaut la chaîne vide', '', $objAuto->fields['titre']);

$objAuto->fields['titre'] = 'Insertion sans toucher à la clé';
$mixCle = $objAuto->save();
T::vrai('l\'insertion aboutit', intval($mixCle) > 0, 'clé obtenue : '.var_export($mixCle, true).', erreur : '.$objDb->get_last_error());

$objRelecture = new article();
T::eq('l\'enregistrement est relisible par sa clé', true, $objRelecture->open($mixCle));
T::eq('contenu conforme', 'Insertion sans toucher à la clé', $objRelecture->fields['titre']);
$objDb->query("DELETE FROM `{$strTable}`");

T::section('insertion');
$objArticle = new article();
$objArticle->init_description();
$objArticle->fields['titre'] = "L'été \"chaud\"";
$objArticle->fields['contenu'] = 'Texte de test';
$intId = $objArticle->save();
T::vrai('save() retourne la clé générée', intval($intId) > 0, 'clé obtenue : '.var_export($intId, true));
T::eq('l\'objet n\'est plus neuf', false, $objArticle->isnew());

T::section('valeurs booléennes');
/**
 * Régression surveillée : save() rendait un booléen PHP par conversion implicite en
 * chaîne — '1' pour true, mais la CHAÎNE VIDE pour false, qu'un serveur en mode strict
 * refuse sur une colonne numérique. Tout champ affecté à false était donc impossible à
 * enregistrer.
 */
$objBool = new article();
$objBool->init_description();
$objBool->fields['titre'] = 'Booleens';
$objBool->fields['note'] = true;
$intIdBool = $objBool->save();
T::vrai('insertion avec un booleen true', intval($intIdBool) > 0, 'erreur : '.$objDb->get_last_error());

$objRelu = new article();
$objRelu->open($intIdBool);
T::eq('true est enregistré comme 1', '1', $objRelu->fields['note']);

$objRelu->fields['note'] = false;
$objRelu->save();
T::eq('mise à jour avec false : aucune erreur SQL', '', $objDb->get_last_error());

$objVerifBool = new article();
$objVerifBool->open($intIdBool);
T::eq('false est enregistré comme 0', '0', $objVerifBool->fields['note']);

$objVerifBool->delete();

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
 * doc/reference.md §17 décrit ce cas comme « fields reste à false » : c'est inexact pour un
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
T::vrai('la collection rend des objets de la bonne classe', $objPremier instanceof article);
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
$objPiege->fields['titre'] = "x'); DROP TABLE `{$strTable}`; --";
$objPiege->save();
$arrCompte = $objDb->fetchrow($objDb->query("SELECT COUNT(*) AS nb FROM `{$strTable}`"));
T::vrai('la table a survécu à la charge d\'injection', $arrCompte !== false && $arrCompte['nb'] > 0, 'la table a disparu');

T::section('mise à jour partielle');
$objPartiel = new article();
$objPartiel->init_description();
$objPartiel->fields['titre'] = 'Partiel';
$objPartiel->fields['contenu'] = 'Contenu initial';
$intIdPartiel = $objPartiel->save();

$objEdite = new article();
$objEdite->open($intIdPartiel);
// Modification concurrente d'une autre colonne, entre la lecture et l'enregistrement.
$objDb->query("UPDATE `{$strTable}` SET `contenu` = 'Contenu concurrent' WHERE `id` = ".intval($intIdPartiel));
$objEdite->fields['titre'] = 'Partiel modifié';
$objEdite->save();
T::vrai('seul le champ modifié est écrit', strpos($objEdite->getsql(), '`contenu`') === false && strpos($objEdite->getsql(), '`titre`') !== false, $objEdite->getsql());
$objVerif = new article();
$objVerif->open($intIdPartiel);
T::eq('le champ modifié est persisté', 'Partiel modifié', $objVerif->fields['titre']);
T::eq('la modification concurrente d\'un autre champ est conservée', 'Contenu concurrent', $objVerif->fields['contenu']);

$objVerif->fields['note'] = intval($objVerif->fields['note']);
$strSqlAvant = $objVerif->getsql();
$objVerif->save();
T::eq('sans modification réelle (7 / \'7\'), aucune requête n\'est émise', $strSqlAvant, $objVerif->getsql());

$objVerif->fields['note'] = 9;
$objVerif->save();
$objVerif->fields['note'] = 9;
$objVerif->fields['titre'] = 'Après un premier save()';
$objVerif->save();
T::vrai('après un save(), un champ déjà enregistré n\'est pas réécrit', strpos($objVerif->getsql(), '`note`') === false && strpos($objVerif->getsql(), '`titre`') !== false, $objVerif->getsql());

$objVerif->fields['note'] = 0;
$objVerif->save();
$objVerif->fields['note'] = false;
$objVerif->fields['titre'] = 'Booléen inchangé';
$objVerif->save();
T::vrai('false / 0 : un booléen égal à la valeur en base n\'est pas réécrit', strpos($objVerif->getsql(), '`note`') === false && strpos($objVerif->getsql(), '`titre`') !== false, $objVerif->getsql());
$objVerif->fields['note'] = true;
$objVerif->save();
T::vrai('true / 0 : un booléen différent est écrit', strpos($objVerif->getsql(), '`note` = 1') !== false, $objVerif->getsql());

$arrLigne = $objDb->fetchrow($objDb->query("SELECT a.*, 'jointure' AS `colonne_jointe` FROM `{$strTable}` a WHERE a.`id` = ".intval($intIdPartiel)));
$objJoint = new article();
$objJoint->open_row($arrLigne);
$objJoint->fields['titre'] = 'Depuis une jointure';
$objJoint->save();
T::vrai('open_row() sur une jointure : la colonne étrangère n\'est pas écrite', strpos($objJoint->getsql(), 'colonne_jointe') === false, $objJoint->getsql());
$objVerif = new article();
$objVerif->open($intIdPartiel);
T::eq('open_row() sur une jointure : la mise à jour aboutit', 'Depuis une jointure', $objVerif->fields['titre']);

$objManuel = new article();
$objManuel->fields = array('id' => $intIdPartiel, 'titre' => 'Rempli à la main');
$objManuel->new = false;
$objManuel->save();
$objVerif = new article();
$objVerif->open($intIdPartiel);
T::eq('objet rempli à la main (new = false) : écrit en entier, par sa clé', 'Rempli à la main', $objVerif->fields['titre']);

T::section('échec d\'insertion');
$objEchec = new article();
$objEchec->init_description();
$objEchec->fields['note'] = 'pas un entier';
$mixRetour = @$objEchec->save();
T::chk('mode strict : l\'insertion est refusée', $mixRetour === false, var_export($mixRetour, true));
if ($mixRetour === false) T::eq('après un échec, l\'objet reste neuf', true, $objEchec->isnew());

T::section('clé de l\'enregistrement');
$objA = new article();
$objA->init_description();
$objA->fields['titre'] = 'A';
$intIdA = $objA->save();
$objB = new article();
$objB->init_description();
$objB->fields['titre'] = 'B';
$intIdB = $objB->save();

$objSuppr = new article();
$objSuppr->open($intIdA);
$objSuppr->fields['id'] = $intIdB;
$objSuppr->delete();
T::eq('delete() vise la clé lue, pas un champ de clé modifié', false, (new article())->open($intIdA));
T::eq('l\'autre enregistrement est intact', true, (new article())->open($intIdB));

$objReutil = new article();
$objReutil->open($intIdB);
$objReutil->open(999999);
T::eq('un objet réutilisé redevient neuf si la clé est absente', true, $objReutil->isnew());

$objCle = new article();
$objCle->open($intIdB);
$intNouvelleCle = $intIdB + 1000;
$objCle->fields['id'] = $intNouvelleCle;
$objCle->save();
$objCle->fields['titre'] = 'B déplacé';
$objCle->save();
$objVerif = new article();
T::eq('après un changement de clé, les mises à jour suivent la nouvelle clé', true, $objVerif->open($intNouvelleCle) && $objVerif->fields['titre'] == 'B déplacé');

T::section('cache de DESCRIBE');
$objDesc = new article();
$objDesc->init_description();
$objDb->query("ALTER TABLE `{$strTable}` ADD `ajoutee` varchar(20) NOT NULL default 'neuve'");
$objDesc = new article();
$objDesc->init_description();
T::eq('une colonne ajoutée par ALTER TABLE est vue par init_description()', 'neuve', isset($objDesc->fields['ajoutee']) ? $objDesc->fields['ajoutee'] : null);

// Ordre précédé d'un commentaire, comme le premier ordre d'un fichier découpé par split_sql().
$objDb->query("-- mise à jour\n/* commentaire */ ALTER TABLE `{$strTable}` ADD `apres_commentaire` varchar(20) NOT NULL default 'vue'");
$objDesc = new article();
$objDesc->init_description();
T::eq('un ALTER précédé de commentaires vide le cache', 'vue', isset($objDesc->fields['apres_commentaire']) ? $objDesc->fields['apres_commentaire'] : null);

$objDb->multiplequeries("SELECT 1; ALTER TABLE `{$strTable}` ADD `apres_point_virgule` varchar(20) NOT NULL default 'vue'");
$objDesc = new article();
$objDesc->init_description();
T::eq('multiplequeries() : un ALTER après « ; » sur la même ligne vide le cache', 'vue', isset($objDesc->fields['apres_point_virgule']) ? $objDesc->fields['apres_point_virgule'] : null);

$objCache = new ReflectionProperty('ploopi\data_object', 'arrDescriptions');
$objCache->setAccessible(true);
$objDb->query("INSERT INTO `{$strTable}` SET `titre` = 'x', `contenu` = 'Ligne 1\nCreate et Drop en début de ligne'");
T::vrai('un contenu dont une ligne commence par « Create » ne vide pas le cache', !empty($objCache->getValue()));

T::section('injection par les noms de champs');
/**
 * Régression surveillée : les clés de setvalues($_POST, 'prefixe_') deviennent des noms
 * de colonnes, et security::filtervar() ne nettoie que les valeurs. Une clé contenant un
 * accent grave sortait du nom de colonne et injectait du SQL dans l'INSERT ou l'UPDATE.
 */
$strClePiege = "titre`=(SELECT/**/'pirate'),`contenu";
$objPiege = new article();
$objPiege->init_description();
$objPiege->setvalues(array('art_'.$strClePiege => 'x', 'art_titre' => 'Saisie normale'), 'art_');
T::eq('setvalues() ignore une clé qui ne peut pas être un nom de colonne', false, isset($objPiege->fields[$strClePiege]));
T::eq('setvalues() reprend les clés valides', 'Saisie normale', $objPiege->fields['titre']);

$objPiege->fields[$strClePiege] = 'x';
T::vrai('un nom de champ affecté directement reste un seul identifiant', strpos($objPiege->dump(), '`titre``=(SELECT/**/\'pirate\'),``contenu`') !== false, $objPiege->dump());
/**
 * Même charge exécutée pour de bon, avec exfiltration de ploopi_user : setvalues()
 * l'écarte désormais, on l'affecte donc directement dans fields pour éprouver
 * l'échappement du nom de colonne lui-même. La clé reste une colonne (inexistante),
 * l'injection ne s'exécute pas.
 */
$objPiege = new article();
$objPiege->init_description();
$strClePiege = "titre`=(SELECT(GROUP_CONCAT(login,0x3a,password))FROM(ploopi_user)),`contenu";
$objPiege->fields[$strClePiege] = 'injic';
$objPiege->fields['titre'] = 'normal';
@$objPiege->save();
T::contient('le backtick du nom de champ est doublé dans le SQL', '``=(SELECT', $objPiege->getsql());
$arrDerniere = $objDb->fetchrow($objDb->query("SELECT titre FROM `{$strTable}` ORDER BY id DESC LIMIT 1"));
T::contientpas('aucun identifiant exfiltré dans la colonne titre', ':', $arrDerniere === false ? '' : (string)$arrDerniere['titre']);

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("DROP TABLE IF EXISTS `{$strTableCle}`");

exit(T::bilan('11_data_object'));
