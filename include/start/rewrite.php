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

$booRewriteRuleFound = false;

// robots.txt
if ($booRewriteRuleFound = (substr($_SERVER['REQUEST_URI'], -10 ) == 'robots.txt'))
{
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_robots';
}

// Gestion du rewriting inverse des modules
clearstatcache();
$rscFolder = @opendir(realpath('./modules/'));
while ($strFolderName = @readdir($rscFolder))
{
    if (!$booRewriteRuleFound && file_exists($strModuleRewrite = "./modules/{$strFolderName}/include/rewrite.php")) include_once $strModuleRewrite;
}
closedir($rscFolder);

?>