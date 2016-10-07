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
 * Gestion des abonnés aux rubriques (frontoffice)
 *
 * @package webedit
 * @subpackage heading_subscriber
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Classe d'accès à la table ploopi_mod_webedit_heading_subscriber
 *
 * @package webedit
 * @subpackage heading_subscriber
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class webedit_heading_subscriber extends ploopi\data_object
{
    /**
     * Contructeur de la classe
     *
     * @return webedit_heading_subscriber
     */

    public function __construct()
    {
        parent::__construct('ploopi_mod_webedit_heading_subscriber', 'id_heading', 'email');
    }
}
