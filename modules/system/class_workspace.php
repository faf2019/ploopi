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

include_once './modules/system/class_workspace_group.php';
include_once './modules/system/include/functions.php';

class workspace extends data_object
{

    /**
    * Class constructor
    *
    * @param int $connection_id
    * @access public
    **/

    function workspace()
    {
        parent::data_object('ploopi_workspace');
    }

    /**
    *
    * @access public
    *
    **/

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

    /**
    *
    * @param int $idworkspace
    * @access private
    *
    **/

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
?>
