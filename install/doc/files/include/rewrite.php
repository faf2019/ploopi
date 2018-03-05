<?php
/*
    Copyright (c) 2007-2009 Ovensia
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
 * Gère le rewriting inverse des URL du module DOC
 *
 * @package doc
 * @subpackage rewrite
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

// webservice "wsdoc"
if ($booRewriteRuleFound = (strpos($arrParsedURI['path'], '/wsdoc') === 0))
{
    // Choix du point d'entrée dans Ploopi
    self::$script = 'webservice';

    // On indique le module utilisé
    $_REQUEST['module'] = $_GET['module'] = 'doc';

    // On force Ploopi à ne pas rediriger la requête entrante lors d'une identification
    $_REQUEST['noredir'] = $_GET['noredir'] = '1';

    // Lecture de l'action à effectuer dans l'url (routage)
    if (preg_match('/wsdoc\/([^\/]*)/', $arrParsedURI['path'], $arrMatches) == 1 && sizeof($arrMatches) == 2)
    {
        $_REQUEST['op'] = $_GET['op'] = $arrMatches[1];
        return;
    }
}

// documents
if (preg_match('/^\/documents\/([a-z0-9]{32})\/(.*)\.[a-zA-Z0-9]*(.*)/', $arrParsedURI['path'], $arrMatches) == 1)
{
    if (!empty($arrMatches[2]))
    {
        self::$script = 'quick';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'doc_file_download';
        $_REQUEST['docfile_md5id'] = $_GET['docfile_md5id'] = $arrMatches[1];
        if (!empty($_SESSION['ploopi']['tokens'])) {
            end($_SESSION['ploopi']['tokens']);
            $_REQUEST['ploopi_token'] = key($_SESSION['ploopi']['tokens']);
        }
        $booRewriteRuleFound = true;
    }
}

elseif (preg_match('/^\/media\/([a-z0-9]{32})\/(.*)\.[a-zA-Z0-9]*(.*)/', $arrParsedURI['path'], $arrMatches) == 1)
{
    if (!empty($arrMatches[2]))
    {
        self::$script = 'quick';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'doc_file_view';
        $_REQUEST['docfile_md5id'] = $_GET['docfile_md5id'] = $arrMatches[1];
        if (!empty($_SESSION['ploopi']['tokens'])) {
            end($_SESSION['ploopi']['tokens']);
            $_REQUEST['ploopi_token'] = key($_SESSION['ploopi']['tokens']);
        }
        $booRewriteRuleFound = true;
    }
}
//flux RSS/Atom
elseif (preg_match('/^\/doc\/(rss|atom)\/(.*)-m([0-9]*)f([0-9]*)\.xml/', $arrParsedURI['path'], $arrMatches) == 1)
{
    if (!empty($arrMatches[1]) && !empty($arrMatches[3]))
    {
        self::$script = 'backend';
        $_REQUEST['format'] = $_GET['format'] = $arrMatches[1];
        $_REQUEST['ploopi_moduleid'] = $_GET['ploopi_moduleid'] = $arrMatches[3];
        $_REQUEST['id_folder'] = $_GET['id_folder'] = $arrMatches[4];
        $booRewriteRuleFound = true;
    }
}
?>
