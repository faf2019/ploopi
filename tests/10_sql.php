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
 * Tests du constructeur de requêtes et de l'échappement SQL (ploopi\sqlformat,
 * ploopi\query_*, ploopi\db). Ces tests sont la protection de première ligne contre
 * les injections SQL : le socle n'utilise aucune requête préparée (cf. doc/reference.md §7.3).
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
use ploopi\query_select;
use ploopi\query_insert;
use ploopi\query_update;
use ploopi\query_delete;
use ploopi\sqlformat;

$objDb = db::get();

T::section('db::addslashes');
T::eq('apostrophe échappée', "o\\'brien", $objDb->addslashes("o'brien"));
T::eq('antislash échappé', '\\\\', $objDb->addslashes('\\'));
T::eq('guillemet échappé', '\\"', $objDb->addslashes('"'));
T::eq('chaîne sans caractère spécial inchangée', 'bonjour', $objDb->addslashes('bonjour'));

/**
 * Identifiant d'un enregistrement pas encore créé (null) : partage, validation, GED et
 * abonnements d'un formulaire d'ajout l'échappent. addslashes(null) et
 * real_escape_string(null) sont obsolètes en PHP 8.1 (relevé sous Docker, erreurs
 * affichées) : la valeur nulle est traitée comme une chaîne vide.
 */
$arrObsoletes = array();
set_error_handler(function($intNum, $strMsg, $strFichier, $intLigne) use (&$arrObsoletes) {
    if ($intNum == E_DEPRECATED) $arrObsoletes[] = basename($strFichier).':'.$intLigne.' '.$strMsg;
    return true;
});
T::eq('null échappé en chaîne vide', '', $objDb->addslashes(null));
ploopi\documents::getid(1, null, 1);
restore_error_handler();
T::vrai('aucun avertissement d\'obsolescence pour un identifiant null', empty($arrObsoletes), implode(' | ', $arrObsoletes));

T::section('sqlformat : marqueurs de substitution');
T::eq(
    '%d force un entier',
    'id = 12',
    sqlformat::replace(array('rawsql' => 'id = %d', 'values' => array('12 OR 1=1')))
);
T::eq(
    '%d sur une chaîne non numérique donne 0',
    'id = 0',
    sqlformat::replace(array('rawsql' => 'id = %d', 'values' => array('DROP TABLE x')))
);
T::eq(
    '%f force un flottant',
    'prix > 3.5',
    sqlformat::replace(array('rawsql' => 'prix > %f', 'values' => array('3.5abc')))
);
T::eq(
    '%s quote ET échappe (ne pas ajouter soi-même les apostrophes)',
    "nom = 'o\\'brien'",
    sqlformat::replace(array('rawsql' => 'nom = %s', 'values' => array("o'brien")))
);
T::eq(
    '%e produit une liste d\'entiers',
    'id IN (1,2,3)',
    sqlformat::replace(array('rawsql' => 'id IN (%e)', 'values' => array(array(1, '2x', 3))))
);
T::eq(
    '%t produit une liste de chaînes échappées',
    "nom IN ('a','b\\'c')",
    sqlformat::replace(array('rawsql' => 'nom IN (%t)', 'values' => array(array('a', "b'c"))))
);
T::eq(
    '%g produit une liste de flottants',
    'prix IN (1.5,2)',
    sqlformat::replace(array('rawsql' => 'prix IN (%g)', 'values' => array(array('1.5', '2zz'))))
);
T::eq(
    'marqueurs positionnels',
    "a = 'deux' AND b = 'un'",
    sqlformat::replace(array('rawsql' => 'a = %2$s AND b = %1$s', 'values' => array('un', 'deux')))
);
/**
 * %r insère la valeur SANS aucun traitement. C'est un point d'injection : il ne doit
 * recevoir que du SQL construit par le code, jamais une donnée venue de l'utilisateur.
 */
T::eq(
    '%r insère la valeur telle quelle (marqueur brut)',
    'x = DROP TABLE t',
    sqlformat::replace(array('rawsql' => 'x = %r', 'values' => array('DROP TABLE t')))
);

T::eq(
    '%% produit un % littéral sans consommer de paramètre',
    "label LIKE 'a%super%' AND id = 5",
    sqlformat::replace(array('rawsql' => "label LIKE 'a%%super%%' AND id = %d", 'values' => array(5)))
);
/**
 * La virgule figurait dans la classe de caractères de la regex ([s,d,f,…]) : « %, »
 * était lu comme un marqueur et remplacé par une valeur brute.
 */
T::eq(
    '%, n\'est pas un marqueur',
    "FIND_IN_SET('a', '1%,2')",
    sqlformat::replace(array('rawsql' => "FIND_IN_SET(%s, '1%%,2')", 'values' => array('a')))
);
T::eq(
    '%, laissé tel quel',
    "x = 'a' AND y LIKE '1%,2'",
    sqlformat::replace(array('rawsql' => "x = %s AND y LIKE '1%,2'", 'values' => array('a')))
);

T::section('query_select');
$objQuery = new query_select();
$objQuery->add_select('e.*');
$objQuery->add_from('ploopi_module e');
$objQuery->add_where('e.id = %d', 1);
T::eq('requête simple', 'SELECT e.* FROM ploopi_module e WHERE e.id = 1', $objQuery->get_sql());

$objQuery->add_where('e.label = %s', "o'brien");
T::contient('les clauses WHERE sont combinées par AND', "AND e.label = 'o\\'brien'", $objQuery->get_sql());

$objQuery->add_orderby('e.id DESC');
$objQuery->add_groupby('e.id');
$objQuery->add_having('COUNT(*) > %d', 1);
$strSql = $objQuery->get_sql();
T::contient('GROUP BY', 'GROUP BY e.id', $strSql);
T::contient('HAVING échappé', 'HAVING COUNT(*) > 1', $strSql);
T::contient('ORDER BY', 'ORDER BY e.id DESC', $strSql);

T::eq('sans clause FROM, aucune requête n\'est produite', '', (new query_select())->get_sql());

// dbreport propose une jointure RIGHT : add_rightjoin() n'existait pas (erreur fatale)
$objQuery = new query_select();
$objQuery->add_from('a');
$objQuery->add_leftjoin('b ON b.id = a.id_b');
$objQuery->add_rightjoin('c ON c.id = a.id_c AND c.x = %d', 3);
T::eq('RIGHT JOIN après LEFT JOIN', 'SELECT * FROM a LEFT JOIN b ON b.id = a.id_b RIGHT JOIN c ON c.id = a.id_c AND c.x = 3', $objQuery->get_sql());

T::section('query_select::add_limit');
$objQuery = new query_select();
$objQuery->add_select('*');
$objQuery->add_from('t');
$objQuery->add_limit('20, 10');
T::contient('offset et nombre de lignes', 'LIMIT 20, 10', $objQuery->get_sql());

$objQuery->add_limit('5; DROP TABLE t');
T::contient('la clause LIMIT est passée à intval', 'LIMIT 5', $objQuery->get_sql());
T::contientpas('aucune injection ne subsiste dans LIMIT', 'DROP', $objQuery->get_sql());

T::section('piège : une valeur tableau est une LISTE DE PARAMÈTRES');
/**
 * add_where($sql, $values) considère un tableau comme la liste des valeurs à
 * substituer, pas comme une valeur unique. Pour alimenter %e / %t / %g avec une
 * liste, il faut donc l'encapsuler : add_where('id IN (%e)', array($arrIds)).
 */
$objQuery = new query_select();
$objQuery->add_select('*');
$objQuery->add_from('t');
$objQuery->add_where('id IN (%e)', array(1, 2, 3));
T::contient('tableau non encapsulé : seul le 1er élément est utilisé', 'id IN (1)', $objQuery->get_sql());

$objQuery = new query_select();
$objQuery->add_select('*');
$objQuery->add_from('t');
$objQuery->add_where('id IN (%e)', array(array(1, 2, 3)));
T::contient('tableau encapsulé : la liste complète est utilisée', 'id IN (1,2,3)', $objQuery->get_sql());

T::section('query_insert / query_update / query_delete');
$objInsert = new query_insert();
$objInsert->set_table('t');
$objInsert->add_set('nom = %s', "o'brien");
$objInsert->add_set('n = %d', 3);
T::eq('INSERT', "INSERT INTO t SET nom = 'o\\'brien', n = 3", $objInsert->get_sql());

$objUpdate = new query_update();
$objUpdate->add_from('t');
$objUpdate->add_set('nom = %s', 'x');
$objUpdate->add_where('id = %d', 1);
T::eq('UPDATE', "UPDATE t SET nom = 'x' WHERE id = 1", $objUpdate->get_sql());

$objDelete = new query_delete();
$objDelete->add_from('t');
$objDelete->add_where('id = %d', 1);
T::contient('DELETE', 'FROM t WHERE id = 1', $objDelete->get_sql());

T::section('exécution réelle');
$strTable = PLOOPI_TESTS_PREFIXE.'sql';
$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("CREATE TABLE `{$strTable}` (`id` int(10) unsigned NOT NULL auto_increment, `label` varchar(255) default '', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$objInsert = new query_insert();
$objInsert->set_table($strTable);
$objInsert->add_set('label = %s', "O'Brien \"le grand\"");
$objInsert->execute();
$intId = $objDb->insertid();
T::vrai('insertion effectuée', $intId > 0, 'id obtenu : '.$intId);

$objQuery = new query_select();
$objQuery->add_select('*');
$objQuery->add_from($strTable);
$objQuery->add_where('id = %d', $intId);
$objRs = $objQuery->execute();
T::vrai('execute() retourne un recordset', $objRs instanceof ploopi\recordset);
T::eq('une ligne retournée', 1, $objRs->numrows());
$arrRow = $objRs->fetchrow();
T::eq(
    'la chaîne est restituée intacte malgré les apostrophes et guillemets',
    "O'Brien \"le grand\"",
    $arrRow['label']
);

// Tentative d'injection par la valeur : la table doit survivre.
$objInsert = new query_insert();
$objInsert->set_table($strTable);
$objInsert->add_set('label = %s', "x'); DROP TABLE `{$strTable}`; --");
$objInsert->execute();

$objQuery = new query_select();
$objQuery->add_select('COUNT(*) AS nb');
$objQuery->add_from($strTable);
$arrCompte = $objQuery->execute()->fetchrow();
T::eq('la charge d\'injection a été stockée comme une donnée', '2', $arrCompte['nb']);

$objUpdate = new query_update();
$objUpdate->add_from($strTable);
$objUpdate->add_set('label = %s', 'modifié');
$objUpdate->add_where('id = %d', $intId);
$objUpdate->execute();
$objQuery = new query_select();
$objQuery->add_select('label');
$objQuery->add_from($strTable);
$objQuery->add_where('id = %d', $intId);
$arrRow = $objQuery->execute()->fetchrow();
T::eq('mise à jour effectuée', 'modifié', $arrRow['label']);

$objDelete = new query_delete();
$objDelete->add_from($strTable);
$objDelete->add_where('id = %d', $intId);
$objDelete->execute();
$objQuery = new query_select();
$objQuery->add_select('COUNT(*) AS nb');
$objQuery->add_from($strTable);
$arrCompte = $objQuery->execute()->fetchrow();
T::eq('suppression effectuée', '1', $arrCompte['nb']);

T::section('nouveaux filtres : exécution réelle');
$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("CREATE TABLE `{$strTable}` (id int PRIMARY KEY, label varchar(255), actif int) DEFAULT CHARSET=utf8mb4");
$objDb->query("INSERT INTO `{$strTable}` VALUES (1, 'commun', 1), (2, 'commun', 1), (3, NULL, 0)");
$objQuery = new query_select();
$objQuery->add_from($strTable);
$objQuery->add_where('actif = %d', 1);
$objQuery->add_where_group(array(
    array('rawsql' => 'id = %d', 'values' => 1),
    array('rawsql' => 'id = %d', 'values' => 3),
));
T::eq('OR reste limité par le WHERE précédent', 1, $objQuery->execute()->numrows());
$objQuery->remove_where();
$objQuery->add_where_in('label', array("x') OR 1=1 --", 'commun'), 's');
T::eq('IN échappe une charge SQL', 2, $objQuery->execute()->numrows());
$objQuery->remove_where();
$objQuery->add_where_null('label');
T::eq('IS NULL sélectionne la ligne nulle', 1, $objQuery->execute()->numrows());
$objQuery->remove_where();
$objQuery->add_where_null('label', true);
$objQuery->add_select('label');
$objQuery->set_distinct();
T::eq('DISTINCT supprime les doublons', 1, $objQuery->execute()->numrows());
$objQuery->set_distinct(false);
T::eq('désactiver DISTINCT restitue les doublons', 2, $objQuery->execute()->numrows());
foreach (array('update', 'delete') as $strType)
{
    $strClass = 'ploopi\\query_'.$strType;
    $objMutation = new $strClass();
    $objMutation->add_from($strTable);
    if ($strType === 'update') $objMutation->add_set('actif = 9');
    $objMutation->add_where_in('id', array());
    $objMutation->execute();
    T::eq($strType.' IN vide ne touche aucune ligne', 0, $objMutation->affectedrows());
}
$objQuery = new query_select();
$objQuery->add_from($strTable);
$objQuery->add_where_in('id', array(), 'd', true);
T::eq('NOT IN vide conserve toutes les lignes', 3, $objQuery->execute()->numrows());

T::section('EXISTS corrélé, pagination et réutilisation : exécution réelle');
$strRelation = $strTable.'_relation';
$objDb->query("DROP TABLE IF EXISTS `{$strRelation}`");
$objDb->query("CREATE TABLE `{$strRelation}` (id int, label varchar(255)) DEFAULT CHARSET=utf8mb4");
$objRelation = new query_insert();
$objRelation->set_table($strRelation);
$objRelation->add_set('id = %d', 1);
$objRelation->add_set('label = %s', "O'Brien %d");
$objRelation->execute();
$objRelation->remove_set();
$objRelation->add_set('id = %d', 3);
$objRelation->add_set('label = %s', 'autre');
$objRelation->execute();
$objSubquery = new query_select();
$objSubquery->add_select('1');
$objSubquery->add_from($strRelation.' r');
$objSubquery->add_where('r.id = t.id');
$objSubquery->add_where('r.label = %s', "O'Brien %d");
$objQuery = new query_select();
$objQuery->add_select('t.id');
$objQuery->add_from($strTable.' t');
$objQuery->add_where_exists($objSubquery);
$objRs = $objQuery->execute();
T::eq('EXISTS corrélé trouve une ligne', 1, $objRs->numrows());
T::eq('EXISTS échappe les paramètres sans relire les pourcents', '1', $objRs->fetchrow()['id']);
$objQuery->remove_where();
$objQuery->add_where_not_exists($objSubquery);
T::eq('NOT EXISTS trouve le complément', 2, $objQuery->execute()->numrows());
$objSubquery->remove_where();
$objSubquery->add_where('r.id = t.id');
$objSubquery->add_where('r.label = %s', "x') OR 1=1 --");
$objQuery->remove_where();
$objQuery->add_where_exists($objSubquery);
T::eq('charge SQL dans EXISTS sans effet', 0, $objQuery->execute()->numrows());
$objSubquery->remove_where();
$objSubquery->add_where('r.id = t.id');
$objSubquery->add_where('r.id = %d', 1);
$objUpdate = new query_update();
$objUpdate->add_from($strTable.' t');
$objUpdate->add_set('actif = 7');
$objUpdate->add_where_exists($objSubquery);
$objUpdate->execute();
T::eq('UPDATE EXISTS ne touche que la ligne liée', 1, $objUpdate->affectedrows());
$objQuery->remove_where();
$objQuery->add_where('actif = %d', 7);
T::eq('valeur réellement modifiée par UPDATE EXISTS', '1', $objQuery->execute()->fetchrow()['id']);
$objDelete = new query_delete();
$objDelete->add_from($strTable.' t');
$objDelete->add_delete('t');
$objDelete->add_where_not_exists($objSubquery);
$objDelete->execute();
T::eq('DELETE NOT EXISTS retire les lignes sans relation', 2, $objDelete->affectedrows());
$objDelete->remove_where();
$objDelete->remove_delete();
$objDelete->remove_from();
$objDelete->add_from($strTable);
$objDelete->add_where('id = %d', 99);
$objDelete->execute();
T::eq('DELETE réutilisé après retrait des cibles et tables', 0, $objDelete->affectedrows());
$objDb->query("INSERT INTO `{$strTable}` VALUES (2, 'commun', 1), (3, NULL, 0)");
$objQuery->remove_where();
$objQuery->add_orderby('t.id');
$objQuery->add_pagination(2, 2);
$objRs = $objQuery->execute();
T::eq('dernière page partielle', 1, $objRs->numrows());
T::eq('offset de la deuxième page appliqué', '3', $objRs->fetchrow()['id']);
$objQuery->add_pagination(3, 2);
T::eq('page au-delà des résultats vide', 0, $objQuery->execute()->numrows());
$objQuery->remove_limit();
T::eq('remove_limit restitue toutes les lignes', 3, $objQuery->execute()->numrows());
$objDb->query("DROP TABLE IF EXISTS `{$strRelation}`");

T::section('recordset::getcsv');
/**
 * Régression surveillée : getcsv() construisait ses fonctions d'échappement avec
 * create_function(), supprimée en PHP 8.0. Tout appel y provoquait une erreur fatale
 * « Call to undefined function create_function() ».
 */
$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("CREATE TABLE `{$strTable}` (`id` int(10) unsigned NOT NULL auto_increment, `label` varchar(255) default '', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
$objInsert = new query_insert();
$objInsert->set_table($strTable);
$objInsert->add_set('label = %s', 'Dupont');
$objInsert->execute();
$objInsert = new query_insert();
$objInsert->set_table($strTable);
$objInsert->add_set('label = %s', "O'Brien \"le grand\"");
$objInsert->execute();

$objQuery = new query_select();
$objQuery->add_select('label');
$objQuery->add_from($strTable);
$objQuery->add_orderby('id');
$strCsv = $objQuery->execute()->getcsv();

T::vrai('un CSV est produit', is_string($strCsv) && $strCsv !== '', var_export($strCsv, true));
$arrLignes = array_values(array_filter(explode("\n", $strCsv), 'strlen'));
T::eq('ligne d\'en-tête', '"label"', $arrLignes[0]);
T::eq('valeur simple encadrée', '"Dupont"', $arrLignes[1]);
T::eq('les guillemets du contenu sont doublés', '"O\'Brien ""le grand"""', $arrLignes[2]);

$strCsv = $objQuery->execute()->getcsv(array('booHeader' => false, 'strTextSep' => '', 'strFieldSep' => ';'));
T::contientpas('sans en-tête sur demande', 'label', $strCsv);
T::contient('contenu présent sans encadrement', 'Dupont', $strCsv);

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");

T::section('db::split_sql');
$arrRequetes = db::split_sql("SELECT 1;\nSELECT 'a;b';\n");
T::eq('première requête isolée', 'SELECT 1;', $arrRequetes[0]);
T::eq('un point-virgule entre apostrophes ne coupe pas la requête', "SELECT 'a;b';", $arrRequetes[1]);
// Le reliquat après le dernier point-virgule est conservé tel quel (ici, un saut de ligne).
T::eq('le reliquat de fin est conservé', 3, sizeof($arrRequetes));
$arrRequetes = db::split_sql("SELECT 1 /* a ; it's */ ;\n-- b ; c\nSELECT 4/2 - 1 # d ; e\n;\nSELECT \"f;g\", 'h\\';i';");
T::eq('commentaires, tirets, barres obliques et échappements', array("SELECT 1 /* a ; it's */ ;", "-- b ; c\nSELECT 4/2 - 1 # d ; e\n;", "SELECT \"f;g\", 'h\\';i';"), array_slice($arrRequetes, 0, 3));
// Longue requête (métabase du socle) : sans groupe possessif, la pile JIT de PCRE s'épuisait et le découpage rendait un tableau vide.
$strLongue = 'INSERT INTO t VALUES '.implode(', ', array_fill(0, 20000, "('ploopi_user', 'champ', 'int(10) unsigned', 1)")).';';
T::eq('une très longue requête est découpée', array($strLongue), array_slice(db::split_sql($strLongue."\nSELECT 1;"), 0, 1));
T::eq('sans erreur PCRE', PREG_NO_ERROR, preg_last_error());

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");

exit(T::bilan('10_sql'));
