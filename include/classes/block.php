<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion des blocs de menus
 *
 * @package ploopi
 * @subpackage template
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class block
{
    /**
     * Tableau des menus
     *
     * @var array
     */
    private $menu;

    /**
     * Contenu additionnel (HTML)
     *
     * @var string
     */
    private $content;

    /**
     * Constructeur de la classe
     *
     * @return block
     */
    public function __construct()
    {
        $this->menu = array();
        $this->content = '';
    }

    /**
     * Ajoute un menu à un block
     *
     * @param string $label intitulé du menu
     * @param string $url adresse du lien vers le menu
     * @param boolean $selected true si le menu est sélectionné (optionnel, false par défaut)
     * @param string $target cible du lien (optionnel, par défaut vide)
     */

    public function addmenu($label, $url, $selected = false, $target = '')
    {
        $this->menu[] = array(
            'label' => $label,
            'cleaned_label' => str::htmlentities(trim(str_replace('&nbsp;', ' ', strip_tags($label)))),
            'url' => $url,
            'selected' => $selected,
            'target' => $target
        );
    }

    /**
     * Ajoute du contenu à un block
     *
     * @param string $content contenu html à ajouter au bloc
     */

    public function addcontent($content)
    {
        $this->content = $content;
    }

    /**
     * Mutateur pour le menu
     *
     * @return array tableau contenu les éléments du menu du block
     */

    public function getmenu()
    {
        return($this->menu);
    }

    /**
     * Mutateur pour le contenu
     *
     * @return strong le contenu du block
     */

    public function getcontent()
    {
        return($this->content);
    }

}
