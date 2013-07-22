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
                'display_values' => true, // affichage des valeurs sur les barres optionnel
                'autofit_scale' => true, // échelle verticale auto-adaptive
                'display_grid' => true, // affichage d'une grille horizontale
                'display_legend' => true, // affichage d'une légende
                'display_ticks' => true, // affichage des valeurs de l'échelle (vertical seulement)
                'display_titles' => true, // affichage de titres
                'class_name' => 'ploopi_barchart', // class de style (css) utilisée
                'bar_arrange' => 'merge', // type d'arrangement des barres : merge, stack, side_by_side
                'padding' => 0, // marge utilisée à l'affichage
				'yaxis_pos'=> 0 // positionnement personnalisé de l'axe des ordonnées
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

        $this->datasets[(empty($dataset_name)) ? 'p'.sizeof($this->datasets) : ploopi_htmlentities($dataset_name)] =
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
     * autofit_scale:boolean,
     * display_grid:boolean,
     * display_legend:boolean,
     * display_ticks:boolean,
     * display_titles:boolean,
     * display_values:boolean,
     * bar_arrange:string ('merge', 'stack', 'side_by_side'),
     * padding:int,
     * yaxis_pos:int,
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

        //mise à l'échelle verticale auto-adaptive
        $autofit_scale_inc = 0;

        $pow = $value_max_root = $value_max_margin = $value_max_rounded1 = 0;
        $scale_div = $sliced_range = 1;
                            
        if ($this->value_max)
        {
            $pow = floor(log10($this->value_max));
    
            if( $this->options['autofit_scale'] )
            {
                $autofit_scale_inc = 1 ;
    
                $value_max_1st= floor($this->value_max / pow(10,$pow)) ;
                $value_max_2nd = floor(($this->value_max - $value_max_1st * pow(10,$pow) ) / pow(10, ($pow-1))) ;
                $value_max_3rd = floor(($this->value_max - $value_max_1st * pow(10,$pow) - $value_max_2nd * pow(10, ($pow-1)) ) / pow(10, ($pow-2))) ;


				$value_max_root = $value_max_1st * pow(10,$pow) + $value_max_2nd * pow(10, ($pow-1)) ;
				$value_max_margin = round( floor( $value_max_root * 0.15 ) / 10 , 1 ) ;
				$value_max_rounded1 = $value_max_root + $value_max_3rd * pow(10, ($pow-2)) ;
            }
        }
        
		/* Les tables div_range1 à 5 représentent le nombre de divisions de l'échelle, suivies des seuils de passage à l'échelle supérieure pour les valeurs comprises entre 10 et 20. Ces valeurs sont modifiables en fonction des goûts esthétiques de chacun.	*/
		
		$div_range1 = array(5,6,10,10.1);
		$div_range2 = array(4,6,10.11,12.1);
		$div_range3 = array(5,6,12.11,15.2);
		$div_range4 = array(6,5,15.21,18.2);
		$div_range5 = array(4,5,18.21,20.3);

		$whole_range = array($div_range1,$div_range2,$div_range3,$div_range4,$div_range5);

		$this->value_max = $this->value_max / pow(10, ($pow-1)) ;
		$value_max_root = $value_max_root / pow(10, ($pow-1)) ;
		$value_max_margin = $value_max_margin / pow(10, ($pow-1)) ;
		$value_max_rounded1 = $value_max_rounded1 / pow(10, ($pow-1)) ;

		if ($this->value_max <= 20 )
		{
			foreach($whole_range as $real_range)
			{
			
				if( $this->value_max <= $real_range[sizeof($real_range)-1]  && $this->value_max >= $real_range[sizeof($real_range)-2] )
				{
					foreach ( array_slice($real_range,0,sizeof($whole_range[1])-3) as $sliced_range)
					{
						for ($myval = $value_max_root ; $myval < $real_range[sizeof($real_range)-1] ; $myval++)
						{
							if ( fmod ($myval,$sliced_range) == 0 && $value_max_rounded1 < $myval + $value_max_margin )
							{
								$scale_div = $sliced_range ;
								$this->value_max = $myval  ;
								break 2;
							}
						}
					}
					break;
				}
			
			}
			$scale_div = $sliced_range ;
		}
		else
		{
			$this->value_max = (( $value_max_2nd < 5 && $value_max_1st < 4 ) ? ( $value_max_1st * pow(10,$pow) + 5 * pow(10,$pow-1) ) / pow(10, 2 - $pow) : ( ($value_max_1st >= 8) ? pow(10,  $pow + 1 )  : ( $value_max_1st + 1 )* pow(10,$pow) ) * pow(10,$pow -2)) / pow(10, 2 * $pow - 3)  ;
			$scale_div = ( $value_max_2nd < 5 && $value_max_1st < 4 ) ? $this->value_max / 5  : (( $value_max_1st == 7 ) ?  4 : ( ($value_max_1st >= 8) ? 5 : $value_max_1st + 1 ))  ;
		}

	    $this->value_max =  $this->value_max * pow(10, ($pow-1)) ;

			

        /**
         * Définition de la largeur de grille si elle n'existe pas déjà
         */

        $this->options =
            array_merge(
                array(
                    'grid_width' => ( $this->value_max >=10 ) ? floor($this->value_max/$scale_div) : $this->value_max/$scale_div ,
                ),
                $this->options
            );

        if( $this->options['bar_arrange'] == 'side_by_side' ) $this->nb_columns *=  sizeof($this->datasets);

        $column_width = floor($this->width / $this->nb_columns) - 1;

        ?>
        <div class="<?php echo $this->options['class_name']; ?>" style ="margin-top: 10px;">
            <?php
            if ($this->value_max)
            {
                if ($this->options['display_legend'])
                {
                    ?>
                    <div class="vlegend" style="height:<?php echo $this->height; ?>px;">
                    <?php
                    for ($t = 1; $t < $this->value_max / $this->options['grid_width'] + $autofit_scale_inc ; $t++)
                    {
						$vscale_value=$t * $this->options['grid_width'];
						$vscale_value = ( $this->options['yaxis_pos'] ) ? $this->options['yaxis_pos'] + $vscale_value / $this->value_max : $vscale_value  ;
                        ?>
                        <div style="bottom:<?php echo floor(($t * $this->options['grid_width'] * $this->height) / $this->value_max) -5 ; ?>px;"><?php echo $vscale_value; ?></div>
                        <?php
                    }
                    ?>
                    &nbsp;
                    </div>
                    <?php
                }
                ?>
                <div style="float:left;">
                    <ul class="chart" style="left: <?php if( $this->options['autofit_scale']) echo strlen(floor($this->value_max))*5 ; ?>px; width:<?php echo $this->width; ?>px;height:<?php echo $this->height; ?>px;">
                    <?php
                    if ($this->options['display_grid'] && !empty($this->options['grid_width']))
                    {
                        for ($t = 1; $t < $this->value_max / $this->options['grid_width'] + $autofit_scale_inc; $t++)
                        {
                            ?>
                            <div class="grid" style="left:-2px;width:<?php echo $this->width+2; ?>;bottom:<?php echo floor(($t * $this->options['grid_width'] * $this->height) / $this->value_max); ; ?>px;"></div>
                            <?php
                        }
                    }
    				
					$this->value_max -= ( $this->options['yaxis_pos'] ) ? $this->options['yaxis_pos'] : 0 ;
 
                    $dataset_index = 0;
                    foreach($this->datasets as $dataset_name => $dataset)
                    {
                        $c = 0;
    
                        $bar_color = (empty($dataset['bgcolor'])) ? '' : "background-color:{$dataset['bgcolor']};";
                        $bar_color .= (empty($dataset['color'])) ? '' : "color:{$dataset['color']};";
    
                        foreach($dataset['values'] as $key => $value)
                        {
							#$value -= ( $this->options['yaxis_pos'] && $value > $this->options['yaxis_pos'] ) ? $this->options['yaxis_pos'] : 0 ;
							#Correction bug 197% dispo

							$value -= ( $this->options['yaxis_pos'] ) ? (( $value > $this->options['yaxis_pos'] ) ? $this->options['yaxis_pos'] : $value ) : 0 ;

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
    
								$value += ( $value>0 ) ? $this->options['yaxis_pos'] : 0 ;

                                if ($this->options['display_titles'])
                                {
                                    $style .= 'cursor:help;';
    
                                    $title = (empty($dataset['label'])) ? '' : $dataset['label'].': ';
                                    $title = 'title="'.ploopi_htmlentities($title.strip_tags($this->legend[$key]).', '.$value).'"';
                                }
                                else $title = '';
    
                                ?>
                                <li class="<?php echo $dataset_name; ?>" style="<?php echo $style; ?>" <?php echo $title ?>>
                                    <?php if ($this->options['display_values']) echo $value; //affichage des valeurs rendu optionnel ?>
                                </li>
                                <?php
                            }
    
                            $c++;
                        }
    
                        $dataset_index += 1;
                    }
                    ?>
                    </ul>
                    <?php
                    if ($this->options['display_ticks'])
                    {
                        ?>
                        <div class="hlegend" style="left: <?php $this->value_max += ( $this->options['yaxis_pos'] ) ? $this->options['yaxis_pos'] : 0 ; if( $this->options['autofit_scale']) echo strlen(floor($this->value_max))*5 ; ?>px; width:<?php echo $this->width; ?>px;">
                        <?php
                            $ticks_column_width = $column_width+1;
                            if ($this->options['bar_arrange'] == 'side_by_side') $ticks_column_width *= sizeof($this->datasets);
    
                            foreach($this->legend as $key => $label)
                            {
                                //alignement automatique de l'échelle horizontale ?>
                                <div style="text-align: center; text-indent: <?php if ($this->options['bar_arrange']=='side_by_side') echo -2 * $this->options['padding'] ; ?>px ; width:<?php echo $ticks_column_width; ?>px; "><?php echo $label; ?></div>
                                <?php
                            }
                        ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
            if ($this->options['display_legend'])
            {
                ?>
                <div class="caption">
                <?php
                /* 
                 * légende en bas à droite du graph, avec alignement automatique du texte (en fonction de la longueur de chaîne):
                 * si les descriptions de la légende sont de longueur différente, le texte et la légende seront alognés en fonction
                 * de la chaîne la plus longue
                 */
                
                    $max_label_strl = 0;
    
                    foreach($this->datasets as $dataset)
                    {
                     $max_label_strl = (strlen($dataset['label']) > $max_label_strl) ? strlen($dataset['label']) : $max_label_strl  ;
                     ?>
                        <div style="position: relative ; clear:both ;top : 10px ;margin-left: <?php echo $this->width + strlen(floor($this->value_max))*10 -7 * $max_label_strl - 20 ; ?>px;
                        font-size: 0px; line-height: 0%; width: 0px;
                        border-top: 5px solid <?php echo $dataset['bgcolor']?>;
                        border-bottom: 5px solid <?php echo $dataset['bgcolor']?>;
                        border-left: 5px solid <?php echo $dataset['bgcolor']?>;
                        border-right: 5px solid <?php echo $dataset['bgcolor']?>;">
                        </div>
                        <div style="position:relative; top: -2px; margin-left: <?php echo $this->width + strlen(floor($this->value_max))*10 - 7 * $max_label_strl ; ?>px;"> <?php echo $dataset['label'] ; ?> </div>
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
}
