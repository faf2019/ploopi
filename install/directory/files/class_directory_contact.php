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
 * Gestion des contacts
 *
 * @package directory
 * @subpackage contacts
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_directory_contact
 *
 * @package directory
 * @subpackage contacts
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class directory_contact extends data_object
{   
    /**
     * Constructeur de la classe
     *
     * @return directory_contact
     */
    
    function directory_contact()
    {
        parent::data_object('ploopi_mod_directory_contact');
    }
    
    /**
     * Supprime le contact et les favoris associés
     */
    
    function delete()
    {
        global $db;
        
        $db->query("DELETE FROM ploopi_mod_directory_favorites WHERE id_contact = {$this->fields['id']}");
        parent::delete();
    }
    
    public function getphotopath()
    {
        return (_PLOOPI_PATHDATA._PLOOPI_SEP.'directory'._PLOOPI_SEP.$this->fields['id'].'.png');
    }
    
}
?>