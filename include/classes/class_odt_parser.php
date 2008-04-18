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

require_once 'class_odt_blockparser.php';
require_once 'class_odt_varparser.php';

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
    // ouverture du fichier modèle ODT
    // extraction des contenus XML (styles+content).
    // /!\ des contenus sont dans styles.xml (entêtes notamment)
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
            exit("Erreur à l'ouverture du fichier '$filename'\n");
        }
    }

    // conversion des espaces au "format" OpenDocument, sinon ils ne sont pas interprétés
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

    // nettoyage des variables qui sont fournies en ISO-8859-15 (non paramétrable pour notre besoin)
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

            // le contenu XML sans les blocks (mais avec des variables à la place)
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
            exit("Rien à parser - vérifiez que les fichiers content.xml et styles.xml sont correctement formés\n");
        }
    }

    // DEBUG / affichage des variables templates avec leurs valeurs
    function print_vars()
    {
        ploopi_print_r($this->vars);
        ploopi_print_r($this->blockvars);
    }

    // génération du document ODT finalisé
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
?>
