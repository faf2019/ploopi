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
 * Gestionnaire de sessions avec une base de donn�es
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

/**
 * Classe permettant de remplacer le gestionnaire de session par d�faut.
 * Les sessions sont stock�es dans la base de donn�es.
 *
 * @package ploopi
 * @subpackage session
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

class ploopi_session
{
    public function open() {}

    public function close() {}

    /**
     * Chargement de la session depuis la base de donn�es.
     * Utilis� par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function read($id)
    {
        global $db_session;
        return ($db_session->query("SELECT `data` FROM `ploopi_session` WHERE `id` = '".$db_session->addslashes($id)."'") && $record = $db_session->fetchrow()) ? gzuncompress($record['data']) : '';
    }

    /**
     * Ecriture de la session dans la base de donn�es.
     * Utilis� par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     * @param string $data donn�es de la session
     */

    public function write($id, $data)
    {
        global $db_session;
        $db_session->query("REPLACE INTO `ploopi_session` VALUES ('".$db_session->addslashes($id)."', '".$db_session->addslashes(time())."', '".$db_session->addslashes(gzcompress($data))."')");
        return true;
    }

    /**
     * Suppression de la session dans la base de donn�es.
     * Utilis� par le gestionnaire de session de Ploopi.
     *
     * @param string $id identifiant de la session
     */

    public function destroy($id)
    {
        global $db_session;
        $db_session->query("DELETE FROM `ploopi_session` WHERE `id` = '".$db_session->addslashes($id)."'");
        return true;
    }

    /**
     * Suppression des sessions p�rim�es (Garbage collector).
     * Utilis� par le gestionnaire de session de Ploopi.
     *
     * @param int $max dur�e d'une session en secondes
     */

    public function gc($max)
    {
        global $db_session;
        $db_session->query("DELETE FROM `ploopi_session` WHERE `access` < '".$db_session->addslashes((time() - $max))."'");
        return true;
    }

    /**
     * Reg�n�re un identifiant de session
     *
     * @see session_regenerate_id
     */

    public function regenerate_id()
    {
        if (defined('_PLOOPI_USE_DBSESSION') && _PLOOPI_USE_DBSESSION)
        {
            global $db_session;
            $old_sess_id = session_id();
            session_regenerate_id(false);
            $new_sess_id = session_id();
            $db_session->query("UPDATE `ploopi_session` SET `id` = '".$db_session->addslashes($new_sess_id)."' WHERE `id` = '".$db_session->addslashes($old_sess_id)."'");
        }
        else session_regenerate_id(true);
    }
}
?>
