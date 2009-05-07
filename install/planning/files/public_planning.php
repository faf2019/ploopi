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
 * Affichage du planning
 *
 * @package planning
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */

ploopi_init_module('planning');

include_once './include/classes/calendar.php';

// Recherche des ressources
$arrResources = planning_get_resources();
// INIT PATTERN de recherche
$arrSearchPattern = array();

// Lecture session
if (isset($_SESSION['planning'][$_SESSION['ploopi']['moduleid']]['planning_request'])) $arrSearchPattern = $_SESSION['planning'][$_SESSION['ploopi']['moduleid']]['planning_request'];

// Lecture des paramètres
if (isset($_REQUEST['planning_display_type'])) $arrSearchPattern['planning_display_type'] = $_REQUEST['planning_display_type']; 

if (isset($_REQUEST['planning_size'])) $arrSearchPattern['planning_size'] = $_REQUEST['planning_size'];

if (isset($_REQUEST['planning_resources'])) $arrSearchPattern['planning_resources'] = $_REQUEST['planning_resources'];

if (isset($_REQUEST['planning_month'])) $arrSearchPattern['planning_month'] = $_REQUEST['planning_month'];
if (isset($_REQUEST['planning_year'])) $arrSearchPattern['planning_year'] = $_REQUEST['planning_year'];
if (isset($_REQUEST['planning_week'])) $arrSearchPattern['planning_week'] = $_REQUEST['planning_week'];
if (isset($_REQUEST['planning_day'])) $arrSearchPattern['planning_day'] = $_REQUEST['planning_day'];

// booléen à true si la date est modifiée par l'utilisateur (mois, année, jour ou semaine)
$booDateModify = isset($_REQUEST['planning_month']) || isset($_REQUEST['planning_year']) || isset($_REQUEST['planning_week']) || isset($_REQUEST['planning_day']);

// Init des valeurs par défaut
if (!isset($arrSearchPattern['planning_display_type'])) $arrSearchPattern['planning_display_type'] = 'week';

if (!isset($arrSearchPattern['planning_size'])) $arrSearchPattern['planning_size'] = $arrPlanningSize[0];

if (!isset($arrSearchPattern['planning_resources'])) 
    $arrSearchPattern['planning_resources'] = array(
        'user' => array($_SESSION['ploopi']['userid'] => $_SESSION['ploopi']['userid']), 
        'group' => array()
    );

// Init de la date "virtuelle"
if (!isset($arrSearchPattern['planning_virtualdate'])) $arrSearchPattern['planning_virtualdate'] = time();

    
if ($booDateModify) // modification de la date de visualisation
{
    // Traitement du cas particulier de changement d'année (en remontant en arrière) qui implique la recherche de la dernière semaine de l'année précédente (52 ou 53 ?)
    if (!empty($_POST['planning_week_previousyear'])) $arrSearchPattern['planning_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['planning_year']));
    
    // Traitement du cas particulier de changement de mois (en remontant en arrière) qui implique la recherche du dernier jour du mois précédent (28, 29, 30, 31 ?)
    if (!empty($_POST['planning_week_previousmonth'])) $arrSearchPattern['planning_day'] = date('t', mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year']));
    
    // Contrôle de la validité de numéro de semaine (cas ou l'on remonte d'une année et que la semaine sélectionnée est 53)
    if ($arrSearchPattern['planning_week'] > 52) $arrSearchPattern['planning_week'] = date('W', mktime(0, 0, 0, 12, 28, $arrSearchPattern['planning_year']));
    
    // Contrôle de la validité de numéro de jour (cas ou l'on remonte d'un mois et que le jour sélectionné est > 28)
    if ($arrSearchPattern['planning_day'] > 28)
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
            $arrSearchPattern['planning_virtualdate'] = ploopi_numweek2unixtimestamp($arrSearchPattern['planning_week'], $arrSearchPattern['planning_year']);
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


$_SESSION['planning'][$_SESSION['ploopi']['moduleid']]['planning_request'] = $arrSearchPattern;

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
        $date_begin = ploopi_numweek2unixtimestamp($arrSearchPattern['planning_week'], $arrSearchPattern['planning_year']);
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

<p class="ploopi_va" style="padding:2px;float:left;">
    <label>Affichage :</label>
    <input type="image" alt="Aujourd'hui" src="./modules/planning/img/ico_today<? if ($arrSearchPattern['planning_display_type'] != 'today') echo'_notsel'; ?>.png" title="Aujourd'hui" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', '<? echo ploopi_queryencode('ploopi_op=planning_refresh&planning_display_type=today'); ?>', 'planning_main');" />
    <input type="image" alt="Quotidien" src="./modules/planning/img/ico_day<? if ($arrSearchPattern['planning_display_type'] != 'day') echo'_notsel'; ?>.png" title="Journée" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', '<? echo ploopi_queryencode('ploopi_op=planning_refresh&planning_display_type=day'); ?>', 'planning_main');" />
    <input type="image" alt="Hebdomadaire" src="./modules/planning/img/ico_week<? if ($arrSearchPattern['planning_display_type'] != 'week') echo'_notsel'; ?>.png" title="Semaine" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', '<? echo ploopi_queryencode('ploopi_op=planning_refresh&planning_display_type=week'); ?>', 'planning_main');" />
    <input type="image" alt="Mensuel" src="./modules/planning/img/ico_month<? if ($arrSearchPattern['planning_display_type'] != 'month') echo'_notsel'; ?>.png" title="Mois" onclick="javascript:ploopi_xmlhttprequest_todiv('admin-light.php', '<? echo ploopi_queryencode('ploopi_op=planning_refresh&planning_display_type=month'); ?>', 'planning_main');" />

    <label>Taille :</label>
    <select class="select" name="planning_size" id="planning_size" onchange="javascript:ploopi_xmlhttprequest_todiv('<? echo ploopi_urlencode('admin-light.php?ploopi_op=planning_refresh'); ?>', 'planning_size='+this.value, 'planning_main');">
    <?
    foreach($arrPlanningSize as $strSize)
    {
        ?><option value="<? echo $strSize; ?>" <? if ($arrSearchPattern['planning_size'] == $strSize) echo 'selected="selected"'; ?>><? echo $strSize; ?></option><?
    }
    ?>
    </select>
</p>
<form style="float:left;" id="planning_form_view" action="<? echo ploopi_urlencode('admin-light.php?ploopi_op=planning_refresh'); ?>" method="post" onsubmit="javascript:ploopi_xmlhttprequest_submitform(this, 'planning_main');return false;">
<p class="ploopi_va" style="padding:2px;float:left;">
    <label>Période :</label>
    <?
    switch($arrSearchPattern['planning_display_type'])
    {
        case 'today':
        case 'day':
            ?>
            <select class="select" name="planning_day" id="planning_day" onchange="javascript:if ($('planning_form_view').onsubmit()) $('planning_form_view').submit();">
            <?
            for ($intDay = 1; $intDay <= date('t', mktime(0, 0, 0, $arrSearchPattern['planning_month'], 1, $arrSearchPattern['planning_year'])); $intDay++)
            {
                ?>
                <option value="<? echo $intDay; ?>" <? if ($arrSearchPattern['planning_day'] == $intDay) echo 'selected="selected";' ?>><? echo $intDay; ?></option>
                <?
            }
            ?>
            </select>
            <?

        case 'month':
            ?>
            <select class="select" name="planning_month" id="planning_month" onchange="javascript:if ($('planning_form_view').onsubmit()) $('planning_form_view').submit();">
            <?
            foreach ($ploopi_months as $intMonth => $strMonth)
            {
                ?>
                <option value="<? echo $intMonth; ?>" <? if ($arrSearchPattern['planning_month'] == $intMonth) echo 'selected="selected";' ?>><? echo $strMonth; ?></option>
                <?
            }
            ?>
            </select>
            <?
        break;

        case 'week':
            ?>
            <select class="select" name="planning_week" id="planning_week" onchange="javascript:if ($('planning_form_view').onsubmit()) $('planning_form_view').submit();">
            <?
            // Détermination du numéro de semaine max de l'année (on se positionne sur le 31/12)
            $intMaxWeek = date('W', mktime(0, 0, 0, 12, 31, $arrSearchPattern['planning_year']));
            if ($intMaxWeek == 1) $intMaxWeek = 52;

            $date_firstweek = ploopi_numweek2unixtimestamp(1, $arrSearchPattern['planning_year']);
            for ($intWeek = 1; $intWeek <= $intMaxWeek; $intWeek++)
            {
                // Date de début de la semaine en cours d'affichage dans la liste
                $date_week = mktime(0, 0, 0, date('n', $date_firstweek), date('j', $date_firstweek)+(($intWeek - 1) * 7), date('Y', $date_firstweek));
                //$date_week = mktime(0, 0, 0, 12, 29 + $d + (($intWeek - 1) * 7), $intSelYear - 1);
                ?>
                <option value="<? echo $intWeek; ?>" <? if ($arrSearchPattern['planning_week'] == $intWeek) echo 'selected="selected";' ?>><? printf("Semaine %02d - %s", $intWeek, substr(ploopi_unixtimestamp2local($date_week),0,5)); ?></option>
                <?
            }
            ?>
            </select>
            <?
        break;
    }
    ?>

    <select class="select" name="planning_year" id="planning_year" onchange="javascript:if ($('planning_form_view').onsubmit()) $('planning_form_view').submit();">
    <?
    for ($intY = $arrSearchPattern['planning_year']-5; $intY <= $arrSearchPattern['planning_year']+5; $intY++)
    {
        ?>
        <option value="<? echo $intY; ?>" <? if ($arrSearchPattern['planning_year'] == $intY) echo 'selected="selected";' ?>><? echo $intY; ?></option>
        <?
    }
    ?>
    </select>

    <?
    switch($arrSearchPattern['planning_display_type'])
    {
        case 'month':
            ?>
            <input type="button" class="button" value="&laquo;&laquo;" title="Mois précédent" onclick="javascript:planning_prevmonth();" />
            <input type="button" class="button" value="&raquo;&raquo;" title="Mois suivant" onclick="javascript:planning_nextmonth();" />
            <?
        break;

        case 'week':
            ?>
            <input type="hidden" name="planning_week_previousyear" id="planning_week_previousyear" value="0" />
            <input type="button" class="button" value="&laquo;&laquo;" title="Semaine précédente" onclick="javascript:planning_prevweek();" />
            <input type="button" class="button" value="&raquo;&raquo;" title="Semaine suivante" onclick="javascript:planning_nextweek();" />
            <?
        break;

        case 'today':
        case 'day':
            ?>
            <input type="hidden" name="planning_week_previousmonth" id="planning_week_previousmonth" value="0" />
            <input type="hidden" name="planning_display_type" value="day" />
            <input type="button" class="button" value="&laquo;&laquo;" title="Jour précédent" onclick="javascript:planning_prevday();" />
            <input type="button" class="button" value="&raquo;&raquo;" title="Jour suivant" onclick="javascript:planning_nextday();" />
            <?
        break;

    }

    $date_today = mktime();

    if ($date_today >= $date_begin && $date_today <= $date_end) $date_sel = $date_today;
    else $date_sel = $date_begin;

    if (ploopi_isactionallowed(_PLANNING_ADD_EVENT))
    {
        ?>
        <input type="button" class="button" value="Ajouter un événement" style="margin:0 10px;" onclick="javascript:ploopi_xmlhttprequest_topopup('450', event, 'popup_planning_event', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=planning_event_add&&planning_resource_date={$date_sel}"); ?>');" />
        <?
    }
    ?>
    </p>
</form>

<?
if (sizeof($arrSize) == 2 && is_numeric($arrSize[0]) && is_numeric($arrSize[1]))
{
    switch($arrSearchPattern['planning_display_type'])
    {
        case 'month':
            $objCalendar = new calendar($arrSize[0], $arrSize[1], 'month');
    
            $objCalendar->setoptions(
                array(
                    'month' => $arrSearchPattern['planning_month'],
                    'year' => $arrSearchPattern['planning_year'],
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
            'hour_begin' => 0,
            'hour_end' => 24
        )
    );
    
    // Recherche des événements
    $arrEvents = array();
    
    // Recherche des événements
    $arrEvents = planning_get_events(
        $arrSearchPattern['planning_resources'],
        ploopi_unixtimestamp2timestamp($date_begin),
        ploopi_unixtimestamp2timestamp(mktime(0, 0, 0, date('n', $date_end), date('j', $date_end)+1, date('Y', $date_end)))
    );    
    
    
    // Affectation de la liste des événements au calendrier
    foreach($arrEvents as $arrEvent)
    {
        
        switch($arrSearchPattern['planning_display_type'])
        {
            case 'month':
                $strContent = '
                    <div style="margin:2px;overflow:auto;">'.htmlentities(ploopi_strcut($arrEvent['object'])).'</div>
                ';
            break;
            
            case 'week':
            case 'day':
                $strUsers = '';
                if (!empty($arrEvent['res']))
                {
                    foreach($arrEvent['res'] as $strTypeResource => $arrTypeResource)
                    {
                        foreach($arrTypeResource as $intResource)
                        {
                            if (isset($arrResources[$strTypeResource][$intResource]))
                            {
                                $arrResource = &$arrResources[$strTypeResource][$intResource];
                                
                                $strColor = !empty($arrResource['color']) ? "background:{$arrResource['color']}" : '';
                                $strUsers .= '<img src="/modules/planning/img/ico_'.$strTypeResource.'.png" style="display:block;margin:0 1px;float:left;'.$strColor.';" title="'.htmlentities($arrResource['label']).'" />';
                            }
                        }
                    }
                }
                
                $strContent = '
                    <div style="margin:2px;float:right;padding:2px;border:1px solid #888;background:#fff">'.$strUsers.'</div>
                    <div style="margin:2px;">'.htmlentities($arrEvent['object']).'</div>
                ';
            break;    
        }
            
        $objCalendar->addevent(
            new calendarEvent(
                $arrEvent['timestp_begin'],
                $arrEvent['timestp_end'],
                htmlentities($arrEvent['object']),
                $strContent,
                '',
                "ploopi_xmlhttprequest_topopup('450', event, 'popup_planning_event', 'admin-light.php', '".ploopi_queryencode("ploopi_op=planning_event_detail_open&planning_event_detail_id={$arrEvent['ed_id']}")."');", // onclick
                'javascript:void(0);', // href
                "if (confirm('Êtes vous certain de vouloir supprimer cet événement ?')) ploopi_xmlhttprequest_todiv('admin-light.php', '".ploopi_queryencode("ploopi_op=planning_event_detail_delete&planning_event_detail_id={$arrEvent['ed_id']}")."', 'planning_main'); ploopi_hidepopup('popup_planning_event');",
                array(
                    'url' => ploopi_urlencode("admin-light.php?ploopi_op=planning_event_detail_quicksave&planning_event_detail_id={$arrEvent['ed_id']}"),
                    'element_id' => 'planning_main'
                )
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
