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
 * Gestion de requêtes SQL construites de type DELETE
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class query_delete extends query_sud
{

    /**
     * Tableau de la clause DELETE
     *
     * @var array
     */
    private $arrDelete;

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
     * Constructeur de la classe
     *
     * @param db $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrDelete = array();
        $this->arrInnerJoin = array();
        $this->arrLeftJoin = array();

        parent::__construct('delete', $objDb);
    }

    /**
     * Ajoute une clause DELETE
     *
     * @param string $strSelect Clause DELETE
     * @param mixed $mixValues valeur
     */
    public function add_delete($strDelete)
    {
        $this->arrDelete[] = $strDelete;
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
     * Retourne la clause DELETE
     *
     * @return string
     */
    protected function get_delete()
    {
        return 'DELETE '.(empty($this->arrDelete) ? '' : implode(', ', $this->arrDelete));
    }

    /**
     * Retourne la clause LEFT JOIN
     *
     * @return string
     */
    protected function get_leftjoin()
    {
        $arrLeftJoin = array();
        foreach($this->arrLeftJoin as $arrLeftJoinDetail) $arrLeftJoin[] = ploopi_sqlformat::replace($arrLeftJoinDetail, $this->objDb);

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
        foreach($this->arrInnerJoin as $arrInnerJoinDetail) $arrInnerJoin[] = ploopi_sqlformat::replace($arrInnerJoinDetail, $this->objDb);

        return empty($arrInnerJoin) ? '' : ' INNER JOIN '.implode(' INNER JOIN ', $arrInnerJoin);
    }

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
            $strSql = $this->get_delete().
                $this->get_from().
                $this->get_innerjoin().
                $this->get_leftjoin().
                $this->get_where().
                $this->get_orderby().
                $this->get_limit().
                $this->get_raw();
        }

        return $strSql;
    }
}
