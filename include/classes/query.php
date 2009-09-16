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
 * Gestion de requêtes SQL construites
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe permettant de formater un contenu SQL à la manière de printf
 */
class ploopi_sqlformat
{
    /**
     * Numéro du paramètre traité
     *
     * @var int
     * @see replace
     */
    private $intNumParam = 0;
    
    /**
     * Tableau des valeurs de remplacement
     *
     * @var array
     * @see replace
     */
    private $arrValues = null;
    
    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    private $objDb = null;
    
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     * @param array $arrValues Tableau des valeurs de remplacement
     */
    public function __construct($objDb, $arrValues)
    {
        $this->intNumParam = 0;
        $this->objDb = $objDb;
        $this->arrValues = $arrValues;
    }

    /**
     * Méthode de remplacement appelée en callback via preg_replace_callback
     *
     * @param array $arrMatches Tableau contenant le texte satisfaisant au masque de recherche
     * @return string chaîne modifiée
     * @link http://fr.php.net/manual/fr/function.preg-replace-callback.php
     */
    public function replace($arrMatches)
    {
        global $db;
        
        if (sizeof($arrMatches) == 4)
        {
            $intNumParam = empty($arrMatches[2]) ? ++$this->intNumParam - 1 : intval($arrMatches[2]) - 1;

            $strValue = isset($this->arrValues[$intNumParam]) ? $this->arrValues[$intNumParam] : null;

            switch($arrMatches[3])
            {
                case 't': // list string
                case 'e': // list integer
                case 'g': // list float
                    $arrValues = split(',', $strValue);
                    $arrValues = ploopi_array_map('trim', $arrValues);
                    foreach($arrValues as &$strListValue)
                    {
                        switch($arrMatches[3])
                        {
                            case 't':
                                $strListValue = "'".$this->objDb->addslashes($strListValue)."'";
                            break;
                            case 'e':
                                $strListValue = intval($strListValue);
                            break;
                            case 'g':
                                $strListValue = floatval($strListValue);
                            break;
                        }
                    }
                    $strValue = implode(',', $arrValues);
                break;
                
                case 'd': // integer
                    $strValue = intval($strValue);
                break;

                case 'f': // float
                    $strValue = floatval($strValue);
                break;

                case 's': // string
                default:
                    $strValue = "'".$this->objDb->addslashes($strValue)."'";
                break;
            }

            return $strValue;
        }
    }
}

/**
 * Classe permettant de construire une requête SQL
 */
class ploopi_query
{
    /**
     * Tableau de la clause SELECT
     *
     * @var array
     */
    private $arrSelect;

    /**
     * Tableau de la clause FROM
     *
     * @var array
     */
    private $arrFrom;

    /**
     * Tableau de la clause WHERE
     *
     * @var array
     */
    private $arrWhere;

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
     * Tableau de la clause ORDER BY
     *
     * @var array
     */
    private $arrOrderBy;

    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    private $objDb;
    
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSelect = array();
        $this->arrFrom = array();
        $this->arrWhere = array();
        $this->arrGroupBy = array();
        $this->arrHaving = array();
        $this->arrOrderBy = array();
        
        if (!is_null($objDb)) $this->objDb = $objDb;
        else { global $db; $this->objDb = $db; }
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
     * Ajoute une clause FROM à la requête
     * Si plusieurs clauses FROM sont ajoutées, elles sont séparées par "," 
     * 
     * @param string $strFrom Clause FROM
     */
    public function add_from($strFrom)
    {
        if (!in_array($strFrom, $this->arrFrom)) $this->arrFrom[] = $strFrom;
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
        
    public function add_having($strHaving, $arrValues)
    {
        $this->strHaving = $strHaving;
        $this->arrHavingValues = $arrValues;
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
     * Génération de la requête SQL
     *
     * @return string Chaîne contenant la requête SQL générée
     */
    public function get_sql()
    {
        $strSql = '';

        if (!empty($this->arrFrom))
        {
            $strSql = 'SELECT '.(empty($this->arrSelect) ? '*' : implode(', ', $this->arrSelect)).' FROM '.implode(', ', $this->arrFrom);

            $arrWhere = array();
            foreach($this->arrWhere as $arrWhereDetail) $arrWhere[] = preg_replace_callback('|%(([0-9]*)\$){0,1}([s,d,f,t,e,g])|', array(new ploopi_sqlformat($this->objDb, $arrWhereDetail['values']), 'replace'), $arrWhereDetail['rawsql']);

            if (!empty($arrWhere)) $strSql .= ' WHERE '.implode(' AND ', $arrWhere);

            if (!empty($this->arrGroupBy)) $strSql .= ' GROUP BY '.implode(', ', $this->arrGroupBy);

            $arrHaving = array();
            foreach($this->arrHaving as $arrHavingDetail) $arrHaving[] = preg_replace_callback('|%(([0-9]*)\$){0,1}([s,d,f,t,e,g])|', array(new ploopi_sqlformat($this->objDb, $arrHavingDetail['values']), 'replace'), $arrHavingDetail['rawsql']);
            
            if (!empty($arrHaving)) $strSql .= ' HAVING '.implode(' AND ', $arrHaving);
            
            if (!empty($this->arrOrderBy)) $strSql .= ' ORDER BY '.implode(', ', $this->arrOrderBy);
        }

        return $strSql;
    }

    /**
     * Exécute la requête SQL
     *
     * @return ploopi_recordset
     */
    public function execute()
    {
        return(new ploopi_recordset($this->objDb, $this->objDb->query($this->get_sql())));
    }
}

class ploopi_recordset
{
    private $objDb;
    
    private $resRs;

    public function __construct($objDb, $resRs)
    {
        $this->objDb = $objDb;
        $this->resRs = $resRs;
    }
    
    public function fetchrow()
    {
        return $this->objDb->fetchrow($this->resRs);
    }
}
?>
