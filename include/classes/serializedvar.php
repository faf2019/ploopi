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
 * Gestion des variables de session sérialisées
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */


class serializedvar
{

    /**
     * Identifiant de la variable
     *
     * @var string
     */

    private $strId = '';

    /**
     * Retourne le chemin de stockage des variables
     *
     * @return string chemin de stockage des variables
     */
    public static function get_path() { return session::get_path()._PLOOPI_SEP.'sv'; }

    /**
     * Génère un ID
     *
     * @return string ID
     */
    public static function generateId() { return uniqid(mt_rand(), true); }

    /**
     * Constructeur
     *
     * @param string $strId Identifiant de la variable
     */
    public function __construct($strId) { $this->strId = md5($strId); }

    /**
     * Lecture de la variable
     *
     * @return mixed contenu de la variable
     */
    public function read()
    {
        if (session::get_usedb())
        {
            return (session::get_db()->query("SELECT `data` FROM `ploopi_serializedvar` WHERE `id` = '".session::get_db()->addslashes($this->strId)."' AND `id_session` = '".session::get_db()->addslashes(session_id())."'") && $arrRecord = session::get_db()->fetchrow()) ? unserialize(gzuncompress($arrRecord['data'])) : false;
        }
        elseif (session::get_usemc())
        {
            $data = session::get_mc()->get('var_'.session_id().'_'.$this->strId);
            if ($data === false) return '';
            return $data;
        }
        else
        {
            return file_exists(self::get_path()._PLOOPI_SEP.$this->strId) ? unserialize(gzuncompress(file_get_contents(self::get_path()._PLOOPI_SEP.$this->strId))) : false;
        }
    }

    /**
     * Enregistrement de la variable
     *
     * @param mixed $data pointeur vers la variable
     */
    public function save(&$data)
    {
        if (session::get_usedb())
        {
            session::get_db()->query("REPLACE INTO `ploopi_serializedvar` VALUES ('".session::get_db()->addslashes($this->strId)."', '".session::get_db()->addslashes(session_id())."', '".session::get_db()->addslashes(gzcompress(serialize($data)))."')");
        }
        elseif (session::get_usemc())
        {
            session::get_mc()->set('var_'.session_id().'_'.$this->strId, $data);
        }
        else
        {
            fs::makedir(self::get_path());
            $resHandle = fopen(self::get_path()._PLOOPI_SEP.$this->strId, 'wb');
            fwrite($resHandle, gzcompress(serialize($data)));
            fclose($resHandle);
        }
        return true;
    }

    /**
     * Suppression de la variable
     *
     * @return boolean true
     */
    public function destroy()
    {
        if (session::get_usedb())
        {
            session::get_db()->query("DELETE FROM `ploopi_serializedvar` WHERE `id` = '".session::get_db()->addslashes($this->strId)."' AND `id_session` = '".session::get_db()->addslashes(session_id())."'");
        }
        elseif (session::get_usemc())
        {
            session::get_mc()->delete('var_'.session_id().'_'.$this->strId);
        }
        else
        {
            if (file_exists(self::get_path()._PLOOPI_SEP.$this->strId)) unlink(self::get_path()._PLOOPI_SEP.$this->strId);
        }
        return true;
    }
}
