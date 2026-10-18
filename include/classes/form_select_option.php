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
 * Gestion des options des champs de type "select" de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class form_select_option extends form_element
{

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé de l'élément
     * @param string $strValue valeur de l'élément
     * @param string $strId propriété "id" de l'élément
     * @param array $arrOptions options du l'élément
     */
    public function __construct($strLabel, $strValue, $strId = null, $arrOptions = null)
    {
        parent::__construct('option', $strLabel, array($strValue), null, $strId, $arrOptions);
    }

    /**
     * Génère le rendu html de l'élément
     *
     * @param int $intTabindex tabindex du champ dans le formulaire
     * @param boolean $booSelected true si sélectionné
     *
     * @return string code html
     */
    public function render($intTabindex = null, $booSelected = false)
    {
        $strId = is_null($this->_strId) ? '' : " id=\"{$this->_strId}\"";
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strLabel = form::htmlentities($this->_strLabel);
        $strValue = form::htmlentities($this->_arrValues[0]);
        $strSelected = $booSelected ? ' selected="selected"' : '';

        return "<option value=\"{$strValue}\"{$strId}{$strStyle}{$strSelected}>{$strLabel}</option>";
    }
}
