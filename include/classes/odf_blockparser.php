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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Classe permettant d'extraire les blocs de variables d'un mod�le de document ODF.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class odf_blockparser
{
    private $blockvars = array();
    private $blocktemplates = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_data;

    /**
     * Constructeur de la classe. Cr�e le parser.
     *
     * @return odf_blockparser
     *
     * @see xml_parser_create
     */

    public function __construct()
    {
        $this->xml_parser = xml_parser_create('UTF-8');

        // resultat du traitement apres le "parsage"
        $this->parsed_data = '';

        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0); // surtout ne pas mettre 1 !

        xml_set_element_handler($this->xml_parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->xml_parser, "cdata");
    }

    /**
     * Parse les donn�es dans le but d'extraire des blocs identifi�s (tableaux nomm�s)
     *
     * @param string $data
     * @param array $blockvars tableau des blocs � extraire
     *
     * @see xml_parse
     */

    public function parse($data, $blockvars)
    {
        $this->blockvars = $blockvars;
        xml_parse($this->xml_parser, $data);
    }

    /**
     * Gestionnaires de d�but de balise XML. On cherche les tableaux correspondant aux blocs.
     *
     * @param resource $parser parser XML
     * @param string $tag balise XML
     * @param string $attribs attributs de la balise XML
     */

    private function tag_open($parser, $tag, $attribs)
    {
        switch($tag)
        {
            case 'table:table':
                // on augmente de 1 la profondeur des tableaux imbriqu�s
                reset($this->blocktemplates);
                foreach($this->blocktemplates as $blockname => $tpl) if (!$tpl['end']) $this->blocktemplates[$blockname]['depth']++;

                if (isset($attribs['table:name']) && isset($this->blockvars[$attribs['table:name']])) // si ce tableau correpond � un bloc
                {
                    // initialisation du template de bloc
                    $this->blocktemplates[$attribs['table:name']] = array('content' => '', 'end' => 0, 'depth' => 0);
                    // on remplace le template de bloc par une variable du nom du bloc
                    $this->parsed_data .= '{'.$attribs['table:name'].'}';
                }
            break;
        }

        //�construction de la chaine de param�tres
        $params = array();
        foreach($attribs as $param => $value) $params[] = "{$param}=\"{$value}\"";
        $params_str = implode(' ',$params);

        $this->xmltags[] = array($tag, $params_str);

        $keep_content = true;

        reset($this->blocktemplates);
        foreach($this->blocktemplates as $blockname => $tpl)
        {
            if (!$tpl['end'])
            {
                $this->blocktemplates[$blockname]['content'] .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";
                $keep_content = false;
            }
        }

        if ($keep_content) $this->parsed_data .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";
    }

    /**
     * Gestionnaires de fin de balise XML
     *
     * @param resource $parser parser XML
     * @param string $tag balise XML
     */

    private function tag_close($parser, $tag)
    {
        $keep_content = true;

        reset($this->blocktemplates);
        foreach($this->blocktemplates as $blockname => &$tpl)
        {
            if (!$tpl['end'])
            {
                $tpl['content'] .= "</{$tag}>";
                $keep_content = false;
            }
        }

        if ($keep_content) $this->parsed_data .= "</{$tag}>";

        switch($tag)
        {
            case 'table:table':
                reset($this->blocktemplates);
                foreach($this->blocktemplates as $blockname => &$tpl)
                {
                    if (!$tpl['end'])
                    {
                        if ($tpl['depth']>0) $tpl['depth']--;
                        if ($tpl['depth'] == 0) $tpl['end'] = true;
                    }
                }
            break;
        }

        array_pop($this->xmltags);
    }

    /**
     * Gestionnaire du flux de donn�es, r�cup�re le contenu des blocs.
     *
     * @param resource $parser parser XML
     * @param string $data donn�es
     */

    private function cdata($parser, $data)
    {
        $tag = &$this->xmltags[sizeof($this->xmltags)-1];

        $keep_content = true;

        reset($this->blocktemplates);
        foreach($this->blocktemplates as $blockname => &$tpl)
        {
            if (!$tpl['end'])
            {
                $tpl['content'] .= $data;
                $keep_content = false;
            }
        }

        if ($keep_content) $this->parsed_data .= $data;

    }

    /**
     * Retourne le contenu XML pars�
     *
     * @return string contenu XML pars�
     */

    public function get_xml()
    {
        return($this->parsed_data);
    }

    /**
     * Retourne les blocs et leur contenu
     *
     * @return unknown
     */

    public function get_blocktemplates()
    {
        return($this->blocktemplates);
    }
}

