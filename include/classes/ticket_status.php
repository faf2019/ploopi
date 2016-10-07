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

namespace ploopi;

use ploopi;

/**
 * Classe d'accès à la table ploopi_ticket_status
 *
 * @package ploopi
 * @subpackage ticket
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class ticket_status extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return ticket_status
     */

    public function ticket_status()
    {
        parent::__construct('ploopi_ticket_status', 'id_ticket', 'id_user', 'status');
    }

    /**
     * Enregistre l'état d'un ticket pour un utilisateur
     */

    public function save()
    {
        if ($this->new)
        {
            $this->fields['timestp'] = date::createtimestamp();
            parent::save();
        }
    }
}
