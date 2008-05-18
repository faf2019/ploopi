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
 * Gestion des espaces de travail
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_workspace
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace extends data_object
{
    function workspace()
    {
        parent::data_object('ploopi_workspace');
    }

    function save()
    {
        $this->fields['depth'] = sizeof(explode(';',$this->fields['parents']));
        return(parent::save());
    }

    function delete()
    {

        global $db;

        if ($this->fields['id']!=-1 && !$this->fields['system'])
        {

            $fatherid = $this->fields['id_workspace'];

            // attach children to new father
            $select =   "
                    SELECT  ploopi_workspace.id
                        FROM    ploopi_workspace
                        WHERE   ploopi_workspace.id_workspace = ".$this->fields['id'];


            $result = $db->query($select);

            while ($child =  $db->fetchrow($result))
            {
                $update =   "
                            UPDATE  ploopi_workspace
                            SET     ploopi_workspace.id_workspace = $fatherid
                            WHERE   ploopi_workspace.id = $child[id]
                            ";

                $db->query($update);
            }

            // update parents workspace
            system_updateparents();


            $delete = "DELETE FROM ploopi_workspace_user WHERE id_workspace = ".$this->fields['id']."; DELETE FROM ploopi_workspace_group WHERE id_workspace = ".$this->fields['id'];
            $db->query($delete);



            parent::delete();

        }
    }


    function getfullworkspace($idworkspace = '')
    {
        global $db;

        if ($idworkspace == '') $idworkspace = $this->fields['id'];

        $res='';

        $select = "SELECT ploopi_workspace.* FROM ploopi_workspace WHERE id = $idworkspace AND id_workspace <> $idworkspace";
        $answer = $db->query($select);
        if ($fields = $db->fetchrow($answer))
        {
            $parents = $this->getfullworkspace($fields['id_workspace']);
            if ($parents != '') $res = $parents .' / ';
            $res .= $fields['label'];
        }
        return $res;
    }


    // return all children id's of current workspace
    function getworkspacechildrenlite($mode = '')
    {
        global $db;

        $where = ($mode != '') ? " AND $mode = 1 " : '';

        $db->query("SELECT id FROM ploopi_workspace WHERE parents = '{$this->fields['parents']};{$this->fields['id']}' OR parents LIKE '{$this->fields['parents']};{$this->fields['id']};%' {$where}");

        return($db->getarray());
    }


    // return all brothers ids of current group
    function getworkspacebrotherslite($mode = '', $domain = '')
    {
        global $db;

        $where = '';
        if ($mode != '') $where .= " AND $mode = 1 ";

        $select =   "
                    SELECT  ploopi_workspace.*
                    FROM    ploopi_workspace
                    WHERE   id_workspace = {$this->fields['id_workspace']}
                    $where
                    AND     id <> {$this->fields['id']}
                    ";
        $result = $db->query($select);
        $ar = array();
        while ($fields = $db->fetchrow($result))
        {
            if ($domain != '')
            {
                $web_dom_array = split("\r\n", $fields['web_domainlist']);
                $admin_dom_array = split("\r\n", $fields['admin_domainlist']);
                foreach($admin_dom_array as $dom)
                {
                    if ($domain == $dom) $ar[] = $fields['id'];
                }
            }
            else $ar[] = $fields['id'];
        }

        return($ar);
    }

    function getparents($parents = '')
    {
        global $db;
        if ($parents == '') $parents = $this->fields['parents'];

        $parents = str_replace(';',',',$parents);

        $select = "SELECT * FROM ploopi_workspace WHERE id IN ({$parents}) ORDER BY depth DESC";
        $result = $db->query($select);

        $workspaces = array();
        while ($fields = $db->fetchrow($result)) $workspaces[$fields['id']] = $fields;

        return($workspaces);
    }

    function getfather()
    {
        $father = new workspace();
        if ($father->open($this->fields['id_workspace'])) return $father;
        else return(false);
    }

    function getusers()
    {
        global $db;

        $users = array();

        $select =   "
                    SELECT  ploopi_user.*,
                            ploopi_workspace_user.adminlevel
                    FROM    ploopi_user,
                            ploopi_workspace_user
                    WHERE   ploopi_workspace_user.id_workspace = {$this->fields['id']}
                    AND     ploopi_workspace_user.id_user = ploopi_user.id


                    ";
        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;

        return $users;
    }

    function getallusers()
    {
        global $db;

        $users = array();
        
        $arrChildren = array();
        $arrChildren = $this->getworkspacechildrenlite();
        $arrChildren[] = $this->fields['id'];
        
        $select =   "
                    SELECT  u.*
                    
                    FROM    ploopi_user u,
                            ploopi_workspace_user wu
                            
                    WHERE   wu.id_workspace IN (".implode(', ', $arrChildren).")
                    AND     wu.id_user = u.id
                    
                    GROUP BY u.id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;

    
        $select =   "
                    SELECT  u.*
                                                    
                    FROM    ploopi_user u,
                            ploopi_workspace_group wg,
                            ploopi_group_user gu
                            
                    WHERE   wg.id_workspace IN (".implode(', ', $arrChildren).")
                    AND     wg.id_group = gu.id_group
                    AND     gu.id_user = u.id

                    GROUP BY u.id
                    ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result)) $users[$fields['id']] = $fields;
        
        return($users);
    }
            
    function getgroups($getchildren = false)
    {
        global $db;

        $groups = array();

        $sql =  "
                    SELECT  ploopi_group.*,
                            ploopi_workspace_group.adminlevel
                    FROM    ploopi_workspace_group,
                            ploopi_group
                    WHERE   ploopi_workspace_group.id_workspace = {$this->fields['id']}
                    AND     ploopi_workspace_group.id_group = ploopi_group.id
                    ";

        $result = $db->query($sql);

        while ($fields = $db->fetchrow($result))
        {
            $groups[$fields['id']] = $fields;
            if ($getchildren)
            {
                $sql =  "
                        SELECT  *
                        FROM    ploopi_group
                        WHERE   parents LIKE '{$fields['parents']};{$fields['id']};%'
                        OR      parents = '{$fields['parents']};{$fields['id']}'
                        ";

                $res_children = $db->query($sql);
                while ($fields = $db->fetchrow($res_children))
                {
                    if (empty($groups[$fields['id']])) $groups[$fields['id']] = $fields;
                }
            }
        }

        return $groups;
    }


    function createchild()
    {
        $child = new workspace();
        $child->fields = $this->fields;
        unset($child->fields['id']);
        $child->fields['id_workspace'] = $this->fields['id'];
        $child->fields['label'] = 'fils de '.$this->fields['label'];
        $child->fields['parents'] = $this->fields['parents'].';'.$this->fields['id'];
        $child->fields['system'] = 0;
        return($child);
    }

    function createclone()
    {
        $clone = new workspace();
        $clone->fields = $this->fields;
        unset($clone->fields['id']);
        $clone->fields['label'] = 'clone de '.$this->fields['label'];
        $clone->fields['system'] = 0;
        return($clone);
    }

    function getmodules($lite = false, $public = false)
    {
        global $db;

        $modules = array();

        $select =   "
                SELECT  ploopi_module_type.*,
                        ploopi_module.label AS instancename,
                        ploopi_module.id AS instanceid,
                        ploopi_module.id_workspace As instanceworkspace,
                        ploopi_module.active,
                        ploopi_module.shared,
                        ploopi_module.herited,
                        ploopi_module.adminrestricted,
                        ploopi_module.public,
                        ploopi_module.viewmode,
                        ploopi_module.transverseview,
                        ploopi_module.id_module_type,
                        ploopi_module_workspace.position,
                        ploopi_module_workspace.blockposition
                FROM    ploopi_module_type,
                        ploopi_module,
                        ploopi_module_workspace
                WHERE   ploopi_module_workspace.id_workspace = {$this->fields['id']}
                AND     ploopi_module_workspace.id_module = ploopi_module.id
                AND     ploopi_module.id_module_type = ploopi_module_type.id
                ORDER BY ploopi_module_workspace.position
                ";

        $result = $db->query($select);

        while ($module = $db->fetchrow($result))
        {
            if ($lite) $modules[] = $module['instanceid'];
            else $modules[$module['instanceid']] = $module;
        }

        return $modules;
    }


    function getsharedmodules($herited = false)
    {
        global $db;

        $modules = array();

        $parents = str_replace(';',',',$this->fields['parents']);

        if ($parents!='')
        {

            if ($herited) $sql_herited = 'AND m.herited = 1';
            else $sql_herited = '';

            $select =   "
                        SELECT      m.id,
                                    m.label,
                                    m.id_workspace,
                                    w.label as workspacelabel,
                                    mt.label as moduletype,
                                    mt.description

                        FROM        ploopi_module m

                        INNER JOIN  ploopi_workspace w
                        ON          w.id = m.id_workspace

                        INNER JOIN  ploopi_module_type mt
                        ON          mt.id = m.id_module_type

                        WHERE   w.id IN ({$parents})
                        AND     m.shared = 1
                        {$sql_herited}
                        ORDER BY    m.label,
                                    workspacelabel
                        ";

            $result = $db->query($select);

            while($module = $db->fetchrow($result)) $modules[$module['id']] = $module;
        }
        return $modules;
    }
}

/**
 * Classe d'accès à la table ploopi_module_workspace
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class module_workspace extends data_object
{

    function module_workspace()
    {
        parent::data_object('ploopi_module_workspace','id_workspace','id_module');
    }

    function save()
    {
        global $db;

        if ($this->new)
        {
            $select =   "
                    SELECT MAX(ploopi_module_workspace.position) AS position
                    FROM ploopi_module_workspace
                    WHERE ploopi_module_workspace.id_workspace = ".$this->fields['id_workspace'];

            $result = $db->query($select);
            $fields = $db->fetchrow($result);
            $this->fields['position'] = $fields['position'] + 1;
        }

        parent::save();
    }

    function delete()
    {
        global $db;

        $update = "UPDATE ploopi_module_workspace SET position=position-1 WHERE id_workspace = {$this->fields['id_workspace']} AND position > {$this->fields['position']}";
        $db->query($update);

        parent::delete();
    }

    function changeposition($direction)
    {

        global $db;

        $workspaceid = $this->fields['id_workspace'];

        $select =   "
                SELECT  min(position) as minpos,
                        max(position) as maxpos
                FROM    ploopi_module_workspace
                WHERE   id_group = $workspaceid
                ";

        $result = $db->query($select);
        $fields = $db->fetchrow($result);
        $minpos = $fields['minpos'];
        $maxpos = $fields['maxpos'];
        $position = $this->fields['position'];
        $move = 0;

        if ($direction=='down' && $position != $maxpos)
        {
            $move = 1;
        }

        if ($direction=='up' && $position != $minpos)
        {
            $move = -1;
        }

        if ($move!=0)
        {
            $update = "update ploopi_module_workspace set position=0 where id_workspace = $workspaceid and position=".($position+$move);
            $db->query($update);;
            $update = "update ploopi_module_workspace set position=".($position+$move)." where id_workspace = $workspaceid and position=$position";
            $db->query($update);;
            $update = "update ploopi_module_workspace set position=$position where id_workspace = $workspaceid and position=0";
            $db->query($update);;
        }
    }


     function getroles()
     {
        global $db;
        $workspace = new workspace();
        $workspace->open($this->fields['id_workspace']);
        $parents = str_replace(';',',',$workspace->fields['parents']);

        $roles = array();


        // select own roles and shared herited roles
        $select =   "
                    SELECT      ploopi_role.*,
                                ploopi_workspace.label as labelworkspace
                    FROM        ploopi_role,
                                ploopi_workspace
                    WHERE       ploopi_role.id_module = {$this->fields['id_module']}
                    AND         (ploopi_role.id_workspace = {$this->fields['id_workspace']}
                    OR          (ploopi_role.id_workspace IN ({$parents}) AND ploopi_role.shared = 1))
                    AND         ploopi_role.id_workspace = ploopi_workspace.id
                    ORDER BY    ploopi_role.label
                    ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result)) $roles[$role['id']] = $role;

        return $roles;
     }

}

/**
 * Classe d'accès à la table ploopi_workspace_user
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace_user extends data_object
{

    function workspace_user()
    {
        parent::data_object('ploopi_workspace_user','id_workspace','id_user');
        $this->fields['adminlevel'] = _PLOOPI_ID_LEVEL_USER;
    }

    function getroles()
    {
        global $db;

        $roles = array();

        $select =   "
                SELECT      ploopi_role.*,
                        ploopi_module.label as modulelabel
                FROM        ploopi_role,
                        ploopi_workspace_user_role,
                        ploopi_module
                WHERE       ploopi_workspace_user_role.id_user = {$this->fields['id_user']}
                AND     ploopi_workspace_user_role.id_workspace = {$this->fields['id_workspace']}
                AND     ploopi_workspace_user_role.id_role = ploopi_role.id
                AND     ploopi_module.id = ploopi_role.id_module
                ORDER BY    ploopi_role.label
                ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result))
        {
            $roles[$role['id']] = $role;
        }

        return $roles;
    }

    function saveroles($roles)
    {
        global $db;

        $delete =   "
                DELETE FROM     ploopi_workspace_user_role
                WHERE       ploopi_workspace_user_role.id_user = {$this->fields['id_user']}
                AND     ploopi_workspace_user_role.id_workspace = {$this->fields['id_workspace']}
                ";

        $db->query($delete);

        foreach($roles as $key => $idrole)
        {
            $workspace_user_role = new workspace_user_role();
            $workspace_user_role->fields['id_user'] = $this->fields['id_user'];
            $workspace_user_role->fields['id_workspace'] = $this->fields['id_workspace'];
            $workspace_user_role->fields['id_role'] = $idrole;
            $workspace_user_role->save();
        }
    }


    function delete()
    {
        global $db;

        // search for modules
        $select =   "
                    SELECT  m.id, m.label, mt.label as moduletype
                    FROM    ploopi_module_workspace mw,
                            ploopi_module m,
                            ploopi_module_type mt
                    WHERE   mw.id_workspace = {$this->fields['id_workspace']}
                    AND     mw.id_module = m.id
                    AND     m.id_module_type = mt.id
                    ";

        $rs_modules = $db->query($select);

        while ($fields = $db->fetchrow($rs_modules))
        {
            $admin_userid = $this->fields['id_user'];
            $admin_workspaceid = $this->fields['id_workspace'];
            $admin_moduleid = $fields['id'];

            echo "<br><b>&laquo; {$fields['label']} &raquo;</b> ({$fields['moduletype']})<br>";
            if (file_exists("./modules/{$fields['moduletype']}/include/admin_user_delete.php")) include "./modules/{$fields['moduletype']}/include/admin_user_delete.php";
        }

        parent::delete();
    }

}

/**
 * Classe d'accès à la table ploopi_workspace_user_role
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace_user_role extends data_object
{
    function workspace_user_role()
    {
        parent::data_object('ploopi_workspace_user_role','id_user','id_workspace','id_role');
    }
}

/**
 * Classe d'accès à la table ploopi_workspace_group
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace_group extends data_object
{

    /**
    * Class constructor
    *
    * @param int $idconnexion
    * @access public
    **/
    function workspace_group()
    {
        parent::data_object('ploopi_workspace_group','id_workspace','id_group');
        $this->fields['adminlevel'] = _PLOOPI_ID_LEVEL_USER;
    }

    function getroles()
    {
        global $db;

        $roles = array();

        $select =   "
                    SELECT      ploopi_role.*,
                                ploopi_module.label as modulelabel
                    FROM        ploopi_role,
                                ploopi_workspace_group_role,
                                ploopi_module
                    WHERE       ploopi_workspace_group_role.id_group = {$this->fields['id_group']}
                    AND         ploopi_workspace_group_role.id_workspace = {$this->fields['id_workspace']}
                    AND         ploopi_workspace_group_role.id_role = ploopi_role.id
                    AND         ploopi_module.id = ploopi_role.id_module
                    ORDER BY    ploopi_role.label
                    ";

        $result = $db->query($select);

        while ($role = $db->fetchrow($result))
        {
            $roles[$role['id']] = $role;
        }

        return $roles;
    }

    function saveroles($roles)
    {
        global $db;

        $delete =   "
                DELETE FROM     ploopi_workspace_group_role
                WHERE           ploopi_workspace_group_role.id_group = {$this->fields['id_group']}
                AND             ploopi_workspace_group_role.id_workspace = {$this->fields['id_workspace']}
                ";

        $db->query($delete);

        foreach($roles as $key => $idrole)
        {
            $workspace_group_role = new workspace_group_role();
            $workspace_group_role->fields['id_group'] = $this->fields['id_group'];
            $workspace_group_role->fields['id_workspace'] = $this->fields['id_workspace'];
            $workspace_group_role->fields['id_role'] = $idrole;
            $workspace_group_role->save();
        }
    }

    function delete()
    {
        global $db;

        // search for modules
        $select =   "
                    SELECT  m.id, m.label, mt.label as moduletype
                    FROM    ploopi_module_workspace mw,
                            ploopi_module m,
                            ploopi_module_type mt
                    WHERE   mw.id_workspace = {$this->fields['id_workspace']}
                    AND     mw.id_module = m.id
                    AND     m.id_module_type = mt.id
                    ";

        $db->query($select);
        while ($fields = $db->fetchrow())
        {
            $admin_groupid = $this->fields['id_group'];
            $admin_workspaceid = $this->fields['id_workspace'];
            $admin_moduleid = $fields['id'];

            echo "<br><b>« {$fields['label']} »</b> ({$fields['moduletype']})<br>";
            if (file_exists("./modules/{$fields['moduletype']}/include/admin_org_delete.php")) include "./modules/{$fields['moduletype']}/include/admin_org_delete.php";
        }
        parent::delete();
    }
}

/**
 * Classe d'accès à la table ploopi_workspace_group_role
 * 
 * @package ploopi
 * @subpackage workspace
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class workspace_group_role extends data_object
{
    function workspace_group_role()
    {
        parent::data_object('ploopi_workspace_group_role','id_group','id_workspace','id_role');
    }
}
?>
