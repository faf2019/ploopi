<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Classe permettant de construire une requête SQL
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
        if (!parent::__construct($objDb)) return false;

        $this->arrFrom = array();
        $this->arrWhere = array();
        $this->arrOrderBy = array();
        $this->strLimit = null;

        if (!in_array($strType, self::$arrType))
        {
            trigger_error('Ce type de requête n\'existe pas', E_USER_ERROR);
            return false;
        }
        else
        {
            $this->strType = $strType;
            return true;
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
     *
     * Numérotation des arguments possible : %1$f, %2$d, %4$r
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
        foreach($this->arrWhere as $arrWhereDetail) $arrWhere[] = sqlformat::replace($arrWhereDetail, $this->objDb);

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
