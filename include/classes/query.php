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
 * Gestion de requêtes SQL construites
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class query
{
    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    protected $objDb;

    /**
     * Tableau de clauses SQL brutes
     *
     * @var array
     */
    protected $arrRaw;

    /**
     * Constructeur de la classe
     *
     * @param db $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        if (!is_null($objDb)) $this->objDb = $objDb;
        else { $this->objDb = db::get(); }

        $this->arrRaw = array();
    }

    /**
     * Ajoute une clause SQL brute, non filtrée
     *
     * @param string $strRaw chaîne SQL
     */
    public function add_raw($strRaw, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrRaw[] = array('rawsql' => $strRaw, 'values' => $mixValues);
    }


   /**
     * Retourne la clause Brute
     *
     * @return string
     */
    protected function get_raw()
    {
        $arrRaw = array();
        foreach($this->arrRaw as $arrRawDetail) $arrRaw[] = sqlformat::replace($arrRawDetail, $this->objDb);

        return empty($arrRaw) ? false : implode(' ', $arrRaw);
    }

    /**
     * Exécute la requête SQL
     *
     * @return ploopi_recordset
     */
    public function execute()
    {
        return(new recordset($this->objDb, $this->objDb->query($this->get_sql())));
    }

    /**
     * Permet de redéfinir la connexion à la BDD
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function set_db($objDb) { $this->objDb = $objDb; }


    /**
     * Retourne le nombre de lignes affectées lors de la dernières requête INSERT, UPDATE, REPLACE, DELETE
     *
     * @return integer nombre de lignes affectées lors de la dernières requête INSERT, UPDATE, REPLACE, DELETE
     */
    public function affectedrows()
    {
        return $this->objDb->affectedrows();
    }


    /**
     * Permet de redéfinir la connexion à la BDD au réveil de l'objet  (utile notamment après désérialisation)
     */
    public function __wakeup()
    {
        $db = db::get();
        $this->objDb = $db;
    }
}
