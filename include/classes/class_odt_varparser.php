<?php
/*
    Copyright (c) 2007-2008 Ovensia
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
?>
<?
class odt_varparser
{
    private $vars = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_data;


    function odt_varparser()
    {
        $this->xml_parser = xml_parser_create();

        // resultat du traitement apres le "parsage"
        $this->parsed_data = '';

        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->xml_parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->xml_parser, "cdata");
    }

    function parse($data, $vars)
    {
        $this->vars = $vars;
        xml_parse($this->xml_parser, $data);
    }


    function tag_open($parser, $tag, $attribs)
    {
        // construction de la chaine de paramètres
        $params = array();
        foreach($attribs as $param => $value) $params[] = "{$param}=\"{$value}\"";
        $params_str = implode(' ',$params);

        // on remplit la chaine XML de sortie
        $this->parsed_data .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";

        $this->xmltags[] = array($tag, $params_str);
    }

    function tag_close($parser, $tag)
    {
        $this->parsed_data .= "</{$tag}>";

        array_pop($this->xmltags);
    }

    function cdata($parser, $data)
    {
        $tag = &$this->xmltags[sizeof($this->xmltags)-1];

        // remplacement des variables template
        $data = str_replace(array_keys($this->vars), array_values($this->vars), $data);

        // gestion du \n \r
        $data = preg_replace("/\r\n|\n|\r/", "</{$tag[0]}><{$tag[0]} {$tag[1]}>", $data);


        $this->parsed_data .= $data;
    }

    function get_xml()
    {
        return($this->parsed_data);
    }
}
