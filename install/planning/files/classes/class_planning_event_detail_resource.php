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

/**
 * Gestion des événements/ressources
 *
 * @package planning
 * @subpackage event_detail_resource
 * @copyright Ovensia
 * @author Ovensia
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Classe d'accès à la table 'ploopi_mod_planning_event_detail'
 *
 * @package planning
 * @subpackage event_detail_resource
 * @author Ovensia
 * @copyright Ovensia
 */

class planning_event_detail_resource extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return planning_event_detail_resource
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_mod_planning_event_detail_resource',
            'id_event_detail',
            'id_resource',
            'type_resource'
        );
    }
}
