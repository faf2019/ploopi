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
 * Fonctions, constantes, variables globales
 *
 * @package webedit
 * @subpackage global
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Définition des constantes
 */

/**
 * Action : Ajout d'un événement
 */
define ('_PLANNING_ADD_EVENT',   10);

/**
 * Objet 'événement'
 */
define ('_PLANNING_OBJECT_EVENT',   1);


/**
 * Variables globales
 */

global $arrPlanningPeriodicity;

$arrPlanningPeriodicity =
    array(
        'day' => 'Quotidienne',
        'week' => 'Hebdomadaire',
        'month' => 'Mensuelle',
        'year' => 'Annuelle'
    );

global $arrPlanningSize;

$arrPlanningSize =
    array(
        '800x650',
        '1000x800',
        '1200x975'
    );

/**
 * Retourne la liste des ressources disponibles (utilisateurs/groupes)
 *
 * @return array tableau contenant les ressources disponibles
 */

function planning_get_resources()
{
    $arrResource = array('user' => array(), 'group' => array());

    $arrWorkspaces = explode(',', ovensia\ploopi\system::viewworkspaces());

    foreach($arrWorkspaces as $intIdWorkspace)
    {
        $objWorkspace = new ovensia\ploopi\workspace();
        if ($objWorkspace->open($intIdWorkspace))
        {
            foreach($objWorkspace->getusers(true) as $arrUser)
            {
                $arrResource['user'][$arrUser['id']] = array(
                    'id' => $arrUser['id'],
                    'label' => "{$arrUser['lastname']} {$arrUser['firstname']}",
                    'color' => $arrUser['color']
                );
            }
            if (ovensia\ploopi\param::get('planning_display_groups'))
            {
                foreach($objWorkspace->getgroups() as $arrGroup)
                {
                    $arrResource['group'][$arrGroup['id']] = array(
                        'id' => $arrGroup['id'],
                        'label' => $arrGroup['label'],
                        'color' => ''
                    );
                }
            }
        }
    }

    return $arrResource;
}

function planning_get_events($arrResources, $intTimepstpBegin = null, $intTimepstpEnd = null)
{
    global $db;

    $arrEvents = array();

    $arrWhere = array();

    $arrWhere['ed'][] = 'ed.id_event = e.id';
    if (!empty($intTimepstpBegin)) $arrWhere['ed'][] = "ed.timestp_end >= $intTimepstpBegin";
    if (!empty($intTimepstpEnd)) $arrWhere['ed'][] = "ed.timestp_begin <= $intTimepstpEnd";

    /**
     * Selection des événements (+ détails)
     */
    $db->query("
        SELECT      e.*,
                    ed.id as ed_id,
                    ed.timestp_begin,
                    ed.timestp_end

        FROM        ploopi_mod_planning_event e

        INNER JOIN  ploopi_mod_planning_event_detail ed
        ON          ".implode(' AND ', $arrWhere['ed'])."

        WHERE       e.id_module = {$_SESSION['ploopi']['moduleid']}
        AND         e.id_workspace IN (".ovensia\ploopi\system::viewworkspaces().")

        ORDER BY    ed.timestp_begin, ed.timestp_end
    ");

    while ($row = $db->fetchrow())
    {
        if ($row['timestp_begin'] > $row['timestp_end'])
        {
            $intTs = $row['timestp_begin'];
            $row['timestp_begin'] = $row['timestp_end'];
            $row['timestp_end'] = $intTs;
        }

        $arrEvents[$row['ed_id']] = $row;
    }

    /**
     * Selection des ressources
     */
    if (!empty($arrEvents))
    {
        $db->query("
            SELECT      edr.*

            FROM        ploopi_mod_planning_event_detail_resource edr

            WHERE       edr.id_event_detail IN (".implode(',', array_keys($arrEvents)).")

            ORDER BY    edr.type_resource, edr.id_resource
        ");

        while ($row = $db->fetchrow())
        {
            $arrEvents[$row['id_event_detail']]['res'][$row['type_resource']][$row['id_resource']] = $row['id_resource'];
            /*$arrEvents[$row['id_event_detail']]['res'][] = array(
                'id_resource' => $row['id_resource'],
                'type_resource' => $row['type_resource']
            );*/
        }
    }

    /**
     * Filtrage en fonction des ressources
     */
    $arrFilteredEvents = array();

    foreach($arrEvents as $intIdEvent => $arrEvent)
    {
        if (isset($arrEvent['res']))
        {
            if ((isset($arrEvent['res']['group']) && isset($arrResources['group']) && count(array_intersect($arrEvent['res']['group'], $arrResources['group']))) || (isset($arrEvent['res']['user']) && isset($arrResources['user']) && count(array_intersect($arrEvent['res']['user'], $arrResources['user']))))
            {
                $arrFilteredEvents[$intIdEvent] = $arrEvent;
            }
        }
    }


    unset($arrEvents);
    unset($arrWhere);

    return $arrFilteredEvents;
}

/**
 * Lecture du cookie de recherche
 */

function planning_getcookie()
{
    $arrSearchPattern = array();

    // Lecture cookie
    ovensia\ploopi\error::unset_handler();
    if (isset($_COOKIE["planning_request{$_SESSION['ploopi']['moduleid']}"])) $arrSearchPattern = unserialize(gzuncompress(base64_decode($_COOKIE["planning_request{$_SESSION['ploopi']['moduleid']}"])));
    ovensia\ploopi\error::set_handler();

    return $arrSearchPattern;
}

function planning_setcookie($arrSearchPattern)
{
    setcookie("planning_request{$_SESSION['ploopi']['moduleid']}", base64_encode(gzcompress(serialize($arrSearchPattern), 9)));
}
?>
