<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion des types de paramètres (table ploopi_param_type)
 *
 * @package ploopi
 * @subpackage param
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class param_type extends data_object
{
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_param_type', 'id_module_type', 'name');
    }

    /**
     * Suppression du type de paramètre
     *
     * @param boolean $preserve_data true pour conserver les données associées
     */
    public function delete($preserve_data = false)
    {
        $db = db::get();

        $delete = "DELETE FROM ploopi_param_choice WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
        $db->query($delete);

        if (!$preserve_data)
        {
            $delete = "DELETE FROM ploopi_param_default WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_workspace WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);

            $delete = "DELETE FROM ploopi_param_user WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
            $db->query($delete);
        }

        parent::delete();
    }

    /**
     * Retourne un tableau contenant la liste des choix possibles pour le paramètre
     *
     * @return array tableau associatif contenant la liste des choix possibles pour le paramètre
     */

    public function getallchoices()
    {
        $db = db::get();

        $arrParamChoice = array();

        $select = "SELECT * FROM ploopi_param_choice WHERE id_module_type = {$this->fields['id_module_type']} AND name = '".$db->addslashes($this->fields['name'])."'";
        $db->query($select);

        while ($fields = $db->fetchrow()) $arrParamChoice[$fields['value']] = $fields['displayed_value'];

        return($arrParamChoice);
    }
}
