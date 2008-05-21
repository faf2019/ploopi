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
 * Gestion de l'acc�s aux donn�es.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe g�n�rique d'acc�s aux donn�es.
 * Permet la manipulation d'enregistrements de base de donn�es sous forme d'objets.
 *
 * @package ploopi
 * @subpackage database
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class data_object
{
    /**
     * Constructeur de la classe
     *
     * @param string nom de la table
     * @param string champ cl� n�1
     * @param string champ cl� n�2
     * @param string champ cl� n�X
     * 
     * @return data_object
     * 
     */

    function data_object()
    {
        global $db;
        // arg(0) : tablename

        $numargs = func_num_args();
        $this->classname = get_class($this) ;
        $this->tablename = func_get_arg(0);
        $this->idfields = array();
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
                }
            }
        }

        $this->db = $db;

        $this->new = true;
    }

    /**
     * Permet de connexion � la base de donn�es
     *
     * @param ressource $db objet de connexion � la base de donn�es
     */
    
    function setdb($db)
    {
        $this->db = $db;
    }

    
    /**
     * Permet de mettre � jour les propri�t�s de l'objet (les champs de la table)
     *
     * @param array $values tableau associatif contenant les valeurs tel que "prefixe_nomduchamp" => "valeur"
     * @param string $prefix pr�fixe utilis�
     */

    function setvalues($values, $prefix)
    {
        // par d�faut on r�cup�re les champs du formulaire ($values)
        $longueurprefixe = strlen($prefix);
        foreach ($values AS $key => $value)
        {
            $pref = substr($key,0,$longueurprefixe);
            if ($pref==$prefix)
            {
                $prop = substr($key,$longueurprefixe);
                $this->fields[$prop] = $value;
            }
        }

    }

    /**
     * Ouvre un enregistrement de la table et met � jour l'objet
     *
     * @param mixed valeur du champ 1 de la cl�
     * @param mixed valeur du champ 2 de la cl�
     * @param mixed valeur du champ X de la cl�
     *
     * @return int nombre d'enregistrements
     */
    
    function open() // id0, id1, id2, etc...
    {
        $numargs = func_num_args();
        if ($numargs > 0)
        {

            for ($i = 0; $i < $numargs; $i++) $id[$i] = func_get_arg($i);


            $sql = "SELECT * FROM `{$this->tablename}` WHERE `{$this->idfields[0]}` = '".$this->db->addslashes($id[0])."'";

            for ($i = 1; $i < $numargs; $i++) $sql = $sql." AND `{$this->idfields[$i]}` = '".$this->db->addslashes($id[$i])."'";

            $this->resultid = $this->db->query($sql);
            $this->numrows = $this->db->numrows($this->resultid);
            $this->fields = $this->db->fetchrow($this->resultid);

            for ($i = 0; $i < $numargs; $i++) $this->fields[$this->idfields[$i]] = $id[$i];

            if ($this->numrows>0) $this->new = false;
        }
        
        return $this->numrows;
    }

    /**
     * Ins�re ou met � jour l'enregistrement dans la base de donn�es
     *
     * @return mixed valeur de la cl� primaire
     */
    
    function save()
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

            $this->sql = "INSERT INTO `{$this->tablename}` {$listvalues}"; // construction de la requ�te
            $this->db->query($this->sql);

            // get insert id from insert (if 1 field primary key and autokey)
            if (sizeof($this->idfields) >= 1) $this->fields[$this->idfields[0]] = $this->db->insertid();

            $this->new = false;
        }
        else // update
        {
            $listvalues='';
            $arrValues = array();
            foreach ($this->fields as $key => $value)
            {
                if (!in_array($key,$this->idfields)) // field is not a key
                {
                    $arrValues[] = (is_null($value)) ? "`{$this->tablename}`.`{$key}` = null" : "`{$this->tablename}`.`{$key}` = '".$this->db->addslashes($value)."'";
                }
            }

            $listvalues = (empty($arrValues)) ? '' : implode(', ', $arrValues);

            // build request
            $sql = "UPDATE `{$this->tablename}` SET {$listvalues} WHERE `{$this->tablename}`.`{$this->idfields[0]}` = '".$this->db->addslashes($this->fields[$this->idfields[0]])."'";
            for ($i = 1; $i < sizeof($this->idfields); $i++) $sql = $sql." AND `{$this->tablename}`.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->fields[$this->idfields[$i]])."'";

            $this->db->query($sql);
            $this->sql = $sql;
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
     * Supprime l'enregistrement dans la base de donn�es
     */

    function delete()
    {
        $numargs = func_num_args();
        if ($numargs > 0) for ($i = 0; $i < $numargs; $i++) $this->fields[$this->idfields[$i]] = func_get_arg($i);

        $sql = "DELETE FROM `{$this->tablename}` WHERE `{$this->tablename}`.`{$this->idfields[0]}` = '".$this->db->addslashes($this->fields[$this->idfields[0]])."'";
        for ($i = 1; $i < sizeof($this->idfields); $i++) $sql = $sql." AND `{$this->tablename}`.`{$this->idfields[$i]}` = '".$this->db->addslashes($this->fields[$this->idfields[$i]])."'";

        $this->db->query($sql);

        $this->sql = $sql;
    }

    /**
     * Initialise les propri�t�s de l'objet avec la structure de la table
     */

    function init_description()
    {
        $result = $this->db->query("describe `{$this->tablename}`");
        while ($fields = $this->db->fetchrow($result)) $this->fields[$fields['Field']] = '';
    }

    /**
     * Met � jour les propri�t�s id_user, id_workspace, id_module de l'objet avec le contenu de la session
     */
    
    function setuwm()
    {
        $this->fields['id_user'] = $_SESSION['ploopi']['userid'] ;
        $this->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    }

    /**
     * G�n�re un dump SQL de l'enregistrement
     *
     * @return string dump SQL
     */
    
    function dump()
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
     * G�n�re des variables templates � partir des propri�t�s de l'objet
     *
     * @param Template $tpl template
     * @param string $prefix pr�fixe � ajouter (optionnel)
     */
    
    function totemplate(&$tpl, $prefix = '')
    {
        $array_vars = array();
        foreach($this->fields as $key => $value) $array_vars[strtoupper("{$prefix}{$key}")] = $value;
        $tpl->assign_vars($array_vars);
    }

    /**
     * Retourne le script SQL de cr�ation de la table
     *
     * @return string script SQL
     */
    
    function getsqlstructure()
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

}
?>
