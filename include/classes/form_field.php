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
 * Classe de gestion des champs d'un formulaire
 *
 */
class form_field extends form_element
{
    /**
     * Différents types acceptés pour un champ de formulaire
     *
     * @var array
     */
    private static $_arrDataType = array(
        'int',
        'float',
        'string',
        'date',
        'time',
        'phone',
        'email',
        'color'
    );

    /**
     * Options par défaut d'un champ de formulaire
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'description' => '',
        'placeholder' => '',
        'autofocus' => false,
        'style' => null,
        'style_form' => null,
        'class' => null,
        'class_form' => null,
        'required' => false,
        'datatype' => 'string',
        'maxlength' => null,
        'autocomplete' => true,
        'autocorrect' => true,
        'autocapitalize' => true,
        'spellcheck' => true,
        'readonly' => false,
        'disabled' => false,
        'accesskey' => null,
        'onblur' => null,
        'onchange' => null,
        'onfocus' => null,
        'onclick' => null,
        'ondblclick' => null,
        'onkeydown' => null,
        'onkeypress' => null,
        'onkeyup' => null,
        'onmousedown' => null,
        'onmousemove' => null,
        'onmouseout' => null,
        'onmouseover' => null,
        'onmouseup' => null,
        'min' => null,
        'max' => null
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strType type du champ
     * @param string $strLabel libellé du champ
     * @param mixed $mixValue valeur(s) du champ (array ou string)
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_field
     */
    public function __construct($strType, $strLabel, $mixValue, $strName = null, $strId = null, $arrOptions = null)
    {
        switch($strType)
        {
            case 'input:hidden':
            case 'input:text':
            case 'input:number':
            case 'input:email':
            case 'input:date':
            case 'input:password':
            case 'input:button':
            case 'input:submit':
            case 'input:reset':
            case 'input:img':
            case 'textarea':
                if (is_array($mixValue)) trigger_error('Ce type d\'élément n\'accepte pas un tableau de valeurs', E_USER_ERROR);
            break;
        }

        $arrValues = is_array($mixValue) ? $mixValue : array($mixValue);
        $arrOptions = is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions);

        parent::__construct($strType, $strLabel, $arrValues, $strName, $strId, $arrOptions);

    }

    /**
     * Génère le rendu html de l'habillage du champ (notamment le libellé)
     *
     * @param string $strOutputField code html du champ de formulaire
     * @return string champ avec libellé
     */
    protected function renderForm($strOutputField = '')
    {
        $strRequired = $this->_arrOptions['required'] ? ' class="required"' : '';
        $strAccesskey = is_null($this->_arrOptions['accesskey']) ? '' : " accesskey=\"{$this->_arrOptions['accesskey']}\"";
        $strStyleform = is_null($this->_arrOptions['style_form']) ? '' : " style=\"{$this->_arrOptions['style_form']}\"";
        $strClassform = is_null($this->_arrOptions['class_form']) ? '' : " class=\"{$this->_arrOptions['class_form']}\"";
        $strDesc = is_null($this->_arrOptions['description']) ? '' : "<span>{$this->_arrOptions['description']}</span>";

        return "<div id=\"{$this->_strId}_form\"{$strStyleform}{$strClassform}><label for=\"{$this->_strId}\"{$strAccesskey}{$strRequired}>{$this->_strLabel}{$strDesc}</label>{$strOutputField}</div>";
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
        $strMaxLength = is_null($this->_arrOptions['maxlength']) || !is_numeric($this->_arrOptions['maxlength']) ? '' : " maxlength=\"{$this->_arrOptions['maxlength']}\"";
        $strValue = str::htmlentities($this->_arrValues[0]);

        $strPlaceHolder = $this->_arrOptions['placeholder'] != '' ? ' placeholder="'.str::htmlentities($this->_arrOptions['placeholder']).'"' : '';
        switch($this->_strType)
        {
            case 'input:number':
                $strMinMax = '';
                if (isset($this->_arrOptions['min']) && is_numeric($this->_arrOptions['min'])) $strMinMax .= " min=\"{$this->_arrOptions['min']}\"";
                if (isset($this->_arrOptions['max']) && is_numeric($this->_arrOptions['max'])) $strMinMax .= " min=\"{$this->_arrOptions['max']}\"";

                $strOutput .= "<input type=\"number\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}{$strPlaceHolder}{$strMinMax} />";
            break;

            case 'input:email':
                $strOutput .= "<input type=\"email\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}{$strPlaceHolder} />";
            break;

            case 'input:date':
            case 'input:text':
                $arrParentOptions = $this->_objParentForm->getOptions();
                $strOutput .= "<input type=\"text\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}{$strPlaceHolder} />";
                if ($this->_arrOptions['datatype'] == 'date' && !$this->_arrOptions['readonly'] && !$this->_arrOptions['disabled'] && !$arrParentOptions['readonly'] && !$arrParentOptions['disabled']) $strOutput .= date::open_calendar($this->_strId, false, null, 'display:block;float:left;margin-left:-35px;margin-top:5px;');
            break;

            case 'input:password':
                $strOutput .= "<input type=\"password\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}{$strPlaceHolder} />";
            break;

            case 'textarea':
                $strOutput .= "<textarea name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}{$strPlaceHolder}>{$strValue}</textarea>";
            break;

            case 'input:file':
                if ($strValue != '') $strValue = "{$strValue}<br />";
                $strOutput .= "<span>{$strValue}<input type=\"file\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strEvents} /></span>";
            break;
        }

        return $this->renderForm($strOutput);
    }
}
