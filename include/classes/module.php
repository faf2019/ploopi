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
 * Gestion des modules.
 *  
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table ploopi_module
 *  
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class module extends data_object
{
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
        include_once './include/classes/workspace.php';
        
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
                if (file_exists("./modules/{$fields['label']}/include/delete.php")) include "./modules/{$fields['label']}/include/delete.php";
                elseif (file_exists("./modules/{$fields['label']}/include/admin_instance_delete.php")) include "./modules/{$fields['label']}/include/admin_instance_delete.php";
            }

            // delete all module_workspace
            $workspaces = $this->getallworkspaces();
            
            foreach($workspaces as $idw => $workspace)
            {
                $module_workspace  = new module_workspace();
                $module_workspace->open($idw,$workspace['id_module']);
                $module_workspace->delete();
            }

            // delete params (default, workspace, user)
            $delete = "DELETE FROM ploopi_param_default WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_workspace WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);
            
            $delete = "DELETE FROM ploopi_param_user WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);

            // delete roles
            $delete = "DELETE FROM ploopi_role WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);
            
            // delete share
            $delete = "DELETE FROM ploopi_share WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);
            
            // delete subscription
            $delete = "DELETE FROM ploopi_subscription WHERE id_module = '{$this->fields['id']}'";
            $db->query($delete);
                    
            // delete workflow
            $delete = "DELETE FROM ploopi_workflow WHERE id_module = '{$this->fields['id']}'";
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

        while ($role = $db->fetchrow($result))
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

        while ($role = $db->fetchrow($result))
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

/**
 * Classe d'acc�s � la table ploopi_module_type
 *  
 * @package ploopi
 * @subpackage module
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class module_type extends data_object
{
    function module_type()
    {
        parent::data_object('ploopi_module_type');
    }

    function delete()
    {
        include_once './include/classes/mb.php';
        include_once './include/classes/param.php';
                
        global $db;
        // delete params

        if ($this->fields['id']!=-1)
        {
            $select = "SELECT * FROM ploopi_
             WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $param_type = new param_type();
                $param_type->open($this->fields['id'], $deletefields['name']);
                $param_type->delete();
            }

            // delete modules

            $select = "SELECT * FROM ploopi_module WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $module = new module();
                $module->open($deletefields['id']);
                $module->delete();
            }

            // delete actions

            $select = "SELECT * FROM ploopi_mb_action WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $mb_action = new mb_action();
                $mb_action->open($this->fields['id'],$deletefields['id_action']);
                $mb_action->delete();
            }

            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$this->fields['id']}");
        }

        parent::delete();
    }


    function delete_params()
    {
        include_once './include/classes/mb.php';
        include_once './include/classes/param.php';
        // used for updating module

        global $db;

        if ($this->fields['id']!=-1)
        {
            // delete params
            $select = "SELECT * FROM ploopi_param_type WHERE id_module_type = {$this->fields['id']}";
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $param_type = new param_type();
                $param_type->open($this->fields['id'], $deletefields['name']);
                $param_type->delete(true);
            }

            // delete actions
            $select = "SELECT * FROM ploopi_mb_action WHERE id_module_type = ".$this->fields['id'];
            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $mb_action = new mb_action();
                $mb_action->open($this->fields['id'],$deletefields['id_action']);
                $mb_action->delete(true);
            }

            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$this->fields['id']}");
            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$this->fields['id']}");
        }
    }


    function createinstance($workspaceid)
    {
        $position = 0;

        $module = new module();

        $module->fields['label'] = 'Nouveau_module_' . $this->fields['label'];
        $module->fields['id_module_type'] = $this->fields['id'];
        $module->fields['id_workspace'] = $workspaceid;
        $module->fields['active'] = '0';
        $module->fields['public'] = '0';
        $module->fields['shared'] = '0';

        return($module);
    }

    function getactions($role_enabled = 1)
    {
        global $db;
        
        $actions = array();
        
        
        $select =   "
                SELECT      *
                FROM        ploopi_mb_action
                WHERE       id_module_type = {$this->fields['id']}
                AND         role_enabled = {$role_enabled}
                ORDER BY    id_action
                ";
        
        $result = $db->query($select);
        
        while ($action = $db->fetchrow($result))
        {
            $actions[$action['id_action']] = $action;
        }
        
        return $actions;
    }
}
?>