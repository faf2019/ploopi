<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Fonctions, constantes, variables globales
 *
 * @package chat
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Temps en secondes au bout duquel l'utilisateur est déconnecté si on ne reçoit aucune demande
 */

define('_CHAT_CONNECTION_TIMEOUT', 5); 

/**
 * Mise à jour des utilisateurs connectés
 *
 * @package chat
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * 
 * @see chat_connected
 */

function chat_connected_update()
{
    global $db;
    
    include_once './modules/chat/classes/class_chat_connected.php';
    
    // calcul du timestamp de timeout
    $intTimeoutTimestp = ploopi_timestamp_add(ploopi_createtimestamp(), 0, 0, 0 - _CHAT_CONNECTION_TIMEOUT);
    
    // suppression des utilisateurs qui ont une trop longue période sans activité
    $sql = "DELETE FROM ploopi_mod_chat_connected WHERE lastupdate_timestp < {$intTimeoutTimestp}";
    $db->query($sql);
    
    // reférencement de l'utilisateur connecté
    $chat_connected = new chat_connected();
    $chat_connected->open();
    $chat_connected->save();
}
?>