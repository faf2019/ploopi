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
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe parent
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'acc�s � la table 'ploopi_mod_booking_event'
 * 
 * @package booking
 * @subpackage event
 * @author St�phane Escaich
 * @copyright OVENSIA
 */

class booking_event extends data_object
{
    
    private $details;
    
    /**
     * Constructeur de la classe
     *
     * @return booking_event
     */
    
    public function booking_event()
    {
        parent::data_object('ploopi_mod_booking_event', 'id');
        $this->details = array();
    }
    
    /**
     * Enregistre l'�v�nement
     *
     * @return int id de la ressource
     */
    
    public function save()
    {

        if ($this->new) 
        {
            $this->fields['timestp_request'] = ploopi_createtimestamp();
        }
        
        echo $intIdEvent = parent::save();
        
        // Si il faut enregistrer des "event_detail"
        if (!empty($this->details))
        {
            include_once './modules/booking/classes/class_booking_event_detail.php';

            $objEventDetail = new booking_event_detail();
            $objEventDetail->fields['timestp_begin'] = ploopi_local2timestamp($this->details['timestp_begin_d'], sprintf("%02d:%02d:00", $this->details['timestp_begin_h'], $this->details['timestp_begin_m']));
            $objEventDetail->fields['timestp_end'] = ploopi_local2timestamp($this->details['timestp_end_d'], sprintf("%02d:%02d:00", $this->details['timestp_end_h'], $this->details['timestp_end_m']));
            $objEventDetail->fields['id_event'] = $intIdEvent;
            $objEventDetail->save();
            
            
             
            if (!empty($this->fields['periodicity']) && !empty($this->details['periodicity_end_date'])) // P�riodicit� d�finie
            {
                // Timestp unix de la date de d�but du premier �v�nement
                $intUxTsEventBegin = ploopi_timestamp2unixtimestamp($objEventDetail->fields['timestp_begin']);
                
                // Timestp unix de la date de fin du premier �v�nement
                $intUxTsEventEnd = ploopi_timestamp2unixtimestamp($objEventDetail->fields['timestp_end']);
                
                // Timestp unix de la date de fin de p�riodicit�
                $intUxTsPeriodEnd = ploopi_timestamp2unixtimestamp(substr(ploopi_local2timestamp($this->details['periodicity_end_date']), 0, 8).'235959');
                
                // Date de d�but du premier �v�nement : Version tableau
                $arrBegin = 
                    array(
                        'd' => date('j', $intUxTsEventBegin),
                        'm' => date('n', $intUxTsEventBegin),        
                        'y' => date('Y', $intUxTsEventBegin),
                        'ho' => date('G', $intUxTsEventBegin),
                        'mi' => intval(date('i', $intUxTsEventBegin), 10),
                        'se' => intval(date('s', $intUxTsEventBegin), 10)
                    );
                
                // Date de fin du premier �v�nement : Version tableau
                $arrENd = 
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
                        // dur�e de la p�riode en jours
                        $d = $period = $this->fields['periodicity'] == 'week' ? 7 : 1;
                        
                        // Timestp du d�but du nouvel �v�n�ment � tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);
                        
                        // Si la date du nouvel �v�nement est compatible avec la date de fin de p�riodicit�
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $objEventDetail = new booking_event_detail();
                            $objEventDetail->fields['timestp_begin'] = ploopi_unixtimestamp2timestamp($intUxTs);
                            $objEventDetail->fields['timestp_end'] = ploopi_unixtimestamp2timestamp(mktime($arrENd['ho'], $arrENd['mi'], $arrENd['se'], $arrENd['m'], $arrENd['d'] + $d, $arrENd['y']));
                            $objEventDetail->fields['id_event'] = $intIdEvent;
                            $objEventDetail->save();

                            $d += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'], $arrBegin['d'] + $d, $arrBegin['y']);
                        }
                    break;
                    
                    case 'month':
                    case 'year':
                        // dur�e de la p�riode en mois
                        $m = $period = $this->fields['periodicity'] == 'year' ? 12 : 1;
                        
                        // Timestp du d�but du nouvel �v�n�ment � tester
                        $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);
                        
                        // Si la date du nouvel �v�nement est compatible avec la date de fin de p�riodicit�
                        while ($intUxTs < $intUxTsPeriodEnd)
                        {
                            $objEventDetail = new booking_event_detail();
                            $objEventDetail->fields['timestp_begin'] = ploopi_unixtimestamp2timestamp($intUxTs);
                            $objEventDetail->fields['timestp_end'] = ploopi_unixtimestamp2timestamp(mktime($arrENd['ho'], $arrENd['mi'], $arrENd['se'], $arrENd['m'] + $m, $arrENd['d'], $arrENd['y']));
                            $objEventDetail->fields['id_event'] = $intIdEvent;
                            $objEventDetail->save();

                            $m += $period;
                            $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);
                        }
                    break;
                }
            }
        }
        
        return($intIdEvent);
    }
    
    public function setdetails($values, $prefix)
    {
        // D�termine le longueur du pr�fixe des variables
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
        
        // Recherche des d�tails li�s � l'�v�nement
        $db->query("SELECT * FROM ploopi_mod_booking_event_detail WHERE id_event = {$this->fields['id']} ORDER BY timestp_begin, timestp_end");
        return $db->getarray();
    }
}
?>