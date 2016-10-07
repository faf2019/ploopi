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
    private $_strId;

    /**
     * Libell� du panel
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

    /**
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
     * @param string $strLabel libell� du panel
     * @param array $arrOptions options du panel
     *
     * @return form_panel
     */
    public function __construct($strId, $strLabel = null, $arrOptions = null)
    {
        // init Champs
        $this->_arrFields = array();

        $this->_strId = empty($strId) ? uniqid('ploopi_panel_') : $strId;
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

        $objField->setParentForm($this->_objParentForm);

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
     * Lecture de la propri�t� "id"
     *
     * @return string
     */
    public function getId() { return $this->_strId; }

    /**
     * Lecture de la propri�t� "label"
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
     * G�n�re le rendu html du panel
     *
     * @param string $strFields contenu du panel
     * @return string code html
     */
    public function render(&$intTabindex = null)
    {
        $strOutputFields = '';

        // G�n�ration des champs
        $strOutputFields = '';

        foreach($this->_arrFields as $objField)
        {
            $strOutputFields .= $objField->render($intTabindex++);
            // On d�termine si le formulaire dispose d'un champ FILE
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

    public function getFormValidateFunc() { return $this->_objParentForm->getId().'_'.$this->getId().'_validate'; }
}
