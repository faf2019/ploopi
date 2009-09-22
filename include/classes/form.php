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
 * @author St�phane Escaich
 * 
 * 
 * @todo g�n�ration formulaire, validation JS 
 */

/**
 * Classe abstraite de gestion des �l�ments de formulaire
 *
 */
abstract class form_element
{
    /**
     * Type de l'�l�ment
     *
     * @var string
     */
    private $strType;
    
    /**
     * Libell� de l'�l�ment
     *
     * @var string
     */
    protected $strLabel;
    
    /**
     * Valeurs de l'�l�ments
     *
     * @var array
     */
    protected $arrValues;
    
    /**
     * Propri�t� "name" de l'�l�ment
     *
     * @var string
     */
    protected $strName;

    /**
     * Propri�t� "id" de l'�l�ment
     *
     * @var string
     */
    protected $strId;
    
    /**
     * Options de l'�l�ment
     *
     * @var array
     */
    protected $arrOptions;
    
    
    /**
     * Diff�rents types accept�s pour un �l�ment
     *
     * @var array
     */
    protected static $arrType = array(
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

        'text',
    
        'richtext'
    );

    /**
     * Lecture du type
     *
     * @return string
     */
    public function getType() { return $this->strType; }

    /**
     * Lecture de la propri�t� "name"
     *
     * @return string
     */
    public function getName() { return $this->strName; }
    
    /**
     * Lecture de la propri�t� "id"
     *
     * @return string
     */
    public function getId() { return $this->strId; }
    
    /**
     * Lecture du libell�
     *
     * @return string
     */
    public function getLabel() { return $this->strLabel; }
    
    /**
     * Lecture des options
     *
     * @return array
     */
    public function getOptions() { return $this->arrOptions; }
    
    /**
     * Attribution du type
     *
     * @param string $strType type de l'�l�ment
     * @return boolean
     */
    public function setType($strType) { 
        if (!in_array($strType, form_element::$arrType)) {
            trigger_error('Ce type d\'�l�ment n\'existe pas', E_USER_ERROR);
            return false;
        }
        else { 
            $this->strType = $strType;
            return true;
        }
    }
    
    /**
     * Attribution des options
     *
     * @param array $arrOptions
     */
    public function setOptions($arrOptions) {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }
    
    /**
     * M�thode abstraite de rendu d'un libell�. Cette m�thode doit �tre red�finie dans les classes filles
     *
     * @param int $intTabindex tabindex de l'�l�ment dans le formulaire
     */
    abstract protected function render($intTabindex);
}

/**
 * Classe de gestion des champs d'un formulaire
 *
 */    
class form_field extends form_element
{
    /**
     * Diff�rents types accept�s pour un champ de formulaire
     *
     * @var array
     */
    protected static $arrDataType = array(
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
     * Options par d�faut d'un champ de formulaire
     *
     * @var array
     */
    protected static $arrDefaultOptions = array(
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
     * @param string $strLabel libell� du champ
     * @param mixed $mixValue valeur(s) du champ (array ou string)
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     * 
     * @return form_field
     */
    public function __construct($strType, $strLabel, $mixValue, $strName = null, $strId = null, $arrOptions = null)
    {
        $this->setType($strType);
        
        switch($this->getType())
        {
            case 'input:hidden':
            case 'input:text':
            case 'input:password':
            case 'input:button':
            case 'input:submit':
            case 'input:reset':
            case 'input:img':
            case 'textarea':
                if (is_array($mixValue)) trigger_error('Ce type d\'�l�ment n\'accepte pas un tableau de valeurs', E_USER_ERROR);
            break;
        }
        
        $this->arrValues = is_array($mixValue) ? $mixValue : array($mixValue);
        $this->strLabel = $strLabel;
        $this->strName = $strName;
        $this->strId = $strId;
        
        $this->arrOptions = is_null($arrOptions) ? form_field::$arrDefaultOptions : array_merge(form_field::$arrDefaultOptions, $arrOptions);
    }
    
    /**
     * G�n�re le rendu html de l'habillage du champ (notamment le libell�)
     *
     * @param string $strOutputField code html du champ de formulaire
     * @return string champ avec libell�
     */
    protected function renderForm($strOutputField = '')
    {
        $strRequired = $this->arrOptions['required'] ? '<em>* </em>' : '';
        $strAccesskey = is_null($this->arrOptions['accesskey']) ? '' : " accesskey=\"{$this->arrOptions['accesskey']}\"";
        $strStyleform = is_null($this->arrOptions['style_form']) ? '' : " style=\"{$this->arrOptions['style_form']}\"";
        $strClassform = is_null($this->arrOptions['class_form']) ? '' : " class=\"{$this->arrOptions['class_form']}\"";
        
        return "<p id=\"{$this->strId}_form\"{$strStyleform}{$strClassform}><label for=\"{$this->strId}\"{$strAccesskey}>{$strRequired}{$this->strLabel}</label>".$strOutputField."</p>";
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex)
    {
        $strOutput = '';
        
        $strMaxLength = is_null($this->arrOptions['maxlength']) || !is_numeric($this->arrOptions['maxlength']) ? '' : " maxlength=\"{$this->arrOptions['maxlength']}\"";
        $strReadonly = is_null($this->arrOptions['readonly']) || !$this->arrOptions['readonly'] ? '' : " readonly=\"readonly\"";
        $strDisabled = is_null($this->arrOptions['disabled']) || !$this->arrOptions['disabled'] ? '' : " disabled=\"disabled\"";
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        
        $strValue = htmlentities($this->arrValues[0]);

        switch($this->getType())
        {
            case 'input:text':
                $strOutput .= "<input type=\"text\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"text{$strClass}\"{$strStyle}{$strMaxLength}{$strDisabled}{$strReadonly} />";
                if ($this->arrOptions['datatype'] == 'date') $strOutput .= ploopi_open_calendar($this->strId, false, null, 'display:block;float:left;margin-left:-35px;margin-top:1px;');
            break;
            
            case 'input:password':
                $strOutput .= "<input type=\"password\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"text{$strClass}\"{$strStyle}{$strMaxLength}{$strDisabled}{$strReadonly} />";
            break;
            
            case 'textarea':
                $strOutput .= "<textarea name=\"{$this->strName}\" id=\"{$this->strId}\" tabindex=\"{$intTabindex}\" class=\"text{$strClass}\"{$strStyle}{$strMaxLength}{$strDisabled}{$strReadonly}>{$strValue}</textarea>";
            break;
            
            case 'input:file':
                $strOutput .= "<input type=\"file\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"{$strClass}\"{$strStyle} />";
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
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     * 
     * @return form_hidden
     */
    public function __construct($strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('input:hidden', '', $strValue, $strName, $strId, $arrOptions);
    }    
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex)
    {
        $strOutput = '';
        
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        $strValue = htmlentities($this->arrValues[0]);

        $strOutput .= "<input type=\"hidden\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\"{$strClass} />";
        
        return $strOutput;
    }    
}

/**
 * Classe de gestion des champs de type "select" d'un formulaire
 *
 */    
class form_select extends form_field
{
    /**
     * Valeur s�lectionn�e dans le select
     *
     * @var string
     */
    private $strSelected;
    
    /**
     * Options par d�faut d'un select
     *
     * @var array
     */
    protected static $arrDefaultOptions = array(
        'onchange' => null,
        'size' => null,
        'multiple' => false
    );      
    
    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libell� du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $strSelected valeur de l'�l�ment s�lectionn�
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     * 
     * @return form_select
     */
    public function __construct($strLabel, $arrValues = array(), $strSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'�l�ment attend un tableau de valeurs', E_USER_ERROR);

        parent::__construct('select', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? form_select::$arrDefaultOptions : array_merge(form_select::$arrDefaultOptions, $arrOptions));
        
        $this->strSelected = htmlentities($strSelected);
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex)
    {
        $strOutput = '';
        
        $strOnchange = is_null($this->arrOptions['onchange']) ? '' : " onchange=\"javascript:{$this->arrOptions['onchange']}\"";
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        $strSize = is_null($this->arrOptions['size']) ? '' : " size=\"{$this->arrOptions['size']}\"";
        $strMultiple = $this->arrOptions['multiple'] ? " multiple=\"multiple\"" : '';
        
        $strOutput .= "<select name=\"{$this->strName}\" id=\"{$this->strId}\" tabindex=\"{$intTabindex}\" class=\"select{$strClass}\"{$strStyle}{$strOnchange}{$strSize}{$strMultiple} />";
        foreach($this->arrValues as $strKey => $strValue) 
        {
            $strValue = htmlentities($strValue);
            $strKey = htmlentities($strKey);
            
            $strSelected = $this->strSelected == $strKey ? ' selected="selected"' : '';
            $strOutput .= "<option value=\"{$strKey}\"{$strSelected}>{$strValue}</option>";
        }
        $strOutput .= "</select>";
        
        return $this->renderForm($strOutput);
    }
}

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
    private $booChecked;
    
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
        $this->setType('select');
        
        parent::__construct('input:checkbox', $strLabel, $strValue, $strName, $strId, $arrOptions);
        
        $this->booChecked = $booChecked;
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex)
    {
        $strChecked = $this->booChecked ? ' checked="checked"' : '';
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        $strValue = htmlentities($this->arrValues[0]);
        
        return $this->renderForm("<input type=\"checkbox\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"checkbox{$strClass}\"{$strStyle}{$strChecked} />");
    }
}

/**
 * Classe de gestion des champs de type "radio" d'un formulaire
 *
 */    
class form_radio extends form_field
{
    /**
     * True si le radiobutton est coch�
     *
     * @var boolean
     */    
    private $booChecked;
    
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
     * @return form_radio
     */    
    public function __construct($strLabel, $strValue, $booChecked, $strName, $strId = null, $arrOptions = null)
    {
        $this->setType('select');
        
        parent::__construct('input:radio', $strLabel, $strValue, $strName, $strId, $arrOptions);
        
        $this->booChecked = $booChecked;
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex)
    {
        $strChecked = $this->booChecked ? ' checked="checked"' : '';
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        $strValue = htmlentities($this->arrValues[0]);
        
        return $this->renderForm("<input type=\"radio\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"radio{$strClass}\"{$strStyle}{$strChecked} />");
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
     * @param string $strLabel libell� du champ
     * @param string $strValue valeur du champ
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     * 
     * @return form_text
     */        
    public function __construct($strLabel, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('text', $strLabel, $strValue, $strName, $strId, $arrOptions);
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */    
    public function render($intTabindex)
    {
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " class=\"{$this->arrOptions['class']}\"";
        $strValue = ploopi_nl2br($this->arrValues[0]);
        
        return $this->renderForm("<span name=\"{$this->strName}\" id=\"{$this->strId}\" {$strStyle}{$strClass}>{$strValue}</span>");
    }
}

/**
 * Classe de gestion des champs de type "richtext" (fckeditor) d'un formulaire
 *
 */    
class form_richtext extends form_field
{
    /**
     * Options par d�faut d'un richtext
     *
     * @var array
     */
    protected static $arrDefaultOptions = array(
        'width' => '100%',
        'height' => '150px',
        'config' => null,
        'css' => null
    );   
      
    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libell� du champ
     * @param string $strValue valeur du champ
     * @param string $strName propri�t� "name" du champ
     * @param string $strId propri�t� "id" du champ
     * @param array $arrOptions options du champ
     * 
     * @return form_richtext
     */  
    public function __construct($strLabel, $strValue, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('richtext', $strLabel, $strValue, $strName, $strId, is_null($arrOptions) ? form_richtext::$arrDefaultOptions : array_merge(form_richtext::$arrDefaultOptions, $arrOptions));
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */    
    public function render($intTabindex)
    {
        include_once './include/functions/fck.php';
        
        $arrConfig = array();
        if (!is_null($this->arrOptions['config'])) $arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.$this->arrOptions['config'];
        if (!is_null($this->arrOptions['css'])) $arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.$this->arrOptions['css'];
        
        ob_start();
        ploopi_fckeditor($this->strId, $this->arrValues[0], $this->arrOptions['width'], $this->arrOptions['height'], $arrConfig);
        $strContent = ob_get_contents();
        ob_end_clean();
        
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        
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
     * Options par d�faut d'un bouton
     *
     * @var array
     */
    static private $arrDefaultOptions = array(
        'style'     => null,
        'class' => null,
        'onclick'   => null
    );    
    
    /**
     * Diff�rents types de boutons accept�s
     *
     * @var array
     */
    protected static $arrType = array(
        'input:button',
        'input:submit',
        'input:reset',
        'input:img'
    );
    
    /**
     * Constructeur de la classe
     *
     * @param string $strType type du bouton
     * @param string $strValue valeur du bouton (intitul�)
     * @param string $strName propri�t� "name" du bouton
     * @param string $strId propri�t� "id" du bouton
     * @param array $arrOptions options du bouton
     * 
     * @return form_button
     */  
    public function __construct($strType, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        if (!in_array($strType, form_button::$arrType)) trigger_error('Ce type d\'�l�ment n\'existe pas', E_USER_ERROR);
        else
        { 
            $this->setType($strType);
            $this->strValue = $strValue;
            $this->strName = $strName;
            $this->strId = $strId;
            $this->arrOptions = is_null($arrOptions) ? form_button::$arrDefaultOptions : array_merge(form_button::$arrDefaultOptions, $arrOptions);
        }
    }
    
    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */    
    public function render($intTabindex)
    {
        $strOutput = '';
        $strClassName = '';
        
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\""; 
        $strClass = is_null($this->arrOptions['class']) ? '' : " {$this->arrOptions['class']}";
        $strOnclick = is_null($this->arrOptions['onclick']) ? '' : " onclick=\"javascript:{$this->arrOptions['onclick']}\""; 
        $strValue = htmlentities($this->strValue);
        
        switch($this->getType())
        {
            case 'input:reset':
                $strOutput .= "<input type=\"reset\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"button{$strClass}\"{$strStyle}{$strOnclick} />";
            break;
            
            case 'input:button':
                $strOutput .= "<input type=\"button\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"button{$strClass}\"{$strStyle}{$strOnclick} />";
            break;
            
            case 'input:submit':
                $strOutput .= "<input type=\"submit\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"button{$strClass}\"{$strStyle}{$strOnclick} />";
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
     * Contenu de l'�l�ment
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
    public function render($intTabindex)
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
     * Propri�t� "id" du panel
     *
     * @var string
     */
    private $strId;
    
    /**
     * Libell� du panel
     *
     * @var string
     */    
    private $strLabel;
    
    /**
     * Champs du panel
     *
     * @var array
     */
    private $arrFields;    
    
    /**
     * Options du panel
     *
     * @var array
     */    
    private $arrOptions;
    
    /*
     * Object form "parent"
     */
    private $objParentForm;
    
    
    static private $arrDefaultOptions = array(
        'style'     => null,
        'class'     => null
    );     
    
    public static $strDefaultPanel = 'ploopi_panel_default';
    

    /**
     * Constructeur du panel
     * @param string $strId identifiant du panel
     * @param string $strLabel libell� du panel
     * @param array $arrOptions options du panel
     * 
     * @return form_panel 
     */
    public function __construct($strId, $strLabel = null, $arrOptions = null)
    {
        // init Champs
        $this->arrFields = array();
        
        $this->strId = $strId;
        $this->strLabel = $strLabel;
        $this->objParentForm = null;
        
        // Fusion des options
        $this->arrOptions = is_null($arrOptions) ? self::$arrDefaultOptions : array_merge(self::$arrDefaultOptions, $arrOptions);
        
    }    
    
    /**
     * Ajoute un objet de type form_field au panel
     * 
     * @param form_field $objField objet form_field
     */
    public function addField(form_field $objField)
    {
        if ($objField->getType() == 'input:file' && !is_null($this->objParentForm) && get_class($this->objParentForm) == 'form') $this->objParentForm->setOptions(array('enctype' => 'multipart/form-data'));
        
        $this->arrFields[] = &$objField;
    }        
    
    /**
     * Affecte le lien vers le formulaire "parent"
     */
    public function setParentForm(form $objParentForm)
    {
        $this->objParentForm = &$objParentForm;
    }
    
    /**
     * Lecture de la propri�t� "id"
     *
     * @return string
     */
    public function getId() { return $this->strId; }
    
    /**
     * Lecture de la propri�t� "label"
     *
     * @return string
     */
    public function getLabel() { return $this->strLabel; }

    /**
     * Lecture des options
     *
     * @return array
     */
    public function getOptions() { return $this->arrOptions; }

    /**
     * Lecture du nombre de champs
     *
     * @return int
     */
    public function getNbFields() { return sizeof($this->arrFields); }
    
    
    /**
     * Retourne les champs du panel
     *
     * @return array
     */
    public function getFields() { return $this->arrFields; }
    
    /**
     * G�n�re le rendu html du panel
     *
     * @param string $strFields contenu du panel
     * @return string code html
     */    
    public function render($intTabindex)
    {
        $strOutputFields = '';
        
        // G�n�ration des champs
        $strOutputFields = '';
        
        foreach($this->arrFields as $objField)
        {
            $strOutputFields .= $objField->render($intTabindex++);
            // On d�termine si le formulaire dispose d'un champ FILE
            // if (!$booHasFile && $objField->getType() == 'input:file') $booHasFile = true;
        }
        
        $strClass = is_null($this->arrOptions['class']) ? '' : " class=\"{$this->arrOptions['class']}\""; 
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\""; 
        
        $strOutput = "
            <fieldset id=\"{$this->strId}\"{$strClass}{$strStyle}>
                <legend>{$this->strLabel}</legend>
                {$strOutputFields}
            </fieldset>
        ";
        
        return $strOutput;
    }
}
    
/**
 * Classe de gestion d'un formulaire HTML compos� de champs, de boutons et d'un syst�me de validation javascript
 */
class form
{
    /**
     * Panels du formulaire
     *
     * @var array
     */
    private $arrPanels;
    
    /**
     * Boutons du formulaire
     *
     * @var array
     */
    private $arrButtons;
    
    /**
     * Options du formulaire
     *
     * @var array
     */
    private $arrOptions;
    
    /**
     * Propri�t� "id" du formulaire
     */
    private $strId;
    
    /**
     * Action ex�cut�e par le formulaire lors du submit
     */
    private $strAction;
    
    /**
     * M�thode de validation du formulaire (post/get)
     */
    private $strMethod;
    
    /**
     * Panel par d�faut
     */
    private $objDefaultPanel;
    
    /**
     * Options par d�faut des formulaires
     *
     * @var array
     */
    static private $arrDefaultOptions = array(
        'tabindex'      => 1,                                       // tabindex de d�part pour le contenu du formulaire
        'target'        => null,                                    // cible de la validation du formulaire (un iframe par exemple)
        'enctype'       => null,                                    // type d'encodage du formulaire
        'onsubmit'      => null,                                    // action � effectuer sur l'�v�nement "onsubmit" du formulaire
        'button_style'  => 'text-align:right;padding:2px 4px;',     // style appliqu� aux boutons de validation du formulaire
        'legend'        => null,                                    // contenu de la l�gende du formulaire
        'legend_style'  => 'margin-right:4px;',                     // style appliqu� � la l�gende du formulaire
        'class'         => 'ploopi_form',                           // class par d�faut du formulaire (partie champs)
        'style'         => null,                                    // style appliqu� au formulaire (partie champs)
        'class_form'    => null,                                    // class par d�faut du formulaire (global, balise form)
        'style_form'    => null                                     // style appliqu� au formulaire (global, balise form)
    );
    
    /**
     * Constructeur du formulaire
     * @param string $strId identifiant du formulaire
     * @param string $strAction propri�t� "action" du formulaire
     * @param string $strMethod propri�t� "method" du formulaire ("post" par d�faut)
     * @param array $arrOptions options du formulaire (tabindex, target, enctype, onsubmit, button_style, legend, legend_style)
     * 
     * @return form 
     */
    public function __construct($strId, $strAction, $strMethod = 'post', $arrOptions = null)
    {
        // Init Panels, Boutons
        $this->arrPanels = array();
        $this->arrButtons = array();
        
        $this->strId = $strId;
        $this->strAction = $strAction;
        $this->strMethod = $strMethod;
        
        // Fusion des options
        $this->arrOptions = is_null($arrOptions) ? form::$arrDefaultOptions : array_merge(form::$arrDefaultOptions, $arrOptions);
        
        // Cr�ation d'un panel par d�faut (utilis� si l'utilisateur n'en cr�e pas)
        $this->addPanel($this->objDefaultPanel = &new form_panel(form_panel::$strDefaultPanel, null, array('style' => 'border:0;')));
    }
    
    /**
     * Ajoute un objet de type form_field au formulaire
     * 
     * @param form_field $objField objet form_field
     */
    public function addField(form_field $objField)
    {
        $this->objDefaultPanel->addField($objField);
        
        /*if ($objField->getType() == 'input:file') $this->setOptions(array('enctype' => 'multipart/form-data'));
        $this->arrFields[] = $objField;*/
    }
    
    /**
     * Ajoute un objet de type form_button au formulaire
     * 
     * @param form_button $objButton objet form_button
     */
    public function addButton(form_button $objButton)
    {
        $this->arrButtons[] = &$objButton;
    }
    
    /**
     * Ajoute un objet de type form_panel au formulaire
     * 
     * @param form_panel $objPanel objet form_panel
     */
    public function addPanel(form_panel $objPanel)
    {
        $this->arrPanels[] = &$objPanel;
        $objPanel->setParentForm($this);
    }    
    
    /**
     * D�finit les options du formulaire
     * 
     * @param array $arrOptions options du formulaire
     */
    public function setOptions($arrOptions)
    {
        $this->arrOptions = array_merge($this->arrOptions, $arrOptions);
    }
    
    /**
     * Retourne les options du formulaire
     * 
     * @return array
     */
    public function getOptions() { return $this->arrOptions; }
    
    /**
     * Retourne les champs du formulaire
     * 
     * @return array
     */
    public function getFields() 
    {
        $arrFields = array();
        
        foreach($this->arrPanels as $objPanel) $arrFields = array_merge($arrFields, $objPanel->getFields());
        
        return $arrFields; 
    }
    
        
    /**
     * Rendu HTML du formulaire
     *
     * @return string code html du formulaire
     */
    public function render()
    {
        $intTabindex = $this->arrOptions['tabindex'];
        
        
        // G�n�ration des Panels
        $strOutputPanels = '';
        $booHasFile = false;
        
        foreach($this->arrPanels as $objPanel)
        {
            // G�n�ration des champs
            /*
            $strOutputFields = '';
            
            foreach($this->arrFields as $objField)
            {
                $strOutputFields .= $objField->render($intTabindex++);
                // On d�termine si le formulaire dispose d'un champ FILE
                if (!$booHasFile && $objField->getType() == 'input:file') $booHasFile = true;
            }
            */
            if($objPanel->getNbFields()) $strOutputPanels .= $objPanel->render(&$intTabindex);
        }
        
        
        $strTarget = is_null($this->arrOptions['target']) ? '' : " target=\"{$this->arrOptions['target']}\"";
        $strEnctype = is_null($this->arrOptions['enctype']) ? ($booHasFile ? ' enctype="multipart/form-data"' : '') : " enctype=\"{$this->arrOptions['enctype']}\"";
        $strOnsubmit = is_null($this->arrOptions['onsubmit']) ? 'onsubmit="javascript:return ploopi.'.$this->getFormValidateFunc().'(this);"' : " onsubmit=\"javascript:{$this->arrOptions['onsubmit']}\"";
        $strButtonStyle = is_null($this->arrOptions['button_style']) ? '' : " style=\"{$this->arrOptions['button_style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " class=\"{$this->arrOptions['class']}\"";
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        $strClassForm = is_null($this->arrOptions['class_form']) ? '' : " class=\"{$this->arrOptions['class_form']}\"";
        $strStyleForm = is_null($this->arrOptions['style_form']) ? '' : " style=\"{$this->arrOptions['style_form']}\"";
        
        /*
         * G�n�ration du script de validation
         * Attention, n�cessit� de passer par eval() pour les appels AJAX
         */
        
        $strOutput = '<script type="text/javascript">'.$this->renderJS().'</script>';
        
        /*
         * G�n�ration du form
         */
        
        $strOutput .= "<form id=\"{$this->strId}\" action=\"{$this->strAction}\" method=\"{$this->strMethod}\"{$strOnsubmit}{$strTarget}{$strEnctype}{$strClassForm}{$strStyleForm}><div{$strClass}{$strStyle}>";
        

        /*
         * Insertion des champs
         */
        
        $strOutput .= $strOutputPanels;
        
        
        /*
         * G�n�ration des boutons
         */
        
        $strLegend = is_null($this->arrOptions['legend']) ? '' : "<em".(is_null($this->arrOptions['legend_style']) ? '' : " style=\"{$this->arrOptions['legend_style']}\"").">{$this->arrOptions['legend']}</em>";
        
        $strOutput .= "</div><div{$strButtonStyle}>{$strLegend}";
        foreach($this->arrButtons as $objButton)
        {
            $strOutput .= $objButton->render($intTabindex++);
        }
        $strOutput .= '</div>';
        
        $strOutput .= '</form>';
        
        
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
            if ($objField->getName() != '')
            {
                $arrOptions = &$objField->getOptions();
                
                switch ($objField->getType())
                {
                    case 'input:text':
                    case 'input:password':
                    case 'input:file':
                        $strFormat = ($arrOptions['required'] ? '' : 'empty').$arrOptions['datatype'];
                        $strOutput .= "if (ploopi_validatefield('".addslashes($objField->getLabel())."', form.".$objField->getName().", '{$strFormat}'))";
                    break;
        
                    case 'select':
                    case 'color':
                        if ($arrOptions['required']) $strOutput .= "if (ploopi_validatefield('".addslashes($objField->getLabel())."', form.".$objField->getName().", 'selected'))";
                    break;
        
                    case 'input:radio':
                    case 'input:checkbox':
                    break;
                }
            }
                
        }
        
        $strOutput .= "return true; return false; }";
        
        return $strOutput;
    }
    
    // function sdis_interop_site_form_validate(form) {if (ploopi_validatefield('Libell�:', form.sdis_interop_site_label, 'string'))if (ploopi_validatefield('IP distante (si fixe):', form.sdis_interop_site_ip_source, 'emptystring'))if (ploopi_validatefield('Code didentification:', form.sdis_interop_site_code, 'string'))return true; return false; }
    
    /**
     * Retourne le nom de la fonction de validation du formulaire
     * 
     * @return string nom de la fonction de validation
     */
    private function getFormValidateFunc() { return "{$this->strId}_validate"; }
      
}