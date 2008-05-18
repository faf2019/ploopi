<?php
/*
    Copyright (c) 2002-2007 Netlor
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

/**
 * Conversion d'un flux de données XML en tableau PHP 
 * 
 * @package ploopi
 * @subpackage xml
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe xml2array 
 * 
 * @package ploopi
 * @subpackage xml
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class xml2array
{

    var $parser;
    var $node_stack = array();
    var $xmlarray = array();
    var $currentdata = '';

    function parse($xmlcontent="")
    {
        // set up a new XML parser to do all the work for us
        $this->parser = xml_parser_create('ISO-8859-1');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "startElement", "endElement");
        xml_set_character_data_handler($this->parser, "characterData");

        // Build a Root node and initialize the node_stack...
        $this->node_stack = array();

        $this->xmlarray['root'] = array();
        $this->node_stack[] = &$this->xmlarray['root'];
        $this->node_stack[] = &$this->xmlarray['root attributes'];
        
        // parse the data and free the parser...
        if (xml_parse($this->parser, $xmlcontent))
        {
            xml_parser_free($this->parser);
            return($this->xmlarray);
        }
        else return(false);
    }

    function parseFile($filename)
    {
        $xmlcontent = '';

        if (file_exists($filename))
        {
            $fd = fopen($filename,"r");
            if ($fd)
            {
                while (!feof($fd)) $xmlcontent .= fgets($fd, 4096);
                fclose($fd);
            }

            return($this->parse($xmlcontent));
        }
        else return(false);
    }

    function startElement($parser, $name, $attrs)
    {
        $this->currentdata = '';
        
        $s = (sizeof($this->node_stack)-2);
        
        $this->node_stack[] = &$this->node_stack[$s][$name][];
        $this->node_stack[] = &$this->node_stack[$s]["{$name} attributes"][];
        
        if (!empty($attrs)) $this->node_stack[$s+3] = $attrs;
    }

    function endElement($parser, $name)
    {
        if (trim($this->currentdata) != '') $this->node_stack[(sizeof($this->node_stack)-2)] = $this->currentdata;
        array_pop($this->node_stack);
        array_pop($this->node_stack);
        $this->currentdata = '';
    }

    function characterData($parser, $data)
    {
        $this->currentdata .= $data;
    }
}
?>
