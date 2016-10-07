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

namespace ploopi;

use ploopi;

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
