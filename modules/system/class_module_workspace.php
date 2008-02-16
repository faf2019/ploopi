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

class module_workspace extends data_object
{
    /**
    * Class constructor
    *
    * @param int $connection_id
    * @access public
    **/

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
            $fields = $db->fetchrow($result,MYSQL_ASSOC);
            $this->fields['position'] = $fields['position'] + 1;
        }

        parent::save();
    }

    function delete()
    {
        global $db;

        $update = "UPDATE ploopi_module_workspace SET position=position-1 WHERE id_workspace = {$this->fields['id_workspace']} AND position > {$this->fields['position']}";
        $db->query($update);;

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
?>
