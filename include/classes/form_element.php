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
 * Gestion des éléments de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class form_element
{
    /**
     * Liste des variables utilisables dans le getter générique
     *
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

    /**
     * Liste des événements gérés
     *
     * @var array
     */
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
        'input:number',
        'input:email',
        'input:date',
        'input:month',
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

        'richtext',

        'datetime'
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
     * Object form "parent"
     *
     * @var form
     */
    protected $_objParentForm;


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
     * Génère les propriétés d'une balise
     *
     * @param string $strClass classe CSS
     * @param string $strStyle style CSS

     * @return string propriétés (HTML)
     */
    protected function generateProperties($strClass = null, $strStyle = null)
    {
        $arrParentOptions = empty($this->_objParentForm) ? array() : $this->_objParentForm->getOptions();

        $strDataSet = '';
        if (!empty($this->_arrOptions['dataset']) && is_array($this->_arrOptions['dataset'])) {
            foreach($this->_arrOptions['dataset'] as $k => $v) {
                $strDataSet .= self::_getProperty('data-'.str::tourl($k), $v);
            }
        }

        return
            $strDataSet.
            self::_getProperty('style',  is_null($strStyle) ? $this->_arrOptions['style'] : $strStyle).
            self::_getProperty('class',  is_null($strClass) ? $this->_arrOptions['class'] : $strClass).
            self::_getProperty('readonly',  !empty($arrParentOptions['readonly']) || !empty($this->_arrOptions['readonly']) ? 'readonly' : null).
            self::_getProperty('disabled',  !empty($arrParentOptions['disabled']) || !empty($this->_arrOptions['disabled']) ? 'disabled' : null).
            //self::_getProperty('required',  isset($this->_arrOptions['required']) && $this->_arrOptions['required'] ? 'required' : null).
            self::_getProperty('autofocus',  $this->_arrOptions['autofocus'] ? 'autofocus' : null).
            self::_getProperty('autocomplete',  isset($this->_arrOptions['autocomplete']) && !$this->_arrOptions['autocomplete'] ? 'off' : null).
            self::_getProperty('autocorrect',  isset($this->_arrOptions['autocorrect']) && !$this->_arrOptions['autocorrect'] ? 'off' : null).
            self::_getProperty('autocapitalize',  isset($this->_arrOptions['autocapitalize']) && !$this->_arrOptions['autocapitalize'] ? 'off' : null).
            self::_getProperty('spellcheck',  isset($this->_arrOptions['spellcheck']) && !$this->_arrOptions['spellcheck'] ? 'false' : null).
            self::_getProperty('accept',  isset($this->_arrOptions['accept']) ? $this->_arrOptions['accept'] : null).
            self::_getProperty('title',  isset($this->_arrOptions['title']) ? form::htmlentities($this->_arrOptions['title']) : null).
            self::_getProperty('pattern',  isset($this->_arrOptions['pattern']) ? $this->_arrOptions['pattern'] : null);
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
        $this->_strId = empty($strId) ? uniqid('ploopi_element_') : $strId;
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
     * Affecte le lien vers le formulaire "parent"
     *
     * @param form $objParentForm formulaire parent
     */
    public function setParentForm(form $objParentForm)
    {
        $this->_objParentForm = &$objParentForm;
    }

   /**
     * Méthode abstraite de rendu d'un libellé. Cette méthode doit être redéfinie dans les classes filles
     *
     * @param int $intTabindex tabindex de l'élément dans le formulaire
     */
    abstract protected function render($intTabindex = null);
}
