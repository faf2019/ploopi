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
 * Gestion des formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
     *
     * @var string
     */
    private $_strId;

    /**
     * Action exécutée par le formulaire lors du submit
     *
     * @var string
     */
    private $_strAction;

    /**
     * Méthode de validation du formulaire (post/get)
     *
     * @var string
     */
    private $_strMethod;

    /**
     * Panel par défaut
     *
     * @var form_panel
     */
    private $_objDefaultPanel;

    /**
     * Options par défaut des formulaires
     *
     *   'tabindex'      : tabindex de départ pour le contenu du formulaire
     *   'target'        : cible de la validation du formulaire (un iframe par exemple)
     *   'enctype'       : type d'encodage du formulaire
     *   'onsubmit'      : action à effectuer sur l'événement "onsubmit" du formulaire
     *   'button_style'  : style appliqué aux boutons de validation du formulaire
     *   'legend'        : contenu de la légende du formulaire
     *   'legend_style'  : style appliqué à la légende du formulaire
     *   'class'         : class par défaut du formulaire (partie champs)
     *   'style'         : style appliqué au formulaire (partie champs)
     *   'class_form'    : class par défaut du formulaire (global, balise form)
     *   'style_form'    : style appliqué au formulaire (global, balise form)
     *   'readonly'      : formulaire complet en readonly
     *   'disabled'      : formulaire complet en disabled
     *
     * @var array
     */
    static private $_arrDefaultOptions = array(
        'tabindex'      => 1,                                       // tabindex de départ pour le contenu du formulaire
        'target'        => null,                                    // cible de la validation du formulaire (un iframe par exemple)
        'enctype'       => null,                                    // type d'encodage du formulaire
        'onsubmit'      => null,                                    // action à effectuer sur l'événement "onsubmit" du formulaire
        'button_style'  => '',                                      // style appliqué aux boutons de validation du formulaire
        'legend'        => null,                                    // contenu de la légende du formulaire
        'legend_style'  => 'margin-right:4px;',                     // style appliqué à la légende du formulaire
        'class'         => 'ploopi_generate_form',                  // class par défaut du formulaire (partie champs)
        'style'         => null,                                    // style appliqué au formulaire (partie champs)
        'class_form'    => null,                                    // class par défaut du formulaire (global, balise form)
        'style_form'    => null,                                    // style appliqué au formulaire (global, balise form)
        'readonly'      => false,
        'disabled'      => false
    );

    /**
     * Constructeur du formulaire
     *
     * @param string $strId identifiant du formulaire
     * @param string $strAction propriété "action" du formulaire
     * @param string $strMethod propriété "method" du formulaire ("post" par défaut)
     * @param array $arrOptions options du formulaire (tabindex, target, enctype, onsubmit, button_style, legend, legend_style)
     */
    public function __construct($strId, $strAction, $strMethod = 'post', $arrOptions = null)
    {
        // Init Panels, Boutons
        $this->_arrPanels = array();
        $this->_arrButtons = array();
        $this->_arrJs = array();

        $this->_strId = empty($strId) ? uniqid('ploopi_form_') : $strId;
        $this->_strAction = $strAction;
        $this->_strMethod = $strMethod;

        // Fusion des options
        $this->_arrOptions = is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions);

        // Création d'un panel par défaut (utilisé si l'utilisateur n'en crée pas)
        $this->addPanel($this->_objDefaultPanel = new form_panel(form_panel::strDefaultPanel, null, array('style' => 'border:0;')));
    }

    /**
     * Lecture de la propriété "id"
     *
     * @return string
     */
    public function getId() { return $this->_strId; }

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
        $objButton->setParentForm($this);

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
     * @param integer $intTabindexOptions  tabindex
     *
     * @return string code html du formulaire
     */

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

        $strButtonStyle = is_null($this->_arrOptions['button_style']) ? '' : " style=\"{$this->_arrOptions['button_style']}\"";
        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strClassForm = is_null($this->_arrOptions['class_form']) ? '' : " class=\"{$this->_arrOptions['class_form']}\"";
        $strStyleForm = is_null($this->_arrOptions['style_form']) ? '' : " style=\"{$this->_arrOptions['style_form']}\"";

        // Formulaire readonly/disabled ?
        if ($this->_arrOptions['readonly'] || $this->_arrOptions['disabled']) {
            /*
             * Génération du form
             */

            $strOutput = "<div{$strClass}{$strStyle}><form id=\"{$this->_strId}\" name=\"{$this->_strId}\"><div {$strClassForm}{$strStyleForm}>";
        }
        else {
            $strTarget = is_null($this->_arrOptions['target']) ? '' : " target=\"{$this->_arrOptions['target']}\"";
            $strEnctype = is_null($this->_arrOptions['enctype']) ? ($booHasFile ? ' enctype="multipart/form-data"' : '') : " enctype=\"{$this->_arrOptions['enctype']}\"";
            $strOnsubmit = is_null($this->_arrOptions['onsubmit']) ? ' onsubmit="javascript:return ploopi.'.$this->getFormValidateFunc().'(this);"' : " onsubmit=\"javascript:{$this->_arrOptions['onsubmit']}\"";

            /*
             * Génération du script de validation
             * Attention, nécessité de passer par eval() pour les appels AJAX
             */
            $strOutput = '<script type="text/javascript">'.$this->renderJS().'</script>';

            /*
             * Génération du form
             */

            $strOutput .= "<div{$strClass}{$strStyle}><form id=\"{$this->_strId}\" name=\"{$this->_strId}\" action=\"{$this->_strAction}\" method=\"{$this->_strMethod}\"{$strOnsubmit}{$strTarget}{$strEnctype}><div {$strClassForm}{$strStyleForm}>";
        }


        /*
         * Insertion des champs
         */

        $strOutput .= $strOutputPanels;


        /*
         * Génération des boutons
         */

        $strLegend = is_null($this->_arrOptions['legend']) ? '' : "<em".(is_null($this->_arrOptions['legend_style']) ? '' : " style=\"{$this->_arrOptions['legend_style']}\"").">{$this->_arrOptions['legend']}</em>";

        $strOutput .= '</div>';

        if (!empty($this->_arrButtons)) {
            $strOutput .= "<div{$strButtonStyle} class=\"buttons\">{$strLegend}";
            foreach(array_reverse($this->_arrButtons) as $objButton) $strOutput .= $objButton->render($intTabindex++);
            $strOutput .= '</div>';
        }

        $strOutput .= '</form></div>';


        return $strOutput;
    }

    /**
     * Rendu de la fonction javascript de validation du formulaire
     *
     * @return string fonction de validation javascript
     */
    private function renderJS()
    {
        $strOutput = '';

        foreach($this->_arrPanels as $objPanel)
        {
            // Génération d'une fonction de validation par panel
            $strOutput .= "\nploopi.".$objPanel->getFormValidateFunc().' = function(form) {';
            foreach($objPanel->getFields() as $objField)
            {
                if ($objField->_strName != '')
                {
                    $strFormField = "form['{$objField->_strName}']";

                    $strCond = "jQuery('#{$objField->_strId}_form').css('display') == 'none' ||";

                    switch ($objField->_strType)
                    {
                        case 'input:text':
                        case 'input:password':
                        case 'input:file':
                        case 'textarea':
                            $strFormat = ($objField->_arrOptions['required'] ? '' : 'empty').$objField->_arrOptions['datatype'];
                            $strOutput .= "\nif ({$strCond} ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_strLabel)))."', {$strFormField}, '{$strFormat}'))";
                        break;

                        case 'input:hidden':
                            if (isset($objField->_arrOptions['label'])) {
                                $strFormat = ($objField->_arrOptions['required'] ? '' : 'empty').$objField->_arrOptions['datatype'];
                                $strOutput .= "\nif (ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_arrOptions['label'])))."', {$strFormField}, '{$strFormat}'))";
                            }
                        break;

                        case 'select':
                        case 'color':
                            if ($objField->_arrOptions['required']) $strOutput .= "\nif ({$strCond} ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_strLabel)))."', {$strFormField}, 'selected'))";
                        break;

                        case 'input:radio':
                            if ($objField->_arrOptions['required']) $strOutput .= "\nif ({$strCond} ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_strLabel)))."', {$strFormField}, 'checked'))";
                        break;

                        case 'input:checkbox':
                            if ($objField->_arrOptions['required']) $strOutput .= "\nif ({$strCond} ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_strLabel)))."', form['{$objField->_strName}[]'], 'checked'))";
                        break;

                        case 'datetime':
                            $strCond = "jQuery('#{$objField->_strId}_form').css('display') == 'none' ||";

                            $strFormField = "form['{$objField->_strName}_date']";
                            $strFormat = ($objField->_arrOptions['required'] ? 'date' : 'emptydate');
                            $strOutput .= "\nif ({$strCond} ploopi.validatefield('".addslashes(strip_tags(html_entity_decode($objField->_strLabel)))."', {$strFormField}, '{$strFormat}'))";

                        break;
                    }
                }
            }

            $strOutput .= "\nreturn true; return false; }";
        }


        // Génération de la fonction globale de validation pour tout le formulaire
        $strOutput .= "\nploopi.".$this->getFormValidateFunc()." = function(form) {";
        $strOutput .= "\nploopi.validatereset(form);";
        foreach($this->_arrPanels as $objPanel) $strOutput .= "\nif (ploopi.".$objPanel->getFormValidateFunc()."(form))";
        $strOutput .= "\nreturn true; return false; }";

        foreach($this->_arrJs as $strJs) $strOutput .= "\n{$strJs}";

        return $strOutput;
    }

    /**
     * Retourne le nom de la fonction de validation du formulaire
     *
     * @return string nom de la fonction de validation
     */
    public function getFormValidateFunc() { return "{$this->_strId}_validate"; }

    /**
     * Encodage HTML
     * @param string $str chaîne brute
     * @return string chaîne encodée
     **/
    public static function htmlentities($str) {
        return str::htmlentities($str, null, null, false);
    }
}
