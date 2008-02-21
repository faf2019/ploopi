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

include_once "./include/classes/class_skin_common.php";

class skin extends skin_common
{
    function skin()
    {
        parent::skin_common('ploopi_menus');
    }

    function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" style=\"{$styletitle}\"><p>{$additionnal_title}{$title}</p></div>";

        $res .= '<div class="simplebloc_content">';

        return $res;
    }

    function close_simplebloc()
    {
        return '</div><div class="simplebloc_footer"></div></div>';
    }

    function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}{$title}</p></div>";
        else $res = "<div class=\"pagetitle\"><p>{$additionnal_title}{$title}</p></div>";

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


    function popup($title, $content, $popupid = '', $title2 = '')
    {
        $res = $this->open_simplebloc($title, 'margin:0px;','','<a title="Fermer" class="ploopi_popup_close" href="javascript:void(0);" onclick="javascript:ploopi_hidepopup(\''.$popupid.'\');"><img alt="Fermer" style="display:block;float:right;margin:2px;" src="'.$this->values['path'].'/template/close_popup.png"></a>');
        $res .= $content;
        $res .= $this->close_simplebloc();

        return($res);
    }
}
?>
