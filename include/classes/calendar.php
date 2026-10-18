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

namespace ploopi;

use ploopi;

/**
 * Affichage d'un calendrier/agenda
 *
 * @package ploopi
 * @subpackage calendar
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class calendar
{
    /**
     * Largeur du calendrier
     *
     * @var int
     */

    protected $intWidth;

    /**
     * Hauteur du calendrier
     *
     * @var int
     */

    protected $intHeight;

    /**
     * Type de calendrier : month/days
     *
     * @var string
     */

    protected $strDisplayType;

    /**
     * Options du calendrier
     *
     * @var array
     */

    protected $arrOptions;

    /**
     * Evénements du calendrier
     *
     * @var array
     */

    protected $arrEvents;

    /**
     * Canaux d'affichage du calendrier
     *
     * @var array
     */

    protected $arrChannels;

    /**
     * Constructeur de la classe
     *
     * @param unknown_type $intWidth
     * @param unknown_type $intHeight
     * @param unknown_type $strDisplayType
     * @param unknown_type $arrOptions
     * @return calendar
     */

    public function __construct($intWidth, $intHeight, $strDisplayType, $arrOptions = array(), $arrEvents = array(), $arrChannels = array())
    {
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        $this->strDisplayType = $strDisplayType;

        $this->arrOptions = array(
            'strClassName' => 'ploopi_calendar_'.($strDisplayType == 'month' ? 'm' : 'd'), // class de style (css) utilisée

            'intMonth' => '', // valable pour display_type = 'month'
            'intYear' => '',

            'strDateBegin' => '',
            'strDateEnd' => '',

            'intHourBegin' => 6,
            'intHourEnd' => 21,

            'booDisplayNumWeeks' => true,
            'booDisplayHours' => true,
            'booDisplayDaysLabel' => true,
            'booDisplayChannelsLabel' => true,

            'intNumWeeksColWidth' => 25,
            'intHoursColWidth' => 25,
            'intDaysLabelHeight' => 20,
            'intChannelsLabelHeight' => 10,
        );

        $this->setOptions($arrOptions);

        $this->arrEvents = array();
        $this->arrChannels = array();

        $this->setEvents($arrEvents);
        $this->setChannels($arrChannels);
    }

    /**
     * Permet de définir les options d'affichage :
     *
     * string 'strClassName' class de rendu (defaut: ploopi_calendar_m ou ploopi_calendar_d en fonction du type de planning monthly/daily)
     * integer 'intMonth' mois à afficher pour l'affichage mensuel
     * integer 'intYear' année à afficher
     * string 'strDateBegin' date de début
     * string 'strDateEnd' date de fin
     * integer 'intHourBegin' heure min (defaut: 6)
     * integer 'intHourEnd' heure max (defaut: 21)
     * bool 'booDisplayNumWeeks' true
     * bool 'booDisplayHours' affichage des heures (defaut: true)
     * bool 'booDisplayDaysLabel' afficher les jours (defaut: true)
     * integer 'intNumWeeksColWidth' largeur de la colonne des semaines pour l'affichage mensuel (defaut: 25)
     * integer 'intHoursColWidth' largeur de la colonne des heures pour l'affichage hebdomadaire (defaut: 25)
     * integer 'intDaysLabelHeight' hauteur de la ligne des jours pour l'affichage hebdomadaire (defaut: 20)
     *
     * @param array $arrOptions tableau des options à modifier
     */

    public function setOptions($arrOptions)
    {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }

    public function getOptions()
    {
        return $this->arrOptions;
    }

    public function setEvents($arrEvents = array())
    {
        $this->arrEvents = $this->arrEvents + $arrEvents;
    }

    public function addEvent(calendarEvent $objEvent)
    {
        $this->arrEvents[] = $objEvent;
    }

    public function setChannels($arrChannels = array())
    {
        $this->arrChannels = $this->arrChannels + $arrChannels;
    }

    public function addChannel(calendarChannel $objChannel, $strId)
    {
        $this->arrChannels[$strId] = $objChannel;
    }

    public function display()
    {
        // Gabarit de génération 4 (ploopi4), backoffice : grille fluide (rendu moderne)
        if ($this->_modern())
        {
            $this->_displayModern();
            return;
        }

        ?>
        <div class="<?php echo $this->arrOptions['strClassName']; ?>" id="calendar">
            <?php
            switch($this->strDisplayType)
            {
                case 'days':
                    $this->_displayDays();
                break;

                case 'month':
                    $this->_displayMonth();
                break;
            }
            ?>
        </div>
        <?php
    }

    /**
     * Rendu moderne : backoffice sous un gabarit de génération 4 (ploopi4). Le frontoffice
     * et les gabarits historiques gardent le rendu en pixels.
     *
     * @return boolean
     */

    protected function _modern()
    {
        return isset($_SESSION['ploopi']['mode']) && $_SESSION['ploopi']['mode'] == 'backoffice' && skin::get()->version() >= 4;
    }

    /**
     * Rendu moderne du planning : grille CSS fluide (largeur de la page, jamais calculée en
     * pixels côté serveur), heures et événements placés en pourcentage de la journée,
     * événements simultanés côte à côte, journée et heure courantes repérées, thème
     * sombre. Mêmes options et mêmes événements que le rendu historique ; la hauteur
     * demandée (intHeight) fixe celle d'une heure ou d'une semaine, bornée pour rester
     * lisible. Déplacement des événements (arrOnDrop) : js/calendar_grid.js, même
     * requête que le rendu historique (calendar_event_date, calendar_event_hour).
     */

    protected function _displayModern()
    {
        $arrEvents = $this->_prepare_events();

        if ($this->strDisplayType == 'month') $this->_modernMonth($arrEvents);
        else $this->_modernDays($arrEvents);
    }

    /**
     * Couleur d'un événement ou d'un canal, utilisable dans un style (sinon vide)
     *
     * @param string $strColor couleur
     * @return string
     */

    protected static function _cssColor($strColor)
    {
        $strColor = trim((string) $strColor);
        return preg_match('/^(#[0-9a-f]{3,8}|[a-z]{3,20}|rgba?\([0-9., %]+\))$/i', $strColor) ? $strColor : '';
    }

    /**
     * Texte d'un événement : balises <date_begin>, <date_end>, <time_begin>, <time_end>
     * remplacées (le contenu est du HTML fourni par le module)
     *
     * @param calendarEvent $objEvent événement
     * @param string $strText texte
     * @return string
     */

    protected static function _eventText($objEvent, $strText)
    {
        $arrBegin = date::timestamp2local($objEvent->intTimestpBegin);
        $arrEnd = date::timestamp2local($objEvent->intTimestpEnd);

        return str_replace(
            array('<date_begin>', '<date_end>', '<time_begin>', '<time_end>'),
            array(substr($arrBegin['date'], 0, 5), substr($arrEnd['date'], 0, 5), substr($arrBegin['time'], 0, 5), substr($arrEnd['time'], 0, 5)),
            (string) $strText
        );
    }

    /**
     * Ouverture du lien d'un événement : href, attributs data-* (arrAttrs) ou, pour les
     * modules non migrés, code strOnClick ; un événement sans action est un simple bloc
     *
     * @param calendarEvent $objEvent événement
     * @param string $strClass classe
     * @param string $strExtra attributs complémentaires (échappés par l'appelant)
     * @return array balise ouvrante, balise fermante
     */

    protected static function _eventLink($objEvent, $strClass, $strExtra = '')
    {
        $arrAttrs = $objEvent->arrAttrs;
        $strOnClick = $objEvent->strOnClick;
        $strHref = $objEvent->strHref;

        if (empty($arrAttrs) && is_null($strOnClick) && empty($strHref)) return array('<div class="'.$strClass.'"'.$strExtra.'>', '</div>');

        return array(
            '<a class="'.$strClass.'" href="'.(empty($strHref) ? '#' : $strHref).'"'
                .(is_null($strOnClick) ? '' : ' onclick="'.$strOnClick.'"')
                .skin::treeview_attrs($arrAttrs).$strExtra.'>',
            '</a>'
        );
    }

    /**
     * Position des événements simultanés d'une journée (d'un canal) : couloir de chaque
     * événement et nombre de couloirs de son groupe d'événements qui se chevauchent
     *
     * @param array $arrItems id => array(heure de début, heure de fin)
     * @return array id => array(couloir, nombre de couloirs)
     */

    protected static function _lanes($arrItems)
    {
        uasort($arrItems, function($a, $b) { return $a[0] == $b[0] ? $a[1] <=> $b[1] : $a[0] <=> $b[0]; });

        $arrResult = array();
        $arrGroup = array();
        $arrLaneEnds = array();
        $floGroupEnd = -1;

        $funcClose = function() use (&$arrResult, &$arrGroup, &$arrLaneEnds) {
            foreach ($arrGroup as $mixId) $arrResult[$mixId][1] = count($arrLaneEnds);
            $arrGroup = array();
            $arrLaneEnds = array();
        };

        foreach ($arrItems as $mixId => $arrItem)
        {
            if (!empty($arrGroup) && $arrItem[0] >= $floGroupEnd) { $funcClose(); $floGroupEnd = -1; }

            $intLane = null;
            foreach ($arrLaneEnds as $intL => $floEnd) if ($floEnd <= $arrItem[0]) { $intLane = $intL; break; }
            if (is_null($intLane)) $intLane = count($arrLaneEnds);

            // Un événement sans durée occupe tout de même une demi-heure
            $arrLaneEnds[$intLane] = max($arrItem[1], $arrItem[0] + 0.5);
            $arrResult[$mixId] = array($intLane, 1);
            $arrGroup[] = $mixId;
            $floGroupEnd = max($floGroupEnd, $arrLaneEnds[$intLane]);
        }
        $funcClose();

        return $arrResult;
    }

    /**
     * Classes d'une journée : aujourd'hui, week-end, jour férié, hors du mois affiché
     *
     * @param int $intDay jour (timestamp unix)
     * @param boolean $booOut hors du mois affiché
     * @return string
     */

    protected static function _dayClass($intDay, $booOut = false)
    {
        $arrClass = array();
        if (date('Ymd', $intDay) == date('Ymd')) $arrClass[] = 'is-today';
        if (date::holiday($intDay)) $arrClass[] = 'is-holiday';
        elseif (date('N', $intDay) > 5) $arrClass[] = 'is-weekend';
        if ($booOut) $arrClass[] = 'is-out';

        return empty($arrClass) ? '' : ' '.implode(' ', $arrClass);
    }

    /**
     * Rendu moderne, journées (jour, semaine) : colonnes de jours (et de canaux), heures
     * en ordonnée
     *
     * @param array $arrEvents événements par jour et par canal (_prepare_events)
     */

    protected function _modernDays($arrEvents)
    {
        global $ploopi_days;

        $intHourBegin = (int) $this->arrOptions['intHourBegin'];
        $intHourEnd = max($intHourBegin + 1, (int) $this->arrOptions['intHourEnd']);
        $intNbHours = $intHourEnd - $intHourBegin;

        // Hauteur d'une heure : celle demandée (option « Taille » des modules), lisible au minimum
        $intHourHeight = max(40, min(120, (int) floor(((int) $this->intHeight - 60) / $intNbHours)));

        $intFirstDay = date::timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateBegin']));
        $intLastDay = date::timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateEnd']));
        $intNbDays = max(1, (int) floor(($intLastDay - $intFirstDay) / 86400) + 1);

        // Sans canal déclaré : un canal implicite, celui des événements sans canal
        $arrChannels = empty($this->arrChannels) ? array('' => null) : $this->arrChannels;
        $intNbChan = count($arrChannels);
        $booChanLabels = $this->arrOptions['booDisplayChannelsLabel'] && !empty($this->arrChannels) && $intNbChan > 1;

        $arrDays = array();
        for ($d = 0; $d < $intNbDays; $d++) $arrDays[] = mktime(0, 0, 0, date('n', $intFirstDay), date('j', $intFirstDay) + $d, date('Y', $intFirstDay));

        // Heure présentée à l'ouverture (js/calendar_grid.js) : le premier événement de la
        // période, 8 h au plus tard, une demi-heure avant
        $floScroll = 8;
        foreach ($arrDays as $intDay)
        {
            if (empty($arrEvents[date('Ymd', $intDay)])) continue;
            foreach ($arrEvents[date('Ymd', $intDay)] as $arrIds) foreach ($arrIds as $intId)
            {
                if (empty($this->arrEvents[$intId]) || substr($this->arrEvents[$intId]->intTimestpBegin, 0, 8) != date('Ymd', $intDay)) continue;
                $floScroll = min($floScroll, (int) substr($this->arrEvents[$intId]->intTimestpBegin, 8, 2) + (int) substr($this->arrEvents[$intId]->intTimestpBegin, 10, 2) / 60);
            }
        }
        $floScroll = max($intHourBegin, $floScroll - 0.5);

        // Colonnes : samedi et dimanche plus étroits quand la période compte aussi des jours ouvrés
        $booNarrowWeekend = count(array_filter($arrDays, function($intDay) { return date('N', $intDay) <= 5; })) > 0 && $intNbDays > 1;
        $arrColumns = array('var(--p4-cal-gutter)');
        // Largeur minimale d'un canal : moindre quand une journée en compte plusieurs
        $intChanMin = $intNbChan > 1 ? 60 : 84;
        foreach ($arrDays as $intDay) $arrColumns[] = $booNarrowWeekend && date('N', $intDay) > 5 ? 'minmax('.($intNbChan * 44).'px, 0.55fr)' : 'minmax('.($intNbChan * $intChanMin).'px, 1fr)';

        ?>
        <div class="p4-cal p4-cal-days" id="calendar" style="--p4-cal-hour:<?php echo $intHourHeight; ?>px;--p4-cal-hours:<?php echo $intNbHours; ?>;--p4-cal-cols:<?php echo $intNbDays; ?>;--p4-cal-chans:<?php echo $intNbChan; ?>;--p4-cal-template:<?php echo implode(' ', $arrColumns); ?>;" data-calendar-hour-begin="<?php echo $intHourBegin; ?>" data-calendar-hour-end="<?php echo $intHourEnd; ?>" data-calendar-scroll="<?php echo $floScroll; ?>">
            <div class="p4-cal-scroll">
                <div class="p4-cal-grid">
                    <?php if ($this->arrOptions['booDisplayDaysLabel'] || $booChanLabels) { ?>
                    <div class="p4-cal-head">
                        <div class="p4-cal-corner"></div>
                        <?php
                        foreach ($arrDays as $intDay)
                        {
                            $strDay = $ploopi_days[date('N', $intDay)];
                            ?>
                            <div class="p4-cal-dayhead<?php echo self::_dayClass($intDay); ?>" title="<?php echo str::htmlentities($strDay.' '.date::unixtimestamp2local($intDay)); ?>">
                                <?php if ($this->arrOptions['booDisplayDaysLabel']) { ?>
                                <div class="p4-cal-daylabel"><span class="p4-cal-dow"><?php echo str::htmlentities(mb_substr($strDay, 0, 3)); ?>.</span> <span class="p4-cal-dnum"><?php echo date('j', $intDay); ?></span><span class="p4-cal-dmonth">/<?php echo date('m', $intDay); ?></span></div>
                                <?php } ?>
                                <?php if ($booChanLabels) { ?>
                                <div class="p4-cal-chanheads">
                                    <?php foreach ($arrChannels as $objChannel) { $strColor = self::_cssColor($objChannel->strColor); ?>
                                    <span class="p4-cal-chanhead"<?php if ($strColor != '') echo ' style="--p4-cal-ev:'.str::htmlentities($strColor).';"'; ?> title="<?php echo str::htmlentities($objChannel->strTitle); ?>"><?php echo str::htmlentities($objChannel->strTitle); ?></span>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php } ?>

                    <div class="p4-cal-body">
                        <div class="p4-cal-hours" aria-hidden="true">
                            <?php for ($h = $intHourBegin; $h < $intHourEnd; $h++) { ?><div class="p4-cal-hour"><span><?php printf('%02d:00', $h); ?></span></div><?php } ?>
                        </div>
                        <?php
                        foreach ($arrDays as $intDay)
                        {
                            $strKey = date('Ymd', $intDay);
                            ?>
                            <div class="p4-cal-day<?php echo self::_dayClass($intDay); ?>" data-calendar-date="<?php echo $strKey; ?>">
                                <?php
                                foreach ($arrChannels as $strChannelId => $objChannel)
                                {
                                    $strChannelId = (string) $strChannelId;

                                    // Heures de début et de fin de chaque événement dans la journée, bornées à l'affichage
                                    $arrItems = array();
                                    if (!empty($arrEvents[$strKey][$strChannelId]))
                                    {
                                        foreach ($arrEvents[$strKey][$strChannelId] as $intId)
                                        {
                                            if (empty($this->arrEvents[$intId])) continue;
                                            $objEvent = $this->arrEvents[$intId];

                                            $intB = date::timestamp2unixtimestamp($objEvent->intTimestpBegin);
                                            $intE = date::timestamp2unixtimestamp($objEvent->intTimestpEnd);
                                            $floBegin = substr($objEvent->intTimestpBegin, 0, 8) == $strKey ? date('G', $intB) + intval(date('i', $intB), 10) / 60 : 0;
                                            $floEnd = substr($objEvent->intTimestpEnd, 0, 8) == $strKey ? date('G', $intE) + intval(date('i', $intE), 10) / 60 : 24;

                                            $floBegin = min(max($floBegin, $intHourBegin), $intHourEnd);
                                            $floEnd = min(max($floEnd, $intHourBegin), $intHourEnd);
                                            if ($floEnd < $floBegin) $floEnd = $floBegin;

                                            $arrItems[$intId] = array($floBegin, $floEnd);
                                        }
                                    }
                                    $arrLanes = self::_lanes($arrItems);
                                    ?>
                                    <div class="p4-cal-chan">
                                        <?php
                                        foreach ($arrItems as $intId => $arrItem)
                                        {
                                            $objEvent = $this->arrEvents[$intId];
                                            list($intLane, $intLanes) = $arrLanes[$intId];

                                            // Événements simultanés en cascade : chacun décalé (au plus 28 % de la
                                            // colonne) et étendu jusqu'au bord droit, les suivants au-dessus ; le
                                            // dernier garde ainsi une largeur lisible
                                            $floLeft = $intLane * min(100 / $intLanes, 28);
                                            $floWidth = 100 - $floLeft;
                                            $strStyle = sprintf('top:%.4F%%;height:%.4F%%;left:%.4F%%;width:%.4F%%;z-index:%d;',
                                                ($arrItem[0] - $intHourBegin) / $intNbHours * 100,
                                                ($arrItem[1] - $arrItem[0]) / $intNbHours * 100,
                                                $floLeft,
                                                $floWidth,
                                                1 + $intLane
                                            );
                                            $strColor = self::_cssColor($objEvent->strColor);
                                            if ($strColor != '') $strStyle .= '--p4-cal-ev:'.$strColor.';';

                                            $arrDrop = $objEvent->arrOnDrop;
                                            $strDrop = empty($arrDrop) ? '' : ' data-calendar-drop="'.str::htmlentities($arrDrop['url']).'" data-calendar-drop-target="'.str::htmlentities($arrDrop['element_id']).'"';

                                            $strTitle = (string) $objEvent->strTitle;
                                            $arrCloseAttrs = $objEvent->arrCloseAttrs;
                                            list($strOpen, $strClose) = self::_eventLink($objEvent, 'p4-cal-ev-body', $strTitle == '' ? ' title="'.str::htmlentities(self::_eventText($objEvent, '<time_begin> – <time_end>')).'"' : '');
                                            ?>
                                            <div class="p4-cal-ev<?php if (!empty($arrDrop)) echo ' is-draggable'; ?>" style="<?php echo str::htmlentities($strStyle); ?>"<?php echo $strDrop; ?>>
                                                <?php if ($strTitle != '' || !empty($arrCloseAttrs) || !is_null($objEvent->strOnClose)) { ?>
                                                <div class="p4-cal-ev-head">
                                                    <span class="p4-cal-ev-time"><?php echo self::_eventText($objEvent, $strTitle); ?></span>
                                                    <?php
                                                    if (!empty($arrCloseAttrs)) echo '<a href="#" class="p4-cal-ev-close" title="Supprimer" aria-label="Supprimer"'.skin::treeview_attrs($arrCloseAttrs).'>×</a>';
                                                    elseif (!is_null($objEvent->strOnClose)) echo '<a href="javascript:void(0);" class="p4-cal-ev-close" title="Supprimer" aria-label="Supprimer" onclick="javascript:'.$objEvent->strOnClose.';">×</a>';
                                                    ?>
                                                </div>
                                                <?php } ?>
                                                <?php echo $strOpen.self::_eventText($objEvent, $objEvent->strContent).$strClose; ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }

                                // Heure courante
                                if ($strKey == date('Ymd'))
                                {
                                    $floNow = date('G') + intval(date('i'), 10) / 60;
                                    if ($floNow >= $intHourBegin && $floNow <= $intHourEnd) printf('<div class="p4-cal-now" style="top:%.4F%%;" aria-hidden="true"></div>', ($floNow - $intHourBegin) / $intNbHours * 100);
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu moderne, mois : semaines en lignes, événements en pastilles
     *
     * @param array $arrEvents événements par jour et par canal (_prepare_events)
     */

    protected function _modernMonth($arrEvents)
    {
        global $ploopi_days;

        $intMonth = (int) $this->arrOptions['intMonth'];
        $intYear = (int) $this->arrOptions['intYear'];

        // Du lundi de la première semaine au dimanche de la dernière
        $intFirst = mktime(0, 0, 0, $intMonth, 1, $intYear);
        $intStart = mktime(0, 0, 0, $intMonth, 2 - date('N', $intFirst), $intYear);
        $intLast = mktime(0, 0, 0, $intMonth + 1, 0, $intYear);
        $intNbWeeks = (int) ceil(((int) round(($intLast - $intStart) / 86400) + 1) / 7);

        $booWeeks = (bool) $this->arrOptions['booDisplayNumWeeks'];
        $intRowHeight = max(96, min(220, (int) floor(((int) $this->intHeight - 40) / max(1, $intNbWeeks))));

        ?>
        <div class="p4-cal p4-cal-month<?php if ($booWeeks) echo ' has-weeks'; ?>" id="calendar" style="--p4-cal-row:<?php echo $intRowHeight; ?>px;">
            <?php if ($this->arrOptions['booDisplayDaysLabel']) { ?>
            <div class="p4-cal-mhead">
                <?php if ($booWeeks) echo '<span class="p4-cal-wnum" title="Semaine">S.</span>'; ?>
                <?php for ($d = 1; $d <= 7; $d++) echo '<span title="'.str::htmlentities($ploopi_days[$d]).'">'.str::htmlentities(mb_substr($ploopi_days[$d], 0, 3)).'.</span>'; ?>
            </div>
            <?php } ?>
            <?php
            for ($w = 0; $w < $intNbWeeks; $w++)
            {
                $intMonday = mktime(0, 0, 0, date('n', $intStart), date('j', $intStart) + $w * 7, date('Y', $intStart));
                ?>
                <div class="p4-cal-week">
                    <?php if ($booWeeks) echo '<span class="p4-cal-wnum">'.date('W', $intMonday).'</span>'; ?>
                    <?php
                    for ($d = 0; $d < 7; $d++)
                    {
                        $intDay = mktime(0, 0, 0, date('n', $intMonday), date('j', $intMonday) + $d, date('Y', $intMonday));
                        $strKey = date('Ymd', $intDay);
                        ?>
                        <div class="p4-cal-mday<?php echo self::_dayClass($intDay, date('n', $intDay) != $intMonth); ?>" data-calendar-date="<?php echo $strKey; ?>" title="<?php echo str::htmlentities(date::unixtimestamp2local($intDay)); ?>">
                            <span class="p4-cal-mnum"><?php echo date('j', $intDay); ?></span>
                            <?php
                            if (!empty($arrEvents[$strKey]))
                            {
                                echo '<div class="p4-cal-chips">';
                                foreach ($arrEvents[$strKey] as $arrIds)
                                {
                                    foreach ($arrIds as $intId)
                                    {
                                        if (empty($this->arrEvents[$intId])) continue;
                                        $objEvent = $this->arrEvents[$intId];
                                        $strColor = self::_cssColor($objEvent->strColor);
                                        list($strOpen, $strClose) = self::_eventLink($objEvent, 'p4-cal-chip', $strColor == '' ? '' : ' style="--p4-cal-ev:'.str::htmlentities($strColor).';"');
                                        echo $strOpen.self::_eventText($objEvent, $objEvent->strContent).$strClose;
                                    }
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Affichage du calendrier multi-journées
     *
     */

    /**
     * Intitulé d'une colonne de jour, abrégé quand la colonne est étroite (planning en
     * largeur automatique sur petit écran) : « Mercredi 07/10 », « Mer 07/10 », « M 07 ».
     * L'intitulé abrégé porte l'intitulé complet en infobulle.
     *
     * @param string $strDay nom du jour
     * @param string $strDate date locale (jj/mm) ou vide
     * @param int $intDayWidth largeur de la colonne en pixels
     * @return string code html de l'intitulé
     */

    private function _dayLabel($strDay, $strDate, $intDayWidth)
    {
        $strFull = trim($strDay.' '.$strDate);

        if ($intDayWidth >= 80) $strLabel = $strFull;
        elseif ($intDayWidth >= 55) $strLabel = trim(mb_substr($strDay, 0, 3).' '.$strDate);
        else $strLabel = trim(mb_substr($strDay, 0, 1).' '.substr($strDate, 0, 2));

        return '<div class="day_header_label"'.($strLabel == $strFull ? '' : ' title="'.str::htmlentities($strFull).'"').'>'.str::htmlentities($strLabel).'</div>';
    }

    private function _displayDays()
    {
        global $ploopi_days;

        // Préparation des événéments
        $arrEvents = $this->_prepare_events();

        // 1er jour de l'intervalle (timestp unix)
        $firstday = date::timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateBegin']));

        // jour/mois/année du 1er jour
        $firstday_d = date('j', $firstday);
        $firstday_m = date('n', $firstday);
        $firstday_y = date('Y', $firstday);

        // Dernier jour de l'intervalle (timestp unix)
        $lastday = date::timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateEnd']));

        // Nombre de jours dans l'intervalles (bornes comprises)
        $intNbDays = floor(($lastday - $firstday) / 86400) + 1;

        // Nombre de canaux par jours
        $intNbChan = sizeof($this->arrChannels);
        if (!$intNbChan) $intNbChan = 1;

        // Largeur total du planning = (NbJours * NbCanaux * LargeurCanal) + LargeurColonneHeures + NbSeparateurs

        // Largeur d'un canal (sans bordure)
        $intChannelWidth = floor(($this->intWidth - $intNbDays*$intNbChan - ($this->arrOptions['intHoursColWidth'] * $this->arrOptions['booDisplayHours'])) / ($intNbDays*$intNbChan));

        // Nombre d'heures par jour
        $intNbHours = $this->arrOptions['intHourEnd'] - $this->arrOptions['intHourBegin'];

        // Hauteur entre chaque séparateur d'heure
        $intHourHeight = floor(($this->intHeight - ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']) - ($this->arrOptions['intChannelsLabelHeight'] * $this->arrOptions['booDisplayChannelsLabel'])) / $intNbHours);

        // Largeur d'une journée (incluant les bordures des canaux)
        $intDayWidth = ($intChannelWidth * $intNbChan) + ($intNbChan - 1);

        // Largeur du séparateur d'heure
        $intHourWidth = $intDayWidth;

        // Hauteur d'une journée
        $intDayHeight = $intHourHeight * $intNbHours;

        // Style du bloc "journée"
        $strDayStyle = "width:".$intDayWidth."px;height:".($intDayHeight - 1)."px;";

        // Style du bloc "heures" (entête des heures)
        $strHoursStyle = "width:".($this->arrOptions['intHoursColWidth'] - 1)."px;height:".($intDayHeight - 1)."px;";

        // Style des entêtes (heures, jours)
        $strDayHeaderStyle = "width:".$intDayWidth."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;";
        $strHourHeaderStyle = "height:".$intHourHeight."px;";
        $strChannelHeaderStyle = "width:".$intDayWidth."px;height:".($this->arrOptions['intChannelsLabelHeight'] - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $intCalendarWidth = $intNbDays * $intDayWidth + ($this->arrOptions['intHoursColWidth'] * $this->arrOptions['booDisplayHours']) + $intNbDays;
        $intCalendarHeight = $intDayHeight + ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']) + ($this->arrOptions['intChannelsLabelHeight'] * $this->arrOptions['booDisplayChannelsLabel']);

        // Chaîne contenant le code javascript à éxécuter (draggables/droppables/fonctions)
        $strJsCode = '';
        ?>
        <div class="days_inner" style=width:<?php echo $intCalendarWidth; ?>px;height:<?php echo $intCalendarHeight; ?>px;">

            <?php
            // Affichage des libellés de jours si demandé
            if ($this->arrOptions['booDisplayDaysLabel'])
            {
                ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection heures/jours)
                    if ($this->arrOptions['booDisplayHours'])
                    {
                        ?>
                        <div class="day_header" style="<?php echo "width:".($this->arrOptions['intHoursColWidth'] - 1)."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;"; ?>">&nbsp;</div>
                        <?php
                    }

                    // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                    for ($d = 1; $d <= $intNbDays; $d++)
                    {
                        // Détermination de la date du jour à afficher
                        $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                        // Date locale
                        $ldate = substr(date::unixtimestamp2local($dateday), 0, 5);

                        $weekday = date('N', $dateday);

                        $extra_class = '';
                        if (date::holiday($dateday)) $extra_class = ' day_header_holiday';
                        else
                        {
                            if ($weekday > 5) $extra_class = ' day_header_weekend';
                        }
                        ?>
                        <div class="day_header<?php echo $extra_class; ?>" style="<?php echo $strDayHeaderStyle; ?>">
                            <?php echo $this->_dayLabel($ploopi_days[$weekday], $ldate, $intDayWidth); ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            // Affichage des libellés de canaux si demandé
            if ($this->arrOptions['booDisplayChannelsLabel'])
            {
            ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection heures/jours)
                    if ($this->arrOptions['booDisplayHours'])
                    {
                        ?>
                        <div class="channel_header" style="font-size:0;<?php echo "width:".($this->arrOptions['intHoursColWidth'] - 1)."px;height:".($this->arrOptions['intChannelsLabelHeight'] - 1)."px;"; ?>">&nbsp;</div>
                        <?php
                    }

                    // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                    for ($d = 1; $d <= $intNbDays; $d++)
                    {
                        ?>
                        <div class="channel_header" style="<?php echo $strChannelHeaderStyle; ?>;">
                        <?php
                        // Affichage des Canaux
                        $intNumChan = 0;
                        $intLeft = 0;
                        foreach($this->arrChannels as $strChannelId => $objChannel)
                        {
                            $strBgColor = is_null($objChannel->strColor) ? '' : "background-color:{$objChannel->strColor};";
                            $channel_style = "{$strBgColor}width:{$intChannelWidth}px;height:".($this->arrOptions['intChannelsLabelHeight'] - 1)."px;left:{$intLeft}px;";
                            ?>

                            <div class="channel" style="<?php echo $channel_style; ?>"><?php echo str::htmlentities($objChannel->strTitle); ?></div>
                            <?php
                            // Position du prochain canal
                            $intLeft = $intLeft + $intChannelWidth + 1;
                            $intNumChan++;

                        }
                        ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            ?>
            <div class="row">
            <?php
            // Affichage des heures
            if ($this->arrOptions['booDisplayHours'])
            {
                ?>
                <div class="hours" style="<?php echo $strHoursStyle; ?>">
                    <?php
                    // Affichage des heures + demi-heures
                    for ($h = $this->arrOptions['intHourBegin']; $h < $this->arrOptions['intHourEnd']; $h++ )
                    {
                        ?>
                        <div class="hour_header" style="<?php echo $strHourHeaderStyle; ?>;">
                            <div class="hour_header_num"><?php echo sprintf("%02d", $h); ?></div>
                        </div>
                        <?php
                        // Affichage du séparateur d'heures
                        if ($h > $this->arrOptions['intHourBegin'])
                        {
                            ?>
                            <div class="tick" style="top:<?php echo $intHourHeight * ($h - $this->arrOptions['intHourBegin']); ?>px;width:<?php echo $this->arrOptions['intHoursColWidth']-1; ?>px;"></div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
            ?>
            </div>

            <div id="calendar_days" style="overflow:hidden;">
                <?php
                // Affichage des journées

                // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                for ($d = 1; $d <= $intNbDays; $d++)
                {
                    // Détermination de la date du jour à afficher
                    $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                    $extra_class = '';
                    if (date::holiday($dateday)) $extra_class = ' day_holiday';
                    else
                    {
                        $weekday = date('N', $dateday);
                        if ($weekday > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<?php echo $extra_class; ?>" id="calendar_day<?php echo $d; ?>" style="<?php echo $strDayStyle; ?>">
                        <?php
                        $c = 0;
                        // Affichage des heures + demi-heures
                        for ($h = $this->arrOptions['intHourBegin']; $h < $this->arrOptions['intHourEnd']; $h++ )
                        {
                            $intHourPx = $intHourHeight * ($h - $this->arrOptions['intHourBegin']);
                            $intHalfHourPx = floor($intHourPx + $intHourHeight / 2);
                            ?>
                            <?php
                            if ($h > $this->arrOptions['intHourBegin'])
                            {
                                ?>
                                <div class="tick" style="top:<?php echo $intHourPx; ?>px;width:<?php echo $intDayWidth-1; ?>px;"></div>
                                <?php
                            }
                            ?>
                            <div class="tick-half" style="top:<?php echo $intHalfHourPx; ?>px;width:<?php echo $intDayWidth-1; ?>px;"></div>
                            <?php
                        }

                        // Clé de date pour lire dans le tableau des événements
                        $strEventsKey = sprintf("%04d%02d%02d",date('Y', $dateday), date('n', $dateday), date('j', $dateday));
                        $strJsCode .= "calendar_days[$d] = '{$strEventsKey}';";

                        // Affichage des Canaux
                        $intNumChan = 0;
                        $intLeft = 0;
                        foreach($this->arrChannels as $strChannelId => $row)
                        {
                            $channel_style = "width:".$intChannelWidth."px;height:".($intDayHeight - 1)."px;left:{$intLeft}px;";
                            // $intLeft = $intLeft + $intWidth + 2; // border
                            $strJsCode .= "calendar_channels[$intNumChan] = $intLeft;";

                            ?>

                            <div class="channel" id="calendar_channel<?php echo $d; ?>_<?php echo $intNumChan; ?>" style="<?php echo $channel_style; ?>">
                                <?php
                                if ($intNumChan > 0)
                                {
                                    ?><div class="channelborder" style="height:<?php echo $intDayHeight - 1; ?>px;"></div><?php
                                }

                                // Affichage des événements
                                if (!empty($arrEvents[$strEventsKey][$strChannelId]))
                                {
                                    foreach($arrEvents[$strEventsKey][$strChannelId] as $intId)
                                    {
                                        if (!empty($this->arrEvents[$intId]))
                                        {
                                            $arrDateBegin = date::timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                                            $arrDateEnd = date::timestamp2local($this->arrEvents[$intId]->intTimestpEnd);

                                            // Détermination heure de début (ajustement de l'heure de début en fonction de la date de l'événement)
                                            $intTsDateBegin = date::timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpBegin);
                                            $floTimeBegin = (substr($this->arrEvents[$intId]->intTimestpBegin, 0 ,8) == $strEventsKey) ? date('G', $intTsDateBegin) + (intval(date('i', $intTsDateBegin), 10) / 60) : 0 ;

                                            // Détermination heure de fin (ajustement de l'heure de fin en fonction de la date de l'événement)
                                            $intTsDateEnd = date::timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpEnd);
                                            $floTimeEnd = (substr($this->arrEvents[$intId]->intTimestpEnd, 0 ,8) == $strEventsKey) ? date('G', $intTsDateEnd) + (intval(date('i', $intTsDateEnd), 10) / 60) : 24;

                                            // On adapte ensuite les heures de début/fin aux limites d'affichage du planning
                                            if ($floTimeBegin < $this->arrOptions['intHourBegin']) $floTimeBegin = $this->arrOptions['intHourBegin'];
                                            if ($floTimeEnd > $this->arrOptions['intHourEnd']) $floTimeEnd = $this->arrOptions['intHourEnd'];

                                            // Durée de l'événement en heures
                                            $floTimeLength = $floTimeEnd - $floTimeBegin;

                                            // Début de l'événement en pix
                                            $intEventTop = floor(($floTimeBegin - $this->arrOptions['intHourBegin']) * $intHourHeight);

                                            // Hauteur de l'événement en pix
                                            $intEventHeight = floor($floTimeLength * $intHourHeight);

                                            ?>
                                            <div class="event" id="calendar_event<?php echo $intId; ?>" style="top:<?php echo $intEventTop; ?>px;height:<?php echo $intEventHeight - 1; ?>px;width:<?php echo $intChannelWidth; ?>px;background-color:<?php echo str::htmlentities($this->arrEvents[$intId]->strColor); ?>;">

                                                <?php if ($this->arrEvents[$intId]->strTitle != '') { ?>
                                                    <div class="event_title" id="calendar_event<?php echo $intId; ?>_handle"  style="overflow:hidden;height:16px;line-height:16px;<?php echo !is_null($this->arrEvents[$intId]->arrOnDrop) ? 'cursor:move;' : ''; ?>">
                                                        <?php
                                                        // Croix : attributs data-* (arrCloseAttrs, CSP stricte), ou code historique
                                                        // (strOnClose). Option lue d'abord : empty() sur une propriété magique
                                                        // interroge __isset(), que calendarEvent ne définit pas
                                                        $arrCloseAttrs = $this->arrEvents[$intId]->arrCloseAttrs;
                                                        if (!empty($arrCloseAttrs))
                                                        {
                                                            ?>
                                                            <a href="#"<?php echo skin::treeview_attrs($arrCloseAttrs); ?>><img align="right" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/close.png" /></a>
                                                            <?php
                                                        }
                                                        elseif (!is_null($this->arrEvents[$intId]->strOnClose))
                                                        {
                                                            ?>
                                                            <a href="javascript:void(0);" onclick="javascript:<?php echo $this->arrEvents[$intId]->strOnClose; ?>;"><img align="right" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/close.png" /></a>
                                                            <?php
                                                        }
                                                        ?>
                                                        <span><?php
                                                            echo str_replace(
                                                                array('<date_begin>', '<date_end>', '<time_begin>', '<time_end>'),
                                                                array(substr($arrDateBegin['date'], 0, 5), substr($arrDateEnd['date'], 0, 5), substr($arrDateBegin['time'], 0, 5), substr($arrDateEnd['time'], 0, 5)),
                                                                $this->arrEvents[$intId]->strTitle
                                                            );
                                                        ?></span>
                                                    </div>
                                                <?php } ?>

                                                <a class="event_inner" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } echo skin::treeview_attrs($this->arrEvents[$intId]->arrAttrs); ?> style="height:<?php echo $intEventHeight - 20; ?>px;<?php if (!empty($this->arrEvents[$intId]->strStyle)) echo $this->arrEvents[$intId]->strStyle; ?>">
                                                    <?php
                                                    echo str_replace(
                                                        array('<date_begin>', '<date_end>', '<time_begin>', '<time_end>'),
                                                        array(substr($arrDateBegin['date'], 0, 5), substr($arrDateEnd['date'], 0, 5), substr($arrDateBegin['time'], 0, 5), substr($arrDateEnd['time'], 0, 5)),
                                                        $this->arrEvents[$intId]->strContent
                                                    );
                                                    ?>
                                                </a>
                                            </div>
                                            <?php
                                            // Paramètres ondrop de l'événement
                                            if (!is_null($this->arrEvents[$intId]->arrOnDrop))
                                            {
                                                // Création du draggable (événement)
                                                $strJsCode .= "jQuery('#calendar_event{$intId}').draggable({
                                                    zindex: 99999,
                                                    handle: '#calendar_event{$intId}_handle',
                                                    snap: '.tick,.tick-half,.day',
                                                    snapMode: 'inner',
                                                    grid: [ {$intDayWidth}, ".($intHourHeight / 2)." ],
                                                    containment : '#calendar_days'
                                                });";

                                                $strJsCode .= "calendar_events[{$intId}] = ".json_encode(array((string) $intNumChan, (string) $this->arrEvents[$intId]->arrOnDrop['url'], (string) $this->arrEvents[$intId]->arrOnDrop['element_id']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).";";
                                            }
                                        }
                                    }
                                }
                                ?>
                            </div>

                            <?php
                            // Position du prochain canal
                            $intLeft = $intLeft + $intChannelWidth + 1;
                            $intNumChan++;
                        }
                        ?>
                    </div>
                    <?php





                    // Création du droppable (jour)
                    //$strJsCode .= "Droppables.add('calendar_day{$d}', { accept: 'event', onHover: calendar_drop_onhover });";
                    $strJsCode .= "jQuery('#calendar_day{$d}').droppable({
                        over: function(event, ui) {
                        },
                        drop: function(event, ui) {
                            var droppable = this
                            var day = droppable.id.substring(12,13);
                            var event = ui.draggable[0].id.substring(14,15);

                            // On calcule la demi-heure la plus proche en fonction des coordonnées

                            var top = parseInt(ui.draggable.position().top);
                            var hour = Math.round((calendar_h_begin + top / calendar_h_height)*2)/2;

                            // Enregistrement de la nouvelle position de l'événement, retour vers l'application métier
                            ploopi.xhr.todiv(calendar_events[event][1], 'calendar_event_date='+calendar_days[day]+'&calendar_event_hour='+hour, calendar_events[event][2]);

                        }
                    });";
                }
                ?>
            </div>
        </div>

        <?php // Script produit par le serveur : nonce (CSP stricte) ?>
        <script nonce="<?php echo csp::nonce(); ?>">
            var calendar_lastdroppable = null;
            var calendar_days = [];
            var calendar_events = [];
            var calendar_channels = [];
            var calendar_h_begin = <?php echo $this->arrOptions['intHourBegin']; ?>;
            var calendar_h_height = <?php echo $intHourHeight; ?>;

            <?php echo $strJsCode; ?>
        </script>
        <?php
    }

    /**
     * Affichage du calendrier mensuel
     *
     */

    private function _displayMonth()
    {
        global $ploopi_days;

        // Préparation des événéments
        $arrEvents = $this->_prepare_events();

        // 1er jour du mois (timestp unix)
        $firstday = mktime(0, 0, 0, $this->arrOptions['intMonth'], 1, $this->arrOptions['intYear']);

        // dernier jour du mois (timestp unix)
        $lastday = mktime(0, 0, 0, $this->arrOptions['intMonth']+1, 0, $this->arrOptions['intYear']);

        // Jour de la semaine où tombe le 1er jour du mois : 1 - 7
        $weekday = $firstweekday = date('N', $firstday);

        // Jour de la semaine où tombe le dernier jour du mois : 1 - 7
        $lastweekday = date('N', $lastday);

        // Nombre de jours dans le mois : 0 - 31
        $intNbDays = date('t', $firstday);

        // Nombre de semaines dans le mois (entamées)
        $intNbWeeks = floor($intNbDays / 7) + ($intNbDays % 7 > 0) + ($firstweekday > $lastweekday);

        // Style (hauteur/largeur) du jour
        $intDayWidth = floor(($this->intWidth - ($this->arrOptions['intNumWeeksColWidth'] * $this->arrOptions['booDisplayNumWeeks'])) / 7);
        $intDayHeight = floor(($this->intHeight - ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel'])) / $intNbWeeks);
        $strDayStyle = "width:".($intDayWidth - 1)."px;height:".($intDayHeight - 1)."px;";

        // Style des entêtes (semaines, jours)
        $strDayHeaderStyle = "width:".($intDayWidth - 1)."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;";
        $strWeekHeaderStyle = "width:".($this->arrOptions['intNumWeeksColWidth'] - 1)."px;height:".($intDayHeight - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $intCalendarWidth = ($intDayWidth * 7) + ($this->arrOptions['intNumWeeksColWidth'] * $this->arrOptions['booDisplayNumWeeks']);
        $intCalendarHeight = ($intDayHeight * $intNbWeeks) + ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']);
        ?>
        <div class="month_inner" style=width:<?php echo $intCalendarWidth; ?>px;height:<?php echo $intCalendarHeight; ?>px;">
            <?php
            // Affichage des libellés de jours si demandé
            if ($this->arrOptions['booDisplayDaysLabel'])
            {
                ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection semaines/jours)
                    if ($this->arrOptions['booDisplayNumWeeks'])
                    {
                        ?>
                        <div class="day_header" style="<?php echo "width:".($this->arrOptions['intNumWeeksColWidth'] - 1)."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;"; ?>">&nbsp;</div>
                        <?php
                    }

                    for ($d=1; $d<=7; $d++)
                    {
                        ?>
                        <div class="day_header" style="<?php echo $strDayHeaderStyle; ?>">
                            <?php echo $this->_dayLabel($ploopi_days[$d], '', $intDayWidth); ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            // Boucle n°1 : Si le 1er jour du mois n'est pas un lundi, on affiche la fin du mois précédent
            if ($weekday > 1)
            {
                // Numéro de la semaine
                $w = date('W', $firstday);
                ?>
                <div class="row">
                <?php
                if ($this->arrOptions['booDisplayNumWeeks'])
                {
                    ?>
                    <div class="week_header" style="<?php echo $strWeekHeaderStyle; ?>">
                        <div class="week_header_num"><?php echo $w; ?></div>
                    </div>
                    <?php
                }

                for ($c = 1; $c < $weekday; $c++)
                {
                    $strTs = mktime(0, 0, 0, $this->arrOptions['intMonth'], 1+$c-$weekday, $this->arrOptions['intYear']);

                    // Jour du mois
                    $d = date('j', $strTs);

                    // Date au format local
                    $date = substr(date::unixtimestamp2local($strTs), 0, 10);

                    $extra_class = '';
                    if (date::holiday($strTs)) $extra_class = ' day_holiday';
                    else
                    {
                        if ($c > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<?php echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
                        <div class="day_num_grayed"><?php echo $d; ?></div>
                        <?php
                        $strEventsKey = substr(date::unixtimestamp2timestamp($strTs), 0, 8);
                        if (!empty($arrEvents[$strEventsKey])) $this->_display_month_events($arrEvents[$strEventsKey]);
                        ?>
                    </div>
                    <?php
                }
            }

            // Boucle n°2 : tous les jours du mois
            for ($d = 1; $d <= date('t', $firstday) ; $d++)
            {
                // Arrivé en fin de semaine, on se repositionne au début
                if ($weekday == 8) $weekday = 1;

                // Chaque début de semaine = une nouvelle ligne
                if ($weekday == 1)
                {
                    // Numéro de la semaine
                    $w = date('W', mktime(0, 0, 0, $this->arrOptions['intMonth'], $d,  $this->arrOptions['intYear']));
                    ?>
                    <div class="row">
                    <?php
                    if ($this->arrOptions['booDisplayNumWeeks'])
                    {
                        ?>
                        <div class="week_header" style="<?php echo $strWeekHeaderStyle; ?>">
                            <div class="week_header_num"><?php echo $w; ?></div>
                        </div>
                        <?php
                    }
                }

                // Date au format local
                $date = current(date::timestamp2local($ts = sprintf("%04d%02d%02d000000",$this->arrOptions['intYear'], $this->arrOptions['intMonth'], $d)));
                $dateday = date::timestamp2unixtimestamp($ts);

                $extra_class = '';
                if (date::holiday($dateday)) $extra_class = ' day_holiday';
                else
                {
                    if ($weekday > 5) $extra_class = ' day_weekend';
                }
                ?>
                <div class="day<?php echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
                    <div class="day_num"><?php echo $d; ?></div>
                    <?php
                    $strEventsKey = sprintf("%04d%02d%02d",$this->arrOptions['intYear'], $this->arrOptions['intMonth'], $d);
                    if (!empty($arrEvents[$strEventsKey])) $this->_display_month_events($arrEvents[$strEventsKey]);
                    ?>
                </div>
                <?php
                // Si fin de semaine, fin de ligne
                if ($weekday == 7) echo '</div>';

                $weekday++;
            }

            // Boucle n°3 : Si le mois ne se termine pas un dimanche, on affiche le début du mois suivant
            if ($weekday <= 7)
            {
                for ($c = $weekday; $c <= 7 ; $c++)
                {
                    $strTs = mktime(0, 0, 0, $this->arrOptions['intMonth']+1, 1+$c-$weekday, $this->arrOptions['intYear']);

                    // Jour du mois
                    $d = date('j', $strTs);

                    // Date au format local
                    $date = substr(date::unixtimestamp2local($strTs), 0, 10);

                    $extra_class = '';
                    if (date::holiday($strTs)) $extra_class = ' day_holiday';
                    else
                    {
                        if ($c > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<?php echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
                        <div class="day_num_grayed"><?php echo $d; ?></div>
                        <?php
                        $strEventsKey = substr(date::unixtimestamp2timestamp($strTs), 0, 8);
                        if (!empty($arrEvents[$strEventsKey])) $this->_display_month_events($arrEvents[$strEventsKey]);
                        ?>
                        </div>
                    <?php
                }

                echo '</div>';
            }
        ?>
        </div>
        <?php // month_inner
    }

    /**
     * Affiche un événement dans le planning mensuel
     *
     * @param int $arrIdEvents id de l'événement
     */

    protected function _display_month_events($arrIdEvents)
    {
        foreach($arrIdEvents as $strChannelId => $arrEvents)
        {
            foreach($arrEvents as $intId)
            {
                if (!empty($this->arrEvents[$intId]))
                {
                    $arrDateBegin = date::timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                    $arrDateEnd = date::timestamp2local($this->arrEvents[$intId]->intTimestpEnd);
                    ?>
                    <a class="event" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } echo skin::treeview_attrs($this->arrEvents[$intId]->arrAttrs); ?>>
                        <div class="event_inner" style="background-color:<?php echo str::htmlentities($this->arrEvents[$intId]->strColor); ?>;" <?php if (!empty($this->arrEvents[$intId]->strStyle)) {?>style="<?php echo $this->arrEvents[$intId]->strStyle; ?>"<?php } ?>>
                            <?php
                            echo str_replace(
                                array('<date_begin>', '<date_end>', '<time_begin>', '<time_end>'),
                                array(substr($arrDateBegin['date'], 0, 5), substr($arrDateEnd['date'], 0, 5), substr($arrDateBegin['time'], 0, 5), substr($arrDateEnd['time'], 0, 5)),
                                $this->arrEvents[$intId]->strContent
                            );
                            ?>
                        </div>
                    </a>
                    <?php
                }
            }
        }
    }

    /**
     * Prépare les événement en les répartissant par jour dans un tableau associatif
     *
     * @return array événéments (id) par jour  (clé : AAAAMMJJ)
     */

    protected function _prepare_events()
    {
        $arrEvents = array();

        // Préparation des événements à afficher (on va les ranger jour par jour)
        foreach($this->arrEvents as $key => $objEvent)
        {
            // Vérification de l'intégrité
            if ($objEvent->intTimestpBegin <= $objEvent->intTimestpEnd)
            {
                $currentday = substr($objEvent->intTimestpBegin, 0, 8).'000000';
                // Si l'événement tient sur plusieurs jours on l'affecte pour chaque jour
                do {
                    $arrEvents[substr($currentday, 0, 8)][$objEvent->strChannelId][] = $key;
                    $currentday = date::timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $objEvent->intTimestpEnd);
            }

        }

        return $arrEvents;
    }


    /**
     * Prépare les événement en les répartissant par jour dans un tableau associatif
     *
     * @return array événéments (id) par jour  (clé : AAAAMMJJ)
     */

    protected function _prepare_events_OLD()
    {
        $arrEvents = array();

        // Préparation des événements à afficher (on va les ranger jour par jour)
        foreach($this->arrEvents as $key => $objEvent)
        {
            // Vérification de l'intégrité
            if ($objEvent->intTimestpBegin <= $objEvent->intTimestpEnd)
            {
                $currentday = substr($objEvent->intTimestpBegin, 0, 8).'000000';
                // Si l'événement tient sur plusieurs jours on l'affecte pour chaque jour
                do {
                    $arrEvents[substr($currentday, 0, 8)][] = $key;
                    $currentday = date::timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $objEvent->intTimestpEnd);
            }

        }

        return $arrEvents;
    }
}
