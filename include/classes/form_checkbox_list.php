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
 * Gestion des champs de type liste de "checkbox" de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
    protected static $_arrDefaultOptions = array();

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param array $arrValues valeur(s) du champ
     * @param string $arrSelected valeurs des éléments sélectionnés
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     */
    public function __construct($strLabel, $arrValues = array(), $arrSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);
        if (!is_array($arrSelected)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);

        parent::__construct('input:checkbox', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));

        $this->arrSelected = arr::map('ploopi\form::htmlentities', $arrSelected);
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champ dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strOutput = '';

        $strEvents = $this->generateEvents();
        $strProperties = $this->generateProperties();

        $intNumCheck = 0;
        foreach($arrValues = $this->_arrValues as $strKey => $mixValue)
        {
            $arrDataset = array();
            if (is_array($mixValue))
            {
                if (isset($mixValue['label']))
                {
                    foreach ($mixValue as $key => $value) {
                        if($key != 'label') {
                            $value = form::htmlentities($value);
                            $arrDataset[] = "data-".$key."=\"{$value}\"";
                        }
                    }
                    $mixValue = $mixValue['label'];
                }
                else
                {
                    trigger_error('Valeur d\'option incorrecte', E_USER_ERROR);
                }
            }

            $strValue = form::htmlentities($strValue);
            $strKey = form::htmlentities($strKey);

            $strDataset = implode(' ', $arrDataset);
            $strChecked = in_array($strKey, $this->arrSelected) ? ' checked="checked"' : '';
            $strOutput .= "<span class=\"checkbutton\"><input type=\"checkbox\" {$strDataset} name=\"{$this->_strName}[]\" id=\"{$this->_strId}_{$intNumCheck}\" value=\"{$strKey}\" tabindex=\"{$intTabindex}\" {$strChecked}{$strProperties}{$strEvents}><label for=\"{$this->_strId}_{$intNumCheck}\">{$strValue}</label></span>";

            $intNumCheck++;
        }

        return $this->renderForm("<span id=\"{$this->_strId}\" class=\"onclick\">{$strOutput}</span>");
    }
}

