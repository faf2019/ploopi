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
 * Gestion de requ�tes SQL construites
 *
 * @package ploopi
 * @subpackage ploopi_query
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe abstraite permettant de formater un contenu SQL � la mani�re de printf
 */
abstract class ploopi_sqlformat
{
    /**
     * Regex utilis�e pour d�tecter point d'injection de donn�es
     *
     * @var string
     */
    private static $strRegExFormat = '|%(([0-9]*)\$){0,1}([s,d,f,t,e,g])|';
    
    /**
     * Num�ro du param�tre trait�
     *
     * @var int
     * @see replace
     */
    private static $intNumParam = 0;
    
    /**
     * Tableau des valeurs de remplacement
     *
     * @var array
     * @see replace
     */
    private static $arrValues = null;
    
    /**
     * Connexion � la BDD
     *
     * @var resource
     */
    private static $objDb = null;
    
    /**
     * M�thode de remplacement appel�e en callback via preg_replace_callback
     *
     * @param array $arrMatches Tableau contenant le texte satisfaisant au masque de recherche
     * @return string cha�ne modifi�e
     * @link http://fr.php.net/manual/fr/function.preg-replace-callback.php
     */
    private static function cb_replace($arrMatches)
    {
        global $db;
        
        if (sizeof($arrMatches) == 4)
        {
            $intNumParam = empty($arrMatches[2]) ? ++self::$intNumParam - 1 : intval($arrMatches[2]) - 1;

            $strValue = isset(self::$arrValues[$intNumParam]) ? self::$arrValues[$intNumParam] : null;

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
                                $strListValue = "'".self::$objDb->addslashes($strListValue)."'";
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
                    $strValue = "'".self::$objDb->addslashes($strValue)."'";
                break;
            }

            return $strValue;
        }
    }
    
    /**
     * M�thode publique de remplacement
     *
     * @param array $arrData tableau associatif contenant la cha�ne SQL brute (rawsql) et les valeurs de remplacement (values)
     * @param resource $objDb connexion � la BDD
     * @return string cha�ne modifi�e
     */
    public static function replace($arrData, $objDb = null)
    {
        // Initialisation du num�ro de param�tre en cours de traitement
        self::$intNumParam = 0;
        
        // Initialisation de la connexion � la BDD
        if (is_null($objDb)) { global $db; self::$objDb = $db; }
        else self::$objDb = $objDb;
        
        // Initialisation des valeurs de remplacement
        self::$arrValues = $arrData['values'];
        
        // Remplacement des variables selon la regex
        return preg_replace_callback(self::$strRegExFormat, array('self', 'cb_replace'), $arrData['rawsql']);
    }
}

/**
 * Classe permettant de construire une requ�te SQL
 */
abstract class ploopi_query
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
     * Clause LIMIT
     *
     * @var string
     */
    private $strLimit;

    /**
     * Connexion � la BDD
     *
     * @var resource
     */
    private $objDb;
    
    /**
     * Diff�rents types accept�s pour un �l�ment
     *
     * @var array
     */
    protected static $arrType = array(
        'select',
        'insert',
        'update',
        'delete',
        'raw'
    );    
    
    private $strType;
    
    /**
     * Constructeur de la classe
     *
     * @param string $strType type de requ�te
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($strType = 'select', $objDb = null)
    {
        $this->arrSelect = array();
        $this->arrFrom = array();
        $this->arrInnerJoin = array();
        $this->arrLeftJoin = array();
        $this->arrWhere = array();
        $this->arrGroupBy = array();
        $this->arrHaving = array();
        $this->arrOrderBy = array();
        $this->strLimit = null;
        
        if (!is_null($objDb)) $this->objDb = $objDb;
        else { global $db; $this->objDb = $db; }
        
        if (!in_array($strType, ploopi_query::$arrType))
        {
            trigger_error('Ce type de requ�te n\'existe pas', E_USER_ERROR);
            return false;
        }
        else 
        { 
            $this->strType = $strType;
            return true;
        }           
    }
    
    public function add_select($strSelect)
    {
        if (!in_array($strSelect, $this->arrSelect)) $this->arrSelect[] = $strSelect;
    }    
    
    /**
     * Ajout d'une clause WHERE � la requ�te
     * Si plusieurs clauses WHERE sont ajout�es, elles sont s�par�es par AND 
     * 
     * Format support�s : 
     * %d int
     * %f float
     * %s string
     * %e int list
     * %g float list
     * %t string list
     * %r raw
     *
     * Num�rotation des arguments possible : %1$f, %2$d, %4$r
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
     * Ajoute une clause LEFT JOIN � la requ�te
     * 
     * @param string $strLeftJoin Clause LEFT JOIN
     */
    public function add_leftjoin($strLeftJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrLeftJoin[] = array('rawsql' => $strLeftJoin, 'values' => $mixValues);
    }
    
    /**
     * Ajoute une clause INNER JOIN � la requ�te
     * 
     * @param string $strInnerJoin Clause INNER JOIN
     */
    public function add_innerjoin($strInnerJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrInnerJoin[] = array('rawsql' => $strInnerJoin, 'values' => $mixValues);
    }
    
    /**
     * Ajoute une clause FROM � la requ�te
     * Si plusieurs clauses FROM sont ajout�es, elles sont s�par�es par "," 
     * 
     * @param string $strFrom Clause FROM
     */
    public function add_from($strFrom)
    {
        if (!in_array($strFrom, $this->arrFrom)) $this->arrFrom[] = $strFrom;
    }    

    /**
     * Ajoute une clause GROUP BY � la requ�te
     * Si plusieurs clauses GROUP BY sont ajout�es, elles sont s�par�es par ","
     *  
     * @param string $strGroupBy Clause ORDER BY
     */
    public function add_groupby($strGroupBy)
    {
        if (!in_array($strGroupBy, $this->arrGroupBy)) $this->arrGroupBy[] = $strGroupBy;
    }

    /**
     * Ajout d'une clause HAVING � la requ�te
     * Si plusieurs clauses HAVING sont ajout�es, elles sont s�par�es par AND 
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
     * Ajoute une clause ORDER BY � la requ�te
     * Si plusieurs clauses ORDER BY sont ajout�es, elles sont s�par�es par ","
     *  
     * @param string $strOrderBy Clause ORDER BY
     */
    public function add_orderby($strOrderBy)
    {
        if (!in_array($strOrderBy, $this->arrOrderBy)) $this->arrOrderBy[] = $strOrderBy;
    }

    /**
     * D�finit la clause LIMIT de la requ�te
     *
     * @param string $strLimit
     */
    public function add_limit($strLimit)
    {
        $this->strLimit = $strLimit;
    }
    
    /**
     * G�n�ration de la requ�te SQL
     *
     * @return string Cha�ne contenant la requ�te SQL g�n�r�e
     */
    public function get_sql()
    {
        $strSql = '';

        if (!empty($this->arrFrom))
        {
            switch($this->strType)
            {
                case 'select':
                    $strSql = 'SELECT '.(empty($this->arrSelect) ? '*' : implode(', ', $this->arrSelect)).' FROM '.implode(', ', $this->arrFrom);
                break;
                
                case 'insert':
                    $strSql = 'INSERT';
                break;
                
                case 'update':
                    $strSql = 'UPDATE';
                break;

                case 'delete':
                    $strSql = 'DELETE FROM '.implode(', ', $this->arrFrom);
                break;
                
                case 'raw':
                default:
                    $strSql = '';
                break;
                
            }
            
            // LEFT JOIN
            $arrLeftJoin = array();
            foreach($this->arrLeftJoin as $arrLeftJoinDetail) $arrLeftJoin[] = ploopi_sqlformat::replace($arrLeftJoinDetail, $this->objDb);
            
            if (!empty($arrLeftJoin)) $strSql .= ' LEFT JOIN '.implode(' LEFT JOIN ', $arrLeftJoin);
            
            // INNER JOIN
            $arrInnerJoin = array();
            foreach($this->arrInnerJoin as $arrInnerJoinDetail) $arrInnerJoin[] = ploopi_sqlformat::replace($arrInnerJoinDetail, $this->objDb);
            
            if (!empty($arrInnerJoin)) $strSql .= ' INNER JOIN '.implode(' INNER JOIN ', $arrInnerJoin);
            
            // WHERE
            $arrWhere = array();
            foreach($this->arrWhere as $arrWhereDetail) $arrWhere[] = ploopi_sqlformat::replace($arrWhereDetail, $this->objDb);

            if (!empty($arrWhere)) $strSql .= ' WHERE '.implode(' AND ', $arrWhere);
            
            // GROUP BY
            if (!empty($this->arrGroupBy)) $strSql .= ' GROUP BY '.implode(', ', $this->arrGroupBy);

            // HAVING
            $arrHaving = array();
            foreach($this->arrHaving as $arrHavingDetail) $arrHaving[] = ploopi_sqlformat::replace($arrHavingDetail, $this->objDb);
            
            if (!empty($arrHaving)) $strSql .= ' HAVING '.implode(' AND ', $arrHaving);
            
            // ORDERBY
            if (!empty($this->arrOrderBy)) $strSql .= ' ORDER BY '.implode(', ', $this->arrOrderBy);
            
            // LIMIT
            if (!empty($this->strLimit)) $strSql .= ' LIMIT '.$this->strLimit;
        }

        return $strSql;
    }

    /**
     * Ex�cute la requ�te SQL
     *
     * @return ploopi_recordset
     */
    public function execute()
    {
        return(new ploopi_recordset($this->objDb, $this->objDb->query($this->get_sql())));
    }
}


/**
 * Classe permettant de construire une requ�te SQL
 */
class ploopi_query_select extends ploopi_query
{
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        return parent::__construct('select', $objDb);
    }    
}

/**
 * Classe permettant de construire une requ�te SQL
 */
class ploopi_query_delete extends ploopi_query
{
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        return parent::__construct('delete', $objDb);
    }    
}


/**
 * Classe permettant de construire une requ�te SQL
 */
class ploopi_query_update extends ploopi_query
{
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        return parent::__construct('update', $objDb);
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

    public function getarray($booFirstColKey = false)
    {
        return $this->objDb->getarray($this->resRs, $booFirstColKey);
    }
    

}
?>
