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
 * les injections SQL : le socle n'utilise aucune requête préparée (cf. CLAUDE.md §7.3).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();
exiger_bdd('05_sql');

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
$strTable = _PLOOPI_TESTS_PREFIXE.'sql';
$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
$objDb->query("CREATE TABLE `{$strTable}` (`id` int(10) unsigned NOT NULL auto_increment, `label` varchar(255) default '', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");

$objInsert = new query_insert();
$objInsert->set_table($strTable);
$objInsert->add_set('label = %s', "O'Brien \"le grand\"");
$objInsert->execute();
$intId = $objDb->insertid();
T::chk('insertion effectuée', $intId > 0, 'id obtenu : '.$intId);

$objQuery = new query_select();
$objQuery->add_select('*');
$objQuery->add_from($strTable);
$objQuery->add_where('id = %d', $intId);
$objRs = $objQuery->execute();
T::chk('execute() retourne un recordset', $objRs instanceof ploopi\recordset);
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

T::section('db::split_sql');
$arrRequetes = db::split_sql("SELECT 1;\nSELECT 'a;b';\n");
T::eq('première requête isolée', 'SELECT 1;', $arrRequetes[0]);
T::eq('un point-virgule entre apostrophes ne coupe pas la requête', "SELECT 'a;b';", $arrRequetes[1]);
// Le reliquat après le dernier point-virgule est conservé tel quel (ici, un saut de ligne).
T::eq('le reliquat de fin est conservé', 3, sizeof($arrRequetes));

$objDb->query("DROP TABLE IF EXISTS `{$strTable}`");

T::bilan('05_sql');
