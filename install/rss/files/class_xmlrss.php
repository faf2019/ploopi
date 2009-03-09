<?php
/*
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
 * Lecture des flux RSS
 *
 * @package rss
 * @subpackage xml
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Inclusion des d�pendances PEAR
 */

require_once 'HTTP/Request.php';
require_once 'XML/Feed/Parser.php';

/**
 * Permet de lire le contenu d'un flux RSS � travers un proxy, puis d'en extraire le contenu.
 *
 * @package rss
 * @subpackage xml
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 *
 * @see _PLOOPI_INTERNETPROXY_HOST
 * @see _PLOOPI_INTERNETPROXY_PORT
 * @see _PLOOPI_INTERNETPROXY_USER
 * @see _PLOOPI_INTERNETPROXY_PASS
 *
 * @link http://pear.php.net/package/HTTP_Request
 * @link http://pear.php.net/package/XML_Feed_Parser
 */

class xmlrss
{
    /**
     * Constructeur de la classe.
     * R�cup�re le contenu du flux (via proxy si n�cessaire)
     *
     * @param string $url URL du flux � parser
     * @param int $moduleid identifiant du module
     * @param string $srcenc codage source (flux)
     * @param string $tgtenc codage destination (ce qu'on veut)
     * @return xmlrss
     */

    function xmlrss($url, $moduleid = -1, $srcenc = null, $tgtenc = null)
    {

        $this->error = false;
        $this->charset = 'UTF-8';

        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

        if ($url == '')
        {
            $this->error = true;
            $this->error_msg = sprintf("Erreur url vide");
        }

        if (!$this->error)
        {
            $request = new HTTP_Request($url, array('timeout' => 500));

            if (_PLOOPI_INTERNETPROXY_HOST != '')
            {
                $request->setProxy( _PLOOPI_INTERNETPROXY_HOST,
                                    _PLOOPI_INTERNETPROXY_PORT,
                                    _PLOOPI_INTERNETPROXY_USER,
                                    _PLOOPI_INTERNETPROXY_PASS
                                    );
            }

            $res = $request->sendRequest();
            $this->header = $request->getResponseHeader();

            // D�tection de l'encoding dans le header HTTP
            foreach(split(';',$this->header['content-type']) as $sp)
            {
                $detail = split('=',$sp);
                if (!empty($detail[0]) && !empty($detail[1]) && strtolower(trim($detail[0])) == 'charset') $this->charset = strtoupper($detail[1]);
            }

            if ($res == 1)
            {
                if ($request->getResponseCode() != '200' && $request->getResponseCode() != '')
                {
                    $this->error = true;
                    $this->error_msg = sprintf("Erreur HTTP %s",$request->getResponseCode());
                }
                else
                {
                    $this->content = $request->getResponseBody();

                    // D�tection de l'encoding dans le source XML
                    if (preg_match('/<?phpxml.*encoding=[\'"](.*?)[\'"].*?>/m', $this->content, $m)) $this->charset = strtoupper($m[1]);
                }
            }
            else
            {
                $this->error = true;
                $this->error_msg = sprintf("Erreur HTTP %s : %s",$res->getCode(),$res->getMessage());
            }
        }
    }

    /**
     * Convertit une cha�ne UTF8 en ISO-8859-1//TRANSLIT.
     * M�thode notamment utile pour traiter le contenu des flux RSS.
     * Une conversion "classique" UTF8 => ISO-8859-1 ne suffit pas.
     *
     * @param string $str cha�ne UTF8 � convertir
     * @return string cha�ne ISO-8859-1
     */

    function _convertstr($str)
    {
        $str = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $str);
        return($str);
    }

    /**
     * Parse le flux.
     *
     * @link http://pear.php.net/package/XML_Feed_Parser
     */

    function parse()
    {
        $this->feed = array ('title' => '', 'entries' => array());

        try
        {
            $xmlfeed = new XML_Feed_Parser($this->content);
        }
        catch (XML_Feed_Parser_Exception $e)
        {
            $this->error_msg = 'Flux invalide: '.$e->getMessage();
            $this->error = true;
        }

        if (!$this->error)
        {
            $this->feed['title'] = $this->_convertstr($xmlfeed->title);
            $this->feed['subtitle'] = $this->_convertstr($xmlfeed->subtitle);
            $this->feed['link'] = $xmlfeed->link;
            $this->feed['updated'] = $xmlfeed->updated;
            $this->feed['author'] = $this->_convertstr($xmlfeed->author);

            foreach ($xmlfeed as $entry)
            {
                $this->feed['entries'][] = array(   'id' => (empty($entry->id)) ? $entry->link : $entry->id,
                                                    'title' => $this->_convertstr($entry->title),
                                                    'subtitle' => $this->_convertstr($entry->subtitle),
                                                    'link' => $entry->link,
                                                    'category' => $this->_convertstr($entry->category),
                                                    'published' => $entry->published,
                                                    'author' => $this->_convertstr($entry->author),
                                                    'content' => $this->_convertstr($entry->content)
                                                );
            }
        }
    }

}
?>
