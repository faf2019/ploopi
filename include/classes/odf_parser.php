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
 * G�n�ration de documents dans diff�rents formats "bureautique" (ODT, ODS, DOC, XLS, RTF, PDF, etc...) � partir de mod�les OpenDocument.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe permettant de g�n�rer un document bureautique (ODT, ODS, DOC, XLS, PDF, RTF, etc.) � partir d'un mod�le OpenDocument.
 * Cette classe fonctionne comme un moteur de template.
 * Il est possible de d�finir des variables ou des blocs de variables qui seront ensuite remplac�s dans le mod�le via un parser XML.
 *
 * @package ploopi
 * @subpackage odf
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
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
     * Ouvre le fichier mod�le ODF.
     * Extrait les contenus XML (styles+content).
     *
     * @param string $filename nom du fichier du mod�le ODF
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
            exit("Erreur � l'ouverture du fichier '{$filename}'\n");
        }
    }


    /**
     * Ajoute une image � la liste des images et g�n�re le code xml associ�
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
     * Nettoie une cha�ne (d�code les entit�s html) et l'encode en UTF8
     * + traitement des URLs
     *
     * @param string $value cha�ne brute
     * @return string cha�ne "nettoy�e"
     *
     */
    public function clean_var($value)
    {
        $odf_html2odf = new odf_html2odf($value, $this);
        return $odf_html2odf->convert();
    }


    /**
     * D�finit une variable template et lui affecte une valeur
     *
     * @param string $key nom de la variable
     * @param string $value valeur
     * @param boolean $clean true si le contenu de la valeur doit �tre nettoy�e
     * @param boolean $html true si le contenu est fourni en html (attention il doit �tre propre)
     *
     * @see odf_parser::clean_var
     */

    public function set_var($key, $value, $clean = true, $html = false)
    {
        if (!$html) $value = str::nl2br(str::htmlentities($value));
        if ($clean) $value = $this->clean_var($value);

        $this->vars['{'.$key.'}'] = $value;
    }

    /**
     * D�finit une variable template de type "image"
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
        $this->set_var($key, $this->add_image($value, $width, $height, $align, $anchortype), false, true);
    }

    /**
     * D�finit une variable template de type bloc et lui affecte un tableau de valeurs
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
                if (!$html) $value = str::nl2br(str::htmlentities($value));
                if ($clean) $value = $this->clean_var($value);
                $this->blockvars[$blockname][$k]['{'.$key.'}'] = $value;
            }
        }
    }

    /**
     * D�finit une variable template de type bloc et lui affecte un tableau de valeurs
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

                if (!$row['html']) $row['value'] = str::nl2br(str::htmlentities($row['value']));
                if ($row['clean']) $row['value'] = $this->clean_var($row['value']);

                $this->blockvars[$blockname][$k]['{'.$key.'}'] = $row['value'];

            }
        }

    }

    /**
     * Parse le contenu du mod�le et remplace les variables du template par leurs valeurs
     */

    public function parse()
    {
        // Traitement du fichier manifest pour int�grer la description des images
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

            // le contenu XML sans les blocks (mais avec des nouvelles variables � la place)
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
                        //output::print_r($varparser->get_xml());
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
            exit("Rien � parser - v�rifiez que les fichiers content.xml et styles.xml sont correctement form�s\n");
        }
    }

    /**
     * Affichage du contenu des variables depuis un appel de type echo ou print
     *
     * @return string contenu du template
     */
    public function __tostring()
    {
        return output::print_r($this->vars, true).output::print_r($this->blockvars, true);
    }

    /**
     * Enregistre le document ODF g�n�r�
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
