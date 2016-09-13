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

    You should have received a copy of the GNU GeneralF Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Affichage frontoffice de l'historique des demandes
 *
 * @package booking
 * @subpackage wce
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

if ($_SESSION['ploopi']['connected'])
{
    $booking_resource_id = (empty($_POST['booking_resource_id']) || !is_numeric($_POST['booking_resource_id'])) ? '' : $_POST['booking_resource_id'];

    echo $skin->open_simplebloc('Suivi des demandes');

    // INIT PATTERN de recherche
    $arrSearchPattern = array();

    if (isset($_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['monitoring_request'])) $arrSearchPattern = $_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['monitoring_request'];

    if (isset($_REQUEST['booking_resource_id'])) $arrSearchPattern['booking_resource_id'] = $_REQUEST['booking_resource_id'];
    if (isset($_REQUEST['booking_event_managed'])) $arrSearchPattern['booking_event_managed'] = $_REQUEST['booking_event_managed'];
    if (isset($_REQUEST['booking_event_object'])) $arrSearchPattern['booking_event_object'] = $_REQUEST['booking_event_object'];
    if (isset($_REQUEST['booking_event_requestedby'])) $arrSearchPattern['booking_event_requestedby'] = $_REQUEST['booking_event_requestedby'];
    if (isset($_REQUEST['booking_event_from'])) $arrSearchPattern['booking_event_from'] = $_REQUEST['booking_event_from'];
    if (isset($_REQUEST['booking_event_to'])) $arrSearchPattern['booking_event_to'] = $_REQUEST['booking_event_to'];

    if (!isset($arrSearchPattern['booking_resource_id'])) $arrSearchPattern['booking_resource_id'] = '';
    if (!isset($arrSearchPattern['booking_event_managed'])) $arrSearchPattern['booking_event_managed'] = 0;
    if (!isset($arrSearchPattern['booking_event_object'])) $arrSearchPattern['booking_event_object'] = '';
    if (!isset($arrSearchPattern['booking_event_requestedby'])) $arrSearchPattern['booking_event_requestedby'] = '';
    if (!isset($arrSearchPattern['booking_event_from'])) $arrSearchPattern['booking_event_from'] = '';
    if (!isset($arrSearchPattern['booking_event_to'])) $arrSearchPattern['booking_event_to'] = '';

    $_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['monitoring_request'] = $arrSearchPattern;

    // Récupération de la liste des ressources
    $arrMenu = array();
    $arrResources = booking_get_resources(false, $booking_moduleid);
    $strResourceType = '';

    ?>
    <form id="booking_form_view" action="<?php echo ovensia\ploopi\crypt::urlencode("{$url}"); ?>" method="post">
    <p class="ploopi_va" style="padding:4px;">
        <label>Ressource :</label>
        <select name="booking_resource_id" id="booking_resource_id">
            <option value="">(choisir)</option>
            <?php
            foreach ($arrResources as $row)
            {
                if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
                {
                    if ($strResourceType != '') echo '</optgroup>';
                    $strResourceType = $row['rt_name'];
                    ?>
                    <optgroup label="<?php echo ovensia\ploopi\str::htmlentities($row['rt_name']); ?>">
                    <?php
                }
                ?>
                <option value="<?php echo $row['id']; ?>" <?php if ($arrSearchPattern['booking_resource_id'] == $row['id']) echo 'selected="selected"'; ?>  style="background-color:<?php echo ovensia\ploopi\str::htmlentities($row['color']); ?>;"><?php echo ovensia\ploopi\str::htmlentities($row['name'].(empty($row['reference']) ? '' : " ({$row['reference']})")); ?><?php echo $row['validator'] ? '&nbsp;-&nbsp;Validateur' : ''; ?></option>
                <?php
            }

            if ($strResourceType != '') echo '</optgroup>';
            ?>
        </select>

        <label style="margin-left:10px;">Traitée :</label>
        <select class="select" name="booking_event_managed">
            <option value="0" <?php if ($arrSearchPattern['booking_event_managed'] == 0) echo 'selected="selected"'; ?>>Non</option>
            <option value="1" <?php if ($arrSearchPattern['booking_event_managed'] == 1) echo 'selected="selected"'; ?>>Oui</option>
        </select>

        <label style="margin-left:10px;">Objet :</label>
        <input type="text" class="text" name="booking_event_object" value="<?php echo ovensia\ploopi\str::htmlentities($arrSearchPattern['booking_event_object']); ?>" style="width:200px;" />
    </p>
    <p class="ploopi_va" style="padding:4px;border-bottom:1px solid #c0c0c0;">
        <label>Demande effectuée entre le :</label>
        <input type="text" class="text" name="booking_event_from" id="booking_event_from" value="<?php echo ovensia\ploopi\str::htmlentities($arrSearchPattern['booking_event_from']); ?>" style="width:70px;" />
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('booking_event_from', event);"><img align="top" src="./img/calendar/calendar.gif" /></a>

        <label>et le :</label>
        <input type="text" class="text" name="booking_event_to" id="booking_event_to" value="<?php echo ovensia\ploopi\str::htmlentities($arrSearchPattern['booking_event_to']); ?>" style="width:70px;" />
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('booking_event_to', event);"><img align="top" src="./img/calendar/calendar.gif" /></a>

        <input type="submit" class="button" value="Filtrer" />
    </p>
    </form>

    <?php
    if (!empty($arrResources))
    {
        // Recherche des événements
        $arrEvents =
            booking_get_events(
                $arrSearchPattern['booking_resource_id'],
                true,
                true,
                '',
                $arrSearchPattern['booking_event_managed'],
                $arrSearchPattern['booking_event_object'],
                '',
                $arrSearchPattern['booking_event_from'],
                $arrSearchPattern['booking_event_to'],
                $booking_moduleid
            );

        // Regroupement des événements (on rassemble les détails)
        $arrRequests = array();
        foreach($arrEvents as $row)
        {
            if (empty($arrRequests[$row['id']])) $arrRequests[$row['id']] = $row;

            $arrRequests[$row['id']]['details'][] =
                array(
                    'timestp_begin' => $row['timestp_begin'],
                    'timestp_end' => $row['timestp_end']
                );
        }

        // Préparation du tableau d'affichage
        $arrResult =
            array(
                'columns' => array(),
                'rows' => array()
            );


        $arrResult['columns']['left']['resourcetype'] =
            array(
                'label' => 'Type',
                'width' => 100,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['left']['resource'] =
            array(
                'label' => 'Ressource',
                'width' => 100,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['auto']['object'] =
            array(
                'label' => 'Objet',
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['managed'] =
            array(
                'label' => 'Traitée',
                'width' => 70,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['timestp_request'] =
            array(
                'label' => 'Date demande',
                'width' => 120,
                'options' => array('sort' => true)
            );

        $arrResult['columns']['right']['datetime'] =
            array(
                'label' => 'Durée / Période',
                'width' => 130,
                'options' => array('sort' => true)
            );

        // Affectation des données dans le tableau
        foreach($arrRequests as $row)
        {
            $arrDateBegin = ovensia\ploopi\date::timestamp2local($row['timestp_begin']);
            $arrDateEnd = ovensia\ploopi\date::timestamp2local($row['timestp_end']);

            if ($arrDateBegin['date'] == $arrDateEnd['date']) // Un seul jour
                $strDateTime = sprintf("Le %s<br />de %s à %s", $arrDateBegin['date'], substr($arrDateBegin['time'], 0, 5), substr($arrDateEnd['time'], 0 ,5));
            else
                $strDateTime = sprintf("Du %s à %s<br />au %s à %s", $arrDateBegin['date'], substr($arrDateBegin['time'], 0, 5), $arrDateEnd['date'], substr($arrDateEnd['time'], 0, 5));


            if (!empty($row['periodicity']) && !empty($arrBookingPeriodicity[$row['periodicity']]))
            {
                $strDateTime .= "<br /><em>Périodicité : {$arrBookingPeriodicity[$row['periodicity']]}</em><br />Occurences : ".sizeof($row['details']);
            }

            $arrResult['rows'][] =
                array(
                    'values' =>
                        array(
                            'resourcetype' => array('label' => ovensia\ploopi\str::htmlentities($row['rt_name'])),
                            'resource' => array('label' => ovensia\ploopi\str::htmlentities($row['r_name'])),
                            'object' => array('label' => ovensia\ploopi\str::htmlentities($row['object'])),
                            'managed' =>
                                array(
                                    'label' => $row['managed'] ? 'Oui' : '<strong>Non</strong>',
                                    'sort_label' => $row['managed']
                                ),
                            'datetime' =>
                                array(
                                    'label' => $strDateTime,
                                    'sort_label' => $row['timestp_begin'].'_'.$row['timestp_end']
                                ),
                            'timestp_request' =>
                                array(
                                    'label' => ovensia\ploopi\str::htmlentities(current(ovensia\ploopi\date::timestamp2local($row['timestp_request']))),
                                    'sort_label' => $row['timestp_request']
                                )
                        ),
                    'description' => "Ouvrir la demande '".ovensia\ploopi\str::htmlentities($row['object'])."'",
                    'link' => 'javascript:void(0);',
                    'onclick' => "booking_front_element_open('event', '{$row['id']}', event, '{$booking_moduleid}');"
                );
        }

        // Affichage du tableau
        $skin->display_array(
            $arrResult['columns'],
            $arrResult['rows'],
            'booking_events',
            array(
                'sortable' => true,
                'orderby_default' => 'timestp_request',
                'sort_default' => 'ASC'
            )
        );
    }

    echo $skin->close_simplebloc();
}
else
{
    ?>
    Vous devez être connecté pour consulter l'historique des demandes
    <?php
}

?>
