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
 * Gestion des ressources
 *
 * @package planning
 * @subpackage event
 * @copyright Ovensia
 * @author St�phane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion des autres classes utilis�es
 */
include_once './modules/planning/classes/class_planning_event_detail.php';

/**
 * Classe d'acc�s � la table 'ploopi_mod_planning_event'
 *
 * @package planning
 * @subpackage event
 * @author St�phane Escaich
 * @copyright OVENSIA
 */

class planning_event extends ovensia\ploopi\data_object
{

    private $arrDetail;
    private $arrResource;

    /**
     * Constructeur de la classe
     *
     * @return planning_event
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_mod_planning_event',
            'id');

        $this->arrDetail = array();

        $this->initresources();
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
            $this->fields['timestp_request'] = ovensia\ploopi\date::createtimestamp();
            parent::save();

            // Si il faut enregistrer des "event_detail"
            if (!empty($this->arrDetails))
            {

                $objEventDetail = new planning_event_detail();
                $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::local2timestamp($this->arrDetails['timestp_begin_d'], sprintf("%02d:%02d:00", $this->arrDetails['timestp_begin_h'], $this->arrDetails['timestp_begin_m']));
                $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::local2timestamp($this->arrDetails['timestp_end_d'], sprintf("%02d:%02d:00", $this->arrDetails['timestp_end_h'], $this->arrDetails['timestp_end_m']));
                $objEventDetail->fields['id_event'] = $this->fields['id'];
                $objEventDetail->setresources($this->arrResource, false);
                $objEventDetail->save();

                if (!empty($this->fields['periodicity']) && !empty($this->arrDetails['periodicity_end_date'])) // P�riodicit� d�finie
                {
                    // Timestp unix de la date de d�but du premier �v�nement
                    $intUxTsEventBegin = ovensia\ploopi\date::timestamp2unixtimestamp($objEventDetail->fields['timestp_begin']);

                    // Timestp unix de la date de fin du premier �v�nement
                    $intUxTsEventEnd = ovensia\ploopi\date::timestamp2unixtimestamp($objEventDetail->fields['timestp_end']);

                    // Timestp unix de la date de fin de p�riodicit�
                    $intUxTsPeriodEnd = ovensia\ploopi\date::timestamp2unixtimestamp(substr(ovensia\ploopi\date::local2timestamp($this->arrDetails['periodicity_end_date']), 0, 8).'235959');

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
                                $objEventDetail = new planning_event_detail();
                                $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::unixtimestamp2timestamp($intUxTs);
                                $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::unixtimestamp2timestamp(mktime($arrENd['ho'], $arrENd['mi'], $arrENd['se'], $arrENd['m'], $arrENd['d'] + $d, $arrENd['y']));
                                $objEventDetail->fields['id_event'] = $this->fields['id'];
                                $objEventDetail->setresources($this->arrResource, false);
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
                                $objEventDetail = new planning_event_detail();
                                $objEventDetail->fields['timestp_begin'] = ovensia\ploopi\date::unixtimestamp2timestamp($intUxTs);
                                $objEventDetail->fields['timestp_end'] = ovensia\ploopi\date::unixtimestamp2timestamp(mktime($arrENd['ho'], $arrENd['mi'], $arrENd['se'], $arrENd['m'] + $m, $arrENd['d'], $arrENd['y']));
                                $objEventDetail->fields['id_event'] = $this->fields['id'];
                                $objEventDetail->setresources($this->arrResource, false);
                                $objEventDetail->save();

                                $m += $period;
                                $intUxTs = mktime($arrBegin['ho'], $arrBegin['mi'], $arrBegin['se'], $arrBegin['m'] + $m, $arrBegin['d'], $arrBegin['y']);
                            }
                        break;
                    }
                }
            }
        }
        else // mise � jour
        {
            parent::save();
        }


        return($this->fields['id']);
    }

    /**
     * Affecte les d�tails concern�s
     *
     * @param $arrValues tableau des d�tails
     */
    public function setdetails($arrValues, $strPrefix)
    {
        // D�termine le longueur du pr�fixe des variables
        $intPrefixLength = strlen($strPrefix);

        foreach ($arrValues as $strKey => $strValue)
        {
            $strPref = substr($strKey, 0, $intPrefixLength);
            if ($strPref == $strPrefix)
            {
                $strProperty = substr($strKey, $intPrefixLength);
                $this->arrDetails[$strProperty] = $strValue;
            }
        }
    }

    /**
     * Affecte les ressources concern�es
     *
     * @param array $arrResource tableau des ressources (groupes/utilisateurs)
     * @param boolean $booVerify true s'il faut v�rifier le contenu de la variable
     */

    public function setresources($arrResource, $booVerify = true)
    {
        if ($booVerify)
        {
            $this->initresources();

            if (is_array($arrResource))
            {
                foreach($arrResource as $strResourceType => $arrTypeResource)
                {
                    foreach($arrTypeResource as $intIdResource)
                    {
                        switch($strResourceType)
                        {
                            case 'user': // utilisateur
                                $objUser = new ovensia\ploopi\user();
                                if ($objUser->open($intIdResource)) // utilisateur existe
                                {
                                    $this->arrResource['user'][$intIdResource] = $intIdResource;
                                }
                            break;

                            case 'group': // groupe
                                $objGroup = new ovensia\ploopi\group();
                                if ($objGroup->open($intIdResource)) // groupe existe
                                {
                                    $this->arrResource['group'][$intIdResource] =$intIdResource;
                                }
                            break;
                        }
                    }
                }
            }
        }
        else $this->arrResource = $arrResource;
    }

    /**
     * Retourne un tableau contenant les d�tails
     *
     * @return array tableau des d�tails
     */
    public function getdetails()
    {
        global $db;

        // Recherche des d�tails li�s � l'�v�nement
        $db->query("SELECT * FROM ploopi_mod_planning_event_detail WHERE id_event = {$this->fields['id']} ORDER BY timestp_begin, timestp_end");
        return $db->getarray();
    }

    /**
     * Initialise le tableau des ressources
     */
    private function initresources()
    {
        $this->arrResource = array(
            'user' => array(),
            'group' => array()
        );
    }

}
?>
