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
 * Gestion des champs d'une requête
 * 
 * @package dbreport
 * @subpackage queryfield
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

include_once './include/classes/query.php';
include_once './modules/dbreport/classes/class_dbreport_query.php';

/**
 * Classe de gestion des champs d'une requête
 */

class dbreport_queryfield extends data_object
{	
    /**
     * Constructeur de la classe
     */
	public function __construct()
	{
		parent::__construct('ploopi_mod_dbreport_queryfield');
		$this->fields['operation'] = 'groupby';
	}

	/**
	 * Enregistre le champ
	 */
	public function save()
	{
		if ($this->new)
		{
		    // Détermination de la nouvelle position
		    $objQuery = new ploopi_query_select();
		    $objQuery->add_select('MAX(position) AS maxposition');
		    $objQuery->add_from('ploopi_mod_dbreport_queryfield');
		    $objQuery->add_where('id_query = %d', $this->fields['id_query']);
		    $row = $objQuery->execute()->fetchrow();
		    
			$this->fields['position'] = isset($row['maxposition']) ? $row['maxposition'] + 1 : 1;
		}		

        // Mise à jour de la requête
		$objDbrQuery = new dbreport_query();
        if ($objDbrQuery->open($this->fields['id_query'])) $objDbrQuery->save();
		
        return parent::save();
	}
	
    /**
     * Supprime le champ
     */	
	public function delete()
	{
	    // Mise à jour des positions des autres champs
        $objQuery = new ploopi_query_update();
        $objQuery->add_from('ploopi_mod_dbreport_queryfield');
        $objQuery->add_set('position = position - 1');
        $objQuery->add_where('id_query = %d', $this->fields['id_query']);
        $objQuery->add_where('position > %d', $this->fields['position']);
        $objQuery->execute();

        // Mise à jour de la requête
        $objDbrQuery = new dbreport_query();
        if ($objDbrQuery->open($this->fields['id_query'])) $objDbrQuery->save();
        
        parent::delete();
	}
	
}
?>