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

namespace ploopi;

use ploopi;

/**
 * Classe de gestion des champs de type "datetime" d'un formulaire
 *
 */
class form_datetime extends form_field
{
    /**
     * Options par défaut d'un richtext
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'style' => 'width:100px;margin-right:2px;',
        'style_h' => 'width:50px;margin-right:2px;',
        'style_m' => 'width:50px;margin-right:2px;',
        'style_s' => 'width:50px;'
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param string $strValue valeur du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_richtext
     */
    public function __construct($strLabel, $arrValues, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('datetime', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));
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

        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties();
        $strProperties_H = $this->generateProperties(null, $this->_arrOptions['style_h']);
        $strProperties_M = $this->generateProperties(null, $this->_arrOptions['style_m']);
        $strProperties_S = $this->generateProperties(null, $this->_arrOptions['style_s']);
        $arrParentOptions = $this->_objParentForm->getOptions();

        $strMaxLength = is_null($this->_arrOptions['maxlength']) || !is_numeric($this->_arrOptions['maxlength']) ? '' : " maxlength=\"{$this->_arrOptions['maxlength']}\"";
        $strDate = str::htmlentities($this->_arrValues['date']);
        list($strHour, $strMinute, $strSecond) = explode(':', $this->_arrValues['time']);

        $strOutput .= "<input type=\"text\" name=\"{$this->_strName}_date\" id=\"{$this->_strId}_date\" value=\"{$strDate}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents} />";
        if (!$this->_arrOptions['readonly'] && !$this->_arrOptions['disabled'] && !$arrParentOptions['readonly'] && !$arrParentOptions['disabled']) $strOutput .= date::open_calendar($this->_strId.'_date', false, null, 'display:block;float:left;margin-left:-35px;margin-top:5px;');

        $strOutput .= "<select name=\"{$this->_strName}_time_h\" id=\"{$this->_strId}_time_h\" tabindex=\"{$intTabindex}\"{$strProperties_H}{$strEvents}>";
        for ($intH = 0; $intH < 24; $intH++ ) $strOutput .= sprintf('<option %s value="%2$02d">%2$02d</option>', $intH == intval($strHour) ? 'selected="selected"' : '', $intH);
        $strOutput .= "</select>";

        $strOutput .= "<select name=\"{$this->_strName}_time_m\" id=\"{$this->_strId}_time_m\" tabindex=\"{$intTabindex}\"{$strProperties_M}{$strEvents}>";
        for ($intM = 0; $intM < 60; $intM++ ) $strOutput .= sprintf('<option %s value="%2$02d">%2$02d</option>', $intM == intval($strMinute) ? 'selected="selected"' : '', $intM);
        $strOutput .= "</select>";

        $strOutput .= "<select name=\"{$this->_strName}_time_s\" id=\"{$this->_strId}_time_s\" tabindex=\"{$intTabindex}\"{$strProperties_S}{$strEvents}>";
        for ($intS = 0; $intS < 60; $intS++ ) $strOutput .= sprintf('<option %s value="%2$02d">%2$02d</option>', $intS == intval($strSecond) ? 'selected="selected"' : '', $intS);
        $strOutput .= "</select>";

        //$strOutput .= "<input type=\"text\" name=\"{$this->_strName}_time\" id=\"{$this->_strId}_time\" value=\"{$arrValues['time']}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents} />";

        return $this->renderForm($strOutput);

    }
}

