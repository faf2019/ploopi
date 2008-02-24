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

class skin_common
{

    function skin_common($skin)
    {
        $this->values = array();
        $this->values['path'] = "./templates/backoffice/$skin/img";
        $this->values['inifile'] = "./templates/backoffice/$skin/skin.ini";
        if (file_exists($this->values['inifile']))
        {
            $this->values = array_merge($this->values,parse_ini_file($this->values['inifile']));
        }
    }

    function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" style=\"{$styletitle}\">{$additionnal_title}{$title}</div>";

        $res .= '<div class="simplebloc_content">';

        return $res;
    }

    function close_simplebloc()
    {
        return '</div></div>';
    }

    function create_pagetitle($title, $style = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\">$title</div>";
        else $res = "<div class=\"pagetitle\">$title</div>";

        return $res;
    }


    function create_toolbar($icons, &$iconsel, $sel = true, $vertical = false)
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

    function create_icon($icon, $sel, $key, $vertical)
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
                            <div class=\"toolbar_icon_title\">$title</div>
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
                            <div class=\"toolbar_icon_title\">$title</div>
                        </a>
                    </div>
                    ";
        }

        return $res;
    }

    function create_tabs($tabs,&$tabsel)
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

    function create_tab($tab,$sel)
    {
        if (!empty($tab['width'])) $style = "style=\"width:{$tab['width']}px;\"";
        else $style = '';

        if ($sel) $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" class=\"selected\" {$style}>{$tab['title']}</a>";
        else  $res = "<a href=\"".ploopi_urlencode($tab['url'])."\" {$style}>{$tab['title']}</a>";
        return $res;
    }

    function create_menu($title, $link, $id_help='', $target='', $urlencode = true)
    {
        if ($urlencode) $link = ploopi_urlencode($link);
    }

    function create_popup($title, $content, $popupid = '')
    {
        $res = $this->open_simplebloc($title, 'margin:0px;','','<a title="Fermer" class="ploopi_popup_close" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup(\''.$popupid.'\');">Fermer</a>');
        $res .= $content;
        $res .= $this->close_simplebloc();

        return($res);
    }


    function array_sort($a,$b)
    {
        $a_label = isset($this->array_values[$a]['values'][$this->array_orderby]['sort_label']) ? 'sort_label' : 'label';
        $b_label = isset($this->array_values[$b]['values'][$this->array_orderby]['sort_label']) ? 'sort_label' : 'label';

        $a_val = &$this->array_values[$a]['values'][$this->array_orderby][$a_label];
        $b_val = &$this->array_values[$b]['values'][$this->array_orderby][$b_label];

        if ($this->array_sort == 'ASC') return($a_val>$b_val);
        else return($b_val>$a_val);
    }

    function display_array($columns, $values, $array_id = null, $options = null)
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

    function display_array_refresh($array_id, $orderby = null)
    {
        $array = &$_SESSION['ploopi']['arrays'][$array_id];

        $sort_img = '';

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

            // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
            $array['orderby'] = (empty($orderby)) ? $array['orderby'] : $orderby;


            $this->array_values = $array['values'];
            $this->array_sort = $array['sort'];
            $this->array_orderby = $array['orderby'];

            uksort ($array['values'], array($this, 'array_sort'));

            $sort_img = ($array['sort'] == 'DESC') ? "<img src=\"{$this->values['path']}/arrays/arrow_down.png\">" : "<img src=\"{$this->values['path']}/arrays/arrow_up.png\">";
        }

        ?>
        <?
        $i = 0;
        $w = 0;
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
        ?>
        <div style="position:relative;">
            <div class="ploopi_explorer_title" id="ploopi_explorer_title_<? echo $array_id; ?>">
                <?
                if (!empty($array['columns']['actions_right']))
                {
                    foreach($array['columns']['actions_right'] as $id => $c)
                    {
                        ?>
                        <a href="<? echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <? if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; ?>" style="width:<? echo $c['width']; ?>px;float:right;<? if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><? echo $c['label']; ?>&nbsp;</span></p></a>
                        <?
                    }
                }

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

            ?>
            <div <? if (!empty($array['options']['height'])) echo "style=\"height:{$array['options']['height']}px;overflow:auto;\""; ?> id="ploopi_explorer_values_outer_<? echo $array_id; ?>">

                <div id="ploopi_explorer_values_inner_<? echo $array_id; ?>">
                <?
                foreach($array['values'] as $v)
                {
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
                            $onclick = (empty($v['onclick'])) ? '' : " onclick=\"{$v['onclick']}\"";
                            ?>
                            <a class="ploopi_explorer_link" title="<? echo $v['description']; ?>" href="<? echo $v['link']; ?>"<? echo $onclick ; ?> <? if (!empty($v['style'])) echo "style=\"{$v['style']}\""; ?> <? echo $option; ?>>
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
                        if (!empty($v['link']))
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
}
?>
