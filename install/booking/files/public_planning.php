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

// INIT Nombre de colonnes
$channelsCount = 0;

// INIT PATTERN de recherche
$arrSearchPattern = array();

// Lecture cookie
if (isset($_COOKIE["booking_request{$_SESSION['ploopi']['moduleid']}"])) {
    ploopi\error::unset_handler();
    $arrSearchPattern = @unserialize(@gzuncompress(@base64_decode($_COOKIE["booking_request{$_SESSION['ploopi']['moduleid']}"])));
    ploopi\error::set_handler();
} else {
    // Si pas de cookie alors on peut charger la valeur par défaut dans les paramètres (pré-cochage ou non)
    if (ploopi\param::get('booking_default_all_checked')) {
        $arrRsc = booking_get_resources();
        foreach($arrRsc as $key) { $arrSearchPattern['booking_resources'][$key['id']] = $key['id'];}
    }
}

// Lecture des paramètres
if (isset($_REQUEST['booking_display_type'])) $arrSearchPattern['booking_display_type'] = $_REQUEST['booking_display_type'];
if (isset($_REQUEST['booking_validated'])) $arrSearchPattern['booking_validated'] = $_REQUEST['booking_validated'];
if (isset($_REQUEST['booking_size'])) $arrSearchPattern['booking_size'] = $_REQUEST['booking_size'];
if (isset($_REQUEST['booking_channels'])) $arrSearchPattern['booking_channels'] = $_REQUEST['booking_channels'];

if (isset($_REQUEST['booking_resources'])) $arrSearchPattern['booking_resources'] = $_REQUEST['booking_resources'];

if (isset($_REQUEST['booking_month'])) $arrSearchPattern['booking_month'] = $_REQUEST['booking_month'];
if (isset($_REQUEST['booking_year'])) $arrSearchPattern['booking_year'] = $_REQUEST['booking_year'];
if (isset($_REQUEST['booking_week'])) $arrSearchPattern['booking_week'] = $_REQUEST['booking_week'];
if (isset($_REQUEST['booking_day'])) $arrSearchPattern['booking_day'] = $_REQUEST['booking_day'];

// booléen à true si la date est modifiée par l'utilisateur (mois, année, jour ou semaine)
$booDateModify = isset($_REQUEST['booking_month']) || isset($_REQUEST['booking_year']) || isset($_REQUEST['booking_week']) || isset($_REQUEST['booking_day']);

// Init des valeurs par défaut
$param_display_type = ploopi\param::get('booking_default_display_type');
if (!isset($arrSearchPattern['booking_display_type'])) $arrSearchPattern['booking_display_type'] = empty( $param_display_type) ? 'month' : $param_display_type;
if (!isset($arrSearchPattern['booking_size'])) $arrSearchPattern['booking_size'] = $arrBookingSize[0];
if (!isset($arrSearchPattern['booking_resources'])) $arrSearchPattern['booking_resources'] = array();
if (!isset($arrSearchPattern['booking_validated'])) $arrSearchPattern['booking_validated'] = '';
$param_booking_channels = ploopi\param::get('booking_default_channels');
if (!isset($arrSearchPattern['booking_channels'])) $arrSearchPattern['booking_channels'] = empty($param_booking_channels) ? 1 : $param_booking_channels;

// Init de la date "virtuelle"
if (!isset($arrSearchPattern['booking_virtualdate'])) $arrSearchPattern['booking_virtualdate'] = time();

if ($booDateModify) // modification de la date de visualisation
{
    // Traitement du cas particulier de changement d'année (en remontant en arrière) qui implique la recherche de la dernière semaine de l'année précédente (52 ou 53 ?)
    if (!empty($_POST['booking_week_previousyear'])) $arrSearchPattern['booking_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['booking_year']));

    // Traitement du cas particulier de changement de mois (en remontant en arrière) qui implique la recherche du dernier jour du mois précédent (28, 29, 30, 31 ?)
    if (!empty($_POST['booking_week_previousmonth'])) $arrSearchPattern['booking_day'] = date('t', mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year']));

    // Contrôle de la validité de numéro de semaine (cas ou l'on remonte d'une année et que la semaine sélectionnée est 53)
    if (isset($arrSearchPattern['booking_week']) && $arrSearchPattern['booking_week'] > 52) $arrSearchPattern['booking_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['booking_year']));

    // Contrôle de la validité de numéro de jour (cas ou l'on remonte d'un mois et que le jour sélectionné est > 28)
    if (isset($arrSearchPattern['booking_day']) && $arrSearchPattern['booking_day'] > 28)
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
            $arrSearchPattern['booking_virtualdate'] = ploopi\date::numweek2unixtimestamp($arrSearchPattern['booking_week'], $arrSearchPattern['booking_year']);
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

// Sauvegarde cookie
setcookie("booking_request{$_SESSION['ploopi']['moduleid']}", $str = base64_encode(gzcompress(serialize($arrSearchPattern), 9)), time()+86400*30, dirname($_SERVER['PHP_SELF']));

// Lecture des ressources
$arrResources = booking_get_resources();

$arrSize = explode('x', $arrSearchPattern['booking_size']);

if (sizeof($arrSize) != 2 || !is_numeric($arrSize[0]) || !is_numeric($arrSize[1])) $arrSize = array(800, 500);

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
        $date_begin = ploopi\date::numweek2unixtimestamp($arrSearchPattern['booking_week'], $arrSearchPattern['booking_year']);
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
        <input type="image" alt="Aujourd'hui" src="./modules/booking/img/ico_today<?php if ($arrSearchPattern['booking_display_type'] != 'today') echo'_notsel'; ?>.png" title="Aujourd'hui" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=booking_refresh&booking_display_type=today'); ?>', 'booking_main');" />
        <input type="image" alt="Quotidien" src="./modules/booking/img/ico_day<?php if ($arrSearchPattern['booking_display_type'] != 'day') echo'_notsel'; ?>.png" title="Journée" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=booking_refresh&booking_display_type=day'); ?>', 'booking_main');" />
        <input type="image" alt="Hebdomadaire" src="./modules/booking/img/ico_week<?php if ($arrSearchPattern['booking_display_type'] != 'week') echo'_notsel'; ?>.png" title="Semaine" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=booking_refresh&booking_display_type=week'); ?>', 'booking_main');" />
        <input type="image" alt="Mensuel" src="./modules/booking/img/ico_month<?php if ($arrSearchPattern['booking_display_type'] != 'month') echo'_notsel'; ?>.png" title="Mois" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=booking_refresh&booking_display_type=month'); ?>', 'booking_main');" />

        <label for="booking_channels">Multi Col:</label>
        <input type="checkbox" name="booking_channels" id="booking_channels" <?php if ($arrSearchPattern['booking_channels']) echo 'checked="checked"'; ?> onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=booking_refresh&booking_channels='.($arrSearchPattern['booking_channels'] ? 0 : 1)); ?>', 'booking_main');"/>

        <label>Taille :</label>
        <select class="select" name="booking_size" id="booking_size" onchange="javascript:ploopi.xhr.todiv('<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=booking_refresh'); ?>', 'booking_size='+this.value, 'booking_main');">
        <?php
        foreach($arrBookingSize as $strSize)
        {
            ?><option value="<?php echo $strSize; ?>" <?php if ($arrSearchPattern['booking_size'] == $strSize) echo 'selected="selected"'; ?>><?php echo $strSize; ?></option><?php
        }
        ?>
        </select>
    </p>

    <form style="float:left;" id="booking_form_view" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=booking_refresh'); ?>" method="post" onsubmit="javascript:ploopi.xhr.submit(this, 'booking_main');return false;">
    <p class="ploopi_va" style="padding:2px;float:left;">
        <label>Période :</label>
        <?php
        switch($arrSearchPattern['booking_display_type'])
        {
            case 'today':
            case 'day':
                ?>
                <select class="select" name="booking_day" id="booking_day" onchange="javascript:if (jQuery('#booking_form_view')[0].onsubmit()) jQuery('#booking_form_view')[0].submit();">
                <?php
                for ($intDay = 1; $intDay <= date('t', mktime(0, 0, 0, $arrSearchPattern['booking_month'], 1, $arrSearchPattern['booking_year'])); $intDay++)
                {
                    ?>
                    <option value="<?php echo $intDay; ?>" <?php if ($arrSearchPattern['booking_day'] == $intDay) echo 'selected="selected";' ?>><?php echo $intDay; ?></option>
                    <?php
                }
                ?>
                </select>
                <?php

            case 'month':
                ?>
                <select class="select" name="booking_month" id="booking_month" onchange="javascript:if (jQuery('#booking_form_view')[0].onsubmit()) jQuery('#booking_form_view')[0].submit();">
                <?php
                foreach ($ploopi_months as $intMonth => $strMonth)
                {
                    ?>
                    <option value="<?php echo $intMonth; ?>" <?php if ($arrSearchPattern['booking_month'] == $intMonth) echo 'selected="selected";' ?>><?php echo $strMonth; ?></option>
                    <?php
                }
                ?>
                </select>
                <?php
            break;

            case 'week':
                ?>
                <select class="select" name="booking_week" id="booking_week" onchange="javascript:if (jQuery('#booking_form_view')[0].onsubmit()) jQuery('#booking_form_view')[0].submit();">
                <?php
                // Détermination du numéro de semaine max de l'année (on se positionne sur le 31/12)
                $intMaxWeek = date('W', mktime(0, 0, 0, 12, 31, $arrSearchPattern['booking_year']));
                if ($intMaxWeek == 1) $intMaxWeek = 52;

                $date_firstweek = ploopi\date::numweek2unixtimestamp(1, $arrSearchPattern['booking_year']);
                for ($intWeek = 1; $intWeek <= $intMaxWeek; $intWeek++)
                {
                    // Date de début de la semaine en cours d'affichage dans la liste
                    $date_week = mktime(0, 0, 0, date('n', $date_firstweek), date('j', $date_firstweek)+(($intWeek - 1) * 7), date('Y', $date_firstweek));
                    //$date_week = mktime(0, 0, 0, 12, 29 + $d + (($intWeek - 1) * 7), $intSelYear - 1);
                    ?>
                    <option value="<?php echo $intWeek; ?>" <?php if ($arrSearchPattern['booking_week'] == $intWeek) echo 'selected="selected";' ?>><?php printf("Semaine %02d - %s", $intWeek, substr(ploopi\date::unixtimestamp2local($date_week),0,5)); ?></option>
                    <?php
                }
                ?>
                </select>
                <?php
            break;
        }
        ?>

        <select class="select" name="booking_year" id="booking_year" onchange="javascript:if (jQuery('#booking_form_view')[0].onsubmit()) jQuery('#booking_form_view')[0].submit();">
        <?php
        for ($intY = $arrSearchPattern['booking_year']-5; $intY <= $arrSearchPattern['booking_year']+5; $intY++)
        {
            ?>
            <option value="<?php echo $intY; ?>" <?php if ($arrSearchPattern['booking_year'] == $intY) echo 'selected="selected";' ?>><?php echo $intY; ?></option>
            <?php
        }
        ?>
        </select>

        <?php
        switch($arrSearchPattern['booking_display_type'])
        {
            case 'month':
                ?>
                <input type="button" class="button" value="&laquo;&laquo;" title="Mois précédent" onclick="javascript:booking_prevmonth();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Mois suivant" onclick="javascript:booking_nextmonth();" />
                <?php
            break;

            case 'week':
                ?>
                <input type="hidden" name="booking_week_previousyear" id="booking_week_previousyear" value="0" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Semaine précédente" onclick="javascript:booking_prevweek();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Semaine suivante" onclick="javascript:booking_nextweek();" />
                <?php
            break;

            case 'today':
            case 'day':
                ?>
                <input type="hidden" name="booking_week_previousmonth" id="booking_week_previousmonth" value="0" />
                <input type="hidden" name="booking_display_type" value="day" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Jour précédent" onclick="javascript:booking_prevday();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Jour suivant" onclick="javascript:booking_nextday();" />
                <?php
            break;

        }

        $date_today = time();

        if ($date_today >= $date_begin && $date_today <= $date_end) $date_sel = $date_today;
        else $date_sel = $date_begin;

        if (ploopi\acl::isactionallowed(_BOOKING_ACTION_ASKFOREVENT))
        {
            ?><input type="button" class="button" value="Réserver" style="font-weight:bold;margin:0 6px;" onclick="javascript:booking_event_add(event, '<?php echo $date_sel; ?>');" /><?php
        }

        ?>
        </p>
    </form>
</div>
<?php
switch($arrSearchPattern['booking_display_type'])
{
    case 'month':
        $objCalendar = new ploopi\calendar($arrSize[0], $arrSize[1], 'month');

        $objCalendar->setoptions(
            array(
                'intMonth' => $arrSearchPattern['booking_month'],
                'intYear' => $arrSearchPattern['booking_year'],
            )
        );
    break;

    case 'week':
        $objCalendar = new ploopi\calendar($arrSize[0], $arrSize[1], 'days');

        $objCalendar->setoptions(
            array(
                'strDateBegin' => substr(ploopi\date::unixtimestamp2timestamp($date_begin), 0, 8),
                'strDateEnd' => substr(ploopi\date::unixtimestamp2timestamp($date_end), 0, 8)
            )
        );
    break;

    default:
    case 'day':
        $objCalendar = new ploopi\calendar($arrSize[0], $arrSize[1], 'days');

        $objCalendar->setoptions(
            array(
                'strDateBegin' => substr(ploopi\date::unixtimestamp2timestamp($date_begin), 0, 8),
                'strDateEnd' => substr(ploopi\date::unixtimestamp2timestamp($date_end), 0, 8)
            )
        );
    break;
}

$objCalendar->setoptions(
    array(
        'intHourBegin' => 6,
        'intHourEnd' => 21,
        'booDisplayChannelsLabel' => $arrSearchPattern['booking_channels'] == 1,
        'intChannelsLabelHeight' => in_array($arrSearchPattern['booking_display_type'], array('day', 'today')) ? '16' : '6'
    )
);

// Recherche des événements
$arrEvents = empty($arrSearchPattern['booking_resources']) ? array() :
    booking_get_events(
        $arrSearchPattern['booking_resources'],
        false,
        false,
        $arrSearchPattern['booking_validated']
    );

if ($arrSearchPattern['booking_channels'])
{
    $arrChannels = array();
    foreach($arrSearchPattern['booking_resources'] as $intIdResource)
    {
        if (isset($arrResources[$intIdResource])) {
            if (empty($arrChannels[$intIdResource])) $arrChannels[$intIdResource] = new ploopi\calendarChannel(in_array($arrSearchPattern['booking_display_type'], array('day', 'today')) ? $arrResources[$intIdResource]['name'] : '', $arrResources[$intIdResource]['color']);
        }
    }

    $objCalendar->setChannels($arrChannels);
    $channelsCount = count($arrChannels);
}
else
{
    $objCalendar->addChannel(new ploopi\calendarChannel(''), '');
}

// Affectation de la liste des événements au calendrier
foreach($arrEvents as $event)
{
    $strBgColor = ($event['validated'] ? $arrBookingColor['validated'] : ($event['canceled'] ? $arrBookingColor['canceled'] : $arrBookingColor['unknown']));
    $strStatus = ($event['validated'] ? 'validé' : ($event['canceled'] ? 'annulé' : 'en attente'));

    if ($arrSearchPattern['booking_display_type'] == 'month')
    {
        $strContent = '<div style="float:right;margin:1px;width:10px;height:10px;background-color:'.$strBgColor.';"></div><div style="height:12px;overflow:hidden;"><time_begin> '.ploopi\str::htmlentities(ploopi\str::cut($event['object'],20)).'</div>';
    }
    else
    {
        $strContent = '<div style="margin:2px;background-color:'.$strBgColor.';border:1px solid #000;text-align:center;">'.$strStatus.'</div><div style="margin:2px;">'.ploopi\str::htmlentities($event['object']).'</div>';
    }

    if ($arrSearchPattern['booking_display_type'] == 'week' && ($channelsCount > 1)) {
        $objCalendar->addEvent(
            new ploopi\calendarEvent(
                $event['timestp_begin'],
                $event['timestp_end'],
                '',
                '',
                $arrSearchPattern['booking_channels'] ? $event['id_resource'] : '',
                array(
                    'strColor' => $event['color'],
                    'strOnClick' => "booking_element_open('event', '{$event['id']},{$event['ed_id']}', event);",
                    'strHref' => 'javascript:void(0);',
                    'strLabel' => ''
                )
            )
        );
    }
    else {
        $objCalendar->addEvent(
            new ploopi\calendarEvent(
                $event['timestp_begin'],
                $event['timestp_end'],
                '<time_begin> / <time_end>',
                $strContent,
                $arrSearchPattern['booking_channels'] ? $event['id_resource'] : '',
                array(
                    'strColor' => $event['color'],
                    'strOnClick' => "booking_element_open('event', '{$event['id']},{$event['ed_id']}', event);",
                    'strHref' => 'javascript:void(0);',
                    'strLabel' => ''
                )
            )
        );
    }
}

?>
<div style="width:100%;overflow:auto;clear:both;" id="planning_display">
    <?php
    $objCalendar->display();
    ?>
</div>
