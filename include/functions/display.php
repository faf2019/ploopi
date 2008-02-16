<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
?>
<?
/**
* ! description !
*
* @param bool $hand
* @return string HTML style
*
* @version 2.09
* @since 0.1
*
* @category HTML styles management
*/
function ploopi_switchstyles($hand = TRUE)
{
    $opacity = 80;
    if (strstr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
    {
        if ($hand) return 'Style="filter:alpha(opacity:'.$opacity.');"  OnMouseOut="javascript:ploopi_switchstyle(this, '.$opacity.');this.style.cursor=\'default\'" OnMouseOver="javascript:ploopi_switchstyle(this,100);this.style.cursor=\'pointer\'"';
        else return 'Style="filter:alpha(opacity:'.$opacity.');"  OnMouseOut="javascript:ploopi_switchstyle(this, '.$opacity.');" OnMouseOver="javascript:ploopi_switchstyle(this,100);"';
    }
    else
    {
        if ($hand) return 'Style="-moz-opacity:'.($opacity/100).';opacity:'.($opacity/100).';"  OnMouseOut="javascript:ploopi_switchstyle(this, '.$opacity.');this.style.cursor=\'default\'" OnMouseOver="javascript:ploopi_switchstyle(this,100);this.style.cursor=\'pointer\'"';
        else return 'Style="-moz-opacity:'.($opacity/100).';opacity:'.($opacity/100).';"  OnMouseOut="javascript:ploopi_switchstyle(this, '.$opacity.');" OnMouseOver="javascript:ploopi_switchstyle(this,100);"';
    }
}


function ploopi_showpopup($msg, $width = '')
{
    $msg = ploopi_nl2br(str_replace("'","\'",$msg));
    //return "onmouseover=\"javascript:this.style.cursor='help';ploopi_showpopup('{$msg}','{$width}', event);\" onmousemove=\"javascript:ploopi_showpopup('{$msg}', '{$width}', event);\" onmouseout=\"javascript:ploopi_hidepopup('{$msg}',event);\" onmouseup=\"javascript:ploopi_showpopup('{$msg}', '{$width}', event, 'click');\"";
    return "onmouseover=\"javascript:this.style.cursor='help';ploopi_showpopup('{$msg}','{$width}', event);\" onmouseout=\"javascript:ploopi_hidepopup();\" onmouseup=\"javascript:ploopi_showpopup('{$msg}', '{$width}', event, 'click');\"";
}

/**
* ! description !
*
* @return string HTML style
*
* @version 2.09
* @since 0.1
*
* @category HTML styles management
*/
function ploopi_switchfocus()
{
    $opacity = 80;
    return 'Style="filter:alpha(opacity:'.$opacity.')"  OnBlur="javascript:ploopi_switchstyle(this,'.$opacity.');" OnFocus="javascript:ploopi_switchstyle(this,100);"';
}

// FCK richtext editor
function ploopi_fckeditor($field,$value,$w,$h)
{
    include_once('./FCKeditor/editor/fckeditor.php') ;

    $oFCKeditor = new FCKeditor("fck_".$field) ;

    // default path for FCKEditor
    $oFCKeditor->BasePath   = "./FCKeditor/";

    // default value
    $oFCKeditor->Value= $value;

    // width & height
    if ($w!="" && $w!="*") $oFCKeditor->Width=$w;
    if ($h!="" && $h!="*") $oFCKeditor->Height=$h;

    // language definition
    $oFCKeditor->Config["AutoDetectLanguage"] = false ;
    $oFCKeditor->Config["DefaultLanguage"]    = "fr" ;

    // render
    $oFCKeditor->Create() ;
}

?>
