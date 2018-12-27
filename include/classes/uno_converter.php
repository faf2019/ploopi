<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Gestion de la conversion d'un document via UNOCONVERTER
 *
 * @package ploopi
 * @subpackage module
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class uno_converter
{
    /**
     * Chemin vers le binaire UNOCONV
     */

    private $_binary;

    /**
     * Constructeur de la classe.
     *
     * @param string $binary Chemin du binaire UNOCONV
     */

    function __construct($binary = null)
    {
        if (empty($binary)) {
            $objParam = new param_default();
            if ($objParam->open(_PLOOPI_MODULE_SYSTEM, 'system_unoconv')) {
                $this->_binary = $objParam->fields['value'];
            }
        }
        else $this->_binary = $binary;
    }

    /**
     * Convertit un document dans un format qu'Open Office peut lire (ODT, ODS, DOC, XLS, etc...) dans un format qu'il peut écrire (PDF, ODT, ODS, DOC, XLS, SXW, RTF, HTML, etc...)
     *
     * @param string $srcFile fichier source
     * @param string $dstFile fichier destination
     * @param string $outputFormat format du document destination (pdf, odt, doc, ...)
     * @return boolean true en cas de réalisation
     */

    function convert($srcFile, $dstFile, $outputType)
    {
        if (file_exists($this->_binary)) {
            exec("/usr/bin/unoconv -v --stdout -f $outputType {$srcFile} > {$dstFile}");
            if (file_exists($dstFile)) return true;
        }

        return false;
    }
}
