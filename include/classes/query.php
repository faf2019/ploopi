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
 * Classe abstraite permettant de formater un contenu SQL à la manière de printf
 */
abstract class ploopi_sqlformat
{
    /**
     * Regex utilisée pour détecter point d'injection de données
     *
     * @var string
     */
    private static $strRegExFormat = '|%(([0-9]*)\$){0,1}([s,d,f,t,e,g])|';
    
    /**
     * Numéro du paramètre traité
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
     * Connexion à la BDD
     *
     * @var resource
     */
    private static $objDb = null;
    
    /**
     * Méthode de remplacement appelée en callback via preg_replace_callback
     *
     * @param array $arrMatches Tableau contenant le texte satisfaisant au masque de recherche
     * @return string chaîne modifiée
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
     * Méthode publique de remplacement
     *
     * @param array $arrData tableau associatif contenant la chaîne SQL brute (rawsql) et les valeurs de remplacement (values)
     * @param resource $objDb connexion à la BDD
     * @return string chaîne modifiée
     */
    public static function replace($arrData, $objDb = null)
    {
        // Initialisation du numéro de paramètre en cours de traitement
        self::$intNumParam = 0;
        
        // Initialisation de la connexion à la BDD
        if (is_null($objDb)) { global $db; self::$objDb = $db; }
        else self::$objDb = $objDb;
        
        // Initialisation des valeurs de remplacement
        self::$arrValues = $arrData['values'];
        
        // Remplacement des variables selon la regex
        return preg_replace_callback(self::$strRegExFormat, array('self', 'cb_replace'), $arrData['rawsql']);
    }
}

/**
 * Classe permettant de construire une requête SQL
 */
abstract class ploopi_query
{
    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    protected $objDb;    
    
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        if (!is_null($objDb)) $this->objDb = $objDb;
        else { global $db; $this->objDb = $db; }
        
        return true;
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

/**
 * Classe permettant de construire une requête SQL
 */
abstract class ploopi_query_sud extends ploopi_query
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
        $this->strLimit = $strLimit;
    }
    
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
        foreach($this->arrWhere as $arrWhereDetail) $arrWhere[] = ploopi_sqlformat::replace($arrWhereDetail, $this->objDb);

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


/**
 * Classe permettant de construire une requête SQL de type SELECT
 */
class ploopi_query_select extends ploopi_query_sud
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
        
        return parent::__construct('select', $objDb);
    }    
    
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
        foreach($this->arrSelect as $arrSelectDetail) $arrSelect[] = ploopi_sqlformat::replace($arrSelectDetail, $this->objDb);
        
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
        foreach($this->arrHaving as $arrHavingDetail) $arrHaving[] = ploopi_sqlformat::replace($arrHavingDetail, $this->objDb);
        
        return empty($arrHaving) ? '' : ' HAVING '.implode(' AND ', $arrHaving);
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
            $strSql = $this->get_select().
                $this->get_from(). 
                $this->get_leftjoin(). 
                $this->get_innerjoin().
                $this->get_where().
                $this->get_groupby().
                $this->get_having().
                $this->get_orderby().
                $this->get_limit();
        }
        
        return $strSql;
    }
}

/**
 * Classe permettant de construire une requête SQL de type DELETE
 */
class ploopi_query_delete extends ploopi_query_sud
{
    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     */
    public function __construct($objDb = null)
    {
        return parent::__construct('delete', $objDb);
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
            echo $strSql = 'DELETE'.
                $this->get_from(). 
                $this->get_where().
                $this->get_orderby().
                $this->get_limit();
        }
        
        return $strSql;
    }    
}


/**
 * Classe permettant de construire une requête SQL de type UPDATE
 */
class ploopi_query_update extends ploopi_query_sud
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
        
        return parent::__construct('update', $objDb);
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
        
        if ($this->get_from() !== false)
        {
            $strSql = 'UPDATE'.
                $this->get_from(). 
                $this->get_set(). 
                $this->get_where().
                $this->get_orderby().
                $this->get_limit();
        }
        
        return $strSql;
    }    
}

/**
 * Classe permettant de construire une requête SQL de type INSERT
 */
class ploopi_query_insert extends ploopi_query
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
            $strSql = 'INSERT INTO '.$this->get_table().$this->get_set();
        }
        
        return $strSql;
    }      
    
}

/**
 * Classe de gestion des recordsets retournés par ploopi_query
 */
class ploopi_recordset
{
    /**
     * Connexion à la BDD
     *
     * @var resource
     */
    private $objDb;   
    
    /**
     * Recordset courant
     *
     * @var resource
     */
    private $resRs;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion à la BDD
     * @param resource $resRs Recordset
     */
    public function __construct($objDb, $resRs)
    {
        $this->objDb = $objDb;
        $this->resRs = $resRs;
    }

    /**
     * Retourne l'enregistrement courant du recordset et avance le pointeur sur l'enregistrement suivant
     *
     * @return array
     */
    public function fetchrow()
    {
        return $this->objDb->fetchrow($this->resRs);
    }

    /**
     * Retourne le nombre d'enregistrements du recordset
     *
     * @return integer
     */
    public function numrows()
    {
        return $this->objDb->numrows($this->resRs);
    }
    
    /**
     * Retourne dans un tableau le contenu du recordset
     *
     * @param $booFirstColKey $firstcolkey true si la première colonne doit servir d'index pour le tableau (optionnel)
     * @return mixed un tableau indexé contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */    
    public function getarray($booFirstColKey = false)
    {
        return $this->objDb->getarray($this->resRs, $booFirstColKey);
    }
    
    /**
     * Retourne au format JSON le contenu du recordset
     *
     * @param boolean $booUtf8 true si le contenu doit être encodé en utf8, false sinon (true par défaut)
     * @return string une chaîne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */    
    public function getjson($booUtf8 = true)
    {
        return $this->objDb->getjson($this->resRs, $booUtf8);
    }    
}
?>