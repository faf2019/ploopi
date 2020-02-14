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
 * Gestion des champs HTML (contenu libre) de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
     */
    public function __construct($strLabel, $strValue, $strName = null, $strId = null, $arrOptions = null)
    {
        parent::__construct('text', $strLabel, $strValue, $strName, $strId, $arrOptions);
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champ dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";
        $strClass = is_null($this->_arrOptions['class']) ? '' : " class=\"{$this->_arrOptions['class']}\"";

        $strDataSet = '';
        if (!empty($this->_arrOptions['dataset']) && is_array($this->_arrOptions['dataset'])) {
            foreach($this->_arrOptions['dataset'] as $k => $v) {
                $strDataSet .= self::_getProperty('data-'.str::tourl($k), $v);
            }
        }

        return $this->renderForm("<span name=\"{$this->_strName}\" id=\"{$this->_strId}\" {$strDataSet}{$strStyle}{$strClass}>{$this->_arrValues[0]}</span>");
    }
}

