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

include_once './include/classes/class_data_object.php';
include_once './modules/system/class_role_action.php';

class mb_action extends data_object
{
    /**
    * Class constructor
    *
    * @param int $connection_id
    * @access public
    **/

    function mb_action()
    {
        parent::data_object('ploopi_mb_action','id_module_type','id_action');
    }

    function save()
    {
        global $db;

        if ($this->new && ($this->fields['id_action'] == "" || $this->fields['id_action'] <= 0))
        {
            $answer = $db->query("select max(id_action) as maxi from ploopi_mb_action where id_module_type={$this->fields['id_module_type']}");
            $resfields=$db->fetchrow($answer);
            $this->fields['id_action']=$resfields['maxi']+1;
        }
        return(parent::save());
    }

    function delete($preserve_data = false)
    {
        global $db;

        if ($this->fields['id_action']!=-1 && !$preserve_data)
        {
            $select =   "
                        SELECT  *
                        FROM    ploopi_role_action
                        WHERE   id_action = ".$this->fields['id_action']."
                        AND     id_module_type = ".$this->fields['id_module_type']."
                        ";

            $answer = $db->query($select);
            while ($deletefields = $db->fetchrow($answer))
            {
                $role_action = new role_action();
                $role_action->open($deletefields['id_role'],$this->fields['id_action'],$this->fields['id_module_type']);
                $role_action->delete();
            }
        }
        parent::delete();
    }


}
?>
