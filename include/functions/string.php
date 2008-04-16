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

##############################################################################
#
# string functions
#
##############################################################################

function ploopi_nl2br($text)
{
   return preg_replace("/\r\n|\n|\r/", "<br />", $text);
}

/**
* ! description !
*
* @param string string to cut
* @param int length of string to keep
* @return string param'd string first n chars
*
* @version 2.09
* @since 0.1
*
* @category string manipulations
*/
function ploopi_strcut($str,$len = 30, $mode = 'left')
{
    // mode = 'left' / 'middle'

    if (strlen($str)>$len)
    {
        switch($mode)
        {
            case 'left':
                $str = substr($str,0,$len).'...';
            break;

            case 'middle':
                $str = substr($str,0,($len-3)/2).'...'.substr($str,-($len-3)/2,($len-3)/2);
            break;
        }

    }
    return($str);
}

// convert all accentuated caracters in string
function ploopi_convertaccents($content)
{
    return (strtr($content, "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿÞ",
                  "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyys"));
}

// rewrite url for being used with apache's mod_rewrite
function ploopi_urlrewrite($url, $title = '')
{
    $title_replacement = str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '_');
    $title = urlencode(ploopi_convertaccents(strtolower(strtr(trim($title), _PLOOPI_INDEXATION_WORDSEPARATORS, $title_replacement))));
    
    $patterns = array('/__+/', '/_$/');
    $replacements = array('_', '');

    $title = preg_replace($patterns, $replacements, $title);
    
    $patterns = array();
    $patterns[0] = '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)/';
    $patterns[1] = '/index.php\?headingid=([0-9]*)/';
    $patterns[2] = '/index.php\?articleid=([0-9]*)/';
    
    $replacements = array();
    $replacements[0] = $title.'-h$1a$2.html';
    $replacements[1] = $title.'-h$1.html';
    $replacements[2] = $title.'-a$1.html';
    
    return preg_replace($patterns, $replacements, $url);
}


// crypt + base64(safe)
function ploopi_urlencode($url)
{
    if (defined('_PLOOPI_URL_ENCODE') && _PLOOPI_URL_ENCODE)
    {
        require_once './include/classes/class_cipher.php';
        if (strstr($url,'?')) list($script, $params) = explode('?', $url, 2);
        else {$script = $url; $params = '';}
        $cipher = new ploopi_cipher();
        return("{$script}?ploopi_url=".urlencode($cipher->crypt($params)));
    }
    else return($url);
}

function ploopi_base64_encode($string)
{
    // base64 safe encoding
    // thx to massimo dot scamarcia at gmail dot com
    // Php version of perl's MIME::Base64::URLSafe, that provides an url-safe base64 string encoding/decoding (compatible with python base64's urlsafe methods)
    $string = base64_encode($string);
    $string = str_replace(array('+','/','='), array('-','_',''), $string);
    return($string);
}

function ploopi_base64_decode($string)
{
    $string = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($string) % 4;
    if ($mod4) $string .= substr('====', $mod4);
    return base64_decode($string);
}

// encode data before being inserted into XML document
function ploopi_xmlencode($str)
{
    return str_replace(array("&", ">", "<", "\""), array("&amp;", "&gt;", "&lt;", "&quot;"), $str);
}

// marten_berglund at hotmail dot com (php.net)
function ploopi_make_links($text)
{
    $text = preg_replace(
                array(
                        '!(^|([^\'"]\s*))([hf][tps]{2,4}:\/\/[^\s<>"\'()]{4,})!mi',
                        '!<a href="([^"]+)[\.:,\]]">!',
                        '!([\.:,\]])</a>!',
                        '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/iex'
                    ),
                array(
                        '$2<a href="$3">$3</a>',
                        '<a href="$1">',
                        '</a>$1',
                       "stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
                    ),
                $text);

    return $text;
}


function ploopi_print_json($str)
{
    $json = json_encode($str);
    if (strlen($json)>4096) { header("Content-Type: test/x-json"); echo $json; }
    else header("x-json: {$json}");
}
?>
