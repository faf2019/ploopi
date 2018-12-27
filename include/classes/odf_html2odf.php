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
 * Gestion de la conversion de balises HTML en version ODF.
 * Pour le moment : strong, b, em, u, i, img, h1, h2, h3.
 *
 * @package ploopi
 * @subpackage module
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class odf_html2odf
{
    /**
     * Contenu HTML à convertir
     *
     * @var string
     */
    private $_html;

    /**
     * Parseur XML/HTMl
     *
     * @var resource
     */
    private $_html_parser;

    /**
     * Résultat XML de la conversion
     *
     * @var string
     */
    private $_result;

    /**
     * Pile d'éléments
     *
     * @var array
     */
    private $_stack = array();

    /**
     * Parseur ODF
     *
     * @var odf_parser
     */
    private $_odf_parser = null;

    /**
     * Constructeur de la classe.
     *
     * @param string $html code HTML à convertir en XML/ODF
     * @param odf_parser $odf_parser parseur ODF
     */

    public function __construct($html, $odf_parser)
    {

        $this->_odf_parser = $odf_parser;

        // On contrôle la qualité du code HTML fourni
        $this->_html = str::htmlpurifier($html);
        $this->_result = '';

        $this->_html_parser = xml_parser_create('ISO-8859-1');

        xml_set_object($this->_html_parser, $this);
        xml_parser_set_option($this->_html_parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->_html_parser, '_tag_open',  '_tag_close');
        xml_set_character_data_handler($this->_html_parser, '_cdata');
    }

    /**
     * Conversion des entités HTML en XML/ODF
     *
     * @return string contenu XML/ODF
     */
    public function convert()
    {
        /**
         * Attention le parser XML est bugué avec les entités html...
         * http://drupal.org/node/384060
         */

        xml_parse($this->_html_parser, '<html>'.str_replace('&', '[ploopi-amp]', $this->_html).'</html>');
        return $this->_result;
    }

    /**
     * Gestionnaire d'ouverture de balises pour le parseur XML
     * @param resource $parser parseur XML
     * @param string $tag balise XML
     * @param array $attribs propriétés de la balise XML
     */

    private function _tag_open($parser, $tag, $attribs)
    {
        switch(strtolower($tag))
        {
            case 'img':
                // $this->_result .= $content;
                // output::print_r($tag);
                // output::print_r($attribs);

                $width = '5cm';
                $height = '5cm';
                $align = 'left';
                $anchortype = 'paragraph';

                if (file_exists($attribs['src'])) {

                    // Récupération des styles (largeur, hauteur, alignement)
                    if (!empty($attribs['style'])) {
                        $arrStyle = explode(';', $attribs['style']);
                        foreach($arrStyle as $key => $rowStyle) {
                            $row = explode(':', $rowStyle);
                            if (sizeof($row) == 2) {
                                switch($row[0]) {
                                    case 'width': $width = trim($row[1]); break;
                                    case 'height': $height = trim($row[1]); break;
                                    case 'text-align': $align = trim($row[1]); break;
                                }
                            }
                        }
                    }

                    $this->_result .= $this->_odf_parser->add_image($attribs['src'], $width, $height, $align, $anchortype);
                }

            break;

            case 'a':
                $content = '<text:a xlink:type="simple" xlink:href="'.$attribs['href'].'">';
                $this->_stack[] = array('a', $content);
                $this->_result .= $content;
            break;

            case 'strong':
            case 'b':
                $content = '<text:span text:style-name="PLOOPI_BOLD">';
                $this->_stack[] = array('b', $content);
                $this->_result .= $content;
            break;

            case 'u':
                $content = '<text:span text:style-name="PLOOPI_UNDERLINE">';
                $this->_stack[] = array('u', $content);
                $this->_result .= $content;
            break;

            case 'em':
            case 'i':
                $content = '<text:span text:style-name="PLOOPI_ITALIC">';
                $this->_stack[] = array('i', $content);
                $this->_result .= $content;
            break;

            case 'h1':
                $content = '<text:span text:style-name="PLOOPI20">';
                $this->_stack[] = array('h1', $content);
                $this->_result .= $content;
            break;

            case 'h2':
                $content = '<text:span text:style-name="PLOOPI16">';
                $this->_stack[] = array('h2', $content);
                $this->_result .= $content;
            break;

            case 'h3':
                $content = '<text:span text:style-name="PLOOPI14">';
                $this->_stack[] = array('h3', $content);
                $this->_result .= $content;
            break;

            case 'hr':
                // Astuce pour traiter les retours à la ligne :
                // Fermer tous les span/a ouverts et les réouvrir
                foreach($this->_stack as $row) {
                    switch($row[0]) {
                        case 'a':
                            $this->_result .= '</text:a>';
                        break;
                        default:
                            $this->_result .= '</text:span>';
                        break;
                    }
                }

                $this->_result .= "[ploopi-hr]"; // Ils sont traités plus tard

                foreach($this->_stack as $row) $this->_result .= $row[1];
            break;


            case 'p':
            case 'br':
                // Astuce pour traiter les retours à la ligne :
                // Fermer tous les span/a ouverts et les réouvrir
                foreach($this->_stack as $row) {
                    switch($row[0]) {
                        case 'a':
                            $this->_result .= '</text:a>';
                        break;
                        default:
                            $this->_result .= '</text:span>';
                        break;
                    }
                }

                $this->_result .= "[ploopi-br]"; // Ils sont traités plus tard

                foreach($this->_stack as $row) $this->_result .= $row[1];
            break;

            default:
            break;
        }
    }

    /**
     * Gestionnaire de fermeture de balises pour le parseur XML
     * @param resource $parser parseur XML
     * @param string $tag balise XML
     */

    private function _tag_close($parser, $tag)
    {
        switch(strtolower($tag))
        {
            case 'a':
                $this->_result .= '</text:a>';
                array_pop($this->_stack);
            break;

            case 'strong':
            case 'b':
                $this->_result .= '</text:span>';
                array_pop($this->_stack);
            break;

            case 'u':
            case 'em':
                $this->_result .= '</text:span>';
                array_pop($this->_stack);
            break;

            case 'i':
                $this->_result .= '</text:span>';
                array_pop($this->_stack);
            break;

            case 'h1':
            case 'h2':
            case 'h3':
                $this->_result .= '</text:span>';
                //$this->_result .= '</text:h>';
                array_pop($this->_stack);
            break;

            default:
            break;
        }
    }

    /**
     * Gestionnaire du contenu des balises pour le parseur XML
     * @param resource $parser parseur XML
     * @param string $data contenu de la balise
     */

    private function _cdata($parser, $data)
    {
        // Conversion en entités XML
        $data = $this->_xmlize(str_replace('[ploopi-amp]', '&', $data));
        // Traitement des espaces multiples
        $data = preg_replace_callback('@\s{2,}@', array($this, '_replace_spaces'), $data);

        $this->_result .= $data;
    }

    /**
     * Nettoie une chaîne (décode les entités html) et l'encode en UTF8
     *
     * @param string $value chaîne brute
     * @return string chaîne "nettoyée"
     */
    private function _xmlize($data)
    {
        return str::xmlentities(html_entity_decode(iconv('ISO-8859-15', 'UTF-8', strip_tags($data)), ENT_QUOTES, 'UTF-8'), true);
    }

    /**
     * Remplacement des espaces par des entitées ODF/XML
     * @param array $matches tableau d'occurences
     * @return string contenu XML
     */
    private function _replace_spaces($matches)
    {
        return ' <text:s text:c="'.(strlen($matches[0])-1).'"/>';
    }

}
