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
 * Partie publique du module
 *
 * @package chat
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Audrey Gilbert
 */

/**
 * Initialisation du module
 */

ploopi_init_module('chat');

/**
 * mise à jour de la liste des utilisateurs connectés
 */

chat_connected_update();

echo $skin->create_pagetitle($_SESSION['ploopi']['modulelabel']);
echo $skin->open_simplebloc('Salle de discussion');
?>
<div style="overflow:auto;">

    <div id="chat_userbox">
        <div id="chat_userbox_title">Utilisateurs connectés :</div>
        <div id="chat_userbox_list"></div>
    </div>

    <div id="chat_msgbox">
    </div>

</div>

<form onsubmit="javascript:chat_msg_send();return (false);" >
<p id="chat_inputbox" class="ploopi_va">
    <span>&nbsp;Nouveau message&nbsp;:&nbsp;</span>
    <input type="text" class="text" id="chat_msg" />
    <input type="submit" class="button" id="chat_msg_submit" value="Envoyer" />
</p>
</form>

<?
echo $skin->close_simplebloc();
?>

<script type="text/javascript">
ploopi_window_onload_stock(function() { $('chat_msg').focus(); chat_refresh_onload(); });
</script>