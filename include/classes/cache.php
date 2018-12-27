<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion du cache
 *
 * @package ploopi
 * @subpackage cache
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class cache
{
    /**
     * Cache activé ?
     *
     * @var boolean
     */
    static private $_booActivated;

    /**
     * Nombre d'écriture dans la page
     *
     * @var integer
     */
    static private $_intWritten;

    /**
     * Nombre de lecture dans la page
     *
     * @var integer
     */
    static private $_intRead;

    /**
     * Id du cache
     *
     * @var string
     */
    private $_strCacheId;

    /**
     * Id du groupe
     *
     * @var string
     */
    private $_strCacheGroup = 'default';


    /**
     * Object \Cache_Lite_Output
     *
     * @var Cache_Lite_Output
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
     * Retourne le nombre d'écriture dans la page
     */
    public static function getwritten() { return self::$_intWritten; }

    /**
     * Retourne true si le cache est activé
     */
    public static function getactivated() { return self::$_booActivated; }

    /**
     * Modifie le groupe de cache
     *
     * @param string $strGroup nom du groupe
     */
    public function set_groupe($strGroup = 'default') { if(is_string($strGroup)) $this->_strCacheGroup = $strGroup; }

    /**
     * Constructeur de la classe
     *
     * @param string $id identifiant du cache
     * @param int $lifetime durée de vie du cache (en secondes)
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
     * Retourne la date de dernière modification du cache
     *
     * @return int date/heure (format ?)
     */
    public function get_lastmodified()
    {
        if (self::$_booActivated)
        {
            $this->_objCache->_setFileName($this->_strCacheId, $this->_strCacheGroup);
            if (file_exists($this->_objCache->_file)) return($this->_objCache->lastModified());
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

    /**
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
