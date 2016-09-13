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
 * Gestion des sous-sous-ressources
 *
 * @package booking
 * @subpackage subsubresource
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Classe d'accès à la table 'ploopi_mod_booking_subresource'
 *
 * @package booking
 * @subpackage subresource
 * @author Stéphane Escaich
 * @copyright OVENSIA
 */

class booking_subresource extends ovensia\ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return booking_subresource
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_booking_subresource', 'id');
    }


    /**
     * Enregistre la sous-ressource
     *
     * @return int id de la sous-ressource
     */

    public function save()
    {
        if ($this->new) $this->setuwm();

        return parent::save();
    }
}
?>
