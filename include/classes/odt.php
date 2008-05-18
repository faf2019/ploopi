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

/**
 * G�n�ration de documents dans diff�rents formats "bureautique" (ODT, DOC, RTF, PDF, etc...) � partir de mod�les ODT.
 * 
 * @package ploopi
 * @subpackage odt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe permettant de traiter les variables simples d'un mod�le de document ODT
 * 
 * @package ploopi
 * @subpackage odt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

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
        //�construction de la chaine de param�tres
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

        // traitement des \n \r
        $data = preg_replace("/\r\n|\n|\r/", "</{$tag[0]}><{$tag[0]} {$tag[1]}>", $data);
        // traitement des espaces
        $data = preg_replace_callback('/\s\s+/',create_function('$matches','if (strlen($matches[0])>1) return(\' <text:s text:c="\'.(strlen($matches[0])-1).\'"/>\'); else return(\' \');'), $data);
        
        $this->parsed_data .= $data;
    }

    function get_xml()
    {
        return($this->parsed_data);
    }
}


/**
 * Classe permettant d'extraire les blocs de variables d'un mod�le de document ODT.
 * 
 * @package ploopi
 * @subpackage odt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class odt_blockparser
{
    private $blockvars = array();
    private $blocktemplates = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_data;


    function odt_blockparser()
    {
        $this->xml_parser = xml_parser_create();

        // resultat du traitement apres le "parsage"
        $this->parsed_data = '';

        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0); // surtout ne pas mettre 1 !

        xml_set_element_handler($this->xml_parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->xml_parser, "cdata");
    }

    function parse($data, $blockvars)
    {
        $this->blockvars = $blockvars;
        xml_parse($this->xml_parser, $data);
    }


    function tag_open($parser, $tag, $attribs)
    {
        switch($tag)
        {
            case 'table:table':
                // on augmente de 1 la profondeur des tableaux imbriqu�s
                reset($this->blocktemplates);
                foreach($this->blocktemplates as $blockname => &$tpl) if (!$tpl['end']) $tpl['depth']++;

                if (isset($this->blockvars[$attribs['table:name']])) // si ce tableau correpond � un bloc
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
        foreach($this->blocktemplates as $blockname => &$tpl)
        {
            if (!$tpl['end'])
            {
                $tpl['content'] .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";
                $keep_content = false;
            }
        }

        if ($keep_content) $this->parsed_data .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";
    }

    function tag_close($parser, $tag)
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

    function cdata($parser, $data)
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


    function get_xml()
    {
        return($this->parsed_data);
    }


    function get_blocktemplates()
    {
        return($this->blocktemplates);
    }
}



/**
 * Classe permettant de g�n�rer un document bureautique (ODT, DOC, PDT, etc.) � partir d'un mod�le ODT.
 * Cette classe fonctionne comme un moteur de template.
 * Il est possible de d�finir de variables ou des blocs de variables qui seront ensuite remplac�es dans le mod�le via un parser XML.
 * 
 * @package ploopi
 * @subpackage odt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class odt_parser
{
    private $filename;
    private $content_xml;
    private $styles_xml;
    private $vars = array();
    private $blockvars = array();
    private $blocktemplates = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_content_xml;

    // constructeur
    // ouverture du fichier mod�le ODT
    // extraction des contenus XML (styles+content).
    // /!\ des contenus sont dans styles.xml (ent�tes notamment)
    function odt_parser($filename)
    {
        $this->filename = $filename;
        $zip = new ZipArchive();
        if ($zip->open($this->filename) === TRUE)
        {
            $this->content_xml = $zip->getFromName('content.xml');
            $this->styles_xml = $zip->getFromName('styles.xml');
            $zip->close();
        }
        else
        {
            exit("Erreur � l'ouverture du fichier '$filename'\n");
        }
    }

    // conversion des espaces au "format" OpenDocument, sinon ils ne sont pas interpr�t�s
    /*function _convert_spaces($matches)
    {
        if (strlen($matches[0])>1) return(' <text:s text:c="'.(strlen($matches[0])-1).'"/>');
        else return(' ');
    }*/

    // encodage utf8 + xml
    function _utf8_encode($value)
    {
        // bug avec OpenOffice 2.3
        //return(str_replace(array("&", ">", "<", "\""), array("&amp;", "&gt;", "&lt;", "&quot;"), iconv("ISO-8859-15", "UTF-8", $value)));
        return(str_replace(array("&", ">", "<", "\""), array("", "&gt;", "&lt;", "&quot;"), iconv("ISO-8859-15", "UTF-8", $value)));
    }

    // nettoyage des variables qui sont fournies en ISO-8859-15 (non param�trable pour notre besoin)
    function _clean_var($value)
    {
        $value = html_entity_decode($value, ENT_QUOTES, 'ISO-8859-15');
        $value = $this->_utf8_encode($value);
        //$value = preg_replace_callback('/\s\s+/',array('self','_convert_spaces'),$value);
        //$value = str_replace('&amp;','aamp;',$value);
        return($value);
    }

    // affectation d'une valeur pour une variable template
    function set_var($key, $value, $clean = true)
    {
        $this->vars['{'.$key.'}'] = ($clean) ? $this->_clean_var($value) : $value;
    }

    // affectation d'un bloc de valeurs  pour une variable template de type block
    function set_blockvar($blockname, $block)
    {
        $this->blockvars[$blockname] = array();

        foreach($block as $k => $v)
            foreach($v as $key => $value)
            {
                $this->blockvars[$blockname][$k]['{'.$key.'}'] = $this->_clean_var($value);
            }
    }

    // partie principale
    // traitement du document
    function parse()
    {
        if ($this->content_xml != NULL || $this->styles_xml != NULL)
        {
            $blockparser = new odt_blockparser();

            $blockparser->parse($this->content_xml, $this->blockvars);

            $this->blocktemplates = &$blockparser->get_blocktemplates();

            // le contenu XML sans les blocks (mais avec des variables � la place)
            $this->content_xml = $blockparser->get_xml();

            // traitement des blocks
            reset($this->blocktemplates);
            foreach($this->blocktemplates as $blockname => $tpl)
            {
                if (isset($this->blockvars[$blockname]))
                {

                    $tpl_res = '';
                    foreach($this->blockvars[$blockname] as $vars)
                    {
                        $varparser = new odt_varparser();
                        $varparser->parse($tpl['content'], $vars);
                        $tpl_res .= $varparser->get_xml();
                    }

                    $this->content_xml = str_replace('{'.$blockname.'}', $tpl_res, $this->content_xml);
                }
            }

            // le reste
            $varparser = new odt_varparser();
            $varparser->parse($this->content_xml, $this->vars);
            $this->content_xml = $varparser->get_xml();

            $varparser = new odt_varparser();
            $varparser->parse($this->styles_xml, $this->vars);
            $this->styles_xml = $varparser->get_xml();
        }
        else
        {
            exit("Rien � parser - v�rifiez que les fichiers content.xml et styles.xml sont correctement form�s\n");
        }
    }

    // DEBUG / affichage des variables templates avec leurs valeurs
    function print_vars()
    {
        ploopi_print_r($this->vars);
        ploopi_print_r($this->blockvars);
    }

    // g�n�ration du document ODT finalis�
    function save($newfilename)
    {
        if ($newfilename != $this->filename)
        {
            copy($this->filename, $newfilename);
            $this->filename = $newfilename;
        }

        $zip = new ZipArchive();
        if ($zip->open($this->filename, ZIPARCHIVE::CREATE) === TRUE)
        {
            if (!$zip->addFromString('content.xml', $this->content_xml))
                exit('Erreur lors de l\'enregistrement');
            if (!$zip->addFromString('styles.xml', $this->styles_xml))
                exit('Erreur lors de l\'enregistrement');
            $zip->close();
        }
        else
        {
            exit('Erreur lors de l\'enregistrement');
        }
    }
}


/**
 * Classe permettant de convertir un document ODT en PDF, DOC, SXW, RTF, etc... via le webservice JODConverter
 * 
 * @package ploopi
 * @subpackage odt
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class odt_converter
{
    var $url = '';

    function odt_converter($url)
    {
        $this->url = "{$url}/service";
    }

    function convert($inputData, $inputType, $outputType) 
    {
        require_once 'HTTP/Request.php';
        $request = new HTTP_Request($this->url);
        $request->setMethod("POST");
        $request->addHeader("Content-Type", $inputType);
        $request->addHeader("Accept", $outputType);
        $request->setBody($inputData);
        $request->sendRequest();
        return $request->getResponseBody();
    }
}

?>
