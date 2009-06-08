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
 * Gère le rewriting inverse des URL du module WEBEDIT
 *
 * @package webedit
 * @subpackage rewrite
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

// sitemap
if ($booRewriteRuleFound = (substr($_SERVER['REQUEST_URI'], -11 ) == 'sitemap.xml'))
{
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_sitemap';
}

// url webedit (article/rubrique + parametres)
elseif ($booRewriteRuleFound = (preg_match('/articles\/(.*)-(h([0-9]*)){0,1}(a([0-9]*)){0,1}\.[a-z0-9]*\?{0,1}(.*)/', $_SERVER['REQUEST_URI'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[3]) && is_numeric($arrMatches[3])) $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[3];
    if (!empty($arrMatches[5]) && is_numeric($arrMatches[5])) $_REQUEST['articleid'] = $_GET['articleid'] = $arrMatches[5];
}

// tags
elseif ($booRewriteRuleFound = (preg_match('/tags\/(.*)\.[a-zA-Z0-9]*(.*)/', $_SERVER['REQUEST_URI'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[1])) $_REQUEST['query_tag'] = $_GET['query_tag'] = $arrMatches[1]; 
}

// atom rubrique
elseif ($booRewriteRuleFound = (preg_match('/atom\/(.*)-h([0-9]*)\.xml/', $_SERVER['REQUEST_URI'], $arrMatches) == 1))
{
    if (!empty($arrMatches[2])) 
    {
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
        $_REQUEST['format'] = $_GET['format'] = 'atom';
        $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[2];
    }    
}

// atom racine
elseif ($booRewriteRuleFound = (preg_match('/atom\/(.*)\.xml/', $_SERVER['REQUEST_URI'], $arrMatches) == 1))
{
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
    $_REQUEST['format'] = $_GET['format'] = 'atom';
}

// rss rubrique
elseif ($booRewriteRuleFound = (preg_match('/rss\/(.*)-h([0-9]*)\.xml/', $_SERVER['REQUEST_URI'], $arrMatches) == 1))
{
    if (!empty($arrMatches[2])) 
    {
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
        $_REQUEST['format'] = $_GET['format'] = 'rss';
        $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[2];
    }    
}

// rss racine
elseif ($booRewriteRuleFound = (preg_match('/rss\/(.*)\.xml/', $_SERVER['REQUEST_URI'], $arrMatches) == 1))
{
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
    $_REQUEST['format'] = $_GET['format'] = 'rss';
}

// Désinscription
elseif ($booRewriteRuleFound = (preg_match('/unsubscribe\/([a-z0-9]{32})\/index\.[a-zA-Z0-9](.*)/', $_SERVER['REQUEST_URI'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[1])) 
    {
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_unsubscribe';
        $_REQUEST['subscription_email'] = $_GET['subscription_email'] = $arrMatches[1];
    }
}?>