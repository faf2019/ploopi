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
 * Traitement des variables simples d'un modèle de document ODF
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class odf_varparser
{
    /**
     * Variables à remplacer
     *
     * @var array
     */
    private $vars = array();

    /**
     * Parseur XML
     *
     * @var resource
     */
    private $xml_parser;

    /**
     * Résultat du traitement (XML)
     *
     * @var string
     */
    private $parsed_data;

    /**
     * Styles XML prédéfinis
     *
     * @var string
     */
    private static $_ploopi_styles = '
        <style:style style:name="PLOOPI_BOLD" style:family="text">
            <style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
        </style:style>

        <style:style style:name="PLOOPI_ITALIC" style:family="text">
            <style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
        </style:style>

        <style:style style:name="PLOOPI_UNDERLINE" style:family="text">
            <style:text-properties style:text-underline-style="solid" style:text-underline-width="auto" style:text-underline-color="font-color"/>
        </style:style>

        <style:style style:name="PLOOPI_PAGE" style:family="paragraph" style:parent-style-name="Standard">
            <style:paragraph-properties fo:break-before="page"/>
        </style:style>

        <style:style style:name="PLOOPI_IMG_LEFT" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties style:horizontal-pos="left" style:horizontal-rel="paragraph" style:mirror="none" fo:margin-top="0.15cm" fo:margin-right="0.15cm" fo:margin-bottom="0.15cm" fo:margin-left="0.15cm" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
        </style:style>

        <style:style style:name="PLOOPI_IMG_RIGHT" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties style:horizontal-pos="right" style:horizontal-rel="paragraph" style:mirror="none" fo:margin-top="0.15cm" fo:margin-right="0.15cm" fo:margin-bottom="0.15cm" fo:margin-left="0.15cm" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
        </style:style>

        <style:style style:name="PLOOPI_IMG_CENTER" style:family="graphic" style:parent-style-name="Graphics">
            <style:graphic-properties style:horizontal-pos="center" style:horizontal-rel="paragraph" style:mirror="none" fo:margin-top="0.15cm" fo:margin-right="0.15cm" fo:margin-bottom="0.15cm" fo:margin-left="0.15cm" fo:clip="rect(0cm, 0cm, 0cm, 0cm)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard"/>
        </style:style>

        <style:style style:name="PLOOPI14" style:family="text">
            <style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt" />
        </style:style>

        <style:style style:name="PLOOPI16" style:family="text">
            <style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt" />
        </style:style>

        <style:style style:name="PLOOPI20" style:family="text">
            <style:text-properties fo:font-size="14pt" style:font-size-asian="14pt" style:font-size-complex="14pt" />
        </style:style>
    ';

    /**
     * Constructeur de la classe. Crée le parser.
     *
     * @return odf_varparser
     *
     * @see xml_parser_create
     */

    public function __construct()
    {
        $this->xml_parser = xml_parser_create('UTF-8');

        // resultat du traitement apres le "parsage"
        $this->parsed_data = '';

        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->xml_parser, '_tag_open', '_tag_close');
        xml_set_character_data_handler($this->xml_parser, '_cdata');
    }

    /**
     * Parse les données dans le but de remplacer des balises
     *
     * @param string $data données à parser
     * @param array $vars tableau des balises à remplacer
     */

    public function parse($data, $vars)
    {
        $this->vars = $vars;
        xml_parse($this->xml_parser, $data);
    }

    /**
     * Gestionnaires de début de balise XML
     *
     * @param resource $parser parser XML
     * @param string $tag balise XML
     * @param string $attribs attributs de la balise XML
     */

    private function _tag_open($parser, $tag, $attribs)
    {
        // construction de la chaine de paramètres
        // + un petit hack pour gérer correctement les urls
        $params = array();
        foreach($attribs as $param => $value) $params[] = "{$param}=\"".str_replace('&', '&amp;', $value)."\"";
        $params_str = implode(' ',$params);

        // on remplit la chaine XML de sortie
        $this->parsed_data .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";

        $this->xmltags[] = array($tag, $params_str);

        if ($tag == 'office:automatic-styles')
        {
            // Insertion des styles PLOOPI de base
            $this->parsed_data .= preg_replace("/\s{2+}/", " ", preg_replace("/\r\n|\n|\r/", "", self::$_ploopi_styles));
        }

    }

    /**
     * Gestionnaires de fin de balise XML
     *
     * @param resource $parser parser XML
     * @param string $tag balise XML
     */

    private function _tag_close($parser, $tag)
    {
        $this->parsed_data .= "</{$tag}>";

        array_pop($this->xmltags);
    }

    /**
     * Gestionnaire du flux de données, remplace les balises par leur valeur. Traite les espaces et les retours à la ligne.
     *
     * @param resource $parser parser XML
     * @param string $data données
     *
     * @uses preg_replace
     * @uses preg_replace_callback
     */

    private function _cdata($parser, $data)
    {
        $s = sizeof($this->xmltags)-1;
        $tag = $this->xmltags[$s];

        // remplacement des variables template
        $data = str_replace(array_keys($this->vars), array_values($this->vars), str::xmlentities($data));

        // Cas d'un retour chariot dans le contenu
        if (strpos($data, '[ploopi-br]') !== false)
        {
            $paragraph = '';

            // Si le tag courant n'est pas un paragraphe
            if ($tag[0] != 'text:p') {
                // Recherche du dernier paragraphe ouvert
                for($i = $s; $i >= 0; $i--) {
                    if ($this->xmltags[$i][0] == 'text:p') {
                        $paragraph = "</{$this->xmltags[$i][0]}><{$this->xmltags[$i][0]} {$this->xmltags[$i][1]}>";
                        break;
                    }
                }
            }

            $data = str_replace('[ploopi-br]', "</{$tag[0]}>{$paragraph}<{$tag[0]} {$tag[1]}>", $data);
        }

        // Cas d'un saut de page dans le contenu
        if (strpos($data, '[ploopi-hr]') !== false)
        {
            $data = str_replace('[ploopi-hr]', "</{$tag[0]}><{$tag[0]} text:style-name=\"PLOOPI_PAGE\">", $data);
        }

        $this->parsed_data .= $data;
    }

    /**
     * Retourne le contenu XML parsé
     *
     * @return string contenu XML parsé
     */

    public function get_xml()
    {
        return $this->parsed_data;
    }
}
