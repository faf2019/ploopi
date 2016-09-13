<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Gestion de l'historique des pages
 *
 * @package wiki
 * @subpackage page_history
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Classe d'accès à la table 'ploopi_mod_wiki_page_history'
 *
 * @package wiki
 * @subpackage page_history
 * @author Stéphane Escaich
 * @copyright Ovensia
 */

class wiki_page_history extends ovensia\ploopi\data_object
{
    /**
     * Constructeur de la classe
     *
     * @return wiki_page_history
     */

    public function __construct()
    {
        parent::__construct(
            'ploopi_mod_wiki_page_history',
            'id_page',
            'revision',
            'id_module'
        );
    }

    public function open(...$args)
    {
        $strIdPage = $args[0];
        $intRevision = $args[1];
        $intIdModule = isset($args[2]) ? $args[2] : null;

        return parent::open($strIdPage, $intRevision, is_null($intIdModule) ? $_SESSION['ploopi']['moduleid'] : $intIdModule);
    }
}
