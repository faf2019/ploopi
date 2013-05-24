<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Gestion des relations d'une requête
 *
 * @package dbreport
 * @subpackage queryrelation
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

include_once './modules/dbreport/classes/class_dbreport_query.php';


/**
 * Classe de gestion des relations d'une requête
 */

class dbreport_queryrelation extends data_object
{
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_mod_dbreport_queryrelation', 'id_query', 'tablename_src', 'fieldname_src', 'tablename_dest', 'fieldname_dest');
    }

    /**
     * Enregistrement du lien relation/requête
     *
     * @return int id
     */
    public function save()
    {
        $objDbrQuery = new dbreport_query();
        if ($objDbrQuery->open($this->fields['id_query'])) $objDbrQuery->save();

        return parent::save();
    }

    /**
     * Suppression du lien relation/requête
     */
    public function delete()
    {
        // Mise à jour de la requête
        $objDbrQuery = new dbreport_query();
        if ($objDbrQuery->open($this->fields['id_query'])) $objDbrQuery->save();

        parent::delete();
    }
}
?>
