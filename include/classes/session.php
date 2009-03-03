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
 * Gestionnaire de sessions avec une base de données
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe permettant de remplacer le gestionnaire de session par défaut.
 * Les sessions sont stockées dans la base de données.
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ploopi_session
{
    public function open() {}

    public function close() {}

    /**
     * Chargement de la session depuis la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function read($id)
    {
        global $db;
        return ($db->query("SELECT `data` FROM `ploopi_session` WHERE `id` = '".$db->addslashes($id)."'") && $record = $db->fetchrow()) ? gzuncompress($record['data']) : '';
    }

    /**
     * Ecriture de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     * @param string $data données de la session
     */

    public function write($id, $data)
    {
        global $db;
        $db->query("REPLACE INTO `ploopi_session` VALUES ('".$db->addslashes($id)."', '".$db->addslashes(time())."', '".$db->addslashes(gzcompress($data))."')");
        return true;
    }

    /**
     * Suppression de la session dans la base de données.
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function destroy($id)
    {
        global $db;
        $db->query("DELETE FROM `ploopi_session` WHERE `id` = '".$db->addslashes($id)."'");
        return true;
    }

    /**
     * Suppression des sessions périmées (Garbage collector).
     * Utilisé par le gestionnaire de session de Ploopi.
     *
     * @param int $max durée d'une session en secondes
     */

    public function gc($max)
    {
        global $db;
        $db->query("DELETE FROM `ploopi_session` WHERE `access` < '".$db->addslashes((time() - $max))."'");
        return true;
    }

    /**
     * Regénère un identifiant de session
     *
     * @see session_regenerate_id
     */

    public function regenerate_id()
    {
        if (defined('_PLOOPI_USE_DBSESSION') && _PLOOPI_USE_DBSESSION)
        {
            global $db;
            $old_sess_id = session_id();
            session_regenerate_id(false);
            $new_sess_id = session_id();
            $db->query("UPDATE `ploopi_session` SET `id` = '".$db->addslashes($new_sess_id)."' WHERE `id` = '".$db->addslashes($old_sess_id)."'");
        }
        else session_regenerate_id(true);
    }
}
?>
