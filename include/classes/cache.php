<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Gestion de la mise en cache
 *
 * @package ploopi
 * @subpackage cache
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

@include_once 'Cache/Lite/Output.php';

/**
 * Classe de gestion du cache
 *
 * @package ploopi
 * @subpackage cache
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopi_cache extends Cache_Lite_Output
{
    /**
     * Cache activé ?
     */
    static private $activated;
    
    /**
     * Nombre d'écriture dans la page
     */
    static private $written;
    
    /**
     * Nombre de lecture dans la page
     */
    static private $read;
    
    /**
     * Id du cache
     */    
    private $cache_id;
    
    
    /**
     * Constructeur statique
     */
    public static function init()
    {
        self::$activated = _PLOOPI_USE_CACHE;
        self::$written = 0;
        self::$read = 0;         
    }
    
    /**
     * Retourne le nombre de lecture dans la page
     */
    public static function getread() { return self::$read; }
    
    /**
     * Retourne le nombre d'écriture dans la page
     */
    public static function getwritten() { return self::$written; }
    
    /**
     * Retourne true si le cache est activé
     */
    public static function getactivated() { return self::$activated; }
    
    /**
     * Constructeur de la classe
     *
     * @param string $id identifiant du cache
     * @param int $lifetime durée de vie du cache (en secondes)
     * @return ploopi_cache
     */

    public function ploopi_cache($id, $lifetime = _PLOOPI_CACHE_DEFAULT_LIFETIME, $cachedir = _PLOOPI_PATHCACHE)
    {
        if (self::$activated)
        {
            if (substr($cachedir, -1) != '/') $cachedir .= '/';
            
            $this->cache_id = $id;
            $this->Cache_Lite_Output(array( 'cacheDir' => $cachedir, 'lifeTime' => $lifetime));
        }
    }

    /**
     * Retourne la date de dernière modification du cache
     *
     * @return int date/heure (format ?)
     */
    public function get_lastmodified()
    {
        if (self::$activated)
        {
            $this->_setFileName($this->cache_id, 'default');
            if (file_exists($this->_file)) return($this->lastModified());
            else return 0;
        }

        return 0;
    }

    /**
     * Démarre une mise en cache
     *
     * @param boolean $force_caching true si la mise en cache est forcée
     * @return mixed contenu du cache ou false si le cache est désactivé ou vide
     */

    public function start($force_caching = false)
    {
        if (self::$activated)
        {
            if ($force_caching) $this->setOption('lifeTime', 0);
            $cache_content = parent::start($this->cache_id);

            if ($cache_content) self::$read++;

            return $cache_content;
        }
        else return false; // no cache
    }

    /**
     * Termine la mise en cache
     */

    public function end() 
    { 
        if (self::$activated) 
        {
            parent::end(); 
            self::$written++;
        }
    }
    
    /**
     * Lit une variable en cache
     */
    public function get_var($force_caching = false)
    { 
        if (self::$activated)
        {
            if ($force_caching) $this->setOption('lifeTime', 0);
            $var = unserialize(parent::get($this->cache_id));

            if ($var) self::$read++;
            
            return $var;
        }
        else return false;
         
    }
    
    /**
     * Enregistre une variable en cache 
     */
    public function save_var($var) 
    { 
        if (self::$activated)
        {
            parent::save(serialize($var));
            self::$written++;
        }
    }
    
    
}

?>
