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

class homepage_column extends data_object
{
    /**
    * Class constructor
    *
    * @param int $connection_id 
    * @access public
    **/

    function homepage_column()
    {
        parent::data_object('ploopi_homepage_column');
        $this->fields['id_module'] = 0;
    }
    
    
    function save()
    {
        global $db;

        if ($this->new) // insert
        {
            // get max from line position
            $select =   "
                    SELECT max(position) as maxposition
                    FROM ploopi_homepage_column
                    WHERE id_line = ".$this->fields['id_line'];
        
            $result = $db->query($select);
        
            $this->fields['position'] = 1;
            
            if ($resfields = $db->fetchrow($result))
            {
                $this->fields['position'] = $resfields['maxposition'] + 1;
            }
        }
                

        parent::save();
    }

    function delete()
    {
        global $db;

        // update all columns in line
        $update =   "   
                UPDATE  ploopi_homepage_column
                SET     position = position - 1
                WHERE   position > ".$this->fields['position']."
                AND     id_line = ".$this->fields['id_line'];

        $db->query($update);;           
        
        parent::delete();
    }
        
}
?>