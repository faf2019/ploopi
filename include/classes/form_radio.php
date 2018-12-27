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
 * Gestion des champs de type "radio" de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class form_radio extends form_field
{
    /**
     * True si le radiobutton est coché
     *
     * @var boolean
     */
    private $_booChecked;

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param string $strValue valeur du champ
     * @param boolean $booChecked true si la checkbox est cochée
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     */
    public function __construct($strLabel, $strValue, $booChecked, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:radio', $strLabel, $strValue, $strName, $strId, $arrOptions);

        $this->_booChecked = $booChecked;
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champ dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties('radio'.(is_null($this->_arrOptions['class']) ? '' : ' '.$this->_arrOptions['class']));
        $strChecked = $this->_booChecked ? ' checked="checked"' : '';
        $strValue = form::htmlentities($this->_arrValues[0]);

        return $this->renderForm("<input type=\"radio\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents} />");
    }
}

