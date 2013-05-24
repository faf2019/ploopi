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
 * Gestion des tables d'une requ�te
 *
 * @package dbreport
 * @subpackage querytable
 * @copyright Ovensia
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';
include_once './include/classes/data_object_collection.php';

//include_once './include/classes/query.php';
include_once './modules/dbreport/classes/class_dbreport_query.php';
include_once './modules/dbreport/classes/class_dbreport_queryfield.php';
include_once './modules/dbreport/classes/class_dbreport_queryrelation.php';

/**
 * Classe de gestion des tables d'une requ�te
 */


class dbreport_querytable extends data_object
{
    /**
     * Constructeur de la classe
     */
    public function __construct()
    {
        parent::__construct('ploopi_mod_dbreport_querytable');
    }

    /**
     * Enregistrement du lien table/requ�te
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
     * Suppression de la table de la requ�te
     */
    public function delete()
    {
        // Suppression des champs li�s � la table supprim�e
        $objCol = new data_object_collection('dbreport_queryfield');
        $objCol->add_where('id_query = %d', $this->fields['id_query']);
        $objCol->add_where('tablename = %s', $this->fields['tablename']);
        $arrObjects = $objCol->get_objects();
        foreach($arrObjects as $objField) $objField->delete();

        // Suppression des relations li�es � la table supprim�e
        $objCol = new data_object_collection('dbreport_queryrelation');
        $objCol->add_where('id_query = %d', $this->fields['id_query']);
        $objCol->add_where('(tablename_src = %1$s OR tablename_dest = %1$s)', $this->fields['tablename']);
        $arrObjects = $objCol->get_objects();
        foreach($arrObjects as $objRelation) $objRelation->delete();

        // Mise � jour de la requ�te
        $objDbrQuery = new dbreport_query();
        if ($objDbrQuery->open($this->fields['id_query'])) $objDbrQuery->save();

        parent::delete();
    }


}
?>
