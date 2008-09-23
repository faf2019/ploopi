<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Gestion de l'affichage des modules.
 * 
 * @package ploopi
 * @subpackage skin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Regroupe des méthodes génériques pour afficher bloc, menus, onglets, barre d'outils, popups, etc...
 * 
 * @package ploopi
 * @subpackage skin
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class skin_common
{
    
    /**
     * Constructeur de la classe skin_common
     *
     * @param string $skin nom du skin (nom du dossier)
     * @return skin_common
     */
    
    public function skin_common($skin)
    {
        $this->values = array();
        $this->values['path'] = "./templates/backoffice/{$skin}/img";
        $this->values['inifile'] = "./templates/backoffice/{$skin}/skin.ini";
        if (file_exists($this->values['inifile'])) $this->values = array_merge($this->values,parse_ini_file($this->values['inifile']));
    }

    /**
     * Créé un haut de bloc
     *
     * @param string $title titre du bloc
     * @param string $style styles optionnels du bloc
     * @param string $styletitle styles optionnels du titre
     * @param srting $additionnal_title titre additionnel
     * @return string code html de l'entête du bloc
     */
    
    public function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" id=\"handle\" style=\"{$styletitle}\"><div class=\"simplebloc_titleleft\">{$additionnal_title}{$title}</div></div>";

        $res .= '<div class="simplebloc_content">';

        return $res;
    }

    /**
     * Créé un bas de bloc (ferme le dernier bloc ouvert) 
     *
     * @return string code html du pied du bloc
     */
    
    function close_simplebloc()
    {
        return '</div><div class="simplebloc_footer"></div></div>';
    }

    
    /**
     * Crée un titre de page
     *
     * @param string $title titre de la page
     * @param string $style styles optionnels
     * @return string code html du titre
     */
    
    public function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}</p>{$title}</div>";
        else $res = "<div class=\"pagetitle\"><p>{$additionnal_title}</p>{$title}</div>";

        return $res;
    }

    /**
     * Crée une barre d'outils (icones)
     *
     * @param array $icons tableau associatif d'icones (propriétés : title, url, icon, width, confirm, javascript)
     * @param string $iconsel clé de l'icone sélectionnée (par référence), sélectionne par défaut la première icone
     * @param boolean $sel true si la sélection est gérée, par défaut tru
     * @param boolean $vertical true si l'affichage est vertical, par défaut false
     * @return string code html de la barre d'outils
     */
    
    public function create_toolbar($icons, &$iconsel, $sel = true, $vertical = false)
    {
        if (!isset($icons[$iconsel])) $iconsel = -1;

        $icons_content_left = '';
        $icons_content_right = '';

        if ($sel)
        {
            foreach($icons AS $key => $value)
            {
                if ($iconsel == -1) $iconsel = $key;
            }
        }


        foreach($icons AS $key => $value)
        {
            if (isset($icons[$key]['position']) && $icons[$key]['position'] == 'right')
            {
                if ($sel)
                {
                    $icons_content_right .= $this->create_icon($icons[$key], ($iconsel == $key), $key, $vertical);
                }
                else
                {
                    $icons_content_right .= $this->create_icon($icons[$key], false, $key, $vertical);
                }
            }
            else
            {
                if ($sel)
                {
                    $icons_content_left .= $this->create_icon($icons[$key], ($iconsel == $key), $key, $vertical);
                }
                else
                {
                    $icons_content_left .= $this->create_icon($icons[$key], false, $key, $vertical);
                }
            }
        }

        $res =  "
                <div class=\"toolbar\">
                    <div class=\"toolbar_left\">{$icons_content_left}</div>
                    <div class=\"toolbar_right\">{$icons_content_right}</div>
                </div>
                ";

        return $res;
    }

    /**
     * Crée une icone pour la barre d'outils
     *
     * @param array $icon icone à afficher (propriétés : title, url, icon, width, confirm, javascript)
     * @param boolean $sel true si l'icone est sélectionnée
     * @param string $key clé (propriété id) de l'icone
     * @param boolean $vertical true si l'affichage est vertical
     * @return string code html de l'icone
     */
    
    public function create_icon($icon, $sel, $key, $vertical)
    {
        $confirm = isset($icon['confirm']);

        $title = $icon['title'];

        if (!empty($icon['javascript'])) $onclick = $icon['javascript'];
        elseif ($confirm) $onclick = "ploopi_confirmlink('".ploopi_urlencode($icon['url'])."','{$icon['confirm']}')";
        else $onclick = "document.location.href='".ploopi_urlencode($icon['url'])."'";

        if (isset($icon['icon']))
        {
            $classpng = '';
            //if (strtolower(substr($icon['icon'],-4,4)) == '.png') $classpng = 'class="png"';
            $image = "<img $classpng alt=\"".strip_tags($title)."\" src=\"$icon[icon]\">";
        }
        else $image = '';

        $class = ($vertical) ? 'toolbar_icon_vertical' : 'toolbar_icon';

        $style = (!empty($icon['width'])) ? "style=\"width:{$icon['width']}px;\"" : '';

        if ($sel)
        {
            $res =  "
                    <div class=\"{$class}_sel\" id=\"{$key}\" {$style}>
                        <a href=\"javascript:void(0);\" onclick=\"javascript:{$onclick};return false;\">
                            <div class=\"toolbar_icon_image\">$image</div>
                            <p>$title</p>
                        </a>
                    </div>
                    ";
        }
        else
        {
            $res =  "
                    <div class=\"{$class}\" id=\"{$key}\" {$style}>
                        <a href=\"javascript:void(0);\" onclick=\"javascript:{$onclick};return false;\">
                            <div class=\"toolbar_icon_image\">$image</div>
                            <p>$title</p>
                        </a>
                    </div>
                    ";
        }

        return $res;
    }

    /**
     * Crée une barre d'onglets
     *
     * @param array $tabs tableau associatif d'onglets (propriétés : title, url, width)
     * @param string $tabsel clé de l'onglet sélectionné (par référence), sélectionne par défaut le premier onglet
     * @return string code html de la barre d'onglets
     */
    
    public function create_tabs($tabs, &$tabsel)
    {

        $res = "<div class=\"tabs\">";

        if (!isset($tabs[$tabsel])) $tabsel = -1;

        foreach($tabs AS $key => $value)
        {
            if ($tabsel == -1) $tabsel = $key;
            $res .= $this->create_tab($tabs[$key], ($tabsel==$key));
        }

        $res .= "</div>";

        return $res;
    }

    /**
     * Crée un onglet
     *
     * @param array $tab onglet (propriétés : title, url, width)
     * @param boolean $sel true si l'onglet est sélectionné
     * @return string code html de l'onglet
     */
    
    public function create_tab($tab, $sel)
    {
        if (!empty($tab['width'])) $style = "style=\"width:{$tab['width']}px;\"";
        else $style = '';

        if ($sel) $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" class=\"selected\" {$style}>{$tab['title']}</a>";
        else  $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" {$style}>{$tab['title']}</a>";
        return $res;
    }

    /**
     * Crée un faux popup (div)
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */
    
    function create_popup($title, $content, $popupid = 'ploopi_popup')
    {
        $res =  '
                <div class="simplebloc" style="margin:0;">
                    <a name="anchor_'.$popupid.'"></a>
                    <div class="simplebloc_title">
                        <div class="simplebloc_titleleft">
                            <img alt="Fermer" onclick="javascript:ploopi_hidepopup(\''.$popupid.'\');" style="display:block;float:right;margin:2px;cursor:pointer;" src="'.$this->values['path'].'/template/close_popup.png">
                            <div style="overflow:auto;cursor:move;" id="handle_'.$popupid.'">'.$title.'</div>
                        </div>
                    </div>
                    <div class="simplebloc_content">'.$content.'</div>
                    <div class="simplebloc_footer" style="cursor:move;" id="handlebottom_'.$popupid.'"></div>
                </div>
                <script type="text/javascript">
                new Draggable(\''.$popupid.'\', { handle: \'handle_'.$popupid.'\'});
                new Draggable(\''.$popupid.'\', { handle: \'handlebottom_'.$popupid.'\'});
                document.location.href=\'#anchor_'.$popupid.'\';
                </script>        
                ';

        return($res);
    }


    /**
     * Trie le tableau avancé
     *
     * @param array $a valeur a
     * @param array $b valeur b
     * @return boolean
     */
    
    public function array_sort($a,$b)
    {
        $a_label = isset($this->array_values[$a]['values'][$this->array_orderby]['sort_label']) ? 'sort_label' : 'label';
        $b_label = isset($this->array_values[$b]['values'][$this->array_orderby]['sort_label']) ? 'sort_label' : 'label';

        $a_val = &$this->array_values[$a]['values'][$this->array_orderby][$a_label];
        $b_val = &$this->array_values[$b]['values'][$this->array_orderby][$b_label];

        if ($this->array_sort == 'ASC') return($a_val>$b_val);
        else return($b_val>$a_val);
    }

    /**
     * Affiche un tableau avancé
     *
     * @param array $columns définition des colonnes
     * @param array $values contenu du tableau
     * @param string $array_id identifiant du tableau
     * @param array $options options d'affichage
     * 
     * propriétés des colonnes : label, width, styles, options
     * propriétés des valeurs : label, style, sort_label
     * propriétés des options : height, sortable, orderby_default, sort_default
     * 
     * <code>
     * <?php
     * $array_columns = array();
     * $array_values = array();
     * 
     * $array_columns['left']['colonne1'] = array('label' => 'Colonne 1', 'width' => '100', 'options' => array('sort' => true));
     * $array_columns['right']['colonne2'] = array('label' => 'Colonne 2', 'width' => '100','options' => array('sort' => true));
     * $array_columns['auto']['colonneauto'] = array('label' => 'Colonne Auto', 'options' => array('sort' => true));
     * 
     * $c = 0;
     * 
     * $array_values[$c]['values']['colonne1'] = array('label' => 'valeur1');
     * $array_values[$c]['values']['colonne2'] = array('label' => 'valeur2', 'style' => 'text-align:right');
     * $array_values[$c]['values']['colonneauto'] = array('label' => 'valeur3', 'sort_label' => '3');
     * 
     * $skin->display_array($array_columns, $array_values, 'id_tableau', array('height' => 200, 'sortable' => true, 'orderby_default' => 'valeur3', 'sort_default' => 'DESC'));
     * ?>
     * </code>
     */
    
    public function display_array($columns, $values, $array_id = null, $options = null)
    {
        if (empty($array_id)) $array_id = md5(uniqid(rand(), true));

        $sort = $orderby = '';

        if (!empty($_SESSION['ploopi']['arrays'][$array_id]))
        {
            // preserve orderby & sort values
            $orderby = $_SESSION['ploopi']['arrays'][$array_id]['orderby'];
            $sort = $_SESSION['ploopi']['arrays'][$array_id]['sort'];
        }

        $_SESSION['ploopi']['arrays'][$array_id] = array('columns' => $columns, 'values' => $values, 'options' => $options, 'orderby' => $orderby, 'sort' => $sort);

        $array = &$_SESSION['ploopi']['arrays'][$array_id];

        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            $array['sortable_columns'] = array();
            if (!empty($array['columns']['left']))
            {
                foreach($array['columns']['left'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['left'][$id]['onclick'] = "ploopi_skin_array_refresh('{$array_id}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['auto']))
            {
                foreach($array['columns']['auto'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['auto'][$id]['onclick'] = "ploopi_skin_array_refresh('{$array_id}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['right']))
            {
                foreach($array['columns']['right'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['right'][$id]['onclick'] = "ploopi_skin_array_refresh('{$array_id}', '{$id}');";
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

        }

        ?>
        <div class="ploopi_explorer_main" id="ploopi_explorer_main_<? echo $array_id; ?>" style="visibility:visible;">
        <? $this->display_array_refresh($array_id); ?>
        </div>
        <?

    }

    /**
     * Rafraichit l'affichage d'un tableau avancé
     *
     * @param string $array_id id du tableau
     * @param string $orderby colonne de tri
     */
    
    /**
     * Rafraichit l'affichage d'un tableau avancé
     *
     * @param string $array_id id du tableau
     * @param string $orderby colonne de tri
     */
    
    public function display_array_refresh($array_id, $orderby = null)
    {
        // On récupère le tableau stocké en session (identifié par array_id)
        $array = &$_SESSION['ploopi']['arrays'][$array_id];

        $sort_img = '';

        // si le tableau est "triable" (option)
        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            // initialisation  du tri par défaut pour le tableau courant
            if (empty($array['orderby']))
            {
                if (!empty($array['options']['orderby_default'])) $array['orderby'] = $array['options']['orderby_default'];
                elseif (!empty($array['sortable_columns'][0])) $array['orderby'] = $array['sortable_columns'][0];
            }

            if (empty($array['sort']))
            {
                if (!empty($array['options']['sort_default'])) $array['sort'] = $array['options']['sort_default'];
                else $array['sort'] = 'ASC';
            }
            // on réinitialise l'ordre de tri si l'ordreby est différent du précédent
            if (!empty($orderby))
            {
                if ($orderby != $array['orderby']) $array['sort'] = 'ASC';
                else $array['sort'] = ($array['sort'] == 'ASC') ? 'DESC' : 'ASC';
            }
            
            $_SESSION['testsort'] = $array['sort'];

            // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
            $array['orderby'] = (empty($orderby)) ? $array['orderby'] : $orderby;


            $this->array_values = $array['values'];
            $this->array_sort = $array['sort'];
            $this->array_orderby = $array['orderby'];

            uksort ($array['values'], array($this, 'array_sort'));

            $sort_img = ($array['sort'] == 'DESC') ? "<img src=\"{$this->values['path']}/arrays/arrow_down.png\">" : "<img src=\"{$this->values['path']}/arrays/arrow_up.png\">";
        }

        $i = 0;
        $w = 0;
        
        // on insère d'abord la colonne optionnelle de droite (actions)
        if (!empty($array['columns']['actions_right']))
        {
            foreach($array['columns']['actions_right'] as $id => $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<? echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<? echo $array_id; ?>_<? echo $i; ?>"></div>
                <?
                $i++;
            }
        }

        // on insère ensuite les colonnes de données de droite (optionnelles)
        if (!empty($array['columns']['right']))
        {
            foreach($array['columns']['right'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<? echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<? echo $array_id; ?>_<? echo $i; ?>"></div>
                <?
                $i++;
            }
        }

        $w = 0;
        
        // puis les colonnes de données de gauche (optionnelles)
        if (!empty($array['columns']['left']))
        {
            foreach($array['columns']['left'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="left:<? echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<? echo $array_id; ?>_<? echo $i; ?>"></div>
                <?
                $i++;
            }
        }

        // on gère ensuite l'affichage des titres de colonne
        ?>
        <div style="position:relative;">
            <div class="ploopi_explorer_title" id="ploopi_explorer_title_<? echo $array_id; ?>">
                <?
                // titres des colonnes d'action (à droite)
                if (!empty($array['columns']['actions_right']))
                {
                    foreach($array['columns']['actions_right'] as $id => $c)
                    {
                        ?>
                        <a href="<? echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <? if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<? echo $c['width']; ?>px;float:right;<? if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><? echo $c['label']; ?>&nbsp;</span></p></a>
                        <?
                    }
                }

                // titres des colonnes de données à droite
                if (!empty($array['columns']['right']))
                {
                    foreach($array['columns']['right'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<? echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <? if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<? echo $c['width']; ?>px;float:right;<? if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><? echo $c['label']; ?>&nbsp;</span><? echo $img; ?></p></a>
                        <?
                    }
                }

                // titres des colonnes de données à gauche
                if (!empty($array['columns']['left']))
                {
                    foreach($array['columns']['left'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<? echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <? if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<? echo $c['width']; ?>px;float:left;<? if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><? echo $c['label']; ?>&nbsp;</span><? echo $img; ?></p></a>
                        <?
                    }
                }

                // titre de la colonne centrale (auto)
                if (!empty($array['columns']['auto']))
                {
                    foreach($array['columns']['auto'] as $id => $c)
                    {
                        $img = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $c['style'] .= 'background-color:#e0e0e0;';
                        }
                        ?>
                        <a href="<? echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <? if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="overflow:auto;<? if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><? echo $c['label']; ?>&nbsp;</span><? echo $img; ?></p></a>
                        <?
                    }
                }
                ?>
            </div>

            <?
            // Gestion de l'affichage des lignes de données
            ?>
            
            <div <? if (!empty($array['options']['height'])) echo "style=\"height:{$array['options']['height']}px;overflow:auto;\""; ?> id="ploopi_explorer_values_outer_<? echo $array_id; ?>">

                <div id="ploopi_explorer_values_inner_<? echo $array_id; ?>">
                <?

                foreach($array['values'] as $v)
                {
                    // alternance des couleurs (une ligne sur 2) : on joue sur les css
                    $color = (empty($color) || $color == 1) ? 2 : 1;
                    ?>
                    <div <? if (!empty($v['id'])) echo "id=\"{$v['id']}\""; ?> class="ploopi_explorer_line_<? echo $color; ?>" <? if (!empty($v['style'])) echo "style=\"{$v['style']}\""; ?>>
                        <?
                        if (!empty($array['columns']['actions_right']))
                        {
                            foreach($array['columns']['actions_right'] as $id => $c)
                            {
                                ?>
                                <div class="ploopi_explorer_tools" style="width:<? echo $c['width']; ?>px;float:right;<? if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>"><? echo $v['values'][$id]['label']; ?></div>
                                <?
                            }
                        }

                        $option = (empty($v['option'])) ? '' : $v['option'];

                        if (!empty($v['link']) || !empty($v['onclick']))
                        {
                            $onclick = (empty($v['onclick'])) ? '' : "onclick=\"{$v['onclick']}\"";
                            $title = (empty($v['description'])) ? '' : 'title="'.htmlentities($v['description']).'"';
                            ?>
                            <a class="ploopi_explorer_link" href="<? echo $v['link']; ?>" <? echo $title ; ?> <? echo $onclick ; ?> <? echo $option; ?>>
                            <?
                        }
                        if (!empty($array['columns']['right']))
                        {
                            foreach($array['columns']['right'] as $id => $c)
                            {
                                ?>
                                <div style="width:<? echo $c['width']; ?>px;float:right;<? if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><? echo $v['values'][$id]['label']; ?></p></div>
                                <?
                            }
                        }

                        if (!empty($array['columns']['left']))
                        {
                            foreach($array['columns']['left'] as $id => $c)
                            {
                                ?>
                                <div style="width:<? echo $c['width']; ?>px;float:left;<? if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><? echo $v['values'][$id]['label']; ?></p></div>
                                <?
                            }
                        }

                        if (!empty($array['columns']['auto']))
                        {
                            foreach($array['columns']['auto'] as $id => $c)
                            {
                                ?>
                                <div style="<? if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element"><p><? echo $v['values'][$id]['label']; ?></p></div>
                                <?
                            }
                        }
                        if (!empty($v['link']) || !empty($v['onclick']))
                        {
                            ?>
                            </a>
                            <?
                        }
                        ?>
                    </div>
                    <?
                }
                ?>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            ploopi_skin_array_renderupdate('<? echo $array_id; ?>');
        </script>
        <?
    }
    
    /**
     * Affiche un treeview
     *
     * @param array $nodes tableau associatif contenant les noeuds 
     * @param array $treeview tableau contenant la hiérarchie des noeuds
     * @param string $node_id_sel identifiant du noeud sélectionné
     * @param string $node_id_from identifiant du noeud de départ (permet de n'afficher qu'un sous-ensemble)
     * @return string code html du treeview
     */
    
    public function display_treeview(&$nodes, &$treeview, $node_id_sel = null, $node_id_from = null)
    {
        // recherche du premier noeud
        if (is_null($node_id_from)) $node_id_from = key($treeview); 
        
        if (!empty($node_id_sel) && isset($nodes[$node_id_sel])) $nodesel = $nodes[$node_id_sel];
        
        // code html généré par ce niveau de boucle
        $html = '';
        
        if (isset($treeview[$node_id_from]))
        {
            $c = 0;
            foreach($treeview[$node_id_from] as $node_id)
            {
                // noeud courant
                $node = $nodes[$node_id];
                
                // true si le noeud courant est sélectionné
                $is_node_sel = (!empty($node_id_sel) && ($node_id_sel == $node['id']));
    
                // parents du noeud sélectionné
                $nodesel_parents = (isset($nodesel)) ? $nodesel['parents'] : array();
                
                // parents du noeud courant
                $node_parents = array_merge($node['parents'], array($node['id']));
    
                // true si le noeud est ouvert : le noeud est ouvert si les parents du noeud courant et du noeud sélectionné se superposent
                $is_node_opened = sizeof(array_intersect_assoc($nodesel_parents, $node_parents)) == sizeof($node_parents);
                
                // true si le noeud est le dernier fils de son père
                $is_node_last = ($c == sizeof($treeview[$node_id_from])-1);
                
                // profondeur du noeud ( = nombre de noeuds parents)
                $node_depth = sizeof($node['parents']);
                
                $node_link = '';
                $bg = '';
    
                if ($node_depth == 1)
                {
                    // au premier niveau de profondeur, on ne crée pas de décalage
                    $marginleft = 0;
                }
                else
                {
                    $type_node = 'join';
                    if (isset($treeview[$node_id])) $type_node = ($is_node_sel || $is_node_opened) ? 'minus' : 'plus';
    
                    if (!$is_node_last) 
                    {
                        $type_node .= 'bottom';
                        $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                    }
                    
                    $n_link = (empty($node['node_link'])) ? 'javascript:void(0);' : $node['node_link']; 
                    $n_onclick = (empty($node['node_onclick'])) ? '' : 'onclick="javascript:'.$node['node_onclick'].';"'; 
                    
                    $node_link = "<a href=\"{$n_link}\" {$n_onclick}><img id=\"t{$node['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$type_node}.png\" /></a>";
                    
                    $marginleft = 20;
                }
    
                // récupération du code html des noeuds fils par un appel récursif    
                $html_children = ($is_node_sel || $is_node_opened || $node_depth == 1) ? $this->display_treeview($nodes, $treeview, $node_id_sel, $node['id']) : '';
    
                // si du contenu à afficher, display = 'block'
                $display = ($html_children == '') ? 'none' : 'block';
                
                // si le noeud courant est sélectionné on le met en gras
                $style_sel = ($is_node_sel) ? 'bold' : 'none';
                
                // lien sur le libellé
                $link = (empty($node['link'])) ? 'javascript:void(0);' : $node['link']; 
                
                // onclick sur le libellé
                $onclick = (empty($node['onclick'])) ? '' : 'onclick="'.$node['onclick'].';"'; 
                
                // génération du code html du noeud courant
                $html .=    "
                            <div class=\"treeview_node\" style=\"{$bg}\">
                                <div>
                                    {$node_link}<img src=\"{$node['icon']}\" />
                                    <a style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;font-weight:{$style_sel};\" href=\"{$link}\" {$onclick}>{$node['label']}</a>
                                </div>
                                <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"n{$node['id']}\">{$html_children}</div>
                            </div>
                            ";
                $c++;
            }
        }  

        return $html;
        
    }
}
?>
