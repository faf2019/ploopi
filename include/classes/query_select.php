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
 * Gestion de requêtes SQL construites de type SELECT
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class query_select extends query_sud
{
    /**
     * Tableau de la clause SELECT
     *
     * @var array
     */
    private $arrSelect;

    /**
     * Tableau de la clause INNER JOIN
     *
     * @var array
     */
    private $arrInnerJoin;

    /**
     * Tableau de la clause LEFT JOIN
     *
     * @var array
     */
    private $arrLeftJoin;

    /**
     * Tableau de la clause GROUP BY
     *
     * @var array
     */
    private $arrGroupBy;

    /**
     * Tableau de la clause HAVING
     *
     * @var array
     */
    private $arrHaving;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSelect = array();
        $this->arrInnerJoin = array();
        $this->arrLeftJoin = array();
        $this->arrGroupBy = array();
        $this->arrHaving = array();

        parent::__construct('select', $objDb);
    }

    /**
     * Ajoute une clause SELECT
     *
     * @param string $strSelect Clause SELECT
     * @param mixed $mixValues valeur(s)
     */
    public function add_select($strSelect, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrSelect[] = array('rawsql' => $strSelect, 'values' => $mixValues);
    }

    /**
     * Ajoute une clause LEFT JOIN à la requête
     *
     * @param string $strLeftJoin Clause LEFT JOIN
     */
    public function add_leftjoin($strLeftJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrLeftJoin[] = array('rawsql' => $strLeftJoin, 'values' => $mixValues);
    }

    /**
     * Ajoute une clause INNER JOIN à la requête
     *
     * @param string $strInnerJoin Clause INNER JOIN
     */
    public function add_innerjoin($strInnerJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrInnerJoin[] = array('rawsql' => $strInnerJoin, 'values' => $mixValues);
    }

    /**
     * Ajoute une clause GROUP BY à la requête
     * Si plusieurs clauses GROUP BY sont ajoutées, elles sont séparées par ","
     *
     * @param string $strGroupBy Clause ORDER BY
     */
    public function add_groupby($strGroupBy)
    {
        if (!in_array($strGroupBy, $this->arrGroupBy)) $this->arrGroupBy[] = $strGroupBy;
    }

    /**
     * Ajout d'une clause HAVING à la requête
     * Si plusieurs clauses HAVING sont ajoutées, elles sont séparées par AND
     *
     * @param string $strWhere Clause SQL brute
     * @param mixed $mixValues Valeurs
     *
     * @see add_where
     */

    public function add_having($strHaving, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrHaving[] = array('rawsql' => $strHaving, 'values' => $mixValues);
    }

    /**
     * Retourne la clause SELECT
     *
     * @return string
     */
    protected function get_select()
    {
        $arrSelect = array();
        foreach($this->arrSelect as $arrSelectDetail) $arrSelect[] = sqlformat::replace($arrSelectDetail, $this->objDb);

        return 'SELECT '.(empty($arrSelect) ? '*' : implode(', ', $arrSelect));
    }

    /**
     * Retourne la clause LEFT JOIN
     *
     * @return string
     */
    protected function get_leftjoin()
    {
        $arrLeftJoin = array();
        foreach($this->arrLeftJoin as $arrLeftJoinDetail) $arrLeftJoin[] = sqlformat::replace($arrLeftJoinDetail, $this->objDb);

        return empty($arrLeftJoin) ? '' : ' LEFT JOIN '.implode(' LEFT JOIN ', $arrLeftJoin);
    }

    /**
     * Retourne la clause INNER JOIN
     *
     * @return string
     */
    protected function get_innerjoin()
    {
        $arrInnerJoin = array();
        foreach($this->arrInnerJoin as $arrInnerJoinDetail) $arrInnerJoin[] = sqlformat::replace($arrInnerJoinDetail, $this->objDb);

        return empty($arrInnerJoin) ? '' : ' INNER JOIN '.implode(' INNER JOIN ', $arrInnerJoin);
    }

    /**
     * Retourne la clause GROUP BY
     *
     * @return string
     */
    protected function get_groupby() { return empty($this->arrGroupBy) ? '' : ' GROUP BY '.implode(', ', $this->arrGroupBy); }

    /**
     * Retourne la clause HAVING
     *
     * @return string
     */
    protected function get_having()
    {
        $arrHaving = array();
        foreach($this->arrHaving as $arrHavingDetail) $arrHaving[] = sqlformat::replace($arrHavingDetail, $this->objDb);

        return empty($arrHaving) ? '' : ' HAVING '.implode(' AND ', $arrHaving);
    }

    /**
     * Supprime la clause SELECT
     */
    public function remove_select() { $this->arrSelect = array(); }

    /**
     * Supprime la clause LEFT JOIN
     */
    public function remove_leftjoin() { $this->arrLeftJoin = array(); }

    /**
     * Supprime la clause INNER JOIN
     */
    public function remove_innerjoin() { $this->arrInnerJoin = array(); }

    /**
     * Supprime la clause GROUP BY
     */
    public function remove_groupby() { $this->arrGroupBy = array(); }

    /**
     * Supprime la clause HAVING
     */
    public function remove_having() { $this->arrHaving = array(); }

    /**
     * Génération de la requête SQL
     *
     * @return string Chaîne contenant la requête SQL générée
     */
    public function get_sql()
    {
        $strSql = '';

        if ($this->get_from() !== false)
        {
            $strSql = $this->get_select().
                $this->get_from().
                $this->get_innerjoin().
                $this->get_leftjoin().
                $this->get_where().
                $this->get_groupby().
                $this->get_having().
                $this->get_orderby().
                $this->get_limit().
                $this->get_raw();
        }
        elseif ($this->get_raw() !== false) {
            $strSql = $this->get_raw();
        }

        return $strSql;
    }
}
