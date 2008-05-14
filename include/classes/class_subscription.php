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

include_once './include/classes/class_data_object.php';

/**
 * Accès à la table ploopi_subscription 
 * 
 * @package ploopi
 * @subpackage subscription
 * @copyright Netlor, Ovensia
 * @license GPL
 */

class subscription extends data_object
{
    function subscription()
    {
        parent::data_object('ploopi_subscription','id');
    }
    
    
    function clean()
    {
        global $db;
        $db->query("DELETE FROM ploopi_subscription_action WHERE id_subscription = '{$this->fields['id']}'");
    }
    
    function getactions()
    {
        global $db;
        
        $arrActions = array();
        
        if (!$this->new && !$this->fields['allactions'])
        {
            $db->query("SELECT id_action FROM ploopi_subscription_action WHERE id_subscription = '{$this->fields['id']}'");
            $arrActions = $db->getarray();
        }
        
        return($arrActions);
    }
    
    function delete()
    {
        $this->clean();
        parent::delete();
    }
}
?>