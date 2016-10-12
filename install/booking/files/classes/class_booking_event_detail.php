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
 * Gestion des ressources
 *
 * @package booking
 * @subpackage event_detail
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

include_once './modules/booking/classes/class_booking_event.php';

/**
 * Classe d'accès à la table 'ploopi_mod_booking_event_detail'
 *
 * @package booking
 * @subpackage event_detail
 * @author Stéphane Escaich
 * @copyright Ovensia
 */

class booking_event_detail extends ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return booking_event_detail
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_booking_event_detail', 'id');
    }

    /**
     * Supprime un détail en vérifiant que l'événement peut être supprimé ou non (booking_event)
     *
     * @return unknown
     */
    public function delete()
    {
        $db = ploopi\db::get();

        // Recherche si l'event contient d'autres détails.
        // S'il n'en contient pas, on le supprime
        $db->query("
            SELECT id FROM ploopi_mod_booking_event_detail WHERE id_event = {$this->fields['id_event']} AND id != {$this->fields['id']}
        ");

        // Pas d'autres détails rattachés
        if ($db->numrows() == 0)
        {
            include_once './modules/booking/classes/class_booking_event.php';
            $objEvent = new booking_event();
            $objEvent->open($this->fields['id_event']);
            $objEvent->delete();
        }

        return(parent::delete());
    }

    /**
     * Détermine si un détail d'événement est valide, c'est à dire qu'il n'y a pas de collision avec un détail déjà validé
     */
    public function isvalid(booking_event $objEvent = null) {
        if (empty($objEvent)) {
            $objEvent = new booking_event();
            if (!$objEvent->open($this->fields['id_event'])) return false;
        }

        // Recherche des événments validés dans l'intervalle de la demande principale
        $arrEvents = booking_get_events(
            $objEvent->fields['id_resource'],
            true,
            false,
            1,
            null, // managed
            '', // object
            '', //requestedby
            current(ploopi\date::timestamp2local($this->fields['timestp_begin'])), //from
            current(ploopi\date::timestamp2local($this->fields['timestp_end'])), //to
            $objEvent->fields['id_module'] // moduleid
        );

        ploopi\output::print_r($arrEvents);

        if (!empty($arrEvents)) {

            $timestp_begin = $this->fields['timestp_begin'];
            $timestp_end = $this->fields['timestp_end'];

            // Recherche plus précise de collisions
            foreach($arrEvents as $row) {
                if (($timestp_begin >= $row['timestp_begin'] && $timestp_begin < $row['timestp_end']) || ($timestp_end > $row['timestp_begin'] && $timestp_end <= $row['timestp_end']) || ($timestp_begin <= $row['timestp_begin'] && $timestp_end >= $row['timestp_end'])) {
                    // Collision détectée
                    return false;
                }
            }
        }

        return true;
    }

}
