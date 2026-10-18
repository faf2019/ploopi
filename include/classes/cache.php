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
     * Dossier du cache (avec / final)
     *
     * @var string
     */
    private $_strCacheDir;

    /**
     * Durée de vie du cache (en secondes)
     *
     * @var int
     */
    private $_intLifetime;


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
     * @param string $cachedir dossier du cache
     */

    public function __construct($id, $lifetime = _PLOOPI_CACHE_DEFAULT_LIFETIME, $cachedir = _PLOOPI_PATHCACHE)
    {
        if (substr($cachedir, -1) != '/') $cachedir .= '/';

        $this->_strCacheId = $id;
        $this->_strCacheDir = $cachedir;
        $this->_intLifetime = (int) $lifetime;
    }

    /**
     * Fichier d'un groupe (préfixe propre à ploopi : les fichiers de l'ancien Cache_Lite, « cache_… », sont ignorés)
     *
     * @param string $strGroup groupe
     * @return string début du chemin des fichiers du groupe
     */
    private function _groupprefix($strGroup) { return $this->_strCacheDir.'ploopi_'.md5($strGroup).'_'; }

    /**
     * Chemin du fichier de cache
     *
     * @return string chemin
     */
    private function _file() { return $this->_groupprefix($this->_strCacheGroup).md5($this->_strCacheId); }

    /**
     * Lit le contenu du cache s'il est encore valide
     *
     * @param boolean $booForceCaching true pour ignorer le cache existant (régénération)
     * @return string|false contenu ou false
     */
    private function _read($booForceCaching)
    {
        if ($booForceCaching) return false;
        $strFile = $this->_file();
        $intTime = @filemtime($strFile);
        if ($intTime === false || $intTime <= time() - $this->_intLifetime) return false;
        return @file_get_contents($strFile);
    }

    /**
     * Retourne la date de dernière modification du cache
     *
     * @return int timestamp unix (0 si absent)
     */
    public function get_lastmodified()
    {
        if (self::$_booActivated) return (int) @filemtime($this->_file());

        return 0;
    }

    /**
     * Démarre une mise en cache : affiche le contenu en cache s'il existe, sinon capture la sortie jusqu'à end()
     *
     * @param boolean $booForceCaching true si la mise en cache est forcée
     * @return boolean true si le contenu en cache a été affiché, false sinon (cache désactivé ou vide)
     */

    public function start($booForceCaching = false)
    {
        if (self::$_booActivated)
        {
            $strContent = $this->_read($booForceCaching);

            if ($strContent !== false)
            {
                echo $strContent;
                self::$_intRead++;
                return true;
            }

            ob_start();
            return false;
        }
        else return false; // no cache
    }

    /**
     * Termine la mise en cache : enregistre et affiche la sortie capturée depuis start()
     */

    public function end()
    {
        if (self::$_booActivated)
        {
            $strContent = ob_get_clean();
            $this->save($strContent);
            echo $strContent;
        }
    }

    /**
     * Enregistre un contenu en cache (écriture atomique)
     *
     * @param string $strContent contenu
     * @return boolean true si le contenu est enregistré
     */
    public function save($strContent)
    {
        if (!self::$_booActivated) return false;

        $strFile = $this->_file();
        $strTmp = $strFile.'.'.uniqid('', true);
        if (@file_put_contents($strTmp, $strContent) === false) return false;
        if (!@rename($strTmp, $strFile)) { @unlink($strTmp); return false; }

        self::$_intWritten++;
        return true;
    }

    /**
     * Vide le cache d'un groupe
     */
    public function clean()
    {
        if (self::$_booActivated)
        {
            foreach(glob($this->_groupprefix($this->_strCacheGroup).'*') ?: array() as $strFile) @unlink($strFile);
        }
    }

    /**
     * Lit une variable en cache
     */
    public function get_var($booForceCaching = false)
    {
        if (self::$_booActivated)
        {
            $strContent = $this->_read($booForceCaching);
            if ($strContent === false) return false;

            // N'en laisser sortir aucun objet
            $mixVar = unserialize($strContent, array('allowed_classes' => false));

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
        if (self::$_booActivated) $this->save(serialize($var));
    }
}
