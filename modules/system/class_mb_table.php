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
?>
<?
include_once('./include/classes/class_data_object.php');

class mb_table extends data_object
{
    /**
    * Class constructor
    *
    * @access public
    **/

    function mb_table()
    {
        parent::data_object('ploopi_mb_table','name','id_module_type');
    }

    function delete()
    {
        global $db;

        $db->query("DELETE FROM ploopi_mb_field WHERE tablename = '".$db->addslashes($this->fields['name'])."' AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_relation WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_schema WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        parent::delete();
    }
}
?>
