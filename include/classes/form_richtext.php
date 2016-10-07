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
    private static $_arrDefaultOptions = array(
        'width' => '100%',
        'height' => '150px',
        'config' => null,
        'css' => null,
        'toolbar'=> null
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
        parent::__construct('richtext', $strLabel, $strValue, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));
    }

    /**
     * G�n�re le rendu html du champ
     *
     * @param int $intTabindex tabindex du champs dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        $arrConfig = array();
        if (!is_null($this->_arrOptions['config'])) $arrConfig['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.$this->_arrOptions['config'];
        if (!is_null($this->_arrOptions['css'])) $arrConfig['EditorAreaCSS'] = _PLOOPI_BASEPATH.$this->_arrOptions['css'];

        $arrProperties = array();
        if (!is_null($this->_arrOptions['toolbar'])) $arrProperties['ToolbarSet'] = $this->_arrOptions['toolbar'];

        ob_start();
        ploopi_fckeditor($this->_strId, $this->_arrValues[0], $this->_arrOptions['width'], $this->_arrOptions['height'], $arrConfig, $arrProperties);
        $strContent = ob_get_contents();
        ob_end_clean();

        $strStyle = is_null($this->_arrOptions['style']) ? '' : " style=\"{$this->_arrOptions['style']}\"";

        return $this->renderForm("<span{$strStyle}>".$strContent.'</span>');
    }
}

