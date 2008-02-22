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
include_once "./modules/system/include/functions.php";
//include_once './modules/system/include/global.php';

class group extends data_object
{

    /**
    * Class constructor
    *
    * @param int $connection_id
    * @access public
    **/

    function group()
    {
        parent::data_object('ploopi_group');
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

            $fatherid = $this->fields['id_group'];

            // attach children to new father
            $select =   "
                    SELECT  ploopi_group.id
                        FROM    ploopi_group
                        WHERE   ploopi_group.id_group = ".$this->fields['id'];


            $result = $db->query($select);

            while ($child =  $db->fetchrow($result,MYSQL_ASSOC))
            {
                $update =   "
                            UPDATE  ploopi_group
                            SET     ploopi_group.id_group = $fatherid
                            WHERE   ploopi_group.id = $child[id]
                            ";

                $db->query($update);
            }

            // update parents group
            system_updateparents();


            $delete = "DELETE FROM ploopi_group_user WHERE id_group = ".$this->fields['id'];
            $db->query($delete);

            // penser à supprimer les users du groupe

            parent::delete();

        }
    }

    /**
    *
    * @param int $idgroup
    * @access private
    *
    **/

    function getfullgroup($idgroup = '')
    {
        global $db;

        if ($idgroup == '') $idgroup = $this->fields['id'];

        $res='';

        $select = "SELECT ploopi_group.* FROM ploopi_group WHERE id = $idgroup AND id_group <> $idgroup";
        $answer = $db->query($select);
        if ($fields = $db->fetchrow($answer))
        {
            $parents = $this->getfullgroup($fields['id_group']);
            if ($parents != '') $res = $parents .' / ';
            $res .= $fields['label'];
        }
        return $res;
    }

    // return all children id's of current group
    function getgroupchildrenlite()
    {
        global $db;

        $db->query("SELECT id FROM ploopi_group WHERE system = 0 AND parents = '{$this->fields['parents']};{$this->fields['id']}' OR parents LIKE '{$this->fields['parents']};{$this->fields['id']};%'");

        return($db->getarray());
    }


    // return all brothers ids of current group
    function getgroupbrotherslite($mode = '', $domain = '')
    {
        global $db;

        $where = '';
        if ($mode != '') $where .= " AND $mode = 1 ";

        $select =   "
                    SELECT  ploopi_group.*
                    FROM    ploopi_group
                    WHERE   id_group = {$this->fields['id_group']}
                    $where
                    AND     id <> {$this->fields['id']}
                    ";
        $result = $db->query($select);
        $ar = array();
        while ($fields = $db->fetchrow($result))
        {
            if ($domain != '')
            {
                $dom_array = split("\r\n", $fields['domainlist']);
                foreach($dom_array as $dom)
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
        if (empty($parents)) $parents = $this->fields['parents'];

        $parents = str_replace(';',',',$parents);

        $select = "SELECT * FROM ploopi_group WHERE id IN ({$parents})";
        $result = $db->query($select);

        $groups = array();
        while ($fields = $db->fetchrow($result)) $groups[$fields['id']] = $fields;

        return($groups);
    }

    function getfather()
    {
        $father = new group();
        if ($father->open($this->fields['id_group'])) return $father;
        else return(false);
    }

    function getusers()
    {
        global $db;

        $users = array();

        // Requête1
        $select =   "
                    SELECT  ploopi_user.*

                    FROM    ploopi_user,
                        ploopi_group_user

                    WHERE   ploopi_group_user.id_group = {$this->fields['id']}
                    AND     ploopi_group_user.id_user = ploopi_user.id
                    ";
        $result = $db->query($select);

        while ($fields = $db->fetchrow($result,MYSQL_ASSOC))
        {
            $users[$fields['id']] = $fields;
        }

        return $users;
    }


    function createchild()
    {
        $child = new group();
        $child->fields = $this->fields;
        unset($child->fields['id']);
        $child->fields['id_group'] = $this->fields['id'];
        $child->fields['label'] = 'fils de '.$this->fields['label'];
        $child->fields['parents'] = $this->fields['parents'].';'.$this->fields['id'];
        $child->fields['system'] = 0;
        return($child);
    }

    function createclone()
    {
        $clone = new group();
        $clone->fields = $this->fields;
        unset($clone->fields['id']);
        $clone->fields['label'] = 'clone de '.$this->fields['label'];
        $clone->fields['system'] = 0;
        return($clone);
    }






    function attachtogroup($workspaceid)
    {
        global $db;

        $workspace_group = new workspace_group();
        $workspace_group->fields['id_group'] = $this->fields['id'];
        $workspace_group->fields['id_workspace'] = $workspaceid;
        $workspace_group->save();
    }

    function getactions(&$actions) // only for org groups
    {
        global $db;

        $select =   "
                SELECT      ploopi_workspace_group_role.id_workspace,
                            ploopi_role_action.id_action,
                            ploopi_role.id_module
                FROM        ploopi_role_action,
                            ploopi_role,
                            ploopi_workspace_group_role
                WHERE       ploopi_workspace_group_role.id_role = ploopi_role.id
                AND         ploopi_role.id = ploopi_role_action.id_role
                AND         ploopi_workspace_group_role.id_group = {$this->fields['id']}
                ";

        $result = $db->query($select);

        while ($fields = $db->fetchrow($result,MYSQL_ASSOC))
        {
            $actions[$fields['id_workspace']][$fields['id_module']][$fields['id_action']] = true;
        }
    }

}
?>
