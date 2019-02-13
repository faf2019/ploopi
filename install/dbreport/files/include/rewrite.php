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

if ($booRewriteRuleFound = (strpos($arrParsedURI['path'], '/wsdbr') === 0))
{
    self::$script = 'webservice';

    $_REQUEST['module'] = $_GET['module'] = 'dbreport';

    if ($booRewriteRuleFound = (preg_match('/wsdbr\/([^\/]*)/', $arrParsedURI['path'], $arrMatches) == 1))
    {
        if (sizeof($arrMatches) == 2)
        {
            $_REQUEST['dbreport_ws_id'] = $_GET['dbreport_ws_id'] = $arrMatches[1];
        }
    }
}
?>
