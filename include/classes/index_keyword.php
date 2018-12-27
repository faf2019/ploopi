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
 * Classe d'accès à la table ploopi_index_keyword
 *
 * @package ploopi
 * @subpackage search_index
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class index_keyword extends data_object
{
    /**
     * Constructeur de la classe
     *
     * @return index_keyword
     */

    public function index_keyword()
    {
        parent::__construct('ploopi_index_keyword', 'id');
        $this->setdb(search_index::getdb());
    }

    /**
     * Enregistre le mot clé
     *
     * @return boolean true si l'enregistrement a été correctement réalisé
     */

    public function save()
    {
        $this->fields['twoletters'] = mb_substr($this->fields['keyword'],0,2);
        return parent::save();
    }
}
