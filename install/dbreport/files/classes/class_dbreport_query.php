<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Gestion des requêtes
 *
 * @package dbreport
 * @subpackage query
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

include_once './include/classes/query.php';

include_once './modules/dbreport/classes/class_dbreport_queryfield.php';
include_once './modules/dbreport/classes/class_dbreport_querytable.php';

/**
 * Classe de gestion des requêtes
 *
 */
class dbreport_query extends data_object
{
    /**
     * Requête SQL générée
     *
     * @var string
     */
    private $strSqlQuery;

    /**
     * Objet Query
     *
     * @var ploopi_query_select
     */
     private $objQuery;

    /**
     * Tableau contenant les champs de la requête
     *
     * @var array
     */
    private $arrFields;

    /**
     * Tableau contenant le résultat de la requête
     *
     * @var array
     */
    private $arrResult;

    /**
     * True si la requête affiche une date (nécessite un post traitement)
     *
     * @var boolean
     */
    private $booHasDate;

    /**
     * Code d'erreur
     *
     * @var int
     */
    private $intErrorCode;

    /**
     * code d'erreur standard : pas d'erreur
     *
     * @var int
     */
    private static $_ERROR_OK       = 0;

    /**
     * code d'erreur "request" : erreur dans la requête
     *
     * @var int
     */
    private static $_ERROR_REQUEST  = 1;

    /**
     * code d'erreur "param" : erreur de paramètre
     *
     * @var int
     */
    private static $_ERROR_PARAM    = 2;

    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_mod_dbreport_query');

        $this->strSqlQuery = '';
        $this->objQuery = new ploopi_query_select();
        $this->arrResult = array();
        $this->booHasDate = false;
        $this->intErrorCode = self::$_ERROR_OK;
    }

    /**
     * Enregistrement de la requête
     */
    public function save()
    {
        $this->fields['timestp_update'] = ploopi_createtimestamp();
        return parent::save();
    }

    /**
     * Suppression de la requête
     */
    public function delete()
    {
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('ploopi_mod_dbreport_querytable');
        $objQuery->add_where('id_query = %d', $this->fields['id']);
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {
            $objDbrQueryTable = new dbreport_querytable();
            $objDbrQueryTable->open($row['id']);
            $objDbrQueryTable->delete();
        }

        parent::delete();
    }


    /**
     * Gère le clone et les entités liées
     *
     * @return dbreport_query
     */
    public function __clone()
    {
        // Stock ancien ID
        $intClonedId = $this->fields['id'];

        // Personnalisation du clone
        $this->new = true;
        $this->fields['label'] = 'Clone de '.$this->fields['label'];
        $this->fields['ws_id'] = 'clone_de_'.$this->fields['ws_id'];
        $this->fields['locked'] = 0;
        $this->fields['id'] = null;

        // Enregistrement du clone pour récupérer une nouvel ID
        $this->save();

        // Clonage de la relation avec "ploopi_mod_dbreport_querytable"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('null, `tablename`, `id_module_type`, %d', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_querytable");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_querytable");
        $objQueryIns->add_raw($objQuerySel->get_sql());
        $objQueryIns->execute();

        // Clonage de la relation avec "ploopi_mod_dbreport_queryfield"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('null, `tablename`, `id_module_type`, `fieldname`, `label`, `function`, `visible`, `sort`, `criteria`, `type_criteria`, `or`, `type_or`, `intervals`, `operation`, `position`, `series`, %d', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_queryfield");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_queryfield");
        $objQueryIns->add_raw($objQuerySel->get_sql());
        $objQueryIns->execute();

        // Clonage de la relation avec "ploopi_mod_dbreport_queryrelation"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('%d, `tablename_src`, `tablename_dest`, `active`', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_queryrelation");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_queryrelation");
        $objQueryIns->add_raw($objQuerySel->get_sql());
        $objQueryIns->execute();

        // Clonage de la relation avec "ploopi_mod_dbreport_query_module_type"
        $objQuerySel = new ploopi_query_select();
        $objQuerySel->add_select('%d, `id_module_type`', $this->fields['id']);
        $objQuerySel->add_from("ploopi_mod_dbreport_query_module_type");
        $objQuerySel->add_where('id_query = %d', $intClonedId);

        $objQueryIns = new ploopi_query_insert();
        $objQueryIns->set_table("ploopi_mod_dbreport_query_module_type");
        $objQueryIns->add_raw($objQuerySel->get_sql());
        $objQueryIns->execute();
    }

    /**
     * Prépare la requête SQL
     *
     * @param array $arrParam tableau de paramètre
     * @return boolean true si la requête a pu être préparée
     */
    public function generate($arrParam = null)
    {
        ploopi_init_module('dbreport', false, false, false);
        global $arrDbReportOperations;

        /**
         * Génération de la requête SQL
         * @todo Nettoyage des nom de critere ? (%)
         */

        $this->strSqlQuery = '';
        $this->objQuery = new ploopi_query_select();

        $arrSqlSelect = array();
        $arrSqlFrom = array();
        $arrSqlWhere = array();
        $arrSqlJoin = array();
        $arrSqlGroupBy = array();
        $arrSqlOrderBy = array();
        $arrSqlHaving = array();
        $arrSqlQuery = array();

        $this->booHasDate = false; // true si la requête contient un champ de type "date"
        $this->arrFields = array(); // tableau des champs de la requête

        /* Boucle sur les champs de la requête (avec le type de champ) */
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('drf.*, mbf.type');
        $objQuery->add_from('ploopi_mod_dbreport_queryfield drf');
        $objQuery->add_from('ploopi_mb_field mbf');
        $objQuery->add_where('mbf.tablename = drf.tablename');
        $objQuery->add_where('mbf.name = drf.fieldname');
        $objQuery->add_where('drf.id_query = %d', $this->fields['id']);
        $objQuery->add_orderby('drf.position');
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {

            $strSqlSelect = '';
            $strSqlGroupBy = '';
            $strSqlWhere = '';
            $strSqlHaving = '';

            // Paramètre de la clause SELECT
            $arrSelectParams = array();

            // Nom du champ dans la requête (init)
            $strQueryField = "{$row['tablename']}.{$row['fieldname']}";
            //if ($row['type'] == 'date') $strQueryField = "CONCAT(SUBSTRING($strQueryField,9,2),'/',SUBSTRING($strQueryField,6,2),'/',SUBSTRING($strQueryField,1,4))";
            if ($row['function'] != '')  $strQueryField = str_replace('%', $strQueryField, $row['function']);

            $strDefaultLabel = "{$row['tablename']}.{$row['fieldname']}";

            /**
             * Construction de la clause SELECT
             * Traitement "operation" et "function"
             * Impacte $arrSqlSelect, $strSqlGroupBy et $strSqlOrderBy
             */

            if ($row['visible'])
            {
                // Détection de date dans la requête
                if ($row['type'] == 'date' && !$this->booHasDate) $this->booHasDate = true;

                switch($row['operation'])
                {
                    case '':
                        $strSqlSelect = $strQueryField;
                    break;

                    case 'groupby':
                        $strSqlSelect = $strQueryField;

                        $this->objQuery->add_groupby(($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`");
                    break;

                    case 'intervals':
                        // Exemple d'intervalle :
                        // If(auteur_affaire.age BETWEEN 12 AND 16,'12-16',If(auteur_affaire.age BETWEEN 17 AND 19,'17-19','20 et +')) as tranche,

                        // On va se servir de IF imbriqués

                        // Nombre de parenthèses ouvertes (à fermer)
                        $intParenthesis = 0;

                        $arrSqlWhereOr = array();
                        $strSqlWhereOr = '';
                        $arrWhereParams = array();

                        // Supprime le ; en trop à la fin (si il existe) dans la saisie des intervalles par l'utilisateur
                        if (substr($row['intervals'], -1) == ';') $row['intervals'] = substr($row['intervals'], 0, -1);

                        // On crée un tableau avec les intervalles (; pour séparer)
                        $arrIntervals = explode(';', $row['intervals']);

                        $intI = 1;
                        // Pour chaque intervalle
                        foreach($arrIntervals as $strInterval)
                        {
                            if ($strInterval != '')
                            {
                                // On décompose l'intervalle (séparateur: "-")
                                $arrInterval = explode('-', $strInterval);
                                if (sizeof($arrInterval) <= 2)
                                {
                                    if (sizeof($arrInterval) == 1) $arrInterval[1] = $arrInterval[0];

                                    // IntervalSql va contenir les dates transformées pour SQL
                                    $arrIntervalSql = $arrInterval;

                                    if ($row['type'] == 'date')
                                    {
                                        $arrIntervalSql[0] = ploopi_local2timestamp($arrInterval[0]);
                                        if ($arrInterval[1] != '+') $arrIntervalSql[1] = ploopi_local2timestamp($arrInterval[1]);
                                    }

                                    if ($intI < sizeof($arrIntervals))
                                    {
                                        $arrSqlWhereOr[] = "{$strQueryField} BETWEEN %s AND %s";
                                        $arrWhereParams[] = $arrIntervalSql[0];
                                        $arrWhereParams[] = $arrIntervalSql[1];

                                        // cas général
                                        $strSqlSelect .= "if ({$strQueryField} BETWEEN %s AND %s, %s, ";
                                        $arrSelectParams[] = $arrIntervalSql[0];
                                        $arrSelectParams[] = $arrIntervalSql[1];
                                        $arrSelectParams[] = "{$arrInterval[0]} à {$arrInterval[1]}";

                                        $intParenthesis++;
                                    }
                                    else // cas particulier du dernier intervalle
                                    {
                                        if ($arrInterval[1] == '+')
                                        {
                                            $arrSqlWhereOr[] = "{$strQueryField} >= %s";
                                            $arrWhereParams[] = $arrIntervalSql[0];

                                            // tout ce qui est supérieur
                                            $strSqlSelect .= "%s";
                                            $arrSelectParams[] = "{$arrInterval[0]} et {$arrInterval[1]}";
                                        }
                                        else
                                        {
                                            $arrSqlWhereOr[] = "{$strQueryField} BETWEEN %s AND %s";
                                            $arrWhereParams[] = $arrIntervalSql[0];
                                            $arrWhereParams[] = $arrIntervalSql[1];

                                            // intervalle normal
                                            $strSqlSelect .= "if ({$strQueryField} BETWEEN %s AND %s, %s,null";
                                            $arrSelectParams[] = $arrIntervalSql[0];
                                            $arrSelectParams[] = $arrIntervalSql[1];
                                            $arrSelectParams[] = "{$arrInterval[0]} à {$arrInterval[1]}";

                                            $intParenthesis++;
                                        }
                                    }
                                }
                            }

                            $intI++;
                        }

                        // Clause WHERE
                        $this->objQuery->add_where('('.implode(' OR ', $arrSqlWhereOr).')', $arrWhereParams);

                        // Clause SELECT
                        // On ferme les parenthèses en fonction du nombre de IF
                        $strSqlSelect .= str_repeat(')', $intParenthesis);

                        // Clause GROUP BY
                        $this->objQuery->add_orderby(($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`");
                    break;

                    default: // AUTRES OPERATIONS
                        $strOpName = strtoupper($row['operation']);

                        // /!\ On change le label par défaut en incluant le nom de l'opération
                        $strDefaultLabel = "{$arrDbReportOperations[$row['operation']]} de {$strDefaultLabel}";

                        $strSqlSelect .= "{$strOpName}({$strQueryField})";

                    break;
                }

                $strLabel = ($row['label']) ? $row['label'] : $strDefaultLabel;
                $this->arrFields[$strLabel] = $row;

                // On ajoute la clause SELECT
                $this->objQuery->add_select("{$strSqlSelect} AS `{$strLabel}`", $arrSelectParams);

                /* Gestion du tri */
                if ($row['sort'] != '')
                {
                    $strSqlOrderBy = ($row['label']) ? "`{$row['label']}`" : "`{$strDefaultLabel}`";
                    $this->objQuery->add_orderby($strSqlOrderBy.' '.strtoupper($row['sort']));
                }
            }
            // FIN de if ($row['visible'])


            /**
             * Construction des clauses WHERE et HAVING
             * Traitement fusionné de "criteria" et "or"
             * Impacte $strSqlWhere et $strSqlHaving
             */

            $arrWhereParams = array();
            $arrHavingParams = array();

            // Lecture des critères depuis l'URL
            foreach(array('criteria', 'or') as $strCrit)
            {
                if ($row["type_{$strCrit}"] != '')
                {
                    // Cas particulier ou le filtre est un paramètre
                    if (strlen($row[$strCrit]) > 0 && $row[$strCrit][0] == '@')
                    {
                        if (isset($arrParam[$row[$strCrit]]))
                        {
                            $row[$strCrit] = $arrParam[$row[$strCrit]];
                        }
                        else
                        {
                            $this->intErrorCode = self::$_ERROR_PARAM;
                            return false;
                        }
                    }
                }
            }

            // Permet de déterminé sur la clause OR est applicable ou non sur ce champ
            $booOrIsValid = ($row['type_criteria'] != '') && ($row['type_or'] != '') && ($row['criteria'] != '%') && ($row['or'] != '%');

            foreach(array('criteria', 'or') as $strCrit)
            {
                if ($row["type_{$strCrit}"] != '')
                {
                    if ($row[$strCrit] != '%')
                    {
                        $arrCriteria = ($row["type_{$strCrit}"] == 'between') ? explode('-',$row[$strCrit]) : null; // Explosition du critère si BETWEEN

                        if ($row["type_{$strCrit}"] != 'between' || ($row["type_{$strCrit}"] == 'between' && sizeof($arrCriteria) == 2))
                        {
                            if ($row['operation'] == 'groupby' || $row['operation'] == '')
                            {
                                if ($strCrit == 'criteria' && $booOrIsValid) $strSqlWhere .= '(';
                                if ($strCrit == 'or' && $booOrIsValid) $strSqlWhere .= " OR ";

                                $strSqlWhere .= $strQueryField.' '; //($row['label']) ? "`{$row['label']}` " : "`{$strDefaultLabel}` ";
                                $strValue = '';

                                if (strstr($row['type'],'int')) // type int
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%e)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrWhereParams[0][] = intval($strVal, 10);
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %d AND %d";
                                        $arrWhereParams[] = intval($arrCriteria[0], 10);
                                        $arrWhereParams[] = intval($arrCriteria[1], 10);
                                    }
                                    else $strValue = intval($row[$strCrit], 10);
                                }
                                elseif (strstr($row['type'],'double') || strstr($row['type'],'float')) // type double
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrWhereParams[0][] = floatval(str_replace(array(' ',','), array('', '.'), $strVal));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %f AND %f";
                                        $arrWhereParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[0]));
                                        $arrWhereParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[1]));
                                    }
                                    else $strValue = floatval(str_replace(array(' ',','), array('', '.'), $row[$strCrit]));
                                }
                                elseif (strstr($row['type'],'date')) // type date
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%t)";
                                        $arrWhereParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %s AND %s";
                                        $arrWhereParams[] = ploopi_local2timestamp($arrCriteria[0]);
                                        $arrWhereParams[] = ploopi_local2timestamp($arrCriteria[1]);
                                    }
                                    else $strValue = ploopi_local2timestamp($row[$strCrit]);
                                }
                                else // Type char/text/enum/???
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlWhere .= "IN (%t)";
                                        $arrWhereParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlWhere .= "BETWEEN %s AND %s";
                                        $arrWhereParams[] = $arrCriteria[0];
                                        $arrWhereParams[] = $arrCriteria[1];
                                    }
                                    else $strValue = $row[$strCrit];
                                }

                                if ($row["type_{$strCrit}"] != 'between' && $row["type_{$strCrit}"] != 'in')
                                {
                                    switch($row["type_{$strCrit}"])
                                    {
                                        case 'like':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "%{$strValue}%";
                                        break;

                                        case 'begining':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "{$strValue}%";
                                        break;

                                        case 'ending':
                                            $strSqlWhere .= "LIKE %s";
                                            $arrWhereParams[] = "%{$strValue}";
                                        break;

                                        default:
                                            $strSqlWhere .= $row["type_{$strCrit}"].' %s';
                                            $arrWhereParams[] = $strValue;
                                        break;
                                    }
                                }

                                if ($strCrit == 'or' && $booOrIsValid) $strSqlWhere .= ')';
                            }
                            else // Critère sur une opération de type 'Compte', 'Somme', etc....
                            {
                                // un critère sur une opération nécessite l'utilisation de HAVING

                                if ($strCrit == 'criteria' && $booOrIsValid) $strSqlHaving .= '(';
                                if ($strCrit == 'or' && $booOrIsValid) $strSqlHaving .= ' OR ';

                                $strSqlHaving .= ($row['label']) ? "`{$row['label']}` " : "`{$strDefaultLabel}` ";
                                $strValue = '';

                                if (strstr($row['type'],'double') || strstr($row['type'],'float') || in_array($row['operation'], array('count', 'sum', 'avg'))) // type double ou opération arithmétique
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrHavingParams[0][] = floatval(str_replace(array(' ',','), array('', '.'), $strVal));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %f AND %f";
                                        $arrHavingParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[0]));
                                        $arrHavingParams[] = floatval(str_replace(array(' ',','), array('', '.'), $arrCriteria[1]));
                                    }
                                    else $strValue = floatval(str_replace(array(' ',','), array('', '.'), $row[$strCrit]));
                                }
                                elseif (strstr($row['type'],'int')) // type int
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%g)";
                                        foreach(explode(',', $row[$strCrit]) as $strVal) $arrHavingParams[0][] = intval($strVal, 10);
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %d AND %d";
                                        $arrHavingParams[] = intval($arrCriteria[0], 10);
                                        $arrHavingParams[] = intval($arrCriteria[1], 10);
                                    }
                                    else $strValue = intval($row[$strCrit], 10);
                                }
                                elseif (strstr($row['type'],'date')) // type date
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%t)";
                                        $arrHavingParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %s AND %s";
                                        $arrHavingParams[] = ploopi_local2timestamp($arrCriteria[0]);
                                        $arrHavingParams[] = ploopi_local2timestamp($arrCriteria[1]);
                                    }
                                    else $strValue = ploopi_local2timestamp($row[$strCrit]);
                                }
                                else // Type char/text/enum/???
                                {
                                    if ($row["type_{$strCrit}"] == 'in')
                                    {
                                        $strSqlHaving .= "IN (%t)";
                                        $arrHavingParams = array(explode(',', $row[$strCrit]));
                                    }
                                    elseif ($row["type_{$strCrit}"] == 'between')
                                    {
                                        $strSqlHaving .= "BETWEEN %s AND %s";
                                        $arrHavingParams[] = $arrCriteria[0];
                                        $arrHavingParams[] = $arrCriteria[1];
                                    }
                                    else $strValue = $row[$strCrit];
                                }

                                if ($row["type_{$strCrit}"] != 'between' && $row["type_{$strCrit}"] != 'in')
                                {
                                    switch($row["type_{$strCrit}"])
                                    {
                                        case 'like':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "%{$strValue}%";
                                        break;

                                        case 'begining':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "{$strValue}%";
                                        break;

                                        case 'ending':
                                            $strSqlHaving .= "LIKE %s";
                                            $arrHavingParams[] = "%{$strValue}";
                                        break;

                                        default:
                                            $strSqlHaving .= $row["type_{$strCrit}"].' %s';
                                            $arrHavingParams[] = $strValue;
                                        break;
                                    }
                                }

                                if ($strCrit == 'or' && $booOrIsValid) $strSqlHaving .= ')';
                            }
                        }
                    }
                }
            }

            if ($strSqlWhere != '') $this->objQuery->add_where($strSqlWhere, $arrWhereParams); //$arrSqlWhere[] = $strSqlWhere;
            if ($strSqlHaving != '')
            {
                $this->objQuery->add_having($strSqlHaving, $arrHavingParams); //$arrSqlHaving[] = $strSqlHaving;
            }

        } // FIN while


        /**
         * Construction de la clause FROM
         */

        // Recherche des tables de la requête
        $objQuery = new ploopi_query_select();
        $objQuery->add_from('ploopi_mod_dbreport_querytable');
        $objQuery->add_where('id_query = %d', $this->fields['id']);
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow()) if ($row['tablename'] != '') $this->objQuery->add_from($row['tablename']); //$arrSqlFrom[$row['tablename']] = $row['tablename'];

        /**
         * Construction de la clause JOIN
         */
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('mbr.tablesrc, mbr.fieldsrc, mbr.tabledest, mbr.fielddest');
        $objQuery->add_from('ploopi_mod_dbreport_queryrelation qr');
        $objQuery->add_from('ploopi_mb_relation mbr');
        $objQuery->add_where('qr.id_query = %d', $this->fields['id']);
        $objQuery->add_where('qr.active = 1');
        $objQuery->add_where('qr.tablename_src = mbr.tablesrc');
        $objQuery->add_where('qr.tablename_dest = mbr.tabledest');
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow())
        {
            if ($row['tablesrc'] != '') $this->objQuery->add_from($row['tablesrc']); //$arrSqlFrom[$row['tablesrc']] = $row['tablesrc'];
            if ($row['tabledest'] != '') $this->objQuery->add_from($row['tabledest']);  //$arrSqlFrom[$row['tabledest']] = $row['tabledest'];

            $this->objQuery->add_where("{$row['tablesrc']}.{$row['fieldsrc']} = {$row['tabledest']}.{$row['fielddest']}"); //$arrSqlJoin[] = "{$row['tablesrc']}.{$row['fieldsrc']} = {$row['tabledest']}.{$row['fielddest']}";
        }

        $this->strSqlQuery = $this->objQuery->get_sql();

        return true;
    }

    /**
     * Exécute la requête
     *
     * @param array $arrParam tableau optionnel de paramètres
     * @return boolean true si la requête a pu être exécutée
     */
    public function exec($intCacheLifetime = 0)
    {
        // Génération de la requête
        //if ($this->generate($arrParam))
        if (!empty($this->strSqlQuery))
        {
            $objCache = new ploopi_cache($this->getcacheid(), $intCacheLifetime);

            if (!$this->arrResult = $objCache->get_var())
            {
                if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);
                // Exécution de la requête
                $objRs = $this->objQuery->execute();

                // Récupération du résultat dans un tableau
                if (!$this->booHasDate) $this->arrResult = $objRs->getarray();
                else
                {
                    // Traitement particulier à cause du post-traitement nécessaire sur les données des champs de type "date"
                    while ($row = $objRs->fetchrow())
                    {
                        // Recherche des champs de type date et traitement
                        foreach($row as $strLabel => $strValue) if (isset($this->arrFields[$strLabel]) && $this->arrFields[$strLabel]['type'] == 'date') $row[$strLabel] =  empty($strValue) ? '' : implode(' ', ploopi_timestamp2local($strValue));
                        $this->arrResult[] = $row;
                    }
                }

                $objCache->save_var($this->arrResult);
            }

            return true;
        }
        else return false;
    }

    /**
     * Retourne l'id du cache pour la requête
     *
     * @return string id du cache
     */
    public function getcacheid()
    {
        return empty($this->strSqlQuery) ? false : "dbreport/query/{$this->fields['id']}/{$this->fields['timestp_update']}/".md5($this->strSqlQuery);
    }

    /**
     * Retourne un tableau contenant le résultat de la requête
     *
     * @return array
     */
    public function getresult() { return $this->arrResult; }

    /**
     * Retourne un tableau optimisé contenant le résultat de la requête
     *
     * @return array
     */
    public function getresult_opt()
    {
        $arrArray = $this->getresult();
        $arrNewArray = array('titles' => array(), 'data' => array());
        foreach($arrArray as $intIdLine => $arrLine)
        {
            // Stockage des titres
            if (empty($arrNewArray['titles'])) foreach($arrLine as $strKey => $strValue) $arrNewArray['titles'][] = $strKey;
            // Stockage des données
            foreach($arrLine as $strKey => $strValue) $arrNewArray['data'][$intIdLine][] = $strValue;
        }

        return $arrNewArray;
    }

    /**
     * Retourne le code SQL généré de la requête
     *
     * @return string
     */
    public function getquery() { return $this->strSqlQuery; }


    /**
     * Génère le contenu du fichier
     *
     * @param string $strFormat Format du fichier parmi sql, html, xls, sxc, ods, pdf, csv, json, json_opt, xml, ser, txt
     * @param string $strFileName Nom du fichier (sans extension)
     * @param boolean $booSetHeaders true si le headers doivent être générés (true par défaut)
     * @param string $strContentDisposition Type de disposition parmi 'attachment', 'inline'
     */
    public function export($strFormat = 'xml', $strFileName = null, $booSetHeaders = true, $strContentDisposition = 'attachment')
    {
        include_once './include/functions/array.php';
        include_once './include/classes/odf.php';

        $strFormat = strtolower($strFormat);
        $strCharset = 'iso-8859-1';

        switch($strFormat)
        {
            case 'sql':
                echo $this->getquery();
            break;

            case 'html':
                echo '
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html;charset=ISO-8859-1" />
                        <style>
                        body {font: 11px Verdana,Tahoma,Arial,sans-serif;}
                        table {padding:0px;margin:2px;border:1px solid #c0c0c0;border-collapse:collapse;}
                        td, th {padding:2px 4px;border:1px solid #888;}
                        th {background-color:#ddd;}
                        </style>
                    </head>
                    <body>'.ploopi_array2html($this->getresult()).'</body></html>';
            break;

            case 'xls':
                echo ploopi_array2xls($this->getresult(), true, 'dbreport.xls', 'query');
            break;

            case 'odt': // experimental
                $strHtml = ploopi_utf8encode('
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
                        <style>
                        body {font: 11px Verdana,Tahoma,Arial,sans-serif;}
                        table {padding:0px;margin:2px;border:1px solid #c0c0c0;border-collapse:collapse;}
                        td, th {padding:2px 4px;border:1px solid #888;}
                        th {background-color:#ddd;}
                        </style>
                    </head>
                    <body>'.ploopi_array2html($this->getresult()).'</body></html>');
                $strCharset = 'UTF-8';

                include_once './lib/xhtml2odt/class.odt.odtfile.php';
                ODTFile::get($strHtml, realpath('.').'/modules/dbreport/odt/template.odt');
            break;

            case 'sxc':
            case 'ods':
            case 'pdf':
                // Création d'un fichier temporaire XLS
                // Création du dossier de travail (si n'existe pas)
                ploopi_makedir($strOutputPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'dbreport'._PLOOPI_SEP.'tmp');
                $strFileId = uniqid();
                $strOutputXls = $strOutputPath._PLOOPI_SEP."{$strFileId}.xls";

                // Génération du fichier XLS
                ploopi_array2xls($this->getresult(), true, $strOutputXls, 'query', null, array('tofile' => true, 'setborder' => true));

                // Instanciation du convertisseur ODF
                $objOdfConverter = new odf_converter(ploopi_getparam('dbreport_webservice_jodconverter', ploopi_getmoduleid('dbreport')));

                $rawOuputFile = $strOutputPath._PLOOPI_SEP."{$strFileId}.{$strFormat}";

                switch($strFormat)
                {
                    case 'pdf':
                        $strOuputMime = 'application/pdf';
                    break;

                    case 'sxc':
                        $strOuputMime = 'application/vnd.sun.xml.calc';
                    break;

                    case 'ods':
                        $strOuputMime = 'application/vnd.oasis.opendocument.spreadsheet';
                    break;
                }

                // Conversion du document dans le format sélectionné
                echo $objOdfConverter->convert(file_get_contents($strOutputXls), 'application/vnd.ms-excel', $strOuputMime);
                // Suppression du fichier temporaire XLS
                unlink($strOutputXls);
            break;

            case 'csv':
                echo ploopi_array2csv($this->getresult());
            break;

            case 'json':
                echo ploopi_array2json($this->getresult());
                $strCharset = 'UTF-8';
            break;

            case 'json_opt':
                echo ploopi_array2json($this->getresult_opt());
                $strCharset = 'UTF-8';
            break;

            case 'xml':
                //ploopi_die($objDbrQuery->getresult());
                echo ploopi_array2xml($this->getresult());
            break;

            case 'ser':
                echo serialize($this->getresult());
            break;

            default:
            case 'txt':
                print_r($this->getresult());
            break;
        }

        if ($booSetHeaders)
        {
            if (is_null($strFileName)) $strFileName = "dbreport.{$strFormat}";

            header('Content-Type: '.ploopi_getmimetype($strFileName).'; charset='.$strCharset);
            header('Content-Disposition: '.$strContentDisposition.'; Filename="'.$strFileName.'"');
            header('Cache-Control: private');
            header('Pragma: private');
            header('Content-Length: '.ob_get_length());
            header("Content-Encoding: None");
            header("X-Ploopi: Download"); // Permet d'indiquer au gestionnaire de buffer qu'il s'agit d'un téléchargement de fichier @see ploopi_ob_callback
        }
    }

    /**
     * Retourne l'uri du webservice associé à la requête
     *
     * @return string
     */
    public function getwsuri()
    {
        $arrParams = $this->getparams();

        // Construction de la chaine de paramètres
        $arrUrlParams = array();
        $arrUrlParams['format'] = 'format=xml';
        if (!empty($this->fields['ws_code'])) $arrUrlParams['code'] = "code={$this->fields['ws_code']}";

        foreach($arrParams as $strParam => $row) $arrUrlParams[$strParam] = "{$strParam}=";

        return _PLOOPI_BASEPATH."/wsdbr/{$this->fields['ws_id']}?".implode('&', $arrUrlParams);
    }

    /**
     * Retourne le détail des champs de la requête
     *
     * @return array tableau des champs
     */
    public function getfields()
    {
        $objCol = new data_object_collection('dbreport_queryfield');
        $objCol->add_where('id_query = %d', $this->fields['id']);
        return $objCol->get_objects();
    }

    /**
     * Retourne le détail des paramètres de la requête
     *
     * @return array tableau des paramètres
     */
    public function getparams()
    {
        $arrParams = array();

        // Recherche des champs de la requête et détection des paramètres utilisateur
        $arrObjFields = $this->getfields();
        // Pour chaque champ de la requête
        foreach($arrObjFields as $objField)
        {
            // Pour chaque type de critère
            foreach(array('criteria', 'or') as $strCrit)
            {
                if (!empty($objField->fields[$strCrit]) && $objField->fields[$strCrit][0] == '@') $arrParams[$objField->fields[$strCrit]] = $objField->fields;
            }
        }

        return $arrParams;
    }

    /**
     * Retourne le dernier code d'erreur rencontré
     *
     * @return int
     */
    public function geterror() { return $this->intErrorCode; }

    /**
     * Retourne un objet en fonction de l'id utilisé pour le webservice
     *
     * @param string $strWsId id utilisé pour le webservice
     * @return dbreport_query instance de dbreport_query
     */
    public static function getInstanceByWsId($strWsId)
    {
        include_once './include/classes/data_object_collection.php';

        $objCol = new data_object_collection('dbreport_query');
        $objCol->add_where('ws_id = %s', $strWsId);

        return current($objCol->get_objects());
    }

}
?>
