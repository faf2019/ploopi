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
* Generic data object class. Allow object-oriented database records manipulation.
*
* @version 2.09
* @since 0.1
*
* @access public
* @abstract
*
* @package includes
* @subpackage data object
*
* @author Netlor Concept
* @copyright © 2003 Netlor Concept
* @license http://www.netlorconcept.com
*/

class data_object
{
    /**
    * constructor
    *
    * @param int one or more database record ID
    *
    * @access public
    *
    * @uses init_description()
    **/

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

    function setdb($db)
    {
        $this->db = $db;
    }

    /**
    * set value for each field in recordset
    *
    * @param array $values array of field values as prefixed_fieldname => $value
    * @param string $prefix prefix of field names
    *
    * @access private
    **/

    function setvalues($values, $prefix)
    {
        // par défaut on récupère les champs du formulaire ($values)
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
    * Allow to open the data contains in the mysql data
    *
    * @param int none, one or more field to test.
    * @return int number of records corresponding to the query
    *
    * @global object $db low level database access object
    *
    * @access private
    */

    function open() // id0, id1, id2, etc...
    {

        //global $db;

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
        else
        {
            $sql = "SELECT * FROM `{$this->tablename}`";
            $this->resultid = $this->db->query($sql);
            $this->numrows = $this->db->numrows($this->resultid);
            $count = 0;
            while ($row = $this->db->fetchrow($this->resultid))
            {
                $this->fields[$count++] = $row;
            }
        }
        return $this->numrows;
    }

    /**
    * Allow to save the difference with mysql data
    *
    * @return void
    *
    * @global object $db low level database access object
    *
    * @access private
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

            $this->sql = "INSERT INTO `{$this->tablename}` {$listvalues}"; // construction de la requète
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
    * Allow to erase data in database
    *
    * @return void
    *
    * @access private
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
    * get table description from the MySQL server
    *
    * @return void
    *
    * @global object $db low level database access object
    *
    * @access private
    */
    function init_description()
    {
        $result = $this->db->query("describe `{$this->tablename}`");
        while ($fields = $this->db->fetchrow($result)) $this->fields[$fields['Field']] = '';
    }


    /**
    * set user/group/module ids
    *
    * @return void
    *
    * @access public
    */
    function setuwm()
    {
        $this->fields['id_user'] = $_SESSION['ploopi']['userid'] ;
        $this->fields['id_workspace'] = $_SESSION['ploopi']['workspaceid'];
        $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
    }

    function setugm()
    {
        $this->setuwm();
    }

    /**
    * sql dump of object content
    *
    * @return string
    *
    * @access private
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
    * template export of object content
    *
    * @return string
    *
    * @access private
    */

    function totemplate(&$tpl, $prefix)
    {
        $array_vars = array();
        foreach($this->fields as $key => $value) $array_vars[strtoupper("{$prefix}{$key}")] = $value;
        $tpl->assign_vars($array_vars);
    }

    /**
    * get object sql structure
    *
    * @return string
    *
    * @access private
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
