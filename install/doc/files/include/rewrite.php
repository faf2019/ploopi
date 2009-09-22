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

// documents
if ($booRewriteRuleFound = (preg_match('/documents\/([a-z0-9]{32})\/(.*)\.[a-zA-Z0-9]*(.*)/', $_SERVER['REQUEST_URI'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[2])) 
    {
        $ploopi_access_script = 'quick';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'doc_file_download';
        $_REQUEST['docfile_md5id'] = $_GET['docfile_md5id'] = $arrMatches[1];
    }
}
if ($booRewriteRuleFound = (preg_match('/media\/([a-z0-9]{32})\/(.*)\.[a-zA-Z0-9]*(.*)/', $_SERVER['REQUEST_URI'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[2])) 
    {
        $ploopi_access_script = 'quick';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'doc_file_view';
        $_REQUEST['docfile_md5id'] = $_GET['docfile_md5id'] = $arrMatches[1];
    }
}
?>