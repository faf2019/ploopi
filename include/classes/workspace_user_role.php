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
 * Gestion de la relation Espace de travail / Utilisateur / Rôle (table ploopi_workspace_user_role)
 *
 * @package ploopi
 * @subpackage workspace
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class workspace_user_role extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return workspace_user_role
     */

    public function __construct()
    {
        parent::__construct('ploopi_workspace_user_role','id_user','id_workspace','id_role');
    }
}
