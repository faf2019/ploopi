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

/**
 * Fonction de manipulation de cha�nes.
 * Conversion, d�coupage, r��criture, etc..
 * 
 * @package ploopi
 * @subpackage string
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Ins�re un retour � la ligne HTML � chaque nouvelle ligne, am�liore le comportement de la fonction php nl2br()
 *
 * @param string $str
 * @return string cha�ne modifi�e
 */

function ploopi_nl2br($str)
{
   return preg_replace("/\r\n|\n|\r/", "<br />", $str);
}

/**
 * Coupe une cha�ne � la longueur d�sir�e et ajoute '...'
 *
 * @param string $str cha�ne � couper
 * @param string $len longueur maximale de la cha�ne
 * @param string $mode d�termine ou couper la cha�ne : 'left', 'middle' 
 * @return string cha�ne modifi�e
 */

function ploopi_strcut($str,$len = 30, $mode = 'left')
{
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

/**
 * Convertit tous les caract�res accentu�s en caract�res non accentu�s en pr�servant les majuscules/minuscules
 *
 * @param string $str cha�ne � convertir
 * @return string cha�ne modifi�e
 */
 
function ploopi_convertaccents($str)
{
    return(
        strtr(
            $str, 
            "���������������������������������������������������������������",
            "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyys"
        )
    );
}



/**
 * R��crit une URL selon les r�gles de r��criture utilis�e par ploopi
 *
 * @param string $url URL � r��crire
 * @param string $title titre � ins�rer dans la nouvelle URL
 * @return string URL r��crite
 * 
 * @see _PLOOPI_FRONTOFFICE_REWRITERULE
 * @see ploopi_convertaccents
 */
 
function ploopi_urlrewrite($url, $title = '', $keep_extension = false)
{
    if (defined('_PLOOPI_FRONTOFFICE_REWRITERULE') && _PLOOPI_FRONTOFFICE_REWRITERULE)
    {
        $ext = 'html';
        
        if ($keep_extension)
        {
            $ext = ploopi_file_getextension($title);
            $title = basename($title, ".{$ext}");
        }
                
        $title = urlencode(ploopi_convertaccents(strtolower(strtr(trim($title), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '_')))));
        
        $patterns = array('/__+/', '/_$/');
        $replacements = array('_', '');
    
        $title = preg_replace($patterns, $replacements, $title);
    
        $patterns = array();
        $patterns[0] = '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)/';
        $patterns[1] = '/index.php\?headingid=([0-9]*)/';
        $patterns[2] = '/index.php\?articleid=([0-9]*)/';
        $patterns[3] = '/index-quick.php\?ploopi_op=doc_file_download&docfile_md5id=([a-z0-9]{32})/';
        $patterns[4] = '/index.php\?ploopi_op=webedit_unsubscribe&subscription_email=([a-z0-9]{32})/';
        $patterns[5] = '/index.php\?query_tag=([a-zA-Z0-9]*)/';

        $replacements = array();
        $replacements[0] = $title.'-h$1a$2.'.$ext;
        $replacements[1] = $title.'-h$1.'.$ext;
        $replacements[2] = $title.'-a$1.'.$ext;
        $replacements[3] = $title.'-d$1.'.$ext;
        $replacements[4] = 'unsubscribe-$1.'.$ext;
        $replacements[5] = 'tag-$1.'.$ext;
        
        return preg_replace($patterns, $replacements, $url);
    }
    else return $url;
}



/**
 * Encode les caract�res sp�ciaux d'une cha�ne pour qu'elle puisse �tre int�gr�e dans un document XML
 *
 * @param string $str cha�ne brute
 * @return string cha�ne encod�e
 */
 
function ploopi_xmlentities($str) 
{ 
    for($i=128; $i<256; $i++) $asc2uni[chr($i)] = "&#x".dechex($i).";"; 
    $str = str_replace(array("&", ">", "<", "\"", "'", "\r"), array("&amp;", "&gt;", "&lt;", "&quot;", "&apos;", ""), $str);
    return strtr($str, $asc2uni);
}

/**
 * Rend les liens d'un texte cliquables
 *
 * @param string $text le texte � traiter
 * @return string le texte modifi�
 */

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

/**
 * Encode et affiche une variable au format JSON et modifie les ent�tes du document. Compatible x-json
 *
 * @param mixed $var variable � encoder
 * @param boolean $utf8encode true si le contenu de la variable doit �tre converti en UTF8 (true par d�faut)
 * @param boolean $use_xjson true si X-json peut �tre utilis�
 * 
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 * 
 * @see iconv
 */

function ploopi_print_json($var, $utf8encode = true, $use_xjson = true)
{
    
    if ($utf8encode) 
    {
        $var = 
            ploopi_array_map(
                create_function(
                    '$v',
                    'return iconv(\'ISO-8859-15\', \'UTF-8\', $v);'
                ), 
                $var
            );
    }
        
    $json = json_encode($var);
    header("Content-Type: text/x-json"); 
    if ($use_xjson === false || strlen($json) > 2048) echo $json;
    else header("X-Json: {$json}");
}

/**
 * Nettoie le code html et le rend valide XHTML
 *
 * @param string $string code HTML � valider
 * @return string code HTML valid�
 * 
 * @link http://htmlpurifier.org/
 */

function ploopi_htmlpurifier($string)
{
    $cache_path = _PLOOPI_PATHDATA._PLOOPI_SEP.'cache';
    if (!file_exists($cache_path)) ploopi_makedir($cache_path);
    
    require_once './lib/htmlpurifier/HTMLPurifier.auto.php';
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache', 'SerializerPath', $cache_path);
    $config->set('Core', 'Encoding', 'ISO-8859-15');
    $config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');
    //$config->set('HTML', 'AllowedModules', 'Target');
    
    $purifier = new HTMLPurifier($config);

    return $purifier->purify($string); 
}
?>
