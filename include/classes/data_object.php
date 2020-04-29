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
 * Gestion générique de l'accès aux données (Mapping objet-relationnel / ORM).
 * Permet la manipulation d'enregistrements de base de données sous forme d'objets.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class data_object
{
    /**
     * Nom de la classe
     *
     * @var string
     */

    protected $classname;

    /**
     * Nom de la table
     *
     * @var string
     */

    protected $tablename;

    /**
     * Nom de la table (intégration SQL)
     *
     * @var string
     */

    protected $tablename_quoted;

    /**
     * Tableau indexé des champs qui composent la clé primaire
     *
     * @var array
     */

    protected $idfields;

    /**
     * Tableau associatif des valeurs qui composent la clé primaire
     *
     * @var array
     */

    protected $id;

    /**
     * Objet de connexion à la base de données
     *
     * @var ploopi\db
     */

    private $db;

    /**
     * Recordset
     *
     * @var resource
     */

    private $resultid;

    /**
     * Nombre de ligne du dernier résultat
     *
     * @var int
     */

    private $numrows;

    /**
     * Requête SQL générée
     *
     * @var string
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

    public function __construct()
    {
        $db = db::get();
        // arg(0) : tablename

        $numargs = func_num_args();
        $this->classname = get_class($this) ;
        $this->tablename = func_get_arg(0);
        $this->tablename_quoted = '`'.str_replace('.', '`.`', $this->tablename).'`';
        $this->idfields = array();
        $this->id = array();
        $this->fields = array();

        if ($numargs == 1) // cas particulier n°1, pas de clé définie, on prend id par défaut
        {
            $this->idfields[0] = 'id';
            $this->fields['id'] = null;
        }
        else
        {
            if ($numargs == 2 && is_array($keys = func_get_arg(1))) // cas particulier n°2, les clés sont définies dans un tableau (nouvelle méthode)
            {
                if (sizeof($keys) == 0) // cas particulier n°2b, pas de clé définie, on prend id par défaut
                {
                    $this->idfields[0] = 'id';
                    $this->fields['id'] = null;
                }
                else
                {
                    $i = 0;
                    foreach(func_get_arg(1) as $key)
                    {
                        $this->idfields[$i] = $key;
                        $this->fields[$this->idfields[$i]] = null;
                        $this->id[$this->idfields[$i]] = null;
                        $i++;
                    }
                }
            }
            else // cas général, les clés sont définies dans une liste d'arguments
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
        }

        $this->db = &$db;

        $this->new = true;
    }


    /**
     * Permet de modifier la connexion à la base de données
     *
     * @param ressource $db objet de connexion à la base de données
     */

    public function setdb($db)
    {
        unset($this->db);
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

    public function open(...$args) // id0, id1, id2, etc...
    {
        if(sizeof($args) == 1 && is_array($args[0])) $args = $args[0];

        $numargs = sizeof($args);

        if ($numargs == sizeof($this->idfields))
        {
            for ($i = 0; $i < $numargs; $i++) $id[$i] = $args[$i];

            $this->sql = "SELECT * FROM {$this->tablename_quoted} WHERE `{$this->idfields[0]}` = '".$this->db->addslashes($id[0])."'";

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
     * Méthode d'ouverture spéciale pour "convertir" une ligne de recordset en objet
     *
     * @param array $row ligne de recordset
     */

    public function open_row($row)
    {
        $this->fields = $row;

        foreach($this->idfields as $field) $this->id[$field] = $row[$field];

        $this->new = false;
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
                $arrValues[] = (is_null($value)) ? "{$this->tablename_quoted}.`{$key}` = null" : "{$this->tablename_quoted}.`{$key}` = '".$this->db->addslashes($value)."'";
            }

            $listvalues = (empty($arrValues)) ? '' : 'SET '.implode(', ', $arrValues);

            $this->sql = "INSERT INTO {$this->tablename_quoted} {$listvalues}"; // construction de la requète
            $this->db->query($this->sql);

            // get "static" key
            foreach($this->idfields as $fieldname) if (isset($this->fields[$fieldname])) $this->id[$fieldname] = $this->fields[$fieldname];

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
                $arrValues[] = (is_null($value)) ? "{$this->tablename_quoted}.`{$key}` = null" : "{$this->tablename_quoted}.`{$key}` = '".$this->db->addslashes($value)."'";
            }

            $listvalues = (empty($arrValues)) ? '' : implode(', ', $arrValues);

            // build request
            $this->sql = "UPDATE {$this->tablename_quoted} SET {$listvalues} WHERE {$this->tablename_quoted}.`{$this->idfields[0]}` = '".$this->db->addslashes($this->id[$this->idfields[0]])."'";
            for ($i = 1; $i < sizeof($this->idfields); $i++) $this->sql .= " AND {$this->tablename_quoted}.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->id[$this->idfields[$i]])."'";

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

        $this->sql = "DELETE FROM {$this->tablename_quoted} WHERE {$this->tablename_quoted}.`{$this->idfields[0]}` = '".$this->db->addslashes($this->fields[$this->idfields[0]])."'";
        for ($i = 1; $i < sizeof($this->idfields); $i++) $this->sql .= " AND {$this->tablename_quoted}.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->fields[$this->idfields[$i]])."'";

        $this->db->query($this->sql);
    }

    /**
     * Initialise les propriétés de l'objet avec la structure et les valeurs par défaut de la table
     */

    public function init_description()
    {
        $this->sql = "DESCRIBE {$this->tablename_quoted}";
        $rs = $this->db->query($this->sql);
        while ($fields = $this->db->fetchrow($rs)) {
            $this->fields[$fields['Field']] = $fields['Default'];
        }
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
                $listvalues .= "{$this->tablename_quoted}.`{$key}` = '".$this->db->addslashes($value)."'";
            }
        }

        return ("INSERT INTO {$this->tablename_quoted} SET {$listvalues}");
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
        $sql = "CREATE TABLE {$this->tablename_quoted} (";

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

    /**
     * Retourne un hash de la clé de l'enregistrement
     *
     * @return string
     */
    public function gethash()
    {
        $arrHash = array();
        foreach($this->idfields as $fieldname) if (isset($this->id[$fieldname])) $arrHash[] = $this->id[$fieldname];
        return(implode(',', $arrHash));
    }

    /**
     * Retourne la collection
     *
     * @return data_object_collection
     */
    public static function get_collection() {
        return new data_object_collection(get_called_class());
    }

    /**
     * Retourne la liste des objets

     * @return array
     */
    public static function get_objects() {
        $objCol = new data_object_collection(get_called_class());
        return $objCol->get_objects();
    }

}
