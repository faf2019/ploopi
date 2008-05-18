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
 * Gestion des formulaires
 *
 * @package forms
 * @subpackage form
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_form
 *
 * @package forms
 * @subpackage form
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class form extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return form
     */
    
    function form()
    {
        parent::data_object('ploopi_mod_forms_form');
    }

    /**
     * Enregistre le formulaire
     *
     * @return int indentifiant du formulaire
     */
    
    function save()
    {
        if ($this->fields['tablename'] == '') $this->fields['tablename'] = $this->fields['label'];
        $this->fields['tablename'] = forms_createphysicalname($this->fields['tablename']);
        return(parent::save());
    }

    /**
     * Renvoie la liste des champs du formulaire 
     *
     * @return array tableau des champs indexé par les identifiants
     */
    
    function getfields()
    {
        global $db;

        $fields = array();

        $select = "SELECT * FROM ploopi_mod_forms_field WHERE id_form = {$this->fields['id']} AND separator = 0";

        $db->query($select);

        while ($row = $db->fetchrow())
        {
            $fields[$row['id']] = $row;
        }

        return($fields);

    }
}
?>