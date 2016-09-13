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
 * Classe de gestion des boutons d'un formulaire
 *
 */
class form_button extends form_element
{

    /**
     * Options par défaut d'un bouton
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'style'     => null,
        'class' => null,
        'readonly' => false,
        'autofocus' => false,
        'disabled' => false,
        'onclick'   => null
    );

    /**
     * Différents types de boutons acceptés
     *
     * @var array
     */
    private static $_arrTypes = array(
        'input:button',
        'input:submit',
        'input:reset',
        'input:img'
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strType type du bouton
     * @param string $strValue valeur du bouton (intitulé)
     * @param string $strName propriété "name" du bouton
     * @param string $strId propriété "id" du bouton
     * @param array $arrOptions options du bouton
     *
     * @return form_button
     */
    public function __construct($strType, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        if (!in_array($strType, self::$_arrTypes)) trigger_error('Ce type de bouton n\'existe pas', E_USER_ERROR);
        else parent::__construct($strType, null, array($strValue), $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strOutput = '';
        $strClassName = '';

        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties();
        $strValue = str::htmlentities($this->_arrValues[0]);

        switch($this->_strType)
        {
            case 'input:reset':
                $strOutput .= "<button type=\"reset\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\" {$strProperties}{$strEvents}>{$strValue}</button>";
            break;

            case 'input:button':
                $strOutput .= "<button type=\"button\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\" {$strProperties}{$strEvents}>{$strValue}</button>";
            break;

            case 'input:submit':
                $strOutput .= "<button type=\"submit\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\" {$strProperties}{$strEvents}>{$strValue}</button>";
            break;
        }

        return $strOutput;
    }
}
