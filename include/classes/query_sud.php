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

namespace ploopi;

use ploopi;

/**
 * Gestion de requêtes SQL construites (class abstraite pour SELECT/UPDATE/DELETE)
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class query_sud extends query
{
    /**
     * Tableau de la clause FROM
     *
     * @var array
     */
    protected $arrFrom;

    /**
     * Tableau de la clause WHERE
     *
     * @var array
     */
    private $arrWhere;

    /**
     * Tableau de la clause ORDER BY
     *
     * @var array
     */
    private $arrOrderBy;

    /**
     * Clause LIMIT
     *
     * @var string
     */
    private $strLimit;

    /**
     * Différents types acceptés pour un élément
     *
     * @var array
     */
    protected static $arrType = array(
        'select',
        'update',
        'delete'
    );

    private $strType;

    /**
     * Constructeur de la classe
     *
     * @param string $strType type de requête
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($strType = 'select', $objDb = null)
    {
        parent::__construct($objDb);

        $this->arrFrom = array();
        $this->arrWhere = array();
        $this->arrOrderBy = array();
        $this->strLimit = null;

        if (!in_array($strType, self::$arrType))
        {
            throw new \ErrorException('Ce type de requête n\'existe pas');
        }
        else
        {
            $this->strType = $strType;
        }
    }

    /**
     * Ajout d'une clause WHERE à la requête
     * Si plusieurs clauses WHERE sont ajoutées, elles sont séparées par AND
     *
     * Format supportés :
     * %d int
     * %f float
     * %s string
     * %e int list
     * %g float list
     * %t string list
     * %r raw
     * %% signe % littéral
     *
     * Numérotation des arguments possible : %1$f, %2$d, %4$r
     *
     * Ne jamais concaténer une donnée dans $strWhere, même échappée : un % qu'elle
     * contiendrait serait lu comme un marqueur. La passer en valeur (%s).
     *
     * @param string $strWhere Clause SQL brute
     * @param mixed $mixValues Valeurs
     */
    public function add_where($strWhere, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrWhere[] = array('rawsql' => $strWhere, 'values' => $mixValues);
    }

    /**
     * Ajoute un groupe parenthésé, combiné aux autres WHERE par AND.
     * Chaque condition contient « rawsql » et, facultativement, « values ».
     * Les marqueurs positionnels sont propres à chaque condition.
     *
     * @param array $arrConditions Liste non vide de conditions
     * @param string $strOperator OR ou AND
     * @throws \InvalidArgumentException
     */
    public function add_where_group(array $arrConditions, $strOperator = 'OR')
    {
        $strOperator = strtoupper(trim($strOperator));
        if (!in_array($strOperator, array('AND', 'OR'), true) || empty($arrConditions))
            throw new \InvalidArgumentException('Un groupe WHERE exige des conditions et un opérateur AND ou OR');

        $arrGroup = array();
        foreach ($arrConditions as $arrCondition)
        {
            if (!is_array($arrCondition) || !isset($arrCondition['rawsql']) || !is_string($arrCondition['rawsql']) || trim($arrCondition['rawsql']) === '')
                throw new \InvalidArgumentException('Chaque condition WHERE exige une clause rawsql non vide');

            $mixValues = $arrCondition['values'] ?? null;
            if (!is_null($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
            $arrGroup[] = array('rawsql' => $arrCondition['rawsql'], 'values' => $mixValues);
        }
        $this->arrWhere[] = array('conditions' => $arrGroup, 'operator' => $strOperator);
    }

    /**
     * Ajoute un filtre IN (ou NOT IN) typé.
     * Une liste vide produit 1 = 0 (IN) ou 1 = 1 (NOT IN).
     * L'expression est du SQL de confiance, jamais une entrée utilisateur.
     *
     * @param string $strExpression Colonne ou expression SQL
     * @param array $arrValues Valeurs scalaires de la liste (sans null)
     * @param string $strType d (entiers), f (flottants) ou s (chaînes)
     * @param boolean $booNot Utiliser NOT IN
     * @throws \InvalidArgumentException
     */
    public function add_where_in($strExpression, array $arrValues, $strType = 'd', $booNot = false)
    {
        $arrTypes = array('d' => 'e', 'f' => 'g', 's' => 't');
        if (!isset($arrTypes[$strType]))
            throw new \InvalidArgumentException('IN exige un type d, f ou s et des valeurs scalaires non nulles');
        foreach ($arrValues as $mixValue)
        {
            if (!is_scalar($mixValue))
                throw new \InvalidArgumentException('IN exige un type d, f ou s et des valeurs scalaires non nulles');
        }

        if (empty($arrValues)) $this->add_where($booNot ? '1 = 1' : '1 = 0');
        else $this->add_where('%r'.($booNot ? ' NOT IN (%' : ' IN (%').$arrTypes[$strType].')', array($strExpression, array_values($arrValues)));
    }

    /**
     * Ajoute IS NULL (ou IS NOT NULL) sur une expression SQL de confiance.
     *
     * @param string $strExpression Colonne ou expression SQL
     * @param boolean $booNot Utiliser IS NOT NULL
     */
    public function add_where_null($strExpression, $booNot = false)
    {
        $this->add_where('%r'.($booNot ? ' IS NOT NULL' : ' IS NULL'), array($strExpression));
    }

    /**
     * Ajoute EXISTS sur une copie de la sous-requête, reliée aux WHERE par AND.
     * La copie est formatée avec la connexion de la requête parente.
     *
     * @param query_select $objQuery Sous-requête, éventuellement corrélée
     * @throws \InvalidArgumentException Si la sous-requête ne produit aucun SQL
     */
    public function add_where_exists(query_select $objQuery)
    {
        $this->add_where_subquery($objQuery, false);
    }

    /**
     * Ajoute NOT EXISTS sur une copie de la sous-requête.
     *
     * @param query_select $objQuery Sous-requête, éventuellement corrélée
     * @throws \InvalidArgumentException Si la sous-requête ne produit aucun SQL
     */
    public function add_where_not_exists(query_select $objQuery)
    {
        $this->add_where_subquery($objQuery, true);
    }

    /**
     * Capture une sous-requête sans conserver de référence à l'objet appelant.
     *
     * @param query_select $objQuery Sous-requête
     * @param boolean $booNot Utiliser NOT EXISTS
     */
    private function add_where_subquery(query_select $objQuery, $booNot)
    {
        $objSubquery = clone $objQuery;
        $objSubquery->set_db($this->objDb);
        if (trim($objSubquery->get_sql()) === '')
            throw new \InvalidArgumentException('EXISTS exige une sous-requête SQL non vide');

        $this->arrWhere[] = array('exists' => $objSubquery, 'not' => $booNot);
    }

    /**
     * Ajoute une clause FROM à la requête (select/delete/update uniquement)
     * Si plusieurs clauses FROM sont ajoutées, elles sont séparées par ","
     *
     * @param string $strFrom Clause FROM
     */
    public function add_from($strFrom)
    {
        if (!in_array($strFrom, $this->arrFrom)) $this->arrFrom[] = $strFrom;
    }

    /**
     * Ajoute une clause ORDER BY à la requête
     * Si plusieurs clauses ORDER BY sont ajoutées, elles sont séparées par ","
     *
     * @param string $strOrderBy Clause ORDER BY
     */
    public function add_orderby($strOrderBy)
    {
        if (!in_array($strOrderBy, $this->arrOrderBy)) $this->arrOrderBy[] = $strOrderBy;
    }

    /**
     * Définit la clause LIMIT de la requête
     *
     * @param string $strLimit
     */
    public function add_limit($strLimit)
    {
        $this->strLimit = implode(', ', array_map('intval', explode(',', $strLimit)));
    }

    /**
     * Supprime la clause WHERE
     */
    public function remove_where() { $this->arrWhere = array(); }

    /**
     * Supprime toutes les tables de la clause FROM.
     */
    public function remove_from() { $this->arrFrom = array(); }

    /**
     * Supprime la clause ORDER BY
     */
    public function remove_orderby() { $this->arrOrderBy = array(); }

    /**
     * Supprime la clause LIMIT
     */
    public function remove_limit() { $this->strLimit = null; }

    /**
     * Retourne la clause FROM
     *
     * @return string
     */
    protected function get_from()
    {
        return empty($this->arrFrom) ? false : ' FROM '.implode(', ', $this->arrFrom);
    }

    /**
     * Retourne la clause WHERE
     *
     * @return string
     */
    protected function get_where()
    {
        $arrWhere = array();
        foreach($this->arrWhere as $arrWhereDetail)
        {
            if (isset($arrWhereDetail['conditions']))
            {
                $arrConditions = array();
                foreach ($arrWhereDetail['conditions'] as $arrCondition)
                    $arrConditions[] = '('.sqlformat::replace($arrCondition, $this->objDb).')';
                $arrWhere[] = '('.implode(' '.$arrWhereDetail['operator'].' ', $arrConditions).')';
            }
            elseif (isset($arrWhereDetail['exists']))
            {
                $objSubquery = clone $arrWhereDetail['exists'];
                $objSubquery->set_db($this->objDb);
                $arrWhere[] = ($arrWhereDetail['not'] ? 'NOT EXISTS (' : 'EXISTS (').$objSubquery->get_sql().')';
            }
            else $arrWhere[] = sqlformat::replace($arrWhereDetail, $this->objDb);
        }

        return empty($arrWhere) ? '' : ' WHERE '.implode(' AND ', $arrWhere);
    }

    /**
     * Retourne la clause ORDER BY
     *
     * @return string
     */
    protected function get_orderby() { return empty($this->arrOrderBy) ? '' : ' ORDER BY '.implode(', ', $this->arrOrderBy); }

    /**
     * Retourne la clause LIMIT
     *
     * @return string
     */
    protected function get_limit() { return empty($this->strLimit) ? '' : " LIMIT {$this->strLimit}"; }

}
