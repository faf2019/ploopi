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
 * Gestion du skin 'ploopi_menus'
 * 
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see skin_common 
 */

/**
 * inclusion de la classe parent
 */

include_once './include/classes/skin_common.php';

/**
 * Gestion de l'affichage du skin 'ploopi_menus'
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see skin_common 
 */

class skin extends skin_common
{
    /**
     * Construction de la classe skin 'ploopi_menus'
     *
     * @return skin
     */
    
    function skin()
    {
        parent::skin_common('ploopi_menus');
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
    
    function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" style=\"{$styletitle}\"><div class=\"simplebloc_titleleft\">{$additionnal_title}{$title}</div></div>";

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

    function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}</p>{$title}</div>";
        else $res = "<div class=\"pagetitle\"><p>{$additionnal_title}</p>{$title}</div>";

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
    
}
?>
