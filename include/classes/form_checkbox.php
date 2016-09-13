<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Classe de gestion des champs de type "checkbox" d'un formulaire
 *
 */
class form_checkbox extends form_field
{
    /**
     * True si la checkbox est coch�e
     *
     * @var boolean
     */
    private $_booChecked;

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libell� du champ
     * @param string $strValue valeur du champ
     * @param boolean $booChecked true si la checkbox est coch�e
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_checkbox
     */
    public function __construct($strLabel, $strValue, $booChecked, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:checkbox', $strLabel, $strValue, $strName, $strId, $arrOptions);

        $this->_booChecked = $booChecked;
    }

    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties('onclick'.(is_null($this->_arrOptions['class']) ? '' : ' '.$this->_arrOptions['class']));
        $strChecked = $this->_booChecked ? ' checked="checked"' : '';
        $strValue = str::htmlentities($this->_arrValues[0]);

        return $this->renderForm("<input type=\"checkbox\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" title=\"{$this->_strLabel}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents} />");
    }
}

