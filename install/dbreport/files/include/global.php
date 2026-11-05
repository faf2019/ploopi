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
 * Fonctions, constantes, variables globales
 *
 * @package dbreport
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */


include_once './modules/dbreport/classes/class_dbreport.php';

/**
 * Écrans prêts pour la Content-Security-Policy stricte (ploopi\csp::moduleready) : aucun
 * script ni gestionnaire en ligne, comportements par attributs data-* (js/actions.js
 * du socle, include/functions.js du module)
 */
define('_DBREPORT_CSP', true);

/**
 * L'utilisateur courant peut-il consulter ou exécuter la requête ?
 *
 * Règle de la liste des requêtes (public_query_list.php) : requêtes de l'instance
 * courante, dans les espaces visibles selon le mode de vue du module.
 *
 * @param array $arrQuery champs de la requête
 * @return boolean true si la requête est visible
 */

function dbreport_query_isvisible($arrQuery)
{
    if (!isset($arrQuery['id_module']) || $arrQuery['id_module'] != $_SESSION['ploopi']['moduleid']) return false;

    return in_array($arrQuery['id_workspace'], explode(',', ploopi\system::viewworkspaces()));
}

/**
 * L'utilisateur courant peut-il modifier ou supprimer la requête ?
 *
 * Requête visible, action « gérer les requêtes » et, pour une requête verrouillée,
 * action « verrouiller les requêtes », comme dans la liste.
 *
 * @param array $arrQuery champs de la requête
 * @return boolean true si la requête est modifiable
 */

function dbreport_query_ismodifiable($arrQuery)
{
    return dbreport_query_isvisible($arrQuery)
        && ploopi\acl::isactionallowed(dbreport::_ACTION_MANAGE)
        && (empty($arrQuery['locked']) || ploopi\acl::isactionallowed(dbreport::_ACTION_LOCK));
}

/**
 * Une table peut-elle entrer dans la requête ?
 *
 * Même règle que la liste proposée (op_querytable_add.php) : table déclarée et visible
 * dans la métabase, pour l'un des types de modules choisis pour la requête.
 *
 * @param int $intQueryId requête
 * @param string $strTable nom de la table
 * @return boolean true si la table est autorisée
 */

function dbreport_table_isallowed($intQueryId, $strTable)
{
    if (!is_string($strTable) || !preg_match('/^[A-Za-z0-9_]+$/D', $strTable)) return false;

    $objQuery = new ploopi\query_select();
    $objQuery->add_select('mbt.name');
    $objQuery->add_from('ploopi_mb_table mbt');
    $objQuery->add_innerjoin('ploopi_mod_dbreport_query_module_type dqm ON dqm.id_module_type = mbt.id_module_type');
    $objQuery->add_where('dqm.id_query = %d', $intQueryId);
    $objQuery->add_where('mbt.name = %s', $strTable);
    $objQuery->add_where('mbt.visible = 1');

    return $objQuery->execute()->numrows() > 0;
}

/**
 * Un champ peut-il entrer dans la requête ?
 *
 * Même règle que la liste proposée (op_queryfield_add.php) : champ visible de la
 * métabase, d'une table autorisée et déjà présente dans la requête. Un champ masqué
 * (identifiant, ou champ sensible) ne s'ajoute plus par un formulaire forgé.
 *
 * @param int $intQueryId requête
 * @param string $strTable nom de la table
 * @param string $strField nom du champ
 * @return boolean true si le champ est autorisé
 */

function dbreport_field_isallowed($intQueryId, $strTable, $strField)
{
    if (!is_string($strField) || !preg_match('/^[A-Za-z0-9_]+$/D', $strField) || !dbreport_table_isallowed($intQueryId, $strTable)) return false;

    $objQuery = new ploopi\query_select();
    $objQuery->add_select('mbf.name');
    $objQuery->add_from('ploopi_mb_field mbf');
    $objQuery->add_innerjoin('ploopi_mod_dbreport_querytable drt ON drt.tablename = mbf.tablename');
    $objQuery->add_where('drt.id_query = %d', $intQueryId);
    $objQuery->add_where('mbf.tablename = %s', $strTable);
    $objQuery->add_where('mbf.name = %s', $strField);
    $objQuery->add_where('mbf.visible = 1');

    return $objQuery->execute()->numrows() > 0;
}
