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
 */

class xmlrss
{
    private $error;
    
    private $content;
    
    private $feed;
    
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

    public function xmlrss($url, $moduleid = -1, $srcenc = null, $tgtenc = null)
    {
        $this->error = false;
        $this->content = '';
        $this->feed = array(
            'title' => '',
            'subtitle' => '',
            'link' => '',
            'updated' => '',
            'author' => '',
            'entries' => array()
        );

        if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

        if ($url == '')
        {
            $this->error = true;
            $this->error_msg = "Erreur url vide";
        }

        if (!$this->error)
        {
            ploopi_unset_error_handler();
            $request = new HTTP_Request($url, array('timeout' => 500));
            ploopi_set_error_handler();
            
            if (_PLOOPI_INTERNETPROXY_HOST != '')
            {
                $request->setProxy( 
                    _PLOOPI_INTERNETPROXY_HOST,
                    _PLOOPI_INTERNETPROXY_PORT,
                    _PLOOPI_INTERNETPROXY_USER,
                    _PLOOPI_INTERNETPROXY_PASS
                );
            }

            ploopi_unset_error_handler();
            $res = !PEAR::isError($objReq = $request->sendRequest());
            ploopi_set_error_handler();
            
            if ($res)
            {
                if ($request->getResponseCode() != '200' && $request->getResponseCode() != '')
                {
                    $this->error = true;
                    $this->error_msg = sprintf("Erreur HTTP %s",$request->getResponseCode());
                }
                else
                {
                    $this->content = $request->getResponseBody();
                }
            }
            else
            {
                $this->error = true;
                $this->error_msg = sprintf("Erreur HTTP %s : %s",$objReq->getCode(),$objReq->getMessage());
            }
        }
    }
    
    /**
     * Parse le flux.
     *
     * @link http://pear.php.net/package/XML_Feed_Parser
     */

    public function parse()
    {
        require_once './lib/simplepie/simplepie.inc.php';
        
        
        $feed = new SimplePie();
        $feed->set_raw_data($this->content);
        //$feed->handle_content_type();
        $feed ->set_output_encoding('ISO-8859-1');
        $feed->init();

        if (!$this->error)
        {
            $this->feed['title'] = $feed->get_title();
            $this->feed['subtitle'] = $feed->get_description();
            $this->feed['link'] = $feed->get_link();
            $this->feed['updated'] = 0;
            $this->feed['author'] = $feed->get_author();
            
            foreach($feed->get_items() as $key => $item)
            {
                $category = $item->get_category();
                $author = $item->get_author();
                
                $this->feed['entries'][] = array(   
                    'id' => $item->get_id(true),
                    'title' => $item->get_title(),
                    'subtitle' => $item->get_description(),
                    'link' => $item->get_link(),
                    'category' => $category ? $category->get_label() : '',
                    'published' => $item->get_date('U'),
                    'author' => $author ? $author->get_name() : '',
                    'content' => $item->get_content()
                );
            }
        }
    }
    
    /**
     * Retourne l'erreur  
     */
    public function geterror() { return $this->error; }

    /**
     * Retourne le flux pars�  
     */
    public function getfeed() { return $this->feed; }

    /**
     * Retourne le contenu brut du flux  
     */
    public function getcontent() { return $this->content; }
    
}
?>
