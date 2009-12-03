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
 * Gestion des champs
 *
 * @package forms
 * @subpackage field
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table ploopi_mod_forms_field
 *
 * @package forms
 * @subpackage field
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class field extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return field
     */
    
    function field()
    {
        parent::data_object('ploopi_mod_forms_field');
    }

    /**
     * Enregistre le champ
     *
     * @return int identifiant du champ enregistr�
     *
     * @see forms_createphysicalname
     */
    
    function save()
    {
        if (empty($this->fields['fieldname'])) $this->fields['fieldname'] = $this->fields['name'];
        $this->fields['fieldname'] = forms_createphysicalname($this->fields['fieldname']);
        
        return parent::save();
    }
    
    
    /**
     * Supprime le champ
     *
     * @return boolean
     */
    function delete()
    {
        global $db;
        
        $db->query("UPDATE `ploopi_mod_forms_field` SET position = position - 1 WHERE position > {$this->fields['position']} AND id_form = {$this->fields['id_form']}");
        
        return parent::delete();
    }
}
?>