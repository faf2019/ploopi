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
 * @subpackage event
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Classe d'accès à la table 'ploopi_mod_booking_event'
 *
 * @package booking
 * @subpackage event
 * @author Stéphane Escaich
 * @copyright OVENSIA
 */

class booking_event extends ovensia\ploopi\data_object
{

    private $details;

    private $subresources;

    /**
     * Constructeur de la classe
     *
     * @return booking_event
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_booking_event', 'id');
        $this->details = array();
        $this->subresources = array();
    }

    public function open(...$args) {
        global $db;

        $res = parent::open($args[0]);
        $this->subresources = array();

        if ($res) {
            $db->query("SELECT id_subresource FROM ploopi_mod_booking_event_subresource WHERE id_event = {$this->fields['id']}");
            while ($row = $db->fetchrow()) $this->subresources[] = $row['id_subresource'];
        }

        return $res;
    }

    /**
     * Enregistre l'événement
     *
     * @return int id de la ressource
     */

    public function save()
    {
        global $db;

        if ($this->new)
        {
            $this->fields['timestp_request'] = ovensia\ploopi\date::createtimestamp();
        }

        $intIdEvent = parent::save();

        // Si il faut enregistrer des "event_detail"
        if (!empty($this->details))
        {
            include_once './modules/booking/classes/class_booking_event_detail.php';

            $objEventDetail = new booking_event_detail();
            $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::local2timestamp($this->details['timestp_begin_d'], sprintf("%02d:%02d:00", $this->details['timestp_begin_h'], $this->details['timestp_begin_m']));
            $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::local2timestamp($this->details['timestp_end_d'], sprintf("%02d:%02d:00", $this->details['timestp_end_h'], $this->details['timestp_end_m']));
            $objEventDetail->fields['message'] = $this->details['message'];
            $objEventDetail->fields['emails'] = $this->details['emails'];
            $objEventDetail->fields['id_event'] = $intIdEvent;
            $objEventDetail->save();



            if (!empty($this->fields['periodicity']) && !empty($this->details['periodicity_end_date'])) // Périodicité définie
            {
                // Timestp unix de la date de début du premier événement
                $intUxTsEventBegin = ovensia\ploopi\date::timestamp2unixtimestamp($objEventDetail->fields['timestp_begin']);

                // Timestp unix de la date de fin du premier événement
                $intUxTsEventEnd = ovensia\ploopi\date::timestamp2unixtimestamp($objEventDetail->fields['timestp_end']);

                // Timestp unix de la date de fin de périodicité
                $intUxTsPeriodEnd = ovensia\ploopi\date::timestamp2unixtimestamp(substr(ovensia\ploopi\date::local2timestamp($this->details['periodicity_end_date']), 0, 8).'235959');

                // Date de début du premier événement : Version tableau
                $arrBegin =
                    array(
                        'd' => date('j', $intUxTsEventBegin),
                        'm' => date('n', $intUxTsEventBegin),
                        'y' => date('Y', $intUxTsEventBegin),
                        'ho' => date('G', $intUxTsEventBegin),
                        'mi' => intval(date('i', $intUxTsEventBegin), 10),
                        'se' => intval(date('s', $intUxTsEventBegin), 10)
                    );

                // Date de fin du premier événement : Version tableau
                $arrEnd =
                    array(
                        'd' => date('j', $intUxTsEventEnd),
                        'm' => date('n', $intUxTsEventEnd),
                        'y' => date('Y', $intUxTsEventEnd),
                        'ho' => date('G', $intUxTsEventEnd),
                        'mi' => intval(date('i', $intUxTsEventEnd), 10),
                        'se' => intval(date('s', $intUxTsEventEnd), 10)
                    );

                switch($this->fields['periodicity'])
                {
                    case 'day':
                    case 'week':
                        // durée de la période en jours
                        $d = $period = $this->fields['periodicity'] == 'week' ? 7 : 1;

                        // Timestp du début du nouvel événément à tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);

                        // Si la date du nouvel événement est compatible avec la date de fin de périodicité
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $objEventDetail = new booking_event_detail();
                            $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::unixtimestamp2timestamp($intUxTs);
                            $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::unixtimestamp2timestamp(mktime($arrEnd['ho'], $arrEnd['mi'], $arrEnd['se'], $arrEnd['m'], $arrEnd['d'] + $d, $arrEnd['y']));
                            $objEventDetail->fields['id_event'] = $intIdEvent;
                            $objEventDetail->save();

                            $d += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);
                        }
                    break;

                    case 'month':
                    case 'year':
                        // durée de la période en mois
                        $m = $period = $this->fields['periodicity'] == 'year' ? 12 : 1;

                        // Timestp du début du nouvel événément à tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);

                        // Si la date du nouvel événement est compatible avec la date de fin de périodicité
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $objEventDetail = new booking_event_detail();
                            $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::unixtimestamp2timestamp($intUxTs);
                            $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::unixtimestamp2timestamp(mktime($arrEnd['ho'], $arrEnd['mi'], $arrEnd['se'], $arrEnd['m'] + $m, $arrEnd['d'], $arrEnd['y']));
                            $objEventDetail->fields['id_event'] = $intIdEvent;
                            $objEventDetail->save();

                            $m += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);
                        }
                    break;
                }
            }
        }

        $db->query("DELETE FROM ploopi_mod_booking_event_subresource WHERE id_event = {$this->fields['id']}");
        // Si il faut enregistrer des "event_detail"
        if (!empty($this->subresources))
        {
            foreach($this->subresources as $intIdSR) {
                if (!empty($intIdSR) && is_numeric($intIdSR)) {
                    $db->query($sql = "REPLACE INTO ploopi_mod_booking_event_subresource VALUES({$this->fields['id']}, {$intIdSR})");
                    echo $sql;
                }
            }
        }

        return($intIdEvent);
    }

    /**
     * Détermine si un événement est valide, c'est à dire qu'il n'y a pas de collision avec un événement déjà validé
     */
    public function isvalid($booValidated = true)
    {
        if (!empty($this->details)) {

            // timestp mysql de la demande principale
            $timestp_begin = ovensia\ploopi\date::local2timestamp($this->details['timestp_begin_d'], sprintf("%02d:%02d:00", $this->details['timestp_begin_h'], $this->details['timestp_begin_m']));
            $timestp_end = ovensia\ploopi\date::local2timestamp($this->details['timestp_end_d'], sprintf("%02d:%02d:00", $this->details['timestp_end_h'], $this->details['timestp_end_m']));

            // Recherche des événments validés dans l'intervalle de la demande principale
            $arrEvents = booking_get_events(
                $this->fields['id_resource'],
                true,
                false,
                $booValidated ? 1 : null,
                null, // managed
                '', // object
                '', //requestedby
                $this->details['timestp_begin_d'], //from
                $this->details['timestp_end_d'], //to
                $this->fields['id_module'] // moduleid
            );

            if (!empty($arrEvents)) {

                // Recherche plus précise de collisions
                foreach($arrEvents as $row) {

                    if ($row['id'] != $this->fields['id'] && ($timestp_begin >= $row['timestp_begin'] && $timestp_begin < $row['timestp_end']) || ($timestp_end > $row['timestp_begin'] && $timestp_end <= $row['timestp_end']) || ($timestp_begin <= $row['timestp_begin'] && $timestp_end >= $row['timestp_end'])) {
                        // Collision détectée
                        echo "je sors";
                        return false;
                    }
                }

            }

            if (!empty($this->fields['periodicity']) && !empty($this->details['periodicity_end_date'])) // Périodicité définie
            {
                // Timestp unix de la date de début du premier événement
                $intUxTsEventBegin = ovensia\ploopi\date::timestamp2unixtimestamp($timestp_begin);

                // Timestp unix de la date de fin du premier événement
                $intUxTsEventEnd = ovensia\ploopi\date::timestamp2unixtimestamp($timestp_end);

                // Timestp unix de la date de fin de périodicité
                $intUxTsPeriodEnd = ovensia\ploopi\date::timestamp2unixtimestamp(substr(ovensia\ploopi\date::local2timestamp($this->details['periodicity_end_date']), 0, 8).'235959');

                // Date de début du premier événement : Version tableau
                $arrBegin =
                    array(
                        'd' => date('j', $intUxTsEventBegin),
                        'm' => date('n', $intUxTsEventBegin),
                        'y' => date('Y', $intUxTsEventBegin),
                        'ho' => date('G', $intUxTsEventBegin),
                        'mi' => intval(date('i', $intUxTsEventBegin), 10),
                        'se' => intval(date('s', $intUxTsEventBegin), 10)
                    );

                // Date de fin du premier événement : Version tableau
                $arrEnd =
                    array(
                        'd' => date('j', $intUxTsEventEnd),
                        'm' => date('n', $intUxTsEventEnd),
                        'y' => date('Y', $intUxTsEventEnd),
                        'ho' => date('G', $intUxTsEventEnd),
                        'mi' => intval(date('i', $intUxTsEventEnd), 10),
                        'se' => intval(date('s', $intUxTsEventEnd), 10)
                    );

                switch($this->fields['periodicity'])
                {
                    case 'day':
                    case 'week':
                        // durée de la période en jours
                        $d = $period = $this->fields['periodicity'] == 'week' ? 7 : 1;

                        // Timestp du début du nouvel événement à tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);

                        // Si la date du nouvel événement est compatible avec la date de fin de périodicité
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $intUxTs2 = mktime($arrEnd['ho'], $arrEnd['mi'], $arrEnd['se'], $arrEnd['m'], $arrEnd['d'] + $d, $arrEnd['y']);

                            // Recherche des événments validés dans l'intervalle
                            $arrEvents = booking_get_events(
                                $this->fields['id_resource'],
                                true,
                                false,
                                1,
                                null, // managed
                                '', // object
                                '', //requestedby
                                date('d/m/Y', $intUxTs), //from
                                date('d/m/Y', $intUxTs2), //to
                                $this->fields['id_module'] // moduleid
                            );


                            // Recherche plus précise de collisions
                            foreach($arrEvents as $row) {
                                $timestp_begin = date('YmdHis', $intUxTs);
                                $timestp_end = date('YmdHis', $intUxTs2);

                                if (($timestp_begin >= $row['timestp_begin'] && $timestp_begin < $row['timestp_end']) || ($timestp_end > $row['timestp_begin'] && $timestp_end <= $row['timestp_end']) || ($timestp_begin <= $row['timestp_begin'] && $timestp_end >= $row['timestp_end'])) {
                                    // Collision détectée
                                    return false;
                                }
                            }

                            $d += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);
                        }
                    break;

                    case 'month':
                    case 'year':
                        // durée de la période en mois
                        $m = $period = $this->fields['periodicity'] == 'year' ? 12 : 1;

                        // Timestp du début du nouvel événément à tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);

                        // Si la date du nouvel événement est compatible avec la date de fin de périodicité
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $intUxTs2 = mktime($arrEnd['ho'], $arrEnd['mi'], $arrEnd['se'], $arrEnd['m'] + $m, $arrEnd['d'], $arrEnd['y']);

                            echo '<br />'.date('d/m/Y', $intUxTs).' -> '.date('d/m/Y', $intUxTs2);

                            // Recherche des événments validés dans l'intervalle
                            $arrEvents = booking_get_events(
                                $this->fields['id_resource'],
                                true,
                                false,
                                1,
                                null, // managed
                                '', // object
                                '', //requestedby
                                date('d/m/Y', $intUxTs), //from
                                date('d/m/Y', $intUxTs2), //to
                                $this->fields['id_module'] // moduleid
                            );

                            // Recherche plus précise de collisions
                            foreach($arrEvents as $row) {
                                $timestp_begin = date('YmdHis', $intUxTs);
                                $timestp_end = date('YmdHis', $intUxTs2);
                                if (($timestp_begin >= $row['timestp_begin'] && $timestp_begin < $row['timestp_end']) || ($timestp_end > $row['timestp_begin'] && $timestp_end <= $row['timestp_end']) || ($timestp_begin <= $row['timestp_begin'] && $timestp_end >= $row['timestp_end'])) {
                                    // Collision détectée
                                    return false;
                                }
                            }

                            $m += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);
                        }
                    break;
                }
            }
        }


        return true;
    }

    public function setdetails($values, $prefix)
    {
        // Détermine le longueur du préfixe des variables
        $intPrefixLength = strlen($prefix);

        foreach ($values as $key => $value)
        {
            $strPref = substr($key, 0, $intPrefixLength);
            if ($strPref == $prefix)
            {
                $strProperty = substr($key,$intPrefixLength);
                $this->details[$strProperty] = $value;
            }
        }
    }


    public function getdetails()
    {
        global $db;

        // Recherche des détails liés à l'événement
        $db->query("SELECT * FROM ploopi_mod_booking_event_detail WHERE id_event = {$this->fields['id']} ORDER BY timestp_begin, timestp_end");
        return $db->getarray();
    }

    public function getrawdetails()
    {
        return $this->details;
    }

    public function setsubresources($subresources) {
        $this->subresources = $subresources;
    }

    public function getsubresources() {
        return $this->subresources;
    }
}
?>
