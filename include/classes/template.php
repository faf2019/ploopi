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
 * Gestion du template backoffice
 * 
 * @package ploopi
 * @subpackage template
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe de gestion des blocs de menus
 * 
 * @package ploopi
 * @subpackage template
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class block
{
    var $menu;
    var $content;
    
    function block()
    {
        $this->menu = array();
        $this->content = '';
    }

    function addmenu($label, $url, $selected = false, $target = '')
    {
        $this->menu[] = array ( 'label' => $label,
                                'url' => $url,
                                'selected' => $selected,
                                'target' => $target
                                );
    }
    
    function addcontent($content)
    {
        $this->content = $content;
    }

    function getmenu()
    {
        return($this->menu);
    }

    function getcontent()
    {
        return($this->content);
    }

}
?>