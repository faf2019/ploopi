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
 * @package planning
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */

ploopi\module::init('planning');
global $arrPlanningPeriodicity;
global $arrPlanningSize;

// Recherche des ressources
$arrResources = planning_get_resources();
// INIT PATTERN de recherche
$arrSearchPattern = array();

$booDateModify = false;

// Requête spéciale en provenance de la gestion des annotations
if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'display_event' && !empty($_REQUEST['planning_event_detail_id']) && is_numeric($_REQUEST['planning_event_detail_id']))
{
    include_once './modules/planning/classes/class_planning_event_detail.php';

    $objEventDetail = new planning_event_detail();
    if ($objEventDetail->open($_REQUEST['planning_event_detail_id'])) // Evénement trouvé
    {
        $arrSearchPattern['planning_display_type'] = 'day';
        $arrSearchPattern['planning_month'] = intval(substr($objEventDetail->fields['timestp_begin'], 4, 2));
        $arrSearchPattern['planning_day'] = intval(substr($objEventDetail->fields['timestp_begin'], 6, 2));
        $arrSearchPattern['planning_year'] = intval(substr($objEventDetail->fields['timestp_begin'], 0, 4));
        $arrSearchPattern['planning_week'] = date('W', ploopi\date::timestamp2unixtimestamp($objEventDetail->fields['timestp_begin']));

        $arrSearchPattern['planning_resources'] = $objEventDetail->getresources();

        $_REQUEST['planning_display_type'] = 'day';

        $booDateModify = true;
    }
}
else
{
    // Lecture cookie
    $arrSearchPattern = planning_getcookie();

    // Lecture des paramètres
    if (isset($_REQUEST['planning_display_type'])) $arrSearchPattern['planning_display_type'] = $_REQUEST['planning_display_type'];

    if (isset($_REQUEST['planning_size'])) $arrSearchPattern['planning_size'] = $_REQUEST['planning_size'];

    if (isset($_REQUEST['planning_resources'])) $arrSearchPattern['planning_resources'] = $_REQUEST['planning_resources'];
    if (isset($_REQUEST['planning_channels'])) $arrSearchPattern['planning_channels'] = $_REQUEST['planning_channels'];

    if (isset($_REQUEST['planning_month'])) $arrSearchPattern['planning_month'] = $_REQUEST['planning_month'];
    if (isset($_REQUEST['planning_year'])) $arrSearchPattern['planning_year'] = $_REQUEST['planning_year'];
    if (isset($_REQUEST['planning_week'])) $arrSearchPattern['planning_week'] = $_REQUEST['planning_week'];
    if (isset($_REQUEST['planning_day'])) $arrSearchPattern['planning_day'] = $_REQUEST['planning_day'];
}


// booléen à true si la date est modifiée par l'utilisateur (mois, année, jour ou semaine)
$booDateModify = $booDateModify || isset($_REQUEST['planning_month']) || isset($_REQUEST['planning_year']) || isset($_REQUEST['planning_week']) || isset($_REQUEST['planning_day']);

// Init des valeurs par défaut
if (!isset($arrSearchPattern['planning_display_type'])) $arrSearchPattern['planning_display_type'] = 'week';

if (!isset($arrSearchPattern['planning_size'])) $arrSearchPattern['planning_size'] = $arrPlanningSize[0];

if (!isset($arrSearchPattern['planning_resources']))
    $arrSearchPattern['planning_resources'] = array(
        'user' => array($_SESSION['ploopi']['userid'] => $_SESSION['ploopi']['userid']),
        'group' => array()
    );
elseif (!isset($arrSearchPattern['planning_resources']['group'])) $arrSearchPattern['planning_resources']['group'] = array();

if (!isset($arrSearchPattern['planning_channels'])) $arrSearchPattern['planning_channels'] = 1;

// Init de la date "virtuelle"
if (!isset($arrSearchPattern['planning_virtualdate'])) $arrSearchPattern['planning_virtualdate'] = time();


if ($booDateModify) // modification de la date de visualisation
{
    // Traitement du cas particulier de changement d'année (en remontant en arrière) qui implique la recherche de la dernière semaine de l'année précédente (52 ou 53 ?)
    if (!empty($_POST['planning_week_previousyear'])) $arrSearchPattern['planning_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['planning_year']));

    // Traitement du cas particulier de changement de mois (en remontant en arrière) qui implique la recherche du dernier jour du mois précédent (28, 29, 30, 31 ?)
    if (!empty($_POST['planning_week_previousmonth'])) $arrSearchPattern['planning_day'] = date('t', mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']));

    // Contrôle de la validité de numéro de semaine (cas ou l'on remonte d'une année et que la semaine sélectionnée est 53)
    if (isset($arrSearchPattern['planning_week']) && $arrSearchPattern['planning_week'] > 52) $arrSearchPattern['planning_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['planning_year']));

    // Contrôle de la validité de numéro de jour (cas ou l'on remonte d'un mois et que le jour sélectionné est > 28)
    if (isset($arrSearchPattern['planning_day']) && $arrSearchPattern['planning_day'] > 28)
    {
        $intMax = date('t', mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']));
        if ($arrSearchPattern['planning_day'] > $intMax) $arrSearchPattern['planning_day'] = $intMax;
    }

    // calcul de la nouvelle date virtuelle en fonction du type d'affichage
    switch ($arrSearchPattern['planning_display_type'])
    {
        case 'day':
            $arrSearchPattern['planning_virtualdate'] = mktime(0, 0, 0, $arrSearchPattern['planning_month'], $arrSearchPattern['planning_day'], $arrSearchPattern['planning_year']);
        break;

        case 'month':
            $arrSearchPattern['planning_virtualdate'] = mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']);
        break;

        case 'week':
            $arrSearchPattern['planning_virtualdate'] = ploopi\date::numweek2unixtimestamp($arrSearchPattern['planning_week'], $arrSearchPattern['planning_year']);
        break;

    }

}

switch ($arrSearchPattern['planning_display_type'])
{
    // modification de la date virtuelle si on choisi "aujourd'hui"
    case 'today':
        $arrSearchPattern['planning_virtualdate'] = time();
    break;

    case 'week':
        $arrSearchPattern['planning_week'] = date('W', $arrSearchPattern['planning_virtualdate']);
    break;

}

$arrSearchPattern['planning_month'] =  date('n', $arrSearchPattern['planning_virtualdate']);
$arrSearchPattern['planning_year'] = date('Y', $arrSearchPattern['planning_virtualdate']);
$arrSearchPattern['planning_day'] = date('j', $arrSearchPattern['planning_virtualdate']);

// Sauvegarde cookie
planning_setcookie($arrSearchPattern);

$arrSize = explode('x', $arrSearchPattern['planning_size']);

/**
 * Détermination des dates de début et fin de la période affichée
 */
switch($arrSearchPattern['planning_display_type'])
{
    case 'month':
        $date_begin = mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']);
        $date_end = mktime(0, 0, 0, $arrSearchPattern['planning_month']+1, 0, $arrSearchPattern['planning_year']);
    break;

    case 'week':
        // On détermine les dates de la semaine courante
        $date_begin = ploopi\date::numweek2unixtimestamp($arrSearchPattern['planning_week'], $arrSearchPattern['planning_year']);
        $date_end = mktime(0, 0, 0, date('n', $date_begin), date('j', $date_begin)+6, date('Y', $date_begin));
    break;

    default:
    case 'today':
    case 'day':
        // On détermine la date du jour
        $date_end = $date_begin = mktime(0, 0, 0, $arrSearchPattern['planning_month'], $arrSearchPattern['planning_day'], $arrSearchPattern['planning_year']);
    break;

}
?>
<div style="overflow:auto;">
    <p class="ploopi_va" style="padding:2px;float:left;">
        <label>Affichage :</label>
        <input type="image" alt="Aujourd'hui" src="./modules/planning/img/ico_today<?php if ($arrSearchPattern['planning_display_type'] != 'today') echo'_notsel'; ?>.png" title="Aujourd'hui" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=today'); ?>', 'planning_main');" />
        <input type="image" alt="Quotidien" src="./modules/planning/img/ico_day<?php if ($arrSearchPattern['planning_display_type'] != 'day') echo'_notsel'; ?>.png" title="Journée" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=day'); ?>', 'planning_main');" />
        <input type="image" alt="Hebdomadaire" src="./modules/planning/img/ico_week<?php if ($arrSearchPattern['planning_display_type'] != 'week') echo'_notsel'; ?>.png" title="Semaine" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=week'); ?>', 'planning_main');" />
        <input type="image" alt="Mensuel" src="./modules/planning/img/ico_month<?php if ($arrSearchPattern['planning_display_type'] != 'month') echo'_notsel'; ?>.png" title="Mois" onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=month'); ?>', 'planning_main');" />
        <a href="javascript:void(0);" onclick="javascript:ploopi.openwin('<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=planning_print") ?>', 800, 600)"><img src="./modules/planning/img/ico_printer.png" title="Imprimer"/></a>

        <label for="booking_channels">Multi Col:</label>
        <input type="checkbox" id="planning_channels" <?php if ($arrSearchPattern['planning_channels']) echo 'checked="checked"'; ?> onclick="javascript:ploopi.xhr.todiv('admin-light.php', '<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_channels='.($arrSearchPattern['planning_channels'] ? 0 : 1)); ?>', 'planning_main');"/>

        <label>Taille :</label>
        <select class="select" name="planning_size" id="planning_size" onchange="javascript:ploopi.xhr.todiv('<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_refresh'); ?>', 'planning_size='+this.value, 'planning_main');">
        <?php
        foreach($arrPlanningSize as $strSize)
        {
            ?><option value="<?php echo $strSize; ?>" <?php if ($arrSearchPattern['planning_size'] == $strSize) echo 'selected="selected"'; ?>><?php echo $strSize; ?></option><?php
        }
        ?>
        </select>
    </p>
    <form style="float:left;" id="planning_form_view" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_refresh'); ?>" method="post" onsubmit="javascript:ploopi.xhr.submit(jQuery('#planning_form_view')[0], 'planning_main');return false;">
    <p class="ploopi_va" style="padding:2px;float:left;">
        <label>Période :</label>
        <?php
        switch($arrSearchPattern['planning_display_type'])
        {
            case 'today':
            case 'day':
                ?>
                <select class="select" name="planning_day" id="planning_day" onchange="javascript:if (jQuery('#planning_form_view')[0].onsubmit()) jQuery('#planning_form_view')[0].submit();">
                <?php
                for ($intDay = 1; $intDay <= date('t', mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year'])); $intDay++)
                {
                    ?>
                    <option value="<?php echo $intDay; ?>" <?php if ($arrSearchPattern['planning_day'] == $intDay) echo 'selected="selected";' ?>><?php echo $intDay; ?></option>
                    <?php
                }
                ?>
                </select>
                <?php

            case 'month':
                ?>
                <select class="select" name="planning_month" id="planning_month" onchange="javascript:if (jQuery('#planning_form_view')[0].onsubmit()) jQuery('#planning_form_view')[0].submit();">
                <?php
                foreach ($ploopi_months as $intMonth => $strMonth)
                {
                    ?>
                    <option value="<?php echo $intMonth; ?>" <?php if ($arrSearchPattern['planning_month'] == $intMonth) echo 'selected="selected";' ?>><?php echo $strMonth; ?></option>
                    <?php
                }
                ?>
                </select>
                <?php
            break;

            case 'week':
                ?>
                <select class="select" name="planning_week" id="planning_week" onchange="javascript:if (jQuery('#planning_form_view')[0].onsubmit()) jQuery('#planning_form_view')[0].submit();">
                <?php
                // Détermination du numéro de semaine max de l'année (on se positionne sur le 31/12)
                $intMaxWeek = date('W', mktime(0, 0, 0, 12, 31, $arrSearchPattern['planning_year']));
                if ($intMaxWeek == 1) $intMaxWeek = 52;

                $date_firstweek = ploopi\date::numweek2unixtimestamp(1, $arrSearchPattern['planning_year']);
                for ($intWeek = 1; $intWeek <= $intMaxWeek; $intWeek++)
                {
                    // Date de début de la semaine en cours d'affichage dans la liste
                    $date_week = mktime(0, 0, 0, date('n', $date_firstweek), date('j', $date_firstweek)+(($intWeek - 1) * 7), date('Y', $date_firstweek));
                    //$date_week = mktime(0, 0, 0, 12, 29 + $d + (($intWeek - 1) * 7), $intSelYear - 1);
                    ?>
                    <option value="<?php echo $intWeek; ?>" <?php if ($arrSearchPattern['planning_week'] == $intWeek) echo 'selected="selected";' ?>><?php printf("Semaine %02d - %s", $intWeek, substr(ploopi\date::unixtimestamp2local($date_week),0,5)); ?></option>
                    <?php
                }
                ?>
                </select>
                <?php
            break;
        }
        ?>

        <select class="select" name="planning_year" id="planning_year" onchange="javascript:if (jQuery('#planning_form_view')[0].onsubmit()) jQuery('#planning_form_view')[0].submit();">
        <?php
        for ($intY = $arrSearchPattern['planning_year']-5; $intY <= $arrSearchPattern['planning_year']+5; $intY++)
        {
            ?>
            <option value="<?php echo $intY; ?>" <?php if ($arrSearchPattern['planning_year'] == $intY) echo 'selected="selected";' ?>><?php echo $intY; ?></option>
            <?php
        }
        ?>
        </select>

        <?php
        switch($arrSearchPattern['planning_display_type'])
        {
            case 'month':
                ?>
                <input type="button" class="button" value="&laquo;&laquo;" title="Mois précédent" onclick="javascript:planning_prevmonth();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Mois suivant" onclick="javascript:planning_nextmonth();" />
                <?php
            break;

            case 'week':
                ?>
                <input type="hidden" name="planning_week_previousyear" id="planning_week_previousyear" value="0" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Semaine précédente" onclick="javascript:planning_prevweek();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Semaine suivante" onclick="javascript:planning_nextweek();" />
                <?php
            break;

            case 'today':
            case 'day':
                ?>
                <input type="hidden" name="planning_week_previousmonth" id="planning_week_previousmonth" value="0" />
                <input type="hidden" name="planning_display_type" value="day" />
                <input type="button" class="button" value="&laquo;&laquo;" title="Jour précédent" onclick="javascript:planning_prevday();" />
                <input type="button" class="button" value="&raquo;&raquo;" title="Jour suivant" onclick="javascript:planning_nextday();" />
                <?php
            break;

        }

        $date_today = time();

        if ($date_today >= $date_begin && $date_today <= $date_end) $date_sel = $date_today;
        else $date_sel = $date_begin;

        if (ploopi\acl::isactionallowed(_PLANNING_ADD_EVENT))
        {
            ?>
            <input type="button" class="button" value="Ajouter un événement" style="margin:0 10px;" onclick="javascript:ploopi.xhr.topopup('450', event, 'popup_planning_event', 'admin-light.php', '<?php echo ploopi\crypt::queryencode("ploopi_op=planning_event_add&&planning_resource_date={$date_sel}"); ?>');" />
            <?php
        }
        ?>
        </p>
    </form>
</div>

<?php
if (sizeof($arrSize) == 2 && is_numeric($arrSize[0]) && is_numeric($arrSize[1]))
{
    switch($arrSearchPattern['planning_display_type'])
    {
        case 'month':
            $objCalendar = new ploopi\calendar($arrSize[0], $arrSize[1], 'month');

            $objCalendar->setoptions(
                array(
                    'intMonth' => $arrSearchPattern['planning_month'],
                    'intYear' => $arrSearchPattern['planning_year'],
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
            'intHourBegin' => 0,
            'intHourEnd' => 24,
            'booDisplayChannelsLabel' => $arrSearchPattern['planning_channels'] == 1,
            'intChannelsLabelHeight' => in_array($arrSearchPattern['planning_display_type'], array('day', 'today')) ? '16' : '6'
        )
    );

    // Recherche des événements
    $arrEvents = array();

    // Recherche des événements
    $arrEvents = planning_get_events(
        $arrSearchPattern['planning_resources'],
        ploopi\date::unixtimestamp2timestamp($date_begin),
        ploopi\date::unixtimestamp2timestamp(mktime(0, 0, 0, date('n', $date_end), date('j', $date_end)+1, date('Y', $date_end)))
    );

    if ($arrSearchPattern['planning_channels']) // Mode multi-canaux
    {
        $arrChannels = array();
        foreach($arrSearchPattern['planning_resources'] as $strTypeResource => $arrTypeResource)
        {
            foreach($arrTypeResource as $intIdResource)
            {
                if (isset($arrResources[$strTypeResource][$intIdResource]))
                {
                    $arrResource = &$arrResources[$strTypeResource][$intIdResource];

                    $strChannelId = $strTypeResource[0].$intIdResource;
                    if (empty($arrChannels[$strChannelId])) $arrChannels[$strChannelId] = new ploopi\calendarChannel(in_array($arrSearchPattern['planning_display_type'], array('day', 'today')) ? $arrResource['label'] : '', $arrResource['color']);
                }
            }
        }

        $objCalendar->setChannels($arrChannels);
    }
    else
    {
        $objCalendar->addChannel(new ploopi\calendarChannel(''), '');
    }

    // Affectation de la liste des événements au calendrier
    foreach($arrEvents as $arrEvent)
    {
        switch($arrSearchPattern['planning_display_type'])
        {
            case 'month':
                $strContent = '<div style="height:12px;overflow:hidden;"><time_begin> '.ploopi\str::htmlentities(ploopi\str::cut($arrEvent['object'],20)).'</div>';
            break;

            case 'week':
            case 'day':
            case 'today':
                $strUsers = '';
                if (!empty($arrEvent['res']))
                {
                    foreach($arrEvent['res'] as $strTypeResource => $arrTypeResource)
                    {
                        foreach($arrTypeResource as $intIdResource)
                        {
                            if (isset($arrResources[$strTypeResource][$intIdResource]))
                            {
                                $arrResource = &$arrResources[$strTypeResource][$intIdResource];

                                $strColor = !empty($arrResource['color']) ? "background:{$arrResource['color']}" : '';
                                $strUsers .= '<img src="./modules/planning/img/ico_'.$strTypeResource.'.png" style="display:block;margin:0 1px;float:left;'.$strColor.';" title="'.ploopi\str::htmlentities($arrResource['label']).'" />';
                            }
                        }
                    }
                }

                if ($arrSearchPattern['planning_channels'])
                {
                    $strContent = '
                        <div style="margin:2px;">'.ploopi\str::htmlentities($arrEvent['object']).'</div>
                    ';
                }
                else
                {
                    $strContent = '
                        <div style="margin:2px;float:right;padding:2px;border:1px solid #888;background:#fff">'.$strUsers.'</div>
                        <div style="margin:2px;">'.ploopi\str::htmlentities($arrEvent['object']).'</div>
                    ';
                }
            break;
        }


        // Options standards pour tous (onclick)
        $arrOptions = array(
            'strHref' => 'javascript:void(0);',
            'strOnClick' => "ploopi.xhr.topopup('450', event, 'popup_planning_event', 'admin-light.php', '".ploopi\crypt::queryencode("ploopi_op=planning_event_detail_open&planning_event_detail_id={$arrEvent['ed_id']}")."');", // onclick
        );

        // Options avancées pour ceux qui peuvent modifier le planning
        if (ploopi\acl::isactionallowed(_PLANNING_ADD_EVENT))
        {
            $arrOptions = array_merge($arrOptions, array(
                'strOnClose' => "if (confirm('Êtes vous certain de vouloir supprimer cet événement ?')) ploopi.xhr.todiv('admin-light.php', '".ploopi\crypt::queryencode("ploopi_op=planning_event_detail_delete&planning_event_detail_id={$arrEvent['ed_id']}")."', 'planning_main'); ploopi.popup.hide('popup_planning_event');",
                'arrOnDrop' => array(
                    'url' => ploopi\crypt::urlencode("admin-light.php?ploopi_op=planning_event_detail_quicksave&planning_event_detail_id={$arrEvent['ed_id']}"),
                    'element_id' => 'planning_main'
                )
            ));
        }


        if ($arrSearchPattern['planning_channels'])
        {
            if (!empty($arrEvent['res']))
            {
                foreach($arrEvent['res'] as $strTypeResource => $arrTypeResource)
                {
                    foreach($arrTypeResource as $intIdResource)
                    {
                        if (isset($arrResources[$strTypeResource][$intIdResource]))
                        {
                            $arrResource = &$arrResources[$strTypeResource][$intIdResource];

                            $objCalendar->addevent(
                                new ploopi\calendarEvent(
                                    $arrEvent['timestp_begin'],
                                    $arrEvent['timestp_end'],
                                    '<time_begin> / <time_end>',
                                    $strContent,
                                    $strTypeResource[0].$intIdResource,
                                    array_merge($arrOptions, array(
                                        'strColor' => $arrResource['color']
                                    ))
                                )
                            );
                        }
                    }
                }
            }
        }
        else
        {
            $objCalendar->addevent(
                new ploopi\calendarEvent(
                    $arrEvent['timestp_begin'],
                    $arrEvent['timestp_end'],
                    '<time_begin> / <time_end>',
                    $strContent,
                    '',
                    $arrOptions
                )
            );
        }
    }
    ?>
    <div style="width:100%;overflow:auto;" id="planning_display">
        <?php
        $objCalendar->display();
        ?>
    </div>
    <?php
}
?>
