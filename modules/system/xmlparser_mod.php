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
 * Parser XML des fichiers de données d'install des modules
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @todo Revoir cette partie
 */

/**
 * variables globales
 */

$globaldata = '';
$datatype = '';
$newrow = false;
$newfield = false;

// XML PARSER
function startElement_mod($parser, $name, $attribs)
{
    global $globaldata;
    global $datatype;
    global $field;
    global $db;
    global $newrow;
    global $newfield;
    global $dataobject;

    $globaldata = '';

    $name = strtolower($name);

    if (strpos($name,'ploopi_') === 0) // new table
    {
        $datatype = $name;
    }

    if ($newrow) $newfield = true; // new field (all element in a row is a field)

    if($name == 'row' && !$newrow) // new row in table
    {
        $dataobject = new ovensia\ploopi\data_object($datatype);
        $newrow = true;
    }

}

function endElement_mod($parser, $name)
{
    global $globaldata;
    global $datatype;
    global $newrow;
    global $newfield;
    global $dataobject;

    $name = strtolower($name);

    if ($newrow)
    {
        if($name == 'row' && !$newfield) // end row
        {
            $dataobject->save();
            $newrow = false;
        }
        else // end field
        {
            $dataobject->fields[$name] = $globaldata;
            $newfield = false;
        }
    }

    $globaldata = '';
}

function characterData_mod($parser, $data)
{
    global $globaldata;
    $globaldata .= $data;
}

function xmlparser_mod()
{
    $xml_parser = xml_parser_create('ISO-8859-1');

    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 1);
    xml_set_element_handler($xml_parser, "startElement_mod", "endElement_mod");
    xml_set_character_data_handler($xml_parser, "characterData_mod");

    return $xml_parser;
}

function xmlparser_mod_free($xml_parser)
{
    xml_parser_free($xml_parser);
    $globaldata = '';
    $datatype = '';
    $newrow = false;
    $newfield = false;
}

?>
