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
 * Suivi des demandes
 *
 * @package booking
 * @subpackage public
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

$booking_resource_id = (empty($_POST['booking_resource_id']) || !is_numeric($_POST['booking_resource_id'])) ? '' : $_POST['booking_resource_id'];

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities("{$_SESSION['ploopi']['modulelabel']} - Gestion"));

echo ploopi\skin::get()->open_simplebloc('Suivi des demandes');

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
$arrResources = booking_get_resources();
$strResourceType = '';
?>
<form id="booking_form_view" action="<?php echo ploopi\crypt::urlencode("admin.php?booking_menu=monitoring"); ?>" method="post">
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
                <optgroup label="<?php echo ploopi\str::htmlentities($row['rt_name']); ?>">
                <?php
            }
            ?>
            <option value="<?php echo $row['id']; ?>" <?php if ($arrSearchPattern['booking_resource_id'] == $row['id']) echo 'selected="selected"'; ?>  style="background-color:<?php echo ploopi\str::htmlentities($row['color']); ?>;"><?php echo ploopi\str::htmlentities($row['name'].(empty($row['reference']) ? '' : " ({$row['reference']})")); ?><?php echo $row['validator'] ? '&nbsp;-&nbsp;Validateur' : ''; ?></option>
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
    <input type="text" class="text" name="booking_event_object" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['booking_event_object']); ?>" style="width:200px;" />
</p>
<p class="ploopi_va" style="padding:4px;border-bottom:1px solid #c0c0c0;">
    <label>Demandé par :</label>
    <input type="text" class="text" name="booking_event_requestedby" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['booking_event_requestedby']); ?>" style="width:150px;" />

    <label style="margin-left:10px;">Entre le :</label>
    <input type="text" class="text" name="booking_event_from" id="booking_event_from" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['booking_event_from']); ?>" style="width:70px;" />
    <?php ploopi\date::open_calendar('booking_event_from'); ?>

    <label>et le :</label>
    <input type="text" class="text" name="booking_event_to" id="booking_event_to" value="<?php echo ploopi\str::htmlentities($arrSearchPattern['booking_event_to']); ?>" style="width:70px;" />
    <?php ploopi\date::open_calendar('booking_event_to'); ?>

    <input type="submit" class="button" value="Filtrer" />
    <input type="submit" class="button" name="xls" value="Extraction XLS" />
</p>
</form>

<?php
if (!empty($arrResources))
{
    // Recherche des événements

    $arrEvents =
        booking_get_events(
            $booking_resource_id,
            true,
            true,
            '',
            $arrSearchPattern['booking_event_managed'],
            $arrSearchPattern['booking_event_object'],
            $arrSearchPattern['booking_event_requestedby'],
            $arrSearchPattern['booking_event_from'],
            $arrSearchPattern['booking_event_to']
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


    if (isset($_REQUEST['xls'])) {

        // Préparation des données pour l'export XLS
        $arrData = array();
        foreach($arrRequests as $row) {

            $arrDateBegin = ploopi\date::timestamp2local($row['timestp_begin']);
            $arrDateEnd = ploopi\date::timestamp2local($row['timestp_end']);

            if ($arrDateBegin['date'] == $arrDateEnd['date']) // Un seul jour
                $strDateTime = sprintf("Le %s\r\nde %s à %s", $arrDateBegin['date'], substr($arrDateBegin['time'], 0, 5), substr($arrDateEnd['time'], 0 ,5));
            else
                $strDateTime = sprintf("Du %s à %s\r\nau %s à %s", $arrDateBegin['date'], substr($arrDateBegin['time'], 0, 5), $arrDateEnd['date'], substr($arrDateEnd['time'], 0, 5));


            if (!empty($row['periodicity']) && !empty($arrBookingPeriodicity[$row['periodicity']]))
            {
                $strDateTime .= "\r\nPériodicité : {$arrBookingPeriodicity[$row['periodicity']]}\r\nOccurences : ".sizeof($row['details']);
            }

            $arrData[] = array(
                'resourcetype' => $row['rt_name'],
                'resource' => $row['r_name'],
                'subresources' => $row['subresources'],
                'object' => $row['object'],
                'datetime' => $strDateTime,
                'user' => trim("{$row['u_lastname']} {$row['u_firstname']}"),
                'workspace' => $row['w_label'],
                'timestp_request' => current(ploopi\date::timestamp2local($row['timestp_request'])),
                'managed' => $row['managed'] ? 'Oui' : 'Non',
            );
        }

        include_once './include/functions/array.php';

        ploopi\buffer::clean();

        ploopi\arr::toxls(
            $arrData,
            true,
            'booking_suivi_demandes.xls',
            'pieces',
            array(
                'resourcetype' => array('width' => 20, 'title' => 'Type'),
                'resource' => array('width' => 20, 'title' => 'Ressource'),
                'subresources' => array('width' => 25, 'title' => 'Sous-ressources'),
                'object' => array('width' => 40, 'title' => 'Objet'),
                'datetime' => array('width' => 40, 'title' => 'Durée / Période'),
                'user' => array('width' => 25, 'title' => 'Demandé par'),
                'workspace' => array('width' => 25, 'title' => 'Espace'),
                'timestp_request' => array('width' => 20, 'title' => 'Date demande'),
                'managed' => array('width' => 10, 'title' => 'Traité'),
            )
        );


        ploopi\system::kill();

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
            'width' => 120,
            'options' => array('sort' => true)
        );

    $arrResult['columns']['left']['resource'] =
        array(
            'label' => 'Ressource',
            'width' => 180,
            'options' => array('sort' => true)
        );

    $arrResult['columns']['left']['subresources'] =
        array(
            'label' => 'Sous-Ressources',
            'width' => 180,
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
            'width' => 80,
            'options' => array('sort' => true)
        );

    $arrResult['columns']['right']['timestp_request'] =
        array(
            'label' => 'Date demande',
            'width' => 130,
            'options' => array('sort' => true)
        );

    $arrResult['columns']['right']['user'] =
        array(
            'label' => 'Demandé par',
            'width' => 150,
            'options' => array('sort' => true)
        );

    $arrResult['columns']['right']['datetime'] =
        array(
            'label' => 'Durée / Période',
            'width' => 200,
            'options' => array('sort' => true)
        );

    // Affectation des données dans le tableau
    foreach($arrRequests as $row)
    {
        $arrDateBegin = ploopi\date::timestamp2local($row['timestp_begin']);
        $arrDateEnd = ploopi\date::timestamp2local($row['timestp_end']);

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
                        'resourcetype' => array('label' => ploopi\str::htmlentities($row['rt_name'])),
                        'resource' => array('label' => ploopi\str::htmlentities($row['r_name'])),
                        'subresources' => array('label' => ploopi\str::htmlentities($row['subresources'])),
                        'object' => array('label' => ploopi\str::htmlentities($row['object'])),
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
                                'label' => ploopi\str::htmlentities(current(ploopi\date::timestamp2local($row['timestp_request']))),
                                'sort_label' => $row['timestp_request']
                            ),
                        'user' => array('label' => ploopi\str::htmlentities("{$row['u_lastname']} {$row['u_firstname']}")." <br /><em>de ".ploopi\str::htmlentities($row['w_label'])."</em>")
                    ),
                'description' => "Ouvrir la demande '".ploopi\str::htmlentities($row['object'])."'",
                'link' => 'javascript:void(0);',
                'onclick' => "booking_element_open('event', '{$row['id']}', event);"
            );
    }

    // Affichage du tableau
    ploopi\skin::get()->display_array(
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

echo ploopi\skin::get()->close_simplebloc();
?>
