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

class calendar
{
    /**
     * Largeur du calendrier
     *
     * @var int
     */

    private $width;

    /**
     * Hauteur du calendrier
     *
     * @var int
     */

    private $height;

    /**
     * Type de calendrier : month/days
     *
     * @var string
     */

    private $display_type;

    /**
     * Options du calendrier
     *
     * @var array
     */

    private $options;

    /**
     * Evénements du calendrier
     *
     * @var array
     */

    private $events;

    /**
     * Constructeur de la classe
     *
     * @param unknown_type $width
     * @param unknown_type $height
     * @param unknown_type $display_type
     * @param unknown_type $options
     * @return calendar
     */

    public function calendar($width, $height, $display_type, $options = array(), $events = array())
    {
        $this->width = $width;
        $this->height = $height;
        $this->display_type = $display_type;

        $this->options =
            array(
                'class_name' => 'ploopi_calendar_'.($display_type == 'month' ? 'm' : 'd'), // class de style (css) utilisée

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

        $this->setoptions($options);

        $this->events = array();

        $this->setevents($events);
    }

    /**
     * Permet de définir les options :
     *
     * @param array $options tableau des options à modifier
     */

    public function setoptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    public function getoptions()
    {
        return $this->options;
    }

    public function setevents($data = array())
    {
        $this->events = $this->events + $data;
    }

    public function addevent($timestp_begin, $timestp_end, $title, $content, $color, $onclick = '', $href = '', $style = '')
    {
        $this->events[] =
            array(
                'timestp_begin' => $timestp_begin,
                'timestp_end' => $timestp_end,
                'title' => $title,
                'content' => $content,
                'color' => $color,
                'onclick' => $onclick,
                'href' => $href,
                'style' => $style,
            );
    }

    public function display()
    {
        ?>
        <div class="<?php echo $this->options['class_name']; ?>">
            <?php
            switch($this->display_type)
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
        $firstday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->options['date_begin']));

        // jour/mois/année du 1er jour
        $firstday_d = date('j', $firstday);
        $firstday_m = date('n', $firstday);
        $firstday_y = date('Y', $firstday);

        // Dernier jour de l'intervalle (timestp unix)
        $lastday = ploopi_timestamp2unixtimestamp(sprintf("%0-14s", $this->options['date_end']));

        // Nombre de jours dans l'intervalles (bornes comprises)
        $nbdays = floor(($lastday - $firstday) / 86400) + 1;

        // Nombre d'heures par jour
        $nbhours = $this->options['hour_end'] - $this->options['hour_begin'];

        // Hauteur entre chaque séparateur d'heure
        $hour_height = floor(($this->height - ($this->options['dayslabel_height'] * $this->options['display_dayslabel'])) / $nbhours);

        // Largeur d'une journée
        $day_width = floor(($this->width - ($this->options['hours_colwidth'] * $this->options['display_hours'])) / $nbdays);

        // Largeur du séparateur d'heure
        $hour_width = $day_width;

        // Hauteur d'une journée
        $day_height = $hour_height * $nbhours;

        // Style du bloc "journée"
        $day_style = "width:".($day_width - 1)."px;height:".($day_height - 1)."px;";

        // Style du bloc "heures" (entête des heures)
        $hours_style = "width:".($this->options['hours_colwidth'] - 1)."px;height:".($day_height - 1)."px;";

        // Style des entêtes (heures, jours)
        $day_header_style = "width:".($day_width - 1)."px;height:".($this->options['dayslabel_height'] - 1)."px;";
        $hour_header_style = "height:".$hour_height."px;";

        // Dimension finale du calendrier (au pixel)
        $calendar_width = $nbdays * $day_width + ($this->options['hours_colwidth'] * $this->options['display_hours']);
        $calendar_height = $day_height + ($this->options['dayslabel_height'] * $this->options['display_dayslabel']);
        ?>
        <div class="days_inner" style=width:<?php echo $calendar_width; ?>px;height:<?php echo $calendar_height; ?>px;">
        <?php
        // Affichage des libellés de jours si demandé
        if ($this->options['display_dayslabel'])
        {
            ?>
            <div class="row">
                <?php
                // Il faut afficher une petite case vide (intersection heures/jours)
                if ($this->options['display_hours'])
                {
                    ?>
                    <div class="day_header" style="<?php echo "width:".($this->options['hours_colwidth'] - 1)."px;height:".($this->options['dayslabel_height'] - 1)."px;"; ?>">&nbsp;</div>
                    <?php
                }

                // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
                for ($d = 1; $d <= $nbdays; $d++)
                {
                    // Détermination de la date du jour à afficher
                    $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                    // Date locale
                    $ldate = substr(ploopi_unixtimestamp2local($dateday), 0, 5);
                    ?>
                    <div class="day_header" style="<?php echo $day_header_style; ?>">
                        <div class="day_header_label"><?php echo $ploopi_days[date('N', $dateday)].' '.$ldate; ?></div>
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
        if ($this->options['display_hours'])
        {
            ?>
            <div class="hours" style="<?php echo $hours_style; ?>">
                <?php
                // Affichage des heures + demi-heures
                for ($h = $this->options['hour_begin']; $h < $this->options['hour_end']; $h++ )
                {
                    ?>
                    <div class="hour_header" style="<?php echo $hour_header_style; ?>;">
                        <div class="hour_header_num"><?php echo sprintf("%02d", $h); ?></div>
                    </div>
                    <?php
                    // Affichage du séparateur d'heures
                    if ($h > $this->options['hour_begin'])
                    {
                        ?>
                        <div class="tick" style="top:<?php echo $hour_height * ($h - $this->options['hour_begin']); ?>px;width:<?php echo $this->options['hours_colwidth']-1; ?>px;"></div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
        }

        // Affichage des journées

        // On boucle sur les jours à afficher (1 = premier jour de l'intervalle)
        for ($d = 1; $d <= $nbdays; $d++)
        {
            ?>
            <div class="day" style="<?php echo $day_style; ?>">
                <?php
                // Affichage des heures + demi-heures
                for ($h = $this->options['hour_begin']+1; $h < $this->options['hour_end']; $h++ )
                {
                    ?>
                    <div class="tick" style="opacity:0.5;filter:alpha(opacity=50);top:<?php echo floor($hour_height * ($h - $this->options['hour_begin'] - 0.5)); ?>px;width:<?php echo $day_width-1; ?>px;"></div>
                    <div class="tick" style="top:<?php echo $hour_height * ($h - $this->options['hour_begin']); ?>px;width:<?php echo $day_width-1; ?>px;"></div>
                    <?php
                }
                // Détermination de la date du jour à afficher
                $dateday = mktime(0, 0, 0, $firstday_m, $firstday_d + $d - 1, $firstday_y);

                // Clé de date pour lire dans le tableau des événements
                $strEventsKey = sprintf("%04d%02d%02d",date('Y', $dateday), date('n', $dateday), date('j', $dateday));

                // Affichage des événements
                if (!empty($arrEvents[$strEventsKey]))
                {
                    foreach($arrEvents[$strEventsKey] as $intId)
                    {
                        if (!empty($this->events[$intId]))
                        {
                            $arrDate = ploopi_timestamp2local($this->events[$intId]['timestp_begin']);
                            $strTime = substr($arrDate['time'], 0, 5);

                            // Détermination heure de début (ajustement de l'heure de début en fonction de la date de l'événement)
                            $intTsDateBegin = ploopi_timestamp2unixtimestamp($this->events[$intId]['timestp_begin']);
                            $floTimeBegin = (substr($this->events[$intId]['timestp_begin'], 0 ,8) == $strEventsKey) ? date('G', $intTsDateBegin) + (intval(date('i', $intTsDateBegin), 10) / 60) : 0 ;

                            // Détermination heure de fin (ajustement de l'heure de fin en fonction de la date de l'événement)
                            $intTsDateEnd = ploopi_timestamp2unixtimestamp($this->events[$intId]['timestp_end']);
                            $floTimeEnd = (substr($this->events[$intId]['timestp_end'], 0 ,8) == $strEventsKey) ? date('G', $intTsDateEnd) + (intval(date('i', $intTsDateEnd), 10) / 60) : 24;

                            // On adapte ensuite les heures de début/fin aux limites d'affichage du planning
                            if ($floTimeBegin < $this->options['hour_begin']) $floTimeBegin = $this->options['hour_begin'];
                            if ($floTimeEnd > $this->options['hour_end']) $floTimeEnd = $this->options['hour_end'];

                            // Durée de l'événement en heures
                            $floTimeLength = $floTimeEnd - $floTimeBegin;

                            // Début de l'événement en pix
                            $intEventTop = floor(($floTimeBegin - $this->options['hour_begin']) * $hour_height);

                            // Hauteur de l'événement en pix
                            $intEventHeight = floor($floTimeLength * $hour_height);

                            ?>
                            <a class="event" href="<?php echo $this->events[$intId]['href']; ?>" title="<?php echo $this->events[$intId]['title']; ?>" <?php if (!empty($this->events[$intId]['onclick'])) {?>onclick="<?php echo $this->events[$intId]['onclick']; ?>"<?php } ?> style="top:<?php echo $intEventTop; ?>px;height:<?php echo $intEventHeight - 1; ?>px;width:<?php echo $day_width - 1 ?>px;background-color:<?php echo htmlentities($this->events[$intId]['color']); ?>;">
                                <div class="event_inner" <?php if (!empty($this->events[$intId]['style'])) {?>style="<?php echo $this->events[$intId]['style']; ?>"<?php } ?>>
                                    <?php
                                    echo str_replace(
                                        array('<timestp_begin>', '<timestp_end>'),
                                        array($strTime, $strTime),
                                        $this->events[$intId]['content']
                                    );
                                    ?>
                                </div>
                            </a>
                            <?php
                        }
                    }
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
     * Affichage du calendrier mensuel
     *
     */

    private function _display_month()
    {
        global $ploopi_days;

        // Préparation des événéments
        $arrEvents = $this->_prepare_events();

        // 1er jour du mois (timestp unix)
        $firstday = mktime(0, 0, 0, $this->options['month'], 1, $this->options['year']);

        // dernier jour du mois (timestp unix)
        $lastday = mktime(0, 0, 0, $this->options['month']+1, 0, $this->options['year']);

        // Jour de la semaine où tombe le 1er jour du mois : 1 - 7
        $weekday = $firstweekday = date('N', $firstday);

        // Jour de la semaine où tombe le dernier jour du mois : 1 - 7
        $lastweekday = date('N', $lastday);

        // Nombre de jours dans le mois : 0 - 31
        $nbdays = date('t', $firstday);

        // Nombre de semaines dans le mois (entamées)
        $nbweeks = floor($nbdays / 7) + ($nbdays % 7 > 0) + ($firstweekday > $lastweekday);

        // Style (hauteur/largeur) du jour
        $day_width = floor(($this->width - ($this->options['numweeks_colwidth'] * $this->options['display_numweeks'])) / 7);
        $day_height = floor(($this->height - ($this->options['dayslabel_height'] * $this->options['display_dayslabel'])) / $nbweeks);
        $day_style = "width:".($day_width - 1)."px;height:".($day_height - 1)."px;";

        // Style des entêtes (semaines, jours)
        $day_header_style = "width:".($day_width - 1)."px;height:".($this->options['dayslabel_height'] - 1)."px;";
        $week_header_style = "width:".($this->options['numweeks_colwidth'] - 1)."px;height:".($day_height - 1)."px;";

        // Dimension finale du calendrier (au pixel)
        $calendar_width = ($day_width * 7) + ($this->options['numweeks_colwidth'] * $this->options['display_numweeks']);
        $calendar_height = ($day_height * $nbweeks) + ($this->options['dayslabel_height'] * $this->options['display_dayslabel']);

        ?>
        <div class="month_inner" style=width:<?php echo $calendar_width; ?>px;height:<?php echo $calendar_height; ?>px;">
            <?php
            // Affichage des libellés de jours si demandé
            if ($this->options['display_dayslabel'])
            {
                ?>
                <div class="row">
                    <?php
                    // Il faut afficher une petite case vide (intersection semaines/jours)
                    if ($this->options['display_numweeks'])
                    {
                        ?>
                        <div class="day_header" style="<?php echo "width:".($this->options['numweeks_colwidth'] - 1)."px;height:".($this->options['dayslabel_height'] - 1)."px;"; ?>">&nbsp;</div>
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
                if ($this->options['display_numweeks'])
                {
                    ?>
                    <div class="week_header" style="<?php echo $week_header_style; ?>">
                        <div class="week_header_num"><?php echo $w; ?></div>
                    </div>
                    <?php
                }

                for ($c = 1; $c < $weekday; $c++)
                {
                    $strTs = mktime(0, 0, 0, $this->options['month'], 1+$c-$weekday, $this->options['year']);

                    // Jour du mois
                    $d = date('j', $strTs);

                    // Date au format local
                    $date = substr(ploopi_unixtimestamp2local($strTs), 0, 10);
                    ?>
                    <div class="day" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
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
                    $w = date('W', mktime(0, 0, 0, $this->options['month'], $d,  $this->options['year']));
                    ?>
                    <div class="row">
                    <?php
                    if ($this->options['display_numweeks'])
                    {
                        ?>
                        <div class="week_header" style="<?php echo $week_header_style; ?>">
                            <div class="week_header_num"><?php echo $w; ?></div>
                        </div>
                        <?php
                    }
                }

                // Date au format local
                $date = current(ploopi_timestamp2local(sprintf("%04d%02d%02d000000",$this->options['year'], $this->options['month'], $d)));

                ?>
                <div class="day" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
                    <div class="day_num"><?php echo $d; ?></div>
                    <?php
                    $strEventsKey = sprintf("%04d%02d%02d",$this->options['year'], $this->options['month'], $d);
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
                    $strTs = mktime(0, 0, 0, $this->options['month']+1, 1+$c-$weekday, $this->options['year']);

                    // Jour du mois
                    $d = date('j', $strTs);

                    // Date au format local
                    $date = substr(ploopi_unixtimestamp2local($strTs), 0, 10);
                    ?>
                    <div class="day" title="<?php echo $date ?>" style="<?php echo $day_style; ?>">
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
            if (!empty($this->events[$intId]))
            {
                $arrDate = ploopi_timestamp2local($this->events[$intId]['timestp_begin']);
                $strTime = substr($arrDate['time'], 0, 5);
                ?>
                <a class="event" href="<?php echo $this->events[$intId]['href']; ?>" title="<?php echo $this->events[$intId]['title']; ?>" <?php if (!empty($this->events[$intId]['onclick'])) {?>onclick="<?php echo $this->events[$intId]['onclick']; ?>"<?php } ?>>
                    <div class="event_inner" style="background-color:<?php echo htmlentities($this->events[$intId]['color']); ?>;" <?php if (!empty($this->events[$intId]['style'])) {?>style="<?php echo $this->events[$intId]['style']; ?>"<?php } ?>>
                        <?php
                        echo str_replace(
                            array('<timestp_begin>', '<timestp_end>'),
                            array($strTime, $strTime),
                            $this->events[$intId]['content']
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
        foreach($this->events as $key => $event)
        {
            // Vérification de l'intégrité
            if ($event['timestp_begin'] <= $event['timestp_end'])
            {
                $currentday = $event['timestp_begin'];
                // Si l'événement tient sur plusieurs jours on l'affecte pour chaque jour
                do {
                    $arrEvents[substr($currentday, 0, 8)][] = $key;
                    $currentday = ploopi_timestamp_add($currentday, 0, 0, 0, 0, 1, 0);
                } while ($currentday <= $event['timestp_end']);
            }
        }

        return $arrEvents;
    }
}
