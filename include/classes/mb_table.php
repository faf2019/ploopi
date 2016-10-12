<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Classe d'accès à la table ploopi_mb_table
 *
 * @package ploopi
 * @subpackage metabase
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class mb_table extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return mb_table
     */

    function __construct()
    {
        parent::__construct('ploopi_mb_table','name','id_module_type');
    }

    /**
     * Supprime la table ainsi que les champs, les relations et le schéma associés
     */

    function delete()
    {
        $db = db::get();

        $db->query("DELETE FROM ploopi_mb_field WHERE tablename = '".$db->addslashes($this->fields['name'])."' AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_relation WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        $db->query("DELETE FROM ploopi_mb_schema WHERE (tablesrc = '".$db->addslashes($this->fields['name'])."' OR tabledest = '".$db->addslashes($this->fields['name'])."') AND id_module_type = {$this->fields['id_module_type']}");
        parent::delete();
    }
}
