<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Gestion du rewriting inverse des URL du module dbreport
 *
 * @package dbreport
 * @subpackage rewrite
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

if ($booRewriteRuleFound = (strpos($arrParsedURI['path'], '/wiki') === 0))
{

    if ($booRewriteRuleFound = (preg_match('/wiki\/(.*)-(h([0-9]*)){0,1}(a([0-9]*)){0,1}\/(.*)\.html/', $arrParsedURI['path'], $arrMatches) == 1))
    {
        $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[3];
        $_REQUEST['articleid'] = $_GET['articleid'] = $arrMatches[5];
        $_REQUEST['wikipageid'] = $_GET['wikipageid'] = urldecode($arrMatches[6]);
    }

}
?>
