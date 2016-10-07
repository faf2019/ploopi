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
    public function render($intTabindex = null)
    {
        $strOutput = '';

        $strClass = is_null($this->_arrOptions['class']) ? '' : " {$this->_arrOptions['class']}";
        $strValue = str::htmlentities($this->_arrValues[0]);

        $strOutput .= "<input type=\"hidden\" name=\"{$this->_strName}\" id=\"{$this->_strId}\" value=\"{$strValue}\"{$strClass} />";

        return $strOutput;
    }
}
