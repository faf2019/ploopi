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
if ($booRewriteRuleFound = ($arrParsedURI['path'] == '/sitemap.xml'))
{
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_sitemap';
}

// url webedit (article/rubrique + parametres)
elseif ($booRewriteRuleFound = (preg_match('/articles\/(.*)-(h([0-9]*)){0,1}(a([0-9]*)){0,1}(r([0-9]*)){0,1}\.[a-z0-9]*\?{0,1}(.*)/', $arrParsedURI['path'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[3]) && is_numeric($arrMatches[3])) $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[3];
    if (!empty($arrMatches[5]) && is_numeric($arrMatches[5])) $_REQUEST['articleid'] = $_GET['articleid'] = $arrMatches[5];
    // reponse pour les commentaires
    if (!empty($arrMatches[7]) && is_numeric($arrMatches[7])) $_REQUEST['comment_return'] = $_GET['comment_return'] = $arrMatches[7];
}

// tags
elseif ($booRewriteRuleFound = (preg_match('/tags\/(.*)\.[a-zA-Z0-9]*(.*)/', $arrParsedURI['path'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[1])) $_REQUEST['query_tag'] = $_GET['query_tag'] = $arrMatches[1]; 
}

// blog
elseif ($booRewriteRuleFound = (preg_match('/blog\/(.*)-(h([0-9]*)){0,1}(p([0-9]{1,4})){0,1}(y([0-9]{4})){0,1}(ym([0-9]{6})){0,1}(d([0-9]{2})){0,1}\.[a-z0-9]*\?{0,1}(.*)/', $arrParsedURI['path'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[3]) && is_numeric($arrMatches[3])) $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[3];
    if (!empty($arrMatches[5]) && is_numeric($arrMatches[5])) $_REQUEST['numpage'] = $_GET['numpage'] = $arrMatches[5];
    if (!empty($arrMatches[7]) && is_numeric($arrMatches[7])) $_REQUEST['year'] = $_GET['year'] = $arrMatches[7];
    if (!empty($arrMatches[9]) && is_numeric($arrMatches[9])) $_REQUEST['yearmonth'] = $_GET['yearmonth'] = $arrMatches[9];
    if ((!empty($arrMatches[9]) && is_numeric($arrMatches[9])) && (!empty($arrMatches[11]) && is_numeric($arrMatches[11]))) $_REQUEST['day'] = $_GET['day'] = $arrMatches[11];
}

// atom rubrique
elseif ($booRewriteRuleFound = (preg_match('/atom\/(.*)-h([0-9]*)\.xml/', $arrParsedURI['path'], $arrMatches) == 1))
{
    if (!empty($arrMatches[2])) 
    {
        $ploopi_access_script = 'index-light';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
        $_REQUEST['format'] = $_GET['format'] = 'atom';
        $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[2];
    }    
}

// atom racine
elseif ($booRewriteRuleFound = (preg_match('/atom\/(.*)\.xml/', $arrParsedURI['path'], $arrMatches) == 1))
{
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
    $_REQUEST['format'] = $_GET['format'] = 'atom';
}

// rss rubrique
elseif ($booRewriteRuleFound = (preg_match('/rss\/(.*)-h([0-9]*)\.xml/', $arrParsedURI['path'], $arrMatches) == 1))
{
    if (!empty($arrMatches[2])) 
    {
        $ploopi_access_script = 'index-light';
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
        $_REQUEST['format'] = $_GET['format'] = 'rss';
        $_REQUEST['headingid'] = $_GET['headingid'] = $arrMatches[2];
    }    
}

// rss racine
elseif ($booRewriteRuleFound = (preg_match('/rss\/(.*)\.xml/', $arrParsedURI['path'], $arrMatches) == 1))
{
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_backend';
    $_REQUEST['format'] = $_GET['format'] = 'rss';
}

// Désinscription
elseif ($booRewriteRuleFound = (preg_match('/unsubscribe\/([a-z0-9]{32})\/index\.[a-zA-Z0-9](.*)/', $arrParsedURI['path'], $arrMatches) == 1)) 
{
    if (!empty($arrMatches[1])) 
    {
        $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'webedit_unsubscribe';
        $_REQUEST['subscription_email'] = $_GET['subscription_email'] = $arrMatches[1];
    }
}?>