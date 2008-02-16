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

include_once('./include/classes/class_data_object.php');

class ticket extends data_object
{

    /**
    * Class constructor
    *
    * @param int $idconnexion
    * @access public
    **/
    function ticket()
    {
        parent::data_object('ploopi_ticket','id');
    }


    function save()
    {
        global $db;

        if (!$this->new && $this->fields['needed_validation'] > _PLOOPI_TICKETS_NONE && $this->fields['status'] < _PLOOPI_TICKETS_DONE)
        {
            // update ticket status

            $sql =  "
                    SELECT  td.id_user,
                            MAX( IF( ISNULL(ts.status), 0, ts.status)) as max_status

                    FROM    ploopi_ticket_dest td

                    LEFT JOIN   ploopi_ticket_status ts
                    ON      ts.id_ticket = td.id_ticket
                    AND     ts.id_user = td.id_user

                    WHERE   td.id_ticket = {$this->fields['id']}

                    GROUP BY td.id_user
                    ";

            $rs_status = $db->query($sql);
            $global_status = _PLOOPI_TICKETS_DONE;
            while ($fields_status = $db->fetchrow($rs_status))
            {
                if ($fields_status['max_status'] < $global_status) $global_status = $fields_status['max_status'];
            }

            $this->fields['status'] = $global_status;

        }

        if ($this->new)
        {
            $ret = parent::save();
            // update root_id
            if (empty($this->fields['root_id'])) $this->fields['root_id'] = $this->fields['id'];
            if (empty($this->fields['parent_id'])) $this->fields['parent_id'] = $this->fields['id'];
            if ($this->fields['parent_id'] == $this->fields['id']) $this->fields['parent_id'] = 0;
            parent::save();
        }
        else $ret = parent::save();

        return($ret);
    }
}
?>
