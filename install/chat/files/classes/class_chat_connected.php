<?php
/*
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

class chat_connected extends data_object
{
    function chat_connected()
    {
        parent::data_object('ploopi_mod_chat_connected', 'id_user', 'id_module');
    }
    
    function open($id_user = -1, $id_module = -1)
    {
        if ($id_user == -1) $id_user = $_SESSION['ploopi']['userid'];
        if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];
        
        parent::open($id_user, $id_module);
    }
    
    function save()
    {
        $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();
        $this->fields['id_user'] = $_SESSION['ploopi']['userid'];
        $this->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
        
        if ($this->new) $this->fields['connection_timestp'] = $this->fields['lastupdate_timestp'];
        
        parent::save();
    }
}
