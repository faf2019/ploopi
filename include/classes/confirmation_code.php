<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Gestion des codes de confirmation (demandes par email)
 * 
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe de gestion des confirmation par mail
 * 
 * @package ploopi
 * @subpackage security
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class confirmation_code extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return confirmation_code
     */
    function confirmation_code()
    {
        parent::data_object('ploopi_confirmation_code', 'action');
    }
    
    /**
     * Enregistrement du code de confirmation
     *
     * @return unknown
     */
    
    function save()
    {
        if ($this->new)
        {
            if (empty($this->fields['code'])) $this->fields['code'] = md5(uniqid(rand(), true));
            if (empty($this->fields['timestp'])) $this->fields['timestp'] = ploopi_createtimestamp();
        }
        return(parent::save());
    }

}
?>
