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
 * Gestion des tickets
 * 
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_ticket
 * 
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket extends data_object
{
    /**
     * Constructeur de la classe
     * 
     * @return subscription_action
     */
    
    function ticket()
    {
        parent::data_object('ploopi_ticket', 'id');
    }

    /**
     * Enregistre le ticket
     *
     * @return boolean true si le ticket s'est enregistré correctement
     */
    
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

        // enregistrement d'un nouveau ticket
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

/**
 * Classe d'accès à la table ploopi_ticket_watch
 * 
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket_watch extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return ticket_watch
     */
    
    public function ticket_watch()
    {
        parent::data_object('ploopi_ticket_watch', 'id_ticket', 'id_user');
    }
}

/**
 * Classe d'accès à la table ploopi_ticket_status
 * 
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket_status extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return ticket_status
     */
    
    public function ticket_status()
    {
        parent::data_object('ploopi_ticket_status', 'id_ticket', 'id_user', 'status');
    }
    
    /**
     * Enregistre l'état d'un ticket pour un utilisateur
     */
    
    public function save()
    {
        if ($this->new)
        {
            $this->fields['timestp'] = ploopi_createtimestamp();
            parent::save();
        }
    }
}

/**
 * Classe d'accès à la table ploopi_ticket_dest
 * 
 * @package ploopi
 * @subpackage ticket
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket_dest extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return ticket_dest
     */
    
    public function ticket_dest()
    {
        parent::data_object('ploopi_ticket_dest','id_user','id_ticket');
    }
}
?>
