<?php
/*
    Copyright (c) 2009 HeXad
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
 * Affichage d'un calendrier spécifique aux besoins de l'affichage type "Blog"
 *
 * @package webedit
 * @subpackage blog
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/calendar.php';

/**
 * Classe d'accès à la table ploopi_mod_webedit_article_backup
 *
 * @package webedit
 * @subpackage article
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class webedit_calendar_blog extends calendar
{
    public function display()
    {
        global $ploopi_days;
        global $ploopi_months;

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
        $day_width = floor($this->intWidth / 7);
        $day_height = floor($this->intHeight / $nbweeks);
        $day_style = "width:".($day_width - 5)."px; height:".($day_height - 5)."px;";

        // Dimension finale du calendrier (au pixel)
        $calendar_width = ($day_width * 7);
        $calendar_height = ($day_height * ($nbweeks)) + 18;

        ?>
        <div class="<?php echo $this->arrOptions['class_name']; ?>" id="calendar_blog">
            <div class="month_inner" style="width: <?php echo $calendar_width; ?>px; height:<?php echo $calendar_height; ?>px;">
                <div class="row_title" style="height: 14px; width: <?php echo $calendar_width-2; ?>px;">
                    <?php
                    if(!empty($this->arrOptions['urlmonthnext']))
                    {
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:window.location.href='<?php echo $this->arrOptions['urlmonthnext']; ?>'" style="float: right; cursor: pointer;" title="Visualiser les articles pour <?php echo $ploopi_months[intval($this->arrOptions['monthnext'])].' '.$this->arrOptions['yearnext']; ?>">&nbsp;&gt;&nbsp;</a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <div style="float: right;">&nbsp;&nbsp;&nbsp;</div>
                        <?php
                    }

                    if(!empty($this->arrOptions['urlmonthbefore']))
                    {
                        ?>
                        <a href="javascript:void(0);" onclick="javascript:window.location.href='<?php echo $this->arrOptions['urlmonthbefore']; ?>'" style="float: left; cursor: pointer;"title="Visualiser les articles pour <?php echo $ploopi_months[intval($this->arrOptions['monthbefore'])].' '.$this->arrOptions['yearbefore']; ?>">&nbsp;&lt;&nbsp;</a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <div style="float: left;">&nbsp;&nbsp;&nbsp;</div>
                        <?php
                    }

                    ?>
                    <div><?php echo $ploopi_months[intval($this->arrOptions['month'])].' '.$this->arrOptions['year']; ?></div>
                </div>
                <?php
                // Boucle n°1 : Si le 1er jour du mois n'est pas un lundi, on affiche la fin du mois précédent
                if ($weekday > 1)
                {
                    ?>
                    <div class="row">
                    <?php
                    for ($c = 1; $c < $weekday; $c++)
                    {
                        $strTs = mktime(0, 0, 0, $this->arrOptions['month'], 1+$c-$weekday, $this->arrOptions['year']);

                        // Jour du mois
                        $d = date('j', $strTs);

                        // Date au format local
                        $date = substr(ploopi_unixtimestamp2local($strTs), 0, 10);
                        $extra_class = '';
                        $extra_title = '';
                        $onclick = '';

                        if (ploopi_holiday($strTs)) $extra_class = ' day_holiday';
                        else
                        {
                            if ($c > 5) $extra_class = ' day_weekend';
                        }

                        $strEventsKey = substr(ploopi_unixtimestamp2timestamp($strTs), 0, 8);

                        if(!empty($arrEvents[$strEventsKey]))
                        {
                            $onclick = '';
                            foreach($arrEvents[$strEventsKey] as $strChannelId => $arrDayEvents)
                            {
                                foreach($arrDayEvents as $intId)
                                {
                                    if (!empty($this->arrEvents[$intId]))
                                    {
                                        $extra_title .=  ' - '.$this->arrEvents[$intId]->strTitle;
                                        if(empty($onclick))
                                        {
                                            $onclick = 'onClick="javascript:window.location.href=\''.$this->arrEvents[$intId]->strHref.'\';return(0);"';
                                            $extra_class .= ' day_num_grayed_event';
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="day_num_grayed<?php echo $extra_class; ?>" title="<?php echo $date ?>" style="<?php echo $day_style; ?>" <?php echo $onclick; ?>>
                            <?php echo $d; ?>
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
                        ?>
                        <div class="row">
                        <?php
                    }

                    // Date au format local
                    $date = current(ploopi_timestamp2local($ts = sprintf("%04d%02d%02d000000",$this->arrOptions['year'], $this->arrOptions['month'], $d)));
                    $dateday = ploopi_timestamp2unixtimestamp($ts);

                    $extra_class = '';
                    $extra_title = '';
                    $onclick = '';

                    if (ploopi_holiday($dateday)) $extra_class = ' day_holiday';
                    else
                    {
                        if ($weekday > 5) $extra_class = ' day_weekend';
                    }
                    $strEventsKey = sprintf("%04d%02d%02d",$this->arrOptions['year'], $this->arrOptions['month'], $d);
                    if(!empty($arrEvents[$strEventsKey]))
                    {
                        $onclick = '';
                        foreach($arrEvents[$strEventsKey] as $strChannelId => $arrDayEvents)
                        {
                            foreach($arrDayEvents as $intId)
                            {
                                if (!empty($this->arrEvents[$intId]))
                                {
                                    $extra_title .=  ' - '.$this->arrEvents[$intId]->strTitle;
                                    if(empty($onclick))
                                    {
                                        $onclick = 'onClick="javascript:window.location.href=\''.$this->arrEvents[$intId]->strHref.'\';return(0);"';
                                        $extra_class .= ' day_event';
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <div class="day_num<?php echo $extra_class; ?>" title="<?php echo $date.$extra_title ?>" style="<?php echo $day_style; ?>" <?php echo $onclick;?>>
                        <?php echo $d; ?>
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
                        $extra_title = '';
                        $onclick = '';

                        if (ploopi_holiday($strTs)) $extra_class = ' day_holiday';
                        else
                        {
                            if ($c > 5) $extra_class = ' day_weekend';
                        }

                        $strEventsKey = substr(ploopi_unixtimestamp2timestamp($strTs), 0, 8);
                        if(!empty($arrEvents[$strEventsKey]))
                        {
                            $onclick = '';
                            foreach($arrEvents[$strEventsKey] as $strChannelId => $arrDayEvents)
                            {
                                foreach($arrDayEvents as $intId)
                                {
                                    if (!empty($this->arrEvents[$intId]))
                                    {
                                        $extra_title .=  ' - '.$this->arrEvents[$intId]->strTitle;
                                        if(empty($onclick))
                                        {
                                            $onclick = 'onClick="javascript:window.location.href=\''.$this->arrEvents[$intId]->strHref.'\';return(0);"';
                                            $extra_class .= ' day_num_grayed_event';
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="day_num_grayed<?php echo $extra_class; ?>" title="<?php echo $date.$extra_title ?>" style="<?php echo $day_style; ?>"<?php echo $onclick; ?>>
                            <?php echo $d; ?>
                        </div>
                    <?php
                    }
                    ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}
