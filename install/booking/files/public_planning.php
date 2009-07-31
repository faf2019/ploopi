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
 * Affichage du planning
 *
 * @package booking
 * @subpackage public
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

include_once './include/classes/calendar.php';

// INIT PATTERN de recherche
$arrSearchPattern = array();

if (isset($_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['booking_request'])) $arrSearchPattern = $_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['booking_request'];

// Lecture des paramètres
if (isset($_REQUEST['booking_resource_id'])) $arrSearchPattern['booking_resource_id'] = $_REQUEST['booking_resource_id'];
if (isset($_REQUEST['booking_display_type'])) $arrSearchPattern['booking_display_type'] = $_REQUEST['booking_display_type'];
if (isset($_REQUEST['booking_validated'])) $arrSearchPattern['booking_validated'] = $_REQUEST['booking_validated'];
if (isset($_REQUEST['booking_size'])) $arrSearchPattern['booking_size'] = $_REQUEST['booking_size'];

if (isset($_REQUEST['booking_month'])) $arrSearchPattern['booking_month'] = $_REQUEST['booking_month'];
if (isset($_REQUEST['booking_year'])) $arrSearchPattern['booking_year'] = $_REQUEST['booking_year'];
if (isset($_REQUEST['booking_week'])) $arrSearchPattern['booking_week'] = $_REQUEST['booking_week'];
if (isset($_REQUEST['booking_day'])) $arrSearchPattern['booking_day'] = $_REQUEST['booking_day'];


// booléen à true si la date est modifiée par l'utilisateur (mois, année, jour ou semaine)
$booDateModify = isset($_REQUEST['booking_month']) || isset($_REQUEST['booking_year']) || isset($_REQUEST['booking_week']) || isset($_REQUEST['booking_day']);

//ploopi_print_r($_REQUEST);

// Init des valeurs par défaut
if (!isset($arrSearchPattern['booking_display_type'])) $arrSearchPattern['booking_display_type'] = 'month';
if (!isset($arrSearchPattern['booking_size'])) $arrSearchPattern['booking_size'] = $arrBookingSize[0];
if (!isset($arrSearchPattern['booking_resource_id'])) $arrSearchPattern['booking_resource_id'] = '';
if (!isset($arrSearchPattern['booking_validated'])) $arrSearchPattern['booking_validated'] = '';

// Init de la date "virtuelle"
if (!isset($arrSearchPattern['booking_virtualdate'])) $arrSearchPattern['booking_virtualdate'] = time();

    
if ($booDateModify) // modification de la date de visualisation
{
    // Traitement du cas particulier de changement d'année (en remontant en arrière) qui implique la recherche de la dernière semaine de l'année précédente (52 ou 53 ?)
    if (!empty($_POST['booking_week_previousyear'])) $arrSearchPattern['booking_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['booking_year']));
    
    // Traitement du cas particulier de changement de mois (en remontant en arrière) qui implique la recherche du dernier jour du mois précédent (28, 29, 30, 31 ?)
    if (!empty($_POST['booking_week_previousmonth'])) $arrSearchPattern['booking_day'] = date('t', mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year']));
    
    // Contrôle de la validité de numéro de semaine (cas ou l'on remonte d'une année et que la semaine sélectionnée est 53)
    if ($arrSearchPattern['booking_week'] > 52) $arrSearchPattern['booking_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['booking_year']));
    
    // Contrôle de la validité de numéro de jour (cas ou l'on remonte d'un mois et que le jour sélectionné est > 28)
    if ($arrSearchPattern['booking_day'] > 28)
    {
        $intMax = date('t', mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year']));
        if ($arrSearchPattern['booking_day'] > $intMax) $arrSearchPattern['booking_day'] = $intMax;
    }
    
    // calcul de la nouvelle date virtuelle en fonction du type d'affichage
    switch ($arrSearchPattern['booking_display_type'])
    {
        case 'day':
            $arrSearchPattern['booking_virtualdate'] = mktime(0, 0, 0, $arrSearchPattern['booking_month'], $arrSearchPattern['booking_day'], $arrSearchPattern['booking_year']);
        break;
            
        case 'month':
            $arrSearchPattern['booking_virtualdate'] = mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year']);
        break;
        
        case 'week':
            $arrSearchPattern['booking_virtualdate'] = ploopi_numweek2unixtimestamp($arrSearchPattern['booking_week'], $arrSearchPattern['booking_year']);
        break;
    
    }    
    
}

switch ($arrSearchPattern['booking_display_type'])
{
    // modification de la date virtuelle si on choisi "aujourd'hui"
    case 'today':
        $arrSearchPattern['booking_virtualdate'] = time();
    break;
    
    case 'week':
        $arrSearchPattern['booking_week'] = date('W', $arrSearchPattern['booking_virtualdate']);
    break;

}

$arrSearchPattern['booking_month'] =  date('n', $arrSearchPattern['booking_virtualdate']);
$arrSearchPattern['booking_year'] = date('Y', $arrSearchPattern['booking_virtualdate']);
$arrSearchPattern['booking_day'] = date('j', $arrSearchPattern['booking_virtualdate']);





$_SESSION['booking'][$_SESSION['ploopi']['moduleid']]['booking_request'] = $arrSearchPattern;

$arrMenu = array();
$arrResources = booking_get_resources();
$strResourceType = '';


$arrMenu[''] = 
    array(
        'label' => '(choisir)',
        'type'  => 'select'
    );


foreach ($arrResources as $row)
{
    
    if ($row['rt_name'] != $strResourceType) // nouveau type de ressource => affichage séparateur
    {
        $arrMenu[$row['rt_name']] = 
            array(
                'label' => $row['rt_name'],
                'type'  => 'group',
            );
        
        $strResourceType = $row['rt_name']; 
    }
        
    $arrMenu[$row['id']] = 
        array(
            'label' => htmlentities($row['name'].(empty($row['reference']) ? '' : " ({$row['reference']})")),
            'label_extended' => $row['validator'] ? '<span>&nbsp;-&nbsp;</span><em><strong>Validateur</strong></em>' : '',
            'type'  => 'select',
            'style' => "background-color:{$row['color']};"
        );
}

$arrSize = explode('x', $arrSearchPattern['booking_size']);

/**
 * Détermination des dates de début et fin de la période affichée
 */
switch($arrSearchPattern['booking_display_type'])
{
    case 'month':
        $date_begin = mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year']);
        $date_end = mktime(0, 0, 0, $arrSearchPattern['booking_month']+1, 0, $arrSearchPattern['booking_year']);
    break;

    case 'week':
        // On détermine les dates de la semaine courante
        $date_begin = ploopi_numweek2unixtimestamp($arrSearchPattern['booking_week'], $arrSearchPattern['booking_year']);
        $date_end = mktime(0, 0, 0, date('n', $date_begin), date('j', $date_begin)+6, date('Y', $date_begin));
    break;

    default:
    case 'today':
    case 'day':
        // On détermine la date du jour
        $date_end = $date_begin = mktime(0, 0, 0, $arrSearchPattern['booking_month'], $arrSearchPattern['booking_day'], $arrSearchPattern['booking_year']);
    break;

}
?>

<div style="padding:4px;">
    <p class="ploopi_va" style="padding:2px;float:left;">
        <label>Affichage :</label>
        <input type="image" alt="Aujourd'hui" src="./modules/booking/img/ico_today<? if ($arrSearchPattern['booking_display_type'] != 'today') echo'_notsel'; ?>.png" title="Aujourd'hui" onclick="javascript:ploopi_xmlhttprequest_todiv('index-light.php', '<? echo ploopi_queryencode('ploopi_op=booking_refresh&booking_display_type=today'); ?>', 'booking_main');" />
        <input type="image" alt="Quotidien" src="./modules/booking/img/ico_day<? if ($arrSearchPattern['booking_display_type'] != 'day') echo'_notsel'; ?>.png" title="Journée" onclick="javascript:ploopi_xmlhttprequest_todiv('index-light.php', '<? echo ploopi_queryencode('ploopi_op=booking_refresh&booking_display_type=day'); ?>', 'booking_main');" />
        <input type="image" alt="Hebdomadaire" src="./modules/booking/img/ico_week<? if ($arrSearchPattern['booking_display_type'] != 'week') echo'_notsel'; ?>.png" title="Semaine" onclick="javascript:ploopi_xmlhttprequest_todiv('index-light.php', '<? echo ploopi_queryencode('ploopi_op=booking_refresh&booking_display_type=week'); ?>', 'booking_main');" />
        <input type="image" alt="Mensuel" src="./modules/booking/img/ico_month<? if ($arrSearchPattern['booking_display_type'] != 'month') echo'_notsel'; ?>.png" title="Mois" onclick="javascript:ploopi_xmlhttprequest_todiv('index-light.php', '<? echo ploopi_queryencode('ploopi_op=booking_refresh&booking_display_type=month'); ?>', 'booking_main');" />
        
        <label>Taille :</label>
        <select class="select" name="booking_size" id="booking_size" onchange="javascript:ploopi_xmlhttprequest_todiv('<? echo ploopi_urlencode('index-light.php?ploopi_op=booking_refresh'); ?>', 'booking_size='+this.value, 'booking_main');">
        <?
        foreach($arrBookingSize as $strSize)
        {
            ?><option value="<? echo $strSize; ?>" <? if ($arrSearchPattern['booking_size'] == $strSize) echo 'selected="selected"'; ?>><? echo $strSize; ?></option><?
        }
        ?>
        </select>
    </p>
    
    <form style="float:left;" id="booking_form_view" action="<? echo ploopi_urlencode('index-light.php?ploopi_op=booking_refresh'); ?>" method="post" onsubmit="ploopi_xmlhttprequest_submitform(this, 'booking_main');return false;">
    <p class="ploopi_va" style="padding:2px;float:left;">
        <label>Période :</label>
        <?
        switch($arrSearchPattern['booking_display_type'])
        {
            case 'today':
            case 'day':
                ?>
                <select class="select" name="booking_day" id="booking_day" onchange="javascript:if ($('booking_form_view').onsubmit()) $('booking_form_view').submit();">
                <?
                for ($intDay = 1; $intDay <= date('t', mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year'])); $intDay++)
                {
                    ?>
                    <option value="<? echo $intDay; ?>" <? if ($arrSearchPattern['booking_day'] == $intDay) echo 'selected="selected";' ?>><? echo $intDay; ?></option>
                    <?
                }
                ?>
                </select>
                <?
    
            case 'month':
                ?>
                <select class="select" name="booking_month" id="booking_month" onchange="javascript:if ($('booking_form_view').onsubmit()) $('booking_form_view').submit();">
                <?
                foreach ($ploopi_months as $intMonth => $strMonth)
                {
                    ?>
                    <option value="<? echo $intMonth; ?>" <? if ($arrSearchPattern['booking_month'] == $intMonth) echo 'selected="selected";' ?>><? echo $strMonth; ?></option>
                    <?
                }
                ?>
                </select>
                <?
            break;
    
            case 'week':
                ?>
                <select class="select" name="booking_week" id="booking_week" onchange="javascript:if ($('booking_form_view').onsubmit()) $('booking_form_view').submit();">
                <?
                // Détermination du numéro de semaine max de l'année (on se positionne sur le 31/12)
                $intMaxWeek = date('W', mktime(0, 0, 0, 12, 31, $arrSearchPattern['booking_year']));
                if ($intMaxWeek == 1) $intMaxWeek = 52;
    
                $date_firstweek = ploopi_numweek2unixtimestamp(1, $arrSearchPattern['booking_year']);
                for ($intWeek = 1; $intWeek <= $intMaxWeek; $intWeek++)
                {
                    // Date de début de la semaine en cours d'affichage dans la liste
                    $date_week = mktime(0, 0, 0, date('n', $date_firstweek), date('j', $date_firstweek)+(($intWeek - 1) * 7), date('Y', $date_firstweek));
                    //$date_week = mktime(0, 0, 0, 12, 29 + $d + (($intWeek - 1) * 7), $intSelYear - 1);
                    ?>
                    <option value="<? echo $intWeek; ?>" <? if ($arrSearchPattern['booking_week'] == $intWeek) echo 'selected="selected";' ?>><? printf("Semaine %02d - %s", $intWeek, substr(ploopi_unixtimestamp2local($date_week),0,5)); ?></option>
                    <?
                }
                ?>
                </select>
                <?
            break;
        }
        ?>
    
        <select class="select" name="booking_year" id="booking_year" onchange="javascript:if ($('booking_form_view').onsubmit()) $('booking_form_view').submit();">
        <?
        for ($intY = $arrSearchPattern['booking_year']-5; $intY <= $arrSearchPattern['booking_year']+5; $intY++)
        {
            ?>
            <option value="<? echo $intY; ?>" <? if ($arrSearchPattern['booking_year'] == $intY) echo 'selected="selected";' ?>><? echo $intY; ?></option>
            <?
        }
        ?>
        </select>
    
        <?
        switch($arrSearchPattern['booking_display_type'])
        {
            case 'month':
                ?>
                <input type="button" class="button" value="&laquo;&laquo;" title="Mois précédent" onclick="javascript:booking_prevmonth();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Mois suivant" onclick="javascript:booking_nextmonth();" />
                <?
            break;
    
            case 'week':
                ?>
                <input type="hidden" name="booking_week_previousyear" id="booking_week_previousyear" value="0" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Semaine précédente" onclick="javascript:booking_prevweek();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Semaine suivante" onclick="javascript:booking_nextweek();" />
                <?
            break;
    
            case 'today':
            case 'day':
                ?>
                <input type="hidden" name="booking_week_previousmonth" id="booking_week_previousmonth" value="0" />
                <input type="hidden" name="booking_display_type" value="day" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Jour précédent" onclick="javascript:booking_prevday();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Jour suivant" onclick="javascript:booking_nextday();" />
                <?
            break;
    
        }
        ?>
        </p>
    </form>

    <?
    $arrOptions = 
        array(
            'input_width' => '300px',
            'menu_width' => '500px',
            'onchange' => "$('booking_form_res').onsubmit();"
        );
    ?>    
    <form id="booking_form_res" action="<? echo ploopi_urlencode('index-light.php?ploopi_op=booking_refresh'); ?>" method="post" onsubmit="ploopi_xmlhttprequest_submitform(this, 'booking_main');return false;">
    <p class="ploopi_va" style="clear:both;padding:2px;">
        <label>Ressource à afficher :</label>
        <span style="padding:0 2px;"><? echo $skin->display_selectbox('booking_resource_id', 'booking_resource_id', $arrMenu, $arrOptions, $arrSearchPattern['booking_resource_id']); ?></span>
        <?
        $date_today = mktime();
        
        if ($date_today >= $date_begin && $date_today <= $date_end) $date_sel = $date_today;
        else $date_sel = $date_begin;
        
        if (ploopi_isactionallowed(_BOOKING_ACTION_ASKFOREVENT))
        {
            ?><input type="button" class="button" value="Effectuer une demande" style="margin:0 10px;" onclick="javascript:booking_event_add(event, '<? echo $date_sel; ?>');" /><?
        }
        ?>
    </p>   
    </form> 
    
</div>
<?
// Choix d'une ressource => on peut chercher les événements associés
if (!empty($arrSearchPattern['booking_resource_id']) && sizeof($arrSize) == 2 && is_numeric($arrSize[0]) && is_numeric($arrSize[1]))
{
    switch($arrSearchPattern['booking_display_type'])
    {
        case 'month':
            $objCalendar = new calendar($arrSize[0], $arrSize[1], 'month');
    
            $objCalendar->setoptions(
                array(
                    'month' => $arrSearchPattern['booking_month'],
                    'year' => $arrSearchPattern['booking_year'],
                )
            );
        break;
    
        case 'week':
            $objCalendar = new calendar($arrSize[0], $arrSize[1], 'days');
    
            $objCalendar->setoptions(
                array(
                    'date_begin' => substr(ploopi_unixtimestamp2timestamp($date_begin), 0, 8),
                    'date_end' => substr(ploopi_unixtimestamp2timestamp($date_end), 0, 8)
                )
            );
        break;
    
        default:
        case 'day':
            $objCalendar = new calendar($arrSize[0], $arrSize[1], 'days');
    
            $objCalendar->setoptions(
                array(
                    'date_begin' => substr(ploopi_unixtimestamp2timestamp($date_begin), 0, 8),
                    'date_end' => substr(ploopi_unixtimestamp2timestamp($date_end), 0, 8)
                )
            );
        break;
    }
    
    $objCalendar->setoptions(
        array(
            'hour_begin' => 6,
            'hour_end' => 21
        )
    );
    
    // Recherche des événements
    $arrEvents = 
        booking_get_events(
            $arrSearchPattern['booking_resource_id'], 
            false, 
            false,
            $arrSearchPattern['booking_validated']
        );
    
    // Affectation de la liste des événements au calendrier
    foreach($arrEvents as $event)
    {
        $objCalendar->addevent(
            new calendarEvent(
                $event['timestp_begin'],
                $event['timestp_end'],
                htmlentities($event['object']),
                '<div style="float:right;margin:2px;background-color:'.($event['validated'] ? $arrBookingColor['validated'] : ($event['canceled'] ? $arrBookingColor['canceled'] : $arrBookingColor['unknown'])).';border:1px solid #000;width:10px;height:8px;"></div><div style="margin:2px;"><strong style="margin-right:2px;"><timestp_begin></strong>'.htmlentities(ploopi_strcut($event['object'],20)).'</div>',
                $event['color'],
                "booking_element_open('event', '{$event['id']},{$event['ed_id']}', event);", // onclick
                'javascript:void(0);'
            )
        );
    }
    ?>
    <div style="width:100%;overflow:auto;clear:both;" id="planning_display">
        <?
        $objCalendar->display(); 
        ?>
    </div>
    <?
}
?>