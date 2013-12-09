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
 * Gestion d'une collection d'objets de type "data_object"
 *
 * @package ploopi
 * @subpackage data_object
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

require_once './include/classes/query.php';

/**
 * Classe permettant de g�rer une collection d'objets de type "data_object"
 *
 */
class data_object_collection
{
    /**
     * Nom de la classe g�r�e dans la collection
     *
     * @var string
     */
    private $strClassName;

    /**
     * Connexion � la bdd
     *
     * @var resource
     */
    private $objDb;

    /**
     * Requ�te
     *
     * @var unknown_type
     */
    private $objQuery;

    /**
     * Tableau permettant de construire la clause WHERE
     *
     * @var array
     */
    private $arrWhere;

    /**
     * Tableau permettant de construire la clause ORDER BY
     *
     * @var array
     */
    private $arrOrderBy;


    /**
     * Constructeur de la classe
     *
     * @param string $strClassName Nom de la classe g�r�e dans la collection (cette classe doit �tre h�rit�e de data_object)
     * @param resource $objDb Connexion � la base de donn�es
     */
    public function __construct($strClassName, &$objDb = null)
    {
        $this->strClassName = $strClassName;

        if (!is_null($objDb)) $this->objDb = $objDb;
        else { global $db; $this->objDb = &$db; }

        //On v�rifie que la classe existe
        if (empty($this->strClassName) || !class_exists($this->strClassName)) throw new Exception("data_object_collection : classe '{$this->strClassName}' inconnue");

        //On tente de cr�er une instance de la classe
        ploopi_unset_error_handler();
        $objDoDescription = new $this->strClassName();
        ploopi_set_error_handler();

        //On v�rifie le type de l'objet obtenu et s'il h�rite de "data_object"
        if (empty($objDoDescription) || !is_subclass_of($objDoDescription, 'data_object')) throw new Exception("data_object_collection : la classe '{$this->strClassName}' n'est pas h�rit�e de 'data_object'");

        $this->objQuery = new ploopi_query_select($this->objDb);
        $this->objQuery->add_select('`'.$objDoDescription->gettablename().'`.*');
        $this->objQuery->add_from('`'.$objDoDescription->gettablename().'`');
    }

    /**
     * Ajoute une clause FROM � la collection
     *
     * @param string $strFrom clause from
     * @see ploopi_query
     */
    public function add_from($strFrom) { $this->objQuery->add_from($strFrom); }

    /**
     * Ajoute une clause INNER JOIN � la collection
     *
     * @param string $strInnerJoin Clause INNER JOIN
     */
    public function add_innerjoin($strInnerJoin, $mixValues = null) { $this->objQuery->add_innerjoin($strInnerJoin, $mixValues); }

    /**
     * Ajoute une clause WHERE � la requ�te
     *
     * @param string $strWhere clause sql non pr�par�e
     * @param mixed $mixValues tableau des variables ou variable seule � ins�rer dans la clause sql
     * @see ploopi_query
     */
    public function add_where($strWhere, $mixValues = null) { $this->objQuery->add_where($strWhere, $mixValues); }

    /**
     * Ajoute une clause ORDER BY � la collection
     *
     * @param string $strOrderBy clause sql
     * @see ploopi_query
     */
    public function add_orderby($strOrderBy) { $this->objQuery->add_orderby($strOrderBy); }

    /**
     * Retourne les objets de la collection
     *
     * @return array tableau d'objets du type demand�
     */
    public function get_objects($booFirstColKey = false)
    {
        $arrResult = array();

        $objRs = $this->objQuery->execute();

        while ($row = $objRs->fetchrow())
        {
            $objDoRecord = new $this->strClassName();

            $objDoRecord->open_row($row);

            if ($booFirstColKey) $arrResult[$objDoRecord->gethash()] = $objDoRecord;
            else $arrResult[] = $objDoRecord;
        }

        return $arrResult;
    }
}
