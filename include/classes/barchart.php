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
     * @return barchart
     */
    
    public function barchart($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->values = array();
        $this->legend = array();
        $this->options = array();
    }
    
    /**
     * Ajoute un jeu de données au diagramme
     *
     * @param array $values tableau de données
     * @param string $dataset_name nom du jeu de données
     */
    
    public function setvalues($values, $dataset_name = null)
    {
        if (empty($dataset_name)) $dataset_name = 'p'.sizeof($this->datasets);
        else $dataset_name = htmlentities($dataset_name);
        $this->datasets[$dataset_name] = $values;    

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
     * Permet de définir les options (grid_width:int, display_grid:boolean, display_legend:boolean, class_name:string)
     *
     * @param array $options tableau des options à modifier
     */
    
    public function setoptions($options)
    {
        $this->options = $options;        
    }
    
    /**
     * Affiche le diagramme
     *
     */
    
    public function draw()
    {
        $default_options = 
            array(
                'grid_width' => ($this->value_max>=5) ? floor($this->value_max/5) : 1,
                'display_grid' => true,
                'display_legend' => true,
                'class_name' => 'ploopi_barchart',
            );
        
        $this->options = array_merge($default_options, $this->options);        
        
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
                
                if ($this->options['display_legend'])
                {
                }
                
                foreach($this->datasets as $dataset_name => $dataset)
                {
                    $c = 0;
                    foreach($dataset as $key => $value)
                    {
                        if (!empty($value) && is_numeric($value))
                        {
                            $column_height = floor(($value * $this->height) / $this->value_max);
                            ?>
                            <li class="<? echo $dataset_name; ?>" style="height:<? echo $column_height; ?>px;width:<? echo $column_width; ?>px;left:<? echo $c*($column_width+1); ?>px;">
                                <? echo $value; ?>
                            </li>
                            <?
                        }
                        $c++;
                    }
                }
                ?>
                </ul>
                <?
                if ($this->options['display_legend'])
                {
                    ?>
                    <div class="hlegend" style="width:<? echo $this->width; ?>px;">
                    <?
                        $c = 0;
                        foreach($this->legend as $key => $label)
                        {
                            ?>
                            <div style="width:<? echo $column_width+1; ?>px; ?>px;"><? echo $label; ?></div>
                            <?
                            $c++;
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