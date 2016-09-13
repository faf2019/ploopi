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
 * Gestion de requ�tes SQL construites
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe permettant de construire une requ�te SQL
 */
abstract class query
{
    /**
     * Connexion � la BDD
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
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        if (!is_null($objDb)) $this->objDb = $objDb;
        else { global $db; $this->objDb = $db; }

        $this->arrRaw = array();

        return true;
    }

    /**
     * Ajoute une clause SQL brute, non filtr�e
     *
     * @param string $strRaw cha�ne SQL
     */
    /*
    public function add_raw($strRaw)
    {
        $this->arrRaw[] = $strRaw;
    }*/

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
    /*
    protected function get_raw()
    {
        return empty($this->arrRaw) ? false : ' '.implode(' ', $this->arrRaw);
    }
    */

    protected function get_raw()
    {
        $arrRaw = array();
        foreach($this->arrRaw as $arrRawDetail) $arrRaw[] = sqlformat::replace($arrRawDetail, $this->objDb);

        return empty($arrRaw) ? false : implode(' ', $arrRaw);
    }

    /**
     * Ex�cute la requ�te SQL
     *
     * @return ploopi_recordset
     */
    public function execute()
    {
        return(new recordset($this->objDb, $this->objDb->query($this->get_sql())));
    }

    /**
     * Permet de red�finir la connexion � la BDD
     *
     * @param resource $objDb Connexion � la BDD
     */
    public function set_db($objDb) { $this->objDb = $objDb; }


    /**
     * Retourne le nombre de lignes affect�es lors de la derni�res requ�te INSERT, UPDATE, REPLACE, DELETE
     *
     * @return integer nombre de lignes affect�es lors de la derni�res requ�te INSERT, UPDATE, REPLACE, DELETE
     */
    public function affectedrows()
    {
        return $this->objDb->affectedrows();
    }


    /**
     * Permet de red�finir la connexion � la BDD au r�veil de l'objet  (utile notamment apr�s d�s�rialisation)
     */
    public function __wakeup()
    {
        global $db;
        $this->objDb = $db;
    }
}
