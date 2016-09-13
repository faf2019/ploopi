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
 * Classe permettant de construire une requ�te SQL de type UPDATE
 */
class query_update extends query_sud
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
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSet = array();

        return parent::__construct('update', $objDb);
    }

    /**
     * Ajout d'une clause SET � la requ�te
     * Si plusieurs clauses SET sont ajout�es, elles sont s�par�es par ,
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
     * Retourne la clause FROM
     *
     * @return string
     */
    protected function get_from()
    {
        return empty($this->arrFrom) ? false : ' '.implode(', ', $this->arrFrom);
    }

    /**
     * Retourne la clause SET
     *
     * @return string
     */
    protected function get_set()
    {
        $arrSet = array();
        foreach($this->arrSet as $arrSetDetail) $arrSet[] = sqlformat::replace($arrSetDetail, $this->objDb);

        return empty($arrSet) ? '' : ' SET '.implode(', ', $arrSet);
    }

    /**
     * G�n�ration de la requ�te SQL
     *
     * @return string Cha�ne contenant la requ�te SQL g�n�r�e
     */
    public function get_sql()
    {
        $strSql = '';

        if ($this->get_from() !== false)
        {
            $strSql = 'UPDATE'.
                $this->get_from().
                $this->get_set().
                $this->get_where().
                $this->get_orderby().
                $this->get_limit().
                $this->get_raw();
        }

        return $strSql;
    }
}
