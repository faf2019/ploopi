<?php
/*
    Copyright (c) 2008 Ovensia, CII67
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
 * Gestion des diagrammes en barre
 * 
 * @package ploopi
 * @subpackage barchart
 * @copyright Ovensia, CII67
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich, Frédéric Belloy, Laurent Ulrich
 */

class barchart
{
    /**
     * Largeur du diagramme
     *
     * @var int
     */
    
    private $width;
    
    /**
     * Hauteur du diagramme
     *
     * @var int
     */
    
    private $height;
    
    /**
     * Données du diagramme
     *
     * @var array
     */
    
    private $datasets;
    
    /**
     * Légende du diagramme
     *
     * @var array
     */
    
    private $legend;
    
    /**
     * Options du diagramme
     *
     * @var array
     */
    
    private $options;
    
    /**
     * Nombre de colonnes à afficher
     *
     * @var int
     */
    private $nb_columns;
    
    /**
     * Valeur max du diagramme
     *
     * @var float
     */
    private $value_max;
    
    /**
     * Constructeur de la classe
     *
     * @param int $width largeur du diagramme en pixel
     * @param int $height hauteur du diagramme en pixel
     * @param array $options tableau d'options
     * @return barchart
     * 
     * @see barchart::setoptions
     */
    
    public function barchart($width, $height, $options = array())
    {
        $this->width = $width;
        $this->height = $height;
        $this->values = array();
        $this->legend = array();
        $this->options = 
            array(
                'display_grid' => true,
                'display_legend' => true,
                'display_titles' => true,
                'class_name' => 'ploopi_barchart',
                'bar_arrange' => 'merge',  // merge, stack, side_by_side
                'padding' => 0
            );
            
        $this->setoptions($options);
        
    }
    
    /**
     * Ajoute un jeu de données au diagramme
     *
     * @param array $values tableau de données
     * @param string $dataset_label libellé du jeu de données (optionnel)
     * @param string $dataset_bgcolor couleur de fond du jeu de données (optionnel)
     * @param string $dataset_color couleur du texte du jeu de données (optionnel)
     * @param string $dataset_name nom du jeu de données (optionnel)
     */
         
    public function setvalues($values, $dataset_label = null, $dataset_bgcolor = null, $dataset_color = null, $dataset_name = null)
    {
        
        $this->datasets[(empty($dataset_name)) ? 'p'.sizeof($this->datasets) : htmlentities($dataset_name)] = 
            array(
                'values' => $values,
                'label' => $dataset_label,    
                'bgcolor' => $dataset_bgcolor,
                'color' => $dataset_color,
            );
                            
        $this->nb_columns = max($this->nb_columns, sizeof($values));
        $this->value_max = max($this->value_max, max($values));
    }
    
    /**
     * Ajoute une légende au diagramme
     *
     * @param array $legend tableau contenant la légende
     */
    
    public function setlegend($legend)
    {
        $this->legend = $legend;        
    }
    
    /**
     * Permet de définir les options : 
     * grid_width:int,
     * display_grid:boolean, 
     * display_legend:boolean, 
     * display_titles:boolean,
     * bar_arrange:string ('merge', 'stack', 'side_by_side'),
     * padding:0,
     * class_name:string
     *
     * @param array $options tableau des options à modifier
     */
    
    public function setoptions($options)
    {
        $this->options = array_merge($this->options, $options);        
    }
    
    /**
     * Affiche le diagramme
     *
     */
    
    public function draw()
    {
        /* Calcul de la valeur max pour les types 'stack'*/
        if($this->options['bar_arrange'] == 'stack' )
        {
            $this->value_max = 0;
            $sum_array = array();
            foreach($this->datasets as $dataset)
            {
                foreach($dataset['values'] as $key => $value)
                {
                    if (!isset($sum_array[$key])) $sum_array[$key] = 0;
                    $sum_array[$key] += $value;
                }
            }
            $this->value_max = max($sum_array);
        }
    
        /**
         * Définition de la largeur de grille si elle n'existe pas déjà
         */
            
        $this->options = 
            array_merge(
                array(
                    'grid_width' => ($this->value_max>=5) ? floor($this->value_max/5) : 1,
                ), 
                $this->options
            );        
        
        if( $this->options['bar_arrange'] == 'side_by_side' ) $this->nb_columns *=  sizeof($this->datasets);

        $column_width = floor($this->width / $this->nb_columns) - 1;
        
        ?>
        <div class="<? echo $this->options['class_name']; ?>">
            <?
            if ($this->options['display_legend'])
            {
                ?>
                <div class="vlegend" style="height:<? echo $this->height; ?>px;">
                <?
                for ($t = 1; $t < $this->value_max / $this->options['grid_width']; $t++)
                {
                    ?>
                    <div style="bottom:<? echo floor(($t * $this->options['grid_width'] * $this->height) / $this->value_max) - 3; ?>px;"><? echo $t * $this->options['grid_width']; ?></div>
                    <?
                }
                ?>
                &nbsp;
                </div>
                <?
            }
            ?>
            <div style="float:left;">
                <ul class="chart" style="width:<? echo $this->width; ?>px;height:<? echo $this->height; ?>px;">
                <?
                if ($this->options['display_grid'] && !empty($this->options['grid_width']))
                {
                    for ($t = 1; $t < $this->value_max / $this->options['grid_width']; $t++)
                    {
                        ?>
                        <div class="grid" style="left:-2px;width:<? echo $this->width+2; ?>;bottom:<? echo floor(($t * $this->options['grid_width'] * $this->height) / $this->value_max); ; ?>px;"></div>
                        <?
                    }
                }

                
                $dataset_index = 0;
                foreach($this->datasets as $dataset_name => $dataset)
                {
                    $c = 0;

                    $bar_color = (empty($dataset['bgcolor'])) ? '' : "background-color:{$dataset['bgcolor']};";
                    $bar_color .= (empty($dataset['color'])) ? '' : "color:{$dataset['color']};";
                    
                    foreach($dataset['values'] as $key => $value)
                    {
                        if (!empty($value) && is_numeric($value))
                        {
                            if( $this->options['bar_arrange'] == 'stack' )
                            {
                                if (!isset($previous_values[$key])) $previous_values[$key] = 0;
                                
                                $display_bottom = $previous_values[$key];
                                $previous_values[$key] += $value;
                            }
                            else $display_bottom = 0;
            
                            if( $this->options['bar_arrange'] == 'side_by_side' )
                            {
                                $col_width = $column_width - ceil(($this->options['padding'] * 2) / sizeof($this->datasets));
                                $column_left = ($c * sizeof($this->datasets)) * ($column_width+1) + $dataset_index * ($col_width+1);
                            }
                            else
                            {
                                $col_width = $column_width - $this->options['padding'] * 2;
                                $column_left = $c * ($column_width+1) + $this->options['padding'];
                            }
                            
                            $display_bottom = floor(($display_bottom * $this->height) / $this->value_max);
                            $column_height = floor(($value * $this->height) / $this->value_max);
                            
                            $style = sprintf("height:%dpx;width:%dpx;left:%dpx;bottom:%dpx;%s", $column_height, $col_width, $column_left, $display_bottom, $bar_color);
                            
                            if ($this->options['display_titles']) 
                            {
                                $style .= 'cursor:help;';
                                
                                $title = (empty($dataset['label'])) ? '' : $dataset['label'].': ';
                                $title = 'title="'.htmlentities($title.strip_tags($this->legend[$key]).', '.$value).'"'; 
                            }
                            else $title = '';
                            
                            ?>
                            <li class="<? echo $dataset_name; ?>" style="<? echo $style; ?>" <? echo $title ?>>
                                <? echo $value; ?>
                            </li>
                            <?
                        }
                        
                        $c++;
                    }
                    
                    $dataset_index += 1;
                }
                ?>
                </ul>
                <?
                if ($this->options['display_legend'])
                {
                    ?>
                    <div class="hlegend" style="width:<? echo $this->width; ?>px;">
                    <?
                        $legend_column_width = $column_width+1;
                        if ($this->options['bar_arrange'] == 'side_by_side') $legend_column_width *= sizeof($this->datasets);
                        
                        foreach($this->legend as $key => $label)
                        {
                            ?>
                            <div style="width:<? echo $legend_column_width; ?>px; "><? echo $label; ?></div>
                            <?
                        }
                    ?>        
                    </div>
                    <?
                }
                ?>
            </div>
        </div>
        <?
    }
}
