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
 * Inclusion de la classe FCKEditor
 */
include_once './FCKeditor/fckeditor.php';

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
        'accesskey' => null
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
                $strOutput .= "<input type=\"text\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"text{$strClass}\"{$strStyle}{$strMaxLength}{$strDisabled}{$strReadonly}/>";
            break;
            
            case 'input:password':
                $strOutput .= "<input type=\"password\" name=\"{$this->strName}\" id=\"{$this->strId}\" value=\"{$strValue}\" tabindex=\"{$intTabindex}\" class=\"text{$strClass}\"{$strStyle}{$strMaxLength}{$strDisabled}{$strReadonly}/>";
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
        'onchange' => null
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
        
        $strOutput .= "<select name=\"{$this->strName}\" id=\"{$this->strId}\" tabindex=\"{$intTabindex}\" class=\"select{$strClass}\"{$strStyle}{$strOnchange} />";
        foreach($this->arrValues as $strKey => $strValue) 
        {
            $strValue = htmlentities($strValue);
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
        
        return $this->renderForm("<span{$strStyle}{$strClass}>{$strValue}</span>");
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
        $objFCKeditor = new FCKeditor($this->strId) ;
        
        $objFCKeditor->Value = $this->arrValues[0];

        $objFCKeditor->BasePath = './FCKeditor/';

        // width & height
        $objFCKeditor->Width = $this->arrOptions['width'];
        $objFCKeditor->Height = $this->arrOptions['height'];

        if (!is_null($this->arrOptions['config'])) $objFCKeditor->Config['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.$this->arrOptions['config'];
        if (!is_null($this->arrOptions['css'])) $objFCKeditor->Config['EditorAreaCSS'] = _PLOOPI_BASEPATH.$this->arrOptions['css'];
        
        $strStyle = is_null($this->arrOptions['style']) ? '' : " style=\"{$this->arrOptions['style']}\"";
        
        return $this->renderForm("<span{$strStyle}>".$objFCKeditor->CreateHtml($this->strName).'</span>');
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
 * Classe de gestion d'un formulaire HTML compos� de champs, de boutons et d'un syst�me de validation javascript
 */
class form
{
    /**
     * Champs du formulaire
     *
     * @var array
     */
    private $arrFields;
    
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
    
    private $strId;
    private $strAction;
    private $strMethod;
    
    /**
     * Options par d�faut des formulaires
     *
     * @var array
     */
    static private $arrDefaultOptions = array(
        'tabindex'      => 1,
        'target'        => null,
        'enctype'       => null,
        'onsubmit'      => null,
        'button_style'  => 'text-align:right;padding:2px 4px;',
        'legend'        => null,
        'legend_style'  => 'margin-right:4px;',
        'class'         => 'ploopi_form'
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
        $this->arrFields = array();
        $this->arrButtons = array();
        
        $this->strId = $strId;
        $this->strAction = $strAction;
        $this->strMethod = $strMethod;
        
        $this->arrOptions = is_null($arrOptions) ? form::$arrDefaultOptions : array_merge(form::$arrDefaultOptions, $arrOptions);
    }
    
    /**
     * Ajoute un objet de type form_field au formulaire
     * 
     * @param form_field $objField objet form_field
     */
    public function addField(form_field $objField)
    {
        if ($objField->getType() == 'input:file') $this->setOptions(array('enctype' => 'multipart/form-data'));
        
        $this->arrFields[] = $objField;
    }
    
    /**
     * Ajout un objet de type form_button au formulaire
     * 
     * @param form_button $objButton objet form_button
     */
    public function addButton(form_button $objButton)
    {
        $this->arrButtons[] = $objButton;
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
     * Rendu HTML du formulaire
     *
     * @return string code html du formulaire
     */
    public function render()
    {
        $intTabindex = $this->arrOptions['tabindex'];
        
        /*
         * G�n�ration des champs
         */
        $strOutputFields = '';
        $booHasFile = false;
        
        foreach($this->arrFields as $objField)
        {
            $strOutputFields .= $objField->render($intTabindex++);
            // On d�termine si le formulaire dispose d'un champ FILE
            if (!$booHasFile && $objField->getType() == 'input:file') $booHasFile = true;
        }
        
        $strTarget = is_null($this->arrOptions['target']) ? '' : " target=\"{$this->arrOptions['target']}\"";
        $strEnctype = is_null($this->arrOptions['enctype']) ? ($booHasFile ? ' enctype="multipart/form-data"' : '') : " enctype=\"{$this->arrOptions['enctype']}\"";
        $strOnsubmit = is_null($this->arrOptions['onsubmit']) ? 'onsubmit="javascript:eval('.$this->getFormValidateFunc().'_var);return result_'.$this->getFormValidateFunc().';"' : " onsubmit=\"javascript:{$this->arrOptions['onsubmit']}\"";
        $strButtonStyle = is_null($this->arrOptions['button_style']) ? '' : " style=\"{$this->arrOptions['button_style']}\"";
        $strClass = is_null($this->arrOptions['class']) ? '' : " class=\"{$this->arrOptions['class']}\"";
        
        /*
         * G�n�ration du script de validation
         * Attention, n�cessit� de passer par eval() pour les appels AJAX
         */
        
        $strOutput = '<script type="text/javascript">'.$this->getFormValidateFunc().'_var = "'.preg_replace("/(\r\n|\n|\r|\t)+/", ' ', $this->renderJS().' var result_'.$this->getFormValidateFunc().' = '.$this->getFormValidateFunc().'(this);').'";</script>';
        
        /*
         * G�n�ration du form
         */
        
        $strOutput .= "<form id=\"{$this->strId}\" action=\"{$this->strAction}\" method=\"{$this->strMethod}\"{$strOnsubmit}{$strTarget}{$strEnctype}><div{$strClass}>";
        

        /*
         * Insertion des champs
         */
        
        $strOutput .= $strOutputFields;
        
        
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
        // javascript:eval(form_validate);return(result);
        
        $strOutput = "function ".$this->getFormValidateFunc()."(form) {";
        
        foreach($this->arrFields as $objField)
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
    
    /**
     * Retourne le nom de la fonction de validation du formulaire
     * 
     * @return string nom de la fonction de validation
     */
    private function getFormValidateFunc() { return "{$this->strId}_validate"; }
    
}