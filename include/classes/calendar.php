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
 * @author Stéphane Escaich
 */


/**
 * Classe de gestion des événements du calendrier
 */
class calendarEvent
{
    /**
     * Heure de début au format timestamp
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
     * Couleur au format #RRGGBB
     */
    private $strColor;

    /**
     * Fonction javascript à exécuter sur l'événement "onclick"
     */
    private $strOnClick;

    /**
     * Lien href sur l'événement
     */
    private $strHref;

    /**
     * Fonction javascript à exécuter sur l'événement "onclose"
     */
    private $strOnClose;

    /**
     * Url à exécuter sur l'événement "ondrop"
     */
    private $arrOnDrop;

    /**
     * Styles complémentaires à appliquer
     */
    private $strStyle;

    /**
     * Constructeur de la classe
     *
     * @param int $intTimestpBegin Heure de début au format timestamp
     * @param int $intTimestpEnd Heure de fin au format timestamp
     * @param string $strTitle Titre
     * @param string $strContent Contenu
     * @param string $strColor  Couleur au format #RRGGBB (optionnel)
     * @param string $strOnClick Fonction javascript à exécuter sur l'événement "onclick" (optionnel)
     * @param string $strHref Lien href sur l'événement (optionnel)
     * @param string $strOnClose Fonction javascript à exécuter sur l'événement "onclose" (optionnel)
     * @param string $strStyle Styles complémentaires à appliquer (optionnel)
     * @return calendarEvent
     */

    public function __construct($intTimestpBegin, $intTimestpEnd, $strTitle, $strContent, $strColor = null, $strOnClick = null, $strHref = null, $strOnClose = null, $arrOnDrop = null, $strStyle = null)
    {
        $this->intTimestpBegin = $intTimestpBegin;
        $this->intTimestpEnd = $intTimestpEnd;
        $this->strTitle = $strTitle;
        $this->strContent = $strContent;
        $this->strColor = $strColor;
        $this->strOnClick = $strOnClick;
        $this->strHref = $strHref;
        $this->strOnClose = $strOnClose;
        $this->arrOnDrop = is_null($arrOnDrop) || is_array($arrOnDrop) ? $arrOnDrop : null;
        $this->strStyle = $strStyle;
    }


    /**
     * Getter par défaut
     *
     * @param string $strName nom de la propriété à lire
     * @return string valeur de la propriété si elle existe
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

    private $intWidth;

    /**
     * Hauteur du calendrier
     *
     * @var int
     */

    private $intHeight;

    /**
     * Type de calendrier : month/days
     *
     * @var string
     */

    private $strDisplayType;

    /**
     * Options du calendrier
     *
     * @var array
     */

    private $arrOptions;

    /**
     * Evénements du calendrier
     *
     * @var array
     */

    private $arrEvents;

    /**
     * Constructeur de la classe
     *
     * @param unknown_type $intWidth
     * @param unknown_type $intHeight
     * @param unknown_type $strDisplayType
     * @param unknown_type $arrOptions
     * @return calendar
     */

    public function __construct($intWidth, $intHeight, $strDisplayType, $arrOptions = array(), $arrEvents = array())
    {
        $this->intWidth = $intWidth;
        $this->intHeight = $intHeight;
        $this->strDisplayType = $strDisplayType;

        $this->arrOptions =
            array(
                'class_name' => 'ploopi_calendar_'.($strDisplayType == 'month' ? 'm' : 'd'), // class de style (css) utilisée

                'month' => '', // valable pour display_type = 'month'
                'year' => '',
                'display_numweeks' => true,
                'numweeks_colwidth' => 25,

                'date_begin' => '',
                'date_end' => '',
                'hour_begin' => 6,
                'hour_end' => 21,
                'display_hours' => true,
                'hours_colwidth' => 25,

                'display_dayslabel' => true,
                'dayslabel_height' => 20

            );

        $this->setoptions($arrOptions);

        $this->arrEvents = array();

        $this->setevents($arrEvents);
    }

    /**
     * Permet de définir les options d'affichage :
     *
     * class_name' => class de rendu (defaut: ploopi_calendar_m ou ploopi_calendar_d en fonction du type de planning monthly/daily)
     * month' => mois à afficher pour l'affichage mensuel
     * year' => année à afficher
     * display_numweeks' => true
     * numweeks_colwidth : largeur de la colonne des semaines pour l'affichage mensuel (defaut: 25)
     * date_begin : date de début
     * date_end : date de fin
     * hour_begin : heure min (defaut: 6)
     * hour_end : heure max (defaut: 21)
     * display_hours : affichage des heures (defaut: true)
     * hours_colwidth : largeur de la colonne des heures pour l'affichage hebdomadaire (defaut: 25)
     * display_dayslabel : afficher les jours (defaut: true)
     * dayslabel_height : hauteur de la ligne des jours pour l'affichage hebdomadaire (defaut: 20)
     *
     * @param array $arrOptions tableau des options à modifier
     */

    public function setoptions($arrOptions)
    {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }

    public function getoptions()
    {
        return $this->arrOptions;
    }

    public function setevents($arrEvents = array())
    {
        $this->arrEvents = $this->arrEvents + $arrEvents;
    }

    public function addevent(calendarEvent $objEvent)
    {
        $this->arrEvents[] = $objEvent;
    }

    public function display()
    {
        ?>
        <div class="<?php echo $this->arrOptions['class_name']; ?>" id="calendar">
            <?php
            switch($this->strDisplayType)
            {
                case 'days':
                    $this->_display_days();
                break;

                case 'month':
                    $this->_display_month();
                break;
            }
            ?>
        </div>
        <?php
    }

    /**
     * Affichage du calendrier multi-journées
     *
     */

    private function _display_days()
    {
        global $ploopi_days;

        // Préparation des événéments
        $arrEvents = $this->_prepare_events();

        // 1er jour de l'intervalle (timestp unix)
        $firstday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['date_begin']));

        // jour/mois/année du 1er jour
        $firstday_d = date('j', $firstday);
        $firstday_m = date('n', $firstday);
        $firstday_y = date('Y', $firstday);

        // Dernier jour de l'intervalle (timestp unix)
        $lastday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->arrOptions['date_end']));

        // Nombre de jours dans l'intervalles (bornes comprises)
        $nbdays = floor(($lastday - $firstday) / 86400) + 1;

        // Nombre d'heures par jour
        $nbhours = $this->arrOptions['hour_end'] - $this->arrOptions['hour_begin'];

        // Hauteur entre chaque séparateur d'heure
        $hour_height = floor(($this->intHeight - ($this->arrOptions['dayslabel_height'] * $this->arrOptions['display_dayslabel'])) / $nbhours);

        // Largeur d'une journée
        $day_width = floor(($this->intWidth - ($this->arrOptions['hours_colwidth'] * $this->arrOptions['display_hours'])) / $nbdays);

        // Largeur du séparateur d'heure
        $hour_width = $day_width;

        // Hauteur d'une journée
        $day_height = $hour_height * $nbhours;

        // Style du bloc "journée"
        $day_style = "width:".($day_width - 1)."px;height:".($day_height - 1)."px;";

        // Style du bloc "heures" (entête des heures)
        $hours_style = "width:".($this->arrOptions['hours_colwidth'] - 1)."px;height:".($day_height - 1)."px;";

        // Style des entêtes (heures, jours)
        $day_header_style = "width:".($day_width - 1)."px;height:".($this->arrOptions['dayslabel_height'] - 1)."px;";
        $hour_header_style = "height:".$hour_height."px;";

        // Dimension finale du calendrier (au pixel)
        $calendar_width = $nbdays * $day_width + ($this->arrOptions['hours_colwidth'] * $this->arrOptions['display_hours']);
        $calendar_height = $day_height + ($this->arrOptions['dayslabel_height'] * $this->arrOptions['display_dayslabel']);

        // Chaîne contenant le code javascript à éxécuter (draggables/droppables/fonctions)
        $strJsCode = '';
        ?>
        <div class="days_inner" style=width:<?php echo $calendar_width; ?>px;height:<?php echo $calendar_height; ?>px;">
        
            <?php
            // Affichage des libellés de jours si demandé
            if ($this->arrOptions['display_dayslabel'])
            {
                ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection heures/jours)
                    if ($this->arrOptions['display_hours'])
                    {
                        ?>
                        <div class="day_header" style="<?php echo "width:".($this->arrOptions['hours_colwidth'] - 1)."px;height:".($this->arrOptions['dayslabel_height'] - 1)."px;"; ?>">&nbsp;</div>
                        <?php
                    }
    
                    // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                    for ($d = 1; $d <= $nbdays; $d++)
                    {
                        // Détermination de la date du jour à afficher
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
                        <div class="day_header<? echo $extra_class; ?>" style="<?php echo $day_header_style; ?>">
                            <div class="day_header_label"><?php echo $ploopi_days[$weekday].' '.$ldate; ?></div>
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
            if ($this->arrOptions['display_hours'])
            {
                ?>
                <div class="hours" style="<?php echo $hours_style; ?>">
                    <?php
                    // Affichage des heures + demi-heures
                    for ($h = $this->arrOptions['hour_begin']; $h < $this->arrOptions['hour_end']; $h++ )
                    {
                        ?>
                        <div class="hour_header" style="<?php echo $hour_header_style; ?>;">
                            <div class="hour_header_num"><?php echo sprintf("%02d", $h); ?></div>
                        </div>
                        <?php
                        // Affichage du séparateur d'heures
                        if ($h > $this->arrOptions['hour_begin'])
                        {
                            ?>
                            <div class="tick" style="top:<?php echo $hour_height * ($h - $this->arrOptions['hour_begin']); ?>px;width:<?php echo $this->arrOptions['hours_colwidth']-1; ?>px;"></div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
            ?>
            </div>
    
            <div id="calendar_days" style="overflow:auto;">
                <?php
                // Affichage des journées
    
                // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                for ($d = 1; $d <= $nbdays; $d++)
                {
                    // Détermination de la date du jour à afficher
                    $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);
    
                    $extra_class = '';
                    if (ploopi_holiday($dateday)) $extra_class = ' day_holiday';
                    else
                    {
                        $weekday = date('N', $dateday);
                        if ($weekday > 5) $extra_class = ' day_weekend';
                    }
                    ?>
                    <div class="day<? echo $extra_class; ?>" id="calendar_day<? echo $d; ?>" style="<?php echo $day_style; ?>">
                        <?php
                        $c = 0;
                        // Affichage des heures + demi-heures
                        for ($h = $this->arrOptions['hour_begin']; $h < $this->arrOptions['hour_end']; $h++ )
                        {
                            $intHourPx = $hour_height * ($h - $this->arrOptions['hour_begin']);
                            $intHalfHourPx = floor($intHourPx + $hour_height / 2);
                            ?>
                            <div class="tick" style="opacity:0.5;filter:alpha(opacity=50);top:<?php echo $intHalfHourPx; ?>px;width:<?php echo $day_width-1; ?>px;"></div>
                            <?
                            if ($h > $this->arrOptions['hour_begin'])
                            {
                                ?>
                                <div class="tick" style="top:<?php echo $intHourPx; ?>px;width:<?php echo $day_width-1; ?>px;"></div>
                                <?
                            }
                            ?>
                            <?php
                        }
    
                        // Clé de date pour lire dans le tableau des événements
                        $strEventsKey = sprintf("%04d%02d%02d",date('Y', $dateday), date('n', $dateday), date('j', $dateday));
                        $strJsCode .= "calendar_days[$d] = '{$strEventsKey}';";
    
                        // Affichage des événements
                        if (!empty($arrEvents[$strEventsKey]))
                        {
                            foreach($arrEvents[$strEventsKey] as $intId)
                            {
                                if (!empty($this->arrEvents[$intId]))
                                {
                                    $arrDateBegin = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                                    $arrDateEnd = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpEnd);
                                    $strTimeBegin = substr($arrDateBegin['time'], 0, 5);
                                    $strTimeEnd = substr($arrDateEnd['time'], 0, 5);
    
                                    // Détermination heure de début (ajustement de l'heure de début en fonction de la date de l'événement)
                                    $intTsDateBegin = ploopi_timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpBegin);
                                    $floTimeBegin = (substr($this->arrEvents[$intId]->intTimestpBegin, 0 ,8) == $strEventsKey) ? date('G', $intTsDateBegin) + (intval(date('i', $intTsDateBegin), 10) / 60) : 0 ;
    
                                    // Détermination heure de fin (ajustement de l'heure de fin en fonction de la date de l'événement)
                                    $intTsDateEnd = ploopi_timestamp2unixtimestamp($this->arrEvents[$intId]->intTimestpEnd);
                                    $floTimeEnd = (substr($this->arrEvents[$intId]->intTimestpEnd, 0 ,8) == $strEventsKey) ? date('G', $intTsDateEnd) + (intval(date('i', $intTsDateEnd), 10) / 60) : 24;
    
                                    // On adapte ensuite les heures de début/fin aux limites d'affichage du planning
                                    if ($floTimeBegin < $this->arrOptions['hour_begin']) $floTimeBegin = $this->arrOptions['hour_begin'];
                                    if ($floTimeEnd > $this->arrOptions['hour_end']) $floTimeEnd = $this->arrOptions['hour_end'];
    
                                    // Durée de l'événement en heures
                                    $floTimeLength = $floTimeEnd - $floTimeBegin;
    
                                    // Début de l'événement en pix
                                    $intEventTop = floor(($floTimeBegin - $this->arrOptions['hour_begin']) * $hour_height);
    
                                    // Hauteur de l'événement en pix
                                    $intEventHeight = floor($floTimeLength * $hour_height);
    
                                    ?>
                                    <div class="event" id="calendar_event<? echo $intId; ?>" title="<?php echo $this->arrEvents[$intId]->strTitle; ?>" style="top:<?php echo $intEventTop; ?>px;left:0px;height:<?php echo $intEventHeight - 1; ?>px;width:<?php echo $day_width - 1 ?>px;background-color:<?php echo htmlentities($this->arrEvents[$intId]->strColor); ?>;">
                                        
                                        <div class="event_title" id="calendar_event<? echo $intId; ?>_handle"  style="height:16px;line-height:16px;<? echo !is_null($this->arrEvents[$intId]->arrOnDrop) ? 'cursor:move;' : ''; ?>">
                                            <?
                                            if (!is_null($this->arrEvents[$intId]->strOnClose))
                                            {
                                                ?>
                                                <a href="javascript:void(0);" onclick="javascript:<? echo $this->arrEvents[$intId]->strOnClose; ?>;"><img align="right" src="<? echo $_SESSION['ploopi']['template_path']; ?>/img/calendar/close.png" /></a>
                                                <?
                                            }
                                            ?>
                                            <span><? printf("%s %s", $strTimeBegin, $strTimeEnd); ?></span>
                                        </div>
                                        <a class="event_inner" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } ?> style="height:<?php echo $intEventHeight - 20; ?>px;<?php if (!empty($this->arrEvents[$intId]->strStyle)) echo $this->arrEvents[$intId]->strStyle; ?>">
                                            <?php
                                            //echo $this->arrEvents[$intId]->strOnClick;
                                            echo str_replace(
                                                array('<timestp_begin>', '<timestp_end>'),
                                                array($strTimeBegin, $strTimeEnd),
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
                                        $strJsCode .= "new Draggable('calendar_event{$intId}', { handle: 'calendar_event{$intId}_handle', snap: calendar_drag_snap, onEnd: calendar_drag_onend });";
                                        $strJsCode .= "calendar_events[{$intId}] = ['{$this->arrEvents[$intId]->arrOnDrop['url']}','{$this->arrEvents[$intId]->arrOnDrop['element_id']}'];";
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <?php
                    // Création du droppable (jour)
                    $strJsCode .= "Droppables.add('calendar_day{$d}', { accept: 'event', onHover: calendar_drop_onhover });";
                }
                ?>
            </div>
        </div>
        
        <script type="text/javascript">
            var calendar_lastdroppable = null;
            var calendar_days = [];
            var calendar_events = [];
            var calendar_h_begin = <? echo $this->arrOptions['hour_begin']; ?>;
            var calendar_h_height = <? echo $hour_height; ?>;

            function calendar_drop_onhover(draggable, droppable, pcent) {
                if (droppable != calendar_lastdroppable) droppable.highlight();
                calendar_lastdroppable = droppable;
            }

            function calendar_drop_ondrop(draggable, droppable) {
                // On détache l'événement du jour d'origine
                draggable.parentNode.removeChild(draggable);
                // On force l'alignement à gauche
                draggable.style.left = '0px';

                // On calcule la demi-heure la plus proche en fonction des coordonnées
                top = parseInt(draggable.style.top);
                hour = Math.round((calendar_h_begin + top / calendar_h_height)*2)/2;
                // On calcule la nouvelle position en fonction de la demi-heure la plus proche
                draggable.style.top = (hour - calendar_h_begin)*calendar_h_height + 'px';
                // On attache l'événement au nouveau jour
                droppable.appendChild(draggable);

                // jour
                day = droppable.id.substring(12,13);
                // id de l'événement
                event = draggable.id.substring(14,15);

                // Enregistrement de la nouvelle position de l'événement, retour vers l'application métier
                ploopi_xmlhttprequest_todiv(calendar_events[event][0], 'calendar_event_date='+calendar_days[day]+'&calendar_event_hour='+hour, calendar_events[event][1]);
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

    private function _display_month()
    {
        global $ploopi_days;

        // Préparation des événéments
        $arrEvents = $this->_prepare_events();

        // 1er jour du mois (timestp unix)
        $firstday = mktime(0, 0, 0, $this->arrOptions['month'], 1, $this->arrOptions['year']);

        // dernier jour du mois (timestp unix)
        $lastday = mktime(0, 0, 0, $this->arrOptions['month']+1, 0, $this->arrOptions['year']);

        // Jour de la semaine où tombe le 1er jour du mois : 1 - 7
        $weekday = $firstweekday = date('N', $firstday);

        // Jour de la semaine où tombe le dernier jour du mois : 1 - 7
        $lastweekday = date('N', $lastday);

        // Nombre de jours dans le mois : 0 - 31
        $nbdays = date('t', $firstday);

        // Nombre de semaines dans le mois (entamées)
        $nbweeks = floor($nbdays / 7) + ($nbdays % 7 > 0) + ($firstweekday > $lastweekday);

        // Style (hauteur/largeur) du jour
        $day_width = floor(($this->intWidth - ($this->arrOptions['numweeks_colwidth'] * $this->arrOptions['display_numweeks'])) / 7);
        $day_height = floor(($this->intHeight - ($this->arrOptions['dayslabel_height'] * $this->arrOptions['display_dayslabel'])) / $nbweeks);
        $day_style = "width:".($day_width - 1)."px;height:".($day_height - 1)."px;";

        // Style des entêtes (semaines, jours)
        $day_header_style = "width:".($day_width - 1)."px;height:".($this->arrOptions['dayslabel_height'] - 1)."px;";
        $week_header_style = "width:".($this->arrOptions['numweeks_colwidth'] - 1)."px;height:".($day_height - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $calendar_width = ($day_width * 7) + ($this->arrOptions['numweeks_colwidth'] * $this->arrOptions['display_numweeks']);
        $calendar_height = ($day_height * $nbweeks) + ($this->arrOptions['dayslabel_height'] * $this->arrOptions['display_dayslabel']);
        ?>
        <div class="month_inner" style=width:<?php echo $calendar_width; ?>px;height:<?php echo $calendar_height; ?>px;">
            <?php
            // Affichage des libellés de jours si demandé
            if ($this->arrOptions['display_dayslabel'])
            {
                ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection semaines/jours)
                    if ($this->arrOptions['display_numweeks'])
                    {
                        ?>
                        <div class="day_header" style="<?php echo "width:".($this->arrOptions['numweeks_colwidth'] - 1)."px;height:".($this->arrOptions['dayslabel_height'] - 1)."px;"; ?>">&nbsp;</div>
                        <?php
                    }

                    for ($d=1; $d<=7; $d++)
                    {
                        ?>
                        <div class="day_header" style="<?php echo $day_header_style; ?>">
                            <div class="day_header_label"><?php echo $ploopi_days[$d]; ?></div>
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
                if ($this->arrOptions['display_numweeks'])
                {
                    ?>
                    <div class="week_header" style="<?php echo $week_header_style; ?>">
                        <div class="week_header_num"><?php echo $w; ?></div>
                    </div>
                    <?php
                }

                for ($c = 1; $c < $weekday; $c++)
                {
                    $strTs = mktime(0, 0, 0, $this->arrOptions['month'], 1+$c-$weekday, $this->arrOptions['year']);

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
                    <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
                        <div class="day_num_grayed"><?php echo $d; ?></div>
                        <?php
                        $strEventsKey = substr(ploopi_unixtimestamp2timestamp($strTs), 0, 8);
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
                    $w = date('W', mktime(0, 0, 0, $this->arrOptions['month'], $d,  $this->arrOptions['year']));
                    ?>
                    <div class="row">
                    <?php
                    if ($this->arrOptions['display_numweeks'])
                    {
                        ?>
                        <div class="week_header" style="<?php echo $week_header_style; ?>">
                            <div class="week_header_num"><?php echo $w; ?></div>
                        </div>
                        <?php
                    }
                }

                // Date au format local
                $date = current(ploopi_timestamp2local($ts = sprintf("%04d%02d%02d000000",$this->arrOptions['year'], $this->arrOptions['month'], $d)));
                $dateday = ploopi_timestamp2unixtimestamp($ts);

                $extra_class = '';
                if (ploopi_holiday($dateday)) $extra_class = ' day_holiday';
                else
                {
                    if ($weekday > 5) $extra_class = ' day_weekend';
                }
                ?>
                <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
                    <div class="day_num"><?php echo $d; ?></div>
                    <?php
                    $strEventsKey = sprintf("%04d%02d%02d",$this->arrOptions['year'], $this->arrOptions['month'], $d);
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
                    $strTs = mktime(0, 0, 0, $this->arrOptions['month']+1, 1+$c-$weekday, $this->arrOptions['year']);

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
                    <div class="day<? echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
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
     * Affiche un événement dans le planning mensuel
     *
     * @param int $arrIdEvents id de l'événement
     */

    private function _display_month_events($arrIdEvents)
    {
        foreach($arrIdEvents as $intId)
        {
            if (!empty($this->arrEvents[$intId]))
            {
                $arrDate = ploopi_timestamp2local($this->arrEvents[$intId]->intTimestpBegin);
                $strTime = substr($arrDate['time'], 0, 5);
                ?>
                <a class="event" href="<?php echo $this->arrEvents[$intId]->strHref; ?>" title="<?php echo $this->arrEvents[$intId]->strTitle; ?>" <?php if (!is_null($this->arrEvents[$intId]->strOnClick)) {?>onclick="<?php echo $this->arrEvents[$intId]->strOnClick; ?>"<?php } ?>>
                    <div class="event_inner" style="background-color:<?php echo htmlentities($this->arrEvents[$intId]->strColor); ?>;" <?php if (!empty($this->arrEvents[$intId]->strStyle)) {?>style="<?php echo $this->arrEvents[$intId]->strStyle; ?>"<?php } ?>>
                        <?php
                        echo str_replace(
                            array('<timestp_begin>', '<timestp_end>'),
                            array($strTime, $strTime),
                            $this->arrEvents[$intId]->strContent
                        );
                        ?>
                    </div>
                </a>
                <?php
            }
        }
    }

    /**
     * Prépare les événement en les répartissant par jour dans un tableau associatif
     *
     * @return array événéments (id) par jour  (clé : AAAAMMJJ)
     */

    private function _prepare_events()
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
                    $currentday = ploopi_timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $objEvent->intTimestpEnd);
            }

        }

        return $arrEvents;
    }
}
