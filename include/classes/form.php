<?php
/*
    Copyright (c) 2008-2009 Ovensia
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
 * Gestion de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 *
 * @todo génération formulaire, validation JS
 */

/**
 * Classe abstraite de gestion des éléments de formulaire
 *
 */
abstract class form_element
{
    /**
     * Liste des variables utilisables dans le getter générique
     * @var array
     */
    private static $_arrAllowedGetters = array(
        '_strType',
        '_strLabel',
        '_arrValues',
        '_strName',
        '_strId',
        '_arrOptions'
    );

    private static $_arrEvents = array(
        'onblur',
        'onchange',
        'onfocus',
        'onclick',
        'ondblclick',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onmousedown',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup'
    );

    /**
     * Différents types acceptés pour un élément
     *
     * @var array
     */
    private static $_arrTypes = array(
        'input:hidden',
        'input:text',
        'input:password',
        'input:file',
        'textarea',

        'input:button',
        'input:submit',
        'input:reset',
        'input:img',

        'input:radio',
        'input:checkbox',

        'select',
        'option',

        'text',

        'richtext'
    );

    /**
     * Type de l'élément
     *
     * @var string
     */
    private $_strType;

    /**
     * Libellé de l'élément
     *
     * @var string
     */
    private $_strLabel;

    /**
     * Valeurs de l'éléments
     *
     * @var array
     */
    private $_arrValues;

    /**
     * Propriété "name" de l'élément
     *
     * @var string
     */
    private $_strName;

    /**
     * Propriété "id" de l'élément
     *
     * @var string
     */
    private $_strId;

    /**
     * Options de l'élément
     *
     * @var array
     */
    private $_arrOptions;


    /**
     * Petit raccourci pour inclure les propriétés de balises
     * @param string $strProperty
     * @param string $strContent
     */
    protected static function _getProperty($strProperty, $strContent = null) { return is_null($strContent) ? '' : " {$strProperty}=\"{$strContent}\""; }

    /**
     * Petit raccourci pour inclure les événements de balises
     * @param string $strProperty
     * @param string $strContent
     */
    protected static function _getEvent($strProperty, $strContent = null) { return is_null($strContent) ? '' : " {$strProperty}=\"javascript:{$strContent}\""; }

    /**
     * Génère les événements d'une balise
     * @return string
     */
    protected function generateEvents()
    {
        $strEvents = '';

        // Pour chaque événement référencé
        foreach(self::$_arrEvents as $strEvent)
        {
            // S'il est présent dans les options
            if (isset($this->_arrOptions[$strEvent]))
            {
                // On génère la chaine à insérer dans la balise
                $strEvents .= self::_getEvent($strEvent, $this->_arrOptions[$strEvent]);
            }
        }

        return $strEvents;
    }

    /**
     * Génère les événements d'une balise
     * @return string
     */
    protected function generateProperties($strClass = null)
    {
        return
            self::_getProperty('style',  $this->_arrOptions['style']).
            self::_getProperty('class',  is_null($strClass) ? $this->_arrOptions['class'] : $strClass).
            self::_getProperty('readonly',  $this->_arrOptions['readonly'] ? 'readonly' : null).
            self::_getProperty('disabled',  $this->_arrOptions['disabled'] ? 'disabled' : null);
    }

    /**
     * Constructeur de la classe
     *
     * @param string $strType type du champ
     * @param string $strLabel libellé du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_element
     */
    protected function __construct($strType, $strLabel, $arrValues, $strName = null, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Tableau attendu pour $arrValues', E_USER_ERROR);

        $this->setType($strType);
        $this->_arrValues = $arrValues;
        $this->_strLabel = $strLabel;
        $this->_strName = $strName;
        $this->_strId = $strId;
        $this->_arrOptions = $arrOptions;
    }

    /**
     * Getter magique !
     * @param mixed $var
     */
    public function __get($var)
    {
        if (isset($this->$var) && in_array($var, self::$_arrAllowedGetters)) return $this->$var;
        return null;
    }

    /**
     * Attribution du type
     *
     * @param string $strType type de l'élément
     * @return boolean
     */
    public function setType($strType) {
        if (!in_array($strType, self::$_arrTypes)) {
            trigger_error('Ce type d\'élément n\'existe pas', E_USER_ERROR);
            return false;
        }
        else {
            $this->_strType = $strType;
            return true;
        }
    }

    /**
     * Attribution des options
     *
     * @param array $arrOptions
     */
    public function setOptions($arrOptions) {
        $this->_arrOptions = array_merge($this->_arrOptions, $arrOptions);
    }

    /**
     * Méthode abstraite de rendu d'un libellé. Cette méthode doit être redéfinie dans les classes filles
     *
     * @param int $intTabindex tabindex de l'élément dans le formulaire
     */
    abstract protected function render($intTabindex = null);
}

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
        'style' => null,
        'style_form' => null,
        'class' => null,
        'class_form' => null,
        'required' => false,
        'datatype' => 'string',
        'maxlength' => null,
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
        'onmouseup' => null
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
        $strValue = htmlentities($this->_arrValues[0]);

        switch($this->_strType)
        {
            case 'input:text':
                $strOutput .= "<input type=\"text\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents} />";
                if ($this->_arrOptions['datatype'] == 'date' && !$this->_arrOptions['readonly'] && !$this->_arrOptions['disabled']) $strOutput .= ploopi_open_calendar($this->_strId, false, null, 'display:block;float:left;margin-left:-35px;margin-top:5px;');
            break;

            case 'input:password':
                $strOutput .= "<input type=\"password\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents} />";
            break;

            case 'textarea':
                $strOutput .= "<textarea name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strMaxLength}{$strEvents}>{$strValue}</textarea>";
            break;

            case 'input:file':
                if ($strValue != '') $strValue = "{$strValue}<br />";
                $strOutput .= "<span>{$strValue}<input type=\"file\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\"{$strProperties}{$strEvents} /></span>";
            break;
        }

        return $this->renderForm($strOutput);
    }
}

/**
 * Classe de gestion des champs de type "hidden"
 *
 */
class form_hidden extends form_field
{
    /**
     * Constructeur de la classe
     *
     * @param string $strValue valeur du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_hidden
     */
    public function __construct($strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:hidden', '', $strValue, $strName, $strId, $arrOptions);
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

        $strClass = is_null($this->_arrOptions['class']) ? '' : " {$this->_arrOptions['class']}";
        $strValue = htmlentities($this->_arrValues[0]);

        $strOutput .= "<input type=\"hidden\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\"{$strClass} />";

        return $strOutput;
    }
}

/**
 * Classe de gestion des options des champs de type "select" d'un formulaire
 */
class form_select_option extends form_element
{
    public function __construct($strLabel, $strValue, $strId = null, $arrOptions = null)
    {
        parent::__construct('option', $strLabel, array($strValue), null, $strId, $arrOptions);
    }

    public function render($intTabindex = null)
    {
        $strId = is_null($this->_strId) ? '' : " id=\"{$this->_strId}\"";
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strLabel = htmlentities($this->_strLabel);
        $strValue = htmlentities($this->_arrValues[0]);
        return "<option value=\"{$strValue}\"{$strId}{$strStyle}>{$strLabel}</option>";
    }
}

/**
 * Classe de gestion des champs de type "select" d'un formulaire
 */
class form_select extends form_field
{
    /**
     * Valeur(s) sélectionnée(s) dans le select
     *
     * @var string
     */
    private $_arrSelected;

    /**
     * Options par défaut d'un select
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'onchange' => null,
        'size' => null,
        'multiple' => false
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $strSelected valeur de l'élément sélectionné
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_select
     */
    public function __construct($strLabel, $arrValues = array(), $arrSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);

        if (!is_array($arrSelected)) { $strTmp = $arrSelected; unset($arrSelected); $arrSelected[] = $strTmp; }

        parent::__construct('select', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));

        $this->_arrSelected = ploopi_array_map('htmlentities', $arrSelected);
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
        $strSize = is_null($this->_arrOptions['size']) ? '' : " size=\"{$this->_arrOptions['size']}\"";
        $strMultiple = $this->_arrOptions['multiple'] ? " multiple=\"multiple\"" : '';

        $strOutput .= "<select name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\" {$strProperties}{$strSize}{$strMultiple}{$strEvents} />";

        $strOutput .= $this->_renderOptions($this->_arrValues, $intTabindex);

        $strOutput .= "</select>";

        return $this->renderForm($strOutput);
    }

    /**
     * Génère le rendu des valeurs (récursif)
     * @param array tableau des valeurs
     * @return string code html
     */
    private function _renderOptions($arrValues, $intTabindex)
    {
        $strOutput = '';

        foreach($arrValues as $mixKey => $mixValue)
        {
            if (is_array($mixValue))
            {
                $mixKey = htmlentities($mixKey);
                //$strOutput .= "<optgroup label=\"{$mixKey}\">".$this->_renderOptions($mixValue)."</optgroup>";
            }
            elseif (is_object($mixValue) && $mixValue instanceof form_select_option)
            {
                $strOutput .= $mixValue->render($intTabindex);
            }
            else
            {
                $mixValue = htmlentities($mixValue);
                $mixKey = htmlentities($mixKey);

                $strSelected = in_array($mixKey, $this->_arrSelected) ? ' selected="selected"' : '';
                $strOutput .= "<option value=\"{$mixKey}\"{$strSelected}>{$mixValue}</option>";
            }
        }

        return $strOutput;
    }
}

/**
 * Classe de gestion des champs de type "select" d'un formulaire
 *
 */
class form_checkbox_list extends form_field
{
    /**
     * Valeurs sélectionnées dans les checkboxes
     *
     * @var array
     */
    private $arrSelected;

    /**
     * Options par défaut d'une liste de checkboxes
     *
     * @var array
     */
    protected static $_arrDefaultOptions = array(
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $arrSelected valeurs des éléments sélectionnés
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_select
     */
    public function __construct($strLabel, $arrValues = array(), $arrSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);
        if (!is_array($arrSelected)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);

        parent::__construct('input:checkbox', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));

        $this->arrSelected = ploopi_array_map('htmlentities', $arrSelected);
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

        $intNumCheck = 0;
        foreach($arrValues = $this->_arrValues as $strKey => $strValue)
        {
            $strValue = htmlentities($strValue);
            $strKey = htmlentities($strKey);

            $strChecked = in_array($strKey, $this->arrSelected) ? ' checked="checked"' : '';
            $strOutput .= "<span class=\"checkbutton\"><input type=\"checkbox\" name=\"{$this->_strName}[]\" id=\"{$this->_strId}_{$intNumCheck}\" value=\"{$strKey}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents}><label for=\"{$this->_strId}_{$intNumCheck}\">{$strValue}</label></span>";

            $intNumCheck++;
        }

        return $this->renderForm("<span class=\"onclick\">{$strOutput}</span>");
    }
}

/**
 * Classe de gestion des listes de boutons radio d'un formulaire
 *
 */
class form_radio_list extends form_field
{
    /**
     * Valeur sélectionnée dans les boutons radio
     *
     * @var array
     */
    private $_strSelected;

    /**
     * Options par défaut d'une liste de checkboxes
     *
     * @var array
     */
    protected static $_arrDefaultOptions = array(
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $arrSelected valeurs des éléments sélectionnés
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_select
     */
    public function __construct($strLabel, $arrValues = array(), $strSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);

        parent::__construct('input:radio', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));

        $this->_strSelected = htmlentities($strSelected);
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

        $intNumCheck = 0;
        foreach($arrValues = $this->_arrValues as $strKey => $strValue)
        {
            $strValue = htmlentities($strValue);
            $strKey = htmlentities($strKey);

            $strChecked = $strKey ==  $this->_strSelected ? ' checked="checked"' : '';
            $strOutput .= "<span class=\"checkbutton\"><input type=\"radio\" name=\"{$this->_strName}\" id=\"{$this->_strId}_{$intNumCheck}\" value=\"{$strKey}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents}><label for=\"{$this->_strId}_{$intNumCheck}\">{$strValue}</label></span>";

            $intNumCheck++;
        }

        return $this->renderForm("<span class=\"onclick\">{$strOutput}</span>");
    }
}


/**
 * Classe de gestion des champs de type "checkbox" d'un formulaire
 *
 */
class form_checkbox extends form_field
{
    /**
     * True si la checkbox est cochée
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
     *
     * @return form_checkbox
     */
    public function __construct($strLabel, $strValue, $booChecked, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:checkbox', $strLabel, $strValue, $strName, $strId, $arrOptions);

        $this->_booChecked = $booChecked;
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties('onclick'.(is_null($this->_arrOptions['class']) ? '' : ' '.$this->_arrOptions['class']));
        $strChecked = $this->_booChecked ? ' checked="checked"' : '';
        $strValue = htmlentities($this->_arrValues[0]);

        return $this->renderForm("<input type=\"checkbox\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents} />");
    }
}

/**
 * Classe de gestion des champs de type "radio" d'un formulaire
 *
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
     *
     * @return form_radio
     */
    public function __construct($strLabel, $strValue, $booChecked, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:radio', $strLabel, $strValue, $strName, $strId, $arrOptions);

        $this->_booChecked = $booChecked;
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties('radio'.(is_null($this->_arrOptions['class']) ? '' : ' '.$this->_arrOptions['class']));
        $strChecked = $this->_booChecked ? ' checked="checked"' : '';
        $strValue = htmlentities($this->_arrValues[0]);

        return $this->renderForm("<input type=\"radio\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents} />");
    }
}

/**
 * Classe de gestion des champs de type "text" (statique) d'un formulaire
 *
 */
class form_text extends form_field
{
    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param string $strValue valeur du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_text
     */
    public function __construct($strLabel, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('text', $strLabel, $strValue, $strName, $strId, $arrOptions);
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";
        $strValue = ploopi_nl2br($this->_arrValues[0]);

        return $this->renderForm("<span name=\"{$this->_strName}\" id=\"{$this->_strId}\" {$strStyle}{$strClass}>{$strValue}</span>");
    }
}


/**
 * Classe de gestion des champs de type "html" d'un formulaire
 *
 */
class form_htmlfield extends form_field
{
    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param string $strValue valeur du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     *
     * @return form_htmlfield
     */
    public function __construct($strLabel, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('text', $strLabel, $strValue, $strName, $strId, $arrOptions);
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";

        return $this->renderForm("<span name=\"{$this->_strName}\" id=\"{$this->_strId}\" {$strStyle}{$strClass}>{$this->_arrValues[0]}</span>");
    }
}

/**
 * Classe de gestion des champs de type "richtext" (fckeditor) d'un formulaire
 *
 */
class form_richtext extends form_field
{
    /**
     * Options par défaut d'un richtext
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'width' => '100%',
        'height' => '150px',
        'config' => null,
        'css' => null,
        'toolbar'=> null
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
    public function __construct($strLabel, $strValue, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('richtext', $strLabel, $strValue, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        include_once './include/functions/fck.php';

        $arrConfig = array();
        if (!is_null($this->_arrOptions['config'])) $arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.$this->_arrOptions['config'];
        if (!is_null($this->_arrOptions['css'])) $arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.$this->_arrOptions['css'];

        $arrProperties = array();
        if (!is_null($this->_arrOptions['toolbar'])) $arrProperties['ToolbarSet'] = $this->_arrOptions['toolbar'];

        ob_start();
        ploopi_fckeditor($this->_strId, $this->_arrValues[0], $this->_arrOptions['width'], $this->_arrOptions['height'], $arrConfig, $arrProperties);
        $strContent = ob_get_contents();
        ob_end_clean();

        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";

        return $this->renderForm("<span{$strStyle}>".$strContent.'</span>');
    }
}

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
        $strValue = htmlentities($this->_arrValues[0]);

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

/**
 * Classe de gestion des contenus libres
 */

class form_html extends form_field
{

    /**
     * Contenu de l'élément
     *
     * @var string
     */
    private $strContent;

    public function __construct($strContent)
    {
        $this->strContent = $strContent;
    }

    /**
     * Retourne le contenu html
     */
    public function render($intTabindex = null)
    {
        return $this->strContent;
    }
}

/**
 * Classe de gestion des panels
 *
 */
class form_panel
{
    /**
     * Propriété "id" du panel
     *
     * @var string
     */
    private $_strId;

    /**
     * Libellé du panel
     *
     * @var string
     */
    private $_strLabel;

    /**
     * Champs du panel
     *
     * @var array
     */
    private $_arrFields;

    /**
     * Options du panel
     *
     * @var array
     */
    private $_arrOptions;

    /*
     * Object form "parent"
     */
    private $_objParentForm;


    static private $_arrDefaultOptions = array(
        'style'     => null,
        'class'     => null
    );

    const strDefaultPanel = 'ploopi_panel_default';


    /**
     * Constructeur du panel
     * @param string $strId identifiant du panel
     * @param string $strLabel libellé du panel
     * @param array $arrOptions options du panel
     *
     * @return form_panel
     */
    public function __construct($strId, $strLabel = null, $arrOptions = null)
    {
        // init Champs
        $this->_arrFields = array();

        $this->_strId = $strId;
        $this->_strLabel = $strLabel;
        $this->_objParentForm = null;

        // Fusion des options
        $this->_arrOptions = is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions);

    }

    /**
     * Ajoute un objet de type form_field au panel
     *
     * @param form_field $objField objet form_field
     */
    public function addField(form_field $objField)
    {
        if ($objField->_strType == 'input:file' && !is_null($this->_objParentForm) && get_class($this->_objParentForm) == 'form') $this->_objParentForm->setOptions(array('enctype' => 'multipart/form-data'));

        $this->_arrFields[] = &$objField;
    }

    /**
     * Affecte le lien vers le formulaire "parent"
     */
    public function setParentForm(form $objParentForm)
    {
        $this->_objParentForm = &$objParentForm;
    }

    /**
     * Lecture de la propriété "id"
     *
     * @return string
     */
    public function getId() { return $this->_strId; }

    /**
     * Lecture de la propriété "label"
     *
     * @return string
     */
    public function getLabel() { return $this->_strLabel; }

    /**
     * Lecture des options
     *
     * @return array
     */
    public function getOptions() { return $this->_arrOptions; }

    /**
     * Lecture du nombre de champs
     *
     * @return int
     */
    public function getNbFields() { return sizeof($this->_arrFields); }


    /**
     * Retourne les champs du panel
     *
     * @return array
     */
    public function getFields() { return $this->_arrFields; }

    /**
     * Génère le rendu html du panel
     *
     * @param string $strFields contenu du panel
     * @return string code html
     */
    public function render(&$intTabindex = null)
    {
        $strOutputFields = '';

        // Génération des champs
        $strOutputFields = '';

        foreach($this->_arrFields as $objField)
        {
            $strOutputFields .= $objField->render($intTabindex++);
            // On détermine si le formulaire dispose d'un champ FILE
            // if (!$booHasFile && $objField->getType() == 'input:file') $booHasFile = true;
        }

        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";

        $strOutput = "
            <fieldset id=\"{$this->_strId}\"{$strClass}{$strStyle}>
                <legend>{$this->_strLabel}</legend>
                {$strOutputFields}
            </fieldset>
        ";

        return $strOutput;
    }
}

/**
 * Classe de gestion d'un formulaire HTML composé de champs, de boutons et d'un système de validation javascript
 */
class form
{
    /**
     * Panels du formulaire
     *
     * @var array
     */
    private $_arrPanels;

    /**
     * Boutons du formulaire
     *
     * @var array
     */
    private $_arrButtons;

    /**
     * Contenus JS additionnels du formulaire
     *
     * @var array
     */
    private $_arrJs;

    /**
     * Options du formulaire
     *
     * @var array
     */
    private $_arrOptions;

    /**
     * Propriété "id" du formulaire
     */
    private $_strId;

    /**
     * Action exécutée par le formulaire lors du submit
     */
    private $_strAction;

    /**
     * Méthode de validation du formulaire (post/get)
     */
    private $_strMethod;

    /**
     * Panel par défaut
     */
    private $_objDefaultPanel;

    /**
     * Options par défaut des formulaires
     *
     * @var array
     */
    static private $_arrDefaultOptions = array(
        'tabindex'      => 1,                                       // tabindex de départ pour le contenu du formulaire
        'target'        => null,                                    // cible de la validation du formulaire (un iframe par exemple)
        'enctype'       => null,                                    // type d'encodage du formulaire
        'onsubmit'      => null,                                    // action à effectuer sur l'événement "onsubmit" du formulaire
        'button_style'  => 'text-align:right;padding:2px 4px;',     // style appliqué aux boutons de validation du formulaire
        'legend'        => null,                                    // contenu de la légende du formulaire
        'legend_style'  => 'margin-right:4px;',                     // style appliqué à la légende du formulaire
        'class'         => 'ploopi_generate_form',                  // class par défaut du formulaire (partie champs)
        'style'         => null,                                    // style appliqué au formulaire (partie champs)
        'class_form'    => null,                                    // class par défaut du formulaire (global, balise form)
        'style_form'    => null                                     // style appliqué au formulaire (global, balise form)
    );

    /**
     * Constructeur du formulaire
     * @param string $strId identifiant du formulaire
     * @param string $strAction propriété "action" du formulaire
     * @param string $strMethod propriété "method" du formulaire ("post" par défaut)
     * @param array $arrOptions options du formulaire (tabindex, target, enctype, onsubmit, button_style, legend, legend_style)
     *
     * @return form
     */
    public function __construct($strId, $strAction, $strMethod = 'post', $arrOptions = null)
    {
        // Init Panels, Boutons
        $this->_arrPanels = array();
        $this->_arrButtons = array();
        $this->_arrJs = array();

        $this->_strId = $strId;
        $this->_strAction = $strAction;
        $this->_strMethod = $strMethod;

        // Fusion des options
        $this->_arrOptions = is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions);

        // Création d'un panel par défaut (utilisé si l'utilisateur n'en crée pas)
        $this->addPanel($this->_objDefaultPanel = new form_panel(form_panel::strDefaultPanel, null, array('style' => 'border:0;')));
    }

    /**
     * Ajoute un objet de type form_field au formulaire
     *
     * @param form_field $objField objet form_field
     */
    public function addField(form_field $objField)
    {
        $this->_objDefaultPanel->addField($objField);

        /*if ($objField->getType() == 'input:file') $this->setOptions(array('enctype' => 'multipart/form-data'));
        $this->_arrFields[] = $objField;*/
    }

    /**
     * Ajoute un objet de type form_button au formulaire
     *
     * @param form_button $objButton objet form_button
     */
    public function addButton(form_button $objButton)
    {
        $this->_arrButtons[] = &$objButton;
    }

    /**
     * Ajoute un objet de type form_panel au formulaire
     *
     * @param form_panel $objPanel objet form_panel
     */
    public function addPanel(form_panel $objPanel)
    {
        $this->_arrPanels[] = &$objPanel;
        $objPanel->setParentForm($this);
    }

    /**
     * Ajoute un contenu JS additionnel au formulaire
     *
     * @param form_panel $objPanel objet form_panel
     */
    public function addJs($strJs)
    {
        $this->_arrJs[] = $strJs;
    }

    /**
     * Définit les options du formulaire
     *
     * @param array $arrOptions options du formulaire
     */
    public function setOptions($arrOptions)
    {
        $this->_arrOptions = array_merge($this->_arrOptions, $arrOptions);
    }

    /**
     * Retourne les options du formulaire
     *
     * @return array
     */
    public function getOptions() { return $this->_arrOptions; }

    /**
     * Retourne les champs du formulaire
     *
     * @return array
     */
    public function getFields()
    {
        $arrFields = array();

        foreach($this->_arrPanels as $objPanel) $arrFields = array_merge($arrFields, $objPanel->getFields());

        return $arrFields;
    }


    /**
     * Rendu HTML du formulaire
     *
     * @return string code html du formulaire
     */
    // public function render()
    public function render($intTabindexOptions = null)
    {
        $intTabindex = $this->_arrOptions['tabindex'];


        // Génération des Panels
        $strOutputPanels = '';
        $booHasFile = false;
        foreach($this->_arrPanels as $objPanel)
        {
            // Génération des panels (+ champs)
            if($objPanel->getNbFields()) $strOutputPanels .= $objPanel->render($intTabindex);
        }

        $strTarget = is_null($this->_arrOptions['target']) ? '' : " target=\"{$this->_arrOptions['target']}\"";
        $strEnctype = is_null($this->_arrOptions['enctype']) ? ($booHasFile ? ' enctype="multipart/form-data"' : '') : " enctype=\"{$this->_arrOptions['enctype']}\"";
        $strOnsubmit = is_null($this->_arrOptions['onsubmit']) ? 'onsubmit="javascript:return ploopi.'.$this->getFormValidateFunc().'(this);"' : " onsubmit=\"javascript:{$this->_arrOptions['onsubmit']}\"";
        $strButtonStyle = is_null($this->_arrOptions['button_style']) ? '' : " style=\"{$this->_arrOptions['button_style']}\"";
        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strClassForm = is_null($this->_arrOptions['class_form']) ? '' : " class=\"{$this->_arrOptions['class_form']}\"";
        $strStyleForm = is_null($this->_arrOptions['style_form']) ? '' : " style=\"{$this->_arrOptions['style_form']}\"";

        /*
         * Génération du script de validation
         * Attention, nécessité de passer par eval() pour les appels AJAX
         */

        $strOutput = '<script type="text/javascript">'.$this->renderJS().'</script>';

        /*
         * Génération du form
         */

        $strOutput .= "<div{$strClass}{$strStyle}><form id=\"{$this->_strId}\" action=\"{$this->_strAction}\" method=\"{$this->_strMethod}\"{$strOnsubmit}{$strTarget}{$strEnctype}><div {$strClassForm}{$strStyleForm}>";


        /*
         * Insertion des champs
         */

        $strOutput .= $strOutputPanels;


        /*
         * Génération des boutons
         */

        $strLegend = is_null($this->_arrOptions['legend']) ? '' : "<em".(is_null($this->_arrOptions['legend_style']) ? '' : " style=\"{$this->_arrOptions['legend_style']}\"").">{$this->_arrOptions['legend']}</em>";

        $strOutput .= "</div><div{$strButtonStyle} class=\"buttons\">";
        foreach(array_reverse($this->_arrButtons) as $objButton) $strOutput .= $objButton->render($intTabindex++);

        $strOutput .= '</div></form></div>';


        return $strOutput;
    }

    /**
     * Rendu de la fonction javascript de validation du formulaire
     *
     * @return string fonction de validation javascript
     */
    private function renderJS()
    {
        $strOutput = "ploopi.".$this->getFormValidateFunc()." = function(form) {";

        foreach($this->getFields() as $objField)
        {
            if ($objField->_strName != '')
            {
                $strFormField = $objField->_strId != '' ? "$('{$objField->_strId}')" : "form.{$objField->_strName}";

                switch ($objField->_strType)
                {
                    case 'input:text':
                    case 'input:password':
                    case 'input:file':
                    case 'textarea':
                        $strFormat = ($objField->_arrOptions['required'] ? '' : 'empty').$objField->_arrOptions['datatype'];
                        $strOutput .= "if (ploopi_validatefield('".addslashes($objField->_strLabel)."', {$strFormField}, '{$strFormat}'))";
                    break;

                    case 'select':
                    case 'color':
                        if ($objField->_arrOptions['required']) $strOutput .= "if (ploopi_validatefield('".addslashes($objField->_strLabel)."', {$strFormField}, 'selected'))";
                    break;

                    case 'input:radio':
                    case 'input:checkbox':
                        if ($objField->_arrOptions['required']) $strOutput .= "if (ploopi_validatefield('".addslashes($objField->_strLabel)."', {$strFormField}, 'checked'))";
                    break;
                }
            }

        }

        $strOutput .= "return true; return false; }";

        foreach($this->_arrJs as $strJs) $strOutput .= "\n{$strJs}";

        return $strOutput;
    }

    /**
     * Retourne le nom de la fonction de validation du formulaire
     *
     * @return string nom de la fonction de validation
     */
    private function getFormValidateFunc() { return "{$this->_strId}_validate"; }

}
