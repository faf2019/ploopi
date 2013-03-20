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
 * Gère le rewriting inverse des URL
 *
 * @package ploopi
 * @subpackage rewrite
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$ploopi_access_script = 'index'; // index/admin/light/quick/webservice/backend

if (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == '200')
{
    $booRewriteRuleFound = false;

    // Attention ! $_SERVER['REQUEST_URI'] peut contenir une url complète avec le nom de domaine
    $arrParsedURI = @parse_url($_SERVER['REQUEST_URI']);
    $strRequestURI = $arrParsedURI['path'].(empty($arrParsedURI['query']) ? '' : "?{$arrParsedURI['query']}");

    if (_PLOOPI_SELFPATH == '' || strpos($strRequestURI, _PLOOPI_SELFPATH) === 0) define('_PLOOPI_REQUEST_URI', substr($strRequestURI, strlen(_PLOOPI_SELFPATH) - strlen($strRequestURI)));
    else define('_PLOOPI_REQUEST_URI', $strRequestURI);

    $arrParsedURI = @parse_url(_PLOOPI_REQUEST_URI);

    if (!empty($arrParsedURI['path']))
    {
        // robots.txt
        if ($booRewriteRuleFound = ($arrParsedURI['path'] == '/robots.txt'))
        {
            $ploopi_access_script = 'quick';
            $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_robots';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/admin.php'))
        {
            $ploopi_access_script = 'admin';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/admin-light.php'))
        {
            $ploopi_access_script = 'admin-light';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/index-light.php'))
        {
            $ploopi_access_script = 'index-light';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/webservice.php'))
        {
            $ploopi_access_script = 'webservice';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/backend.php'))
        {
            $ploopi_access_script = 'backend';
        }
        elseif ($booRewriteRuleFound = ($arrParsedURI['path'] == '/index-quick.php'))
        {
            $ploopi_access_script = 'quick';
        }
    }
    else $arrParsedURI['path'] = '';

    if (!$booRewriteRuleFound)
    {
        // Gestion du rewriting inverse des modules
        clearstatcache();
        $rscFolder = @opendir(realpath('./modules/'));
        while ($strFolderName = @readdir($rscFolder))
        {
            if (!$booRewriteRuleFound && $strFolderName != '.' && $strFolderName != '..' && file_exists($strModuleRewrite = "./modules/{$strFolderName}/include/rewrite.php")) include_once $strModuleRewrite;
        }
        closedir($rscFolder);
    }

    if (!$booRewriteRuleFound)
    {
        ploopi_h404();
        ploopi_die('Page non trouvée');
    }
}
