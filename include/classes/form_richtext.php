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
 * Gestion des champs de type "richtext" (éditeur du socle, js/editor.js) de formulaires
 *
 * @package ploopi
 * @subpackage form
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class form_richtext extends form_field
{
    /**
     * Options par défaut d'un richtext
     *
     * @var array
     */
    private static $_arrDefaultOptions = array(
        'profile' => 'simple',      // profil de l'éditeur (js/editor.js) : simple, complet
        'height' => null,           // hauteur minimale en pixels
        'css' => null,              // feuilles du contenu (tableau d'URL)
        'link' => null,             // URL de la fenêtre de choix d'un lien
        'image' => null             // URL de la fenêtre de choix d'une image
    );

    /**
     * Constructeur de la classe
     *
     * @param string $strLabel libellé du champ
     * @param string $strValue valeur du champ
     * @param string $strName propriété "name" du champ
     * @param string $strId propriété "id" du champ
     * @param array $arrOptions options du champ
     */
    public function __construct($strLabel, $strValue, $strName, $strId = null, $arrOptions = null)
    {
        parent::__construct('richtext', $strLabel, $strValue, $strName, $strId, is_null($arrOptions) ? self::$_arrDefaultOptions : array_merge(self::$_arrDefaultOptions, $arrOptions));
    }

    /**
     * Génère le rendu html du champ
     *
     * @param int $intTabindex tabindex du champ dans le formulaire
     * @return string code html
     */
    public function render($intTabindex = null)
    {
        /**
         * Champ textarea déclaré par data-ploopi-editor : l'éditeur (TinyMCE) se crée seul
         * (js/editor.js). La valeur est envoyée avec le formulaire (l'ancien éditeur remplaçait
         * une balise <span> sans nom : rien n'était envoyé).
         */
        $arrAttributes = array(
            'data-ploopi-editor' => $this->_arrOptions['profile'],
            'data-ploopi-editor-height' => $this->_arrOptions['height'],
            'data-ploopi-editor-css' => is_array($this->_arrOptions['css']) ? implode(',', $this->_arrOptions['css']) : $this->_arrOptions['css'],
            'data-ploopi-editor-link' => $this->_arrOptions['link'],
            'data-ploopi-editor-image' => $this->_arrOptions['image']
        );

        $strAttributes = '';
        foreach($arrAttributes as $strName => $mixValue)
        {
            if (!is_null($mixValue) && $mixValue !== '') $strAttributes .= ' '.$strName.'="'.str::htmlentities($mixValue).'"';
        }

        $strStyle = is_null($this->_arrOptions['style']) ? '' : ' style="'.str::htmlentities($this->_arrOptions['style']).'"';
        $strTabindex = is_null($intTabindex) ? '' : ' tabindex="'.(int) $intTabindex.'"';

        $strContent = '<textarea id="'.str::htmlentities($this->_strId).'" name="'.str::htmlentities($this->_strName).'"'.$strStyle.$strTabindex.$strAttributes.'>'.str::htmlentities($this->_arrValues[0]).'</textarea>';

        return $this->renderForm($strContent);
    }
}
