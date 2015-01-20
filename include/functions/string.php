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

function ploopi_strcut($str, $len = 30, $mode = 'left')
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
 * Encode les contenus d'url selon la RFC 3986
 * @see http://www.php.net/manual/fr/function.urlencode.php#97969
 */

function ploopi_rawurlencode($str)
{
    return str_replace(
        array('%2F'),
        array('/'),
        rawurlencode($str)
    );
}

/**
 * R��crit une cha�ne destin�e � �tre transform�e en URL
 *
 * @param string $str cha�ne � transformer
 * @return string cha�ne transform�e
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_string2url($str)
{
    $str = urlencode(strtolower(ploopi_convertaccents(strtr(trim($str), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '-')))));
    return preg_replace(array('/--+/', '/-$/'), array('-', ''), $str);
}

/**
 * R��crit une URL selon les r�gles de r��criture fournies en param�tre
 *
 * @param string $strUrl URL � r��crire
 * @param array $arrRules r�gles de r��criture au format array('patterns' => array(), 'replacements' => array())
 * @param string $strTitle titre � ins�rer dans la nouvelle URL
 * @param array $arrFolders tableau contenu les intitul� de dossiers � ajouter � l'url
 * @param bool $booKeepExt true si l'url doit contenir l'extension de fichier �ventuellement utilis�e dans le param�tre $strTitle (par d�faut : false)
 * @return string URL r��crite
 *
 * @see _PLOOPI_FRONTOFFICE_REWRITERULE
 * @see ploopi_convertaccents
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_urlrewrite($strUrl, $arrRules, $strTitle = '', $arrFolders = null, $booKeepExt = false)
{
    if (defined('_PLOOPI_FRONTOFFICE_REWRITERULE') && _PLOOPI_FRONTOFFICE_REWRITERULE && !empty($arrRules['patterns']) && !empty($arrRules['replacements']))
    {
        $strExt = 'html';

        if ($booKeepExt)
        {
            $strExt = ploopi_file_getextension($strTitle);
            $strTitle = basename($strTitle, ".{$strExt}");
        }

        $strTitle = ploopi_string2url($strTitle);

        // Construction des dossiers si n�cessaire
        if (!empty($arrFolders) && is_array($arrFolders))
        {
            foreach($arrFolders as &$strFolder) $strFolder = ploopi_string2url($strFolder);
            $strFolders = implode('/', $arrFolders).'/';
        }
        else $strFolders = '';

//        ploopi_print_r($arrRules);

        return str_replace(
            array('<TITLE>', '<FOLDERS>', '<EXT>'),
            array($strTitle, $strFolders, $strExt),
            preg_replace($arrRules['patterns'], $arrRules['replacements'], $strUrl)
        );
    }
    else return $strUrl;
}


/**
 * Equivalent de strtr en version multibyte (UTF-8) car la version "mbstring" de strtr n'existe pas.
 * Remplace des caract�res dans une cha�ne.
 *
 * @param string $str la cha�ne � traiter
 * @param mixed $from caract�res de d�part sous forme d'une chaine ou d'un tableau associatif (si to est null)
 * @param string $to caract�res de remplacement ou null
 * @return string cha�ne modifi�e
 *
 * @see strtr
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_strtr($str, $from, $to = null)
{
    if (!isset($to) && is_array($from))
    {
        return str_replace(array_keys($from), $from, $str);
    }
    else return str_replace(ploopi_str_split($from), ploopi_str_split($to), $str);

}

/**
 * Equivalent de str_split en version multibyte (UTF-8) car la version "mbstring" de str_split n'existe pas.
 * Convertit une cha�ne de caract�res en tableau.
 *
 * @param string $str la cha�ne à convertir
 * @return array tableau de caract�res
 *
 * @see strtr
 */

function ploopi_str_split($str)
{
    $strlen = mb_strlen($str);

    while ($strlen)
    {
        $array[] = mb_substr($str, 0, 1, 'UTF-8');
        $str = mb_substr($str, 1, $strlen, 'UTF-8');
        $strlen = mb_strlen($str);
    }

    return $array;
}

/**
 * Encode les caract�res sp�ciaux d'une cha�ne pour qu'elle puisse �tre int�gr�e dans un document XML
 *
 * @param string $str cha�ne brute
 * @param boolean $utf8 true si la cha�ne � encoder est en UTF-8
 * @param boolean $extended true permet d'encoder les caract�res ascii sp�ciaux de 128 � 255
 * @return string cha�ne encod�e
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_xmlentities($str, $utf8 = false, $extended = true)
{
    if ($extended) for($i=128; $i<256; $i++) $asc2uni[$utf8 ? utf8_encode(chr($i)) : chr($i)] = "&#x".dechex($i).";";

    $str = str_replace(array("&", ">", "<", "\"", "'", "\r"), array("&amp;", "&gt;", "&lt;", "&quot;", "&apos;", ""), $str);

    return $extended ? $utf8 ? ploopi_strtr($str, $asc2uni) : strtr($str, $asc2uni) : $str;
}

/**
 * Convertit tous les caract�res �ligibles en entit�s HTML (via htmlentities) mais en ISO-8859-1 par d�faut et supprime les balises HTML.
 *
 * @param string $str cha�ne brute
 * @param int masque qui d�termine la fa�on dans les guillemets sont g�r�s
 * @param string $encoding d�finit l'encodage utilis� durant la conversion
 * @return string cha�ne convertie
 */

function ploopi_htmlentities($str, $flags = null, $encoding = 'ISO-8859-1')
{
    if (is_null($flags)) $flags = version_compare(phpversion(), '5.4', '<') ? ENT_COMPAT : ENT_COMPAT | ENT_HTML401;

    return htmlentities(strip_tags($str), $flags, $encoding);
}


/**
 *  Convertit toutes les entit�s HTML en caract�res normaux (via html_entity_decode) mais en ISO-8859-1 par d�faut.
 *
 * @param string $str cha�ne brute
 * @param int masque qui d�termine la fa�on dans les guillemets sont g�r�s
 * @param string $encoding d�finit l'encodage utilis� durant la conversion
 * @return string cha�ne convertie
 */

function ploopi_html_entity_decode($str, $flags = null, $encoding = 'ISO-8859-1')
{
    if (is_null($flags)) $flags = version_compare(phpversion(), '5.4', '<') ? ENT_COMPAT : ENT_COMPAT | ENT_HTML401;

    return html_entity_decode($str, $flags, $encoding);
}

/**
 * Encode une cha�ne en UTF8
 *
 * @param string $str cha�ne ISO-8859-15
 * @return string cha�ne encod�e UTF8
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_utf8encode($str)
{
    return iconv('ISO-8859-15', 'UTF-8', $str);
}

/**
 * D�code les caract�res iso non repr�sentables
 *
 * @param string cha�ne iso � d�coder $str
 * @param boolean true si les caract�res doivent �tre adapt�s
 * @return cha�ne d�cod�e
 */
function ploopi_iso8859_clean($str, $booTranslit = true)
{
    $str = strtr($str, array(
       "\x80" => "&#8364;", /* EURO SIGN */
       "\x82" => "&#8218;", /* SINGLE LOW-9 QUOTATION MARK */
       "\x83" => "&#402;",  /* LATIN SMALL LETTER F WITH HOOK */
       "\x84" => "&#8222;", /* DOUBLE LOW-9 QUOTATION MARK */
       "\x85" => "&#8230;", /* HORIZONTAL ELLIPSIS */
       "\x86" => "&#8224;", /* DAGGER */
       "\x87" => "&#8225;", /* DOUBLE DAGGER */
       "\x88" => "&#710;",  /* MODIFIER LETTER CIRCUMFLEX ACCENT */
       "\x89" => "&#8240;", /* PER MILLE SIGN */
       "\x8a" => "&#352;",  /* LATIN CAPITAL LETTER S WITH CARON */
       "\x8b" => "&#8249;", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
       "\x8c" => "&#338;",  /* LATIN CAPITAL LIGATURE OE */
       "\x8e" => "&#381;",  /* LATIN CAPITAL LETTER Z WITH CARON */
       "\x91" => "&#8216;", /* LEFT SINGLE QUOTATION MARK */
       "\x92" => "&#8217;", /* RIGHT SINGLE QUOTATION MARK */
       "\x93" => "&#8220;", /* LEFT DOUBLE QUOTATION MARK */
       "\x94" => "&#8221;", /* RIGHT DOUBLE QUOTATION MARK */
       "\x95" => "&#8226;", /* BULLET */
       "\x96" => "&#8211;", /* EN DASH */
       "\x97" => "&#8212;", /* EM DASH */

       "\x98" => "&#732;",  /* SMALL TILDE */
       "\x99" => "&#8482;", /* TRADE MARK SIGN */
       "\x9a" => "&#353;",  /* LATIN SMALL LETTER S WITH CARON */
       "\x9b" => "&#8250;", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
       "\x9c" => "&#339;",  /* LATIN SMALL LIGATURE OE */
       "\x9e" => "&#382;",  /* LATIN SMALL LETTER Z WITH CARON */
       "\x9f" => "&#376;"   /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
    ));

    if ($booTranslit)
        $str = strtr($str, array(
           '&#8364;' => 'Euro', /* EURO SIGN */
           '&#8218;' => ',',    /* SINGLE LOW-9 QUOTATION MARK */
           '&#402;' => 'f',     /* LATIN SMALL LETTER F WITH HOOK */
           '&#8222;' => ',,',   /* DOUBLE LOW-9 QUOTATION MARK */
           '&#8230;' => '...',  /* HORIZONTAL ELLIPSIS */
           '&#8224;' => '+',    /* DAGGER */
           '&#8225;' => '++',   /* DOUBLE DAGGER */
           '&#710;' => '^',     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
           '&#8240;' => '0/00', /* PER MILLE SIGN */
           '&#352;' => 'S',     /* LATIN CAPITAL LETTER S WITH CARON */
           '&#8249;' => '<',    /* SINGLE LEFT-POINTING ANGLE QUOTATION */
           '&#338;' => 'OE',    /* LATIN CAPITAL LIGATURE OE */
           '&#381;' => 'Z',     /* LATIN CAPITAL LETTER Z WITH CARON */
           '&#8216;' => "'",    /* LEFT SINGLE QUOTATION MARK */
           '&#8217;' => "'",    /* RIGHT SINGLE QUOTATION MARK */
           '&#8220;' => '"',    /* LEFT DOUBLE QUOTATION MARK */
           '&#8221;' => '"',    /* RIGHT DOUBLE QUOTATION MARK */
           '&#8226;' => '*',    /* BULLET */
           '&#8211;' => '-',    /* EN DASH */
           '&#8212;' => '--',   /* EM DASH */
           '&#732;' => '~',     /* SMALL TILDE */
           '&#8482;' => '(TM)', /* TRADE MARK SIGN */
           '&#353;' => 's',     /* LATIN SMALL LETTER S WITH CARON */
           '&#8250;' => '>',    /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
           '&#339;' => 'oe',    /* LATIN SMALL LIGATURE OE */
           '&#382;' => 'z',     /* LATIN SMALL LETTER Z WITH CARON */
           '&#376;' => 'Y'      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
        ));

    return $str;
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
 */

function ploopi_print_json($var, $utf8encode = true, $use_xjson = true)
{

    if ($utf8encode) $var = ploopi_array_map('ploopi_utf8encode', $var);

    $json = json_encode($var);
    header("Content-Type: text/x-json");
    if ($use_xjson === false || strlen($json) > 1024) echo $json;
    else header("X-Json: {$json}");
}

/**
 * Nettoie le code html et le rend valide XHTML
 *
 * @param string $strContent code HTML � valider
 * @param boolean $booTrusted true si le code fourni est "s�r", dans ce cas le filtrage est moins s�v�re (par d�faut : false)
 * @return string code HTML valid�
 *
 * @link http://htmlpurifier.org/
 *
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

function ploopi_htmlpurifier($strContent, $booTrusted = false)
{
    $strCachePath = _PLOOPI_PATHDATA._PLOOPI_SEP.'cache';
    if (!file_exists($strCachePath)) ploopi_makedir($strCachePath);

    require_once './lib/htmlpurifier/HTMLPurifier.auto.php';
    $objConfig = HTMLPurifier_Config::createDefault();
    $objConfig->set('Cache.SerializerPath', $strCachePath);
    // $objConfig->set('Core.Encoding', 'ISO-8859-15');
    $objConfig->set('Core.EscapeNonASCIICharacters', true);
    $objConfig->set('HTML.Doctype', 'XHTML 1.0 Strict');

    if ($booTrusted)
    {
        $objConfig->set('HTML.Trusted', true);
        $objConfig->set('Attr.EnableID', true);
        $objConfig->set('HTML.SafeEmbed', true);
        $objConfig->set('HTML.SafeObject', true);
    }

    $objPurifier = new HTMLPurifier($objConfig);

    $subst = mb_substitute_character();
    mb_substitute_character('');
    $res = mb_convert_encoding($objPurifier->purify(mb_convert_encoding($strContent, 'UTF-8', 'ISO-8859-15')), 'ISO-8859-15', 'UTF-8');
    mb_substitute_character($subst);

    return $res;
}


/**
 * Convertit une couleur HTML/Hex en un tableau de composantes RVB (entier)
 *
 * @param string $strHex couleur au format HTML/Hex
 * @return array tableau contenant les composantes RVB (entier)
 */

function ploopi_color_hex2rgb($strHex)
{
    return array_map('hexdec', str_split(str_replace('#', '', $strHex) ,2));
}

/**
 * V�rifie qu'une url est valide
 *
 * @param string $url url � tester
 * @return boolean true si l'url est valide
 */

function ploopi_is_url($url)
{
    $urlregex = "/^(https?)\:\/\/";

    // USER AND PASS (optional)
    $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

    // HOSTNAME OR IP
    $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*";

    // PORT (optional)
    $urlregex .= "(\:[0-9]{2,5})?";

    // PATH  (optional)
    $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";

    // GET Query (optional)
    $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@\/&%=+\$_.-]*)?";

    // ANCHOR (optional)
    $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$/i";

    return preg_match($urlregex, $url) ? true : false;
}

/**
 * Nettoie une chaine pour en faire un nom de fichier valide. Ne conserve que les caracteres : [a-zA-Z0-9_-]
 *
 * @param string $str chaine � nettoyer
 * @return string la chaine nettoy�e
 */

function ploopi_clean_filename($str)
{
    $str = ploopi_convertaccents($str);
    $arrSearch = array ('@[ */]@i','@[^a-zA-Z0-9_-]@');
    $arrReplace = array ('_','');
    return preg_replace($arrSearch, $arrReplace, $str);
}
