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
 * Génération de documents dans différents formats "bureautique" (ODT, ODS, DOC, XLS, RTF, PDF, etc...) à partir de modèles OpenDocument.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe permettant de traiter les variables simples d'un modèle de document OpenDocument.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class odf_varparser
{
    private $vars = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_data;

    /**
     * Constructeur de la classe. Crée le parser.
     *
     * @return odf_varparser
     *
     * @see xml_parser_create
     */

    public function odf_varparser()
    {
        $this->xml_parser = xml_parser_create('UTF-8');

        // resultat du traitement apres le "parsage"
        $this->parsed_data = '';

        xml_set_object($this->xml_parser, $this);
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->xml_parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->xml_parser, "cdata");
    }

    /**
     * Parse les données dans le but de remplacer des balises
     *
     * @param string $data données à parser
     * @param array $vars tableau des balises à remplacer
     *
     * @see xml_parse
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

    private function tag_open($parser, $tag, $attribs)
    {
        // construction de la chaine de paramètres
        $params = array();
        foreach($attribs as $param => $value) $params[] = "{$param}=\"{$value}\"";
        $params_str = implode(' ',$params);

        // on remplit la chaine XML de sortie
        $this->parsed_data .= ($params_str == '') ? "<{$tag}>" : "<{$tag} {$params_str}>";

        $this->xmltags[] = array($tag, $params_str);
    }

    /**
     * Gestionnaires de fin de balise XML
     *
     * @param resource $parser parser XML
     * @param string $tag balise XML
     */

    private function tag_close($parser, $tag)
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

    private function cdata($parser, $data)
    {
        $tag = &$this->xmltags[sizeof($this->xmltags)-1];

        // remplacement des variables template
        $data = str_replace(array_keys($this->vars), array_values($this->vars), ploopi_xmlentities($data, true));

        // traitement des \n \r
        $data = preg_replace("/\r\n|\n|\r/", "</{$tag[0]}><{$tag[0]} {$tag[1]}>", $data);
        // traitement des espaces
        $data = preg_replace_callback('/\s\s+/',create_function('$matches','if (strlen($matches[0])>1) return(\' <text:s text:c="\'.(strlen($matches[0])-1).\'"/>\'); else return(\' \');'), $data);

        $this->parsed_data .= $data;
    }

    /**
     * Retourne le contenu XML parsé
     *
     * @return string contenu XML parsé
     */

    public function get_xml()
    {
        return($this->parsed_data);
    }
}

/**
 * Classe permettant d'extraire les blocs de variables d'un modèle de document ODF.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class odf_blockparser
{
    private $blockvars = array();
    private $blocktemplates = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_data;

    /**
     * Constructeur de la classe. Crée le parser.
     *
     * @return odf_blockparser
     *
     * @see xml_parser_create
     */

    public function odf_blockparser()
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
     * Parse les données dans le but d'extraire des blocs identifiés (tableaux nommés)
     *
     * @param string $data
     * @param array $blockvars tableau des blocs à extraire
     *
     * @see xml_parse
     */

    public function parse($data, $blockvars)
    {
        $this->blockvars = $blockvars;
        xml_parse($this->xml_parser, $data);
    }

    /**
     * Gestionnaires de début de balise XML. On cherche les tableaux correspondant aux blocs.
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
                // on augmente de 1 la profondeur des tableaux imbriqués
                reset($this->blocktemplates);
                foreach($this->blocktemplates as $blockname => &$tpl) if (!$tpl['end']) $tpl['depth']++;

                if (isset($attribs['table:name']) && isset($this->blockvars[$attribs['table:name']])) // si ce tableau correpond à un bloc
                {
                    // initialisation du template de bloc
                    $this->blocktemplates[$attribs['table:name']] = array('content' => '', 'end' => 0, 'depth' => 0);
                    // on remplace le template de bloc par une variable du nom du bloc
                    $this->parsed_data .= '{'.$attribs['table:name'].'}';
                }
            break;
        }

        // construction de la chaine de paramètres
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
     * Gestionnaire du flux de données, récupère le contenu des blocs.
     *
     * @param resource $parser parser XML
     * @param string $data données
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
     * Retourne le contenu XML parsé
     *
     * @return string contenu XML parsé
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

/**
 * Classe permettant de générer un document bureautique (ODT, ODS, DOC, XLS, PDF, RTF, etc.) à partir d'un modèle OpenDocument.
 * Cette classe fonctionne comme un moteur de template.
 * Il est possible de définir des variables ou des blocs de variables qui seront ensuite remplacés dans le modèle via un parser XML.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class odf_parser
{
    private $filename;
    private $content_xml;
    private $styles_xml;
    private $vars = array();
    private $images = array();
    private $blockvars = array();
    private $blocktemplates = array();

    private $xml_parser;
    private $xml_data = array();
    private $parsed_content_xml;

    private $zip;

    /**
     * Constructeur de la classe.
     * Ouvre le fichier modèle ODF.
     * Extrait les contenus XML (styles+content).
     *
     * @param string $filename nom du fichier du modèle ODF
     * @return odf_parser
     */

    public function odf_parser($filename)
    {
        $this->filename = $filename;
        $this->zip = new ZipArchive();
        if ($this->zip->open($this->filename) === true)
        {
            $this->content_xml = $this->zip->getFromName('content.xml');
            $this->styles_xml = $this->zip->getFromName('styles.xml');
            $this->zip->close();
        }
        else
        {
            exit("Erreur à l'ouverture du fichier '{$filename}'\n");
        }
    }

    /**
     * Nettoie une chaîne (décode les entités html) et l'encode en UTF8
     *
     * @param string $value chaîne brute
     * @return string chaîne "nettoyée"
     *
     */
    protected static function clean_var($value)
    {
        return ploopi_xmlentities(html_entity_decode(iconv('ISO-8859-15', 'UTF-8', $value), ENT_QUOTES, 'UTF-8'), true);
    }
    
    /**
     * Définit une variable template et lui affecte une valeur
     *
     * @param string $key nom de la variable
     * @param string $value valeur
     * @param boolean $clean true si le contenu de la valeur doit être nettoyée
     *
     * @see odf_parser::clean_var
     */

    public function set_var($key, $value, $clean = true)
    {
        $this->vars['{'.$key.'}'] = ($clean) ? self::clean_var($value) : $value;
    }

    /**
     * Définit une variable template de type "image"
     *
     * @param string $key nom de la variable
     * @param string $value chemin absolu vers le fichier image
     * @param string $width largeur de l'image
     * @param string $height hauteur de l'image
     */

    public function set_image($key, $value, $width = '5cm', $height = '5cm')
    {
        $file = basename($value);
        $name = strtok($file,'/.');

        $xml = '<draw:frame draw:style-name="fr1" draw:name="'.$name.'" text:anchor-type="paragraph" svg:width="'.$width.'" svg:height="'.$height.'" draw:z-index="0"><draw:image xlink:href="Pictures/'.$file.'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>';

        $this->set_var($key, $xml, false);

        $this->images[$value] = $file;
    }

    /**
     * Définit une variable template de type bloc et lui affecte un tableau valeurs
     *
     * @param unknown_type $blockname
     * @param unknown_type $block
     */

    public function set_blockvar($blockname, $block)
    {
        $this->blockvars[$blockname] = array();

        foreach($block as $k => $v)
            foreach($v as $key => $value)
            {
                $this->blockvars[$blockname][$k]['{'.$key.'}'] = self::clean_var($value);
            }
    }

    /**
     * Parse le contenu du modèle et remplace les variables du template par leurs valeurs
     */

    public function parse()
    {
        if ($this->content_xml != NULL || $this->styles_xml != NULL)
        {
            $blockparser = new odf_blockparser();

            $blockparser->parse($this->content_xml, $this->blockvars);

            $this->blocktemplates = &$blockparser->get_blocktemplates();

            // le contenu XML sans les blocks (mais avec des nouvelles variables à la place)
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
                        $varparser = new odf_varparser();
                        $varparser->parse($tpl['content'], $vars);
                        $tpl_res .= $varparser->get_xml();
                    }

                    $this->content_xml = str_replace('{'.$blockname.'}', $tpl_res, $this->content_xml);
                }
            }

            // le reste
            $varparser = new odf_varparser();
            $varparser->parse($this->content_xml, $this->vars);
            $this->content_xml = $varparser->get_xml();

            $varparser = new odf_varparser();
            $varparser->parse($this->styles_xml, $this->vars);
            $this->styles_xml = $varparser->get_xml();
        }
        else
        {
            exit("Rien à parser - vérifiez que les fichiers content.xml et styles.xml sont correctement formés\n");
        }
    }

    /**
     * Affichage du contenu des variables depuis un appel de type echo ou print
     *
     * @return string contenu du template
     */
    public function __tostring()
    {
        return ploopi_print_r($this->vars, true).ploopi_print_r($this->blockvars, true);
    }

    /**
     * Enregistre le document ODF généré
     *
     * @param string $newfilename chemin du fichier de destination (ODF)
     *
     * @see ZipArchive
     */

    function save($newfilename)
    {
        if ($newfilename != $this->filename)
        {
            copy($this->filename, $newfilename);
            $this->filename = $newfilename;
        }

        if ($this->zip->open($this->filename, ZIPARCHIVE::CREATE) === TRUE)
        {
            foreach($this->images as $path => $file)
                $this->zip->addFile($path,'Pictures/'.$file);

            if (!$this->zip->addFromString('content.xml', $this->content_xml))
                exit('Erreur lors de l\'enregistrement');
            if (!$this->zip->addFromString('styles.xml', $this->styles_xml))
                exit('Erreur lors de l\'enregistrement');
            $this->zip->close();
        }
        else
        {
            exit('Erreur lors de l\'enregistrement');
        }
    }
}

/**
 * Classe permettant de convertir un document au format OpenDocument en en PDF, DOC, SXW, RTF, XLS,  etc... via le webservice JODConverter
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 *
 * @link http://www.artofsolving.com/opensource/jodconverter
 */

class odf_converter
{
    var $url = '';

    /**
     * Constructeur de la classe.
     *
     * @param string $url URL du webservice JODConverter
     * @return odf_converter
     *
     * @link http://www.artofsolving.com/opensource/jodconverter
     */

    function odf_converter($url)
    {
        $this->url = "{$url}/service";
    }

    /**
     * Convertit un document dans un format qu'Open Office peut lire (ODT, ODS, DOC, XLS, etc...) dans un format qu'il peut écrire (PDF, ODT, ODS, DOC, XLS, SXW, RTF, HTML, etc...)
     *
     * @param string $inputData contenu du document
     * @param string $inputType type mime du document source
     * @param string $outputType type mime du document destination
     * @return string contenu du document généré
     */

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
