<?php
/*
    Copyright (c) 2007-2013 Ovensia
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

        // traitement des retours chariot (CR LF), fonction de la balise contenant
        // $data = preg_replace("/\r\n|\n|\r/", "</{$tag[0]}><{$tag[0]} {$tag[1]}>", $data);
        $data = str_replace(
            array("[ploopi-br]", "[ploopi-hr]"),
            array("</{$tag[0]}><{$tag[0]} {$tag[1]}>", "</{$tag[0]}><{$tag[0]} text:style-name=\"PLOOPI_PAGE\">"),
            $data
        );

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
                foreach($this->blocktemplates as $blockname => $tpl) if (!$tpl['end']) $this->blocktemplates[$blockname]['depth']++;

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
 * Conversion de balises HTML en version ODF
 * Pour le moment : strong, b, em, u, i, img ?
 */

class odf_html2odf
{
    // Contenu HTML à convertir
    private $_html;
    // Parser XML/HTMl
    private $_html_parser;
    // Résultat de la conversion
    private $_result;

    private $_stack = array();

    private $_odf_parser = null;

    public function __construct($html, $odf_parser)
    {

        $this->_odf_parser = $odf_parser;

        // On contrôle la qualité du code HTML fourni
        $this->_html = ploopi_htmlpurifier($html);
        $this->_result = '';

        $this->_html_parser = xml_parser_create('ISO-8859-1');

        xml_set_object($this->_html_parser, $this);
        xml_parser_set_option($this->_html_parser, XML_OPTION_CASE_FOLDING, 0);

        xml_set_element_handler($this->_html_parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->_html_parser, "cdata");
    }

    public function convert()
    {
        /**
         * Attention le parser XML est bugué avec les entités html...
         * http://drupal.org/node/384060
         */

        xml_parse($this->_html_parser, '<html>'.str_replace('&', '[ploopi-amp]', $this->_html).'</html>');
        return $this->_result;
    }

    public function tag_open($parser, $tag, $attribs)
    {
        switch(strtolower($tag))
        {
            case 'img':
                // $this->_result .= $content;
                // ploopi_print_r($tag);
                // ploopi_print_r($attribs);

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

    public function tag_close($parser, $tag)
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

            default:
            break;
        }
    }

    public function cdata($parser, $data)
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
     *
     */

    private function _xmlize($data)
    {
        return ploopi_xmlentities(html_entity_decode(iconv('ISO-8859-15', 'UTF-8', strip_tags($data)), ENT_QUOTES, 'UTF-8'), true);
    }

    private function _replace_spaces($matches)
    {
        return ' <text:s text:c="'.(strlen($matches[0])-1).'"/>';
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
    private $manifest_xml;
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

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->zip = new ZipArchive();
        if ($this->zip->open($this->filename) === true)
        {
            $this->content_xml = $this->zip->getFromName('content.xml');
            $this->styles_xml = $this->zip->getFromName('styles.xml');
            $this->manifest_xml = $this->zip->getFromName('META-INF/manifest.xml');
            $this->zip->close();
        }
        else
        {
            exit("Erreur à l'ouverture du fichier '{$filename}'\n");
        }
    }


    /**
     * Ajoute une image à la liste des images et génère le code xml associé
     * @param string $image chemin absolu vers le fichier image
     * @param string $width largeur de l'image
     * @param string $height hauteur de l'image
     * @param string $align left, right, center
     * @param string $anchortype paragraph, as-char
     * @return string xml source code
     */

    public function add_image($image, $width = '5cm', $height = '5cm', $align = 'left', $anchortype = 'paragraph')
    {
        $file = basename($image);
        $this->images[$image] = $file;
        $name = 'image'.sizeof($this->images);
        $style = 'PLOOPI_IMG_'.strtoupper($align);

        return '<draw:frame draw:style-name="'.$style.'" draw:name="'.$name.'" text:anchor-type="'.$anchortype.'" svg:width="'.$width.'" svg:height="'.$height.'" draw:z-index="0"><draw:image xlink:href="Pictures/'.$file.'" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>';
    }

    /**
     * Nettoie une chaîne (décode les entités html) et l'encode en UTF8
     * + traitement des URLs
     *
     * @param string $value chaîne brute
     * @return string chaîne "nettoyée"
     *
     */
    public function clean_var($value)
    {
        $odf_html2odf = new odf_html2odf($value, $this);
        return $odf_html2odf->convert();
    }


    /**
     * Définit une variable template et lui affecte une valeur
     *
     * @param string $key nom de la variable
     * @param string $value valeur
     * @param boolean $clean true si le contenu de la valeur doit être nettoyée
     * @param boolean $html true si le contenu est fourni en html (attention il doit être propre)
     *
     * @see odf_parser::clean_var
     */

    public function set_var($key, $value, $clean = true, $html = false)
    {
        if (!$html) $value = ploopi_nl2br(htmlentities($value));
        if ($clean) $value = $this->clean_var($value);

        $this->vars['{'.$key.'}'] = $value;
    }

    /**
     * Définit une variable template de type "image"
     *
     * @param string $key nom de la variable
     * @param string $value chemin absolu vers le fichier image
     * @param string $width largeur de l'image
     * @param string $height hauteur de l'image
     * @param string $align left, right, center
     * @param string $anchortype paragraph, as-char
     */

    public function set_image($key, $value, $width = '5cm', $height = '5cm', $align = 'left', $anchortype = 'paragraph')
    {
        $this->set_var($this->add_image($value, $width, $height, $align, $anchortype), $xml, false, true);
    }

    /**
     * Définit une variable template de type bloc et lui affecte un tableau de valeurs
     *
     * @param unknown_type $blockname
     * @param unknown_type $block
     */

    public function set_blockvar($blockname, $block, $clean = true, $html = false)
    {
        $this->blockvars[$blockname] = array();

        foreach($block as $k => $v)
        {
            foreach($v as $key => $value)
            {
                if (!$html) $value = ploopi_nl2br(htmlentities($value));
                if ($clean) $value = $this->clean_var($value);
                $this->blockvars[$blockname][$k]['{'.$key.'}'] = $value;
            }
        }
    }

    /**
     * Définit une variable template de type bloc et lui affecte un tableau de valeurs
     * Le tableau peut contenir des images
     *
     * @param unknown_type $blockname
     * @param unknown_type $block
     */

    public function set_blockvar_advanced($blockname, $block)
    {
        $this->blockvars[$blockname] = array();

        foreach($block as $k => $v)
        {
            foreach($v as $key => $row)
            {
                if (!isset($row['type'])) $row['type'] = 'var';
                if (!isset($row['value'])) $row['value'] = '';

                if (!isset($row['clean'])) $row['clean'] = true;
                if (!isset($row['html'])) $row['html'] = false;

                if ($row['type'] == 'image')
                {
                    if (empty($row['width'])) $row['width'] = '5cm';
                    if (empty($row['height'])) $row['height'] = '5cm';
                    if (empty($row['align'])) $row['align'] = 'left';
                    if (empty($row['anchortype'])) $row['anchortype'] = 'paragraph';

                    $row['html'] = true;
                    $row['clean'] = false;

                    $row['value'] = $this->add_image($row['value'], $row['width'], $row['height'], $row['align'], $row['anchortype']);
                }

                if (!$row['html']) $row['value'] = ploopi_nl2br(htmlentities($row['value']));
                if ($row['clean']) $row['value'] = $this->clean_var($row['value']);

                $this->blockvars[$blockname][$k]['{'.$key.'}'] = $row['value'];

            }
        }

    }

    /**
     * Parse le contenu du modèle et remplace les variables du template par leurs valeurs
     */

    public function parse()
    {
        // Traitement du fichier manifest pour intégrer la description des images
        if ($this->manifest_xml != NULL)
        {
            if ($this->images && preg_match('@<manifest:file-entry.*/>@i', $this->manifest_xml, $arrMatches, PREG_OFFSET_CAPTURE))
            {
                $insert = '';
                foreach($this->images as $path => $file) {
                    $info = @getimagesize($path);
                    $insert .= '<manifest:file-entry manifest:media-type="'.$info['mime'].'" manifest:full-path="Pictures/'.$file.'"/>'."\r\n ";
                }

                $this->manifest_xml = substr($this->manifest_xml, 0, $arrMatches[0][1]).$insert.substr($this->manifest_xml, $arrMatches[0][1]);
            }
        }

        // Traitement des contenus XML
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
                        //ploopi_print_r($varparser->get_xml());
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

            if (!$this->zip->addFromString('content.xml', $this->content_xml))
                exit('Erreur lors de l\'enregistrement');
            if (!$this->zip->addFromString('styles.xml', $this->styles_xml))
                exit('Erreur lors de l\'enregistrement');
            if (!$this->zip->addFromString('META-INF/manifest.xml', $this->manifest_xml))
                exit('Erreur lors de l\'enregistrement');

            foreach($this->images as $path => $file)
                $this->zip->addFile($path,'Pictures/'.$file);

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

    function __construct($url)
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
        require_once 'HTTP/Request2.php';

        $objRequest = new HTTP_Request2($this->url);

        return $objRequest
            ->setMethod(HTTP_Request2::METHOD_POST)
            ->setHeader("Content-Type", $inputType)
            ->setHeader("Accept", $outputType)
            ->setBody($inputData)
            ->send()
            ->getBody();
    }
}
?>

