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
 * Gestion de la mise en cache
 *
 * @package ploopi
 * @subpackage cache
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe de gestion du cache
 *
 * @package ploopi
 * @subpackage cache
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class cache
{
    /**
     * Cache activ� ?
     */
    static private $_booActivated;

    /**
     * Nombre d'�criture dans la page
     */
    static private $_intWritten;

    /**
     * Nombre de lecture dans la page
     */
    static private $_intRead;

    /**
     * Id du cache
     */
    private $_strCacheId;

    /**
     * Id du cache
     */
    private $_strCacheGroup = 'default';


    /**
     * Object Cache_Lite_Output
     */
    private $_objCache = null;


    /**
     * Constructeur statique
     */
    public static function init()
    {
        self::$_booActivated = _PLOOPI_USE_CACHE;
        self::$_intWritten = 0;
        self::$_intRead = 0;
    }

    /**
     * Retourne le nombre de lecture dans la page
     */
    public static function getread() { return self::$_intRead; }

    /**
     * Retourne le nombre d'�criture dans la page
     */
    public static function getwritten() { return self::$_intWritten; }

    /**
     * Retourne true si le cache est activ�
     */
    public static function getactivated() { return self::$_booActivated; }

    /*
     * Modifie le groupe de cache
     */
    public function set_groupe($strGroup = 'default') { if(is_string($strGroup)) $this->_strCacheGroup = $strGroup; }

    /**
     * Constructeur de la classe
     *
     * @param string $id identifiant du cache
     * @param int $lifetime dur�e de vie du cache (en secondes)
     * @return ploopi_cache
     */

    public function __construct($id, $lifetime = _PLOOPI_CACHE_DEFAULT_LIFETIME, $cachedir = _PLOOPI_PATHCACHE)
    {
        if (self::$_booActivated)
        {
            if (substr($cachedir, -1) != '/') $cachedir .= '/';

            $this->_strCacheId = $id;
            $this->_objCache = new \Cache_Lite_Output(array( 'cacheDir' => $cachedir, 'lifeTime' => $lifetime));
        }
    }

    /**
     * Retourne la date de derni�re modification du cache
     *
     * @return int date/heure (format ?)
     */
    public function get_lastmodified()
    {
        if (self::$_booActivated)
        {
            $this->_objCache->_setFileName($this->_strCacheId, $this->_strCacheGroup);
            if (file_exists($this->_file)) return($this->_objCache->lastModified());
            else return 0;
        }

        return 0;
    }

    /**
     * D�marre une mise en cache
     *
     * @param boolean $force_caching true si la mise en cache est forc�e
     * @return mixed contenu du cache ou false si le cache est d�sactiv� ou vide
     */

    public function start($booForceCaching = false)
    {
        if (self::$_booActivated)
        {
            if ($booForceCaching) $this->_objCache->setOption('lifeTime', 0);
            $strContent = $this->_objCache->start($this->_strCacheId, $this->_strCacheGroup);

            if ($strContent) self::$_intRead++;

            return $strContent;
        }
        else return false; // no cache
    }

    /**
     * Termine la mise en cache
     */

    public function end()
    {
        if (self::$_booActivated)
        {
            $this->_objCache->end();
            self::$_intWritten++;
        }
    }

    /*
     * Vide le cache d'un groupe
     */
    public function clean()
    {
        if (self::$_booActivated)
        {
            $this->_objCache->clean($this->_strCacheGroup);
        }
    }

    /**
     * Lit une variable en cache
     */
    public function get_var($booForceCaching = false)
    {
        if (self::$_booActivated)
        {
            if ($booForceCaching) $this->_objCache->setOption('lifeTime', 0);
            $mixVar = unserialize($this->_objCache->get($this->_strCacheId,$this->_strCacheGroup));

            if ($mixVar) self::$_intRead++;

            return $mixVar;
        }
        else return false;

    }

    /**
     * Enregistre une variable en cache
     */
    public function save_var($var)
    {
        if (self::$_booActivated)
        {
            $this->_objCache->save(serialize($var),$this->_strCacheGroup);
            self::$_intWritten++;
        }
    }


}
