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
 * Tests du SQL généré sans serveur : connexion factice limitée à l'échappement.
 * Les tests d'exécution avec mysqli restent dans 10_sql.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
amorcer_socle();

$objDb = new class {
    public function addslashes($strValue)
    {
        return str_replace(array("\\", "'"), array("\\\\", "\\'"), $strValue);
    }
};

T::section('Compatibilité du SQL existant');
foreach (array('select', 'update', 'delete') as $strType)
{
    $strClass = 'ploopi\\query_'.$strType;
    $objQuery = new $strClass($objDb);
    $objQuery->add_from('t');
    if ($strType === 'update') $objQuery->add_set('label = %s', 'ancien');
    $strPrefix = array('select' => 'SELECT *', 'update' => "UPDATE t SET label = 'ancien'", 'delete' => 'DELETE ');
    $strBase = $strType === 'update' ? $strPrefix[$strType] : $strPrefix[$strType].' FROM t';
    T::eq($strType.' sans nouvelle option', $strBase, $objQuery->get_sql());
    $objQuery->add_where('actif = %d', 1);
    $objQuery->add_where_group(array(
        array('rawsql' => 'id = %1$d', 'values' => 0),
        array('rawsql' => 'label = %1$s', 'values' => "O'Brien %d"),
    ));
    T::eq($strType.' groupe OR et marqueurs indépendants', $strBase." WHERE actif = 1 AND ((id = 0) OR (label = 'O\\'Brien %d'))", $objQuery->get_sql());
    T::eq($strType.' génération répétable', $objQuery->get_sql(), $objQuery->get_sql());
    $objQuery->remove_where();
    $objQuery->add_where_group(array(
        array('rawsql' => 'a = 1 OR b = 2'),
        array('rawsql' => 'c = %d', 'values' => 3),
    ), 'and');
    T::eq($strType.' priorité des conditions conservée', $strBase.' WHERE ((a = 1 OR b = 2) AND (c = 3))', $objQuery->get_sql());
    $objQuery->remove_where();
    $objQuery->add_where_in('id', array('x' => 1, 'y' => '2 OR 1=1'));
    $objQuery->add_where_in('prix', array(1.5, 2), 'f', true);
    $objQuery->add_where_in('label', array("O'Brien", '%s'), 's');
    $objQuery->add_where_null('fin');
    $objQuery->add_where_null('debut', true);
    T::eq($strType.' listes typées et NULL', $strBase." WHERE id IN (1,2) AND prix NOT IN (1.5,2) AND label IN ('O\\'Brien','%s') AND fin IS NULL AND debut IS NOT NULL", $objQuery->get_sql());
    $objQuery->remove_where();
    $objQuery->add_where_in('id', array());
    T::eq($strType.' IN vide ne sélectionne rien', $strBase.' WHERE 1 = 0', $objQuery->get_sql());
    $objQuery->remove_where();
    $objQuery->add_where_in('id', array(), 's', true);
    T::eq($strType.' NOT IN vide ne restreint pas', $strBase.' WHERE 1 = 1', $objQuery->get_sql());
    $objQuery->remove_where();
    T::eq($strType.' remove_where retire les nouveaux filtres', $strBase, $objQuery->get_sql());
}

T::section('Validation atomique des nouveaux filtres');
$objQuery = new ploopi\query_select($objDb);
$objQuery->add_from('t');
$arrInvalides = array(
    'groupe vide' => function() use ($objQuery) { $objQuery->add_where_group(array()); },
    'opérateur SQL arbitraire' => function() use ($objQuery) { $objQuery->add_where_group(array(array('rawsql' => '1')), 'OR 1=1 --'); },
    'condition invalide après une condition valide' => function() use ($objQuery) { $objQuery->add_where_group(array(array('rawsql' => '1'), array('rawsql' => ' '))); },
    'type IN brut refusé' => function() use ($objQuery) { $objQuery->add_where_in('id', array(1), 'r'); },
    'type invalide même pour une liste vide' => function() use ($objQuery) { $objQuery->add_where_in('id', array(), 'r'); },
    'NULL dans IN refusé' => function() use ($objQuery) { $objQuery->add_where_in('id', array(null)); },
    'tableau dans IN refusé' => function() use ($objQuery) { $objQuery->add_where_in('nom', array('a', array('b')), 's'); },
);
foreach ($arrInvalides as $strLabel => $fnEssai)
{
    $booRefuse = false;
    try { $fnEssai(); } catch (InvalidArgumentException $e) { $booRefuse = true; }
    T::vrai($strLabel, $booRefuse);
    T::eq('requête inchangée après '.$strLabel, 'SELECT * FROM t', $objQuery->get_sql());
}

T::section('SELECT DISTINCT');
$objQuery->set_distinct();
T::eq('DISTINCT sur étoile', 'SELECT DISTINCT * FROM t', $objQuery->get_sql());
$objQuery->add_select('label');
$objQuery->add_orderby('label');
$objQuery->add_limit('2');
T::eq('DISTINCT conserve colonnes et autres clauses', 'SELECT DISTINCT label FROM t ORDER BY label LIMIT 2', $objQuery->get_sql());
$objQuery->set_distinct(false);
T::eq('désactivation réversible', 'SELECT label FROM t ORDER BY label LIMIT 2', $objQuery->get_sql());

T::section('EXISTS et NOT EXISTS');
foreach (array('select', 'update', 'delete') as $strType)
{
    $strClass = 'ploopi\\query_'.$strType;
    $objQuery = new $strClass($objDb);
    $objQuery->add_from('t');
    if ($strType === 'update') $objQuery->add_set('actif = 1');
    $strBase = $objQuery->get_sql();
    $objSubquery = new ploopi\query_select($objDb);
    $objSubquery->add_select('1');
    $objSubquery->add_from('r');
    $objSubquery->add_where('r.id = t.id');
    $objSubquery->add_where('r.label = %s', "O'Brien %d");
    $objQuery->add_where('t.actif = %d', 1);
    $objQuery->add_where_exists($objSubquery);
    $strExpected = $strBase." WHERE t.actif = 1 AND EXISTS (SELECT 1 FROM r WHERE r.id = t.id AND r.label = 'O\\'Brien %d')";
    T::eq($strType.' sous-requête corrélée et paramètres', $strExpected, $objQuery->get_sql());
    $objSubquery->remove_where();
    T::eq($strType.' sous-requête capturée à l’ajout', $strExpected, $objQuery->get_sql());
    T::eq($strType.' génération répétable', $strExpected, $objQuery->get_sql());
    $objQuery->remove_where();
    $objQuery->add_where_not_exists($objSubquery);
    T::eq($strType.' NOT EXISTS', $strBase.' WHERE NOT EXISTS (SELECT 1 FROM r)', $objQuery->get_sql());
    $objQuery->remove_where();
    T::eq($strType.' remove_where retire EXISTS', $strBase, $objQuery->get_sql());
    $booRefuse = false;
    try { $objQuery->add_where_exists(new ploopi\query_select($objDb)); }
    catch (InvalidArgumentException $e) { $booRefuse = true; }
    T::vrai($strType.' sous-requête vide refusée', $booRefuse);
    T::eq($strType.' refus sans modification', $strBase, $objQuery->get_sql());
}
$objSubquery = new ploopi\query_select($objDb);
$objSubquery->add_from('r');
$objSubquery->add_where('label = %s', 'valeur');
$objQuery = new ploopi\query_select($objDb);
$objQuery->add_from('t');
$objQuery->add_where_exists($objSubquery);
$objQuery->set_db(new class {
    public function addslashes($strValue) { return 'autre_'.$strValue; }
});
T::eq('EXISTS utilise la connexion parente courante', "SELECT * FROM t WHERE EXISTS (SELECT * FROM r WHERE label = 'autre_valeur')", $objQuery->get_sql());
T::eq('connexion de la sous-requête originale préservée', "SELECT * FROM r WHERE label = 'valeur'", $objSubquery->get_sql());
$objNested = new ploopi\query_select($objDb);
$objNested->add_from('s');
$objSubquery->add_where_not_exists($objNested);
$objQuery->remove_where();
$objQuery->set_db($objDb);
$objQuery->add_where_exists($objSubquery);
T::eq('EXISTS imbriqué', "SELECT * FROM t WHERE EXISTS (SELECT * FROM r WHERE label = 'valeur' AND NOT EXISTS (SELECT * FROM s))", $objQuery->get_sql());

T::section('Pagination SELECT');
$objQuery = new ploopi\query_select($objDb);
$objQuery->add_from('t');
$objQuery->add_orderby('id');
$objQuery->add_pagination(1, 20);
T::eq('première page depuis zéro', 'SELECT * FROM t ORDER BY id LIMIT 0, 20', $objQuery->get_sql());
$objQuery->add_pagination('3', '20');
T::eq('page et taille sous forme de chaînes', 'SELECT * FROM t ORDER BY id LIMIT 40, 20', $objQuery->get_sql());
$arrInvalides = array(array(0, 20), array(-1, 20), array(1, 0), array(1, -1), array(1.5, 20), array(true, 20), array(null, 20), array('2 OR 1=1', 20), array(1, '3.2'), array((string)PHP_INT_MAX.'0', 1), array(PHP_INT_MAX, 2));
foreach ($arrInvalides as $intIndex => $arrArgs)
{
    $booRefuse = false;
    try { $objQuery->add_pagination(...$arrArgs); } catch (InvalidArgumentException $e) { $booRefuse = true; }
    T::vrai('pagination invalide refusée n°'.$intIndex, $booRefuse);
    T::eq('limite intacte après refus n°'.$intIndex, 'SELECT * FROM t ORDER BY id LIMIT 40, 20', $objQuery->get_sql());
}
$objQuery->add_pagination(PHP_INT_MAX, 1);
T::eq('grand décalage entier exact', 'SELECT * FROM t ORDER BY id LIMIT '.(PHP_INT_MAX - 1).', 1', $objQuery->get_sql());
$objQuery->add_limit('5');
T::eq('add_limit remplace la pagination', 'SELECT * FROM t ORDER BY id LIMIT 5', $objQuery->get_sql());
$objQuery->remove_limit();
T::eq('remove_limit retire la pagination', 'SELECT * FROM t ORDER BY id', $objQuery->get_sql());
T::vrai('pagination réservée aux SELECT', !method_exists('ploopi\query_update', 'add_pagination') && !method_exists('ploopi\query_delete', 'add_pagination'));

T::section('Retrait et réutilisation des clauses');
foreach (array('select', 'insert', 'update', 'delete') as $strType)
{
    $strClass = 'ploopi\\query_'.$strType;
    $objQuery = new $strClass($objDb);
    if ($strType === 'insert') { $objQuery->set_table('t'); $objQuery->add_set('id = %d', 1); }
    else $objQuery->add_from('t');
    if ($strType === 'update') $objQuery->add_set('id = %d', 1);
    $strBase = $objQuery->get_sql();
    $objQuery->add_raw(' /* %s */', 'marqueur');
    T::contient($strType.' clause brute ajoutée', " /* 'marqueur' */", $objQuery->get_sql());
    $objQuery->remove_raw();
    $objQuery->remove_raw();
    T::eq($strType.' remove_raw idempotent', $strBase, $objQuery->get_sql());
    if ($strType === 'insert')
    {
        $objQuery->remove_set();
        $objQuery->remove_set();
        $objQuery->add_set('label = %s', 'neuf');
        T::eq('INSERT réutilisé sans ancien SET', "INSERT INTO t SET label = 'neuf'", $objQuery->get_sql());
        $objQuery->remove_table();
    }
    else $objQuery->remove_from();
    T::eq($strType.' retrait de la table', '', $objQuery->get_sql());
}
$objUpdate = new ploopi\query_update($objDb);
$objUpdate->add_from('t');
$objUpdate->add_set('t.id = 1');
$objUpdate->add_leftjoin('r ON r.id = t.id');
$objUpdate->remove_leftjoin();
$objUpdate->remove_leftjoin();
T::eq('UPDATE retire LEFT JOIN seulement', 'UPDATE t SET t.id = 1', $objUpdate->get_sql());
$objDelete = new ploopi\query_delete($objDb);
$objDelete->add_from('t');
$objDelete->add_delete('t');
$objDelete->add_innerjoin('r ON r.id = t.id');
$objDelete->add_leftjoin('s ON s.id = t.id');
$objDelete->remove_innerjoin();
T::eq('DELETE conserve LEFT JOIN après retrait INNER JOIN', 'DELETE t FROM t LEFT JOIN s ON s.id = t.id', $objDelete->get_sql());
$objDelete->remove_leftjoin();
$objDelete->remove_delete();
T::eq('DELETE retrouve la forme historique sans cible explicite', 'DELETE  FROM t', $objDelete->get_sql());
$objDelete->add_delete('t');
$objDelete->add_innerjoin('r ON r.id = t.id');
T::eq('DELETE réutilisé après retrait', 'DELETE t FROM t INNER JOIN r ON r.id = t.id', $objDelete->get_sql());

exit(T::bilan('47_query_builder'));
