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
    if (isset($_REQUEST['planning_autowidth']) && is_numeric($_REQUEST['planning_autowidth'])) $arrSearchPattern['planning_autowidth'] = (int) $_REQUEST['planning_autowidth'];

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

// Taille validée (une des tailles proposées ou « auto »), cf. planning_size()
$arrSize = planning_size($arrSearchPattern);

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
<?php
// ploopi4 : barre d'outils en segments (vue, navigation, options) ; ids, noms et data-* inchangés
$booTb4 = ploopi\skin::get()->version() >= 4;
$objTbSkin = ploopi\skin::get();
$fnTbView = function($strType, $strAlt, $strTitle, $strIcon, $strQuery) use ($booTb4, $objTbSkin, $arrSearchPattern) {
    $booSel = $arrSearchPattern['planning_display_type'] == $strType;
    if ($booTb4) echo '<button type="button" class="p4-caltb-view'.($booSel ? ' is-active' : '').'" aria-pressed="'.($booSel ? 'true' : 'false').'" title="'.$strTitle.'" data-view="'.$strAlt.'" data-ploopi-todiv="planning_main" data-ploopi-query="'.$strQuery.'">'.$objTbSkin->icon($strIcon).'<span>'.$strTitle.'</span></button>';
    else echo '<input type="image" alt="'.$strAlt.'" src="./modules/planning/img/ico_'.$strType.($booSel ? '' : '_notsel').'.png" title="'.$strTitle.'" data-ploopi-todiv="planning_main" data-ploopi-query="'.$strQuery.'" />';
};
?>
<div class="<?php echo $booTb4 ? 'p4-caltb' : ''; ?>" style="<?php if (!$booTb4) echo 'overflow:auto;'; ?>">
    <p class="ploopi_va<?php if ($booTb4) echo ' p4-caltb-group p4-caltb-views'; ?>" style="<?php if (!$booTb4) echo 'padding:2px;float:left;'; ?>">
        <?php if (!$booTb4) { ?><label>Affichage :</label><?php } ?>
        <?php $fnTbView('today', 'Aujourd\'hui', 'Aujourd\'hui', 'calendar-event', ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=today')); ?>
        <?php $fnTbView('day', 'Quotidien', 'Journée', 'calendar', ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=day')); ?>
        <?php $fnTbView('week', 'Hebdomadaire', 'Semaine', 'calendar-week', ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=week')); ?>
        <?php $fnTbView('month', 'Mensuel', 'Mois', 'calendar-month', ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_display_type=month')); ?>

        <?php if ($booTb4) { ?></p><p class="p4-caltb-group p4-caltb-opts"><?php } ?>
        <label for="planning_channels"<?php if ($booTb4) echo ' class="p4-caltb-check" title="Une colonne par ressource"'; ?>><?php echo $booTb4 ? $objTbSkin->icon('table-column').'<span>Colonnes</span>' : 'Multi Col:'; ?></label>
        <input type="checkbox" id="planning_channels" <?php if ($arrSearchPattern['planning_channels']) echo 'checked="checked"'; ?> data-ploopi-todiv="planning_main" data-ploopi-query="<?php echo ploopi\crypt::queryencode('ploopi_op=planning_refresh&planning_channels='.($arrSearchPattern['planning_channels'] ? 0 : 1)); ?>"/>

        <label for="planning_size"><?php echo $booTb4 ? '<span class="p4-caltb-lbl">Taille</span>' : 'Taille :'; ?></label>
        <select class="select" name="planning_size" id="planning_size" data-ploopi-action="planning_size" data-ploopi-on="change" data-ploopi-args="<?php echo ploopi\str::htmlentities(json_encode(array(ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_refresh')))); ?>">
        <option value="auto" <?php if ($arrSearchPattern['planning_size'] == 'auto') echo 'selected="selected"'; ?>>Auto</option>
        <?php
        foreach($arrPlanningSize as $strSize)
        {
            ?><option value="<?php echo $strSize; ?>" <?php if ($arrSearchPattern['planning_size'] == $strSize) echo 'selected="selected"'; ?>><?php echo $strSize; ?></option><?php
        }
        ?>
        </select>
        <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Imprimer" data-ploopi-action="planning_openwin" data-ploopi-args="<?php echo ploopi\str::htmlentities(json_encode(array(ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_print'), 800, 600))); ?>"><?php echo $objTbSkin->icon('printer'); ?></button><?php } else { ?><a href="#" data-ploopi-action="planning_openwin" data-ploopi-args="<?php echo ploopi\str::htmlentities(json_encode(array(ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_print'), 800, 600))); ?>"><img src="./modules/planning/img/ico_printer.png" title="Imprimer"/></a><?php } ?>
    </p>
    <form style="<?php if (!$booTb4) echo 'float:left;'; ?>" class="<?php if ($booTb4) echo 'p4-caltb-period'; ?>" id="planning_form_view" action="<?php echo ploopi\crypt::urlencode('admin-light.php?ploopi_op=planning_refresh'); ?>" method="post" data-planning-submit="planning_main">
    <p class="ploopi_va<?php if ($booTb4) echo ' p4-caltb-group'; ?>" style="<?php if (!$booTb4) echo 'padding:2px;float:left;'; ?>">
        <?php if (!$booTb4) { ?><label>Période :</label><?php } ?>
        <?php
        switch($arrSearchPattern['planning_display_type'])
        {
            case 'today':
            case 'day':
                ?>
                <select class="select" name="planning_day" id="planning_day" data-ploopi-autosubmit>
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
                <select class="select" name="planning_month" id="planning_month" data-ploopi-autosubmit>
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
                <select class="select" name="planning_week" id="planning_week" data-ploopi-autosubmit>
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

        <select class="select" name="planning_year" id="planning_year" data-ploopi-autosubmit>
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
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Mois précédent" data-ploopi-action="planning_prevmonth"><?php echo $objTbSkin->icon('chevron-left'); ?></button><?php } else { ?><input type="button" class="button" value="&laquo;&laquo;" title="Mois précédent" data-ploopi-action="planning_prevmonth" /><?php } ?>
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Mois suivant" data-ploopi-action="planning_nextmonth"><?php echo $objTbSkin->icon('chevron-right'); ?></button><?php } else { ?><input type="button" class="button" value="&raquo;&raquo;" title="Mois suivant" data-ploopi-action="planning_nextmonth" /><?php } ?>
                <?php
            break;

            case 'week':
                ?>
                <input type="hidden" name="planning_week_previousyear" id="planning_week_previousyear" value="0" />
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Semaine précédente" data-ploopi-action="planning_prevweek"><?php echo $objTbSkin->icon('chevron-left'); ?></button><?php } else { ?><input type="button" class="button" value="&laquo;&laquo;" title="Semaine précédente" data-ploopi-action="planning_prevweek" /><?php } ?>
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Semaine suivante" data-ploopi-action="planning_nextweek"><?php echo $objTbSkin->icon('chevron-right'); ?></button><?php } else { ?><input type="button" class="button" value="&raquo;&raquo;" title="Semaine suivante" data-ploopi-action="planning_nextweek" /><?php } ?>
                <?php
            break;

            case 'today':
            case 'day':
                ?>
                <input type="hidden" name="planning_week_previousmonth" id="planning_week_previousmonth" value="0" />
                <input type="hidden" name="planning_display_type" value="day" />
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Jour précédent" data-ploopi-action="planning_prevday"><?php echo $objTbSkin->icon('chevron-left'); ?></button><?php } else { ?><input type="button" class="button" value="&laquo;&laquo;" title="Jour précédent" data-ploopi-action="planning_prevday" /><?php } ?>
                <?php if ($booTb4) { ?><button type="button" class="p4-caltb-icon" title="Jour suivant" data-ploopi-action="planning_nextday"><?php echo $objTbSkin->icon('chevron-right'); ?></button><?php } else { ?><input type="button" class="button" value="&raquo;&raquo;" title="Jour suivant" data-ploopi-action="planning_nextday" /><?php } ?>
                <?php
            break;

        }

        $date_today = time();

        if ($date_today >= $date_begin && $date_today <= $date_end) $date_sel = $date_today;
        else $date_sel = $date_begin;

        if (ploopi\acl::isactionallowed(_PLANNING_ADD_EVENT))
        {
            ?>
            <?php if ($booTb4) { ?></p><p class="p4-caltb-group p4-caltb-add"><button type="button" class="button p4-caltb-primary" data-ploopi-topopup="popup_planning_event" data-ploopi-width="580" data-ploopi-query="<?php echo ploopi\crypt::queryencode("ploopi_op=planning_event_add&&planning_resource_date={$date_sel}"); ?>"><?php echo $objTbSkin->icon('plus'); ?><span>Ajouter un événement</span></button><?php } else { ?>
            <input type="button" class="button" value="Ajouter un événement" style="margin:0 10px;" data-ploopi-topopup="popup_planning_event" data-ploopi-width="450" data-ploopi-query="<?php echo ploopi\crypt::queryencode("ploopi_op=planning_event_add&&planning_resource_date={$date_sel}"); ?>" />
            <?php } ?>
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
                        <div class="planning_cartouche">'.$strUsers.'</div>
                        <div style="margin:2px;">'.ploopi\str::htmlentities($arrEvent['object']).'</div>
                    ';
                }
            break;
        }


        // Options standards pour tous : détail de l'événement dans une fenêtre (js/actions.js)
        $arrOptions = array(
            'strHref' => '#',
            'arrAttrs' => array(
                'data-ploopi-topopup' => 'popup_planning_event',
                'data-ploopi-width' => $booTb4 ? '580' : '450',
                'data-ploopi-query' => ploopi\crypt::queryencode("ploopi_op=planning_event_detail_open&planning_event_detail_id={$arrEvent['ed_id']}"),
            ),
        );

        // Options avancées pour ceux qui peuvent modifier le planning
        if (ploopi\acl::isactionallowed(_PLANNING_ADD_EVENT))
        {
            $arrOptions = array_merge($arrOptions, array(
                'arrCloseAttrs' => array(
                    'data-ploopi-action' => 'planning_event_delete',
                    'data-ploopi-args' => array(ploopi\crypt::queryencode("ploopi_op=planning_event_detail_delete&planning_event_detail_id={$arrEvent['ed_id']}")),
                ),
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
    <div style="width:100%;overflow:auto;" id="planning_display"<?php if ($arrSearchPattern['planning_size'] == 'auto') echo ' data-autowidth="'.$arrSize[0].'" data-autowidth-op="planning_refresh" data-autowidth-param="planning_autowidth" data-autowidth-target="planning_main" data-autowidth-avoid="popup_planning"'; ?>>
        <?php
        $objCalendar->display();
        ?>
    </div>
    <?php
}
?>
