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
 * Gestion des champs de type "select" de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
     */
    public function __construct($strLabel, $arrValues, $arrSelected, $strName, $strId = null, $arrOptions = null)
    {
        if (!is_array($arrValues)) trigger_error('Ce type d\'élément attend un tableau de valeurs', E_USER_ERROR);

        if (!is_array($arrSelected)) { $strTmp = $arrSelected; unset($arrSelected); $arrSelected[] = $strTmp; }

        parent::__construct('select', $strLabel, $arrValues, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));

        $this->_arrSelected = arr::map('ploopi\form::htmlentities', $arrSelected);
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
        $strSize = is_null($this->_arrOptions['size']) ? '' : " size=\"{$this->_arrOptions['size']}\"";
        $strMultiple = $this->_arrOptions['multiple'] ? " multiple=\"multiple\"" : '';

        $strOutput .= "<select name=\"{$this->_strName}\" id=\"{$this->_strId}\" tabindex=\"{$intTabindex}\" {$strProperties}{$strSize}{$strMultiple}{$strEvents}>";

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
        $strCurrentGroup = '';
        $strGroup = '';

        foreach($arrValues as $mixKey => $mixValue)
        {
            $mixKey = form::htmlentities($mixKey);
            $arrDataset = array();
            $booSelected = in_array($mixKey, $this->_arrSelected);

            if (is_object($mixValue) && $mixValue instanceof form_select_option)
            {
                $strOutput .= $mixValue->render($intTabindex, $booSelected);
            }
            else
            {
                if (is_array($mixValue))
                {
                    if (isset($mixValue['label']))
                    {
                        $strGroup = isset($mixValue['group']) ? form::htmlentities($mixValue['group']) : '';
                        foreach ($mixValue as $key => $value) {
                            if($key != 'group' && $key != 'label') {
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

                if ($strGroup != $strCurrentGroup)
                {
                    // Fermeture du précédent groupe
                    if ($strCurrentGroup != '') $strOutput .= "</optgroup>";

                    // Ouverture nouveau groupe
                    if ($strGroup != '') $strOutput .= "<optgroup label=\"{$strGroup}\">";

                    $strCurrentGroup = $strGroup;
                }


                $mixValue = str_replace(' ', '&nbsp;', form::htmlentities($mixValue));

                $strDataset = implode(' ', $arrDataset);
                $strSelected = $booSelected ? ' selected="selected"' : '';
                $strOutput .= "<option {$strDataset} value=\"{$mixKey}\"{$strSelected}>{$mixValue}</option>";
            }
        }

        // Fermeture du précédent groupe
        if ($strCurrentGroup != '') $strOutput .= "</optgroup>";

        return $strOutput;
    }
}
