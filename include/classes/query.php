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
    private static $strRegExFormat = '|%(([0-9]*)\$){0,1}([s,d,f,t,e,g,r])|';

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

            // La valeur correspondante du param�tre peut �tre un tableau de valeurs ou une valeur simple
            $mixValue = isset(self::$arrValues[$intNumParam]) ? self::$arrValues[$intNumParam] : null;

            switch($arrMatches[3])
            {
                case 't': // list string
                case 'e': // list integer
                case 'g': // list float
                    $arrValues = is_array($mixValue) ? $mixValue : preg_split('/,/', $mixValue);
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
                    $strValue = intval($mixValue);
                break;

                case 'f': // float
                    $strValue = floatval($mixValue);
                break;

                case 's': // string
                    $strValue = "'".self::$objDb->addslashes($mixValue)."'";
                break;

                case 'r': // raw
                default:
                    $strValue = $mixValue;
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
        foreach($this->arrRaw as $arrRawDetail) $arrRaw[] = ploopi_sqlformat::replace($arrRawDetail, $this->objDb);

        return empty($arrRaw) ? false : implode(' ', $arrRaw);
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

/**
 * Classe permettant de construire une requ�te SQL
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
     * Diff�rents types accept�s pour un �l�ment
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
     * @param string $strType type de requ�te
     * @param resource $objDb Connexion � la BDD
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
            trigger_error('Ce type de requ�te n\'existe pas', E_USER_ERROR);
            return false;
        }
        else
        {
            $this->strType = $strType;
            return true;
        }
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
     * Ajoute une clause FROM � la requ�te (select/delete/update uniquement)
     * Si plusieurs clauses FROM sont ajout�es, elles sont s�par�es par ","
     *
     * @param string $strFrom Clause FROM
     */
    public function add_from($strFrom)
    {
        if (!in_array($strFrom, $this->arrFrom)) $this->arrFrom[] = $strFrom;
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
 * Classe permettant de construire une requ�te SQL de type SELECT
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
     * Tableau de la clause RIGHT JOIN
     *
     * @var array
     */
    private $arrRightJoin;

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
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSelect = array();
        $this->arrInnerJoin = array();
        $this->arrLeftJoin = array();
        $this->arrRightJoin = array();
        $this->arrGroupBy = array();
        $this->arrHaving = array();

        return parent::__construct('select', $objDb);
    }

    /**
     * Ajoute une clause SELECT
     *
     * @param string $strSelect Clause SELECT
     * @param mixed $mixValues valeur(s)
     */
    public function add_select($strSelect, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrSelect[] = array('rawsql' => $strSelect, 'values' => $mixValues);
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
     * Ajoute une clause RIGHT JOIN � la requ�te
     *
     * @param string $strRightJoin Clause RIGHT JOIN
     */
    public function add_rightjoin($strRightJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrRightJoin[] = array('rawsql' => $strRightJoin, 'values' => $mixValues);
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
     * Retourne la clause RIGHT JOIN
     *
     * @return string
     */
    protected function get_rightjoin()
    {
        $arrRightJoin = array();
        foreach($this->arrRightJoin as $arrRightJoinDetail) $arrRightJoin[] = ploopi_sqlformat::replace($arrRightJoinDetail, $this->objDb);

        return empty($arrRightJoin) ? '' : ' RIGHT JOIN '.implode(' RIGHT JOIN ', $arrRightJoin);
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
     * Supprime la clause SELECT
     */
    public function remove_select() { $this->arrSelect = array(); }

    /**
     * Supprime la clause LEFT JOIN
     */
    public function remove_leftjoin() { $this->arrLeftJoin = array(); }

    /**
     * Supprime la clause RIGHT JOIN
     */
    public function remove_rightjoin() { $this->arrRightJoin = array(); }

    /**
     * Supprime la clause INNER JOIN
     */
    public function remove_innerjoin() { $this->arrInnerJoin = array(); }

    /**
     * Supprime la clause GROUP BY
     */
    public function remove_groupby() { $this->arrGroupBy = array(); }

    /**
     * Supprime la clause HAVING
     */
    public function remove_having() { $this->arrHaving = array(); }

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
            $strSql = $this->get_select().
                $this->get_from().
                $this->get_innerjoin().
                $this->get_leftjoin().
                $this->get_rightjoin().
                $this->get_where().
                $this->get_groupby().
                $this->get_having().
                $this->get_orderby().
                $this->get_limit().
                $this->get_raw();
        }
        elseif ($this->get_raw() !== false) {
            $strSql = $this->get_raw();
        }

        return $strSql;
    }
}

/**
 * Classe permettant de construire une requ�te SQL de type DELETE
 */
class ploopi_query_delete extends ploopi_query_sud
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
     * Tableau de la clause RIGHT JOIN
     *
     * @var array
     */
    private $arrRightJoin;

    /**
     * Constructeur de la classe
     *
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrDelete = array();
        $this->arrInnerJoin = array();
        $this->arrLeftJoin = array();
        $this->arrRightJoin = array();

        return parent::__construct('delete', $objDb);
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
     * Ajoute une clause RIGHT JOIN � la requ�te
     *
     * @param string $strRightJoin Clause RIGHT JOIN
     */
    public function add_rightjoin($strRightJoin, $mixValues = null)
    {
        if (!empty($mixValues) && !is_array($mixValues)) $mixValues = array($mixValues);
        $this->arrRightJoin[] = array('rawsql' => $strRightJoin, 'values' => $mixValues);
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
     * Retourne la clause RIGHT JOIN
     *
     * @return string
     */
    protected function get_rightjoin()
    {
        $arrRightJoin = array();
        foreach($this->arrRightJoin as $arrRightJoinDetail) $arrRightJoin[] = ploopi_sqlformat::replace($arrRightJoinDetail, $this->objDb);

        return empty($arrRightJoin) ? '' : ' LEFT JOIN '.implode(' LEFT JOIN ', $arrRightJoin);
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
     * G�n�ration de la requ�te SQL
     *
     * @return string Cha�ne contenant la requ�te SQL g�n�r�e
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
                $this->get_rightjoin().
                $this->get_where().
                $this->get_orderby().
                $this->get_limit().
                $this->get_raw();
        }

        return $strSql;
    }
}


/**
 * Classe permettant de construire une requ�te SQL de type UPDATE
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
        foreach($this->arrSet as $arrSetDetail) $arrSet[] = ploopi_sqlformat::replace($arrSetDetail, $this->objDb);

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

/**
 * Classe permettant de construire une requ�te SQL de type INSERT
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
     * @param resource $objDb Connexion � la BDD
     */
    public function __construct($objDb = null)
    {
        $this->arrSet = array();

        return parent::__construct($objDb);
    }

    /**
     * D�finit la table
     *
     * @param string $strTable nom de la table
     */
    public function set_table($strTable)
    {
        $this->arrFrom = array($strTable);
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
     * G�n�ration de la requ�te SQL
     *
     * @return string Cha�ne contenant la requ�te SQL g�n�r�e
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

/**
 * Classe de gestion des recordsets retourn�s par ploopi_query
 */
class ploopi_recordset
{
    /**
     * Connexion � la BDD
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
     * @param resource $objDb Connexion � la BDD
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
     * D�place le pointeur interne sur un enregistrement particulier
     *
     * @param integer $intPos position dans le recordset
     * @return boolean true si le d�placement a �t� effectu� sinon false
     */
    public function dataseek($intPos = 0)
    {
        return $this->objDb->dataseek($this->resRs, $intPos);
    }

    /**
     * Retourne dans un tableau le contenu du recordset
     *
     * @param $booFirstColKey $firstcolkey true si la premi�re colonne doit servir d'index pour le tableau (optionnel)
     * @return mixed un tableau index� contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getarray($booFirstColKey = false)
    {
        return $this->objDb->getarray($this->resRs, $booFirstColKey);
    }

    /**
     * Retourne au format JSON le contenu du recordset
     *
     * @param boolean $booUtf8 true si le contenu doit �tre encod� en utf8, false sinon (true par d�faut)
     * @return string une cha�ne au format JSON contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getjson($booUtf8 = true)
    {
        return $this->objDb->getjson($this->resRs, $booUtf8);
    }


    /**
     * Retourne au format CSV le contenu du recordset
     *
     * @param array $arrOptions options du format CSV : booHeader:true si la ligne d'ent�te doit �tre ajout�e (nom des colonnes), strFieldSep:s�parateur de champs, strLineSep:s�parateur de lignes, strTextSep:caract�re d'encapsulation des contenus
     * @return string une cha�ne au format CSV contenant les enregistrements du recordset ou false si le recordset n'est pas valide
     */
    public function getcsv($arrOptions = array()) {

        $arrDefaultOptions = array(
            'booHeader' => true,
            'strFieldSep' => ',',
            'strLineSep' => "\n",
            'strTextSep' => '"',
            'booClean' => true
        );

        $arrOptions = array_merge($arrDefaultOptions, $arrOptions);

        // Fonction d'�chappement & formatage du contenu
        $funcLineEchap = null;

        if ($arrOptions['strTextSep'] != '') {
            $funcLineEchap = create_function('$value', 'return \''.$arrOptions['strTextSep'].'\'.str_replace(\''.$arrOptions['strTextSep'].'\', \''.$arrOptions['strTextSep'].$arrOptions['strTextSep'].'\', $value).\''.$arrOptions['strTextSep'].'\';');
        } elseif ($arrOptions['strFieldSep'] != '') {
            $funcLineEchap = create_function('$value', 'return str_replace(\''.$arrOptions['strFieldSep'].'\', \'\\'.$arrOptions['strFieldSep'].'\', $value);');
        }

        $booHeader = false;
        $strCsv = '';

        while ($row = $this->fetchrow()) {

            if ($arrOptions['booClean']) $row = ploopi_array_map('ploopi_iso8859_clean', $row);

            // Ajout de la ligne d'ent�te
            if ($arrOptions['booHeader'] && !$booHeader) {
                $booHeader = true;
                $strCsv = implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? array_keys($row) : ploopi_array_map($funcLineEchap, array_keys($row))).$arrOptions['strLineSep'];
            }

            $strCsv .= implode($arrOptions['strFieldSep'], is_null($funcLineEchap) ? $row : ploopi_array_map($funcLineEchap, $row)).$arrOptions['strLineSep'];

        }

        return $strCsv;
    }

}
