<?php
/*
    Copyright (c) 2007-2016 Ovensia
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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Gestion du buffer
 *
 * @package ploopi
 * @subpackage buffer
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

abstract class buffer
{
    /**
     * Gère la sortie du buffer principal.
     * Met à jour le rendu final en mettant à jour les variables d'éxection.
     * Compresse éventuellement le contenu.
     * Ecrit dans le log.
     *
     * @param string $buffer contenu du buffer de sortie
     * @return string buffer modifié
     *
     * @see _PLOOPI_USE_OUTPUT_COMPRESSION
     * @see ob_start
     */

    public static function callback($buffer)
    {

        global $ploopi_timer;
        global $db;

        if (!strlen(trim($buffer))) return '';

        // On essaye de récupérer le content-type du contenu du buffer
        $content_type = 'text/html';
        $headers = headers_list();
        $booDownloadFile = false;

        foreach($headers as $property)
        {
            $matches = array();

            if (preg_match('/Content-type:((.*);(.*)|(.*))/i', $property, $matches))
            {
                $content_type = (empty($matches[2])) ? $matches[1] : $matches[2];
                $content_type = strtolower(trim($content_type));
            }

            if (preg_match('/X-Ploopi:(.*)/i', $property, $matches))
            {
                if (isset($matches[1]))
                {
                    switch(trim($matches[1]))
                    {
                        case 'Download': $booDownloadFile = true; break;
                    }
                }
            }
        }

        $ploopi_stats = array();

        if (isset($buffer)) $ploopi_stats['pagesize'] = strlen($buffer);
        else $ploopi_stats['pagesize'] = 0;

        if (isset($db))
        {
            $ploopi_stats['numqueries'] = $db->get_num_queries();
            $ploopi_stats['sql_exectime'] = round($db->get_exectime_queries()*1000,1);
        }
        else
        {
            $ploopi_stats['numqueries'] = 0;
            $ploopi_stats['sql_exectime'] = 0;
        }

        if (isset($ploopi_timer))
        {
            $ploopi_stats['total_exectime'] = round($ploopi_timer->getexectime()*1000,1);
            $ploopi_stats['sql_ratiotime'] = round(($ploopi_stats['sql_exectime']*100)/$ploopi_stats['total_exectime'] ,0);
            $ploopi_stats['php_ratiotime'] = 100 - $ploopi_stats['sql_ratiotime'];
        }
        else
        {
            $ploopi_stats['total_exectime'] = 0;
            $ploopi_stats['sql_ratiotime'] = 0;
            $ploopi_stats['php_ratiotime'] = 0;
        }

        $ploopi_stats['php_memory'] = memory_get_peak_usage();

        $ploopi_stats['sessionsize'] = isset($_SESSION) ? strlen(session_encode()) : 0;

        if (defined('_PLOOPI_ACTIVELOG') && _PLOOPI_ACTIVELOG && isset($db))
        {
            $log = new log();

            $log->fields['request_method'] = $_SERVER['REQUEST_METHOD'];
            $log->fields['query_string'] = $_SERVER['QUERY_STRING'];
            $log->fields['remote_addr'] = (empty($_SESSION['ploopi']['remote_ip'])) ? '' : implode(',', $_SESSION['ploopi']['remote_ip']);
            $log->fields['remote_port'] = $_SERVER['REMOTE_PORT'];
            $log->fields['script_filename'] = $_SERVER['SCRIPT_FILENAME'];
            $log->fields['script_name'] = $_SERVER['SCRIPT_NAME'];
            $log->fields['request_uri'] = $_SERVER['REQUEST_URI'];
            $log->fields['ploopi_moduleid'] = (empty($_SESSION['ploopi']['moduleid'])) ? 0 : $_SESSION['ploopi']['moduleid'];
            $log->fields['ploopi_userid'] = (empty($_SESSION['ploopi']['userid'])) ? 0 : $_SESSION['ploopi']['userid'];
            $log->fields['ploopi_workspaceid'] = (empty($_SESSION['ploopi']['workspaceid'])) ? 0 : $_SESSION['ploopi']['workspaceid'];;
            $log->fields['ts'] = date::createtimestamp();

            $log->fields['browser'] = isset($_SESSION['ploopi']['remote_browser']) ? $_SESSION['ploopi']['remote_browser'] : '';
            $log->fields['system'] = isset($_SESSION['ploopi']['remote_system']) ? $_SESSION['ploopi']['remote_system'] : '';

            $log->fields['total_exec_time'] = $ploopi_stats['total_exectime'];
            $log->fields['sql_exec_time'] = $ploopi_stats['sql_exectime'];
            $log->fields['sql_percent_time'] = $ploopi_stats['sql_ratiotime'];
            $log->fields['php_percent_time'] = $ploopi_stats['php_ratiotime'];
            $log->fields['numqueries'] = $ploopi_stats['numqueries'];
            $log->fields['page_size'] = $ploopi_stats['pagesize'];
            $log->save();
        }

        if ($content_type == 'text/html' && !$booDownloadFile)
        {
            $array_tags = array(
                '<PLOOPI_PAGE_SIZE>',
                '<PLOOPI_EXEC_TIME>',
                '<PLOOPI_SQL_TIME>',
                '<PLOOPI_PHP_P100>',
                '<PLOOPI_SQL_P100>',
                '<PLOOPI_NUMQUERIES>',
                '<PLOOPI_SESSION_SIZE>',
                '<PLOOPI_PHP_MEMORY>'
            );

            $array_values = array(
                sprintf("%.02f",$ploopi_stats['pagesize']/1024),
                $ploopi_stats['total_exectime'],
                $ploopi_stats['sql_exectime'],
                $ploopi_stats['php_ratiotime'],
                $ploopi_stats['sql_ratiotime'],
                $ploopi_stats['numqueries'],
                sprintf("%.02f",$ploopi_stats['sessionsize']/1024),
                sprintf("%.02f",$ploopi_stats['php_memory']/1024)
           );

            $buffer = trim(str_replace($array_tags, $array_values, $buffer));
        }

        if (!$booDownloadFile && _PLOOPI_USE_OUTPUT_COMPRESSION && self::accepts_gzip() && ($content_type == 'text/plain' || $content_type == 'text/html' || $content_type == 'text/xml' || $content_type == 'text/x-json'))
        {
            header("Content-Encoding: gzip");
            $buffer = gzencode($buffer);
        }
        else
        {
            // Attention, Content-Encoding: none ET Content-Type: text/html ne font pas bon ménage !
            // => Problème avec le validateur W3C : Line 1, Column 0: end of document in prolog
            if ($content_type != 'text/html') header("Content-Encoding: none");
        }

        header('Content-Length: '.mb_strlen($buffer, '8bit'));

        return $buffer;
    }


    /**
     * Vide les buffers de sortie ouverts en préservant le buffer principal
     * @copyright Ovensia
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @see ovensia\ploopi\buffer::callback
     */

    public static function clean($booDeleteAllBuffers = false)
    {
        if ($booDeleteAllBuffers) while (ob_get_level()) @ob_end_clean();
        else
        {
            while (ob_get_level() > 1) @ob_end_clean();
            if (ob_get_level() == 1) ob_clean();
        }
    }



    /**
     * Détecte si le navigateur supporte la compression gzip
     *
     * @return boolean true si le navigateur supporte la compression gzip
     *
     * @copyright tellinya.com
     * @license GNU General Public License (GPL)
     * @author Stéphane Escaich
     *
     * @link http://www.tellinya.com/read/2007/09/09/106.html
     */

    public static function accepts_gzip()
    {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']) && in_array('gzip', explode(',', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_ENCODING']))));
    }
}
