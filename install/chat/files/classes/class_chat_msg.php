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
 * Gestion des messages.
 *
 * @package chat
 * @subpackage msg
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès à la table ploopi_mod_chat_msg
 *
 * @package chat
 * @subpackage msg
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class chat_msg extends data_object
{   
    /**
     * Constructeur de la classe
     *
     * @return chat_msg
     */
    
    function chat_msg()
    {
        parent::data_object('ploopi_mod_chat_msg');
    }
    
    /**
     * Enregistrement
     *
     * @param string $content contenu du message
     * @return int identifiant du message
     */
    
    function save($content)
    {
        $this->fields['content'] = $content;
        $this->fields['timestp'] = ploopi_createtimestamp();
        parent::setuwm();
        return(parent::save());
    }
}
?>