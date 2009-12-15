<?php
/*
    Copyright (c) 2009 HeXad
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
 * Gère le rewriting inverse des URL du module GALLERY
 *
 * @package webedit
 * @subpackage rewrite
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

if(preg_match('/gallery\/dewslider-g([0-9]*)-sb([0,1]{1})-st([0,1]{1})-rs([0,1]{1})-t([0-9]{1,2})-at([b,t]{1})-ab([b,t]{1})-tr([f,s,p,o,w,b]{1})-s([0-9]{1,2})\.xml/', $arrParsedURI['path'], $arrMatches) == 1) 
{
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_get_dewsliderXML';
    $_REQUEST['id_gallery'] = $_GET['id_gallery'] = $arrMatches[1];
    $_REQUEST['showbuttons'] = $_GET['showbuttons'] = ($arrMatches[2]) ? 'yes' : 'no';
    $_REQUEST['showtitles'] = $_GET['showtitles'] = ($arrMatches[3]) ? 'yes' : 'no';
    $_REQUEST['randomstart'] = $_GET['randomstart'] = ($arrMatches[4]) ? 'yes' : 'no';
    $_REQUEST['timer'] = $_GET['timer'] = intval($arrMatches[5]);
    $_REQUEST['aligntitles'] = $_GET['aligntitles'] = ($arrMatches[6] == 'b') ? 'bottom' : 'top';
    $_REQUEST['alignbuttons'] = $_GET['alignbuttons'] = ($arrMatches[7] == 'b') ? 'bottom' : 'top';
    switch ($arrMatches[8])
    {
        case 'f':
            $_REQUEST['transition'] = $_GET['transition'] = 'fade';
            break;
        case 's':
            $_REQUEST['transition'] = $_GET['transition'] = 'slide';
            break;
        case 'p':
            $_REQUEST['transition'] = $_GET['transition'] = 'push';
            break;
        case 'o':
            $_REQUEST['transition'] = $_GET['transition'] = 'pop';
            break;
        case 'w':
            $_REQUEST['transition'] = $_GET['transition'] = 'warp';
            break;
        default:
        case 'b':
            $_REQUEST['transition'] = $_GET['transition'] = 'blur';
            break;
    }
    $_REQUEST['speed'] = $_GET['speed'] = intval($arrMatches[9]);
    $booRewriteRuleFound = true;    
}
?>