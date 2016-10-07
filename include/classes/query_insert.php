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

namespace ploopi;

use ploopi;

/**
 * Classe permettant de construire une requête SQL de type INSERT
 */
class query_insert extends query
{
    /**
     * Tableau de la clause SET
     *
     * @var array
     */
    private $arrSet;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSet = array();

        return parent::__construct($objDb);
    }

    /**
     * Définit la table
     *
     * @param string $strTable nom de la table
     */
    public function set_table($strTable)
    {
        $this->arrFrom = array($strTable);
    }

    /**
     * Ajout d'une clause SET à la requête
     * Si plusieurs clauses SET sont ajoutées, elles sont séparées par ,
     *
     * @param string $strSet Clause SQL brute
     * @param mixed $mixValues Valeurs
     */
    public function add_set($strSet, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrSet[] = array('rawsql' => $strSet, 'values' => $mixValues);
    }

    /**
     * Retourne la table
     *
     * @return string
     */
    protected function get_table()
    {
        return empty($this->arrFrom) ? false : current($this->arrFrom);
    }

    /**
     * Retourne la clause SET
     *
     * @return string
     */
    protected function get_set()
    {
        $arrSet = array();
        foreach($this->arrSet as $arrSetDetail) $arrSet[] = ploopi_sqlformat::replace($arrSetDetail, $this->objDb);

        return empty($arrSet) ? '' : ' SET '.implode(', ', $arrSet);
    }

    /**
     * Génération de la requête SQL
     *
     * @return string Chaîne contenant la requête SQL générée
     */
    public function get_sql()
    {
        $strSql = '';

        if ($this->get_table() !== false)
        {
            $strSql = 'INSERT INTO '.$this->get_table().$this->get_set().$this->get_raw();
        }

        return $strSql;
    }

}
