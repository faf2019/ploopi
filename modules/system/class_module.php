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
include_once './include/classes/class_data_object.php';
include_once './modules/system/class_module_workspace.php';
include_once './modules/system/class_homepage_column.php';

class module extends data_object
{
    /**
    * Class constructor
    *
    * @param int $connection_id
    * @access public
    **/

    function module()
    {
        parent::data_object('ploopi_module','id');
    }

    function save()
    {
        global $db;

        $res = -1;

        if ($this->new) // insert
        {
            $res = parent::save();

            // insert default parameters
            $insert = "INSERT INTO ploopi_param_default SELECT ".$this->fields['id'].", name, default_value, id_module_type FROM ploopi_param_type WHERE id_module_type = ".$this->fields['id_module_type'];
            $db->query($insert);

            // todo when new module
            $select = "SELECT * FROM ploopi_module_type WHERE ploopi_module_type.id = ".$this->fields['id_module_type'];
            $answer = $db->query($select);
            $fields = $db->fetchrow($answer);

            $admin_moduleid = $this->fields['id'];
            // script to execute to create specific module data
            if (file_exists("./modules/$fields[label]/include/create.php")) include "./modules/$fields[label]/include/create.php";
            elseif (file_exists("./modules/$fields[label]/include/admin_instance_create.php")) include "./modules/$fields[label]/include/admin_instance_create.php";
        }
        else $res = parent::save();

        return($res);
    }

    function delete()
    {
        global $db;

        if ($this->fields['id']!=-1)
        {
            // delete specific data of the module (call to delete function of the module)
            $select = "SELECT ploopi_module_type.label FROM ploopi_module_type, ploopi_module WHERE ploopi_module_type.id = ploopi_module.id_module_type AND ploopi_module.id = ".$this->fields['id'];
            $answer = $db->query($select);
            if ($fields = $db->fetchrow($answer))
            {
                $admin_moduleid = $this->fields['id'];
                // script to execute to delete specific module data
                if (file_exists("./modules/$fields[label]/include/delete.php")) include "./modules/$fields[label]/include/delete.php";
                elseif (file_exists("./modules/$fields[label]/include/admin_instance_delete.php")) include "./modules/$fields[label]/include/admin_instance_delete.php";
            }

            // delete all module_workspace
            $workspaces = $this->getallworkspaces();
            
            foreach($workspaces as $idw => $workspace)
            {
                $module_workspace  = new module_workspace();
                $module_workspace->open($idw,$workspace['id_module']);
                $module_workspace->delete();
            }

            // delete params (default & user)

            $delete = "DELETE FROM ploopi_param_default WHERE id_module = " . $this->fields['id'];
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_user WHERE id_module = " . $this->fields['id'];
            $db->query($delete);
        }

        parent::delete();

    }

    function getallworkspaces()
    {
        global $db;

        $workspaces = array();

        $select =   "
                SELECT  *
                FROM    ploopi_module_workspace
                WHERE   id_module = {$this->fields['id']}
                ";

        $result = $db->query($select);
        while ($fields = $db->fetchrow($result))
        {
            $workspaces[$fields['id_group']] = $fields;
        }

        return($workspaces);
    }

    function getroles()
    {
        global $db;

        $roles = array();

        $select =   "
                SELECT      ploopi_role.*
                FROM        ploopi_role
                WHERE       ploopi_role.id_module = {$this->fields['id']}
                ORDER BY    ploopi_role.label
                ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result,MYSQL_ASSOC))
        {
            $roles[$role['id']] = $role;
        }

        return $roles;
    }

    function getrolesshared()
    {
        global $db;

        $roles = array();

        $select =   "
                SELECT      ploopi_role.*
                FROM        ploopi_role
                WHERE       ploopi_role.id_module = {$this->fields['id']}
                AND     ploopi_role.shared=1
                ORDER BY    ploopi_role.label
                ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result,MYSQL_ASSOC))
        {
            $roles[$role['id']] = $role;
        }

        return $roles;
    }


    function unlink($idgroup)
    {
        global $db;

        $delete =   "
                DELETE FROM     ploopi_module_workspace
                WHERE       ploopi_module_workspace.id_group = $idgroup
                AND         ploopi_module_workspace.id_module = {$this->fields['id']}
                ";

        $db->query($delete);
    }


}
?>
