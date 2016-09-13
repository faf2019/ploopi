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
 * Classe abstraite de gestion des �l�ments de formulaire
 *
 */
abstract class form_element
{
    /**
     * Liste des variables utilisables dans le getter g�n�rique
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
     * Diff�rents types accept�s pour un �l�ment
     *
     * @var array
     */
    private static $_arrTypes = array(
        'input:hidden',
        'input:text',
        'input:number',
        'input:email',
        'input:date',
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
     * Type de l'�l�ment
     *
     * @var string
     */
    private $_strType;

    /**
     * Libell� de l'�l�ment
     *
     * @var string
     */
    private $_strLabel;

    /**
     * Valeurs de l'�l�ments
     *
     * @var array
     */
    private $_arrValues;

    /**
     * Propri�t� "name" de l'�l�ment
     *
     * @var string
     */
    private $_strName;

    /**
     * Propri�t� "id" de l'�l�ment
     *
     * @var string
     */
    private $_strId;

    /**
     * Options de l'�l�ment
     *
     * @var array
     */
    private $_arrOptions;

    /**
     * Object form "parent"
     */
    protected $_objParentForm;


    /**
     * Petit raccourci pour inclure les propri�t�s de balises
     * @param string $strProperty
     * @param string $strContent
     */
    protected static function _getProperty($strProperty, $strContent = null) { return is_null($strContent) ? '' : " {$strProperty}=\"{$strContent}\""; }

    /**
     * Petit raccourci pour inclure les �v�nements de balises
     * @param string $strProperty
     * @param string $strContent
     */
    protected static function _getEvent($strProperty, $strContent = null) { return is_null($strContent) ? '' : " {$strProperty}=\"javascript:{$strContent}\""; }

    /**
     * G�n�re les �v�nements d'une balise
     * @return string
     */
    protected function generateEvents()
    {
        $strEvents = '';

        // Pour chaque �v�nement r�f�renc�
        foreach(self::$_arrEvents as $strEvent)
        {
            // S'il est pr�sent dans les options
            if (isset($this->_arrOptions[$strEvent]))
            {
                // On g�n�re la chaine � ins�rer dans la balise
                $strEvents .= self::_getEvent($strEvent, $this->_arrOptions[$strEvent]);
            }
        }

        return $strEvents;
    }

    /**
     * G�n�re les �v�nements d'une balise
     * @return string
     */
    protected function generateProperties($strClass = null, $strStyle = null)
    {
        $arrParentOptions = $this->_objParentForm->getOptions();

        return
            self::_getProperty('style',  is_null($strStyle) ? $this->_arrOptions['style'] : $strStyle).
            self::_getProperty('class',  is_null($strClass) ? $this->_arrOptions['class'] : $strClass).
            self::_getProperty('readonly',  $arrParentOptions['readonly'] || $this->_arrOptions['readonly'] ? 'readonly' : null).
            self::_getProperty('disabled',  $arrParentOptions['disabled'] || $this->_arrOptions['disabled'] ? 'disabled' : null).
            self::_getProperty('autofocus',  $this->_arrOptions['autofocus'] ? 'autofocus' : null).
            self::_getProperty('autocomplete',  isset($this->_arrOptions['autocomplete']) && !$this->_arrOptions['autocomplete'] ? 'off' : null).
            self::_getProperty('autocorrect',  isset($this->_arrOptions['autocorrect']) && !$this->_arrOptions['autocorrect'] ? 'off' : null).
            self::_getProperty('autocapitalize',  isset($this->_arrOptions['autocapitalize']) && !$this->_arrOptions['autocapitalize'] ? 'off' : null).
            self::_getProperty('spellcheck',  isset($this->_arrOptions['spellcheck']) && !$this->_arrOptions['spellcheck'] ? 'false' : null);
    }

    /**
     * Constructeur de la classe
     *
     * @param string $strType type du champ
     * @param string $strLabel libell� du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
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
     * @param string $strType type de l'�l�ment
     * @return boolean
     */
    public function setType($strType) {
        if (!in_array($strType, self::$_arrTypes)) {
            trigger_error('Ce type d\'�l�ment n\'existe pas', E_USER_ERROR);
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
     */
    public function setParentForm(form $objParentForm)
    {
        $this->_objParentForm = &$objParentForm;
    }

   /**
     * M�thode abstraite de rendu d'un libell�. Cette m�thode doit �tre red�finie dans les classes filles
     *
     * @param int $intTabindex tabindex de l'�l�ment dans le formulaire
     */
    abstract protected function render($intTabindex = null);
}
