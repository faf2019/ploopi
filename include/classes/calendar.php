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

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Gestion de calendrier
 *
 * @package ploopi
 * @subpackage calendar
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */


/**
 * Classe de gestion des �v�nements du calendrier
 */
class calendarEvent
{
    /**
     * Heure de d�but au format timestamp
     */
    private $intTimestpBegin;

    /**
     * Heure de fin au format timestamp
     */
    private $intTimestpEnd;

    /**
     * Titre
     */
    private $strTitle;

    /**
     * Contenu
     */
    private $strContent;

    /**
     * Canal de rattachement
     */
    private $strChannelId;

    /**
     * Options de l'�v�nement
     *
     * @var array
     */

    private $arrOptions;


    /**
     * Constructeur de la classe
     *
     * @param int $intTimestpBegin Heure de d�but au format timestamp
     * @param int $intTimestpEnd Heure de fin au format timestamp
     * @param string $strTitle Titre
     * @param string $strContent Contenu
     * @param string $strChannelId Id du canal de rattachement
     * @param array $arrOptions sarray('strColor', 'strOnClick', 'strHref', 'strOnClose', 'strStyle')
     * @return calendarEvent
     *
     * Informations d�taill�es pour $arrOption :
     * string 'strColor' Couleur au format #RRGGBB
     * string 'strLabel' Contenu � afficher au survol (popup)
     * string 'strOnClick' Fonction javascript � ex�cuter sur l'�v�nement "onclick"
     * string 'strHref' Lien href sur l'�v�nement
     * string 'strOnClose' Fonction javascript � ex�cuter sur l'�v�nement "onclose"
     * string 'strStyle' Styles compl�mentaires � appliquer
     */

    public function __construct($intTimestpBegin, $intTimestpEnd, $strTitle, $strContent, $strChannelId = null, $arrOptions = array())
    {
        $this->intTimestpBegin = $intTimestpBegin;
        $this->intTimestpEnd = $intTimestpEnd;
        $this->strTitle = $strTitle;
        $this->strContent = $strContent;
        $this->strChannelId = $strChannelId;

        $this->arrOptions = array(
            'strColor' => null,
            'strLabel' => null,
            'strHref' => null,
            'strOnClick' => null,
            'strOnClose' => null,
            'arrOnDrop' => null,
            'strStyle' => null
        );

        $this->setOptions($arrOptions);
    }

    /**
     * Permet de d�finir les options :
     *
     * @param array $arrOptions tableau des options � modifier
     */

    public function setOptions($arrOptions)
    {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }

    public function getOptions()
    {
        return $this->arrOptions;
    }

    /**
     * Getter par d�faut
     *
     * @param string $strName nom de la propri�t� � lire
     * @return string valeur de la propri�t� si elle existe
     */
    public function __get($strName)
    {
        if (isset($this->{$strName})) return $this->{$strName};
        elseif (isset($this->arrOptions[$strName])) return $this->arrOptions[$strName];
        else return null;
    }
}

/**
 * Classe de gestion des canaux du calendrier
 */

class calendarChannel
{
    /**
     * Titre
     */
    private $strTitle;

    /**
     * Couleur
     */
    private $strColor;


    /**
     * Constructeur de la classe
     *
     * @param string $strTitle Titre
     * @param string $strColor Couleur
     *
     * @return calendarChannel
     */
    public function __construct($strTitle = null, $strColor = null)
    {
        $this->strTitle = $strTitle;
        $this->strColor = $strColor;
    }

    /**
     * Getter par d�faut
     *
     * @param string $strName nom de la propri�t� � lire
     * @return string valeur de la propri�t� si elle existe
     */
    public function __get($strName)
    {
        if (isset($this->{$strName})) return $this->{$strName};
        else return null;
    }
}

/**
 * Classe d'affichage d'un calendrier/agenda
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
     * Ev�nements du calendrier
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
            'strClassName' => 'ploopi_calendar_'.($strDisplayType == 'month' ? 'm' : 'd'), // class de style (css) utilis�e

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
     * Permet de d�finir les options d'affichage :
     *
     * string 'strClassName' class de rendu (defaut: ploopi_calendar_m ou ploopi_calendar_d en fonction du type de planning monthly/daily)
     * integer 'intMonth' mois � afficher pour l'affichage mensuel
     * integer 'intYear' ann�e � afficher
     * string 'strDateBegin' date de d�but
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
     * @param array $arrOptions tableau des options � modifier
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
     * Affichage du calendrier multi-journ�es
     *
     */

    private function _displayDays()
    {
        global $ploopi_days;

        // Pr�paration des �v�n�ments
        $arrEvents = $this->_prepare_events();

        // 1er jour de l'intervalle (timestp unix)
        $firstday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateBegin']));

        // jour/mois/ann�e du 1er jour
        $firstday_d = date('j', $firstday);
        $firstday_m = date('n', $firstday);
        $firstday_y = date('Y', $firstday);

        // Dernier jour de l'intervalle (timestp unix)
        $lastday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['strDateEnd']));

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

        // Hauteur entre chaque s�parateur d'heure
        $intHourHeight = floor(($this->intHeight - ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']) - ($this->arrOptions['intChannelsLabelHeight'] * $this->arrOptions['booDisplayChannelsLabel'])) / $intNbHours);

        // Largeur d'une journ�e (incluant les bordures des canaux)
        $intDayWidth = ($intChannelWidth * $intNbChan) + ($intNbChan - 1);

        // Largeur du s�parateur d'heure
        $intHourWidth = $intDayWidth;

        // Hauteur d'une journ�e
        $intDayHeight = $intHourHeight * $intNbHours;

        // Style du bloc "journ�e"
        $strDayStyle = "width:".$intDayWidth."px;height:".($intDayHeight - 1)."px;";

        // Style du bloc "heures" (ent�te des heures)
        $strHoursStyle = "width:".($this->arrOptions['intHoursColWidth'] - 1)."px;height:".($intDayHeight - 1)."px;";

        // Style des ent�tes (heures, jours)
        $strDayHeaderStyle = "width:".$intDayWidth."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;";
        $strHourHeaderStyle = "height:".$intHourHeight."px;";
        $strChannelHeaderStyle = "width:".$intDayWidth."px;height:".($this->arrOptions['intChannelsLabelHeight'] - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $intCalendarWidth = $intNbDays * $intDayWidth + ($this->arrOptions['intHoursColWidth'] * $this->arrOptions['booDisplayHours']) + $intNbDays;
        $intCalendarHeight = $intDayHeight + ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']) + ($this->arrOptions['intChannelsLabelHeight'] * $this->arrOptions['booDisplayChannelsLabel']);

        // Cha�ne contenant le code javascript � �x�cuter (draggables/droppables/fonctions)
        $strJsCode = '';
        ?>
        <div class="days_inner" style=width:<?php echo $intCalendarWidth; ?>px;height:<?php echo $intCalendarHeight; ?>px;">

            <?php
            // Affichage des libell�s de jours si demand�
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

                    // On boucle sur les jours � afficher (1 = premier jour de l'intervalle)
                    for ($d = 1; $d <= $intNbDays; $d++)
                    {
                        // D�termination de la date du jour � afficher
                        $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                        // Date locale
                        $ldate = substr(ploopi_unixtimestamp2local($dateday), 0, 5);

                        $weekday = date('N', $dateday);

                        $extra_class = '';
                        if (ploopi_holiday($dateday)) $extra_class = ' day_header_holiday';
                        else
                        {
                            if ($weekday > 5) $extra_class = ' day_header_weekend';
                        }
                        ?>
                        <div class="day_header<? echo $extra_class; ?>" style="<?php echo $strDayHeaderStyle; ?>">
                            <div class="day_header_label"><?php echo $ploopi_days[$weekday].' '.$ldate; ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            // Affichage des libell�s de canaux si demand�
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

                    // On boucle sur les jours � afficher (1 = premier jour de l'intervalle)
                    for ($d = 1; $d <= $intNbDays; $d++)
                    {
                        ?>
                        <div class="channel_header" style="<?php echo $strChannelHeaderStyle; ?>;">
                        <?
                        // Affichage des Canaux
                        $intNumChan = 0;
                        $intLeft = 0;
                        foreach($this->arrChannels as $strChannelId => $objChannel)
                        {
                            $strBgColor = is_null($objChannel->strColor) ? '' : "background-color:{$objChannel->strColor};";
                            $channel_style = "{$strBgColor}width:{$intChannelWidth}px;height:".($this->arrOptions['intChannelsLabelHeight'] - 1)."px;left:{$intLeft}px;";
                            ?>

                            <div class="channel" style="<?php echo $channel_style; ?>"><? echo ploopi_htmlentities($objChannel->strTitle); ?></div>
                            <?
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
                        // Affichage du s�parateur d'heures
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
                // Affichage des journ�es

                // On boucle sur les jours � afficher (1 = premier jour de l'intervalle)
                for ($d = 1; $d <= $intNbDays; $d++)
                {
                    // D�termination de la date du jour � afficher
                    $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                    $extra_class = '';
                    if (ploopi_holiday($dateday)) $extra_class = ' day_holiday';
                    else
                    {
                        $weekday = date('N', $dateday);
                        if ($weekday > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<? echo $extra_class; ?>" id="calendar_day<? echo $d; ?>" style="<?php echo $strDayStyle; ?>">
                        <?php
                        $c = 0;
                        // Affichage des heures + demi-heures
                        for ($h = $this->arrOptions['intHourBegin']; $h < $this->arrOptions['intHourEnd']; $h++ )
                        {
                            $intHourPx = $intHourHeight * ($h - $this->arrOptions['intHourBegin']);
                            $intHalfHourPx = floor($intHourPx + $intHourHeight / 2);
                            ?>
                            <div class="tick" style="top:<?php echo $intHalfHourPx; ?>px;width:<?php echo $intDayWidth-1; ?>px;"></div>
                            <?
                            if ($h > $this->arrOptions['intHourBegin'])
                            {
                                ?>
                                <div class="tick-half" style="top:<?php echo $intHourPx; ?>px;width:<?php echo $intDayWidth-1; ?>px;"></div>
                                <?
                            }
                            ?>
                            <?php
                        }

                        // Cl� de date pour lire dans le tableau des �v�nements
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

                            <div class="channel" id="calendar_channel<? echo $d; ?>_<? echo $intNumChan; ?>" style="<?php echo $channel_style; ?>">
                                <?
                                if ($intNumChan > 0)
                                {
                                    ?><div class="channelborder" style="height:<? echo $intDayHeight - 1; ?>px;"></div><?
                                }

                                // Affichage des �v�nements
                                if (!empty($arrEvents[$strEventsKey][$strChannelId]))
                                {
                                    foreach($arrEvents[$strEventsKey][$strChannelId] as $intId)
                                    {
                                        if (!empty($this->arrEvents[$intId]))
                                        {
                                            $arrDateBegin = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                                            $arrDateEnd = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpEnd);

                                            // D�termination heure de d�but (ajustement de l'heure de d�but en fonction de la date de l'�v�nement)
                                            $intTsDateBegin = ploopi_timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpBegin);
                                            $floTimeBegin = (substr($this->arrEvents[$intId]->intTimestpBegin, 0 ,8) == $strEventsKey) ? date('G', $intTsDateBegin) + (intval(date('i', $intTsDateBegin), 10) / 60) : 0 ;

                                            // D�termination heure de fin (ajustement de l'heure de fin en fonction de la date de l'�v�nement)
                                            $intTsDateEnd = ploopi_timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpEnd);
                                            $floTimeEnd = (substr($this->arrEvents[$intId]->intTimestpEnd, 0 ,8) == $strEventsKey) ? date('G', $intTsDateEnd) + (intval(date('i', $intTsDateEnd), 10) / 60) : 24;

                                            // On adapte ensuite les heures de d�but/fin aux limites d'affichage du planning
                                            if ($floTimeBegin < $this->arrOptions['intHourBegin']) $floTimeBegin = $this->arrOptions['intHourBegin'];
                                            if ($floTimeEnd > $this->arrOptions['intHourEnd']) $floTimeEnd = $this->arrOptions['intHourEnd'];

                                            // Dur�e de l'�v�nement en heures
                                            $floTimeLength = $floTimeEnd - $floTimeBegin;

                                            // D�but de l'�v�nement en pix
                                            $intEventTop = floor(($floTimeBegin - $this->arrOptions['intHourBegin']) * $intHourHeight);

                                            // Hauteur de l'�v�nement en pix
                                            $intEventHeight = floor($floTimeLength * $intHourHeight);

                                            ?>
                                            <div class="event" id="calendar_event<? echo $intId; ?>" style="top:<?php echo $intEventTop; ?>px;height:<?php echo $intEventHeight - 1; ?>px;width:<?php echo $intChannelWidth; ?>px;background-color:<?php echo ploopi_htmlentities($this->arrEvents[$intId]->strColor); ?>;">

                                                <div class="event_title" id="calendar_event<? echo $intId; ?>_handle"  style="overflow:hidden;height:16px;line-height:16px;<? echo !is_null($this->arrEvents[$intId]->arrOnDrop) ? 'cursor:move;' : ''; ?>">
                                                    <?
                                                    if (!is_null($this->arrEvents[$intId]->strOnClose))
                                                    {
                                                        ?>
                                                        <a href="javascript:void(0);" onclick="javascript:<? echo $this->arrEvents[$intId]->strOnClose; ?>;"><img align="right" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/close.png" /></a>
                                                        <?
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
                                                <a class="event_inner" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } ?> style="height:<?php echo $intEventHeight - 20; ?>px;<?php if (!empty($this->arrEvents[$intId]->strStyle)) echo $this->arrEvents[$intId]->strStyle; ?>">
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
                                            // Param�tres ondrop de l'�v�nement
                                            if (!is_null($this->arrEvents[$intId]->arrOnDrop))
                                            {
                                                // Cr�ation du draggable (�v�nement)
                                                $strJsCode .= "new Draggable('calendar_event{$intId}', { handle: 'calendar_event{$intId}_handle', snap: calendar_drag_snap, onEnd: calendar_drag_onend });";
                                                $strJsCode .= "calendar_events[{$intId}] = ['{$intNumChan}', '{$this->arrEvents[$intId]->arrOnDrop['url']}','{$this->arrEvents[$intId]->arrOnDrop['element_id']}'];";
                                            }
                                        }
                                    }
                                }
                                ?>
                            </div>

                            <?
                            // Position du prochain canal
                            $intLeft = $intLeft + $intChannelWidth + 1;
                            $intNumChan++;
                        }
                        ?>
                    </div>
                    <?php
                    // Cr�ation du droppable (jour)
                    $strJsCode .= "Droppables.add('calendar_day{$d}', { accept: 'event', onHover: calendar_drop_onhover });";
                }
                ?>
            </div>
        </div>

        <script type="text/javascript">
            var calendar_lastdroppable = null;
            var calendar_days = [];
            var calendar_events = [];
            var calendar_channels = [];
            var calendar_h_begin = <? echo $this->arrOptions['intHourBegin']; ?>;
            var calendar_h_height = <? echo $intHourHeight; ?>;

            function calendar_drop_onhover(draggable, droppable, pcent) {
                if (droppable != calendar_lastdroppable) droppable.highlight();
                calendar_lastdroppable = droppable;
            }

            function calendar_drop_ondrop(draggable, droppable) {
                // jour
                day = droppable.id.substring(12,13);
                // id de l'�v�nement
                event = draggable.id.substring(14,15);

                // On d�tache l'�v�nement du jour d'origine
                draggable.parentNode.removeChild(draggable);
                // On force l'alignement � gauche
                draggable.style.left = calendar_channels[calendar_events[event][0]]+'px';

                // On calcule la demi-heure la plus proche en fonction des coordonn�es
                top = parseInt(draggable.style.top);
                hour = Math.round((calendar_h_begin + top / calendar_h_height)*2)/2;
                // On calcule la nouvelle position en fonction de la demi-heure la plus proche
                draggable.style.top = (hour - calendar_h_begin)*calendar_h_height + 'px';
                // On attache l'�v�nement au nouveau jour
                droppable.appendChild(draggable);

                // Enregistrement de la nouvelle position de l'�v�nement, retour vers l'application m�tier
                ploopi_xmlhttprequest_todiv(calendar_events[event][1], 'calendar_event_date='+calendar_days[day]+'&calendar_event_hour='+hour, calendar_events[event][2]);
            }

            function calendar_drag_snap(x, y, draggable) {
                day = draggable.element.parentNode;
                days = $('calendar_days');

                // test haut
                if (y < 0) y = 0;
                // test bas
                if (y + draggable.element.getHeight() > day.getHeight()) y = day.getHeight() - draggable.element.getHeight();

                // test gauche
                min_x = - (day.cumulativeOffset().left - days.cumulativeOffset().left - 1);
                if (x < min_x) x = min_x;
                // test droite
                max_x = days.getWidth() - day.cumulativeOffset().left + days.cumulativeOffset().left - draggable.element.getWidth();

                if (x > max_x) x = max_x;

                return [ x, y ];
            }

            // permet de capter le ondrop hors zone de drop
            function calendar_drag_onend(draggable, event)
            {
                calendar_drop_ondrop(draggable.element, calendar_lastdroppable, event);
            }

            <? echo $strJsCode; ?>
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

        // Pr�paration des �v�n�ments
        $arrEvents = $this->_prepare_events();

        // 1er jour du mois (timestp unix)
        $firstday = mktime(0, 0, 0, $this->arrOptions['intMonth'], 1, $this->arrOptions['intYear']);

        // dernier jour du mois (timestp unix)
        $lastday = mktime(0, 0, 0, $this->arrOptions['intMonth']+1, 0, $this->arrOptions['intYear']);

        // Jour de la semaine o� tombe le 1er jour du mois : 1 - 7
        $weekday = $firstweekday = date('N', $firstday);

        // Jour de la semaine o� tombe le dernier jour du mois : 1 - 7
        $lastweekday = date('N', $lastday);

        // Nombre de jours dans le mois : 0 - 31
        $intNbDays = date('t', $firstday);

        // Nombre de semaines dans le mois (entam�es)
        $intNbWeeks = floor($intNbDays / 7) + ($intNbDays % 7 > 0) + ($firstweekday > $lastweekday);

        // Style (hauteur/largeur) du jour
        $intDayWidth = floor(($this->intWidth - ($this->arrOptions['intNumWeeksColWidth'] * $this->arrOptions['booDisplayNumWeeks'])) / 7);
        $intDayHeight = floor(($this->intHeight - ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel'])) / $intNbWeeks);
        $strDayStyle = "width:".($intDayWidth - 1)."px;height:".($intDayHeight - 1)."px;";

        // Style des ent�tes (semaines, jours)
        $strDayHeaderStyle = "width:".($intDayWidth - 1)."px;height:".($this->arrOptions['intDaysLabelHeight'] - 1)."px;";
        $strWeekHeaderStyle = "width:".($this->arrOptions['intNumWeeksColWidth'] - 1)."px;height:".($intDayHeight - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $intCalendarWidth = ($intDayWidth * 7) + ($this->arrOptions['intNumWeeksColWidth'] * $this->arrOptions['booDisplayNumWeeks']);
        $intCalendarHeight = ($intDayHeight * $intNbWeeks) + ($this->arrOptions['intDaysLabelHeight'] * $this->arrOptions['booDisplayDaysLabel']);
        ?>
        <div class="month_inner" style=width:<?php echo $intCalendarWidth; ?>px;height:<?php echo $intCalendarHeight; ?>px;">
            <?php
            // Affichage des libell�s de jours si demand�
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
                            <div class="day_header_label"><?php echo $ploopi_days[$d]; ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            // Boucle n�1 : Si le 1er jour du mois n'est pas un lundi, on affiche la fin du mois pr�c�dent
            if ($weekday > 1)
            {
                // Num�ro de la semaine
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
                    $date = substr(ploopi_unixtimestamp2local($strTs), 0, 10);

                    $extra_class = '';
                    if (ploopi_holiday($strTs)) $extra_class = ' day_holiday';
                    else
                    {
                        if ($c > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
                        <div class="day_num_grayed"><?php echo $d; ?></div>
                        <?php
                        $strEventsKey = substr(ploopi_unixtimestamp2timestamp($strTs), 0, 8);
                        if (!empty($arrEvents[$strEventsKey])) $this->_display_month_events($arrEvents[$strEventsKey]);
                        ?>
                    </div>
                    <?php
                }
            }

            // Boucle n�2 : tous les jours du mois
            for ($d = 1; $d <= date('t', $firstday) ; $d++)
            {
                // Arriv� en fin de semaine, on se repositionne au d�but
                if ($weekday == 8) $weekday = 1;

                // Chaque d�but de semaine = une nouvelle ligne
                if ($weekday == 1)
                {
                    // Num�ro de la semaine
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
                $date = current(ploopi_timestamp2local($ts = sprintf("%04d%02d%02d000000",$this->arrOptions['intYear'], $this->arrOptions['intMonth'], $d)));
                $dateday = ploopi_timestamp2unixtimestamp($ts);

                $extra_class = '';
                if (ploopi_holiday($dateday)) $extra_class = ' day_holiday';
                else
                {
                    if ($weekday > 5) $extra_class = ' day_weekend';
                }
                ?>
                <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
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

            // Boucle n�3 : Si le mois ne se termine pas un dimanche, on affiche le d�but du mois suivant
            if ($weekday <= 7)
            {
                for ($c = $weekday; $c <= 7 ; $c++)
                {
                    $strTs = mktime(0, 0, 0, $this->arrOptions['intMonth']+1, 1+$c-$weekday, $this->arrOptions['intYear']);

                    // Jour du mois
                    $d = date('j', $strTs);

                    // Date au format local
                    $date = substr(ploopi_unixtimestamp2local($strTs), 0, 10);

                    $extra_class = '';
                    if (ploopi_holiday($strTs)) $extra_class = ' day_holiday';
                    else
                    {
                        if ($c > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $strDayStyle; ?>">
                        <div class="day_num_grayed"><?php echo $d; ?></div>
                        <?php
                        $strEventsKey = substr(ploopi_unixtimestamp2timestamp($strTs), 0, 8);
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
     * Affiche un �v�nement dans le planning mensuel
     *
     * @param int $arrIdEvents id de l'�v�nement
     */

    protected function _display_month_events($arrIdEvents)
    {
        foreach($arrIdEvents as $strChannelId => $arrEvents)
        {
            foreach($arrEvents as $intId)
            {
                if (!empty($this->arrEvents[$intId]))
                {
                    $arrDateBegin = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                    $arrDateEnd = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpEnd);
                    ?>
                    <a class="event" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } ?>>
                        <div class="event_inner" style="background-color:<?php echo ploopi_htmlentities($this->arrEvents[$intId]->strColor); ?>;" <?php if (!empty($this->arrEvents[$intId]->strStyle)) {?>style="<?php echo $this->arrEvents[$intId]->strStyle; ?>"<?php } ?>>
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
     * Pr�pare les �v�nement en les r�partissant par jour dans un tableau associatif
     *
     * @return array �v�n�ments (id) par jour  (cl� : AAAAMMJJ)
     */

    protected function _prepare_events()
    {
        $arrEvents = array();

        // Pr�paration des �v�nements � afficher (on va les ranger jour par jour)
        foreach($this->arrEvents as $key => $objEvent)
        {
            // V�rification de l'int�grit�
            if ($objEvent->intTimestpBegin <= $objEvent->intTimestpEnd)
            {
                $currentday = substr($objEvent->intTimestpBegin, 0, 8).'000000';
                // Si l'�v�nement tient sur plusieurs jours on l'affecte pour chaque jour
                do {
                    $arrEvents[substr($currentday, 0, 8)][$objEvent->strChannelId][] = $key;
                    $currentday = ploopi_timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $objEvent->intTimestpEnd);
            }

        }

        return $arrEvents;
    }


    /**
     * Pr�pare les �v�nement en les r�partissant par jour dans un tableau associatif
     *
     * @return array �v�n�ments (id) par jour  (cl� : AAAAMMJJ)
     */

    protected function _prepare_events_OLD()
    {
        $arrEvents = array();

        // Pr�paration des �v�nements � afficher (on va les ranger jour par jour)
        foreach($this->arrEvents as $key => $objEvent)
        {
            // V�rification de l'int�grit�
            if ($objEvent->intTimestpBegin <= $objEvent->intTimestpEnd)
            {
                $currentday = substr($objEvent->intTimestpBegin, 0, 8).'000000';
                // Si l'�v�nement tient sur plusieurs jours on l'affecte pour chaque jour
                do {
                    $arrEvents[substr($currentday, 0, 8)][] = $key;
                    $currentday = ploopi_timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $objEvent->intTimestpEnd);
            }

        }

        return $arrEvents;
    }
}
