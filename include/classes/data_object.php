<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Gestion de l'accès aux données.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe générique d'accès aux données.
 * Permet la manipulation d'enregistrements de base de données sous forme d'objets.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class data_object
{
    /**
     * Propriété expérimentale
     *
     * @var array
     */
    protected static $arrDef = array(
        'strTableName' => null,
        'arrId' => null
    );    
    
    /**
     * Nom de la classe
     *
     * @var string
     */
    
    private $classname;
    
    /**
     * Nom de la table
     *
     * @var string
     */
    
    private $tablename;
    
    /**
     * Tableau indexé des champs qui composent la clé primaire
     *
     * @var array
     */

    private $idfields;
    
    /**
     * Tableau associatif des valeurs qui composent la clé primaire
     *
     * @var array
     */

    private $id;
    
    /**
     * Objet de connexion à la base de données
     *
     * @var ploopi_db
     * @see ploopi_db
     */
    
    private $db;
    
    /**
     * Connexion à la base de données
     *
     * @var resource
     * @see data_objet::setdb
     */
    
    private $resultid;
    
    /**
     * Nombre de ligne du dernier résultat
     *
     * @var int
     */
    
    private $numrows;
    
    /**
     * Requête SQL générée par l
     *
     * @var string
     * @see data_objet::getsql
     */
    
    private $sql;
    
    /**
     * Contenu d'un enregistrement de la table dans un tableau associatif : champ => valeur
     *
     * @var array
     */
    
    public $fields;
    
    /**
     * Indique s'il s'agit d'un nouvel enregistrement (true) ou d'un enregistrement existant (false)
     *
     * @var boolean
     */
    
    public $new;
    
    /**
     * Méthode statique expérimentale : contructeur statique de la classe
     * 
     * @param string $strTableName nom de la table
     * @param array $arrId tableau contenant la description des champs composants la clé primaire
     */
    public static function init($strTableName, $arrId = array())
    {
        self::$arrDef['strTableName'] = $strTableName;
        self::$arrDef['arrId'] = $arrId;
    }
    

    /**
     * Méthode statique expérimentale permettant de renvoyer le contenu de la table sous forme d'un tableau d'objets
     *
     * @return array tableau d'objets
     */
    public static function getObjects()
    {
        global $db;
        
        if (is_null(self::$arrDef['strTableName'])) return null;
        
        $arrResult = array();
        
        $db->query("SELECT * FROM `".self::$arrDef['strTableName']."`");
        while ($row = $db->fetchrow())
        {
            $objRecord = new data_object(self::$arrDef['strTableName'], self::$arrDef['arrId'][0]);
            $objRecord->new = false;
            
            // Construction de la clé du tableau (à partir de la clé primaire du tuple)
            $arrKeyValue = array();
            foreach(self::$arrDef['arrId'] as $strKeyField) $arrKeyValue[] = $row[$strKeyField];
            
            foreach($row as $strKey => $strValue) $objRecord->fields[$strKey] = $strValue;

            $arrResult[implode(',', $arrKeyValue)] = $objRecord;
        }
        
        return $arrResult;
    }        
    
    
    /**
     * Constructeur de la classe
     *
     * @param string nom de la table
     * @param string champ clé n°1
     * @param string champ clé n°2
     * @param string champ clé n°X
     *
     * @return data_object
     *
     */

    public function data_object()
    {
        global $db;
        // arg(0) : tablename

        $numargs = func_num_args();
        $this->classname = get_class($this) ;
        $this->tablename = func_get_arg(0);
        $this->idfields = array();
        $this->id = array();
        $this->fields = array();

        if ($numargs == 1) // special case
        {
            $this->idfields[0] = 'id';
            $this->fields['id'] = null;
        }
        else
        {
            for ($i = 1; $i < $numargs; $i++)
            {
                if (!is_null(func_get_arg($i)))
                {
                    $this->idfields[$i-1] = func_get_arg($i);
                    $this->fields[$this->idfields[$i-1]] = null;
                    $this->id[$this->idfields[$i-1]] = null;
                }
            }
        }

        $this->db = $db;

        $this->new = true;
    }

    /**
     * Permet de connexion à la base de données
     *
     * @param ressource $db objet de connexion à la base de données
     */
    
    public function setdb($db)
    {
        $this->db = $db;
    }

    /**
     * Permet de mettre à jour les propriétés de l'objet (les champs de la table)
     *
     * @param array $values tableau associatif contenant les valeurs tel que "prefixe_nomduchamp" => "valeur"
     * @param string $prefix préfixe utilisé
     */

    public function setvalues($values, $prefix = '')
    {
        // par défaut on récupère les champs du formulaire ($values)
        $lprefix = strlen($prefix);
        foreach ($values as $key => $value)
        {
            if ($lprefix == 0) $this->fields[$key] = $value;
            else
            {
                $pref = substr($key, 0, $lprefix);
                if ($pref == $prefix)
                {
                    $prop = substr($key, $lprefix);
                    $this->fields[$prop] = $value;
                }
            }   
        }

    }

    /**
     * Ouvre un enregistrement de la table et met à jour l'objet
     *
     * @param mixed valeur du champ 1 de la clé
     * @param mixed valeur du champ 2 de la clé
     * @param mixed valeur du champ X de la clé
     *
     * @return int nombre d'enregistrements
     */
    
    public function open() // id0, id1, id2, etc...
    {
        $numargs = func_num_args();
        
        if ($numargs == sizeof($this->idfields))
        {
            
            for ($i = 0; $i < $numargs; $i++) $id[$i] = func_get_arg($i);
            
            $this->sql = "SELECT * FROM `{$this->tablename}` WHERE `{$this->idfields[0]}` = '".$this->db->addslashes($id[0])."'";

            for ($i = 1; $i < $numargs; $i++) $this->sql .= " AND `{$this->idfields[$i]}` = '".$this->db->addslashes($id[$i])."'";

            $this->resultid = $this->db->query($this->sql);
            $this->numrows = $this->db->numrows($this->resultid);
            $this->fields = $this->db->fetchrow($this->resultid);

            for ($i = 0; $i < $numargs; $i++) $this->id[$this->idfields[$i]] = $this->fields[$this->idfields[$i]] = $id[$i];

            if ($this->numrows > 0) $this->new = false;
            
            return $this->numrows > 0;
        }
        else return false;

    }

    /**
     * Insère ou met à jour l'enregistrement dans la base de données
     *
     * @return mixed valeur de la clé primaire
     */
    
    public function save()
    {

        if ($this->new) // insert
        {
            $listvalues='';

            // build insert
            $arrValues = array();
            foreach ($this->fields as $key => $value)
            {
                $arrValues[] = (is_null($value)) ? "`{$this->tablename}`.`{$key}` = null" : "`{$this->tablename}`.`{$key}` = '".$this->db->addslashes($value)."'";
            }

            $listvalues = (empty($arrValues)) ? '' : 'SET '.implode(', ', $arrValues);

            $this->sql = "INSERT INTO `{$this->tablename}` {$listvalues}"; // construction de la requète
            $this->db->query($this->sql);

            // get insert id from insert (if 1 field primary key and autokey)
            if (sizeof($this->idfields) >= 1 && $this->db->insertid() !== 0) 
            {
                $this->id[$this->idfields[0]] = $this->fields[$this->idfields[0]] = $this->db->insertid();
            }

            $this->new = false;
        }
        else // update
        {
            $listvalues='';
            $arrValues = array();
            foreach ($this->fields as $key => $value)
            {
                $arrValues[] = (is_null($value)) ? "`{$this->tablename}`.`{$key}` = null" : "`{$this->tablename}`.`{$key}` = '".$this->db->addslashes($value)."'";
            }

            $listvalues = (empty($arrValues)) ? '' : implode(', ', $arrValues);

            // build request
            $this->sql = "UPDATE `{$this->tablename}` SET {$listvalues} WHERE `{$this->tablename}`.`{$this->idfields[0]}` = '".$this->db->addslashes($this->id[$this->idfields[0]])."'";
            for ($i = 1; $i < sizeof($this->idfields); $i++) $this->sql .= " AND `{$this->tablename}`.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->id[$this->idfields[$i]])."'";

            $this->db->query($this->sql);
        }

        // return key (array if multiple key)
        if (sizeof($this->idfields) == 1) return ($this->fields[$this->idfields[0]]);
        else
        {
            $res = array();
            foreach($this->idfields as $idfield) $res[] = $this->fields[$idfield];
            return ($res);
        }
    }

    /**
     * Supprime l'enregistrement dans la base de données
     */

    public function delete()
    {
        $numargs = func_num_args();
        if ($numargs > 0) for ($i = 0; $i < $numargs; $i++) $this->fields[$this->idfields[$i]] = func_get_arg($i);

        $this->sql = "DELETE FROM `{$this->tablename}` WHERE `{$this->tablename}`.`{$this->idfields[0]}` = '".$this->db->addslashes($this->fields[$this->idfields[0]])."'";
        for ($i = 1; $i < sizeof($this->idfields); $i++) $this->sql .= " AND `{$this->tablename}`.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->fields[$this->idfields[$i]])."'";

        $this->db->query($this->sql);
    }

    /**
     * Initialise les propriétés de l'objet avec la structure de la table
     */

    public function init_description()
    {
        $this->sql = "DESCRIBE `{$this->tablename}`";
        $result = $this->db->query($this->sql);
        while ($fields = $this->db->fetchrow($result)) $this->fields[$fields['Field']] = '';
    }
    
    /**
     * Met à jour les propriétés id_user, id_workspace, id_module de l'objet avec le contenu de la session
     */
    
    public function setuwm()
    {
        $this->fields['id_user'] = $_SESSION['ploopi']['userid'] ;
        $this->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    }

    /**
     * Génère un dump SQL de l'enregistrement
     *
     * @return string dump SQL
     */
    
    public function dump()
    {
        $listvalues='';

        // build insert
        foreach ($this->fields as $key => $value)
        {
            if ($key != '' && !is_null($value))
            {
                if ($listvalues != '') $listvalues .= ', ';
                $listvalues .= "`{$this->tablename}`.`{$key}` = '".$this->db->addslashes($value)."'";
            }
        }

        return ("INSERT INTO `{$this->tablename}` SET {$listvalues}");
    }

    /**
     * Génère des variables templates à partir des propriétés de l'objet
     *
     * @param Template $tpl template
     * @param string $prefix préfixe à ajouter (optionnel)
     */
    
    public function totemplate(&$tpl, $prefix = '')
    {
        $array_vars = array();
        foreach($this->fields as $key => $value) $array_vars[strtoupper("{$prefix}{$key}")] = $value;
        $tpl->assign_vars($array_vars);
    }

    /**
     * Retourne le script SQL de création de la table
     *
     * @return string script SQL
     */
    
    public function getsqlstructure()
    {
        $sql = "CREATE TABLE `{$this->tablename}` (";

        $fields = '';

        foreach ($this->fields as $key => $value)
        {
            if ($key != '')
            {
                if ($fields != '') $fields .= ",\n";
                $fields .= "`{$key}` varchar(255) NOT NULL default ''";
            }
        }

        $sql .= $fields.") TYPE=MyISAM;";

        return($sql);
    }
    
    /**
     * Retourne la dernière requête SQL exécutée
     *
     * @return string
     */
    
    public function getsql() { return $this->sql; }

    /**
     * Retourne la table associée à l'instance
     *
     * @return string nom de la table
     */

    public function gettablename() { return $this->tablename; }
    
    /**
     * Retourne true si l'enregistrement n'existe pas encore dans la base de données
     *
     * @return boolean
     */

    public function isnew() { return $this->new; }

}
?>
